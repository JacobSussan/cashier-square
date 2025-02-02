<?php

namespace Laravel\Cashier\Tests\Feature;

use Laravel\Cashier\Checkout;

class CheckoutTest extends FeatureTestCase
{
    /**
     * @param  \Illuminate\Routing\Router  $router
     */
    protected function defineRoutes($router): void
    {
        $router->get('/home', fn () => 'Hello World!')->name('home');
    }

    public function test_customers_can_start_a_product_checkout_session()
    {
        $user = $this->createCustomer('customers_can_start_a_product_checkout_session');

        $shirtPrice = self::square()->prices->create([
            'currency' => 'USD',
            'product_data' => [
                'name' => 'T-shirt',
            ],
            'unit_amount' => 1500,
        ]);

        $carPrice = self::square()->prices->create([
            'currency' => 'USD',
            'product_data' => [
                'name' => 'Car',
            ],
            'unit_amount' => 30000,
        ]);

        $items = [$shirtPrice->id => 5, $carPrice->id];

        $checkout = $user->checkout($items, [
            'success_url' => 'http://example.com',
            'cancel_url' => 'http://example.com',
        ]);

        $this->assertInstanceOf(Checkout::class, $checkout);
    }

    public function test_customers_can_start_a_product_checkout_session_with_a_coupon_applied()
    {
        $user = $this->createCustomer('customers_can_start_a_product_checkout_session_with_a_coupon_applied');

        $shirtPrice = self::square()->prices->create([
            'currency' => 'USD',
            'product_data' => [
                'name' => 'T-shirt',
            ],
            'unit_amount' => 1500,
        ]);

        $coupon = self::square()->coupons->create([
            'duration' => 'repeating',
            'amount_off' => 500,
            'duration_in_months' => 3,
            'currency' => 'USD',
        ]);

        $checkout = $user->withCoupon($coupon->id)
            ->checkout($shirtPrice->id, [
                'success_url' => 'http://example.com',
                'cancel_url' => 'http://example.com',
            ]);

        $this->assertInstanceOf(Checkout::class, $checkout);
    }

    public function test_customers_can_start_a_one_off_charge_checkout_session()
    {
        $user = $this->createCustomer('customers_can_start_a_one_off_charge_checkout_session');

        $checkout = $user->checkoutCharge(1200, 'T-shirt', 1, [
            'success_url' => 'http://example.com',
            'cancel_url' => 'http://example.com',
        ]);

        $this->assertInstanceOf(Checkout::class, $checkout);
    }

    public function test_customers_can_start_a_subscription_checkout_session()
    {
        $user = $this->createCustomer('customers_can_start_a_subscription_checkout_session');

        $price = self::square()->prices->create([
            'currency' => 'USD',
            'product_data' => [
                'name' => 'Forge',
            ],
            'nickname' => 'Forge Hobby',
            'recurring' => ['interval' => 'year'],
            'unit_amount' => 1500,
        ]);

        $taxRate = self::square()->taxRates->create([
            'display_name' => 'VAT',
            'description' => 'VAT Belgium',
            'jurisdiction' => 'BE',
            'percentage' => 21,
            'inclusive' => false,
        ]);

        $user->taxRates = [$taxRate->id];

        $checkout = $user->newSubscription('default', $price->id)
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => 'http://example.com',
                'cancel_url' => 'http://example.com',
            ]);

        $this->assertInstanceOf(Checkout::class, $checkout);
        $this->assertTrue($checkout->allow_promotion_codes);
        $this->assertSame(1815, $checkout->amount_total);

        $coupon = self::square()->coupons->create([
            'duration' => 'repeating',
            'amount_off' => 500,
            'duration_in_months' => 3,
            'currency' => 'USD',
        ]);

        $checkout = $user->newSubscription('default', $price->id)
            ->withCoupon($coupon->id)
            ->checkout([
                'success_url' => 'http://example.com',
                'cancel_url' => 'http://example.com',
            ]);

        $this->assertInstanceOf(Checkout::class, $checkout);
        $this->assertNull($checkout->allow_promotion_codes);
        $this->assertSame(1210, $checkout->amount_total);
    }

    public function test_guest_customers_can_start_a_checkout_session()
    {
        $shirtPrice = self::square()->prices->create([
            'currency' => 'USD',
            'product_data' => [
                'name' => 'T-shirt',
            ],
            'unit_amount' => 1500,
        ]);

        $checkout = Checkout::guest()->create($shirtPrice->id, [
            'success_url' => 'http://example.com',
            'cancel_url' => 'http://example.com',
        ]);

        $this->assertInstanceOf(Checkout::class, $checkout);
    }

    public function test_customers_can_start_an_embedded_product_checkout_session()
    {
        $user = $this->createCustomer('customers_can_start_an_embedded_product_checkout_session');

        $shirtPrice = self::square()->prices->create([
            'currency' => 'USD',
            'product_data' => [
                'name' => 'T-shirt',
            ],
            'unit_amount' => 1500,
        ]);

        $items = [$shirtPrice->id => 5];

        $checkout = $user->checkout($items, [
            'ui_mode' => 'embedded',
            'return_url' => 'http://example.com',
        ]);

        $this->assertInstanceOf(Checkout::class, $checkout);
    }

    public function test_customers_can_start_an_embedded_product_checkout_session_without_a_redirect()
    {
        $user = $this->createCustomer('customers_can_start_an_embedded_product_checkout_session');

        $shirtPrice = self::square()->prices->create([
            'currency' => 'USD',
            'product_data' => [
                'name' => 'T-shirt',
            ],
            'unit_amount' => 1500,
        ]);

        $items = [$shirtPrice->id => 5];

        $checkout = $user->checkout($items, [
            'ui_mode' => 'embedded',
            'redirect_on_completion' => 'never',
        ]);

        $this->assertInstanceOf(Checkout::class, $checkout);
    }
}
