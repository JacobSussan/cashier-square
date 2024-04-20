<?php

namespace Laravel\Cashier;

use Laravel\Cashier\Exceptions\InvalidCustomerBalanceTransaction;
use Square\Models\CustomerBalanceTransaction as SquareCustomerBalanceTransaction;

class CustomerBalanceTransaction
{
    /**
     * The Square model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $owner;

    /**
     * The Square CustomerBalanceTransaction instance.
     *
     * @var \Square\Models\CustomerBalanceTransaction
     */
    protected $transaction;

    /**
     * Create a new CustomerBalanceTransaction instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $owner
     * @param  \Square\Models\CustomerBalanceTransaction  $transaction
     * @return void
     *
     * @throws \Laravel\Cashier\Exceptions\InvalidCustomerBalanceTransaction
     */
    public function __construct($owner, SquareCustomerBalanceTransaction $transaction)
    {
        if ($owner->square_id !== $transaction->getCustomerId()) {
            throw InvalidCustomerBalanceTransaction::invalidOwner($transaction, $owner);
        }

        $this->owner = $owner;
        $this->transaction = $transaction;
    }

    /**
     * Get the total transaction amount.
     *
     * @return string
     */
    public function amount()
    {
        return $this->formatAmount($this->rawAmount());
    }

    /**
     * Get the raw total transaction amount.
     *
     * @return int
     */
    public function rawAmount()
    {
        return $this->transaction->getAmountMoney()->getAmount();
    }

    /**
     * Get the ending balance.
     *
     * @return string
     */
    public function endingBalance()
    {
        return $this->formatAmount($this->rawEndingBalance());
    }

    /**
     * Get the raw ending balance.
     *
     * @return int
     */
    public function rawEndingBalance()
    {
        return $this->transaction->getEndingBalanceMoney()->getAmount();
    }

    /**
     * Format the given amount into a displayable currency.
     *
     * @param  int  $amount
     * @return string
     */
    protected function formatAmount($amount)
    {
        return Cashier::formatAmount($amount, $this->transaction->getAmountMoney()->getCurrency());
    }

    /**
     * Return the related invoice for this transaction.
     *
     * @return \Laravel\Cashier\Invoice
     */
    public function invoice()
    {
        return $this->transaction->getInvoiceId()
            ? $this->owner->findInvoice($this->transaction->getInvoiceId())
            : null;
    }

    /**
     * Get the Square CustomerBalanceTransaction instance.
     *
     * @return \Square\Models\CustomerBalanceTransaction
     */
    public function asSquareCustomerBalanceTransaction()
    {
        return $this->transaction;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->asSquareCustomerBalanceTransaction()->jsonSerialize();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Dynamically get values from the Square object.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->transaction->{'get'.ucfirst($key)}();
    }
}
