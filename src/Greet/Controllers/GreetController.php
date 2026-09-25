<?php

declare(strict_types=1);

namespace App\Greet\Controllers;

use Core\LoggerService;

class GreetController
{
    public function __construct(protected LoggerService $loggerService)
    {
    }

    public function run(?string $name): void
    {
        $target = $name ??= "Unknown";
        $this->loggerService->info("Hello, {$target}! Welcome to the tiny CLI App.", [
            'target_user' => $target,
            'execution_mode' => 'CLI'
        ]);
    }
}
