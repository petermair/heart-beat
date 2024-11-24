<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('server_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('interface_class'); // PHP class name that implements the monitoring interface
            $table->text('description')->nullable();
            $table->json('required_settings')->nullable(); // JSON array of required settings keys
            $table->json('required_credentials')->nullable(); // JSON array of required credential keys
            $table->timestamps();
        });

        // Insert default server types
        DB::table('server_types')->insert([
            [
                'name' => 'ThingsBoard',
                'interface_class' => 'App\\Services\\Monitoring\\ThingsBoardMonitor',
                'description' => 'ThingsBoard IoT Platform monitoring',
                'required_settings' => json_encode(['api_endpoint']),
                'required_credentials' => json_encode(['username', 'password']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ChirpStack',
                'interface_class' => 'App\\Services\\Monitoring\\ChirpStackMonitor',
                'description' => 'ChirpStack LoRaWAN Network Server monitoring',
                'required_settings' => json_encode(['grpc_endpoint', 'api_endpoint']),
                'required_credentials' => json_encode(['api_token']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('server_types');
    }
};
