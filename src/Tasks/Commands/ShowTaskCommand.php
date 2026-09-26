<?php

declare(strict_types=1);

namespace App\Tasks\Commands;

use App\Tasks\Exceptions\TaskNotFoundException;
use App\Tasks\Services\TaskService;
use Core\LoggerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:task:show',
    description: 'Show task details by ID.'
)]
final class ShowTaskCommand extends Command
{
    public function __construct(
        private LoggerService $logger,
        private TaskService $service
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'Task ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $taskId = (int) $input->getArgument('id');

        try {
            $task = $this->service->getTaskById($taskId);

            $io->title("📋 Task details #{$task->id}");
            $io->definitionList(
                ['Title' => $task->title],
                ['Description' => $task->description ?? 'No description'],
                ['Status' => $task->status],
                ['Remainders' => $task->reminderAt?->format('Y-m-d H:i:s') ?? 'No scheduled']
            );

            $this->logger->info("Task #{$task->id} showed successfully.");
            return Command::SUCCESS;
        } catch (TaskNotFoundException $e) {
            $this->logger->warning($e->getMessage(), ['task_id' => $taskId]);
            $io->warning($e->getMessage());
            return Command::FAILURE;
        } catch (\Throwable $e) {
            $this->logger->critical('Unexpected error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $io->error('An unexpected system error occurred.');
            return Command::FAILURE;
        }
    }
}
