<?php

namespace App\DTOs;

class ProviderResult
{
    public function __construct(
        public bool $ok,
        public string $provider,
        public ?string $reference,
        public array $normalized,
        public array $raw,
        public ?string $error = null,
    ) {}
}