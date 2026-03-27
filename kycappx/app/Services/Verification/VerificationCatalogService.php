<?php

namespace App\Services\Verification;

use App\Models\VerificationService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class VerificationCatalogService
{
    public function __construct(private IdentityEngineRegistry $identityEngines)
    {
    }

    public function definitions(): array
    {
        $premblyDefinitions = collect(config('services.prembly.products', []))
            ->mapWithKeys(function (array $product, string $productKey) {
                $service = data_get($product, 'service', []);
                $serviceCode = strtoupper((string) data_get($service, 'code', $productKey));

                return [$serviceCode => array_merge($product, [
                    'provider' => 'prembly',
                    'product_key' => $productKey,
                    'service' => array_merge([
                        'code' => $serviceCode,
                        'name' => data_get($product, 'label', Str::headline($serviceCode)),
                        'type' => 'kyc',
                        'country' => 'NG',
                        'default_price' => 0,
                        'default_cost' => 0,
                        'is_active' => false,
                        'featured' => false,
                    ], $service),
                ])];
            })
            ->all();

        $catalogAdditions = collect($this->identityEngines->serviceCatalogAdditions())
            ->mapWithKeys(function (array $definition, string $serviceCode) {
                return [strtoupper($serviceCode) => $this->normalizeCatalogAddition($serviceCode, $definition)];
            })
            ->all();

        return array_replace($premblyDefinitions, $catalogAdditions);
    }

    public function definitionFor(VerificationService|string|null $service): ?array
    {
        if ($service instanceof VerificationService) {
            $service = $service->code;
        }

        if (! is_string($service) || $service === '') {
            return null;
        }

        return $this->definitions()[strtoupper($service)] ?? null;
    }

    public function seedableServices(): array
    {
        return collect($this->definitions())
            ->map(function (array $definition) {
                $service = data_get($definition, 'service', []);
                $fields = collect($this->requestBody($definition))
                    ->pluck('name')
                    ->filter()
                    ->values()
                    ->all();

                if ($fields === [] && $this->usesJsonPayloadFallback($definition)) {
                    $fields = ['payload_json'];
                }

                return [
                    'code' => data_get($service, 'code'),
                    'name' => data_get($service, 'name'),
                    'type' => data_get($service, 'type', 'kyc'),
                    'country' => strtoupper((string) data_get($service, 'country', 'NG')),
                    'is_active' => (bool) data_get($service, 'is_active', false),
                    'default_price' => (float) data_get($service, 'default_price', 0),
                    'default_cost' => (float) data_get($service, 'default_cost', 0),
                    'required_fields' => $fields,
                    'engine_preferences' => $this->identityEngines->defaultServicePreferences((string) data_get($service, 'code')),
                    'response_template' => $this->identityEngines->responseTemplate((string) data_get($service, 'code')),
                ];
            })
            ->values()
            ->all();
    }

    public function isLaunchable(VerificationService $service): bool
    {
        $definition = $this->definitionFor($service);

        if (! $definition || ! $service->is_active) {
            return false;
        }

        return collect($this->identityEngines->availableProvidersForService($service))->isNotEmpty();
    }

    public function filterLaunchable(Collection $services): Collection
    {
        return $services
            ->filter(fn ($service) => $service instanceof VerificationService && $this->isLaunchable($service))
            ->values();
    }

    public function filterFeatured(Collection $services): Collection
    {
        return $services
            ->filter(function ($service) {
                $definition = $service instanceof VerificationService ? $this->definitionFor($service) : null;

                return $definition && (bool) data_get($definition, 'service.featured', false);
            })
            ->values();
    }

    public function fieldBlueprintsFor(VerificationService $service): array
    {
        $definition = $this->definitionFor($service);

        if (! $definition) {
            return [];
        }

        $fields = collect($this->requestBody($definition))
            ->map(function (array $field) use ($service) {
                $name = (string) data_get($field, 'name');

                return [
                    'name' => $name,
                    'label' => Str::headline(str_replace('_', ' ', $name)),
                    'type' => $this->inputTypeForField($service, $field),
                    'placeholder' => data_get($field, 'placeholder'),
                    'helper' => data_get($field, 'description'),
                    'required' => (bool) data_get($field, 'required', false),
                ];
            })
            ->values();

        if ($fields->isNotEmpty()) {
            return $fields->all();
        }

        if ($this->usesJsonPayloadFallback($definition)) {
            return [[
                'name' => 'payload_json',
                'label' => 'Request Payload',
                'type' => 'textarea',
                'placeholder' => '{"key":"value"}',
                'helper' => 'Paste the raw request body as JSON for this endpoint.',
                'required' => true,
            ]];
        }

        return [];
    }

    public function validationRulesFor(VerificationService $service): array
    {
        $definition = $this->definitionFor($service);

        if (! $definition) {
            return [];
        }

        $fields = $this->requestBody($definition);

        if ($fields === [] && $this->usesJsonPayloadFallback($definition)) {
            return [
                'payload_json' => ['required', 'json'],
            ];
        }

        $rules = [];
        $oneOfFields = collect($this->oneOfGroups($definition))->flatten()->all();

        foreach ($fields as $field) {
            $name = (string) data_get($field, 'name');
            $rules[$name] = $this->rulesForField($service, $field, in_array($name, $oneOfFields, true));
        }

        foreach ($this->oneOfGroups($definition) as $group) {
            if (count($group) !== 2) {
                continue;
            }

            [$first, $second] = array_values($group);

            if (isset($rules[$first])) {
                $rules[$first][] = 'required_without:'.$second;
            }

            if (isset($rules[$second])) {
                $rules[$second][] = 'required_without:'.$first;
            }
        }

        return $rules;
    }

    public function attributeNamesFor(VerificationService $service): array
    {
        return collect($this->requestBody($this->definitionFor($service) ?? []))
            ->mapWithKeys(fn (array $field) => [
                (string) data_get($field, 'name') => Str::headline(str_replace('_', ' ', (string) data_get($field, 'name'))),
            ])
            ->all();
    }

    public function payloadFor(VerificationService $service, array $validated): array
    {
        $definition = $this->definitionFor($service);

        if (! $definition) {
            return [];
        }

        $fields = $this->requestBody($definition);

        if ($fields === [] && $this->usesJsonPayloadFallback($definition)) {
            $decoded = json_decode((string) ($validated['payload_json'] ?? '{}'), true);

            return is_array($decoded) ? $decoded : [];
        }

        $payload = [];

        foreach ($fields as $field) {
            $name = (string) data_get($field, 'name');

            if (array_key_exists($name, $validated)) {
                $payload[$name] = $validated[$name];
            }
        }

        return $payload;
    }

    public function defaultValuesFor(VerificationService $service, array $profile): array
    {
        $definition = $this->definitionFor($service);

        if (! $definition) {
            return $profile;
        }

        $defaults = $profile;
        $fullName = trim(collect([
            data_get($profile, 'first_name'),
            data_get($profile, 'middle_name'),
            data_get($profile, 'last_name'),
        ])->filter()->implode(' '));

        foreach ($this->requestBody($definition) as $field) {
            $name = (string) data_get($field, 'name');
            $defaults[$name] = data_get($field, 'default', $this->defaultForField($definition, $name, $profile, $fullName));
        }

        return $defaults;
    }

    public function validateExclusiveFields(VerificationService $service, array $validated): array
    {
        $definition = $this->definitionFor($service);

        if (! $definition) {
            return [];
        }

        $errors = [];

        foreach ($this->oneOfGroups($definition) as $group) {
            $provided = collect($group)
                ->filter(fn (string $field) => filled($validated[$field] ?? null))
                ->values();

            if ($provided->count() !== 1) {
                foreach ($group as $field) {
                    $errors[$field] = 'Provide exactly one of '.collect($group)->map(fn (string $name) => Str::headline(str_replace('_', ' ', $name)))->implode(' or ').'.';
                }
            }
        }

        return $errors;
    }

    private function normalizeCatalogAddition(string $serviceCode, array $definition): array
    {
        $service = (array) data_get($definition, 'service', []);

        return [
            'label' => data_get($definition, 'label', Str::headline($serviceCode)),
            'method' => data_get($definition, 'method', 'POST'),
            'path' => data_get($definition, 'path'),
            'docs_url' => data_get($definition, 'docsUrl', data_get($definition, 'docs_url')),
            'countries' => data_get($definition, 'countries', ['NG']),
            'required' => (bool) data_get($definition, 'required', true),
            'normalizer' => data_get($definition, 'normalizer'),
            'request_body' => data_get($definition, 'requestBody', data_get($definition, 'request_body', [])),
            'json_payload_fallback' => (bool) data_get($definition, 'jsonPayloadFallback', data_get($definition, 'json_payload_fallback', false)),
            'one_of' => data_get($definition, 'oneOf', data_get($definition, 'one_of', [])),
            'notes' => data_get($definition, 'notes'),
            'service' => [
                'code' => strtoupper((string) data_get($service, 'code', $serviceCode)),
                'name' => data_get($service, 'name', data_get($definition, 'label', Str::headline($serviceCode))),
                'type' => data_get($service, 'type', 'kyc'),
                'country' => strtoupper((string) data_get($service, 'country', 'NG')),
                'default_price' => (float) data_get($service, 'defaultPrice', data_get($service, 'default_price', 0)),
                'default_cost' => (float) data_get($service, 'defaultCost', data_get($service, 'default_cost', 0)),
                'is_active' => (bool) data_get($service, 'isActive', data_get($service, 'is_active', false)),
                'featured' => (bool) data_get($service, 'featured', false),
            ],
        ];
    }

    private function requestBody(array $definition): array
    {
        return collect(data_get($definition, 'request_body', []))
            ->filter(fn ($field) => is_array($field) && filled(data_get($field, 'name')))
            ->values()
            ->all();
    }

    private function usesJsonPayloadFallback(array $definition): bool
    {
        return (bool) data_get($definition, 'json_payload_fallback', false)
            && strtoupper((string) data_get($definition, 'method', 'POST')) !== 'GET';
    }

    private function oneOfGroups(array $definition): array
    {
        return collect(data_get($definition, 'one_of', []))
            ->map(fn ($group) => Arr::wrap($group))
            ->filter(fn (array $group) => $group !== [])
            ->values()
            ->all();
    }

    private function rulesForField(VerificationService $service, array $field, bool $isExclusiveField): array
    {
        $name = (string) data_get($field, 'name');
        $required = (bool) data_get($field, 'required', false);
        $type = $this->inputTypeForField($service, $field);
        $rules = [$required && ! $isExclusiveField ? 'required' : 'nullable'];

        if ($type === 'date') {
            $rules[] = 'date';

            return $rules;
        }

        $rules[] = 'string';

        if ($name === 'image') {
            $rules[] = 'max:500000';

            return $rules;
        }

        $digits = $this->digitRuleForField($service, $name);

        if ($digits) {
            $rules[] = $digits;

            return $rules;
        }

        $rules[] = 'max:255';

        return $rules;
    }

    private function digitRuleForField(VerificationService $service, string $fieldName): ?string
    {
        $code = strtoupper($service->code);
        $definition = $this->definitionFor($service);
        $path = (string) data_get($definition, 'path', '');

        return match (true) {
            in_array($fieldName, ['dob', 'address_verification_due_date'], true) => null,
            $fieldName === 'exam_year' => 'digits:4',
            $fieldName === 'number' && str_contains($code, 'BVN') => 'digits:11',
            in_array($fieldName, ['number', 'number_nin'], true) && str_contains($code, 'NIN') => 'digits:11',
            $fieldName === 'number' && str_contains($path, '/bank_account/') => 'digits:10',
            default => null,
        };
    }

    private function inputTypeForField(VerificationService $service, array $field): string
    {
        $name = (string) data_get($field, 'name');

        return match (true) {
            $name === 'payload_json' => 'textarea',
            in_array($name, ['dob', 'address_verification_due_date'], true) => 'date',
            default => 'text',
        };
    }

    private function defaultForField(array $definition, string $fieldName, array $profile, string $fullName): mixed
    {
        $serviceCode = strtoupper((string) data_get($definition, 'service.code', ''));

        return match ($fieldName) {
            'first_name',
            'middle_name',
            'last_name',
            'dob',
            'phone',
            'city',
            'state',
            'zip',
            'country' => data_get($profile, $fieldName),
            'address' => collect([
                data_get($profile, 'address_line1'),
                data_get($profile, 'address_line2'),
                data_get($profile, 'city'),
                data_get($profile, 'state'),
                data_get($profile, 'zip'),
            ])->filter()->implode(', '),
            'street' => data_get($profile, 'address_line1'),
            'customer',
            'customer_name',
            'account_name' => $fullName ?: null,
            'number' => match (true) {
                str_contains($serviceCode, 'BVN') => data_get($profile, 'bvn'),
                str_contains($serviceCode, 'NIN') => data_get($profile, 'nin'),
                str_contains($serviceCode, 'PHONE') => data_get($profile, 'phone'),
                default => null,
            },
            'number_nin' => data_get($profile, 'nin'),
            default => null,
        };
    }
}
