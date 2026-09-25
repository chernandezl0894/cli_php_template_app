<?php

namespace App\Tasks\Repositories;

use App\Tasks\DTOs\CreateTaskData;
use App\Tasks\Entities\Task;
use App\Tasks\Exceptions\TaskNotFoundException;
use DateTimeImmutable;

final class TaskInMemoryRepository implements TaskRepository
{
    /** @var Task[] */
    private array $tasks = [];
    private int $autoIncrement = 1;

    public function __construct() {}

    public function create(CreateTaskData $data): Task
    {
        $id = $this->autoIncrement++;

        $this->tasks[$id] = Task::fromArray([
            'id'            => $id,
            'title'         => $data->title,
            'description'   => $data->description,
            'status'        => $data->status,
            'due_date'      => $data->dueDate?->format('Y-m-d H:i:s'),
            'reminder_at'   => $data->reminderAt?->format('Y-m-d H:i:s'),
            'reminder_send' => 0,
        ]);

        return $this->findById($id);
    }

    /**
     * @throws TaskNotFoundException
     */
    public function findById(int $id): Task
    {
        if (!isset($this->tasks[$id])) {
            throw TaskNotFoundException::withId($id);
        }

        return $this->tasks[$id];
    }

    /**
     * @return Task[]
     */
    public function findAll(): array
    {
        return array_map(fn(array $task) => Task::fromArray($task), $this->tasks);
    }

    /**
     * @return Task[]
     */
    public function findPendingReminders(DateTimeImmutable $now): array
    {
        $tasks = array_map(fn(array $task) => Task::fromArray($task), $this->tasks);
        return array_filter($tasks, function (Task $task) use ($now) {
            return $task->reminderAt !== null
                && $task->reminderAt <= $now
                && !$task->reminderSent
                && $task->status !== 'done';
        });
    }

    public function markReminderAsSent(int $id): void
    {
        $this->tasks[$id]->reminderSent = true;
    }
}
