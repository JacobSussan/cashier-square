<?php

namespace Laravel\Cashier\Exceptions;

use Exception;
use Square\Invoice as SquareInvoice;

class InvalidInvoice extends Exception
{
    /**
     * Create a new InvalidInvoice instance.
     *
     * @param  \Square\Invoice  $invoice
     * @param  \Illuminate\Database\Eloquent\Model  $owner
     * @return static
     */
    public static function invalidOwner(SquareInvoice $invoice, $owner)
    {
        return new static("The invoice `{$invoice->id}` does not belong to this customer `$owner->square_id`.");
    }
}
