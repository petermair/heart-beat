<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('notification_types')->insert([
            [
                'name' => 'email',
                'display_name' => 'Email',
                'description' => 'Send notifications via email',
                'configuration_schema' => json_encode([
                    'required' => ['email'],
                    'properties' => [
                        'email' => [
                            'type' => 'string',
                            'format' => 'email',
                            'title' => 'Email Address'
                        ],
                        'name' => [
                            'type' => 'string',
                            'title' => 'Recipient Name'
                        ]
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'webhook',
                'display_name' => 'Webhook',
                'description' => 'Send notifications to a webhook URL',
                'configuration_schema' => json_encode([
                    'required' => ['url'],
                    'properties' => [
                        'url' => [
                            'type' => 'string',
                            'format' => 'uri',
                            'title' => 'Webhook URL'
                        ],
                        'method' => [
                            'type' => 'string',
                            'enum' => ['POST', 'PUT'],
                            'default' => 'POST',
                            'title' => 'HTTP Method'
                        ],
                        'headers' => [
                            'type' => 'object',
                            'title' => 'HTTP Headers'
                        ]
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'sms',
                'display_name' => 'SMS',
                'description' => 'Send notifications via SMS',
                'configuration_schema' => json_encode([
                    'required' => ['phone_number'],
                    'properties' => [
                        'phone_number' => [
                            'type' => 'string',
                            'title' => 'Phone Number',
                            'pattern' => '^\+[1-9]\d{1,14}$'
                        ],
                        'country_code' => [
                            'type' => 'string',
                            'title' => 'Country Code'
                        ]
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
