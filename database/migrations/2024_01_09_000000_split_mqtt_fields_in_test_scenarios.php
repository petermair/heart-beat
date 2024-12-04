<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('test_scenarios', function (Blueprint $table) {
            // Add new MQTT TB fields
            $table->timestamp('mqtt_tb_last_success_at')->nullable();
            $table->float('mqtt_tb_success_rate_1h')->default(0);
            $table->float('mqtt_tb_success_rate_24h')->default(0);
            $table->integer('mqtt_tb_messages_count_1h')->default(0);
            $table->integer('mqtt_tb_messages_count_24h')->default(0);
            $table->string('mqtt_tb_status')->default('unknown');

            // Add new MQTT CS fields
            $table->timestamp('mqtt_cs_last_success_at')->nullable();
            $table->float('mqtt_cs_success_rate_1h')->default(0);
            $table->float('mqtt_cs_success_rate_24h')->default(0);
            $table->integer('mqtt_cs_messages_count_1h')->default(0);
            $table->integer('mqtt_cs_messages_count_24h')->default(0);
            $table->string('mqtt_cs_status')->default('unknown');            

            // Drop old MQTT fields
            $table->dropColumn([
                'mqtt_last_success_at',
                'mqtt_success_rate_1h',
                'mqtt_success_rate_24h',
                'mqtt_messages_count_1h',
                'mqtt_messages_count_24h',
                'mqtt_status'
            ]);
        });
    }

    public function down()
    {
        Schema::table('test_scenarios', function (Blueprint $table) {
            // Add back old MQTT fields
            $table->timestamp('mqtt_last_success_at')->nullable();
            $table->float('mqtt_success_rate_1h')->default(0);
            $table->float('mqtt_success_rate_24h')->default(0);
            $table->integer('mqtt_messages_count_1h')->default(0);
            $table->integer('mqtt_messages_count_24h')->default(0);
            $table->string('mqtt_status')->default('unknown');

            // Copy data from TB fields (we choose TB as the source for rollback)
            DB::statement('UPDATE test_scenarios SET 
                mqtt_last_success_at = mqtt_tb_last_success_at,
                mqtt_success_rate_1h = mqtt_tb_success_rate_1h,
                mqtt_success_rate_24h = mqtt_tb_success_rate_24h,
                mqtt_messages_count_1h = mqtt_tb_messages_count_1h,
                mqtt_messages_count_24h = mqtt_tb_messages_count_24h,
                mqtt_status = mqtt_tb_status
            ');

            // Drop new MQTT fields
            $table->dropColumn([
                'mqtt_tb_last_success_at',
                'mqtt_tb_success_rate_1h',
                'mqtt_tb_success_rate_24h',
                'mqtt_tb_messages_count_1h',
                'mqtt_tb_messages_count_24h',
                'mqtt_tb_status',
                'mqtt_cs_last_success_at',
                'mqtt_cs_success_rate_1h',
                'mqtt_cs_success_rate_24h',
                'mqtt_cs_messages_count_1h',
                'mqtt_cs_messages_count_24h',
                'mqtt_cs_status'
            ]);
        });
    }
};
