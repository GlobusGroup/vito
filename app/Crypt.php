<?php

namespace App;

class Crypt
{
    protected static function deriveKey($password, $salt)
    {
        return hash_pbkdf2('sha256', $password, $salt, 100000, 32, true);
    }

    public static function encryptString(string $plaintext, string $password)
    {
        $method = 'aes-256-cbc';
        $salt = random_bytes(16);
        $iv = random_bytes(16);
        $key = self::deriveKey($password, $salt);
        $encrypted = openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($salt . $iv . $encrypted);
    }

    public static function decryptString(string $encrypted, string $password)
    {
        $method = 'aes-256-cbc';
        $data = base64_decode($encrypted);
        $salt = substr($data, 0, 16);
        $iv = substr($data, 16, 16);
        $ciphertext = substr($data, 32);
        $key = self::deriveKey($password, $salt);
        return openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
    }
}
