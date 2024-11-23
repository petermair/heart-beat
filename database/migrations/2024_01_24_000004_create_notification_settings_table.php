<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->morphs('notifiable'); // For devices, test scenarios, etc.
            $table->string('channel'); // email, slack, webhook
            $table->json('configuration'); // Channel-specific settings
            $table->json('conditions')->nullable(); // When to send notifications
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
