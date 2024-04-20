<?php

namespace App\Exceptions;

use Exception;
use Square\Models\Payment as SquarePayment;

class InvalidPaymentMethod extends Exception
{
    /**
     * Create a new InvalidPaymentMethod instance.
     *
     * @param  \Square\Models\Payment  $payment
     * @param  \Illuminate\Database\Eloquent\Model  $owner
     * @return static
     */
    public static function invalidOwner(SquarePayment $payment, $owner)
    {
        return new static(
            "The payment method `{$payment->getId()}`'s customer `{$payment->getCustomerId()}` does not belong to this customer `$owner->square_id`."
        );
    }
}
