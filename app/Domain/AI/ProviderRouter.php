<?php

namespace App\Domain\AI;

use App\Domain\AI\Contracts\TryOnProviderContract;
use App\Domain\AI\Providers\FashnProvider;

class ProviderRouter
{
    public function resolve(string $providerName = 'fashn'): TryOnProviderContract
    {
        return match ($providerName) {
            'fashn' => app(FashnProvider::class),
            default => app(FashnProvider::class),
        };
    }
}