<?php

namespace Laravel\Cashier;

use Square\Models\Money;
use Square\Models\Tax;

class Tax
{
    /**
     * The total tax amount.
     *
     * @var int
     */
    protected $amount;

    /**
     * The applied currency.
     *
     * @var string
     */
    protected $currency;

    /**
     * The Square Tax object.
     *
     * @var \Square\Models\Tax
     */
    protected $tax;

    /**
     * Create a new Tax instance.
     *
     * @param  int  $amount
     * @param  string  $currency
     * @param  \Square\Models\Tax  $tax
     * @return void
     */
    public function __construct($amount, $currency, Tax $tax)
    {
        $this->amount = $amount;
        $this->currency = $currency;
        $this->tax = $tax;
    }

    /**
     * Get the applied currency.
     *
     * @return string
     */
    public function currency()
    {
        return $this->currency;
    }

    /**
     * Get the total tax that was paid (or will be paid).
     *
     * @return string
     */
    public function amount()
    {
        return $this->formatAmount($this->amount);
    }

    /**
     * Get the raw total tax that was paid (or will be paid).
     *
     * @return int
     */
    public function rawAmount()
    {
        return $this->amount;
    }

    /**
     * Format the given amount into a displayable currency.
     *
     * @param  int  $amount
     * @return string
     */
    protected function formatAmount($amount)
    {
        return Cashier::formatAmount($amount, $this->currency);
    }

    /**
     * Determine if the tax is inclusive or not.
     *
     * @return bool
     */
    public function isInclusive()
    {
        return $this->tax->getInclusionType() === 'INCLUSIVE';
    }

    /**
     * @return \Square\Models\Tax
     */
    public function tax()
    {
        return $this->tax;
    }

    /**
     * Dynamically get values from the Square object.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        $method = 'get' . ucfirst($key);
        if (method_exists($this->tax, $method)) {
            return $this->tax->{$method}();
        }
        return null;
    }
}
