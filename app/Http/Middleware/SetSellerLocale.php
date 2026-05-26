<?php

namespace App\Http\Middleware;

use App\Models\Seller;
use App\Support\CurrentSellerResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSellerLocale
{
    public function __construct(private readonly CurrentSellerResolver $currentSellerResolver)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $locale = config('app.locale', 'en');

        if ($request->user() !== null) {
            try {
                $seller = $this->currentSellerResolver->resolveForUser($request->user());
                $locale = $this->normalizeLocale($seller->ui_locale ?? null);
            } catch (\Throwable) {
                // Fallback to default locale when seller is unavailable.
            }
        } else {
            $sellerSlug = (string) $request->route('seller_slug', '');
            if ($sellerSlug !== '') {
                $seller = Seller::query()
                    ->where('slug', $sellerSlug)
                    ->where('status', 'active')
                    ->first();

                if ($seller !== null) {
                    $locale = $this->normalizeLocale($seller->ui_locale ?? null);
                }
            }
        }

        app()->setLocale($locale);

        return $next($request);
    }

    private function normalizeLocale(?string $locale): string
    {
        $normalized = strtolower(trim((string) $locale));

        return in_array($normalized, ['id', 'en'], true) ? $normalized : 'id';
    }
}
