<?php

namespace LaswitchTech\Core\Objects;

/**
 * User Registration — config-gated public user registration flow.
 *
 * Handles new account creation with email verification, password policy enforcement,
 * and uniqueness checking. Only active when auth.allow_registration is true.
 */
class UserRegistration {

    /**
     * Attempt to register a new user.
     *
     * @param string $email Email address (becomes the username).
     * @param string $password Plaintext password.
     * @return array ['status' => bool, 'errors' => string[], 'user' => User|null]
     */
    public static function attempt(string $email, string $password): array {
        global $CONFIG, $DATABASE;

        // Check registration gate
        if (!$CONFIG->get('auth', 'allow_registration')) {
            return ['status' => false, 'errors' => ['Registration is currently disabled.'], 'user' => null];
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['status' => false, 'errors' => ['Please enter a valid email address.'], 'user' => null];
        }

        // Password policy check
        $backend = new \LaswitchTech\Core\Backends\Local(null);
        $policyErrors = $backend->validatePolicy($password);
        if (!empty($policyErrors)) {
            return ['status' => false, 'errors' => $policyErrors, 'user' => null];
        }

        // Check email uniqueness (username = email)
        $existing = $DATABASE->query()->table('users')
            ->select('id')
            ->where('username', $email)
            ->limit(1)
            ->result();

        if (!empty($existing)) {
            return ['status' => false, 'errors' => ['An account with that email already exists.'], 'user' => null];
        }

        // Create backend record (local auth)
        $backendId = $DATABASE->query()->table('backends')->insert([
            'type'     => 'local',
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'domain'   => '',
            'server'   => '',
            'ssl'      => 0,
        ])->result();

        // Create user record (unverified until email verification completes)
        $userId = $DATABASE->query()->table('users')->insert([
            'uuid'        => '',
            'username'    => $email,
            'backend'     => $backendId,
            'session'     => null,
            'vcard'       => null,
            'organization'=> null,
            'pin'         => null,
            'token'       => null,
            'settings'    => json_encode(['totp_enabled' => false]),
            'isVerified'  => 0,
            'isBanned'    => null,
            'isInactive'  => null,
            'isDeleted'   => null,
        ])->result();

        if (!$userId) {
            return ['status' => false, 'errors' => ['Registration failed. Please try again later.'], 'user' => null];
        }

        // Send verification email using the Pin system (same pattern as forgot password flow)
        $user = new User($userId);
        $pin  = new Pin();
        $code = $pin->generate();
        $pin->save($userId, $code);

        $pin->notify($user, $code, function(object $u, string $c): object {
            global $SMTP;
            return $SMTP->message()
                ->subject('Verify your account')
                ->body('<p>Your verification code is: <strong>' . $c . '</strong></p>');
        });

        return ['status' => true, 'errors' => [], 'user' => $user];
    }
}
