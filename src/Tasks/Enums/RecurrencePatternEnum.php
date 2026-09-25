<?php

declare(strict_types=1);

namespace App\Tasks\Enums;

enum RecurrencePatternEnum: string
{
    case ONCE = 'once';
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
}
