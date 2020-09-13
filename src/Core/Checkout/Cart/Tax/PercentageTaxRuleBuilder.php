<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Cart\Tax;

use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTax;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRule;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;

class PercentageTaxRuleBuilder
{
    public function buildRules(CalculatedPrice $price): TaxRuleCollection
    {
        $rules = new TaxRuleCollection([]);

        /** @var CalculatedTax $tax */
        foreach ($price->getCalculatedTaxes() as $tax) {
            $percentage = 0;

            if (!$price->getTotalPrice()->isZero()) {
                $percentage = $tax->getPrice()->ratioOf($price->getTotalPrice());
            }

            $rules->add(
                new TaxRule(
                    $tax->getTaxRate(),
                    $percentage
                )
            );
        }

        return $rules;
    }
}
