<?php

namespace App\Jobs;

use App\Models\VerificationRequest;
use App\Services\Verification\VerificationOrchestrator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessVerificationRequestJob implements ShouldQueue
{
    use Queueable;

    public string $queue = 'verifications';

    public function __construct(
        public int $verificationRequestId,
    ) {
    }

    public function handle(VerificationOrchestrator $verificationOrchestrator): void
    {
        $verificationRequest = VerificationRequest::query()
            ->with(['service', 'user'])
            ->find($this->verificationRequestId);

        if (! $verificationRequest) {
            return;
        }

        $verificationOrchestrator->process($verificationRequest);
    }
}
