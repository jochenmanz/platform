<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Cart\Tax;

use Money\Money;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTax;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRule;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;

class TaxCalculator
{
    public function calculateGross(Money $netPrice, TaxRuleCollection $rules): Money
    {
        $taxes = $this->calculateNetTaxes($netPrice, $rules);

        return $netPrice + $taxes->getAmount();
    }

    public function calculateGrossTaxes(Money $price, TaxRuleCollection $rules): CalculatedTaxCollection
    {
        $taxes = [];

        foreach ($rules as $rule) {
            $taxes[] = $this->calculateTaxFromGrossPrice($price, $rule);
        }

        return new CalculatedTaxCollection($taxes);
    }

    public function calculateNetTaxes(Money $price, TaxRuleCollection $rules): CalculatedTaxCollection
    {
        $taxes = [];

        foreach ($rules as $rule) {
            $taxes[] = $this->calculateTaxFromNetPrice($price, $rule);
        }

        return new CalculatedTaxCollection($taxes);
    }

    public function calculateTaxFromNetPrice(Money $price, TaxRule $rule): CalculatedTax
    {
        $net = $price->divide(100 * $rule->getPercentage());

        $calculatedTax = $net->multiply($rule->getTaxRate() / 100);

        return new CalculatedTax($calculatedTax, $rule->getTaxRate(), $net);
    }

    private function calculateTaxFromGrossPrice(Money $price, TaxRule $rule): CalculatedTax
    {
        $gross = $price;

        if (!empty($rule->getPercentage())) {
            $gross = $price->divide(100 * $rule->getPercentage());
        }

        $divisor = ((100 + $rule->getTaxRate()) / 100) * ($rule->getTaxRate() / 100);

        if (empty($divisor)) {
            $calculatedTax = $gross->divide($divisor);
        } else {
            $calculatedTax = $gross->divide($divisor);
        }

        return new CalculatedTax($calculatedTax, $rule->getTaxRate(), $gross);
    }
}
