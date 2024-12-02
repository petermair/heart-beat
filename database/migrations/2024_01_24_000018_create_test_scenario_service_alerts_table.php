<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ServiceType;
use App\Enums\AlertType;
use App\Enums\AlertStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_scenario_service_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_scenario_id')->constrained('test_scenarios')->cascadeOnDelete();
            $table->enum('service_type', array_map(fn($case) => $case->value, ServiceType::cases()));
            $table->enum('alert_type', array_map(fn($case) => $case->value, AlertType::cases()));
            $table->enum('status', array_map(fn($case) => $case->value, AlertStatus::cases()))->default(AlertStatus::ACTIVE->value);
            $table->text('message');
            $table->json('metadata')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['test_scenario_id', 'service_type'], 'alert_scenario_service_index');
            $table->index(['status', 'started_at'], 'alert_status_time_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_scenario_service_alerts');
    }
};
