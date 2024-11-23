<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('DEFAULT_EMAIL')],
            [
                'name' => env('DEFAULT_USER'),
                'password' => Hash::make(env('DEFAULT_PASSWORD')),
            ]
        );
    }
}
