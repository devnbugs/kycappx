<?php

namespace App\Contracts;

use App\DTOs\ProviderResult;

interface VerificationProviderInterface
{
    public function providerName(): string;

    public function verifyBvn(array $payload): ProviderResult;
    public function verifyNin(array $payload): ProviderResult;

    // KYB / CAC (you’ll wire exact endpoints per provider docs)
    public function verifyCac(array $payload): ProviderResult;
}