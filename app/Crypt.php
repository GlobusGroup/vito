<?php

namespace App;

class Crypt
{
    const DEFAULT_PASSWORD = 'no_password_specified';

    public static function encryptString(string $plaintext, string $encryptionKey, string $password)
    {
        $salt = random_bytes(16);
        $iv = random_bytes(16);
        $key = self::deriveKey($encryptionKey, $salt, $password);
        $encrypted = openssl_encrypt($plaintext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        $mac = hash_hmac('sha256', $salt . $iv . $encrypted, $key, true);

        return base64_encode($salt . $iv . $mac . $encrypted);
    }

    public static function decryptString(string $encrypted, string $encryptionKey, string $password)
    {
        $data = base64_decode($encrypted);
        $salt = substr($data, 0, 16);
        $iv = substr($data, 16, 16);
        $mac = substr($data, 32, 32);
        $ciphertext = substr($data, 64);
        $key = self::deriveKey($encryptionKey, $salt, $password);
        $calculated_mac = hash_hmac('sha256', $salt . $iv . $ciphertext, $key, true);

        if (!hash_equals($mac, $calculated_mac)) {
            return false;
        }

        return openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    }

    protected static function deriveKey($password, $salt, $additionalPassword)
    {
        $combined = $password . $additionalPassword;
        return hash_pbkdf2('sha256', $combined, $salt, 100000, 32, true);
    }
}
