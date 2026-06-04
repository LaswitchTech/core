<?php

namespace LaswitchTech\Core;

/**
 * Encryption service — AES-256-GCM authenticated encryption.
 *
 * Stateless utility class providing symmetric encryption, key generation,
 * and passphrase-based key derivation.
 *
 * Format: base64( nonce(12) || ciphertext || tag(16) )
 * Key derivation uses PBKDF2-HMAC-SHA256 with random salt (100k iterations).
 */
class Encryption {

    /** @var string Cipher algorithm */
    const CIPHER = 'aes-256-gcm';

    /** @var int Salt length in bytes */
    const SALT_LENGTH = 32;

    /** @var int Nonce (IV) length in bytes for GCM */
    const NONCE_LENGTH = 12;

    /** @var int Auth tag length in bytes for GCM */
    const TAG_LENGTH = 16;

    /** @var int PBKDF2 iterations for key derivation from passphrase */
    const KDF_ITERATIONS = 100000;

    /** @var string Hash algorithm for PBKDF2 */
    const KDF_ALGO = 'sha256';

    // --------------------------------------------------------------------
    // Key Generation
    // --------------------------------------------------------------------

    /**
     * Generate a cryptographically secure random key.
     *
     * @param int $length Key length in bytes (default 32 for AES-256).
     * @return string Raw binary key.
     */
    public static function generateKey(int $length = 32): string {
        return random_bytes($length);
    }

    // --------------------------------------------------------------------
    // Key Derivation (passphrase → key)
    // --------------------------------------------------------------------

    /**
     * Derive a key from a passphrase using PBKDF2-HMAC-SHA256.
     *
     * @param string $passphrase The passphrase to derive from.
     * @param null|string $salt Hex-encoded salt (auto-generated if null).
     * @return array { key: binary, salt: hex }
     */
    public static function deriveKey(string $passphrase, ?string $salt = null): array {
        $salt = $salt ?? bin2hex(random_bytes(self::SALT_LENGTH));
        $key = hash_pbkdf2(
            self::KDF_ALGO,
            $passphrase,
            hex2bin($salt),
            self::KDF_ITERATIONS,
            32,           // 256-bit key
            true            // raw output
        );
        return ['key' => $key, 'salt' => $salt];
    }

    /**
     * Derive a key from a passphrase with an auto-generated salt.
     *
     * @param string $passphrase The passphrase to derive from.
     * @return string Hex-encoded key.
     */
    public static function deriveKeyFromPassphrase(string $passphrase): string {
        $derived = self::deriveKey($passphrase);
        return bin2hex($derived['key']);
    }

    // --------------------------------------------------------------------
    // Encryption / Decryption (raw key)
    // --------------------------------------------------------------------

    /**
     * Encrypt data using AES-256-GCM with a raw binary key.
     *
     * @param string $data Plaintext to encrypt.
     * @param string $key Raw 32-byte encryption key.
     * @return string base64-encoded payload (nonce || ciphertext || tag).
     */
    public static function encrypt(string $data, string $key): string {
        $nonce = random_bytes(self::NONCE_LENGTH);
        $tagBytes = '';

        // GCM: openssl_encrypt returns ciphertext only; tag via reference param
        $ciphertext = openssl_encrypt(
            $data,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tagBytes
        );

        if ($ciphertext === false && strlen($data) > 0) {
            throw new \RuntimeException('Encryption failed: ' . openssl_error_string());
        }

        // For empty plaintext, GCM may return false — use empty ciphertext with generated tag
        if ($ciphertext === false) {
            $ciphertext = '';
            // Generate a valid tag for the nonce-only payload
            $tagBytes = random_bytes(self::TAG_LENGTH);
        }

        // Pack: nonce(12) || ciphertext || tag(16)
        return base64_encode($nonce . $ciphertext . $tagBytes);
    }

    /**
     * Encrypt data with an associated data (AAD) field for bound integrity.
     *
     * AAD is authenticated but not encrypted — useful for binding a key_id
     * or context string to the ciphertext so it can only be decrypted in
     * the correct context.
     *
     * @param string $data Plaintext to encrypt.
     * @param string $key Raw 32-byte encryption key.
     * @param string $aad Associated authenticated data.
     * @return string base64-encoded payload (nonce || ciphertext || tag).
     */
    public static function encryptWithAad(string $data, string $key, string $aad): string {
        $nonce = random_bytes(self::NONCE_LENGTH);
        $tagBytes = '';

        $ciphertext = openssl_encrypt(
            $data,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tagBytes,
            $aad
        );

        if ($ciphertext === false && strlen($data) > 0) {
            throw new \RuntimeException('Encryption with AAD failed: ' . openssl_error_string());
        }

        if ($ciphertext === false) {
            $ciphertext = '';
            $tagBytes = random_bytes(self::TAG_LENGTH);
        }

        return base64_encode($nonce . $ciphertext . $tagBytes);
    }

    /**
     * Decrypt data encrypted with encrypt().
     *
     * @param string $payload base64-encoded payload (nonce || ciphertext || tag).
     * @param string $key Raw 32-byte encryption key.
     * @return string Plaintext.
     */
    public static function decrypt(string $payload, string $key): string {
        $raw = base64_decode($payload);

        // Allow nonce-only payloads (empty plaintext was encrypted with openssl producing nonce-only)
        if (strlen($raw) < self::NONCE_LENGTH + self::TAG_LENGTH) {
            if (strlen($raw) === self::NONCE_LENGTH) {
                return '';   // nonce-only: empty plaintext was encrypted
            }
            throw new \InvalidArgumentException('Payload too short to be valid.');
        }

        $nonce  = substr($raw, 0, self::NONCE_LENGTH);
        $tag    = substr($raw, -self::TAG_LENGTH);
        $ciphertextLen = strlen($raw) - self::NONCE_LENGTH - self::TAG_LENGTH;
        $ciphertext = ($ciphertextLen > 0) ? substr($raw, self::NONCE_LENGTH, $ciphertextLen) : '';

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag
        );

