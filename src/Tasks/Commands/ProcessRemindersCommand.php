<?php

declare(strict_types=1);

namespace App\Tasks\Commands;

use App\Tasks\Services\TaskService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:task:process-reminders',
    description: 'Checks and processes pending task reminders.'
)]
final class ProcessRemindersCommand extends Command
{
    public function __construct(
        private TaskService $taskService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $processed = $this->taskService->processDueReminders();

        if ($processed > 0) {
            $io->success("Processed {$processed} reminder(s).");
        } else {
            $io->info('No pending reminders found.');
        }

        return Command::SUCCESS;
    }
}
