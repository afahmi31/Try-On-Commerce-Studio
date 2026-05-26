<?php

namespace App\Domain\AI\Contracts;

interface TryOnProviderContract
{
    public function createJob(array $input): array;

    public function getJobStatus(string $jobId, array $context = []): array;

    public function estimateCost(array $input): int;
}
