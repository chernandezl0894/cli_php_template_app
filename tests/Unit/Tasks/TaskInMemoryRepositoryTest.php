<?php

declare(strict_types=1);

namespace Tests\Unit\Tasks;

use App\Tasks\DTOs\CreateTaskData;
use App\Tasks\Enums\StatusEnum;
use App\Tasks\Exceptions\TaskNotFoundException;
use App\Tasks\Repositories\TaskInMemoryRepository;
use PHPUnit\Framework\TestCase;

final class TaskInMemoryRepositoryTest extends TestCase
{
    private TaskInMemoryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TaskInMemoryRepository();
    }

    public function testItCanCreateAndFindTask(): void
    {
        $dto = new CreateTaskData(
            title: 'Testing Task',
            description: 'Testing in memory repository',
            status: StatusEnum::INBOX,
            dueDate: null,
            reminderAt: null
        );

        $task = $this->repository->create($dto);

        $this->assertEquals(1, $task->id);
        $this->assertEquals('Testing Task', $task->title);

        $foundTask = $this->repository->findById(1);
        $this->assertEquals($task->id, $foundTask->id);
    }

    public function testItThrowsExceptionWhenTaskNotFound(): void
    {
        $this->expectException(TaskNotFoundException::class);

        $this->repository->findById(999);
    }
}
