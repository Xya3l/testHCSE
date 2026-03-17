<?php

namespace App\Enums;

enum ProfileStatus: string
{
    case INACTIVE = 'inactive';
    case PENDING = 'pending';
    case ACTIVE = 'active';
}
