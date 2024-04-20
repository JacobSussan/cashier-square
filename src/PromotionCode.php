<?php

namespace Laravel\Cashier;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use Square\Models\Promotion as SquarePromotion;

class PromotionCode implements Arrayable, Jsonable, JsonSerializable
{
    /**
     * The Square Promotion instance.
     *
     * @var \Square\Models\Promotion
     */
    protected $promotion;

    /**
     * Create a new PromotionCode instance.
     *
     * @param  \Square\Models\Promotion  $promotion
     * @return void
     */
    public function __construct(SquarePromotion $promotion)
    {
        $this->promotion = $promotion;
    }

    /**
     * Get the coupon that belongs to the promotion code.
     *
     * @return \Laravel\Cashier\Coupon
     */
    public function coupon()
    {
        // Assuming there's a similar method to get a coupon from a promotion in Square
        return new Coupon($this->promotion->getCoupon());
    }

    /**
     * Get the Square Promotion instance.
     *
     * @return \Square\Models\Promotion
     */
    public function asSquarePromotion()
    {
        return $this->promotion;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        // Assuming there's a similar method to convert a promotion to an array in Square
        return $this->asSquarePromotion()->toArray();
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
        // Assuming the Square SDK provides a similar dynamic property access
        return $this->promotion->{$key};
    }
}
