<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Database\Seeders\ServerTypesSeeder;
use Database\Seeders\CommunicationTypesSeeder;
use Database\Seeders\NotificationTypesSeeder;
use Database\Seeders\ServiceFailurePatternsSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ServerTypesSeeder::class,
            CommunicationTypesSeeder::class,
            ServiceFailurePatternsSeeder::class,
            NotificationTypesSeeder::class,
        ]);
    }
}
