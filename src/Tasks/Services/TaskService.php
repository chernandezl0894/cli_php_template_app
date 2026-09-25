<?php

declare(strict_types=1);

namespace App\Tasks\Services;

use App\Shared\Services\TelegramService;
use App\Tasks\DTOs\CreateTaskData;
use App\Tasks\Entities\Task;
use App\Tasks\Repositories\TaskRepository;
use Core\LoggerService;
use DateTimeImmutable;

final class TaskService
{
    public function __construct(
        private TaskRepository $repository,
        private LoggerService $logger,
        private TelegramService $telegram
    ) {
    }

    /**
     * @return Task[]
     */
    public function findAllTasks(): array
    {
        return $this->repository->findAll();
    }

    public function createTask(CreateTaskData $data): Task
    {
        $task = $this->repository->create($data);

        $this->logger->info("Task created successfully", [
            'task_id'     => $task->id,
            'title'       => $task->title,
            'status'      => $task->status,
            'reminder_at' => $task->reminderAt?->format('Y-m-d H:i:s'),
        ]);

        return $task;
    }

    public function getTaskById(int $id): Task
    {
        $task = $this->repository->findById($id);

        return $task;
    }

    public function cancelTaskById(int $id): Task
    {
        return $this->repository->cancelTaskById($id);
    }

    public function processDueReminders(): int
    {
        $now = new DateTimeImmutable();
        $pendingTasks = $this->repository->findPendingReminders($now);
        $processedCount = 0;

        foreach ($pendingTasks as $task) {
            $message = "⏰ <b>Reminder:</b> {$task->title}\n";
            if ($task->description) {
                $message .= "📝 {$task->description}\n";
            }
            if ($task->dueDate) {
                $message .= "📅 Due: {$task->dueDate->format('Y-m-d H:i')}";
            }

            $sent = $this->telegram->send($message);

            if ($sent) {
                $this->logger->info("Telegram reminder sent for task #{$task->id}", [
                    'task_id' => $task->id,
                    'pattern' => $task->recurrencePattern->value,
                ]);
                $this->repository->markReminderAsSent($task->id);
                $processedCount++;
            }
        }

        if ($processedCount > 0) {
            $this->logger->info("Processed {$processedCount} task reminder(s).");
        }

        return $processedCount;
    }
}
