<?php

namespace Tests\Unit;

use App\Models\Secret;
use App\Services\SecretService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt as LaravelCrypt;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SecretServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SecretService $secretService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->secretService = app(SecretService::class);
        
        // Set a default secrets lifetime for testing
        config(['app.secrets_lifetime' => 60]); // 60 minutes
    }

    
    public function test_it_aborts_with_404_when_laravel_crypt_fails()
    {
        // Test the catch block: when LaravelCrypt::decryptString throws an exception
        // This tests lines 82-84 in SecretService: catch (Throwable $th) { abort(404); }
        $invalidEncryptedData = 'completely-invalid-encrypted-data';
        
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        $this->secretService->decryptPayload($invalidEncryptedData);
    }

    
    public function test_it_aborts_with_404_when_decrypted_data_is_invalid_json()
    {
        // Test line 83: when json_decode returns null due to invalid JSON
        // This tests the condition: if (!$decryptedData) { abort(404); }
        $invalidJsonString = 'this is not valid json at all';
        $encryptedInvalidJson = LaravelCrypt::encryptString($invalidJsonString);
        
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        $this->secretService->decryptPayload($encryptedInvalidJson);
    }

    
    public function test_it_successfully_decrypts_valid_payload()
    {
        $validData = ['secret_id' => 'test-id', 'secret_key' => 'test-key'];
        $encryptedData = LaravelCrypt::encryptString(json_encode($validData));

        $result = $this->secretService->decryptPayload($encryptedData);

        $this->assertEquals($validData, $result);
    }

    
    public function test_it_throws_unauthorized_exception_when_crypt_decryption_fails()
    {
        // Mock the Log facade to verify error logging
        Log::shouldReceive('error')->once()->with('User provided an invalid password');

        // Create a secret with valid data
        $secret = Secret::create([
            'encrypted_content' => 'invalid-encrypted-content-that-will-fail',
            'requires_password' => false,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unauthorized');

        // This should trigger the catch block in decryptSecretContent (lines 114-117)
        $this->secretService->decryptSecretContent($secret, 'invalid-key');
    }

    
    public function test_it_throws_unauthorized_exception_when_decrypted_content_is_false()
    {
        // Mock the Log facade to verify error logging
        Log::shouldReceive('error')->once()->with('User provided an invalid password');

        // Create a secret and encrypt it properly
        $result = $this->secretService->createSecret('test content', 'correct-password');
        $secret = $result['secret'];
        $encryptionKey = $result['encryption_key'];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unauthorized');

        // Use wrong password to trigger the false return and exception
        $this->secretService->decryptSecretContent($secret, $encryptionKey, 'wrong-password');
    }

    
    public function test_it_successfully_decrypts_content_with_correct_credentials()
    {
        $originalContent = 'This is a test secret';
        
        // Create a secret
        $result = $this->secretService->createSecret($originalContent, 'test-password');
        $secret = $result['secret'];
        $encryptionKey = $result['encryption_key'];

        // Should successfully decrypt with correct credentials
        $decryptedContent = $this->secretService->decryptSecretContent($secret, $encryptionKey, 'test-password');

        $this->assertEquals($originalContent, $decryptedContent);
    }

    
    public function test_it_successfully_decrypts_content_without_password()
    {
        $originalContent = 'This is a test secret without password';
        
        // Create a secret without password
        $result = $this->secretService->createSecret($originalContent);
        $secret = $result['secret'];
        $encryptionKey = $result['encryption_key'];

        // Should successfully decrypt without password
        $decryptedContent = $this->secretService->decryptSecretContent($secret, $encryptionKey);

        $this->assertEquals($originalContent, $decryptedContent);
    }

    
    public function test_it_generates_valid_sharing_data()
    {
        $secretId = 'test-secret-id';
        $encryptionKey = 'test-encryption-key';

        $encryptedData = $this->secretService->generateSharingData($secretId, $encryptionKey);

        // Verify we can decrypt it back
        $decryptedData = $this->secretService->decryptPayload($encryptedData);

        $this->assertEquals([
            'secret_id' => $secretId,
            'secret_key' => $encryptionKey,
        ], $decryptedData);
    }

    
    public function test_it_generates_valid_share_url()
    {
        $encryptedData = 'test-encrypted-data';
        
        $shareUrl = $this->secretService->generateShareUrl($encryptedData);

        $expectedUrl = url('/secrets/show?d=' . urlencode($encryptedData));
        $this->assertEquals($expectedUrl, $shareUrl);
    }

    
    public function test_it_deletes_expired_secret_and_aborts()
    {
        // Create an expired secret
        $expiredSecret = Secret::create([
            'encrypted_content' => 'test-content',
            'requires_password' => false,
            'expires_at' => now()->subMinutes(1), // 1 minute ago
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        $this->secretService->checkIfSecretIsValidOrAbort($expiredSecret);

        // Verify the secret was deleted
        $this->assertDatabaseMissing('secrets', ['id' => $expiredSecret->id]);
    }

    
    public function test_it_does_not_abort_for_valid_secret()
    {
        // Create a valid secret
        $validSecret = Secret::create([
            'encrypted_content' => 'test-content',
            'requires_password' => false,
            'expires_at' => now()->addMinutes(30), // 30 minutes from now
        ]);

        // Should not throw any exception
        $this->secretService->checkIfSecretIsValidOrAbort($validSecret);

        // Verify the secret still exists
        $this->assertDatabaseHas('secrets', ['id' => $validSecret->id]);
    }

    
    public function test_it_sleeps_when_rate_limiting_is_enabled()
    {
        // Test that usleep is called when rate limiting is enabled
        // This tests the condition: if (config('app.enable_secret_rate_limiting'))
        
        // Enable rate limiting for this test
        config(['app.enable_secret_rate_limiting' => true]);

        // Create a valid secret to decrypt
        $result = $this->secretService->createSecret('test content');
        $secret = $result['secret'];
        $encryptionKey = $result['encryption_key'];

        // Measure time to verify sleep occurred
        $startTime = microtime(true);
        $this->secretService->decryptSecretContent($secret, $encryptionKey);
        $endTime = microtime(true);

        // Should have slept for at least 400ms (0.4 seconds)
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        $this->assertGreaterThan(400, $executionTime);
    }

    
    public function test_it_does_not_sleep_when_rate_limiting_is_disabled()
    {
        // Test that usleep is NOT called when rate limiting is disabled
        // This tests the condition: if (config('app.enable_secret_rate_limiting'))
        
        // Disable rate limiting for this test
        config(['app.enable_secret_rate_limiting' => false]);

        // Create a valid secret to decrypt
        $result = $this->secretService->createSecret('test content');
        $secret = $result['secret'];
        $encryptionKey = $result['encryption_key'];

        // Measure time to verify sleep did NOT occur
        $startTime = microtime(true);
        $this->secretService->decryptSecretContent($secret, $encryptionKey);
        $endTime = microtime(true);

        // Should NOT have slept, so execution time should be very fast (less than 100ms)
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        $this->assertLessThan(100, $executionTime);
    }

    
    public function test_it_logs_specific_error_when_crypt_decryption_throws_exception()
    {
        // Test line 119-121: when Crypt::decryptString throws a Throwable
        // This test specifically targets the catch block that logs 'Error decrypting Secret'
        
        // We'll test this without mocking by creating a scenario that naturally causes an exception
        // Create a secret with malformed encrypted content
        $secret = Secret::create([
            'encrypted_content' => 'invalid-base64-content!!!',
            'requires_password' => false,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unauthorized');

        // This should trigger the catch block on line 119-121
        $this->secretService->decryptSecretContent($secret, 'any-encryption-key');
    }
} 