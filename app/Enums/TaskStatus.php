<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class TaskStatus extends Enum
{
    const TODO = 'todo';
    const DONE = 'done';
}
