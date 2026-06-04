<?php

namespace Tests\Unit;

use LaswitchTech\Core\Encryption;
use PHPUnit\Framework\TestCase;

class EncryptionTest extends TestCase
{
    // --------------------------------------------------------------------
    // Key Generation
    // --------------------------------------------------------------------

    public function testGenerateKeyReturns32Bytes(): void
    {
        $key = Encryption::generateKey();
        $this->assertEquals(32, strlen($key));
    }

    public function testGenerateKeyCustomLength(): void
    {
        $key = Encryption::generateKey(64);
        $this->assertEquals(64, strlen($key));
    }

    public function testGenerateKeyProducesUniqueKeys(): void
    {
        $keys = [];
        for ($i = 0; $i < 10; $i++) {
            $keys[] = Encryption::generateKey();
        }
        $this->assertEquals(10, count(array_unique($keys)));
    }

    // --------------------------------------------------------------------
    // Key Derivation
    // --------------------------------------------------------------------

    public function testDeriveKeyReturnsKeyAndSalt(): void
    {
        $result = Encryption::deriveKey('test-passphrase');
        $this->assertArrayHasKey('key', $result);
        $this->assertArrayHasKey('salt', $result);
        $this->assertEquals(32, strlen($result['key']));
        $this->assertEquals(64, strlen($result['salt']));
    }

    public function testDeriveKeyDeterministicWithSameSalt(): void
    {
        $salt = bin2hex(random_bytes(32));
        $r1 = Encryption::deriveKey('passphrase', $salt);
        $r2 = Encryption::deriveKey('passphrase', $salt);
        $this->assertEquals($r1['key'], $r2['key']);
        $this->assertEquals($r1['salt'], $r2['salt']);
    }

    public function testDeriveKeyProducesDifferentSaltWhenNoneProvided(): void
    {
        $s1 = Encryption::deriveKey('passphrase')['salt'];
        $s2 = Encryption::deriveKey('passphrase')['salt'];
        $this->assertNotEquals($s1, $s2);
    }

