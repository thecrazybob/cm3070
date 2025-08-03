<?php

namespace Database\Seeders;

use App\Models\Context;
use App\Models\ContextProfileValue;
use App\Models\ProfileAttribute;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FeaturePrototypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Profile Attributes
        $attributes = [
            ['key_name' => 'full_name', 'display_name' => 'Full Name', 'data_type' => 'string', 'is_system' => true],
            ['key_name' => 'email', 'display_name' => 'Email Address', 'data_type' => 'email', 'is_system' => true],
            ['key_name' => 'username', 'display_name' => 'Username', 'data_type' => 'string'],
            ['key_name' => 'bio', 'display_name' => 'Biography', 'data_type' => 'text'],
            ['key_name' => 'profile_picture', 'display_name' => 'Profile Picture URL', 'data_type' => 'url'],
            ['key_name' => 'student_id', 'display_name' => 'Student ID', 'data_type' => 'string'],
            ['key_name' => 'legal_name', 'display_name' => 'Legal Name', 'data_type' => 'string'],
            ['key_name' => 'professional_name', 'display_name' => 'Professional Name', 'data_type' => 'string'],
            ['key_name' => 'job_title', 'display_name' => 'Job Title', 'data_type' => 'string'],
        ];

        foreach ($attributes as $attr) {
            ProfileAttribute::create($attr);
        }
        $this->command->info('Profile attributes created.');

        // 2. Create Users (Arda and Elif)
        $arda = User::create([
            'name' => 'Arda',
            'email' => 'arda@university.com',
            'password' => Hash::make('password'),
        ]);

        $elif = User::create([
            'name' => 'Elif',
            'email' => 'elif.kaya@hospital.com',
            'password' => Hash::make('password'),
        ]);
        $this->command->info('Users Arda and Elif created.');

        // 3. Create Contexts for Arda
        $ardaUniversity = Context::create(['user_id' => $arda->id, 'slug' => 'university', 'name' => 'University Life', 'is_default' => true]);
        $ardaGaming = Context::create(['user_id' => $arda->id, 'slug' => 'gaming', 'name' => 'Public Gaming']);

        // 4. Create Contexts for Elif
        $elifWork = Context::create(['user_id' => $elif->id, 'slug' => 'work', 'name' => 'Professional Work', 'is_default' => true]);
        $elifFormal = Context::create(['user_id' => $elif->id, 'slug' => 'formal', 'name' => 'Formal/Legal']);
        $this->command->info('Contexts for users created.');

        // 5. Populate Profile Values for Arda
        $this->createProfileValue($arda, $ardaUniversity, 'full_name', 'Arda Yılmaz', 'private');
        $this->createProfileValue($arda, $ardaUniversity, 'email', 'arda@university.com', 'private');
        $this->createProfileValue($arda, $ardaUniversity, 'student_id', '12345678', 'private');

        $this->createProfileValue($arda, $ardaGaming, 'username', 'ArdaPlays', 'public');
        $this->createProfileValue($arda, $ardaGaming, 'bio', 'Top-tier streamer and pro gamer.', 'public');
        $this->createProfileValue($arda, $ardaGaming, 'full_name', 'Arda Y.', 'protected'); // Protected example

        // 6. Populate Profile Values for Elif
        $this->createProfileValue($elif, $elifWork, 'professional_name', 'Dr. Elif Aydın', 'public');
        $this->createProfileValue($elif, $elifWork, 'job_title', 'Cardiologist', 'public');
        $this->createProfileValue($elif, $elifWork, 'email', 'e.aydin@hospital.com', 'protected');

        $this->createProfileValue($elif, $elifFormal, 'legal_name', 'Dr. Elif Kaya', 'private');
        $this->createProfileValue($elif, $elifFormal, 'email', 'elif.kaya@private.com', 'private');
        $this->command->info('Profile values populated for users.');
    }

    private function createProfileValue(User $user, Context $context, string $keyName, string $value, string $visibility)
    {
        $attribute = ProfileAttribute::where('key_name', $keyName)->first();
        if ($attribute) {
            ContextProfileValue::create([
                'user_id' => $user->id,
                'context_id' => $context->id,
                'attribute_id' => $attribute->id,
                'value' => $value,
                'visibility' => $visibility,
            ]);
        }
    }
}
