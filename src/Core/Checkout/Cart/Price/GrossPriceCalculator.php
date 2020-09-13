<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Cart\Price;

use Money\Money;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\ListPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTax;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Cart\Tax\TaxCalculator;

class GrossPriceCalculator
{
    /**
     * @var TaxCalculator
     */
    private $taxCalculator;

    /**
     * @var PriceRoundingInterface
     */
    private $priceRounding;

    /**
     * @var ReferencePriceCalculator
     */
    private $referencePriceCalculator;

    public function __construct(
        TaxCalculator $taxCalculator,
        PriceRoundingInterface $priceRounding,
        ReferencePriceCalculator $referencePriceCalculator
    ) {
        $this->taxCalculator = $taxCalculator;
        $this->priceRounding = $priceRounding;
        $this->referencePriceCalculator = $referencePriceCalculator;
    }

    public function calculate(QuantityPriceDefinition $definition): CalculatedPrice
    {
        $unitPrice = $this->getUnitPrice($definition);

        /** @var CalculatedTax[]|CalculatedTaxCollection $unitTaxes */
        $unitTaxes = $this->taxCalculator->calculateGrossTaxes($unitPrice, $definition->getTaxRules());

        foreach ($unitTaxes as $tax) {
            $totalTax = $tax->getTax()->multiply($definition->getQuantity());
            $totalPrice = $tax->getPrice()->multiply($definition->getQuantity());

            $tax->setTax($totalTax);
            $tax->setPrice($totalPrice);
        }

        $price = $unitPrice->multiply($definition->getQuantity());

        return new CalculatedPrice(
            $unitPrice,
            $price,
            $unitTaxes,
            $definition->getTaxRules(),
            $definition->getQuantity(),
            $this->referencePriceCalculator->calculate($price, $definition),
            $this->calculateListPrice($unitPrice, $definition)
        );
    }

    private function getUnitPrice(QuantityPriceDefinition $definition): Money
    {
        //item price already calculated?
        if ($definition->isCalculated()) {
            return $definition->getPrice();
        }

        return $this->taxCalculator->calculateGross(
            $definition->getPrice(),
            $definition->getTaxRules()
        );
    }

    private function calculateListPrice(Money $unitPrice, QuantityPriceDefinition $definition): ?ListPrice
    {
        if (!$definition->getListPrice()) {
            return null;
        }

        $price = $definition->getListPrice();

        if (!$definition->isCalculated()) {
            $price = $this->taxCalculator->calculateGross(
                $definition->getListPrice(),
                $definition->getTaxRules()
            );
        }

        return ListPrice::createFromUnitPrice($unitPrice, $price);
    }
}
