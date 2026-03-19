<?php

namespace App\Services\Kyc;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class KycStrengthService
{
    public function profile(User $user): array
    {
        [$firstName, $middleName, $lastName] = $this->splitName($user->name);

        return array_merge([
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'dob' => null,
            'phone' => $user->phone,
            'nin' => null,
            'bvn' => null,
            'address_line1' => null,
            'address_line2' => null,
            'city' => null,
            'state' => null,
            'zip' => null,
            'country' => 'NG',
        ], $user->kyc_profile ?? []);
    }

    public function persist(User $user, array $attributes): User
    {
        $profile = array_merge($this->profile($user), $this->clean($attributes));
        $snapshot = $this->snapshot($user, $profile);

        $user->forceFill([
            'phone' => $profile['phone'] ?: $user->phone,
            'kyc_level' => $snapshot['level_key'],
            'kyc_profile' => $profile,
        ])->save();

        return $user->refresh();
    }

    public function snapshot(User $user, ?array $profile = null): array
    {
        $profile ??= $this->profile($user);

        $bioComplete = collect([
            $profile['first_name'] ?? null,
            $profile['last_name'] ?? null,
            $profile['dob'] ?? null,
            $profile['address_line1'] ?? null,
            $profile['city'] ?? null,
            $profile['state'] ?? null,
        ])->every(fn ($value) => filled($value));

        $phoneComplete = filled($profile['phone'] ?? null);
        $ninComplete = filled($profile['nin'] ?? null);
        $bvnComplete = filled($profile['bvn'] ?? null);

        $score = 0;
        $score += $bioComplete ? 35 : 0;
        $score += $phoneComplete ? 20 : 0;
        $score += $ninComplete ? 25 : 0;
        $score += $bvnComplete ? 20 : 0;

        $levelKey = match (true) {
            $bioComplete && $phoneComplete && $ninComplete && $bvnComplete => 'level_3',
            $bioComplete && $phoneComplete && $ninComplete => 'level_2',
            $bioComplete && $phoneComplete => 'level_1',
            default => 'level_0',
        };

        $levelLabel = match ($levelKey) {
            'level_3' => 'Level 3',
            'level_2' => 'Level 2',
            'level_1' => 'Level 1',
            default => 'Level 0',
        };

        $nextStep = match ($levelKey) {
            'level_0' => 'Add your phone number and full bio details to unlock the first KYC tier.',
            'level_1' => 'Add your NIN to reach a stronger verification posture.',
            'level_2' => 'Add your BVN to complete the strongest KYC level available in-app.',
            default => 'Your profile is ready for the strongest in-app KYC tier.',
        };

        return [
            'level_key' => $levelKey,
            'level_label' => $levelLabel,
            'score' => min(100, $score),
            'bio_complete' => $bioComplete,
            'phone_complete' => $phoneComplete,
            'nin_complete' => $ninComplete,
            'bvn_complete' => $bvnComplete,
            'target_level' => $user->isUserPro() ? 'Level 3' : 'Level 2',
            'role_label' => $user->isUserPro() ? 'User Pro' : 'User',
            'next_step' => $nextStep,
            'masked_profile' => [
                'phone' => $this->mask($profile['phone'] ?? null),
                'nin' => $this->mask($profile['nin'] ?? null),
                'bvn' => $this->mask($profile['bvn'] ?? null),
            ],
            'profile' => $profile,
        ];
    }

    private function clean(array $attributes): array
    {
        return collect($attributes)
            ->map(function ($value) {
                if (! is_string($value)) {
                    return $value;
                }

                $trimmed = trim($value);

                return $trimmed === '' ? null : $trimmed;
            })
            ->all();
    }

    private function splitName(?string $name): array
    {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
        $firstName = Arr::get($parts, 0);
        $lastName = count($parts) > 1 ? Arr::last($parts) : null;
        $middle = count($parts) > 2 ? collect($parts)->slice(1, -1)->implode(' ') : null;

        return [$firstName, $middle, $lastName];
    }

    private function mask(?string $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        return Str::mask($value, '*', 3, max(strlen($value) - 5, 0));
    }
}
