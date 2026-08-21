<?php

namespace Tests\Feature;

use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MpesaTransactionStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        config()->set('gocare.application_fee', 1000);
        config()->set('mpesa.env', 'live');
        config()->set('mpesa.consumer_key', 'key');
        config()->set('mpesa.consumer_secret', 'secret');
        config()->set('mpesa.type', 'till');
        config()->set('mpesa.shortcode', '7425170');
        config()->set('mpesa.till_number', '9373479');
        config()->set('mpesa.status', [
            'initiator' => 'testapi',
            'initiator_password' => null,
            'security_credential' => 'pre-encrypted-credential',
            'certificate_path' => null,
            'identifier_type' => '4',
            'result_url' => 'https://gocare.test/mpesa/status/result',
            'timeout_url' => 'https://gocare.test/mpesa/status/timeout',
        ]);
    }

    private function fakeDaraja(): void
    {
        Http::fake([
            'api.safaricom.co.ke/oauth/*' => Http::response(['access_token' => 'token', 'expires_in' => '3599']),
            'api.safaricom.co.ke/mpesa/transactionstatus/v1/query' => Http::response([
                'OriginatorConversationID' => 'AG-ORIG-1',
                'ConversationID' => 'AG-CONV-1',
                'ResponseCode' => '0',
                'ResponseDescription' => 'Accept the service request successfully.',
            ]),
        ]);
    }

    private function startApplication(array $attributes = []): Application
    {
        $application = Application::create(array_merge([
            'reference' => 'GC-20260821-AAAAAA',
            'status' => 'submitted',
            'phone' => '254712345678',
            'amount' => 1000,
            'payment_status' => 'pending',
            'data' => [],
            'submitted_at' => now(),
        ], $attributes));

        $this->withSession(['application_reference' => $application->reference]);

        return $application;
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

        $items = [];

        foreach ($parameters as $key => $value) {
            $items[] = ['Key' => $key, 'Value' => $value];
        }

        return [
            'Result' => array_merge([
                'ResultType' => 0,
                'ResultCode' => 0,
                'ResultDesc' => 'The service request is processed successfully.',
                'OriginatorConversationID' => 'AG-ORIG-1',
                'ConversationID' => 'AG-CONV-1',
                'TransactionID' => 'SHK3XY8ZT9',
                'ResultParameters' => ['ResultParameter' => $items],
            ], $overrides),
        ];
    }

    public function test_a_typed_code_is_sent_to_safaricom_for_verification(): void
    {
        $this->fakeDaraja();
        $application = $this->startApplication();

        $this->postJson('/applications/paid', ['transaction_code' => 'shk3xy8zt9'])
            ->assertOk()
            ->assertJson(['verifying' => true, 'payment_status' => 'awaiting_verification']);

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/mpesa/transactionstatus/v1/query')) {
                return false;
            }

            return $request['CommandID'] === 'TransactionStatusQuery'
                && $request['TransactionID'] === 'SHK3XY8ZT9'
                && $request['PartyA'] === '7425170'
                && $request['IdentifierType'] === '4';
        });

        $application->refresh();
        $this->assertSame('SHK3XY8ZT9', $application->mpesa_receipt);
        $this->assertSame('AG-ORIG-1', $application->status_conversation_id);
        $this->assertSame('awaiting_verification', $application->payment_status);
    }

    public function test_a_successful_result_marks_the_application_paid(): void
    {
        $this->fakeDaraja();
        $application = $this->startApplication();

        $this->postJson('/applications/paid', ['transaction_code' => 'SHK3XY8ZT9'])->assertOk();

        $this->postJson('/mpesa/status/result', $this->resultPayload())
            ->assertOk()
            ->assertJson(['ResultCode' => 0]);

        $this->assertSame('paid', $application->refresh()->payment_status);

        $this->getJson("/applications/status/{$application->reference}")
            ->assertOk()
            ->assertJson(['payment_status' => 'paid', 'payment_note' => null]);
    }

    public function test_an_unknown_code_is_not_marked_paid(): void
    {
        $this->fakeDaraja();
        $application = $this->startApplication();

        $this->postJson('/applications/paid', ['transaction_code' => 'SHK3XY8ZT9'])->assertOk();

        $this->postJson('/mpesa/status/result', $this->resultPayload([
            'ResultCode' => 1,
            'ResultDesc' => 'The transaction does not exist.',
        ]))->assertOk();

        $application->refresh();

        // Still parked for an admin rather than failed outright.
        $this->assertSame('awaiting_verification', $application->payment_status);
        $this->assertStringContainsString('could not find this confirmation code', $application->payment_note);
    }

    public function test_an_underpayment_is_not_marked_paid(): void
    {
        $this->fakeDaraja();
        $application = $this->startApplication();

        $this->postJson('/applications/paid', ['transaction_code' => 'SHK3XY8ZT9'])->assertOk();

        $this->postJson('/mpesa/status/result', $this->resultPayload(parameterOverrides: ['Amount' => 200]))
            ->assertOk();

        $application->refresh();
        $this->assertSame('awaiting_verification', $application->payment_status);
        $this->assertStringContainsString('less than the application fee', $application->payment_note);
    }

    public function test_a_payment_to_another_till_is_not_marked_paid(): void
    {
        $this->fakeDaraja();
        $application = $this->startApplication();

        $this->postJson('/applications/paid', ['transaction_code' => 'SHK3XY8ZT9'])->assertOk();

        $this->postJson('/mpesa/status/result', $this->resultPayload(
            parameterOverrides: ['CreditPartyName' => '888888 - Someone Else']
        ))->assertOk();

        $application->refresh();
        $this->assertSame('awaiting_verification', $application->payment_status);
        $this->assertStringContainsString('not made to our M-Pesa number', $application->payment_note);
    }

    public function test_the_store_number_is_accepted_as_the_credit_party(): void
    {
        $this->fakeDaraja();
        $application = $this->startApplication();

        $this->postJson('/applications/paid', ['transaction_code' => 'SHK3XY8ZT9'])->assertOk();

        $this->postJson('/mpesa/status/result', $this->resultPayload(
            parameterOverrides: ['CreditPartyName' => '7425170 - GoCare Training Institute']
        ))->assertOk();

        $this->assertSame('paid', $application->refresh()->payment_status);
    }

    public function test_a_result_cannot_settle_a_different_application(): void
    {
        $this->fakeDaraja();
        $mine = $this->startApplication();

        $other = Application::create([
            'reference' => 'GC-20260821-BBBBBB',
            'status' => 'submitted',
            'amount' => 1000,
            'payment_status' => 'pending',
            'data' => [],
            'submitted_at' => now(),
        ]);

        $this->postJson('/applications/paid', ['transaction_code' => 'SHK3XY8ZT9'])->assertOk();
        $this->postJson('/mpesa/status/result', $this->resultPayload())->assertOk();

        $this->assertSame('paid', $mine->refresh()->payment_status);
        $this->assertSame('pending', $other->refresh()->payment_status);
    }

    public function test_a_result_with_no_matching_conversation_is_ignored(): void
    {
        $this->fakeDaraja();
        $application = $this->startApplication();

        $this->postJson('/applications/paid', ['transaction_code' => 'SHK3XY8ZT9'])->assertOk();

        $this->postJson('/mpesa/status/result', $this->resultPayload(['OriginatorConversationID' => 'AG-SOMEONE-ELSE']))
            ->assertOk();

        $this->assertSame('awaiting_verification', $application->refresh()->payment_status);
    }

    public function test_a_code_confirmed_for_another_application_is_refused(): void
    {
        Application::create([
            'reference' => 'GC-20260821-CCCCCC',
            'status' => 'submitted',
            'amount' => 1000,
            'payment_status' => 'paid',
            'mpesa_receipt' => 'SHK3XY8ZT9',
            'data' => [],
            'submitted_at' => now(),
        ]);

        $this->fakeDaraja();
        $this->startApplication();

        $this->postJson('/applications/paid', ['transaction_code' => 'SHK3XY8ZT9'])
            ->assertStatus(422)
            ->assertJsonPath('error', 'This confirmation code has already been used for another application. Please check the code on your M-Pesa message.');

        Http::assertNothingSent();
    }

    public function test_a_timeout_leaves_the_claim_for_an_admin(): void
    {
        $this->fakeDaraja();
        $application = $this->startApplication();

        $this->postJson('/applications/paid', ['transaction_code' => 'SHK3XY8ZT9'])->assertOk();
        $this->postJson('/mpesa/status/timeout', $this->resultPayload())->assertOk();

        $application->refresh();
        $this->assertSame('awaiting_verification', $application->payment_status);
        $this->assertStringContainsString('did not respond in time', $application->payment_note);
    }

    public function test_an_stk_confirmed_payment_is_never_walked_back(): void
    {
        $this->fakeDaraja();
        $application = $this->startApplication(['payment_status' => 'paid', 'mpesa_receipt' => 'STKCODE123']);

        $this->postJson('/applications/paid', ['transaction_code' => 'SHK3XY8ZT9'])
            ->assertOk()
            ->assertJson(['verifying' => false, 'payment_status' => 'paid']);

        $application->refresh();
        $this->assertSame('paid', $application->payment_status);
        $this->assertSame('STKCODE123', $application->mpesa_receipt);

        Http::assertNothingSent();
    }

    public function test_without_an_initiator_it_records_the_code_exactly_as_before(): void
    {
        config()->set('mpesa.status.initiator', null);
        Http::fake();

        $application = $this->startApplication();

        $this->postJson('/applications/paid', ['transaction_code' => 'SHK3XY8ZT9'])
            ->assertOk()
            ->assertJson([
                'verifying' => false,
                'payment_status' => 'awaiting_verification',
                'message' => 'Thank you. We have recorded your payment and will confirm it shortly.',
            ]);

        $this->assertSame('SHK3XY8ZT9', $application->refresh()->mpesa_receipt);

        Http::assertNothingSent();
    }

    public function test_a_daraja_outage_still_records_the_claim(): void
    {
        Http::fake([
            'api.safaricom.co.ke/oauth/*' => Http::response(['access_token' => 'token']),
            'api.safaricom.co.ke/mpesa/transactionstatus/v1/query' => Http::response('gateway down', 500),
        ]);

        $application = $this->startApplication();

        $this->postJson('/applications/paid', ['transaction_code' => 'SHK3XY8ZT9'])
            ->assertOk()
            ->assertJson(['verifying' => false, 'payment_status' => 'awaiting_verification']);

        $this->assertSame('SHK3XY8ZT9', $application->refresh()->mpesa_receipt);
    }
}
