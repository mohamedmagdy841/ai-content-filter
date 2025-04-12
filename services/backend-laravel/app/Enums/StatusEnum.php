<?php

namespace App\Enums;

enum StatusEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case DELETED = 'deleted';
    case FLAGGED = 'flagged';
}
