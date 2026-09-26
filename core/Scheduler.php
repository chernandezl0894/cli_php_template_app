<?php

declare(strict_types=1);

namespace Core;

use Closure;

final class Scheduler
{
    /** @var Event[] */
    private array $events = [];

    public function __construct(private LoggerService $logger)
    {
    }

    public function call(Closure $action, string $expression): Event
    {
        $event = new Event($expression, $action);
        $this->events[] = $event;
        return $event;
    }

    public function run(): void
    {
        $executedCount = 0;

        foreach ($this->events as $event) {
            if ($event->isDue()) {
                $event->run();
                $executedCount++;
            }
        }

        if ($executedCount > 0) {
            $this->logger->cron("The scheduler processed {$executedCount} task(s) this minute.");
        }
    }
}
