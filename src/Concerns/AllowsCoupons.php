<?php

namespace Laravel\Cashier\Concerns;

trait AllowsCoupons
{
    /**
     * The coupon ID being applied.
     *
     * @var string|null
     */
    public $couponId;

    /**
     * The promotion code ID being applied.
     *
     * @var string|null
     */
    public $promotionCodeId;

    /**
     * Determines if user redeemable promotion codes are available in Square Checkout.
     *
     * @var bool
     */
    public $allowPromotionCodes = false;

    /**
     * The coupon ID to be applied.
     *
     * @param  string  $couponId
     * @return $this
     */
    public function withCoupon($couponId)
    {
        $this->couponId = $couponId;

        return $this;
    }

    /**
     * The promotion code ID to apply.
     *
     * @param  string  $promotionCodeId
     * @return $this
     */
    public function withPromotionCode($promotionCodeId)
    {
        $this->promotionCodeId = $promotionCodeId;

        return $this;
    }

    /**
     * Enables user redeemable promotion codes for a Square Checkout session.
     *
     * @return $this
     */
    public function allowPromotionCodes()
    {
        $this->allowPromotionCodes = true;

        return $this;
    }

    /**
     * Return the discounts for a Square Checkout session.
     *
     * @return array[]|null
     */
    protected function checkoutDiscounts()
    {
        if ($this->couponId) {
            return [['coupon' => $this->couponId]];
        }

        if ($this->promotionCodeId) {
            return [['promotion_code' => $this->promotionCodeId]];
        }
    }
}
