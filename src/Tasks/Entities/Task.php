<?php

declare(strict_types=1);

namespace App\Tasks\Entities;

use DateTimeImmutable;

final class Task
{
    public function __construct(
        public ?int $id,
        public string $title,
        public ?string $description,
        public string $status, // inbox, next_action, waiting, done, archived
        public ?DateTimeImmutable $dueDate,
        public ?DateTimeImmutable $reminderAt,
        public bool $reminderSent,
        public ?DateTimeImmutable $createdAt = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            title: $data['title'],
            description: $data['description'] ?? null,
            status: $data['status'] ?? 'inbox',
            dueDate: isset($data['due_date']) ? new DateTimeImmutable($data['due_date']) : null,
            reminderAt: isset($data['reminder_at']) ? new DateTimeImmutable($data['reminder_at']) : null,
            reminderSent: (bool) ($data['reminder_sent'] ?? false),
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null
        );
    }
}
