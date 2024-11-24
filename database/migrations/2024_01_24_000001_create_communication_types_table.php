<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();  // 'mqtt', 'http'
            $table->string('label');           // 'MQTT', 'HTTP'
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_types');
    }
};
