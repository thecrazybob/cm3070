<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileViewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\FeaturePrototypeSeeder::class);
    }

    /**
     * Scenario 1: The Profile Owner
     * Authenticated user views their own private profile and sees all data.
     */
    public function test_profile_owner_can_view_their_own_private_profile(): void
    {
        $arda = User::where('email', 'arda@university.com')->first();
        Sanctum::actingAs($arda);

        $response = $this->getJson("/api/view/profile/{$arda->id}?context=university");

        $response->assertStatus(200)
            ->assertJson([
                'full_name' => 'Arda Yılmaz',
                'email' => 'arda@university.com',
                'student_id' => '12345678',
            ]);
    }

    /**
     * Scenario 2: Public, Unauthenticated Access
     * Unauthenticated visitor views a public profile and sees only public data.
     */
    public function test_public_can_view_public_profile_data(): void
    {
        $arda = User::where('email', 'arda@university.com')->first();

        $response = $this->getJson("/api/view/profile/{$arda->id}?context=gaming");

        $response->assertStatus(200)
            ->assertJson([
                'username' => 'ArdaPlays',
                'bio' => 'Top-tier streamer and pro gamer.',
            ])
            ->assertJsonMissing(['full_name']); // Should not include the 'protected' full_name
    }

    /**
     * Scenario 3: Unauthorised Access Attempt
     * Authenticated user attempts to view another user's private profile.
     */
    public function test_authenticated_user_cannot_view_another_users_private_profile(): void
    {
        $arda = User::where('email', 'arda@university.com')->first();
        $elif = User::where('email', 'elif.kaya@hospital.com')->first();
        Sanctum::actingAs($elif);

        $response = $this->getJson("/api/view/profile/{$arda->id}?context=university");

        $response->assertStatus(403);
    }

    /**
     * Test that a non-existent context returns a 404.
     */
    public function test_accessing_non_existent_context_returns_not_found(): void
    {
        $arda = User::where('email', 'arda@university.com')->first();

        $response = $this->getJson("/api/view/profile/{$arda->id}?context=non-existent-context");

        $response->assertStatus(404);
    }
}
