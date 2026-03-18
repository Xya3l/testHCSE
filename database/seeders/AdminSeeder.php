<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::firstOrCreate(
            ['name' => 'admin'],
            [
                'email' => 'admin@admin.com',
                'password' => bcrypt('admin'),
            ]
        );
    }
}
