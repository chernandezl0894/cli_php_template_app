<?php

declare(strict_types=1);

namespace App\Tasks\Enums;

enum RecurrencePatternEnum: string
{
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
}
