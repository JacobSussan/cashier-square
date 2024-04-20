<?php

namespace Laravel\Cashier;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use JsonSerializable;
use Square\Models\OrderLineItem as SquareOrderLineItem;
use Square\Models\Money as SquareMoney;

class InvoiceLineItem implements Arrayable, Jsonable, JsonSerializable
{
    /**
     * The Cashier Invoice instance.
     *
     * @var \Laravel\Cashier\Invoice
     */
    protected $invoice;

    /**
     * The Square order line item instance.
     *
     * @var \Square\Models\OrderLineItem
     */
    protected $item;

    /**
     * Create a new invoice line item instance.
     *
     * @param  \Laravel\Cashier\Invoice  $invoice
     * @param  \Square\Models\OrderLineItem  $item
     * @return void
     */
    public function __construct(Invoice $invoice, SquareOrderLineItem $item)
    {
        $this->invoice = $invoice;
        $this->item = $item;
    }

    /**
     * Get the total for the invoice line item.
     *
     * @return string
     */
    public function total()
    {
        return $this->formatAmount($this->item->getTotalMoney());
    }

    /**
     * Get the unit amount excluding tax for the invoice line item.
     *
     * @return string
     */
    public function unitAmountExcludingTax()
    {
        // Assuming taxes are included in the total price and need to be subtracted
        $totalMoney = $this->item->getTotalMoney();
        $taxMoney = $this->item->getTaxMoney();
        $unitAmountExcludingTax = $totalMoney->getAmount() - $taxMoney->getAmount();
        
        return $this->formatAmount(new SquareMoney($unitAmountExcludingTax, $totalMoney->getCurrency()));
    }

    // ... (other methods would need to be updated accordingly)

    /**
     * Format the given amount into a displayable currency.
     *
     * @param  \Square\Models\Money  $money
     * @return string
     */
    protected function formatAmount(SquareMoney $money)
    {
        return Cashier::formatAmount($money->getAmount(), $money->getCurrency());
    }

    /**
     * Get the Square model instance.
     *
     * @return \Laravel\Cashier\Invoice
     */
    public function invoice()
    {
        return $this->invoice;
    }

    /**
     * Get the underlying Square order line item.
     *
     * @return \Square\Models\OrderLineItem
     */
    public function asSquareOrderLineItem()
    {
        return $this->item;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        // TODO: finish this
        return [
            'uid' => $this->item->getUid(),
            'name' => $this->item->getName(),
            'quantity' => $this->item->getQuantity(),
            'base_price_money' => $this->item->getBasePriceMoney()->getAmount(),
            'total_money' => $this->item->getTotalMoney()->getAmount(),
            'total_tax_money' => $this->item->getTaxMoney()->getAmount(),
        ];
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
     * Dynamically access the Square order line item instance.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        $getter = 'get'.ucfirst($key);
        if (method_exists($this->item, $getter)) {
            return $this->item->{$getter}();
        }
        return null;
    }
}
