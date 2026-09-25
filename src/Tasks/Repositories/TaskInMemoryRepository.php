<?php

namespace App\Tasks\Repositories;

use App\Tasks\DTOs\CreateTaskData;
use App\Tasks\Entities\Task;
use App\Tasks\Enums\RecurrencePatternEnum;
use App\Tasks\Enums\StatusEnum;
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

        $nextOccurrence = null;
        if ($data->isRecurring && $data->reminderAt !== null) {
            $nextOccurrence = $this->calculateNextDate($data->reminderAt, $data->recurrencePattern);
        }

        $this->tasks[$id] = Task::fromArray([
            'id'                 => $id,
            'title'              => $data->title,
            'description'        => $data->description,
            'status'             => $data->status->value,
            'due_date'           => $data->dueDate?->format('Y-m-d H:i:s'),
            'reminder_at'        => $data->reminderAt?->format('Y-m-d H:i:s'),
            'reminder_sent'      => 0,
            'is_recurring'       => $data->isRecurring,
            'recurrence_pattern' => $data->recurrencePattern->value,
            'next_ocurrence_at'  => $nextOccurrence?->format('Y-m-d H:i:s'),
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
        $task = $this->findById($id);

        if ($task->isRecurring && $task->reminderAt !== null) {
            // 🔁 Si es recurrente: calculamos la siguiente fecha y reiniciamos el flag de envío
            $nextReminder = $this->calculateNextDate($task->reminderAt, $task->recurrencePattern);
            $followingOccurrence = $this->calculateNextDate($nextReminder, $task->recurrencePattern);

            $this->tasks[$id] = new Task(
                id: $task->id,
                title: $task->title,
                description: $task->description,
                status: $task->status,
                dueDate: $task->dueDate,
                reminderAt: $nextReminder,
                reminderSent: false,
                isRecurring: true,
                recurrencePattern: $task->recurrencePattern,
                nextOccurrenceAt: $followingOccurrence,
                createdAt: $task->createdAt
            );
        } else {
            // 🛑 Si no es recurrente: simplemente marcamos como enviado
            $this->tasks[$id] = new Task(
                id: $task->id,
                title: $task->title,
                description: $task->description,
                status: $task->status,
                dueDate: $task->dueDate,
                reminderAt: $task->reminderAt,
                reminderSent: true,
                isRecurring: false,
                recurrencePattern: $task->recurrencePattern,
                nextOccurrenceAt: null,
                createdAt: $task->createdAt
            );
        }
    }

    public function cancelTaskById(int $id): Task
    {
        $task = $this->findById($id);

        $updatedTask = new Task(
            id: $task->id,
            title: $task->title,
            description: $task->description,
            status: StatusEnum::CANCELLED,
            dueDate: $task->dueDate,
            reminderAt: $task->reminderAt,
            reminderSent: true,
            isRecurring: false,
            recurrencePattern: $task->recurrencePattern,
            nextOccurrenceAt: null,
            createdAt: $task->createdAt
        );

        $this->tasks[$id] = $updatedTask;

        return $updatedTask;
    }

    private function calculateNextDate(DateTimeImmutable $currentDate, RecurrencePatternEnum $pattern): DateTimeImmutable
    {
        $modifier = match ($pattern) {
            'daily'   => '+1 day',
            'weekly'  => '+1 week',
            'monthly' => '+1 month',
            default   => '+1 day',
        };

        return $currentDate->modify($modifier);
    }
}
