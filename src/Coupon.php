<?php

namespace Laravel\Cashier;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use Square\Models\Coupon as SquareCoupon;

class Coupon implements Arrayable, Jsonable, JsonSerializable
{
    /**
     * The Square Coupon instance.
     *
     * @var \Square\Models\Coupon
     */
    protected $coupon;

    /**
     * Create a new Coupon instance.
     *
     * @param  \Square\Models\Coupon  $coupon
     * @return void
     */
    public function __construct(SquareCoupon $coupon)
    {
        $this->coupon = $coupon;
    }

    /**
     * Get the readable name for the Coupon.
     *
     * @return string
     */
    public function name()
    {
        return $this->coupon->getName() ?: $this->coupon->getId();
    }

    /**
     * Determine if the coupon is a percentage.
     *
     * @return bool
     */
    public function isPercentage()
    {
        return ! is_null($this->coupon->getPercentage());
    }

    /**
     * Get the discount percentage for the invoice.
     *
     * @return float|null
     */
    public function percentOff()
    {
        return $this->coupon->getPercentage();
    }

    /**
     * Get the amount off for the coupon.
     *
     * @return string|null
     */
    public function amountOff()
    {
        if (! is_null($this->coupon->getAmountOff())) {
            return $this->formatAmount($this->rawAmountOff());
        }
    }

    /**
     * Get the raw amount off for the coupon.
     *
     * @return int|null
     */
    public function rawAmountOff()
    {
        return $this->coupon->getAmountOff();
    }

    /**
     * Format the given amount into a displayable currency.
     *
     * @param  int  $amount
     * @return string
     */
    protected function formatAmount($amount)
    {
        return Cashier::formatAmount($amount, $this->coupon->getCurrency());
    }

    /**
     * Get the Square Coupon instance.
     *
     * @return \Square\Models\Coupon
     */
    public function asSquareCoupon()
    {
        return $this->coupon;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->asSquareCoupon()->toArray();
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
        $getter = 'get' . ucfirst($key);
        return $this->coupon->{$getter}();
    }
}
