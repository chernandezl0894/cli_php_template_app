<?php

declare(strict_types=1);

namespace App\Tasks\Enums;

enum StatusEnum: string
{
    case INBOX = 'inbox';
    case NEXT_ACTION = 'next_action';
    case WAITING = 'waiting';
    case DONE = 'done';
    case ARCHIVED = 'archived';
    case CANCELLED = 'cancelled';
}
