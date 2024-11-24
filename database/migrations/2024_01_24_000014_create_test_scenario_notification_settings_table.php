<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_scenario_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_scenario_id')->constrained()->cascadeOnDelete();
            $table->foreignId('notification_setting_id')
                ->constrained('notification_settings')
                ->cascadeOnDelete()
                ->name('fk_tsns_notification_setting');
            $table->timestamp('last_notification_at')->nullable();
            $table->foreignId('last_result_id')
                ->nullable()
                ->constrained('device_monitoring_results')
                ->nullOnDelete()
                ->name('fk_tsns_last_result');
            $table->timestamps();

            // Unique constraint to prevent duplicate assignments
            $table->unique(['test_scenario_id', 'notification_setting_id'], 'unique_tsns_notification');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_scenario_notification_settings');
    }
};
