<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class TaskPriority extends Enum
{
    const LOW = 1;
    const MEDIUM = 2;
    const HIGH = 3;
    const URGENT = 4;
    const IMMEDIATE = 5;
}
