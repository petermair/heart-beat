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
        Schema::create('mqtt_brokers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('host');
            $table->integer('port')->default(1883);
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->boolean('ssl_enabled')->default(false);
            $table->text('ssl_ca')->nullable();
            $table->text('ssl_cert')->nullable();
            $table->text('ssl_key')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mqtt_brokers');
    }
};
