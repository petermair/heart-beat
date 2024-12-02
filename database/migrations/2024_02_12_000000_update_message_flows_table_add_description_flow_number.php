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
        Schema::table('message_flows', function (Blueprint $table) {
            $table->dropColumn('service_type');
            $table->integer('flow_number')->after('flow_type');
            $table->string('description')->nullable()->after('flow_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('message_flows', function (Blueprint $table) {
            $table->string('service_type')->after('flow_type');
            $table->dropColumn(['flow_number', 'description']);
        });
    }
};
