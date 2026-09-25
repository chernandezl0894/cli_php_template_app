<?php

declare(strict_types=1);

namespace App\Tasks\Repositories;

use App\Tasks\DTOs\CreateTaskData;
use App\Tasks\Entities\Task;
use App\Tasks\Exceptions\TaskNotFoundException;
use DateTimeImmutable;

interface TaskRepository
{
    public function create(CreateTaskData $data): Task;

    /**
     * @throws TaskNotFoundException
     */
    public function findById(int $id): Task;

    /**
     * @return Task[]
     */
    public function findAll(): array;

    /**
     * @return Task[]
     */
    public function findPendingReminders(DateTimeImmutable $now): array;

    public function markReminderAsSent(int $id): void;

    public function cancelTaskById(int $id): Task;
}
