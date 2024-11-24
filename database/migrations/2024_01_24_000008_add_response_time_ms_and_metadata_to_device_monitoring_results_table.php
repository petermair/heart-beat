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
        Schema::table('device_monitoring_results', function (Blueprint $table) {
            $table->integer('response_time_ms')->nullable()->after('additional_data');
            $table->json('metadata')->nullable()->after('response_time_ms');
            $table->foreignId('test_scenario_id')->nullable()->after('device_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_monitoring_results', function (Blueprint $table) {
            $table->dropForeign(['test_scenario_id']);
            $table->dropColumn(['response_time_ms', 'metadata', 'test_scenario_id']);
        });
    }
};
