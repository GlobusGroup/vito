<?php

namespace App\Services;

use App\Crypt;
use App\Models\Secret;
use Illuminate\Support\Facades\Crypt as LaravelCrypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Request;
use Throwable;

class SecretNotFoundException extends \Exception {}
class TooManyAttemptsException extends \Exception {}

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
     * Check rate limits for secret decryption
     * 
     * @param Secret $secret
     * @throws TooManyAttemptsException
     */
    private function checkRateLimits(Secret $secret): void
    {
        if (!config('app.enable_secret_rate_limiting')) {
            return;
        }

        $ip = Request::ip();
        $secretKey = 'decrypt:secret:' . $secret->id;
        $ipKey = 'decrypt:ip:' . $ip;

        // Per-secret rate limiting: 5 attempts per secret
        if (RateLimiter::tooManyAttempts($secretKey, config('app.secret_rate_limit_attempts', 5))) {
            throw new TooManyAttemptsException('Too many attempts for this secret');
        }

        // Per-IP rate limiting: 20 attempts per hour
        if (RateLimiter::tooManyAttempts($ipKey, config('app.ip_rate_limit_attempts', 20))) {
            throw new TooManyAttemptsException('Too many attempts from this IP address');
        }

        // Increment counters
        RateLimiter::hit($secretKey, config('app.secret_rate_limit_decay', 60)); // 1 hour decay
        RateLimiter::hit($ipKey, config('app.ip_rate_limit_decay', 3600)); // 1 hour decay
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

            // Check rate limits before attempting decryption
            $this->checkRateLimits($lockedSecret);

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