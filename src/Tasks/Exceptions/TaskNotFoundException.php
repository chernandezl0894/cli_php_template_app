<?php

declare(strict_types=1);

namespace App\Tasks\Exceptions;

final class TaskNotFoundException extends TaskDomainException
{
    public static function withId(int $id): self
    {
        return new self("The task with ID {$id} wasn't found.");
    }
}
