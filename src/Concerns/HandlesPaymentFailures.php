<?php

namespace App\Concerns;

use App\Exceptions\IncompletePayment;
use App\Payment;
use App\Subscription;
use Square\Exceptions\ApiException;
use Square\Models\Payment as SquarePayment;

trait HandlesPaymentFailures
{
    /**
     * Indicates if incomplete payments should be confirmed automatically.
     *
     * @var bool
     */
    protected $confirmIncompletePayment = true;

    /**
     * The options to be used when confirming a payment.
     *
     * @var array
     */
    protected $paymentConfirmationOptions = [];

    /**
     * Handle a failed payment for the given subscription.
     *
     * @param  \App\Subscription  $subscription
     * @param  string|null  $paymentMethodId
     * @return void
     *
     * @throws \App\Exceptions\IncompletePayment
     *
     * @internal
     */
    public function handlePaymentFailure(Subscription $subscription, $paymentMethodId = null)
    {
        if ($this->confirmIncompletePayment && $subscription->hasIncompletePayment()) {
            try {
                $subscription->latestPayment()->validate();
            } catch (IncompletePayment $e) {
                if ($e->payment->requiresConfirmation()) {
                    try {
                        $payment = new SquarePayment($this->paymentConfirmationOptions);
                        $payment->setSourceId($paymentMethodId);
                        $payment->setAutocomplete(true);
                        $payment->setCustomerId($subscription->customer_id);
                        $payment->setReferenceId($subscription->id);
                        $payment->setAmountMoney($e->payment->getAmountMoney());

                        $result = $this->getSquareClient()->getPaymentsApi()->createPayment($payment);

                        $subscription->updateStatus($result->getPayment()->getStatus());
                    } catch (ApiException $exception) {
                        // Handle Square API exception
                    }

                    if ($subscription->hasIncompletePayment()) {
                        (new Payment($result->getPayment()))->validate();
                    }
                } else {
                    throw $e;
                }
            }
        }

        $this->confirmIncompletePayment = true;
        $this->paymentConfirmationOptions = [];
    }

    /**
     * Prevent automatic confirmation of incomplete payments.
     *
     * @return $this
     */
    public function ignoreIncompletePayments()
    {
        $this->confirmIncompletePayment = false;

        return $this;
    }

    /**
     * Specify the options to be used when confirming a payment.
     *
     * @param  array  $options
     * @return $this
     */
    public function withPaymentConfirmationOptions(array $options)
    {
        $this->paymentConfirmationOptions = $options;

        return $this;
    }

    /**
     * Get the Square client instance.
     *
     * @return \Square\SquareClient
     */
    protected function getSquareClient()
    {
        // Return the Square client instance
    }
}
