<?php

namespace LaswitchTech\Core\Objects;

/**
 * Password Reset Tokens — secure token-based password reset.
 *
 * Generates, stores (hashed), verifies, and consumes one-time reset tokens
 * with 60-minute expiry. Replaces the old random-password generation flow.
 */
class PasswordResetToken {

    /** @var int Token length in bytes (generates 64-char hex token) */
    const TOKEN_BYTES = 32;

    /** @var int Expiry in minutes */
    const EXPIRY_MINUTES = 60;

    /**
     * Generate a new password reset token, store it, and email it to the user.
     *
     * @param int $userId User ID to generate for.
     * @return string|false The plain-text token (to include in URL) or false on failure.
     */
    public static function generate(int $userId) {
        global $DATABASE, $SMTP;

        // Generate secure 64-char hex token
        $token = Encryption::token(self::TOKEN_BYTES);

        // Store hashed token with expiry (one pending reset per user at a time)
        $DATABASE->query()->table('password_resets')
            ->delete()  // Invalidate any existing pending resets for this user
            ->where('user', $userId)->result();

        $DATABASE->query()->table('password_resets')->insert([
            'user'     => $userId,
            'token'    => hash('sha256', $token),
            'expires'  => date('Y-m-d H:i:s', strtotime('+' . self::EXPIRY_MINUTES . ' minutes')),
            'used'     => 0,
        ])->result();

        // Email user with token link
        $user = new Objects\User($userId);
        if (!$user->found()) {
            return false;
        }

        // Use Pin system for delivery (it handles SMTP correctly)
        $pin  = new Objects\Pin();
        $code = strtoupper($token);
        $pin->save($userId, $token, self::EXPIRY_MINUTES);

        return $pin->notify($user, $code, function(object $u, string $t): object {
            global $SMTP;
            return $SMTP->message()
                ->subject('Reset your password')
                ->body(sprintf(
                    '<p>We received a request to reset your password.</p><p>Your verification code: <strong>%s</strong></p>',
                    $t
                ));
        });
    }

    /**
     * Verify a submitted token and reset the user's password.
     *
     * @param string $token The plain-text token from the email link.
     * @param string $newPassword New password to set (will be policy-checked by caller).
     * @return bool True on success, false on failure.
     */
    public static function verifyAndReset(string $token, string $newPassword): bool {
        global $DATABASE;

        $query = $DATABASE->query()
            ->table('password_resets')
            ->select('*')
            ->where('token', hash('sha256', $token))
            ->where('used', 0)
            ->where('expires', date('Y-m-d H:i:s'), '>')
            ->limit(1)
            ->result();

        if (empty($query)) {
            return false;
        }

        $entry = $query[0];

        // Mark as used
        $DATABASE->query()->table('password_resets')
            ->update(['used' => 1])
            ->where('id', $entry['id'])->result();

        // Update user password
        $user = new Objects\User((int) $entry['user']);
        if (!$user->found()) {
            return false;
        }

        $user->backend()->set($newPassword)->save();

        // Invalidate all existing sessions and remember tokens for this user
        self::invalidateSessions((int) $entry['user']);

        // Clean up reset token
        $DATABASE->query()->table('password_resets')
            ->delete()->where('id', $entry['id'])->result();

        return true;
    }

    /**
     * Invalidate all sessions and remember tokens for a user.
     */
    protected static function invalidateSessions(int $userId): void {
        global $DATABASE;

        // Delete database sessions
        $DATABASE->query()->table('sessions')
            ->delete()->where('user', $userId)->result();

        // Delete remember tokens
        $DATABASE->query()->table('remember_tokens')
            ->delete()->where('user', $userId)->result();
    }

    /**
     * Check if a valid (unused, non-expired) reset token exists for a user.
     */
    public static function hasValidToken(int $userId): bool {
        global $DATABASE;

        return (bool) $DATABASE->query()
            ->table('password_resets')
            ->select('COUNT(*) as count')
            ->where('user', $userId)
            ->where('used', 0)
            ->where('expires', date('Y-m-d H:i:s'), '>')
            ->result()[0]['count'];
    }
}
