<?php

namespace Laravel\Cashier\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Laravel\Cashier\Subscription;
use Laravel\Cashier\SubscriptionItem;

class SubscriptionItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SubscriptionItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subscription_id' => Subscription::factory(),
            'square_id' => 'si_'.Str::random(40),
            'square_product' => 'prod_'.Str::random(40),
            'square_price' => 'price_'.Str::random(40),
            'quantity' => null,
        ];
    }
}
