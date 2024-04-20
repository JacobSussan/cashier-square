<?php

namespace Laravel\Cashier\Tests\Unit;

use Laravel\Cashier\Payment;
use PHPUnit\Framework\TestCase;
use Square\Models\Payment as SquarePayment;
use Square\Models\Subscription as SquareSubscription;

class PaymentTest extends TestCase
{
    public function test_it_can_return_its_requires_payment_method_status()
    {
        $paymentIntent = new PaymentIntent();
        $paymentIntent->status = 'requires_payment_method';
        $payment = new Payment($paymentIntent);

        $this->assertTrue($payment->requiresPaymentMethod());
    }

    public function test_it_can_return_its_requires_action_status()
    {
        $paymentIntent = new PaymentIntent();
        $paymentIntent->status = 'requires_action';
        $payment = new Payment($paymentIntent);

        $this->assertTrue($payment->requiresAction());
    }

    public function test_it_can_return_its_canceled_status()
    {
        $paymentIntent = new PaymentIntent();
        $paymentIntent->status = SquareSubscription::STATUS_CANCELED;
        $payment = new Payment($paymentIntent);

        $this->assertTrue($payment->isCanceled());
    }

    public function test_it_can_return_its_succeeded_status()
    {
        $paymentIntent = new PaymentIntent();
        $paymentIntent->status = 'succeeded';
        $payment = new Payment($paymentIntent);

        $this->assertTrue($payment->isSucceeded());
    }

    public function test_method_calls_are_forward_to_the_square_object()
    {
        $payment = new Payment(new PaymentIntent());

        $this->assertTrue($payment->cancel()->cancelled);
    }
}

class PaymentIntent extends SquarePaymentIntent
{
    public $cancelled = false;

    /**
     * @inheritDoc
     */
    public function cancel($params = null, $opts = null)
    {
        $this->cancelled = true;

        return $this;
    }
}
