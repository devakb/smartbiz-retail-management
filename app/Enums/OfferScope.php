<?php

namespace App\Enums;

enum OfferScope: string
{
    case Product = 'product';
    case Category = 'category';
    case Order = 'order';
}
