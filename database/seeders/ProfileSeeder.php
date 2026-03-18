<?php

namespace Database\Seeders;

use App\Models\Profile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    use WithoutModelEvents;
    public function run(): void
    {
        Profile::factory()->count(10)->create();
        Profile::factory()->count(5)->active()->create();
    }
}
