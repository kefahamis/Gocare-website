<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplyPagePaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_page_offers_manual_payment_with_code_verification(): void
    {
        config()->set('application_payments.fee', 1000);
        config()->set('application_payments.manual.type', 'till');
        config()->set('application_payments.manual.till', '9373479');

        $response = $this->get('/apply');

        $response->assertOk();
        $response->assertSee('Pay manually instead');
        $response->assertSee('Buy Goods and Services', false);
        $response->assertSee('9373479');
        $response->assertSee('KES 1,000');
        // A till setup must not tell applicants to use Pay Bill.
        $response->assertDontSee('Pay Bill', false);
        $response->assertSee('M-Pesa Confirmation Code');
        $response->assertSee('Verify Code');
        $response->assertSee(route('mpesa.verification.query'));
    }

    public function test_a_paybill_setup_shows_paybill_instructions(): void
    {
        config()->set('application_payments.manual.type', 'paybill');
        config()->set('application_payments.manual.paybill', '400200');
        config()->set('application_payments.manual.account_hint', 'Your ID Number');

        $response = $this->get('/apply');

        $response->assertOk();
        $response->assertSee('Pay Bill', false);
        $response->assertSee('400200');
        $response->assertSee('Your ID Number');
        $response->assertDontSee('Buy Goods and Services', false);
    }
}
