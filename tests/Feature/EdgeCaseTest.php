<?php

declare(strict_types=1);

use App\Models\Context;
use App\Models\ContextProfileValue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->context = Context::create([
        'user_id' => $this->user->id,
        'slug' => 'test-context',
        'name' => 'Test Context',
        'is_default' => true,
        'is_active' => true,
    ]);
});

test('rejects attribute values over 1000 characters', function () {
    $this->actingAs($this->user);

    $tooLongValue = str_repeat('a', 1001);

    $response = $this->postJson("/api/contexts/{$this->context->id}/attributes", [
        'key_name' => 'bio',
        'value' => $tooLongValue,
        'visibility' => 'public',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['value']);
});

test('handles special characters in attribute values', function () {
    $this->actingAs($this->user);

    $specialValue = "Test 'quotes' \"double\" & symbols";

    $response = $this->postJson("/api/contexts/{$this->context->id}/attributes", [
        'key_name' => 'description',
        'value' => $specialValue,
        'visibility' => 'public',
    ]);

    $response->assertStatus(201);
    $stored = ContextProfileValue::where('context_id', $this->context->id)->first();
    expect($stored->value)->toBe($specialValue);
});

test('prevents sql injection in context slug', function () {
    $this->actingAs($this->user);

    $maliciousSlug = "test'; DROP TABLE users; --";

    $response = $this->postJson('/api/contexts', [
        'name' => 'Test Context',
        'slug' => $maliciousSlug,
        'description' => 'Test',
    ]);

    // Should fail validation for invalid slug format
    $response->assertStatus(422);
    $this->assertDatabaseMissing('contexts', [
        'slug' => $maliciousSlug,
    ]);
});

test('handles missing default context gracefully', function () {
    $userWithoutDefault = User::factory()->create();

    $response = $this->getJson("/api/view/profile/{$userWithoutDefault->id}");

    $response->assertStatus(200);
    $response->assertJsonPath('error', true);
    $response->assertJsonPath('message', 'This user has no default context configured.');
});

test('validates context slug uniqueness per user', function () {
    $this->actingAs($this->user);

    // Create first context
    $this->postJson('/api/contexts', [
        'name' => 'First',
        'slug' => 'duplicate-test',
        'description' => 'First context',
    ])->assertStatus(201);

    // Try to create another with same slug
    $response = $this->postJson('/api/contexts', [
        'name' => 'Second',
        'slug' => 'duplicate-test',
        'description' => 'Should fail',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['slug']);
});

test('rejects empty string values', function () {
    $this->actingAs($this->user);

    $response = $this->postJson("/api/contexts/{$this->context->id}/attributes", [
        'key_name' => 'empty_test',
        'value' => '',
        'visibility' => 'public',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['value']);
});

test('validates attribute visibility values', function () {
    $this->actingAs($this->user);

    $response = $this->postJson("/api/contexts/{$this->context->id}/attributes", [
        'key_name' => 'test',
        'value' => 'test value',
        'visibility' => 'invalid', // Invalid visibility
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['visibility']);
});

test('security headers are present in API responses', function () {
    $response = $this->getJson('/api/user');

    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('X-XSS-Protection', '1; mode=block');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

test('correctly logs access attempts', function () {
    $accessor = User::factory()->create();

    // Access another user's profile
    $this->actingAs($accessor);
    $response = $this->getJson("/api/view/profile/{$this->user->id}");

    $response->assertStatus(200);

    // Check access log was created
    $this->assertDatabaseHas('access_logs', [
        'user_id' => $this->user->id,
        'accessor_type' => 'user',
        'accessor_id' => $accessor->id,
        'response_code' => 200,
    ]);
});

test('cannot access protected endpoints without authentication', function () {
    $response = $this->getJson('/api/contexts');
    $response->assertStatus(401);

    $response = $this->postJson('/api/contexts', [
        'name' => 'Test',
        'slug' => 'test',
    ]);
    $response->assertStatus(401);

    $response = $this->deleteJson("/api/contexts/{$this->context->id}");
    $response->assertStatus(401);
});
