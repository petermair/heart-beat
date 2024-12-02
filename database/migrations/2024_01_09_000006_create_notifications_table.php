<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_status_id')
                ->constrained('service_statuses')
                ->onDelete('cascade');
            $table->foreignId('notification_type_id')
                ->constrained('notification_types')
                ->onDelete('cascade');
            $table->timestamp('last_sent_at')->nullable();
            $table->unsignedInteger('retry_count')->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};
