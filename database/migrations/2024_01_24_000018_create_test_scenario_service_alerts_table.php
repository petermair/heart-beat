<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_scenario_service_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_scenario_id')->constrained('test_scenarios')->cascadeOnDelete();
            $table->enum('service_type', [
                'THINGSBOARD',
                'CHIRPSTACK',
                'MQTT',
                'LORATX',
                'LORARX'
            ]);
            $table->enum('alert_type', ['CRITICAL', 'WARNING']);
            $table->enum('status', ['ACTIVE', 'RESOLVED']);
            $table->text('message');
            $table->timestamp('triggered_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users');
            $table->timestamps();

            // Indexes
            $table->index(['test_scenario_id', 'service_type', 'status'], 'scenario_service_status_index');
            $table->index(['test_scenario_id', 'alert_type'], 'scenario_alert_type_index');
            $table->index(['test_scenario_id', 'triggered_at'], 'scenario_triggered_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_scenario_service_alerts');
    }
};
