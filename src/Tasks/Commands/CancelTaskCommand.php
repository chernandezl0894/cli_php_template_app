<?php

namespace App\Tasks\Commands;

use App\Tasks\Enums\StatusEnum;
use App\Tasks\Repositories\TaskRepository;
use App\Tasks\Services\TaskService;
use Core\Database;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:task:cancel',
    description: 'Cancels a task by its ID to stop sending reminders.'
)]
final class CancelTaskCommand extends Command
{
    public function __construct(
        private Database $db,
        private TaskRepository $taskRepository,
        private TaskService $service,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'ID of the task to be cancelled');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $id = (int) $input->getArgument('id');

        $task = $this->taskRepository->findById($id);

        // Cambiamos estado a CANCELLED y desactivamos recordatorio
        $this->service->cancelTaskById($id);

        $io->success("La tarea ID {$id} ('{$task->title}') ha sido cancelada exitosamente.");

        return Command::SUCCESS;
    }
}
