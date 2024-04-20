<?php

namespace Laravel\Cashier\Exceptions;

use Exception;
use Square\CustomerBalanceTransaction as SquareCustomerBalanceTransaction;

class InvalidCustomerBalanceTransaction extends Exception
{
    /**
     * Create a new CustomerBalanceTransaction instance.
     *
     * @param  \Square\CustomerBalanceTransaction  $transaction
     * @param  \Illuminate\Database\Eloquent\Model  $owner
     * @return static
     */
    public static function invalidOwner(SquareCustomerBalanceTransaction $transaction, $owner)
    {
        return new static("The transaction `{$transaction->id}` does not belong to customer `$owner->square_id`.");
    }
}