    public function testDeriveKeyFromPassphraseReturnsHex(): void
    {
        $hex = Encryption::deriveKeyFromPassphrase('test');
        $this->assertEquals(64, strlen($hex));
        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $hex);
    }

    // --------------------------------------------------------------------
    // Encrypt / Decrypt (raw key)
    // --------------------------------------------------------------------

    public function testEncryptDecryptRoundTrip(): void
    {
        $key = Encryption::generateKey();
        $plaintext = 'Sensitive PII data: 123-45-6789';
        $encrypted = Encryption::encrypt($plaintext, $key);
        $decrypted = Encryption::decrypt($encrypted, $key);
        $this->assertEquals($plaintext, $decrypted);
    }

    public function testEncryptDecryptRoundTripEmpty(): void
    {
        $key = Encryption::generateKey();
        $plaintext = '';
        $encrypted = Encryption::encrypt($plaintext, $key);
        $decrypted = Encryption::decrypt($encrypted, $key);
        $this->assertEquals($plaintext, $decrypted);
    }

    public function testEncryptDecryptRoundTripLargeData(): void
    {
        $key = Encryption::generateKey();
        $plaintext = str_repeat('A', 10000);
        $encrypted = Encryption::encrypt($plaintext, $key);
        $decrypted = Encryption::decrypt($encrypted, $key);
        $this->assertEquals($plaintext, $decrypted);
    }

    public function testEncryptDecryptRoundTripUnicode(): void
    {
        $key = Encryption::generateKey();
        $plaintext = 'Données sensibles: 日本語 🇨🇦 émojis: ñ';
        $encrypted = Encryption::encrypt($plaintext, $key);
        $decrypted = Encryption::decrypt($encrypted, $key);
        $this->assertEquals($plaintext, $decrypted);
    }

    public function testEncryptProducesDifferentPayloads(): void
    {
        $key = Encryption::generateKey();
        $plaintext = 'same data';
        $e1 = Encryption::encrypt($plaintext, $key);
        $e2 = Encryption::encrypt($plaintext, $key);
        // Different nonces → different ciphertexts (probabilistically certain)
        $this->assertNotEquals($e1, $e2);
    }

    public function testDecryptFailsWithWrongKey(): void
    {
        $key1 = Encryption::generateKey();
        $key2 = Encryption::generateKey();
        $plaintext = 'secret';
        $encrypted = Encryption::encrypt($plaintext, $key1);
        $this->expectException(\RuntimeException::class);
        Encryption::decrypt($encrypted, $key2);
    }

    public function testDecryptRejectsShortPayload(): void
    {
        $key = Encryption::generateKey();
        $this->expectException(\InvalidArgumentException::class);
        Encryption::decrypt('dGVzdA==', $key); // base64("test") — too short
    }

    // --------------------------------------------------------------------
    // Encrypt / Decrypt with AAD
    // --------------------------------------------------------------------

    public function testEncryptDecryptWithAadRoundTrip(): void
    {
        $key = Encryption::generateKey();
        $aad = 'resource:invoice:1234';
        $plaintext = 'confidential amount: $5000';
        $encrypted = Encryption::encryptWithAad($plaintext, $key, $aad);
        $decrypted = Encryption::decryptWithAad($encrypted, $key, $aad);
        $this->assertEquals($plaintext, $decrypted);
    }

    public function testDecryptWithWrongAadFails(): void
    {
        $key = Encryption::generateKey();
        $aad1 = 'resource:invoice';
        $aad2 = 'resource:other';
        $plaintext = 'secret';
        $encrypted = Encryption::encryptWithAad($plaintext, $key, $aad1);
        $this->expectException(\RuntimeException::class);
        Encryption::decryptWithAad($encrypted, $key, $aad2);
    }

    // --------------------------------------------------------------------
    // Passphrase-based methods
    // --------------------------------------------------------------------

    public function testEncryptDecryptPassphraseRoundTrip(): void
    {
        $passphrase = 'my-super-secret-config-key';
        $plaintext = 'db_password=super_secret_123';
        $encrypted = Encryption::encryptWithPassphrase($plaintext, $passphrase);
        $decrypted = Encryption::decryptWithPassphrase($encrypted, $passphrase);
        $this->assertEquals($plaintext, $decrypted);
    }

    public function testEncryptDecryptPassphraseDifferentKeyFails(): void
    {
        $passphrase1 = 'correct-key';
        $passphrase2 = 'wrong-key';
        $plaintext = 'secret data';
        $encrypted = Encryption::encryptWithPassphrase($plaintext, $passphrase1);
        $this->expectException(\RuntimeException::class);
        Encryption::decryptWithPassphrase($encrypted, $passphrase2);
    }

    public function testEncryptPassphrasePayloadFormat(): void
    {
        $encrypted = Encryption::encryptWithPassphrase('data', 'pass');
        // Hex-encoded — check format
        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $encrypted);
        // Salt is first 32 bytes (64 hex chars)
        $raw = hex2bin($encrypted);
        $this->assertTrue(strlen($raw) >= Encryption::SALT_LENGTH + Encryption::NONCE_LENGTH + Encryption::TAG_LENGTH);
    }

    // --------------------------------------------------------------------
    // Token generation
    // --------------------------------------------------------------------

    public function testTokenReturnsHex(): void
    {
        $token = Encryption::token();
        $this->assertEquals(64, strlen($token));
        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $token);
    }

    public function testTokenCustomLength(): void
    {
        $token = Encryption::token(16);
        $this->assertEquals(32, strlen($token));
    }

    public function testTokensAreUnique(): void
    {
        $tokens = [];
        for ($i = 0; $i < 100; $i++) {
            $tokens[] = Encryption::token();
        }
        $this->assertEquals(100, count(array_unique($tokens)));
    }

    public function testUrlTokenReturnsUrlSafeString(): void
    {
        $token = Encryption::urlToken();
        // URL-safe base64: no padding (=), no +, no / (they are replaced with - and _)
        $this->assertStringNotContainsString('=', $token);
        $this->assertStringNotContainsString('+', $token);
        $this->assertStringNotContainsString('/', $token);
        // Correct length (32 bytes → ceil(32*4/3) = 44 chars, minus padding stripped)
        // 32 bytes → ceil(32/3)*4 = 44 base64 chars, minus 1 padding char = 43
        $this->assertEquals(43, strlen($token));
    }

    public function testUrlTokenCanBeReconstituted(): void
    {
        // URL-safe base64 should be pad-able back to standard base64
        $urlToken = Encryption::urlToken();
        $padded = str_pad(strtr($urlToken, '-_', '+/'), strlen($urlToken) % 4, '=', STR_PAD_RIGHT);
        $decoded = base64_decode($padded);
        $this->assertNotFalse($decoded);
        $this->assertEquals(32, strlen($decoded));
    }

    // --------------------------------------------------------------------
    // HMAC
    // --------------------------------------------------------------------

    public function testHmacReturnsHex(): void
    {
        $key = Encryption::generateKey();
        $hmac = Encryption::hmac('data', $key);
        $this->assertEquals(64, strlen($hmac));
        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $hmac);
    }

    public function testHmacDeterministic(): void
    {
        $key = Encryption::generateKey();
        $h1 = Encryption::hmac('data', $key);
        $h2 = Encryption::hmac('data', $key);
        $this->assertEquals($h1, $h2);
    }

    public function testHmacDifferentData(): void
    {
        $key = Encryption::generateKey();
        $h1 = Encryption::hmac('data1', $key);
        $h2 = Encryption::hmac('data2', $key);
        $this->assertNotEquals($h1, $h2);
    }

    public function testHashEqualsTrue(): void
    {
        $key = Encryption::generateKey();
        $hmac = Encryption::hmac('data', $key);
        $this->assertTrue(Encryption::hashEquals($hmac, $hmac));
    }

    public function testHashEqualsFalse(): void
    {
        $key1 = Encryption::generateKey();
        $key2 = Encryption::generateKey();
        $h1 = Encryption::hmac('data', $key1);
        $h2 = Encryption::hmac('data', $key2);
        $this->assertFalse(Encryption::hashEquals($h1, $h2));
    }

    public function testHashEqualsTimingSafe(): void
    {
        // hash_equals should reject strings of same length quickly
        // but in a timing-safe manner — just verify behavior, not actual timing
        $sameLen = str_repeat('a', 64);
        $differentLen = 'short';
        $this->assertFalse(Encryption::hashEquals($sameLen, $differentLen));
    }
}
