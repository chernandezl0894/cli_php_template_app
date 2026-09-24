<?php

declare(strict_types=1);

return [
    'name' => $_ENV['APP_NAME'] ?? 'CLI App',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'America/Lima',
];
