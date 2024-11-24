<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_monitoring_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();

            // Status for each platform
            $table->boolean('chirpstack_status')->default(false);
            $table->boolean('thingsboard_status')->default(false);

            // Response times
            $table->integer('chirpstack_response_time')->nullable(); // in milliseconds
            $table->integer('thingsboard_response_time')->nullable(); // in milliseconds

            // Overall test result
            $table->boolean('success')->default(false);
            $table->text('error_message')->nullable();

            // Test metadata
            $table->string('test_type')->default('scheduled'); // scheduled, manual, or api
            $table->json('additional_data')->nullable(); // For storing any additional test data

            $table->timestamps();

            // Index for quick lookups
            $table->index(['device_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_monitoring_results');
    }
};
