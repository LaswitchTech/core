# Encryption Service

> **File**: `src/Encryption.php`  
> **Phase**: 1.5 — Stabilization  

## Overview

The Encryption service provides symmetric authenticated encryption, key management, and cryptographic utilities for Core-Web. All operations are stateless — no persistent state, no singletons.

## Cryptographic Choices

| Parameter | Value | Rationale |
|---|---|---|
| **Cipher** | AES-256-GCM (AEAD) | Authenticated encryption — confidentiality + integrity in one step |
| **Key size** | 256 bits (32 bytes) | Industry standard for symmetric encryption |
| **Nonce (IV)** | 12 bytes, random per operation | Standard GCM nonce length; unique nonces prevent replay attacks |
| **Auth tag** | 16 bytes, returned separately by OpenSSL | Guarantees ciphertext integrity on decryption |
| **Key derivation** | PBKDF2-HMAC-SHA256, 100k iterations | NIST-recommended minimum for passphrase-based key derivation |

### Payload Format

**Raw encryption:**
```
base64( nonce[12] || ciphertext[n] || tag[16] )
```

**Passphrase-based:**
```
hex( salt[32] || base64_payload )
```

The salt is included in the payload so each encryption gets a unique key even with the same passphrase.

## API Reference

### Key Management

#### `Encryption::generateKey(?int $length = 32): string`

Generate a cryptographically secure random key using `random_bytes()`.

```php
$key = Encryption::generateKey();        // 32 bytes (AES-256)
$key = Encryption::generateKey(64);      // 64 bytes
```

#### `Encryption::deriveKey(string $passphrase, ?string $salt = null): array`

Derive a key from a passphrase using PBKDF2-HMAC-SHA256.

```php
$result = Encryption::deriveKey('my-passphrase');
// Returns: ['key' => binary_key, 'salt' => 'hex_encoded_salt']

// With existing salt (deterministic — useful for testing):
$result = Encryption::deriveKey('my-passphrase', $existingSalt);
```

#### `Encryption::deriveKeyFromPassphrase(string $passphrase): string`

Convenience method — returns hex-encoded derived key with auto-generated salt.

```php
$hexKey = Encryption::deriveKeyFromPassphrase('my-passphrase');
// e.g., "a1b2c3d4..." (64 hex chars)
```

### Encryption / Decryption

#### `Encryption::encrypt(string $data, string $key): string`

Encrypt data with AES-256-GCM using a raw binary key. Generates a random nonce and packs it into the payload.

```php
$key = Encryption::generateKey();
$encrypted = Encryption::encrypt('sensitive PII', $key);
// Returns: base64-encoded payload

$decrypted = Encryption::decrypt($encrypted, $key);
// Returns: 'sensitive PII'
```

**Guarantees:**
- Each call produces a **unique** ciphertext (different nonce every time)
- Tampered payloads fail decryption with an exception
- Empty strings are supported

#### `Encryption::encryptWithAad(string $data, string $key, string $aad): string`

Encrypt with associated authenticated data. The AAD is bound to the ciphertext — decryption requires the exact same AAD value, preventing ciphertext theft across contexts.

```php
$keyId = 'invoice:1234';
$encrypted = Encryption::encryptWithAad('amount: $5000', $key, $keyId);

// Correct AAD — works:
$plaintext = Encryption::decryptWithAad($encrypted, $key, 'invoice:1234');

// Wrong AAD — fails with RuntimeException:
Encryption::decryptWithAad($encrypted, $key, 'invoice:9999'); // throws
```

**Use case:** Bind encrypted data to a resource identifier or context so it can only be decrypted in the correct context. Prevents "ciphertext theft" where an attacker swaps encrypted data between contexts.

#### `Encryption::decrypt(string $payload, string $key): string`

Decrypt a raw payload. Throws `\RuntimeException` on tampered or wrong-key payloads; throws `\InvalidArgumentException` on malformed input.

```php
$plaintext = Encryption::decrypt($encryptedPayload, $key);
```

#### `Encryption::encryptWithPassphrase(string $data, string $passphrase): string`

Encrypt directly with a passphrase. Internally derives the key and packs the salt into the payload.

