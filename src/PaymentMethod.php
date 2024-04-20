<?php

namespace Laravel\Cashier;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use Laravel\Cashier\Exceptions\InvalidPaymentMethod;
use LogicException;
use Square\Models\Payment as SquarePayment;

class PaymentMethod implements Arrayable, Jsonable, JsonSerializable
{
    /**
     * The Square model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $owner;

    /**
     * The Square Payment instance.
     *
     * @var \Square\Models\Payment
     */
    protected $paymentMethod;

    /**
     * Create a new PaymentMethod instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $owner
     * @param  \Square\Models\Payment  $paymentMethod
     * @return void
     *
     * @throws \Laravel\Cashier\Exceptions\InvalidPaymentMethod
     */
    public function __construct($owner, SquarePayment $paymentMethod)
    {
        if (is_null($paymentMethod->getCustomerId())) {
            throw new LogicException('The payment method is not attached to a customer.');
        }

        if ($owner->square_id !== $paymentMethod->getCustomerId()) {
            throw InvalidPaymentMethod::invalidOwner($paymentMethod, $owner);
        }

        $this->owner = $owner;
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * Delete the payment method.
     *
     * @return void
     */
    public function delete()
    {
        $this->owner->deletePaymentMethod($this->paymentMethod);
    }

    /**
     * Get the Square model instance.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function owner()
    {
        return $this->owner;
    }

    /**
     * Get the Square Payment instance.
     *
     * @return \Square\Models\Payment
     */
    public function asSquarePayment()
    {
        return $this->paymentMethod;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->asSquarePayment()->jsonSerialize();
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
        $method = 'get' . ucfirst($key);
        if (method_exists($this->paymentMethod, $method)) {
            return $this->paymentMethod->$method();
        }

        return null;
    }
}
