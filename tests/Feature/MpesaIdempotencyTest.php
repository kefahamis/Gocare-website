<?php

namespace Tests\Feature;

use App\Mail\ApplicationReceived;
use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Repeated deliveries and repeated clicks must not double-charge, double-record
 * or double-notify — while the STK prompt stays deliberately re-sendable.
 */
class MpesaIdempotencyTest extends TestCase
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
        config()->set('mpesa.passkey', 'passkey');
        config()->set('mpesa.type', 'till');
        config()->set('mpesa.shortcode', '7425170');
        config()->set('mpesa.till_number', '9373479');
        config()->set('mpesa.callback_url', 'https://gocare.test/mpesa/callback');
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
            'api.safaricom.co.ke/mpesa/stkpush/v1/processrequest' => Http::sequence()
                ->push(['CheckoutRequestID' => 'ws_CO_1', 'ResponseCode' => '0'])
                ->push(['CheckoutRequestID' => 'ws_CO_2', 'ResponseCode' => '0'])
                ->whenEmpty(Http::response(['CheckoutRequestID' => 'ws_CO_3', 'ResponseCode' => '0'])),
            'api.safaricom.co.ke/mpesa/transactionstatus/v1/query' => Http::response([
                'OriginatorConversationID' => 'AG-ORIG-1',
                'ConversationID' => 'AG-CONV-1',
                'ResponseCode' => '0',
            ]),
        ]);
    }

    private function stkCallback(string $checkoutRequestId, int $resultCode = 0, string $receipt = 'STK1234567'): array
    {
        return [
            'Body' => [
                'stkCallback' => [
                    'MerchantRequestID' => 'mr-1',
                    'CheckoutRequestID' => $checkoutRequestId,
                    'ResultCode' => $resultCode,
                    'ResultDesc' => $resultCode === 0 ? 'The service request is processed successfully.' : 'Cancelled by user',
                    'CallbackMetadata' => [
                        'Item' => [
                            ['Name' => 'Amount', 'Value' => 1000],
                            ['Name' => 'MpesaReceiptNumber', 'Value' => $receipt],
                            ['Name' => 'PhoneNumber', 'Value' => 254712345678],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function statusResult(string $originator = 'AG-ORIG-1'): array
    {
        $parameters = [
            'ReceiptNo' => 'SHK3XY8ZT9',
            'TransactionStatus' => 'Completed',
            'Amount' => 1000,
            'CreditPartyName' => '9373479 - GoCare Training Institute',
        ];

        $items = [];

        foreach ($parameters as $key => $value) {
            $items[] = ['Key' => $key, 'Value' => $value];
        }

        return [
            'Result' => [
                'ResultCode' => 0,
                'ResultDesc' => 'The service request is processed successfully.',
                'OriginatorConversationID' => $originator,
                'ConversationID' => 'AG-CONV-1',
                'TransactionID' => 'SHK3XY8ZT9',
                'ResultParameters' => ['ResultParameter' => $items],
            ],
        ];
    }

    public function test_tapping_send_again_reuses_the_same_application(): void
    {
        $this->fakeDaraja();

        $first = $this->postJson('/applications', ['phone' => '254712345678'])->assertOk();
        $second = $this->postJson('/applications', ['phone' => '254712345678'])->assertOk();

        // One applicant, one application - not an orphaned row per attempt.
        $this->assertSame(1, Application::count());
        $this->assertSame($first->json('reference'), $second->json('reference'));
    }

    public function test_a_retry_still_sends_a_fresh_stk_prompt(): void
    {
        $this->fakeDaraja();

        $this->postJson('/applications', ['phone' => '254712345678'])->assertOk();
        $this->postJson('/applications', ['phone' => '254712345678'])->assertOk();

        // The prompt must stay re-sendable: the UI invites "tap to try again".
        $pushes = 0;

        Http::assertSent(function ($request) use (&$pushes) {
            if (str_contains($request->url(), '/mpesa/stkpush/v1/processrequest')) {
                $pushes++;
            }

            return true;
        });

        $this->assertSame(2, $pushes);
    }

    public function test_a_retry_after_a_failed_attempt_resets_the_row_to_pending(): void
    {
        $this->fakeDaraja();

        $reference = $this->postJson('/applications', ['phone' => '254712345678'])->json('reference');
        $application = Application::where('reference', $reference)->first();
        $application->update(['payment_status' => 'failed']);

        $this->postJson('/applications', ['phone' => '254712345678'])->assertOk();

        $this->assertSame('pending', $application->refresh()->payment_status);
    }

    public function test_a_retry_does_not_discard_a_code_awaiting_verification(): void
    {
        $this->fakeDaraja();

        $reference = $this->postJson('/applications', ['phone' => '254712345678'])->json('reference');
        $application = Application::where('reference', $reference)->first();
        $application->update(['payment_status' => 'awaiting_verification', 'mpesa_receipt' => 'SHK3XY8ZT9']);

        $this->postJson('/applications', ['phone' => '254712345678'])->assertOk();

        $application->refresh();
        $this->assertSame('awaiting_verification', $application->payment_status);
        $this->assertSame('SHK3XY8ZT9', $application->mpesa_receipt);
    }

    public function test_an_already_paid_application_is_never_pushed_again(): void
    {
        $this->fakeDaraja();

        $reference = $this->postJson('/applications', ['phone' => '254712345678'])->json('reference');
        Application::where('reference', $reference)->update(['payment_status' => 'paid']);

        Http::fake([
            'api.safaricom.co.ke/*' => Http::response([], 500),
        ]);

        $this->postJson('/applications', ['phone' => '254712345678'])
            ->assertOk()
            ->assertJson(['payment_status' => 'paid']);

        $this->assertSame(1, Application::count());
        Http::assertNothingSent();
    }

    public function test_a_callback_for_an_earlier_prompt_still_settles_the_application(): void
    {
        $this->fakeDaraja();

        // Prompt did not arrive, so the applicant taps again - then pays the
        // first prompt after all.
        $reference = $this->postJson('/applications', ['phone' => '254712345678'])->json('reference');
        $this->postJson('/applications', ['phone' => '254712345678'])->assertOk();

        $this->postJson('/mpesa/callback', $this->stkCallback('ws_CO_1'))->assertOk();

        $application = Application::where('reference', $reference)->first();
        $this->assertSame('paid', $application->payment_status);
        $this->assertSame(['ws_CO_1', 'ws_CO_2'], $application->checkout_request_ids);
        Mail::assertSent(ApplicationReceived::class, 1);
    }

    public function test_a_sent_prompt_is_not_reported_as_failed_when_it_cannot_be_stored(): void
    {
        $this->fakeDaraja();

        // Reproduces a schema that is behind the code: the write after the push
        // fails, but the prompt is already on the applicant's phone.
        Schema::table('applications', function ($table) {
            $table->dropColumn('checkout_request_ids');
        });

        $this->postJson('/applications', ['phone' => '254712345678'])
            ->assertOk()
            ->assertJson([
                'message' => 'Payment initiated. Please enter your M-Pesa PIN.',
                'checkout_request_id' => 'ws_CO_1',
            ]);

        // Telling the applicant it failed would only push them into asking for
        // a second prompt for a payment that is already in flight.
        $this->assertSame(1, Application::count());
    }

    public function test_a_repeated_stk_callback_notifies_only_once(): void
    {
        $this->fakeDaraja();

        $reference = $this->postJson('/applications', ['phone' => '254712345678'])->json('reference');

        $this->postJson('/mpesa/callback', $this->stkCallback('ws_CO_1'))->assertOk();
        $this->postJson('/mpesa/callback', $this->stkCallback('ws_CO_1'))->assertOk();
        $this->postJson('/mpesa/callback', $this->stkCallback('ws_CO_1'))->assertOk();

        $application = Application::where('reference', $reference)->first();
        $this->assertSame('paid', $application->payment_status);
        $this->assertSame('STK1234567', $application->mpesa_receipt);

        Mail::assertSent(ApplicationReceived::class, 1);
    }

    public function test_a_late_failed_stk_callback_cannot_unpay_an_application(): void
    {
        $this->fakeDaraja();

        $reference = $this->postJson('/applications', ['phone' => '254712345678'])->json('reference');

        $this->postJson('/mpesa/callback', $this->stkCallback('ws_CO_1'))->assertOk();
        $this->postJson('/mpesa/callback', $this->stkCallback('ws_CO_1', resultCode: 1032))->assertOk();

        $this->assertSame('paid', Application::where('reference', $reference)->first()->payment_status);
        Mail::assertSent(ApplicationReceived::class, 1);
    }

    public function test_a_repeated_status_result_notifies_only_once(): void
    {
        $this->fakeDaraja();

        $reference = $this->postJson('/applications', ['phone' => '254712345678'])->json('reference');
        $this->postJson('/applications/paid', ['transaction_code' => 'SHK3XY8ZT9'])->assertOk();

        $this->postJson('/mpesa/status/result', $this->statusResult())->assertOk();
        $this->postJson('/mpesa/status/result', $this->statusResult())->assertOk();

        $this->assertSame('paid', Application::where('reference', $reference)->first()->payment_status);
        Mail::assertSent(ApplicationReceived::class, 1);
    }

    public function test_an_stk_callback_after_a_status_result_does_not_notify_twice(): void
    {
        $this->fakeDaraja();

        $this->postJson('/applications', ['phone' => '254712345678'])->assertOk();
        $this->postJson('/applications/paid', ['transaction_code' => 'SHK3XY8ZT9'])->assertOk();

        $this->postJson('/mpesa/status/result', $this->statusResult())->assertOk();
        $this->postJson('/mpesa/callback', $this->stkCallback('ws_CO_1'))->assertOk();

        Mail::assertSent(ApplicationReceived::class, 1);
    }

    public function test_resubmitting_the_same_code_does_not_requery_safaricom(): void
    {
        $this->fakeDaraja();

        $this->postJson('/applications', ['phone' => '254712345678'])->assertOk();

        $this->postJson('/applications/paid', ['transaction_code' => 'SHK3XY8ZT9'])
            ->assertOk()
            ->assertJson(['verifying' => true]);

        $this->postJson('/applications/paid', ['transaction_code' => 'SHK3XY8ZT9'])
            ->assertOk()
            ->assertJson(['verifying' => true]);

        $queries = 0;

        Http::assertSent(function ($request) use (&$queries) {
            if (str_contains($request->url(), '/mpesa/transactionstatus/v1/query')) {
                $queries++;
            }

            return true;
        });

        $this->assertSame(1, $queries);
    }

    public function test_a_different_code_is_queried_even_while_one_is_in_flight(): void
    {
        $this->fakeDaraja();

        $this->postJson('/applications', ['phone' => '254712345678'])->assertOk();
        $this->postJson('/applications/paid', ['transaction_code' => 'SHK3XY8ZT9'])->assertOk();

        // Correcting a typo must still reach Safaricom.
        $this->postJson('/applications/paid', ['transaction_code' => 'QGH7YT2WQ1'])->assertOk();

        $queries = 0;

        Http::assertSent(function ($request) use (&$queries) {
            if (str_contains($request->url(), '/mpesa/transactionstatus/v1/query')) {
                $queries++;
            }

            return true;
        });

        $this->assertSame(2, $queries);
    }

    public function test_the_same_code_is_requeried_once_the_in_flight_window_lapses(): void
    {
        $this->fakeDaraja();

        $reference = $this->postJson('/applications', ['phone' => '254712345678'])->json('reference');
        $this->postJson('/applications/paid', ['transaction_code' => 'SHK3XY8ZT9'])->assertOk();

        // Safaricom never answered; the applicant tries again later.
        Application::where('reference', $reference)->update(['status_queried_at' => now()->subMinutes(5)]);

        $this->postJson('/applications/paid', ['transaction_code' => 'SHK3XY8ZT9'])->assertOk();

        $queries = 0;

        Http::assertSent(function ($request) use (&$queries) {
            if (str_contains($request->url(), '/mpesa/transactionstatus/v1/query')) {
                $queries++;
            }

            return true;
        });

        $this->assertSame(2, $queries);
    }
}