        if ($plaintext === false) {
            throw new \RuntimeException('Decryption failed (invalid key or corrupted payload): ' . openssl_error_string());
        }

        return $plaintext;
    }

    /**
     * Decrypt data that was encrypted with encryptWithAad().
     *
     * @param string $payload base64-encoded payload (nonce || ciphertext || tag).
     * @param string $key Raw 32-byte encryption key.
     * @param string $aad Associated authenticated data (must match the value used during encryption).
     * @return string Plaintext.
     */
    public static function decryptWithAad(string $payload, string $key, string $aad): string {
        $raw = base64_decode($payload);

        // Allow nonce-only payloads (empty plaintext was encrypted with openssl producing nonce-only)
        if (strlen($raw) < self::NONCE_LENGTH + self::TAG_LENGTH) {
            if (strlen($raw) === self::NONCE_LENGTH) {
                return '';   // nonce-only: empty plaintext was encrypted
            }
            throw new \InvalidArgumentException('Payload too short to be valid.');
        }

        $nonce  = substr($raw, 0, self::NONCE_LENGTH);
        $tag    = substr($raw, -self::TAG_LENGTH);
        $ciphertextLen = strlen($raw) - self::NONCE_LENGTH - self::TAG_LENGTH;
        $ciphertext = ($ciphertextLen > 0) ? substr($raw, self::NONCE_LENGTH, $ciphertextLen) : '';

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            $aad
        );

        if ($plaintext === false) {
            throw new \RuntimeException('Decryption with AAD failed (invalid key, wrong AAD, or corrupted payload): ' . openssl_error_string());
        }

        return $plaintext;
    }

    // --------------------------------------------------------------------
    // Passphrase-based convenience methods
    // --------------------------------------------------------------------

    /**
     * Encrypt data using a passphrase directly.
     *
     * Generates a random salt internally, derives the key, and packs
     * (salt || encrypted_payload) for storage in config files or DB columns.
     *
     * Format: base64( salt(32) || nonce(12) || ciphertext || tag(16) )
     *
     * @param string $data Plaintext to encrypt.
     * @param string $passphrase Passphrase for key derivation.
     * @return string Hex-encoded payload (salt || encrypted).
     */
    public static function encryptWithPassphrase(string $data, string $passphrase): string {
        $derived = self::deriveKey($passphrase);
        $encrypted = self::encrypt($data, $derived['key']);
        // Pack salt(32) || encrypted_payload
        return bin2hex(hex2bin($derived['salt']) . base64_decode($encrypted));
    }

    /**
     * Decrypt data that was encrypted with encryptWithPassphrase().
     *
     * @param string $payload Hex-encoded payload (salt || encrypted).
     * @param string $passphrase Passphrase for key derivation.
     * @return string Plaintext.
     */
    public static function decryptWithPassphrase(string $payload, string $passphrase): string {
        $raw = hex2bin($payload);

        if (strlen($raw) < self::SALT_LENGTH + self::NONCE_LENGTH + self::TAG_LENGTH) {
            throw new \InvalidArgumentException('Payload too short to be valid.');
        }

        $salt    = bin2hex(substr($raw, 0, self::SALT_LENGTH));
        $encryptedData = substr($raw, self::SALT_LENGTH);
        $derived = self::deriveKey($passphrase, $salt);
        return self::decrypt(base64_encode($encryptedData), $derived['key']);
    }

    // --------------------------------------------------------------------
    // Token generation (convenience for CSRF-like tokens, API keys, etc.)
    // --------------------------------------------------------------------

    /**
     * Generate a secure random token suitable for API keys, session IDs, etc.
     *
     * @param int $bytes Number of random bytes.
     * @return string Hex-encoded token.
     */
    public static function token(int $bytes = 32): string {
        return bin2hex(random_bytes($bytes));
    }

    /**
     * Generate a URL-safe secure token (base64-url encoded, no padding).
     *
     * @param int $bytes Number of random bytes.
     * @return string URL-safe base64 string without padding.
     */
    public static function urlToken(int $bytes = 32): string {
        return rtrim(strtr(base64_encode(random_bytes($bytes)), '+/', '-_'), '=');
    }

    // --------------------------------------------------------------------
    // Hashing (one-way) for password/storage hashing
    // --------------------------------------------------------------------

    /**
     * Generate a secure hash of data using HMAC-SHA256.
     *
     * Useful for verifying data integrity without needing the key stored
     * alongside the data. The key must be kept secret.
     *
     * @param string $data Data to hash.
     * @param string $key HMAC key (must be ≥ 32 bytes for security).
     * @return string Hex-encoded HMAC.
     */
    public static function hmac(string $data, string $key): string {
        return bin2hex(hash_hmac(self::KDF_ALGO, $data, $key, true));
    }

    /**
     * Verify an HMAC in timing-safe manner.
     *
     * @param string $expected Hex-encoded HMAC.
     * @param string $actual Hex-encoded HMAC to verify against.
     * @return bool True if they match.
     */
    public static function hashEquals(string $expected, string $actual): bool {
        return hash_equals($expected, $actual);
    }
}
