<?php

namespace App\Enums;

enum SubscriptionStatus : string
{
    //
    case STATUS_PENDING = 'pending';
    case STATUS_ACTIVE = 'active';
    case STATUS_EXPIRED = 'expired';
    case STATUS_CANCELLED = 'cancelled';
}
