<?php

namespace Laravel\Cashier;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use Square\Models\Discount as SquareDiscount;

class Discount implements Arrayable, Jsonable, JsonSerializable
{
    /**
     * The Square Discount instance.
     *
     * @var \Square\Models\Discount
     */
    protected $discount;

    /**
     * Create a new Discount instance.
     *
     * @param  \Square\Models\Discount  $discount
     * @return void
     */
    public function __construct(SquareDiscount $discount)
    {
        $this->discount = $discount;
    }

    /**
     * Get the coupon applied to the discount.
     *
     * @return \Laravel\Cashier\Coupon
     */
    public function coupon()
    {
        return new Coupon($this->discount->getCoupon());
    }

    /**
     * Get the promotion code applied to create this discount.
     *
     * @return \Laravel\Cashier\PromotionCode|null
     */
    public function promotionCode()
    {
        $promotionCode = $this->discount->getPromotionCode();
        if (! is_null($promotionCode)) {
            return new PromotionCode($promotionCode);
        }
    }

    /**
     * Get the date that the coupon was applied.
     *
     * @return \Carbon\Carbon
     */
    public function start()
    {
        return Carbon::createFromTimestamp($this->discount->getStartAt());
    }

    /**
     * Get the date that this discount will end.
     *
     * @return \Carbon\Carbon|null
     */
    public function end()
    {
        $endAt = $this->discount->getEndAt();
        if (! is_null($endAt)) {
            return Carbon::createFromTimestamp($endAt);
        }
    }

    /**
     * Get the Square Discount instance.
     *
     * @return \Square\Models\Discount
     */
    public function asSquareDiscount()
    {
        return $this->discount;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->asSquareDiscount()->jsonSerialize();
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
        return $this->discount->{"get".ucfirst($key)}();
    }
}
