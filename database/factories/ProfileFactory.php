<?php

namespace Database\Factories;

use App\Enums\ProfileStatus;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        $uuid = (string) Str::uuid();

        return [
            'id' => $uuid,
            'last_name' => $this->faker->lastName(),
            'first_name' => $this->faker->firstName(),
            'picture' => "profiles/{$uuid}.jpg", // Chemin métier réaliste
            'status' => $this->faker->randomElement(ProfileStatus::cases())->value,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProfileStatus::ACTIVE->value,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProfileStatus::INACTIVE->value,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProfileStatus::PENDING->value,
        ]);
    }
}
