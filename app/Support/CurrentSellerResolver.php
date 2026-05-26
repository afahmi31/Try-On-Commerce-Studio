<?php

namespace App\Support;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CurrentSellerResolver
{
    public function resolveForUser(User $user): Seller
    {
        $ownedSeller = Seller::query()
            ->where('owner_user_id', $user->id)
            ->first();

        if ($ownedSeller) {
            return $ownedSeller;
        }

        throw (new ModelNotFoundException())->setModel(Seller::class);
    }
}
