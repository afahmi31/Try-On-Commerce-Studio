<?php

namespace App\Support;

class SellerSlug
{
    public static function isReserved(string $slug): bool
    {
        return in_array(strtolower($slug), config('tryon.reserved_seller_slugs', []), true);
    }
}