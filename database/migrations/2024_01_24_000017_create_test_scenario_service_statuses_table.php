<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_scenario_service_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_scenario_id')->constrained('test_scenarios')->cascadeOnDelete();
            $table->enum('service_type', [
                'THINGSBOARD',
                'CHIRPSTACK',
                'MQTT',
                'LORATX',
                'LORARX'
            ]);
            $table->enum('status', ['HEALTHY', 'WARNING', 'CRITICAL']);
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('last_failure_at')->nullable();
            $table->integer('success_count_1h')->default(0);
            $table->integer('total_count_1h')->default(0);
            $table->float('success_rate_1h')->default(0);
            $table->timestamp('downtime_started_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['test_scenario_id', 'service_type'], 'scenario_service_type_unique')->unique();
            $table->index(['test_scenario_id', 'status'], 'scenario_status_index_2');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_scenario_service_statuses');
    }
};
