<?php

namespace App\Support;

use App\Models\Seller;

class InitialSetup
{
    public static function isCompleted(): bool
    {
        return Seller::query()->exists();
    }
}

