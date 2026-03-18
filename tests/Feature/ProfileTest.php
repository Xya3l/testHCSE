<?php

namespace Tests\Feature;

use App\Enums\ProfileStatus;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_public_endpoint_returns_only_active_profiles(): void
    {
        Profile::factory()->active()->count(3)->create();
        Profile::factory()->inactive()->count(2)->create();
        Profile::factory()->pending()->count(2)->create();

        $response = $this->getJson('/api/profiles');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');

        $profiles = $response->json('data');
        foreach ($profiles as $profile) {
            $this->assertArrayNotHasKey('status', $profile);
        }
    }

    public function test_public_endpoint_does_not_return_status_field(): void
    {
        Profile::factory()->active()->create();

        $response = $this->getJson('/api/profiles');

        $response->assertStatus(200);
        $profile = $response->json('data.0');

        $this->assertArrayNotHasKey('status', $profile);

        $this->assertArrayHasKey('id', $profile);
        $this->assertArrayHasKey('last_name', $profile);
        $this->assertArrayHasKey('first_name', $profile);
        $this->assertArrayHasKey('picture', $profile);
    }

    public function test_authenticated_user_can_see_status_field(): void
    {
        $user = User::factory()->create();
        Profile::factory()->active()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/profiles');

        $response->assertStatus(200);
        $profile = $response->json('data.0');

        // Le statut DOIT être présent pour utilisateur authentifié
        $this->assertArrayHasKey('status', $profile);
        $this->assertEquals('active', $profile['status']);
    }


    public function test_unauthenticated_user_cannot_create_profile(): void
    {
        $file = UploadedFile::fake()->image('profile.jpg');

        $response = $this->postJson('/api/profiles', [
            'last_name' => 'Doe',
            'first_name' => 'John',
            'picture' => $file,
            'status' => ProfileStatus::ACTIVE->value,
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_create_profile(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('profile.jpg');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/profiles', [
                'last_name' => 'Doe',
                'first_name' => 'John',
                'picture' => $file,
                'status' => ProfileStatus::ACTIVE->value,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'last_name',
                    'first_name',
                    'picture',
                    'status',
                    'created_at',
                    'updated_at',
                ]
            ]);

        // Vérifier en DB
        $this->assertDatabaseHas('profiles', [
            'last_name' => 'Doe',
            'first_name' => 'John',
            'status' => ProfileStatus::ACTIVE->value,
        ]);

        $profileId = $response->json('data.id');
        $picturePath = $response->json('data.picture');
        $this->assertNotNull($picturePath);
    }

    public function test_profile_creation_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/profiles', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['last_name', 'first_name', 'picture', 'status']);
    }

    public function test_only_image_files_are_accepted(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/profiles', [
                'last_name' => 'Doe',
                'first_name' => 'John',
                'picture' => $file,
                'status' => ProfileStatus::ACTIVE->value,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['picture']);
    }

    public function test_unauthenticated_user_cannot_update_profile(): void
    {
        $profile = Profile::factory()->create();

        $response = $this->putJson("/api/profiles/{$profile->id}", [
            'last_name' => 'Smith',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_update_profile(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create([
            'last_name' => 'Doe',
            'first_name' => 'John',
            'status' => ProfileStatus::INACTIVE,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/profiles/{$profile->id}", [
                'last_name' => 'Smith',
                'status' => ProfileStatus::ACTIVE->value,
            ]);

        $response->assertStatus(200);

        // Vérifier en DB
        $this->assertDatabaseHas('profiles', [
            'id' => $profile->id,
            'last_name' => 'Smith',
            'first_name' => 'John', // Inchangé
            'status' => ProfileStatus::ACTIVE->value,
        ]);
    }

    public function test_partial_update_only_changes_provided_fields(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create([
            'last_name' => 'Doe',
            'first_name' => 'John',
            'status' => ProfileStatus::INACTIVE,
        ]);

        // Update uniquement first_name
        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/profiles/{$profile->id}", [
                'first_name' => 'Jane',
            ]);

        $response->assertStatus(200);

        // Vérifier que seul first_name a changé
        $this->assertDatabaseHas('profiles', [
            'id' => $profile->id,
            'last_name' => 'Doe', // INCHANGÉ
            'first_name' => 'Jane', // MODIFIÉ
            'status' => ProfileStatus::INACTIVE->value, // INCHANGÉ
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_profile(): void
    {
        $profile = Profile::factory()->create();

        $response = $this->deleteJson("/api/profiles/{$profile->id}");

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_delete_profile(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/profiles/{$profile->id}");

        $response->assertStatus(204);

        // Vérifier suppression en DB
        $this->assertDatabaseMissing('profiles', [
            'id' => $profile->id,
        ]);
    }

    public function test_invalid_status_is_rejected(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('profile.jpg');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/profiles', [
                'last_name' => 'Doe',
                'first_name' => 'John',
                'picture' => $file,
                'status' => 'invalid_status',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_update_profile_with_new_image(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create();
        $newFile = UploadedFile::fake()->image('new_profile.jpg');

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/profiles/{$profile->id}", [
                'picture' => $newFile,
            ]);

        $response->assertStatus(200);

        $updatedProfile = Profile::find($profile->id);
        $this->assertNotEquals($profile->picture, $updatedProfile->picture);
    }
}
