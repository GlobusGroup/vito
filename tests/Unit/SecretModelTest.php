<?php

namespace Tests\Unit;

use App\Models\Secret;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecretModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set a default secrets lifetime for testing
        config(['app.secrets_lifetime' => 60]); // 60 minutes
    }

    /** @test */
    public function it_sets_default_expiry_time_when_not_provided()
    {
        // Create a secret without specifying expires_at
        $secret = Secret::create([
            'encrypted_content' => 'test-content',
            'requires_password' => false,
            // expires_at is intentionally not set
        ]);

        // Verify that expires_at was automatically set
        $this->assertNotNull($secret->expires_at);
        
        // Verify it's set to the configured lifetime (60 minutes from now)
        $expectedExpiry = now()->addMinutes(60);
        $this->assertTrue($secret->expires_at->diffInSeconds($expectedExpiry) < 2); // Allow 2 second tolerance
    }

    /** @test */
    public function it_does_not_override_provided_expiry_time()
    {
        $customExpiry = now()->addHours(2);
        
        // Create a secret with a specific expires_at
        $secret = Secret::create([
            'encrypted_content' => 'test-content',
            'requires_password' => false,
            'expires_at' => $customExpiry,
        ]);

        // Verify that the provided expires_at was not overridden
        $this->assertEquals($customExpiry->format('Y-m-d H:i:s'), $secret->expires_at->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_generates_uuid_for_id()
    {
        $secret = Secret::create([
            'encrypted_content' => 'test-content',
            'requires_password' => false,
        ]);

        // Verify that an ID was generated
        $this->assertNotNull($secret->id);
        
        // Verify it's a valid UUID format
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $secret->id);
    }

    /** @test */
    public function it_correctly_identifies_expired_secrets()
    {
        // Create an expired secret
        $expiredSecret = Secret::create([
            'encrypted_content' => 'test-content',
            'requires_password' => false,
            'expires_at' => now()->subMinutes(1), // 1 minute ago
        ]);

        $this->assertTrue($expiredSecret->isExpired());
    }

    /** @test */
    public function it_correctly_identifies_non_expired_secrets()
    {
        // Create a non-expired secret
        $validSecret = Secret::create([
            'encrypted_content' => 'test-content',
            'requires_password' => false,
            'expires_at' => now()->addMinutes(30), // 30 minutes from now
        ]);

        $this->assertFalse($validSecret->isExpired());
    }

    /** @test */
    public function it_uses_different_config_values_for_default_expiry()
    {
        // Change the config value
        config(['app.secrets_lifetime' => 120]); // 2 hours

        $secret = Secret::create([
            'encrypted_content' => 'test-content',
            'requires_password' => false,
        ]);

        // Verify it uses the new config value
        $expectedExpiry = now()->addMinutes(120);
        $this->assertTrue($secret->expires_at->diffInSeconds($expectedExpiry) < 2); // Allow 2 second tolerance
    }
} 