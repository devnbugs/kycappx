<?php

namespace App\Contracts;

use App\DTOs\ProviderResult;

interface VerificationProviderInterface
{
    public function providerName(): string;

    public function verifyBvn(array $payload): ProviderResult;
    public function verifyNin(array $payload): ProviderResult;
    public function verifyPhone(array $payload): ProviderResult;
    public function verifyCac(array $payload): ProviderResult;
    public function verifyUsBiodata(array $payload): ProviderResult;
    public function verifyUsAddress(array $payload): ProviderResult;
    public function verifyUsSsn(array $payload): ProviderResult;
}
