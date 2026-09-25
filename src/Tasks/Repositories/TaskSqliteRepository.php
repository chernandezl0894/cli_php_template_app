<?php

declare(strict_types=1);

namespace App\Tasks\Repositories;

use App\Tasks\DTOs\CreateTaskData;
use App\Tasks\Entities\Task;
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
        $sql = "INSERT INTO tasks (title, description, status, due_date, reminder_at)
                VALUES (:title, :description, :status, :due_date, :reminder_at)";

        $this->db->query($sql, [
            'title'       => $data->title,
            'description' => $data->description,
            'status'      => $data->status,
            'due_date'    => $data->dueDate?->format('Y-m-d H:i:s'),
            'reminder_at' => $data->reminderAt?->format('Y-m-d H:i:s'),
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

    public function markReminderAsSent(int $id): void
    {
        $sql = "UPDATE tasks SET reminder_sent = 1, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        $this->db->query($sql, ['id' => $id]);
    }
}
