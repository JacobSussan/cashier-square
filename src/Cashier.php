<?php

namespace Laravel\Cashier;

use Illuminate\Database\Eloquent\SoftDeletes;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use NumberFormatter;
use Square\Client;
use Square\Models\Customer as SquareCustomer;
use Square\SquareClient;

class Cashier
{
    /**
     * The Cashier library version.
     *
     * @var string
     */
    const VERSION = '15.3.2';

    /**
     * The Square API version.
     *
     * @var string
     */
    const SQUARE_VERSION = '2023-10-16';

    /**
     * The base URL for the Square API.
     *
     * @var string
     */
    public static $apiBaseUrl = Client::DEFAULT_API_BASE;

    /**
     * The custom currency formatter.
     *
     * @var callable
     */
    protected static $formatCurrencyUsing;

    /**
     * Indicates if Cashier routes will be registered.
     *
     * @var bool
     */
    public static $registersRoutes = true;

    /**
     * Indicates if Cashier will mark past due subscriptions as inactive.
     *
     * @var bool
     */
    public static $deactivatePastDue = true;

    /**
     * Indicates if Cashier will mark incomplete subscriptions as inactive.
     *
     * @var bool
     */
    public static $deactivateIncomplete = true;

    /**
     * Indicates if Cashier will automatically calculate taxes using Square Tax.
     *
     * @var bool
     */
    public static $calculatesTaxes = false;

    /**
     * The default customer model class name.
     *
     * @var string
     */
    public static $customerModel = 'App\\Models\\User';

    /**
     * The subscription model class name.
     *
     * @var string
     */
    public static $subscriptionModel = Subscription::class;

    /**
     * The subscription item model class name.
     *
     * @var string
     */
    public static $subscriptionItemModel = SubscriptionItem::class;

    /**
     * Get the customer instance by its Square ID.
     *
     * @param  \Square\Models\Customer|string|null  $squareId
     * @return \Laravel\Cashier\Billable|null
     */
    public static function findBillable($squareId)
    {
        $squareId = $squareId instanceof SquareCustomer ? $squareId->getId() : $squareId;

        $model = static::$customerModel;

        $builder = in_array(SoftDeletes::class, class_uses_recursive($model))
            ? $model::withTrashed()
            : new $model;

        return $squareId ? $builder->where('square_id', $squareId)->first() : null;
    }

    /**
     * Get the Square SDK client.
     *
     * @param  array  $options
     * @return \Square\SquareClient
     */
    public static function square(array $options = [])
    {
        $config = array_merge([
            'accessToken' => $options['accessToken'] ?? config('cashier.secret'),
            'squareVersion' => static::SQUARE_VERSION,
            'apiBase' => static::$apiBaseUrl,
        ], $options);

        return new SquareClient([
            'accessToken' => $config['accessToken'],
            'environment' => isset($config['environment']) ? $config['environment'] : 'production',
        ]);
    }

    /**
     * Set the custom currency formatter.
     *
     * @param  callable  $callback
     * @return void
     */
    public static function formatCurrencyUsing(callable $callback)
    {
        static::$formatCurrencyUsing = $callback;
    }

    /**
     * Format the given amount into a displayable currency.
     *
     * @param  int  $amount
     * @param  string|null  $currency
     * @param  string|null  $locale
     * @param  array  $options
     * @return string
     */
    public static function formatAmount($amount, $currency = null, $locale = null, array $options = [])
    {
        if (static::$formatCurrencyUsing) {
            return call_user_func(static::$formatCurrencyUsing, $amount, $currency, $locale, $options);
        }

        $money = new Money($amount, new Currency(strtoupper($currency ?? config('cashier.currency'))));

        $locale = $locale ?? config('cashier.currency_locale');

        $numberFormatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);

        if (isset($options['min_fraction_digits'])) {
            $numberFormatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $options['min_fraction_digits']);
        }

        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies());

        return $moneyFormatter->format($money);
    }

    /**
     * Configure Cashier to not register its routes.
     *
     * @return static
     */
    public static function ignoreRoutes()
    {
        static::$registersRoutes = false;

        return new static;
    }

    /**
     * Configure Cashier to maintain past due subscriptions as active.
     *
     * @return static
     */
    public static function keepPastDueSubscriptionsActive()
    {
        static::$deactivatePastDue = false;

        return new static;
    }

    /**
     * Configure Cashier to maintain incomplete subscriptions as active.
     *
     * @return static
     */
    public static function keepIncompleteSubscriptionsActive()
    {
        static::$deactivateIncomplete = false;

        return new static;
    }

    /**
     * Configure Cashier to automatically calculate taxes using Square Tax.
     *
     * @return static
     */
    public static function calculateTaxes()
    {
        static::$calculatesTaxes = true;

        return new static;
    }

    /**
     * Set the customer model class name.
     *
     * @param  string  $customerModel
     * @return void
     */
    public static function useCustomerModel($customerModel)
    {
        static::$customerModel = $customerModel;
    }

    /**
     * Set the subscription model class name.
     *
     * @param  string  $subscriptionModel
     * @return void
     */
    public static function useSubscriptionModel($subscriptionModel)
    {
        static::$subscriptionModel = $subscriptionModel;
    }

    /**
     * Set the subscription item model class name.
     *
     * @param  string  $subscriptionItemModel
     * @return void
     */
    public static function useSubscriptionItemModel($subscriptionItemModel)
    {
        static::$subscriptionItemModel = $subscriptionItemModel;
    }
}
