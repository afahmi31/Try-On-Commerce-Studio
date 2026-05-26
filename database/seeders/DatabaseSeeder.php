<?php

namespace Database\Seeders;

use App\Models\Seller;
use App\Models\SellerAiSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $sellerOwner = User::query()->updateOrCreate(
            ['email' => 'seller@tryon.test'],
            [
                'name' => 'Seller',
                'password' => 'password',
                'role' => User::ROLE_SELLER,
            ]
        );

        $seller = Seller::query()->updateOrCreate(
            ['slug' => 'fashion'],
            [
                'owner_user_id' => $sellerOwner->id,
                'store_name' => 'Fashion',
                'status' => 'active',
            ]
        );

        SellerAiSetting::query()->updateOrCreate(
            ['seller_id' => $seller->id],
            [
                'provider_name' => 'fashn',
                'fashn_api_key' => null,
                'fashn_model' => 'tryon-max',
                'fashn_dummy_enabled' => false,
                'fashn_dummy_result_url' => null,
            ]
        );
    }
}
