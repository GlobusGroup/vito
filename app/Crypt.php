<?php

namespace App;

class Crypt
{
    protected static function deriveKey($password, $salt, $additionalPassword = '')
    {
        $combined = $password . $additionalPassword;
        return hash_pbkdf2('sha256', $combined, $salt, 100000, 32, true);
    }

    public static function encryptString(string $plaintext, string $encryptionKey, string $password = '')
    {
        $method = 'aes-256-cbc';
        $salt = random_bytes(16);
        $iv = random_bytes(16);
        $key = self::deriveKey($encryptionKey, $salt, $password);
        $encrypted = openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($salt . $iv . $encrypted);
    }

    public static function decryptString(string $encrypted, string $encryptionKey, string $password = '')
    {
        $method = 'aes-256-cbc';
        $data = base64_decode($encrypted);
        $salt = substr($data, 0, 16);
        $iv = substr($data, 16, 16);
        $ciphertext = substr($data, 32);
        $key = self::deriveKey($encryptionKey, $salt, $password);
        return openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
    }
}
