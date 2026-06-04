<?php

namespace LaswitchTech\Core\Objects;

/**
 * Recovery Codes — single-use backup codes for TOTP 2FA.
 *
 * Generates, stores, verifies, and consumes recovery codes.
 * Codes are hashed with password_hash() before storage.
 */
class RecoveryCodes {

    /** @var int Default number of codes to generate */
    const DEFAULT_COUNT = 10;

    /** @var int Default expiry in hours (90 days) */
    const DEFAULT_EXPIRY_HOURS = 2160;

    /** @var int Selector length in bytes */
    const SELECTOR_LENGTH = 4;

    /**
     * Generate N new recovery codes for a user.
     *
     * Deletes all existing codes first (one-time regeneration).
     * Returns plain-text codes — show to user once and save nothing!
     *
     * @param int $userId User ID to generate codes for.
     * @param int $count Number of codes to generate.
     * @return array List of plain-text recovery codes (ONLY time visible).
     */
    public static function generate(int $userId, int $count = self::DEFAULT_COUNT): array {
        global $DATABASE;

        // Delete old codes
        $DATABASE->query()->table('recovery_codes')
            ->delete()->where('user', $userId)->result();

        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $code    = bin2hex(random_bytes(5));   // 10 hex chars (~67 bits entropy)
            $selector = bin2hex(random_bytes(self::SELECTOR_LENGTH));  // 8-char selector

            $DATABASE->query()->table('recovery_codes')->insert([
                'user'        => $userId,
                'selector'    => $selector,
                'hashed_code' => password_hash($code, PASSWORD_DEFAULT),
                'expires'     => date('Y-m-d H:i:s', strtotime('+' . self::DEFAULT_EXPIRY_HOURS . ' hours')),
            ])->result();

            $codes[] = $code;
        }

        return $codes;  // User must save these now — won't be recoverable later
    }

    /**
     * Verify a recovery code and consume it (single-use).
     *
     * @param int $userId User ID to verify against.
     * @param string $selector The public selector from the original code pair.
     * @param string $code Full plain-text code provided by user.
     * @return bool True if valid and consumed, false otherwise.
     */
    public static function verifyAndConsume(int $userId, string $selector, string $code): bool {
        global $DATABASE;

        $query = $DATABASE->query()
            ->table('recovery_codes')
            ->select('*')
            ->where('user', $userId)
            ->where('selector', $selector)
            ->where('expires', date('Y-m-d H:i:s'), '>')
            ->limit(1)
            ->result();

        if (empty($query)) {
            return false;
        }

        $entry = $query[0];

        // Timing-safe password_verify (same pattern as Pin.php)
        if (!password_verify($code, $entry['hashed_code'])) {
            return false;
        }

        // Delete the used code (single-use enforcement)
        $DATABASE->query()->table('recovery_codes')
            ->delete()->where('id', $entry['id'])->result();

        return true;
    }

    /**
     * Get remaining codes count for a user.
     *
     * @param int $userId User ID.
     * @return int Number of unused, non-expired codes remaining.
     */
    public static function remaining(int $userId): int {
        global $DATABASE;

        return (int) $DATABASE->query()
            ->table('recovery_codes')
            ->select('COUNT(*) as count')
            ->where('user', $userId)
            ->where('expires', date('Y-m-d H:i:s'), '>')
            ->result()[0]['count'];
    }

    /**
     * List active codes (selector only — for user reference).
     *
     * @param int $userId User ID.
     * @return array Array of ['selector' => string, 'expires' => string].
     */
    public static function listActive(int $userId): array {
        global $DATABASE;

        return $DATABASE->query()
            ->table('recovery_codes')
            ->select('selector, expires')
            ->where('user', $userId)
            ->where('expires', date('Y-m-d H:i:s'), '>')
            ->result();
    }
}
