<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('test_results', function (Blueprint $table) {
            $table->dropColumn('flow_type');
            $table->dropColumn('service_type');
        });
    }

    public function down()
    {
        Schema::table('test_results', function (Blueprint $table) {
            $table->string('flow_type')->nullable();
            $table->string('service_type')->nullable();
        });
    }
};
