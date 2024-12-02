<?php

return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'database' => 'it-service-heart-beat-test',
            'username' => 'it-service-heart-beat',
            'password' => 'it-service-heart-beat',
            'host' => '127.0.0.1',
            'port' => '3306',
        ],
    ],
    'migrations' => 'migrations',
];
