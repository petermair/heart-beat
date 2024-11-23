<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_scenarios', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('test_type'); // e.g., 'mqtt_rx', 'mqtt_tx', 'http_health', etc.
            $table->json('test_configuration')->nullable(); // For test-specific settings
            $table->integer('interval_seconds')->default(300); // Default 5 minutes
            $table->integer('timeout_seconds')->default(30);
            $table->integer('max_retries')->default(3);
            $table->boolean('is_active')->default(true);
            $table->json('notification_settings')->nullable(); // For alert thresholds and recipients
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_scenarios');
    }
};
