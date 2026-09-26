<?php

declare(strict_types=1);

namespace App\Tasks\DTOs;

use App\Tasks\Enums\RecurrencePatternEnum;
use App\Tasks\Enums\StatusEnum;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

#[Assert\Expression(
    "this.recurrencePattern.value === 'once' ? this.reminderAt !== null : true",
    message: "If the frequency is 'once', a reminder date (reminderAt) must be specified."
)]
#[Assert\Expression(
    "this.recurrencePattern.value !== 'once' ? this.isRecurring === true : true",
    message: 'If the frequency is daily, weekly, or monthly, the task must be marked as recurring.'
)]
final class CreateTaskData
{
    public function __construct(
        #[Assert\NotBlank(message: 'The title cannot be empty.')]
        #[Assert\Length(
            min: 3,
            max: 255,
            minMessage: 'The title must be at least {{ limit }} characters long.',
            maxMessage: 'The title cannot exceed {{ limit }} characters.'
        )]
        public string $title,
        #[Assert\Length(max: 1000, maxMessage: 'The description is too long.')]
        public ?string $description = null,
        public StatusEnum $status = StatusEnum::INBOX,
        public ?DateTimeImmutable $dueDate = null,
        public bool $isRecurring = false,
        public RecurrencePatternEnum $recurrencePattern = RecurrencePatternEnum::DAILY,
        public ?DateTimeImmutable $reminderAt = null
    ) {
    }
}
