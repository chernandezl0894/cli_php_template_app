<?php

declare(strict_types=1);

return [
    'path' => BASE_PATH . '/storage/logs',
    'days' => 14,
    'channels' => [
        'app' => [
            'name' => 'app',
            'file' => 'app.log',
        ],
        'cron' => [
            'name' => 'cron',
            'file' => 'cron.log',
        ],
    ],
];
