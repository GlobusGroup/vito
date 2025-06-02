<?php

namespace Tests\Feature;

use App\Models\Secret;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecretApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set a default secrets lifetime for testing
        config(['app.secrets_lifetime' => 60]); // 60 minutes
    }

    
    public function test_it_can_create_a_secret_via_api()
    {
        $response = $this->postJson('/api/v1/secrets', [
            'content' => 'This is a test secret',
            'password' => null,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'share_url',
                    'expires_at',
                    'expires_on',
                    'expires_in_minutes',
                    'requires_password',
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'requires_password' => false,
                    'expires_in_minutes' => 60,
                ]
            ]);

        // Verify secret was created in database (we can't check by ID since it's not returned)
        $this->assertDatabaseHas('secrets', [
            'requires_password' => false,
        ]);
    }

    
    public function test_it_can_create_a_password_protected_secret_via_api()
    {
        $response = $this->postJson('/api/v1/secrets', [
            'content' => 'This is a password protected secret',
            'password' => 'mypassword123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'share_url',
                    'expires_at',
                    'expires_on',
                    'expires_in_minutes',
                    'requires_password',
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'requires_password' => true,
                    'expires_in_minutes' => 60,
                ]
            ]);

        // Verify secret was created in database
        $this->assertDatabaseHas('secrets', [
            'requires_password' => true,
        ]);
    }

    
    public function test_it_can_create_a_secret_with_custom_expiry_via_api()
    {
        $customExpiryMinutes = 120;

        $response = $this->postJson('/api/v1/secrets', [
            'content' => 'This secret expires in 2 hours',
            'expires_in_minutes' => $customExpiryMinutes,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'expires_in_minutes' => $customExpiryMinutes,
                ]
            ]);
    }

    
    public function test_it_validates_required_content_field()
    {
        $response = $this->postJson('/api/v1/secrets', [
            'password' => 'test',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    
    public function test_it_validates_content_max_length()
    {
        $response = $this->postJson('/api/v1/secrets', [
            'content' => str_repeat('a', 200001), // Exceeds 200000 character limit
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    
    public function test_it_validates_password_max_length()
    {
        $response = $this->postJson('/api/v1/secrets', [
            'content' => 'Test content',
            'password' => str_repeat('a', 101), // Exceeds 100 character limit
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    
    public function test_it_validates_expires_in_minutes_is_integer()
    {
        $response = $this->postJson('/api/v1/secrets', [
            'content' => 'Test content',
            'expires_in_minutes' => 'not-an-integer',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['expires_in_minutes']);
    }

    
    public function test_it_validates_expires_in_minutes_minimum_value()
    {
        $response = $this->postJson('/api/v1/secrets', [
            'content' => 'Test content',
            'expires_in_minutes' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['expires_in_minutes']);
    }

    
    public function test_it_validates_expires_in_minutes_maximum_value()
    {
        $response = $this->postJson('/api/v1/secrets', [
            'content' => 'Test content',
            'expires_in_minutes' => (24 * 60 * 5) + 1, // Exceeds 5 days limit
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['expires_in_minutes']);
    }

    
    public function test_it_respects_rate_limiting()
    {
        // Make 10 requests (the limit)
        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson('/api/v1/secrets', [
                'content' => "Test content $i",
            ]);
            $response->assertStatus(201);
        }

        // The 11th request should be rate limited
        $response = $this->postJson('/api/v1/secrets', [
            'content' => 'This should be rate limited',
        ]);

        $response->assertStatus(429); // Too Many Requests
    }

    
    public function test_it_generates_valid_share_url()
    {
        $response = $this->postJson('/api/v1/secrets', [
            'content' => 'Test secret for URL validation',
        ]);

        $response->assertStatus(201);
        
        $shareUrl = $response->json('data.share_url');
        $this->assertStringStartsWith(url('/secrets/show?d='), $shareUrl);
        
        // Verify the URL contains encrypted data parameter
        $parsedUrl = parse_url($shareUrl);
        parse_str($parsedUrl['query'], $queryParams);
        $this->assertArrayHasKey('d', $queryParams);
        $this->assertNotEmpty($queryParams['d']);
    }

    
    public function test_it_returns_proper_expires_at_format()
    {
        $response = $this->postJson('/api/v1/secrets', [
            'content' => 'Test secret for date format',
        ]);

        $response->assertStatus(201);
        
        $expiresAt = $response->json('data.expires_at');
        
        // Verify it's a valid ISO 8601 format (Laravel uses microseconds, not milliseconds)
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/', $expiresAt);
        
        // Verify it's a future date
        $expiresAtCarbon = \Carbon\Carbon::parse($expiresAt);
        $this->assertTrue($expiresAtCarbon->isFuture());
    }
} 