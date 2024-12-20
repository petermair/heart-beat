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
        Schema::table('test_scenario_notification_settings', function (Blueprint $table) {
            $table->json('settings')->nullable()->after('notification_setting_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_scenario_notification_settings', function (Blueprint $table) {
            $table->dropColumn('settings');
        });
    }
};
