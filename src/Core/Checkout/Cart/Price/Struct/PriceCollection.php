<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Cart\Price\Struct;

use Money\Currency;
use Money\Money;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Framework\Struct\Collection;

/**
 * @method void                 add(CalculatedPrice $entity)
 * @method void                 set(string $key, CalculatedPrice $entity)
 * @method CalculatedPrice[]    getIterator()
 * @method CalculatedPrice[]    getElements()
 * @method CalculatedPrice|null first()
 * @method CalculatedPrice|null last()
 */
class PriceCollection extends Collection
{
    public function get($key): ?CalculatedPrice
    {
        $key = (int) $key;

        if ($this->has($key)) {
            return $this->elements[$key];
        }

        return null;
    }

    public function getTaxRules(): TaxRuleCollection
    {
        $rules = new TaxRuleCollection([]);

        foreach ($this->getIterator() as $price) {
            $rules = $rules->merge($price->getTaxRules());
        }

        return $rules;
    }

    public function sum(): CalculatedPrice
    {
        return new CalculatedPrice(
            $this->getUnitPriceAmount(),
            $this->getAmount(),
            $this->getCalculatedTaxes(),
            $this->getTaxRules()
        );
    }

    public function getCalculatedTaxes(): CalculatedTaxCollection
    {
        $taxes = new CalculatedTaxCollection([]);

        foreach ($this->getIterator() as $price) {
            $taxes = $taxes->merge($price->getCalculatedTaxes());
        }

        return $taxes;
    }

    public function merge(self $prices): self
    {
        return new self(array_merge($this->elements, $prices->getElements()));
    }

    public function getApiAlias(): string
    {
        return 'cart_price_collection';
    }

    protected function getExpectedClass(): ?string
    {
        return CalculatedPrice::class;
    }

    private function getUnitPriceAmount(): Money
    {
        $sum = new Money(0, new Currency('EUR'));

        foreach ($this->getIterator() as $price) {
            $sum->add($price->getUnitPrice());
        }

        return $sum;
    }

    private function getAmount(): Money
    {
        $sum = new Money(0, new Currency('EUR'));

        foreach ($this->getIterator() as $price) {
            $sum->add($price->getTotalPrice());
        }

        return $sum;
    }
}
