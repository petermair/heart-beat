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
        Schema::table('server_types', function (Blueprint $table) {
            $table->string('monitoring_interface')->nullable()->after('required_settings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('server_types', function (Blueprint $table) {
            $table->dropColumn('monitoring_interface');
        });
    }
};
