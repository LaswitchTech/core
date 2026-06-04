<?php

namespace LaswitchTech\Core\Objects;

/**
 * TOTP (RFC 6238) — Time-based one-time password implementation.
 *
 * Compatible with Google Authenticator, Authy, and other TOTP apps.
 * Generates provisioning URIs for QR code scanning.
 */
class TOTP {

    /** @var int Seconds per time step */
    const PERIOD = 30;

    /** @var int Number of OTP digits (Google Authenticator standard) */
    const DIGITS = 6;

    /**
     * Generate a new cryptographically secure TOTP secret.
     *
     * @return string Hex-encoded 32-byte secret.
     */
    public static function generateSecret(): string {
        return bin2hex(random_bytes(32));
    }

    /**
     * Encode a hex secret to Base32 for QR code / provisioning URIs.
     *
     * @param string $hex Hex-encoded secret (from generateSecret()).
     * @return string Base32 string, uppercase, no padding.
     */
    public static function hexToBase32(string $hex): string {
        $bytes = hex2bin($hex);
        $base32 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $result = '';

        for ($i = 0; $i < strlen($bytes); $i += 5) {
            $a = ord($bytes[$i]);
            $b = isset($bytes[$i + 1]) ? ord($bytes[$i + 1]) : 0;
            $c = isset($bytes[$i + 2]) ? ord($bytes[$i + 2]) : 0;
            $d = isset($bytes[$i + 3]) ? ord($bytes[$i + 3]) : 0;
            $e = isset($bytes[$i + 4]) ? ord($bytes[$i + 4]) : 0;

            $result .= $base32[$a >> 3 & 0x1f];
            $result .= $base32[$a & 0x1f << 5 | $b >> 3];
            $result .= $base32[$b & 0x07 << 2 | $c >> 6];
            $result .= $base32[$c & 0x3c >> 2];
            $result .= $base32[$c & 0x03 << 4 | $d >> 4];
            $result .= $base32[$d & 0x0f << 1 | $e >> 7];
            $result .= $base32[$e & 0x7f >> 1];
        }

        return rtrim(strtr($result, '=', ''), '=');
    }

    /**
     * Generate a provisioning URI for QR code scanning.
     *
     * @param string $secret Hex-encoded TOTP secret.
     * @param string $issuer Application/issuer name (e.g., "Core-Web").
     * @param string $account Account identifier (e.g., user email).
     * @return string otpauth:// URI scannable by Google Authenticator.
     */
    public static function provisioningURI(string $secret, string $issuer, string $account): string {
        $base32 = self::hexToBase32($secret);

        return 'otpauth://totp/'
            . urlencode($issuer) . ':' . urlencode($account)
            . '?secret=' . $base32
            . '&issuer=' . urlencode($issuer)
            . '&digits=' . self::DIGITS
            . '&period=' . self::PERIOD;
    }

    /**
     * Verify a TOTP code against the current time window.
     *
     * Allows ±1 step (90 seconds total window) for clock skew tolerance.
     *
     * @param string $secret Hex-encoded TOTP secret.
     * @param string $code 6-digit OTP code from user.
     * @param null|int $timestamp Override timestamp for testing.
     * @return bool True if the code is valid within any valid window.
     */
    public static function verify(string $secret, string $code, ?int $timestamp = null): bool {
        $timeSlot = floor(($timestamp ?? time()) / self::PERIOD);

        // Check current slot and ±1 for clock skew
        foreach ([-1, 0, 1] as $offset) {
            if (self::atotp($secret, $timeSlot + $offset) === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate the TOTP code for a specific time slot.
     *
     * @param string $secret Hex-encoded secret.
     * @param int $timeSlot Time step (floor(time / period)).
     * @return string 6-digit zero-padded OTP code.
     */
    public static function atotp(string $secret, int $timeSlot): string {
        $key = hex2bin($secret);

        // Pack timestamp as 8-byte big-endian (RFC 6238 §4.2)
        $msg = pack('N*', 0) . pack('N*', $timeSlot);

        // HMAC-SHA1
        $hmac = hash_hmac('sha1', $msg, $key, true);

        // Dynamic truncation (RFC 6238 §5.3)
        $offset = ord($hmac[19]) & 0x0f;
        $binary = ((ord($hmac[$offset]) & 0x7f) << 24)
                | ((ord($hmac[$offset + 1]) & 0xff) << 16)
                | ((ord($hmac[$offset + 2]) & 0xff) << 8)
                | (ord($hmac[$offset + 3]) & 0xff);

        $otp = $binary % pow(10, self::DIGITS);

        return str_pad((string)$otp, self::DIGITS, '0', STR_PAD_LEFT);
    }
}
