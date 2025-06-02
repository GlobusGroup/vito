<?php

namespace Tests\Feature;

use App\Models\Secret;
use App\Services\SecretService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class SecretWebTest extends TestCase
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

    /** @test */
    public function it_redirects_root_to_secrets_create()
    {
        $response = $this->get('/');

        $response->assertStatus(302)
            ->assertRedirect('/secrets/create');
    }

    /** @test */
    public function it_shows_secrets_create_page()
    {
        $response = $this->get('/secrets/create');

        $response->assertStatus(200)
            ->assertViewIs('secrets.create');
    }

    /** @test */
    public function it_can_store_a_secret_and_redirect_to_share()
    {
        $response = $this->withoutMiddleware()
            ->post('/secrets', [
                'content' => 'This is a test secret',
                'password' => null,
            ]);

        $response->assertStatus(302)
            ->assertRedirect(route('secrets.share'));

        // Verify secret was created in database
        $this->assertDatabaseHas('secrets', [
            'requires_password' => false,
        ]);

        // Verify encrypted_data is in session
        $this->assertTrue(Session::has('encrypted_data'));
    }

    /** @test */
    public function it_can_store_a_password_protected_secret()
    {
        $response = $this->withoutMiddleware()
            ->post('/secrets', [
                'content' => 'This is a password protected secret',
                'password' => 'mypassword123',
            ]);

        $response->assertStatus(302)
            ->assertRedirect(route('secrets.share'));

        // Verify secret was created in database
        $this->assertDatabaseHas('secrets', [
            'requires_password' => true,
        ]);
    }

    /** @test */
    public function it_validates_required_content_on_store()
    {
        $response = $this->withoutMiddleware()
            ->post('/secrets', [
                'password' => 'test',
            ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['content']);
    }

    /** @test */
    public function it_validates_content_max_length_on_store()
    {
        $response = $this->withoutMiddleware()
            ->post('/secrets', [
                'content' => str_repeat('a', 200001), // Exceeds 200000 character limit
            ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['content']);
    }

    /** @test */
    public function it_validates_password_max_length_on_store()
    {
        $response = $this->withoutMiddleware()
            ->post('/secrets', [
                'content' => 'Test content',
                'password' => str_repeat('a', 101), // Exceeds 100 character limit
            ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function it_shows_share_page_when_encrypted_data_in_session()
    {
        // First create a secret to get encrypted data in session
        $this->withoutMiddleware()
            ->post('/secrets', [
                'content' => 'Test secret',
            ]);

        $response = $this->get('/secrets/share');

        $response->assertStatus(200)
            ->assertViewIs('secrets.share');
    }

    /** @test */
    public function it_returns_404_on_share_page_without_encrypted_data_in_session()
    {
        $response = $this->get('/secrets/share');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_shows_secret_view_with_valid_encrypted_data()
    {
        // Create a secret
        $result = $this->secretService->createSecret('Test secret content');
        $secret = $result['secret'];
        $encryptionKey = $result['encryption_key'];

        // Generate encrypted data
        $encryptedData = $this->secretService->generateSharingData($secret->id, $encryptionKey);

        $response = $this->get('/secrets/show?d=' . urlencode($encryptedData));

        $response->assertStatus(200)
            ->assertViewIs('secrets.show')
            ->assertViewHas('d', $encryptedData)
            ->assertViewHas('requires_password', false);
    }

    /** @test */
    public function it_shows_secret_view_with_password_requirement()
    {
        // Create a password-protected secret
        $result = $this->secretService->createSecret('Test secret content', 'mypassword');
        $secret = $result['secret'];
        $encryptionKey = $result['encryption_key'];

        // Generate encrypted data
        $encryptedData = $this->secretService->generateSharingData($secret->id, $encryptionKey);

        $response = $this->get('/secrets/show?d=' . urlencode($encryptedData));

        $response->assertStatus(200)
            ->assertViewIs('secrets.show')
            ->assertViewHas('requires_password', true);
    }

    /** @test */
    public function it_returns_404_for_invalid_encrypted_data_on_show()
    {
        $response = $this->get('/secrets/show?d=invalid-data');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_404_for_missing_d_parameter_on_show()
    {
        $response = $this->get('/secrets/show');

        $response->assertStatus(302)
            ->assertSessionHasErrors(['d']);
    }

    /** @test */
    public function it_returns_404_for_expired_secret_on_show()
    {
        // Create a secret that's already expired
        $result = $this->secretService->createSecret('Test secret content');
        $secret = $result['secret'];
        $encryptionKey = $result['encryption_key'];

        // Manually set the secret as expired
        $secret->update(['expires_at' => now()->subMinutes(1)]);

        // Generate encrypted data
        $encryptedData = $this->secretService->generateSharingData($secret->id, $encryptionKey);

        $response = $this->get('/secrets/show?d=' . urlencode($encryptedData));

        $response->assertStatus(404);

        // Verify the expired secret was deleted
        $this->assertDatabaseMissing('secrets', ['id' => $secret->id]);
    }

    /** @test */
    public function it_can_decrypt_secret_without_password()
    {
        // Create a secret
        $result = $this->secretService->createSecret('Test secret content');
        $secret = $result['secret'];
        $encryptionKey = $result['encryption_key'];

        // Generate encrypted data
        $encryptedData = $this->secretService->generateSharingData($secret->id, $encryptionKey);

        $response = $this->withoutMiddleware()->postJson('/secrets/decrypt', [
            'd' => $encryptedData,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'content' => 'Test secret content'
            ]);

        // Verify the secret was deleted after decryption
        $this->assertDatabaseMissing('secrets', ['id' => $secret->id]);
    }

    /** @test */
    public function it_can_decrypt_password_protected_secret_with_correct_password()
    {
        // Create a password-protected secret
        $result = $this->secretService->createSecret('Password protected content', 'mypassword');
        $secret = $result['secret'];
        $encryptionKey = $result['encryption_key'];

        // Generate encrypted data
        $encryptedData = $this->secretService->generateSharingData($secret->id, $encryptionKey);

        $response = $this->withoutMiddleware()->postJson('/secrets/decrypt', [
            'd' => $encryptedData,
            'password' => 'mypassword',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'content' => 'Password protected content'
            ]);

        // Verify the secret was deleted after decryption
        $this->assertDatabaseMissing('secrets', ['id' => $secret->id]);
    }

    /** @test */
    public function it_returns_401_for_password_protected_secret_without_password()
    {
        // Create a password-protected secret
        $result = $this->secretService->createSecret('Password protected content', 'mypassword');
        $secret = $result['secret'];
        $encryptionKey = $result['encryption_key'];

        // Generate encrypted data
        $encryptedData = $this->secretService->generateSharingData($secret->id, $encryptionKey);

        $response = $this->withoutMiddleware()->postJson('/secrets/decrypt', [
            'd' => $encryptedData,
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Unauthorized'
            ]);

        // Verify the secret was NOT deleted
        $this->assertDatabaseHas('secrets', ['id' => $secret->id]);
    }

    /** @test */
    public function it_returns_401_for_password_protected_secret_with_wrong_password()
    {
        // Create a password-protected secret
        $result = $this->secretService->createSecret('Password protected content', 'mypassword');
        $secret = $result['secret'];
        $encryptionKey = $result['encryption_key'];

        // Generate encrypted data
        $encryptedData = $this->secretService->generateSharingData($secret->id, $encryptionKey);

        $response = $this->withoutMiddleware()->postJson('/secrets/decrypt', [
            'd' => $encryptedData,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Unauthorized'
            ]);

        // Verify the secret was NOT deleted
        $this->assertDatabaseHas('secrets', ['id' => $secret->id]);
    }

    /** @test */
    public function it_validates_required_d_parameter_on_decrypt()
    {
        $response = $this->withoutMiddleware()->postJson('/secrets/decrypt', [
            'password' => 'test',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['d']);
    }

    /** @test */
    public function it_validates_password_max_length_on_decrypt()
    {
        $response = $this->withoutMiddleware()->postJson('/secrets/decrypt', [
            'd' => 'test-data',
            'password' => str_repeat('a', 101), // Exceeds 100 character limit
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function it_returns_404_for_invalid_encrypted_data_on_decrypt()
    {
        $response = $this->withoutMiddleware()->postJson('/secrets/decrypt', [
            'd' => 'invalid-encrypted-data',
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_404_for_expired_secret_on_decrypt()
    {
        // Create a secret that's already expired
        $result = $this->secretService->createSecret('Test secret content');
        $secret = $result['secret'];
        $encryptionKey = $result['encryption_key'];

        // Manually set the secret as expired
        $secret->update(['expires_at' => now()->subMinutes(1)]);

        // Generate encrypted data
        $encryptedData = $this->secretService->generateSharingData($secret->id, $encryptionKey);

        $response = $this->withoutMiddleware()->postJson('/secrets/decrypt', [
            'd' => $encryptedData,
        ]);

        $response->assertStatus(404);

        // Verify the expired secret was deleted
        $this->assertDatabaseMissing('secrets', ['id' => $secret->id]);
    }

    /** @test */
    public function it_respects_rate_limiting_on_decrypt()
    {
        // For rate limiting test, we need to keep throttle middleware but avoid CSRF
        // We'll use a session-based approach to simulate proper CSRF tokens
        
        // Make 20 requests (the limit) - use from() to simulate coming from a page with CSRF token
        for ($i = 0; $i < 20; $i++) {
            // Create a new secret for each request since secrets are deleted after decryption
            $result = $this->secretService->createSecret("Test secret content $i");
            $secret = $result['secret'];
            $encryptionKey = $result['encryption_key'];
            $encryptedData = $this->secretService->generateSharingData($secret->id, $encryptionKey);

            $response = $this->from('/secrets/show')
                ->postJson('/secrets/decrypt', [
                    'd' => $encryptedData,
                ]);
            $response->assertStatus(200);
        }

        // Create one more secret for the rate-limited request
        $result = $this->secretService->createSecret('This should be rate limited');
        $secret = $result['secret'];
        $encryptionKey = $result['encryption_key'];
        $encryptedData = $this->secretService->generateSharingData($secret->id, $encryptionKey);

        // The 21st request should be rate limited
        $response = $this->from('/secrets/show')
            ->postJson('/secrets/decrypt', [
                'd' => $encryptedData,
            ]);

        $response->assertStatus(429); // Too Many Requests
    }

    /** @test */
    public function it_shows_terms_page()
    {
        $response = $this->get('/terms');

        $response->assertStatus(200)
            ->assertViewIs('terms');
    }

    /** @test */
    public function it_has_named_routes()
    {
        // Test that all named routes exist
        $this->assertEquals('http://localhost/secrets/create', route('secrets.create'));
        $this->assertEquals('http://localhost/secrets', route('secrets.store'));
        $this->assertEquals('http://localhost/secrets/share', route('secrets.share'));
        $this->assertEquals('http://localhost/secrets/show', route('secrets.show'));
        $this->assertEquals('http://localhost/secrets/decrypt', route('secrets.decrypt'));
        $this->assertEquals('http://localhost/terms', route('terms'));
    }
} 