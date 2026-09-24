<?php

declare(strict_types=1);

namespace App\Tasks\DTOs;

use DateTimeImmutable;

final class CreateTaskData
{
    public function __construct(
        public string $title,
        public ?string $description = null,
        public string $status = 'inbox',
        public ?DateTimeImmutable $dueDate = null,
        public ?DateTimeImmutable $reminderAt = null
    ) {}
}
