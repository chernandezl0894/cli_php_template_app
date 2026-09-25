<?php

declare(strict_types=1);

namespace App\Tasks\DTOs;

use App\Tasks\Enums\RecurrencePatternEnum;
use App\Tasks\Enums\StatusEnum;
use DateTimeImmutable;

final class CreateTaskData
{
    public function __construct(
        public string $title,
        public ?string $description = null,
        public StatusEnum $status = StatusEnum::INBOX,
        public ?DateTimeImmutable $dueDate = null,
        public bool $isRecurring = false,
        public RecurrencePatternEnum $recurrencePattern = RecurrencePatternEnum::DAILY,
        public ?DateTimeImmutable $reminderAt = null
    ) {}
}
