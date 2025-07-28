<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\FeaturePrototypeSeeder;
use Laravel\Sanctum\Sanctum;
use function Pest\Stressless\stress;

beforeEach(function () {
    $this->seed(FeaturePrototypeSeeder::class);
});

test('profile owner can view their own private profile', function () {
    $arda = User::where('email', 'arda@university.com')->first();
    Sanctum::actingAs($arda);
    $response = $this->getJson("/api/view/profile/{$arda->id}?context=university");
    $response->assertStatus(200)->assertJson(['full_name' => 'Arda Yılmaz', 'email' => 'arda@university.com', 'student_id' => '12345678']);
});

test('public can view public profile data', function () {
    $arda = User::where('email', 'arda@university.com')->first();
    $response = $this->getJson("/api/view/profile/{$arda->id}?context=gaming");
    $response->assertStatus(200)->assertJson(['username' => 'ArdaPlays', 'bio' => 'Top-tier streamer and pro gamer.'])->assertJsonMissing(['full_name']);
});

test('authenticated user cannot view another users private profile', function () {
    $arda = User::where('email', 'arda@university.com')->first();
    $elif = User::where('email', 'elif.kaya@hospital.com')->first();
    Sanctum::actingAs($elif);
    $response = $this->getJson("/api/view/profile/{$arda->id}?context=university");
    $response->assertStatus(403);
});

test('accessing non existent context returns not found', function () {
    $arda = User::where('email', 'arda@university.com')->first();
    $response = $this->getJson("/api/view/profile/{$arda->id}?context=non-existent-context");
    $response->assertStatus(404);
});

test('profile retrieval performs under load', function () {
    $result = stress("api/view/profile/1?context=work")->concurrently(10)->for(5)->seconds();
    expect($result->requests()->duration()->avg())->toBeLessThan(100);
});
