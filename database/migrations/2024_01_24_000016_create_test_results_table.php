<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_scenario_id')->constrained('test_scenarios')->cascadeOnDelete();
            $table->enum('flow_type', [
                'FULL_ROUTE_1',
                'ONE_WAY_ROUTE',
                'TWO_WAY_ROUTE',
                'DIRECT_TEST_1',
                'DIRECT_TEST_2',
                'TB_MQTT_HEALTH',
                'CS_MQTT_HEALTH',
                'TB_HTTP_HEALTH',
                'CS_HTTP_HEALTH'
            ]);
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->enum('status', ['SUCCESS', 'FAILURE', 'TIMEOUT']);
            $table->text('error_message')->nullable();
            $table->integer('execution_time_ms')->nullable();
            $table->enum('service_type', [
                'THINGSBOARD',
                'CHIRPSTACK',
                'MQTT',
                'LORATX',
                'LORARX'
            ])->nullable(); // Only set when status is FAILURE
            $table->timestamps();

            // Indexes
            $table->index(['test_scenario_id', 'created_at'], 'scenario_created_at_index');
            $table->index(['test_scenario_id', 'status'], 'scenario_status_index');
            $table->index(['test_scenario_id', 'service_type'], 'scenario_service_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_results');
    }
};
