<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Database\Seeders\ServerTypeSeeder;
use Database\Seeders\CommunicationTypeSeeder;
use Database\Seeders\NotificationTypeSeeder;
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
            ServerTypeSeeder::class,
            CommunicationTypeSeeder::class,
            NotificationTypeSeeder::class,
        ]);
    }
}
