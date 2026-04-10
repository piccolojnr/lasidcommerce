<?php

namespace App\Domain\Order\Services;

use Illuminate\Support\Str;

class OrderNumberGenerator
{
    public function generate(): string
    {
        return 'ORD-'.Str::upper(Str::random(10));
    }
}
