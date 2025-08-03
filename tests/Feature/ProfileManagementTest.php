<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\FeaturePrototypeSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(FeaturePrototypeSeeder::class);
});

test('authenticated user can list their contexts', function () {
    $arda = User::where('email', 'arda@university.com')->first();
    Sanctum::actingAs($arda);

    $response = $this->getJson('/api/profiles');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'contexts' => [
                '*' => [
                    'id', 'slug', 'name', 'description', 'is_default', 'is_active',
                    'attributes_count', 'created_at', 'updated_at',
                ],
            ],
        ]);
});

test('authenticated user can create a new context', function () {
    $arda = User::where('email', 'arda@university.com')->first();
    Sanctum::actingAs($arda);

    $contextData = [
        'slug' => 'professional',
        'name' => 'Professional',
        'description' => 'Professional identity for business use',
        'is_active' => true,
    ];

    $response = $this->postJson('/api/profiles', $contextData);

    $response->assertStatus(201)
        ->assertJson([
            'message' => 'Context created successfully',
            'context' => [
                'slug' => 'professional',
                'name' => 'Professional',
                'description' => 'Professional identity for business use',
                'is_active' => true,
            ],
        ]);

    $this->assertDatabaseHas('contexts', [
        'user_id' => $arda->id,
        'slug' => 'professional',
        'name' => 'Professional',
    ]);
});

test('user cannot create context with duplicate slug', function () {
    $arda = User::where('email', 'arda@university.com')->first();
    Sanctum::actingAs($arda);

    $contextData = [
        'slug' => 'university', // Already exists in seeder
        'name' => 'University Duplicate',
    ];

    $response = $this->postJson('/api/profiles', $contextData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['slug']);
});

test('authenticated user can view specific context with attributes', function () {
    $arda = User::where('email', 'arda@university.com')->first();
    $context = $arda->contexts()->where('slug', 'university')->first();
    Sanctum::actingAs($arda);

    $response = $this->getJson("/api/profiles/{$context->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'context' => [
                'id', 'slug', 'name', 'description', 'is_default', 'is_active',
                'attributes' => [
                    '*' => [
                        'id', 'attribute' => ['id', 'key_name', 'display_name', 'data_type'],
                        'value', 'visibility',
                    ],
                ],
            ],
        ]);
});

test('user cannot view another users context', function () {
    $arda = User::where('email', 'arda@university.com')->first();
    $elif = User::where('email', 'elif.kaya@hospital.com')->first();
    $elifContext = $elif->contexts()->first();

    Sanctum::actingAs($arda);

    $response = $this->getJson("/api/profiles/{$elifContext->id}");

    $response->assertStatus(404)
        ->assertJson(['message' => 'Context not found']);
});

test('authenticated user can update their context', function () {
    $arda = User::where('email', 'arda@university.com')->first();
    $context = $arda->contexts()->where('slug', 'gaming')->first();
    Sanctum::actingAs($arda);

    $updateData = [
        'name' => 'Updated Gaming Profile',
        'description' => 'Updated description for gaming',
    ];

    $response = $this->putJson("/api/profiles/{$context->id}", $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Context updated successfully',
            'context' => [
                'name' => 'Updated Gaming Profile',
            ],
        ]);

    $this->assertDatabaseHas('contexts', [
        'id' => $context->id,
        'name' => 'Updated Gaming Profile',
    ]);
});

test('authenticated user can delete non-default context', function () {
    $arda = User::where('email', 'arda@university.com')->first();
    $context = $arda->contexts()->where('slug', 'gaming')->first();
    Sanctum::actingAs($arda);

    $response = $this->deleteJson("/api/profiles/{$context->id}");

    $response->assertStatus(200)
        ->assertJson(['message' => 'Context deleted successfully']);

    $this->assertDatabaseMissing('contexts', ['id' => $context->id]);
});

test('user cannot delete default context', function () {
    $arda = User::where('email', 'arda@university.com')->first();
    $defaultContext = $arda->contexts()->where('is_default', true)->first();
    Sanctum::actingAs($arda);

    $response = $this->deleteJson("/api/profiles/{$defaultContext->id}");

    $response->assertStatus(422)
        ->assertJson(['message' => 'Cannot delete default context']);
});

test('authenticated user can add attribute to context', function () {
    $arda = User::where('email', 'arda@university.com')->first();
    $context = $arda->contexts()->where('slug', 'gaming')->first();
    Sanctum::actingAs($arda);

    $attributeData = [
        'key_name' => 'favorite_game',
        'display_name' => 'Favorite Game',
        'data_type' => 'string',
        'value' => 'League of Legends',
        'visibility' => 'public',
    ];

    $response = $this->postJson("/api/profiles/{$context->id}/attributes", $attributeData);

    $response->assertStatus(201)
        ->assertJson([
            'message' => 'Attribute added successfully',
            'attribute' => [
                'attribute' => [
                    'key_name' => 'favorite_game',
                    'display_name' => 'Favorite Game',
                ],
                'value' => 'League of Legends',
                'visibility' => 'public',
            ],
        ]);
});

test('authenticated user can update attribute value', function () {
    $arda = User::where('email', 'arda@university.com')->first();
    $context = $arda->contexts()->where('slug', 'gaming')->first();
    $profileValue = $context->profileValues()->first();
    Sanctum::actingAs($arda);

    $updateData = [
        'value' => 'Updated value',
        'visibility' => 'protected',
    ];

    $response = $this->putJson("/api/profiles/{$context->id}/attributes/{$profileValue->id}", $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Attribute updated successfully',
            'attribute' => [
                'value' => 'Updated value',
                'visibility' => 'protected',
            ],
        ]);
});

test('authenticated user can delete attribute from context', function () {
    $arda = User::where('email', 'arda@university.com')->first();
    $context = $arda->contexts()->where('slug', 'gaming')->first();
    $profileValue = $context->profileValues()->first();
    Sanctum::actingAs($arda);

    $response = $this->deleteJson("/api/profiles/{$context->id}/attributes/{$profileValue->id}");

    $response->assertStatus(200)
        ->assertJson(['message' => 'Attribute deleted successfully']);

    $this->assertDatabaseMissing('context_profile_values', ['id' => $profileValue->id]);
});

test('unauthenticated user cannot access profile management endpoints', function () {
    $this->getJson('/api/profiles')->assertStatus(401);
    $this->postJson('/api/profiles', [])->assertStatus(401);
});
