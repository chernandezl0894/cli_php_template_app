<?php

declare(strict_types=1);

namespace Core;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

final class Kernel
{
    public function schedule(Scheduler $scheduler, Application $application): void
    {
        // ⏰ Process reminders every minute
        $scheduler->call(function () use ($application) {
            $command = $application->find('app:task:process-reminders');
            $command->run(new ArrayInput([]), new NullOutput());
        }, '* * * * *');

        $scheduler->run();
    }
}
