<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('device_monitoring_results', function (Blueprint $table) {
            // MQTT TB Status
            $table->boolean('mqtt_tb_status')->nullable();
            $table->integer('mqtt_tb_response_time')->nullable();
            
            // MQTT CS Status
            $table->boolean('mqtt_cs_status')->nullable();
            $table->integer('mqtt_cs_response_time')->nullable();
            
            // Lora Status
            $table->boolean('lora_tx_status')->nullable();
            $table->integer('lora_tx_response_time')->nullable();
            $table->boolean('lora_rx_status')->nullable();
            $table->integer('lora_rx_response_time')->nullable();
        });
    }

    public function down()
    {
        Schema::table('device_monitoring_results', function (Blueprint $table) {
            $table->dropColumn([
                'mqtt_tb_status',
                'mqtt_tb_response_time',
                'mqtt_cs_status',
                'mqtt_cs_response_time',
                'lora_tx_status',
                'lora_tx_response_time',
                'lora_rx_status',
                'lora_rx_response_time',
            ]);
        });
    }
};
