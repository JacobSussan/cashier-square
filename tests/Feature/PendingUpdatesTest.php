<?php

namespace Laravel\Cashier\Tests\Feature;

use Square\Exceptions\ApiException as SquareApiException;

class PendingUpdatesTest extends FeatureTestCase
{
    /**
     * @var string
     */
    protected static $productId;

    /**
     * @var string
     */
    protected static $priceId;

    /**
     * @var string
     */
    protected static $otherPriceId;

    /**
     * @var string
     */
    protected static $premiumPriceId;

    public static function setUpBeforeClass(): void
    {
        if (! getenv('SQUARE_ACCESS_TOKEN')) {
            return;
        }

        parent::setUpBeforeClass();

        static::$productId = self::square()->catalogApi->createCatalogObject([
            'type' => 'ITEM',
            'idempotency_key' => uniqid(),
            'item_data' => [
                'name' => 'Laravel Cashier Test Product',
                'description' => 'Test product for Laravel Cashier',
                'available_online' => true,
            ],
        ])->getObject()->getId();

        static::$priceId = self::square()->catalogApi->createCatalogObject([
            'type' => 'ITEM_VARIATION',
            'idempotency_key' => uniqid(),
            'item_variation_data' => [
                'item_id' => static::$productId,
                'name' => 'Monthly $10',
                'pricing_type' => 'FIXED_PRICING',
                'price_money' => [
                    'amount' => 1000,
                    'currency' => 'USD',
                ],
            ],
        ])->getObject()->getId();

        static::$otherPriceId = self::square()->catalogApi->createCatalogObject([
            'type' => 'ITEM_VARIATION',
            'idempotency_key' => uniqid(),
            'item_variation_data' => [
                'item_id' => static::$productId,
                'name' => 'Monthly $10 Other',
                'pricing_type' => 'FIXED_PRICING',
                'price_money' => [
                    'amount' => 1000,
                    'currency' => 'USD',
                ],
            ],
        ])->getObject()->getId();

        static::$premiumPriceId = self::square()->catalogApi->createCatalogObject([
            'type' => 'ITEM_VARIATION',
            'idempotency_key' => uniqid(),
            'item_variation_data' => [
                'item_id' => static::$productId,
                'name' => 'Monthly $20 Premium',
                'pricing_type' => 'FIXED_PRICING',
                'price_money' => [
                    'amount' => 2000,
                    'currency' => 'USD',
                ],
            ],
        ])->getObject()->getId();
    }

    public function test_subscription_can_error_if_incomplete()
    {
        $user = $this->createCustomer('subscription_can_error_if_incomplete');

        $subscription = $user->newSubscription('main', static::$priceId)->create('cnon:card-nonce-ok');

        // Set a faulty card as the customer's default payment method.
        $user->updateDefaultPaymentMethod('cnon:card-nonce-requires-verification');

        try {
            // Attempt to swap and pay with a faulty card.
            $subscription = $subscription->errorIfPaymentFails()->swapAndInvoice(static::$premiumPriceId);

            $this->fail('Expected exception '.SquareApiException::class.' was not thrown.');
        } catch (SquareApiException $e) {
            // Assert that the price was not swapped.
            $this->assertEquals(static::$priceId, $subscription->refresh()->square_price);

            // Assert subscription is active.
            $this->assertTrue($subscription->active());
        }
    }

    // public function test_subscription_can_be_pending_if_incomplete()
    // {
    //     $user = $this->createCustomer('subscription_can_be_pending_if_incomplete');
    //
    //     $subscription = $user->newSubscription('main', static::$priceId)->create('pm_card_visa');
    //
    //     // Set a faulty card as the customer's default payment method.
    //     $user->updateDefaultPaymentMethod('pm_card_threeDSecure2Required');
    //
    //     try {
    //         // Attempt to swap and pay with a faulty card.
    //         $subscription = $subscription->pendingIfPaymentFails()->swapAndInvoice(static::$premiumPriceId);
    //
    //         $this->fail('Expected exception '.IncompletePayment::class.' was not thrown.');
    //     } catch (IncompletePayment $e) {
    //         // Assert that the payment needs an extra action.
    //         $this->assertTrue($e->payment->requiresAction());
    //
    //         // Assert that the price was not swapped.
    //         $this->assertEquals(static::$priceId, $subscription->refresh()->square_price);
    //
    //         // Assert subscription is active.
    //         $this->assertTrue($subscription->active());
    //
    //         // Assert subscription has pending updates.
    //         $this->assertTrue($subscription->pending());
    //
    //         // Void the last invoice to cancel any pending updates.
    //         $subscription->latestInvoice()->void();
    //
    //         // Assert subscription has no more pending updates.
    //         $this->assertFalse($subscription->pending());
    //     }
    // }
}
