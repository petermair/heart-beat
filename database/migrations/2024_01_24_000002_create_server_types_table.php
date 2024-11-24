<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('server_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('interface_class'); // PHP class name that implements the monitoring interface
            $table->text('description')->nullable();
            $table->json('required_settings')->nullable(); // JSON array of required settings keys
            $table->json('required_credentials')->nullable(); // JSON array of required credential keys
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_types');
    }
};
