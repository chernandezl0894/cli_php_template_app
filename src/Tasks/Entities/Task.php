<?php

declare(strict_types=1);

namespace App\Tasks\Entities;

use App\Tasks\Enums\RecurrencePatternEnum;
use App\Tasks\Enums\StatusEnum;
use DateTimeImmutable;

final class Task
{
    public function __construct(
        public ?int $id,
        public string $title,
        public ?string $description,
        public StatusEnum $status, // inbox, next_action, waiting, done, archived
        public ?DateTimeImmutable $dueDate,
        public ?DateTimeImmutable $reminderAt,
        public bool $reminderSent,
        public bool $isRecurring,
        public RecurrencePatternEnum $recurrencePattern, // daily, weekly, monthly
        public ?DateTimeImmutable $nextOccurrenceAt,
        public ?DateTimeImmutable $createdAt = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            title: $data['title'],
            description: $data['description'] ?? null,
            status: StatusEnum::tryFrom($data['status']) ?? StatusEnum::INBOX,
            dueDate: isset($data['due_date']) ? new DateTimeImmutable($data['due_date']) : null,
            reminderAt: isset($data['reminder_at']) ? new DateTimeImmutable($data['reminder_at']) : null,
            reminderSent: (bool) ($data['reminder_sent'] ?? false),
            isRecurring: (bool) ($data['is_recurring'] ?? false),
            recurrencePattern: RecurrencePatternEnum::tryFrom($data['recurrence_pattern']) ?? RecurrencePatternEnum::DAILY,
            nextOccurrenceAt: isset($data['next_ocurrence_at']) && $data['next_ocurrence_at'] !== null
                ? new DateTimeImmutable($data['next_ocurrence_at'])
                : null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null
        );
    }
}
