<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('device_monitoring_results');
    }

    public function down()
    {
        Schema::create('device_monitoring_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->string('service_type');
            $table->string('status');
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }
};
