<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationType;

class NotificationTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'email',
                'display_name' => 'Email Notification',
                'description' => 'Send notifications via email',
                'configuration_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'recipients' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'string',
                                'format' => 'email'
                            ],
                            'minItems' => 1
                        ],
                        'min_interval' => [
                            'type' => 'integer',
                            'minimum' => 60,
                            'default' => 300
                        ]
                    ],
                    'required' => ['recipients']
                ],
                'is_active' => true
            ],
            [
                'name' => 'slack',
                'display_name' => 'Slack Notification',
                'description' => 'Send notifications to Slack',
                'configuration_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'webhook_url' => [
                            'type' => 'string',
                            'format' => 'uri'
                        ],
                        'min_interval' => [
                            'type' => 'integer',
                            'minimum' => 60,
                            'default' => 300
                        ]
                    ],
                    'required' => ['webhook_url']
                ],
                'is_active' => true
            ],
            [
                'name' => 'webhook',
                'display_name' => 'Webhook Notification',
                'description' => 'Send notifications to a custom webhook',
                'configuration_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'url' => [
                            'type' => 'string',
                            'format' => 'uri'
                        ],
                        'method' => [
                            'type' => 'string',
                            'enum' => ['GET', 'POST', 'PUT', 'PATCH'],
                            'default' => 'POST'
                        ],
                        'headers' => [
                            'type' => 'object',
                            'additionalProperties' => [
                                'type' => 'string'
                            ]
                        ],
                        'min_interval' => [
                            'type' => 'integer',
                            'minimum' => 60,
                            'default' => 300
                        ]
                    ],
                    'required' => ['url']
                ],
                'is_active' => true
            ]
        ];

        foreach ($types as $type) {
            NotificationType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
