<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Paid = 'paid';
    case Refunded = 'refunded';
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
}
