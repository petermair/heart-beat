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
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('server_type_id')->constrained()->onDelete('restrict');
            $table->foreignId('mqtt_broker_id')->nullable()->constrained()->nullOnDelete();
            $table->string('url')->nullable();
            $table->text('description')->nullable();
            $table->integer('monitoring_interval')->default(60);
            $table->boolean('is_active')->default(true);
            $table->text('credentials')->nullable();
            $table->text('settings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
