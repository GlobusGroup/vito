<?php

namespace Tests\Unit;

use App\Crypt;
use Illuminate\Support\Str;
use Tests\TestCase;

class CryptTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_crypt_string(): void
    {
        $encryptionKey = Str::uuid();
        $encryptedContent = Crypt::encryptString('test', $encryptionKey, Crypt::DEFAULT_PASSWORD);
        $decryptedContent = Crypt::decryptString($encryptedContent, $encryptionKey, Crypt::DEFAULT_PASSWORD);
        $this->assertEquals('test', $decryptedContent);
    }

    public function test_crypt_string_with_password(): void
    {
        $encryptionKey = Str::uuid();

        $encryptedContent = Crypt::encryptString('test', $encryptionKey, 'password');
        $decryptedContent = Crypt::decryptString($encryptedContent, $encryptionKey, 'password');
        $this->assertEquals('test', $decryptedContent);
    }

    public function test_crypt_string_with_wrong_password(): void
    {
        $encryptionKey = Str::uuid();

        $encryptedContent = Crypt::encryptString('test', $encryptionKey, 'password');
        $decryptedContent = Crypt::decryptString($encryptedContent, $encryptionKey, 'wrong');
        $this->assertFalse($decryptedContent);
    }
}
