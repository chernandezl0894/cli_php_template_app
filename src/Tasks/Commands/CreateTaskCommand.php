<?php

declare(strict_types=1);

namespace App\Tasks\Commands;

use App\Tasks\DTOs\CreateTaskData;
use App\Tasks\Services\TaskService;
use DateTimeImmutable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:task:create',
    description: 'Creates a new task with GTD status and optional reminder.'
)]
final class CreateTaskCommand extends Command
{
    public function __construct(
        private TaskService $taskService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('title', InputArgument::REQUIRED, 'Task title')
            ->addOption('desc', 'd', InputOption::VALUE_REQUIRED, 'Task description')
            ->addOption('status', 's', InputOption::VALUE_REQUIRED, 'GTD Status (inbox, next_action, waiting)', 'inbox')
            ->addOption('reminder', 'r', InputOption::VALUE_REQUIRED, 'Reminder date/time (e.g., "2026-09-23 18:00:00" or "+5 minutes")');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $title = $input->getArgument('title');
        $desc = $input->getOption('desc');
        $status = $input->getOption('status');
        $reminderStr = $input->getOption('reminder');

        $reminderAt = $reminderStr ? new DateTimeImmutable($reminderStr) : null;

        $dto = new CreateTaskData(
            title: $title,
            description: $desc,
            status: $status,
            reminderAt: $reminderAt
        );

        $task = $this->taskService->createTask($dto);

        $io->success("Task #{$task->id} created successfully!");
        $io->definitionList(
            ['Title' => $task->title],
            ['Status' => $task->status],
            ['Reminder' => $task->reminderAt?->format('Y-m-d H:i:s') ?? 'None']
        );

        return Command::SUCCESS;
    }
}
