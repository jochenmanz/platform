<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Cart\Tax\Struct;

use Money\Money;
use Shopware\Core\Framework\Struct\Struct;

class CalculatedTax extends Struct
{
    /**
     * @var Money
     */
    protected $tax = 0;

    /**
     * @var float
     */
    protected $taxRate;

    /**
     * @var Money
     */
    protected $price = 0;

    public function __construct(Money $tax, float $taxRate, Money $price)
    {
        $this->tax = $tax;
        $this->taxRate = $taxRate;
        $this->price = $price;
    }

    public function getTax(): Money
    {
        return $this->tax;
    }

    public function setTax(Money $tax): void
    {
        $this->tax = $tax;
    }

    public function getTaxRate(): float
    {
        return $this->taxRate;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function increment(self $calculatedTax): void
    {
        $this->tax->add($calculatedTax->getTax());
        $this->price->add($calculatedTax->getPrice());
    }

    public function setPrice(Money $price): void
    {
        $this->price = $price;
    }

    public function getApiAlias(): string
    {
        return 'cart_tax_calculated';
    }
}
