<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\FeaturePrototypeSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(FeaturePrototypeSeeder::class);
});

test('user can register with valid data', function () {
    $userData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response = $this->postJson('/api/register', $userData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'user' => ['id', 'name', 'email'],
            'token',
        ])
        ->assertJson([
            'message' => 'User registered successfully',
            'user' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
});

test('user cannot register with invalid email', function () {
    $userData = [
        'name' => 'John Doe',
        'email' => 'invalid-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response = $this->postJson('/api/register', $userData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('user cannot register with duplicate email', function () {
    $userData = [
        'name' => 'Duplicate User',
        'email' => 'arda@university.com', // Already exists in seeder
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response = $this->postJson('/api/register', $userData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('user can login with valid credentials', function () {
    $loginData = [
        'email' => 'arda@university.com',
        'password' => 'password',
    ];

    $response = $this->postJson('/api/login', $loginData);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'user' => ['id', 'name', 'email'],
            'token',
        ])
        ->assertJson([
            'message' => 'Login successful',
            'user' => [
                'email' => 'arda@university.com',
            ],
        ]);
});

test('user cannot login with invalid credentials', function () {
    $loginData = [
        'email' => 'arda@university.com',
        'password' => 'wrong-password',
    ];

    $response = $this->postJson('/api/login', $loginData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('authenticated user can logout', function () {
    $user = User::where('email', 'arda@university.com')->first();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/logout');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Logged out successfully']);

    // Verify token is revoked by checking token count
    expect($user->fresh()->tokens()->count())->toBe(0);
});

test('authenticated user can get their information', function () {
    $user = User::where('email', 'arda@university.com')->first();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/me');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
        ])
        ->assertJson([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
});

test('unauthenticated user cannot access protected routes', function () {
    $this->getJson('/api/me')->assertStatus(401);
    $this->postJson('/api/logout')->assertStatus(401);
});
