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
        Schema::create('test_scenario_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_scenario_id')->constrained()->cascadeOnDelete();
            $table->foreignId('notification_type_id')->constrained()->cascadeOnDelete();
            
            // Specific thresholds for this test scenario
            $table->integer('warning_threshold')->nullable();  // In percent
            $table->integer('critical_threshold')->nullable(); // In percent
            $table->integer('min_downtime_minutes')->nullable(); // Minimum downtime before notification
            
            $table->timestamps();

            // Unique constraint to prevent duplicate assignments
            $table->unique(['test_scenario_id', 'notification_type_id'], 'unique_test_scenario_notification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_scenario_notifications');
    }
};
