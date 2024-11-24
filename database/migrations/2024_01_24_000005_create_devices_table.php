<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();

            // Platform Links
            $table->foreignId('thingsboard_server_id')
                ->constrained('servers')
                ->onDelete('cascade');
            $table->foreignId('chirpstack_server_id')
                ->constrained('servers')
                ->onDelete('cascade');

            // ChirpStack Specific
            $table->string('application_id');
            $table->string('device_profile_id');
            $table->string('device_eui')->unique();

            // Communication Config
            $table->foreignId('communication_type_id')
                ->constrained('communication_types')
                ->onDelete('restrict');

            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();

            // Monitoring Config
            $table->boolean('monitoring_enabled')->default(true);

            $table->timestamps();

            // Add unique constraint for device across servers with a shorter name
            $table->unique(
                ['thingsboard_server_id', 'chirpstack_server_id', 'device_eui'],
                'device_servers_eui_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
