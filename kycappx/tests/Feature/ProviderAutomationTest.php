<?php

namespace Tests\Feature;

use App\Models\DedicatedVirtualAccount;
use App\Models\SmsDispatch;
use App\Models\User;
use App\Models\VerificationRequest;
use App\Models\VerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProviderAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_run_automated_bvn_verification_and_trigger_sms_notification(): void
    {
        config([
            'services.prembly.app_id' => 'prembly-app',
            'services.prembly.secret_key' => 'prembly-secret',
            'services.squad.secret_key' => 'squad-secret',
            'services.squad.sms_sender_id' => 'S-Alert',
        ]);

        Http::fake([
            'https://api.prembly.com/verification/bvn' => Http::response([
                'status' => true,
                'detail' => 'Verification successful',
                'verification' => ['reference' => 'prembly-ref-123'],
                'data' => [
                    'firstName' => 'Ada',
                    'lastName' => 'Okafor',
                    'bvn' => '22123456789',
                ],
            ], 200),
            'https://sandbox-api-d.squadco.com/sms/send/instant' => Http::response([
                'success' => true,
                'message' => 'Success',
                'data' => [
                    'success' => true,
                    'message' => 'submitted successfully',
                    'data' => [
                        'batch_id' => 'batch_123',
                        'sent' => [['phone_number' => '08030000000', 'status' => 'SENT']],
                        'errors' => [],
                        'total_cost' => 5.41,
                        'currency' => 'NGN',
                    ],
                ],
            ], 200),
        ]);

        $user = User::factory()->create([
            'phone' => '08030000000',
            'settings' => ['security_alerts' => true],
            'kyc_profile' => ['phone' => '08030000000'],
        ]);
        $user->wallet()->create([
            'currency' => 'NGN',
            'balance' => 1000,
            'status' => 'active',
        ]);

        $service = VerificationService::create([
            'code' => 'BVN',
            'name' => 'Bank Verification Number',
            'type' => 'kyc',
            'country' => 'NG',
            'is_active' => true,
            'default_price' => 150,
            'default_cost' => 95,
            'required_fields' => ['bvn'],
        ]);

        $response = $this->actingAs($user)->post('/verifications', [
            'service_id' => $service->id,
            'identifier' => '22123456789',
        ]);

        $response->assertRedirect('/verifications');

        $verification = VerificationRequest::query()->firstOrFail();
        $this->assertSame('success', $verification->status);
        $this->assertSame('prembly', $verification->provider_used);
        $this->assertSame('850.00', $user->wallet->fresh()->balance);

        $sms = SmsDispatch::query()->firstOrFail();
        $this->assertSame('success', $sms->status);
        $this->assertSame('batch_123', $sms->remote_reference);
    }

    public function test_user_can_create_a_squad_virtual_account_from_saved_kyc_data(): void
    {
        config([
            'services.squad.secret_key' => 'squad-secret',
            'services.squad.beneficiary_account' => '4920299492',
        ]);

        Http::fake([
            'https://sandbox-api-d.squadco.com/virtual-account' => Http::response([
                'success' => true,
                'message' => 'Success',
                'data' => [
                    'first_name' => 'Ada',
                    'last_name' => 'Okafor',
                    'bank_code' => '058',
                    'virtual_account_number' => '7834927713',
                    'beneficiary_account' => '4920299492',
                    'customer_identifier' => 'SQUAD_USER_1',
                    'created_at' => now()->toIso8601String(),
                    'updated_at' => now()->toIso8601String(),
                ],
            ], 200),
        ]);

        $user = User::factory()->create([
            'kyc_profile' => [
                'first_name' => 'Ada',
                'middle_name' => 'N',
                'last_name' => 'Okafor',
                'dob' => '1990-07-19',
                'gender' => 'female',
                'phone' => '08030000000',
                'bvn' => '22123456789',
                'address_line1' => '12 Marine Road',
                'city' => 'Lagos',
                'state' => 'Lagos',
                'country' => 'NG',
            ],
        ]);

        $response = $this->actingAs($user)->post('/wallet/accounts/squad', [
            'bvn' => '22123456789',
        ]);

        $response->assertRedirect('/wallet');

        $account = DedicatedVirtualAccount::query()->firstOrFail();
        $this->assertSame('squad', $account->provider);
        $this->assertSame('7834927713', $account->account_number);
        $this->assertSame('active', $account->status);
    }

    public function test_user_can_send_bulk_sms_batches(): void
    {
        config([
            'services.squad.secret_key' => 'squad-secret',
        ]);

        Http::fake([
            'https://sandbox-api-d.squadco.com/sms/send/instant' => Http::response([
                'success' => true,
                'message' => 'Success',
                'data' => [
                    'success' => true,
                    'message' => 'submitted successfully',
                    'data' => [
                        'batch_id' => 'batch_bulk_1',
                        'sent' => [
                            ['phone_number' => '08030000000', 'status' => 'SENT'],
                            ['phone_number' => '08031111111', 'status' => 'SENT'],
                        ],
                        'errors' => [],
                        'total_cost' => 10.82,
                        'currency' => 'NGN',
                    ],
                ],
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/sms/send', [
            'sender_id' => 'S-Alert',
            'recipients' => "08030000000\n08031111111",
            'message' => 'Verification is complete.',
        ]);

        $response->assertRedirect();

        $dispatch = SmsDispatch::query()->firstOrFail();
        $this->assertSame('success', $dispatch->status);
        $this->assertCount(2, $dispatch->recipients ?? []);
        $this->assertSame('batch_bulk_1', $dispatch->remote_reference);
    }
}
