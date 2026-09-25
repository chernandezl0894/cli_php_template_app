<?php

declare(strict_types=1);

namespace App\Tasks\Repositories;

use App\Tasks\DTOs\CreateTaskData;
use App\Tasks\Entities\Task;
use App\Tasks\Enums\RecurrencePatternEnum;
use App\Tasks\Enums\StatusEnum;
use App\Tasks\Exceptions\TaskNotFoundException;
use Core\Database;
use DateTimeImmutable;

final class TaskSqliteRepository implements TaskRepository
{
    public function __construct(
        private Database $db
    ) {}

    public function create(CreateTaskData $data): Task
    {
        $nextOccurrence = null;
        if ($data->isRecurring && $data->reminderAt !== null) {
            $nextOccurrence = $this->calculateNextDate($data->reminderAt, $data->recurrencePattern);
        }

        $sql = "INSERT INTO tasks (
                    title, description, status, due_date, reminder_at,
                    is_recurring, recurrence_pattern, next_ocurrence_at
                ) VALUES (
                    :title, :description, :status, :due_date, :reminder_at,
                    :is_recurring, :recurrence_pattern, :next_ocurrence_at
                )";

        $this->db->query($sql, [
            'title'              => $data->title,
            'description'        => $data->description,
            'status'             => $data->status->value,
            'due_date'           => $data->dueDate?->format('Y-m-d H:i:s'),
            'reminder_at'        => $data->reminderAt?->format('Y-m-d H:i:s'),
            'is_recurring'       => $data->isRecurring ? 1 : 0,
            'recurrence_pattern' => $data->recurrencePattern->value,
            'next_ocurrence_at'  => $nextOccurrence?->format('Y-m-d H:i:s'),
        ]);

        $id = (int) $this->db->getConnection()->lastInsertId();

        return $this->findById($id);
    }

    public function findById(int $id): Task
    {
        $stmt = $this->db->query("SELECT * FROM tasks WHERE id = :id", ['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            throw TaskNotFoundException::withId($id);
        }

        return Task::fromArray($row);
    }

    /**
     * @return Task[]
     */
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM tasks ORDER BY id DESC");
        $row = $stmt->fetch();

        return array_map(fn(array $row) => Task::fromArray($row), $stmt->fetchAll());
    }

    /**
     * @return Task[]
     */
    public function findPendingReminders(DateTimeImmutable $now): array
    {
        $sql = "SELECT * FROM tasks
                WHERE reminder_at IS NOT NULL
                  AND reminder_at <= :now
                  AND reminder_sent = 0
                  AND status != 'done'";

        $stmt = $this->db->query($sql, [
            'now' => $now->format('Y-m-d H:i:s'),
        ]);

        return array_map(fn(array $row) => Task::fromArray($row), $stmt->fetchAll());
    }

    public function cancelTaskById(int $id): Task
    {
        $sql = "UPDATE tasks
                SET status = :status,
                    reminder_sent = 1,
                    is_recurring = 0,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $stmt = $this->db->query($sql, [
            'id' => $id,
            'status' => StatusEnum::CANCELLED->value
        ]);
        $row = $stmt->fetch();
        return Task::fromArray($row);
    }

    public function markReminderAsSent(int $id): void
    {
        $task = $this->findById($id);

        if ($task->isRecurring && $task->reminderAt !== null) {
            // 🔁 Si es recurrente: reprogramamos la fecha y dejamos reminder_sent = 0
            $nextReminder = $this->calculateNextDate($task->reminderAt, $task->recurrencePattern);
            $followingOccurrence = $this->calculateNextDate($nextReminder, $task->recurrencePattern);

            $sql = "UPDATE tasks
                    SET reminder_at = :reminder_at,
                        next_ocurrence_at = :next_ocurrence_at,
                        reminder_sent = 0,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id";

            $this->db->query($sql, [
                'id'                => $id,
                'reminder_at'       => $nextReminder->format('Y-m-d H:i:s'),
                'next_ocurrence_at' => $followingOccurrence->format('Y-m-d H:i:s'),
            ]);
        } else {
            // 🛑 Si no es recurrente: marcamos como enviado
            $sql = "UPDATE tasks
                    SET reminder_sent = 1,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id";

            $this->db->query($sql, ['id' => $id]);
        }
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
