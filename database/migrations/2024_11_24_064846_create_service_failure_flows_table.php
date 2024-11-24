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
        Schema::create('service_failure_flows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pattern_id')->constrained('service_failure_patterns')->onDelete('cascade');
            $table->integer('flow_number');
            $table->boolean('fails')->default(true);
            $table->boolean('is_optional')->default(false);
            $table->timestamps();

            $table->unique(['pattern_id', 'flow_number'], 'unique_pattern_flow');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_failure_flows');
    }
};