```php
$encrypted = Encryption::encryptWithPassphrase('config_secret', 'app-encryption-key');
// Returns: hex-encoded (salt || base64_payload) — ready for DB or config storage

$plaintext = Encryption::decryptWithPassphrase($encrypted, 'app-encryption-key');
```

**Format:** `hex( salt[32] || nonce[12] || ciphertext[n] || tag[16] )`  
Total length: 80 + n * 4/3 hex chars (varies with plaintext size)

**Use case:** Storing sensitive values in config files or database columns where the key is not available as a binary object.

### Token Generation

#### `Encryption::token(int $bytes = 32): string`

Generate a hex-encoded secure random token.

```php
$tokenId = Encryption::token();    // 64 hex chars (256 bits of entropy)
$apiKey  = Encryption::token(64);  // 128 hex chars (512 bits of entropy)
```

#### `Encryption::urlToken(int $bytes = 32): string`

Generate a URL-safe base64 token with no padding. Suitable for URLs, form fields, and headers.

```php
$token = Encryption::urlToken();    // e.g., "abc123XYZ_def..." (no =, +, or /)
```

### HMAC Utilities

#### `Encryption::hmac(string $data, string $key): string`

Generate an HMAC-SHA256 for data integrity verification.

```php
$hmac = Encryption::hmac('payload', $secretKey);  // 64 hex chars
```

#### `Encryption::hashEquals(string $expected, string $actual): bool`

Timing-safe comparison of two hex-encoded hashes. Prevents timing attacks.

```php
if (Encryption::hashEquals($storedHmac, $computedHmac)) {
    // verified — safe to proceed
}
```

## Usage Examples

### Encrypting PII for database storage

```php
// Derive key from app configuration (store passphrase in environment, not code)
$passphrase = getenv('ENCRYPTION_PASSPHRASE');
$encrypted = Encryption::encryptWithPassphrase($ssn, $passphrase);

// Store $encrypted in the database as a hex string

// Decrypt later:
$plaintext = Encryption::decryptWithPassphrase($encrypted, $passphrase);
```

### Encrypting with context binding (AAD)

```php
// Bind encrypted data to a specific user/resource
$userKey = Encryption::generateKey();
$encrypted = Encryption::encryptWithAad(
    json_encode(['creditCard' => '4111-1111-1111-1111']),
    $userKey,
    "user:{$userId}"  // AAD binds this ciphertext to user ID
);

// If the ciphertext is stolen and used for a different user, decryption fails.
```

### Generating API keys

```php
$apiKey = 'api-' . Encryption::urlToken(32);
// e.g., "api-abc123XYZ_def456GHI"
```

## Security Notes

1. **Never reuse nonces** — GCM with the same nonce + key pair is vulnerable to nonce-reuse attacks that completely break confidentiality. This service handles nonce generation automatically; never pass a fixed nonce.

2. **Use different keys per purpose** — Don't use the same encryption key for both PII storage and API key generation. Derive separate keys using `deriveKey()` with different passphrase strings.

3. **Keep passphrases secret** — `encryptWithPassphrase()` is convenient but depends entirely on the strength of the passphrase. Use strong, high-entropy passphrases (at least 20 characters).

4. **100k PBKDF2 iterations** — Default key derivation uses 100k iterations for brute-force resistance. Increase this over time as hardware improves.

5. **AES-256-GCM is AEAD** — The authentication tag guarantees that any tampered ciphertext is detected. Don't verify encryption results with additional signatures unless you need third-party verifiability (e.g., sending encrypted data to external parties).

## Constants

All constants are class-level and accessible via `Encryption::CONSTANT_NAME`:

| Constant | Value | Purpose |
|---|---|---|
| `CIPHER` | `'aes-256-gcm'` | OpenSSL cipher identifier |
| `SALT_LENGTH` | 32 | Salt size in bytes for key derivation |
| `NONCE_LENGTH` | 12 | GCM nonce size in bytes |
| `TAG_LENGTH` | 16 | GCM authentication tag size in bytes |
| `KDF_ITERATIONS` | 100000 | PBKDF2 iteration count |
| `KDF_ALGO` | `'sha256'` | Hash algorithm for PBKDF2 |
