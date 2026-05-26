<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\SellerAiSetting;
use App\Models\User;
use App\Support\InitialSetup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SetupController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (InitialSetup::isCompleted()) {
            return redirect()->route('login');
        }

        return view('auth.setup');
    }

    public function store(Request $request): RedirectResponse
    {
        if (InitialSetup::isCompleted()) {
            return redirect()->route('login');
        }

        $reservedSlugs = config('tryon.reserved_seller_slugs', []);

        $data = $request->validate([
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'string', 'email:rfc,dns', 'max:255', Rule::unique('users', 'email')],
            'owner_password' => ['required', 'string', 'min:8', 'confirmed'],
            'store_name' => ['required', 'string', 'max:120'],
            'store_slug' => [
                'required',
                'string',
                'max:120',
                'regex:/^[A-Za-z0-9-]+$/',
                Rule::notIn($reservedSlugs),
                Rule::unique('sellers', 'slug'),
            ],
        ]);

        $owner = DB::transaction(function () use ($data): User {
            $owner = User::query()->create([
                'name' => $data['owner_name'],
                'email' => Str::lower($data['owner_email']),
                'password' => $data['owner_password'],
                'role' => User::ROLE_SELLER,
            ]);

            $seller = Seller::query()->create([
                'owner_user_id' => $owner->id,
                'store_name' => $data['store_name'],
                'slug' => Str::lower($data['store_slug']),
                'status' => 'active',
            ]);

            SellerAiSetting::query()->create([
                'seller_id' => $seller->id,
                'provider_name' => 'fashn',
                'fashn_api_key' => null,
                'fashn_model' => 'tryon-max',
                'fashn_dummy_enabled' => false,
                'fashn_dummy_result_url' => null,
            ]);

            return $owner;
        });

        Auth::login($owner, true);
        $request->session()->regenerate();

        return redirect()
            ->route('seller.dashboard')
            ->with('success', 'Initial setup selesai. Silakan lanjutkan konfigurasi toko dan API key.');
    }
}
