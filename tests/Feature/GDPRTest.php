<?php

namespace Tests\Feature;

use App\Models\AccessLog;
use App\Models\User;
use Database\Seeders\FeaturePrototypeSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->seed(FeaturePrototypeSeeder::class);
});

test('authenticated user can export their data', function () {
    $arda = User::where('email', 'arda@university.com')->first();
    Sanctum::actingAs($arda);

    $response = $this->getJson('/api/export-data');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'data' => [
                'export_info' => ['exported_at', 'user_id', 'format_version'],
                'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
                'contexts' => [
                    '*' => [
                        'id', 'slug', 'name', 'description', 'is_default', 'is_active',
                        'profile_values' => [
                            '*' => [
                                'id', 'attribute', 'value', 'visibility', 'created_at', 'updated_at'
                            ]
                        ]
                    ]
                ],
                'access_logs',
                'statistics' => ['total_contexts', 'total_profile_values', 'total_access_logs', 'account_age_days'],
            ]
        ])
        ->assertJson([
            'message' => 'Data export generated successfully',
            'data' => [
                'user' => [
                    'id' => $arda->id,
                    'email' => 'arda@university.com',
                ],
            ],
        ]);
});

test('authenticated user can view their audit log', function () {
    $arda = User::where('email', 'arda@university.com')->first();
    
    // Create some access logs
    AccessLog::create([
        'user_id' => $arda->id,
        'context_requested' => 'university',
        'accessor_type' => 'owner',
        'accessor_id' => $arda->id,
        'attributes_returned' => ['full_name', 'email'],
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test User Agent',
        'response_code' => 200,
    ]);
    
    Sanctum::actingAs($arda);

    $response = $this->getJson('/api/audit-log');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'access_logs' => [
                '*' => [
                    'created_at',
                    'context_requested',
                    'accessor_type',
                    'attributes_returned',
                    'ip_address',
                    'accessor_id',
                    'response_code',
                ]
            ],
            'total_logs',
        ]);
});

test('authenticated user can get GDPR compliance information', function () {
    $arda = User::where('email', 'arda@university.com')->first();
    Sanctum::actingAs($arda);

    $response = $this->getJson('/api/gdpr-info');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'gdpr_rights' => [
                'right_to_access',
                'right_to_portability',
                'right_to_erasure',
                'right_to_rectification',
            ],
            'data_processing' => [
                'purposes',
                'legal_basis',
                'retention_period',
                'data_categories',
            ],
            'user_statistics' => [
                'account_created',
                'contexts_count',
                'total_profile_attributes',
                'access_logs_count',
            ],
        ]);
});

test('authenticated user can delete their account with valid password', function () {
    $arda = User::where('email', 'arda@university.com')->first();
    $ardaId = $arda->id;
    Sanctum::actingAs($arda);

    $deleteData = [
        'password' => 'password', // From seeder
        'confirmation' => true,
    ];

    $response = $this->deleteJson('/api/delete-account', $deleteData);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'deleted_data' => ['contexts', 'profile_values', 'access_logs'],
            'deleted_at',
        ])
        ->assertJson([
            'message' => 'Account deleted successfully',
        ]);

    // Verify user is deleted
    $this->assertDatabaseMissing('users', ['id' => $ardaId]);
    
    // Verify related data is deleted
    $this->assertDatabaseMissing('contexts', ['user_id' => $ardaId]);
    $this->assertDatabaseMissing('context_profile_values', ['user_id' => $ardaId]);
});

test('user cannot delete account with wrong password', function () {
    $arda = User::where('email', 'arda@university.com')->first();
    Sanctum::actingAs($arda);

    $deleteData = [
        'password' => 'wrong-password',
        'confirmation' => true,
    ];

    $response = $this->deleteJson('/api/delete-account', $deleteData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);

    // Verify user still exists
    $this->assertDatabaseHas('users', ['id' => $arda->id]);
});

test('user cannot delete account without confirmation', function () {
    $arda = User::where('email', 'arda@university.com')->first();
    Sanctum::actingAs($arda);

    $deleteData = [
        'password' => 'password',
        'confirmation' => false,
    ];

    $response = $this->deleteJson('/api/delete-account', $deleteData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['confirmation']);
});

test('unauthenticated user cannot access GDPR endpoints', function () {
    $this->getJson('/api/export-data')->assertStatus(401);
    $this->getJson('/api/audit-log')->assertStatus(401);
    $this->getJson('/api/gdpr-info')->assertStatus(401);
    $this->deleteJson('/api/delete-account')->assertStatus(401);
});