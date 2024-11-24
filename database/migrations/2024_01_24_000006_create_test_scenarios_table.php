<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('test_scenarios', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->foreignId('mqtt_device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->foreignId('http_device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->boolean('is_active')->default(true);

            // Test Schedule
            $table->integer('interval_seconds')->default(300);
            $table->integer('timeout_seconds')->default(30);
            $table->integer('max_retries')->default(3);

            // ThingsBoard Statistics
            $table->timestamp('thingsboard_last_success_at')->nullable();
            $table->float('thingsboard_success_rate_1h')->default(0);
            $table->float('thingsboard_success_rate_24h')->default(0);
            $table->integer('thingsboard_messages_count_1h')->default(0);
            $table->integer('thingsboard_messages_count_24h')->default(0);
            $table->string('thingsboard_status')->default('HEALTHY');

            // ChirpStack Statistics
            $table->timestamp('chirpstack_last_success_at')->nullable();
            $table->float('chirpstack_success_rate_1h')->default(0);
            $table->float('chirpstack_success_rate_24h')->default(0);
            $table->integer('chirpstack_messages_count_1h')->default(0);
            $table->integer('chirpstack_messages_count_24h')->default(0);
            $table->string('chirpstack_status')->default('HEALTHY');

            // MQTT Statistics
            $table->timestamp('mqtt_last_success_at')->nullable();
            $table->float('mqtt_success_rate_1h')->default(0);
            $table->float('mqtt_success_rate_24h')->default(0);
            $table->integer('mqtt_messages_count_1h')->default(0);
            $table->integer('mqtt_messages_count_24h')->default(0);
            $table->string('mqtt_status')->default('HEALTHY');

            // LoraTX Statistics
            $table->timestamp('loratx_last_success_at')->nullable();
            $table->float('loratx_success_rate_1h')->default(0);
            $table->float('loratx_success_rate_24h')->default(0);
            $table->integer('loratx_messages_count_1h')->default(0);
            $table->integer('loratx_messages_count_24h')->default(0);
            $table->string('loratx_status')->default('HEALTHY');

            // LoraRX Statistics
            $table->timestamp('lorarx_last_success_at')->nullable();
            $table->float('lorarx_success_rate_1h')->default(0);
            $table->float('lorarx_success_rate_24h')->default(0);
            $table->integer('lorarx_messages_count_1h')->default(0);
            $table->integer('lorarx_messages_count_24h')->default(0);
            $table->string('lorarx_status')->default('HEALTHY');

            $table->timestamps();

            // Add indexes for better performance
            $table->index('mqtt_device_id');
            $table->index('http_device_id');
            $table->index('is_active');
            // Add individual indexes for status columns instead of a combined one
            $table->index('thingsboard_status');
            $table->index('chirpstack_status');
            $table->index('mqtt_status');
            $table->index('loratx_status');
            $table->index('lorarx_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_scenarios');
    }
};
