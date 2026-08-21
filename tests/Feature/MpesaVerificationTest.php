<?php

namespace Tests\Feature;

use App\Models\MpesaVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class MpesaVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('application_payments.fee', 1000);
        config()->set('application_payments.manual.type', 'till');
        config()->set('application_payments.daraja', array_merge(config('application_payments.daraja'), [
            'base_url' => 'https://api.safaricom.co.ke',
            'consumer_key' => 'key',
            'consumer_secret' => 'secret',
            'shortcode' => '7425170',
            'till_number' => '9373479',
            'identifier_type' => '4',
            'initiator' => 'testapi',
            'initiator_password' => null,
            'security_credential' => 'pre-encrypted-credential',
            'result_url' => 'https://gocare.test/mpesa/verification/callback/result',
            'timeout_url' => 'https://gocare.test/mpesa/verification/callback/timeout',
        ]));
    }

    private function fakeDaraja(?array $queryResponse = null): void
    {
        Http::fake([
            'api.safaricom.co.ke/oauth/*' => Http::response(['access_token' => 'token', 'expires_in' => '3599']),
            'api.safaricom.co.ke/mpesa/transactionstatus/v1/query' => Http::response($queryResponse ?? [
                'OriginatorConversationID' => 'AG-ORIG-1',
                'ConversationID' => 'AG-CONV-1',
                'ResponseCode' => '0',
                'ResponseDescription' => 'Accept the service request successfully.',
            ]),
        ]);
    }

    private function resultPayload(array $overrides = [], array $parameterOverrides = []): array
    {
        $parameters = array_merge([
            'ReceiptNo' => 'SHK3XY8ZT9',
            'TransactionStatus' => 'Completed',
            'Amount' => 1000,
            'CreditPartyName' => '9373479 - GoCare Training Institute',
            'DebitPartyName' => '254712345678 - Jane Doe',
            'FinalisedTime' => '20260821141824',
        ], $parameterOverrides);

        return [
            'Result' => array_merge([
                'ResultType' => 0,
                'ResultCode' => 0,
                'ResultDesc' => 'The service request is processed successfully.',
                'OriginatorConversationID' => 'AG-ORIG-1',
                'ConversationID' => 'AG-CONV-1',
                'TransactionID' => 'SHK3XY8ZT9',
                'ResultParameters' => [
                    'ResultParameter' => collect($parameters)
                        ->map(fn ($value, $key) => ['Key' => $key, 'Value' => $value])
                        ->values()
                        ->all(),
                ],
            ], $overrides),
        ];
    }

    public function test_it_queries_safaricom_and_waits_for_the_callback(): void
    {
        $this->fakeDaraja();

        $response = $this->postJson('/mpesa/verification/query', ['code' => 'shk3xy8zt9', 'phone' => '254712345678']);

        $response->assertOk()->assertJson([
            'status' => 'pending',
            'code' => 'SHK3XY8ZT9',
        ]);

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/mpesa/transactionstatus/v1/query')) {
                return false;
            }

            return $request['CommandID'] === 'TransactionStatusQuery'
                && $request['TransactionID'] === 'SHK3XY8ZT9'
                && $request['PartyA'] === '7425170'
                && $request['IdentifierType'] === '4';
        });

        $this->assertDatabaseHas('mpesa_verifications', [
            'transaction_code' => 'SHK3XY8ZT9',
            'status' => 'pending',
            'originator_conversation_id' => 'AG-ORIG-1',
        ]);
    }

    public function test_a_successful_result_callback_confirms_the_payment(): void
    {
        $this->fakeDaraja();

        $token = $this->postJson('/mpesa/verification/query', ['code' => 'SHK3XY8ZT9'])->json('token');

        $this->postJson('/mpesa/verification/callback/result', $this->resultPayload())
            ->assertOk()
            ->assertJson(['ResultCode' => 0]);

        $this->getJson("/mpesa/verification/status/{$token}")
            ->assertOk()
            ->assertJson([
                'status' => 'confirmed',
                'code' => 'SHK3XY8ZT9',
                'amount' => 1000.0,
                'paid_at' => '2026-08-21',
            ]);
    }

    public function test_an_unknown_code_is_rejected(): void
    {
        $this->fakeDaraja();

        $token = $this->postJson('/mpesa/verification/query', ['code' => 'SHK3XY8ZT9'])->json('token');

        $this->postJson('/mpesa/verification/callback/result', $this->resultPayload([
            'ResultCode' => 1,
            'ResultDesc' => 'The initiator information is invalid.',
        ]))->assertOk();

        $this->getJson("/mpesa/verification/status/{$token}")
            ->assertOk()
            ->assertJsonPath('status', 'failed');
    }

    public function test_an_underpayment_is_rejected(): void
    {
        $this->fakeDaraja();

        $token = $this->postJson('/mpesa/verification/query', ['code' => 'SHK3XY8ZT9'])->json('token');

        $this->postJson('/mpesa/verification/callback/result', $this->resultPayload(parameterOverrides: ['Amount' => 200]))
            ->assertOk();

        $response = $this->getJson("/mpesa/verification/status/{$token}")->assertOk();

        $this->assertSame('failed', $response->json('status'));
        $this->assertStringContainsString('less than the application fee', $response->json('message'));
    }

    public function test_a_payment_to_the_store_number_is_accepted(): void
    {
        $this->fakeDaraja();

        $token = $this->postJson('/mpesa/verification/query', ['code' => 'SHK3XY8ZT9'])->json('token');

        // Some till results credit the head office / store number instead.
        $this->postJson('/mpesa/verification/callback/result', $this->resultPayload(
            parameterOverrides: ['CreditPartyName' => '7425170 - GoCare Training Institute']
        ))->assertOk();

        $this->getJson("/mpesa/verification/status/{$token}")->assertJsonPath('status', 'confirmed');
    }

    public function test_a_payment_to_another_till_is_rejected(): void
    {
        $this->fakeDaraja();

        $token = $this->postJson('/mpesa/verification/query', ['code' => 'SHK3XY8ZT9'])->json('token');

        $this->postJson('/mpesa/verification/callback/result', $this->resultPayload(
            parameterOverrides: ['CreditPartyName' => '888888 - Someone Else']
        ))->assertOk();

        $response = $this->getJson("/mpesa/verification/status/{$token}")->assertOk();

        $this->assertSame('failed', $response->json('status'));
        $this->assertStringContainsString('not made to the GoCare till', $response->json('message'));
    }

    public function test_a_timeout_callback_marks_the_verification_timed_out(): void
    {
        $this->fakeDaraja();

        $token = $this->postJson('/mpesa/verification/query', ['code' => 'SHK3XY8ZT9'])->json('token');

        $this->postJson('/mpesa/verification/callback/timeout', $this->resultPayload())->assertOk();

        $this->getJson("/mpesa/verification/status/{$token}")
            ->assertOk()
            ->assertJsonPath('status', 'timed_out');
    }

    public function test_a_malformed_code_never_reaches_safaricom(): void
    {
        $this->fakeDaraja();

        $this->postJson('/mpesa/verification/query', ['code' => 'NOPE'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('code');

        Http::assertNothingSent();
    }

    public function test_a_confirmed_code_cannot_be_claimed_by_another_application(): void
    {
        MpesaVerification::create([
            'public_token' => (string) Str::uuid(),
            'transaction_code' => 'SHK3XY8ZT9',
            'application_reference' => 'GC-20260821-AAAAAA',
            'claim_key' => 'GC-20260821-AAAAAA',
            'status' => MpesaVerification::STATUS_CONFIRMED,
        ]);

        $this->fakeDaraja();

        $this->postJson('/mpesa/verification/query', ['code' => 'SHK3XY8ZT9', 'reference' => 'GC-20260821-BBBBBB'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'This confirmation code has already been used for another application.');

        Http::assertNothingSent();
    }

    public function test_missing_credentials_surface_a_clean_error(): void
    {
        config()->set('application_payments.daraja.consumer_key', null);
        Http::fake();

        $response = $this->postJson('/mpesa/verification/query', ['code' => 'SHK3XY8ZT9'])
            ->assertStatus(422)
            ->assertJsonPath('status', 'failed');

        // The applicant must not be shown which credentials are missing.
        $this->assertStringNotContainsString('consumer_key', $response->json('message'));

        Http::assertNothingSent();
    }

    public function test_callbacks_without_the_configured_secret_are_not_found(): void
    {
        config()->set('application_payments.daraja.callback_secret', 's3cr3t');
        $this->fakeDaraja();

        $token = $this->postJson('/mpesa/verification/query', ['code' => 'SHK3XY8ZT9'])->json('token');

        $this->postJson('/mpesa/verification/callback/result', $this->resultPayload())->assertNotFound();
        $this->postJson('/mpesa/verification/callback/result/wrong', $this->resultPayload())->assertNotFound();

        $this->getJson("/mpesa/verification/status/{$token}")->assertJsonPath('status', 'pending');

        $this->postJson('/mpesa/verification/callback/result/s3cr3t', $this->resultPayload())->assertOk();

        $this->getJson("/mpesa/verification/status/{$token}")->assertJsonPath('status', 'confirmed');
    }
}
