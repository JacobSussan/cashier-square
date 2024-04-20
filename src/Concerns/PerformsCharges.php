<?php

namespace App\Square\Concerns;

use App\Square\Payment;
use Square\Exceptions\ApiException;

trait PerformsCharges
{
    use AllowsCoupons;

    /**
     * Make a "one off" charge on the customer for the given amount.
     *
     * @param  int  $amount
     * @param  string  $paymentMethod
     * @param  array  $options
     * @return \App\Square\Payment
     *
     * @throws \App\Square\Exceptions\IncompletePayment
     */
    public function charge($amount, $paymentMethod, array $options = [])
    {
        $options = array_merge([
            'autocomplete' => true,
        ], $options);

        $options['source_id'] = $paymentMethod;

        $payment = $this->createPayment($amount, $options);

        $payment->validate();

        return $payment;
    }

    /**
     * Create a new Payment instance with a Square Payment.
     *
     * @param  int  $amount
     * @param  array  $options
     * @return \App\Square\Payment
     */
    public function createPayment($amount, array $options = [])
    {
        $options = array_merge([
            'currency' => $this->preferredCurrency(),
        ], $options);

        $options['amount_money'] = [
            'amount' => $amount,
            'currency' => $options['currency'],
        ];

        if ($this->hasSquareCustomerId()) {
            $options['customer_id'] = $this->square_customer_id;
        }

        try {
            $squarePayment = static::square()->payments->createPayment($options);
        } catch (ApiException $e) {
            throw new IncompletePayment($e->getMessage());
        }

        return new Payment($squarePayment);
    }

    /**
     * Refund a customer for a charge.
     *
     * @param  string  $paymentId
     * @param  array  $options
     * @return \Square\Models\Refund
     */
    public function refund($paymentId, array $options = [])
    {
        $options = array_merge([
            'payment_id' => $paymentId,
            'amount_money' => [
                'amount' => $options['amount'],
                'currency' => $this->preferredCurrency(),
            ],
        ], $options);

        try {
            $refund = static::square()->refunds->refundPayment($options);
        } catch (ApiException $e) {
            throw new IncompletePayment($e->getMessage());
        }

        return $refund;
    }
}
