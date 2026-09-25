<?php

declare(strict_types=1);

namespace Tests\Integration\Tasks;

use App\Tasks\DTOs\CreateTaskData;
use App\Tasks\Enums\RecurrencePatternEnum;
use App\Tasks\Enums\StatusEnum;
use App\Tasks\Exceptions\TaskNotFoundException;
use App\Tasks\Repositories\TaskSqliteRepository;
use Core\Database;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class TaskSqliteRepositoryTest extends TestCase
{
    private Database $db;
    private TaskSqliteRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // 🚀 Pasamos null en Config y el $pdo directamente como segundo argumento
        $this->db = new Database(null, $pdo);

        // 🛠️ Creamos la estructura
        $this->db->query("
        CREATE TABLE tasks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT,
            status TEXT NOT NULL DEFAULT 'inbox',
            due_date DATETIME NULL,
            reminder_at DATETIME NULL,
            reminder_sent INTEGER NOT NULL DEFAULT 0,
            is_recurring INTEGER NOT NULL DEFAULT 0,
            recurrence_pattern TEXT NOT NULL DEFAULT 'daily',
            next_ocurrence_at DATETIME DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
        ");

        $this->repository = new TaskSqliteRepository($this->db);
    }

    public function testItCanCreateAndFindTask(): void
    {
        $data = new CreateTaskData(
            title: 'Tarea de prueba SQLite',
            description: 'Probando integración',
            status: StatusEnum::INBOX
        );

        $task = $this->repository->create($data);

        $this->assertEquals(1, $task->id);
        $this->assertEquals('Tarea de prueba SQLite', $task->title);
        $this->assertEquals(StatusEnum::INBOX, $task->status);
    }

    public function testItHandlesRecurringRemindersCorrectly(): void
    {
        $now = new DateTimeImmutable('2026-09-25 07:00:00');

        $data = new CreateTaskData(
            title: 'Sacar la basura',
            status: StatusEnum::INBOX,
            isRecurring: true,
            recurrencePattern: RecurrencePatternEnum::DAILY,
            reminderAt: $now
        );

        $task = $this->repository->create($data);

        // Verificamos que esté pendiente
        $pending = $this->repository->findPendingReminders($now);
        $this->assertCount(1, $pending);

        // Marcamos como enviado y verificamos reprogramación
        $this->repository->markReminderAsSent($task->id);

        $updatedTask = $this->repository->findById($task->id);
        $this->assertEquals('2026-09-26 07:00:00', $updatedTask->reminderAt->format('Y-m-d H:i:s'));
        $this->assertFalse($updatedTask->reminderSent);
    }

    public function testItThrowsExceptionWhenNotFound(): void
    {
        $this->expectException(TaskNotFoundException::class);
        $this->repository->findById(999);
    }
}
