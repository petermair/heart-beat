<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('service_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_scenario_id')
                ->constrained()
                ->onDelete('cascade');
            $table->string('service_type');
            $table->string('status');
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('last_failure_at')->nullable();
            $table->unsignedInteger('success_count_1h')->default(0);
            $table->unsignedInteger('total_count_1h')->default(0);
            $table->float('success_rate_1h')->default(0);
            $table->timestamp('downtime_started_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_statuses');
    }
};
