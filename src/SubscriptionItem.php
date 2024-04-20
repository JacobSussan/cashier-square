<?php

namespace Laravel\Cashier;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Laravel\Cashier\Concerns\HandlesPaymentFailures;
use Laravel\Cashier\Concerns\InteractsWithPaymentBehavior;
use Laravel\Cashier\Concerns\Prorates;
use Laravel\Cashier\Database\Factories\SubscriptionItemFactory;

/**
 * @property \Laravel\Cashier\Subscription|null $subscription
 */
class SubscriptionItem extends Model
{
    use HandlesPaymentFailures;
    use HasFactory;
    use InteractsWithPaymentBehavior;
    use Prorates;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Get the subscription that the item belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subscription()
    {
        $model = Cashier::$subscriptionModel;

        return $this->belongsTo($model, (new $model)->getForeignKey());
    }

    /**
     * Increment the quantity of the subscription item.
     *
     * @param  int  $count
     * @return $this
     *
     * @throws \Laravel\Cashier\Exceptions\SubscriptionUpdateFailure
     */
    public function incrementQuantity($count = 1)
    {
        $this->updateQuantity($this->quantity + $count);

        return $this;
    }

    /**
     *  Increment the quantity of the subscription item, and invoice immediately.
     *
     * @param  int  $count
     * @return $this
     *
     * @throws \Laravel\Cashier\Exceptions\IncompletePayment
     * @throws \Laravel\Cashier\Exceptions\SubscriptionUpdateFailure
     */
    public function incrementAndInvoice($count = 1)
    {
        $this->alwaysInvoice();

        $this->incrementQuantity($count);

        return $this;
    }

    /**
     * Decrement the quantity of the subscription item.
     *
     * @param  int  $count
     * @return $this
     *
     * @throws \Laravel\Cashier\Exceptions\SubscriptionUpdateFailure
     */
    public function decrementQuantity($count = 1)
    {
        $this->updateQuantity(max(1, $this->quantity - $count));

        return $this;
    }

    /**
     * Update the quantity of the subscription item.
     *
     * @param  int  $quantity
     * @return $this
     *
     * @throws \Laravel\Cashier\Exceptions\SubscriptionUpdateFailure
     */
    public function updateQuantity($quantity)
    {
        $this->subscription->guardAgainstIncomplete();

        $squareSubscriptionItem = $this->updateSquareSubscriptionItem([
            'quantity' => $quantity,
        ]);

        $this->fill([
            'quantity' => $squareSubscriptionItem->quantity,
        ])->save();

        $squareSubscription = $this->subscription->asSquareSubscription();

        if ($this->subscription->hasSinglePrice()) {
            $this->subscription->fill([
                'quantity' => $squareSubscriptionItem->quantity,
            ]);
        }

        $this->subscription->fill([
            'square_status' => $squareSubscription->status,
        ])->save();

        $this->handlePaymentFailure($this->subscription);

        return $this;
    }

    /**
     * Swap the subscription item to a new Square price.
     *
     * @param  string  $price
     * @param  array  $options
     * @return $this
     *
     * @throws \Laravel\Cashier\Exceptions\SubscriptionUpdateFailure
     */
    public function swap($price, array $options = [])
    {
        $this->subscription->guardAgainstIncomplete();

        $squareSubscriptionItem = $this->updateSquareSubscriptionItem(array_merge(
            array_filter([
                'price' => $price,
                'quantity' => $this->quantity,
                'payment_behavior' => $this->paymentBehavior(),
                'proration_behavior' => $this->prorateBehavior(),
                'tax_rates' => $this->subscription->getPriceTaxRatesForPayload($price),
            ], function ($value) {
                return ! is_null($value);
            }),
            $options));

        $this->fill([
            'square_product' => $squareSubscriptionItem->price->product,
            'square_price' => $squareSubscriptionItem->price->id,
            'quantity' => $squareSubscriptionItem->quantity,
        ])->save();

        $squareSubscription = $this->subscription->asSquareSubscription();

        if ($this->subscription->hasSinglePrice()) {
            $this->subscription->fill([
                'square_price' => $price,
                'quantity' => $squareSubscriptionItem->quantity,
            ]);
        }

        $this->subscription->fill([
            'square_status' => $squareSubscription->status,
        ])->save();

        $this->handlePaymentFailure($this->subscription);

        return $this;
    }

    /**
     * Swap the subscription item to a new Square price, and invoice immediately.
     *
     * @param  string  $price
     * @param  array  $options
     * @return $this
     *
     * @throws \App\Exceptions\IncompletePayment
     * @throws \App\Exceptions\SubscriptionUpdateFailure
     */
    public function swapAndInvoice($price, array $options = [])
    {
        $this->alwaysInvoice();

        return $this->swap($price, $options);
    }

    /**
     * Report usage for a metered product.
     *
     * @param  int  $quantity
     * @param  \DateTimeInterface|int|null  $timestamp
     * @return \Square\Models\UsageRecord
     */
    public function reportUsage($quantity = 1, $timestamp = null)
    {
        // Square does not currently support metered billing usage records
        throw new \Exception('Square does not support metered billing usage records.');
    }

    /**
     * Get the usage records for a metered product.
     *
     * @param  array  $options
     * @return \Illuminate\Support\Collection
     */
    public function usageRecords($options = [])
    {
        // Square does not currently support metered billing usage records
        throw new \Exception('Square does not support metered billing usage records.');
    }

    /**
     * Update the underlying Square subscription item information for the model.
     *
     * @param  array  $options
     * @return \Square\Models\SubscriptionItem
     */
    public function updateSquareSubscriptionItem(array $options = [])
    {
        // Square API call to update subscription item
        // This will depend on the Square SDK and implementation details
    }

    /**
     * Get the subscription as a Square subscription item object.
     *
     * @param  array  $expand
     * @return \Square\Models\SubscriptionItem
     */
    public function asSquareSubscriptionItem(array $expand = [])
    {
        // Square API call to retrieve subscription item
        // This will depend on the Square SDK and implementation details
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return SubscriptionItemFactory::new();
    }
}
