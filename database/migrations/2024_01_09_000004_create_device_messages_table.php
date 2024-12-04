<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('device_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('message_flow_id')
                ->constrained('message_flows')
                ->onDelete('cascade');
            $table->string('source');
            $table->boolean('success')->default(false);
            $table->text('error_message')->nullable();
            $table->unsignedInteger('response_time_ms');
            $table->json('metadata');
        });
    }

    public function down()
    {
        Schema::dropIfExists('device_messages');
    }
};
