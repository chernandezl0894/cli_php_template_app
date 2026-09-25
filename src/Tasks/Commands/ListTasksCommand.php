<?php

declare(strict_types=1);

namespace App\Tasks\Commands;

use App\Tasks\Repositories\TaskRepository;
use App\Tasks\Services\TaskService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:task:list',
    description: 'Lists all tasks stored in the database.'
)]
final class ListTasksCommand extends Command
{
    public function __construct(
        private TaskService $service,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $tasks = $this->service->findAllTasks();

        if (empty($tasks)) {
            $io->warning('No tasks found in the database.');
            return Command::SUCCESS;
        }

        $rows = array_map(fn($t) => [
            $t->id,
            $t->title,
            $t->status,
            $t->reminderAt?->format('Y-m-d H:i:s') ?? '-',
            $t->reminderSent ? '✅ Yes' : '⏳ Pending',
        ], $tasks);

        $io->table(['ID', 'Title', 'Status', 'Reminder At', 'Sent?'], $rows);

        return Command::SUCCESS;
    }
}
