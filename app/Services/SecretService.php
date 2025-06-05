<?php

namespace App\Services;

use App\Crypt;
use App\Models\Secret;
use Illuminate\Support\Facades\Crypt as LaravelCrypt;
use Illuminate\Support\Facades\DB;
use Throwable;

class SecretNotFoundException extends \Exception {}

class SecretService
{
    /**
     * Common validation rules for secret content and password
     */
    public static function getCommonValidationRules(): array
    {
        return [
            'content' => 'required|string|max:200000',
            'password' => 'nullable|string|max:100',
        ];
    }

    /**
     * Create a secret with encrypted content
     */
    public function createSecret(string $content, ?string $password = null, ?int $expiresInMinutes = null): array
    {
        $encryptionKey = bin2hex(random_bytes(32));

        $encryptedContent = Crypt::encryptString(
            $content,
            $encryptionKey,
            $password ?? Crypt::DEFAULT_PASSWORD
        );

        // Calculate expiry time
        $expiresInMinutes = $expiresInMinutes ?? (int) config('app.secrets_lifetime');
        $expiresAt = now()->addMinutes($expiresInMinutes);

        $secret = Secret::create([
            'encrypted_content' => $encryptedContent,
            'requires_password' => !is_null($password),
            'expires_at' => $expiresAt,
        ]);

        return [
            'secret' => $secret,
            'encryption_key' => $encryptionKey,
            'expires_in_minutes' => $expiresInMinutes,
        ];
    }

    /**
     * Generate encrypted sharing data
     */
    public function generateSharingData(string $secretId, string $encryptionKey): string
    {
        $data = json_encode(['secret_id' => $secretId, 'secret_key' => $encryptionKey]);
        return LaravelCrypt::encryptString($data);
    }

    /**
     * Generate the full sharing URL
     */
    public function generateShareUrl(string $encryptedData): string
    {
        return url('/secrets/show?d=' . urlencode($encryptedData));
    }

    /**
     * Decrypt payload from URL parameter
     */
    public function decryptPayload(string $payload): array
    {
        try {
            $decryptedData = LaravelCrypt::decryptString($payload);
            $decryptedData = json_decode($decryptedData, true);
        } catch (Throwable $th) {
            abort(404);
        }

        if (!$decryptedData) {
            abort(404);
        }
        
        return $decryptedData;
    }

    /**
     * Check if secret is valid or abort
     */
    public function checkIfSecretIsValidOrAbort(Secret $secret): void
    {
        if ($secret->isExpired()) {
            $secret->delete();
            abort(404);
        }
    }

    /**
     * Decrypt secret content with race condition protection
     * 
     * This method uses database transactions with row locking to ensure
     * single-use enforcement, preventing race conditions where multiple
     * simultaneous requests could access the same secret.
     */
    public function decryptSecretContent(Secret $secret, string $encryptionKey, ?string $password = null): string
    {
        // First check if the secret is expired and delete it immediately if so
        // We do this outside the main transaction to ensure deletion happens
        if ($secret->isExpired()) {
            $secret->delete();
            throw new SecretNotFoundException('Secret not found or already accessed');
        }

        return DB::transaction(function () use ($secret, $encryptionKey, $password) {
            // Lock the record for this transaction to prevent race conditions
            $lockedSecret = Secret::where('id', $secret->id)->lockForUpdate()->first();
            if (!$lockedSecret) {
                throw new SecretNotFoundException('Secret not found or already accessed');
            }

            // Double-check if the locked secret is expired (race condition protection)
            if ($lockedSecret->isExpired()) {
                $lockedSecret->delete();
                throw new SecretNotFoundException('Secret not found or already accessed');
            }

            // Slow down decryption to prevent brute force attacks
            if (config('app.enable_secret_rate_limiting')) {
                usleep(random_int(400_000, 600_000));
            }

            try {
                $decryptedContent = Crypt::decryptString(
                    $lockedSecret->encrypted_content,
                    $encryptionKey,
                    $password ?? Crypt::DEFAULT_PASSWORD
                );
            } catch (Throwable $th) {
                app('log')->error('Error decrypting Secret');
                throw new \Exception('Unauthorized');
            }

            if ($decryptedContent === false) {
                app('log')->error('User provided an invalid password');
                throw new \Exception('Unauthorized');
            }

            // Delete immediately after successful decryption to ensure single-use
            $lockedSecret->delete();

            return $decryptedContent;
        });
    }
} 