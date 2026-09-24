<?php

declare(strict_types=1);

namespace Core;

use Closure;

final class Event
{
    private string $expression;
    private Closure $action;

    public function __construct(
        string $expression,
        Closure $action,
    ) {
        $this->expression = $expression;
        $this->action = $action;
    }

    public function isDue(): bool
    {
        $currentMinute = (int)date('i');
        $currentHour = (int)date('G');

        return match ($this->expression) {
            '* * * * *'  => true,                                    // Cada minuto
            '*/5 * * * *' => ($currentMinute % 5 === 0),              // Cada 5 minutos
            '0 * * * *'  => ($currentMinute === 0),                  // Cada hora
            '0 0 * * *'  => ($currentMinute === 0 && $currentHour === 0), // Cada medianoche
            default      => false,
        };
    }

    public function run(): void
    {
        ($this->action)();
    }
}
