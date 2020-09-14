<?php declare(strict_types=1);

namespace Shopware\Core\Content\Product\SalesChannel\Price;

use Money\Currency;
use Money\Money;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceDefinitionCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\ReferencePriceDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductPrice\ProductPriceCollection;
use Shopware\Core\Content\Product\Aggregate\ProductPrice\ProductPriceEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\Price;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceRuleEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ProductPriceDefinitionBuilder implements ProductPriceDefinitionBuilderInterface
{
    public function build(ProductEntity $product, SalesChannelContext $context, int $quantity = 1): ProductPriceDefinitions
    {
        $listingPrice = $this->buildListingPriceDefinition($product, $context);

        return new ProductPriceDefinitions(
            $this->buildPriceDefinition($product, $context),
            $this->buildPriceDefinitions($product, $context),
            $listingPrice['from'],
            $listingPrice['to'],
            $this->buildPriceDefinitionForQuantity($product, $context, $quantity)
        );
    }

    private function buildPriceDefinitions(ProductEntity $product, SalesChannelContext $context): PriceDefinitionCollection
    {
        $taxRules = $context->buildTaxRules($product->getTaxId());

        $prices = $this->getFirstMatchingPriceRule($product->getPrices(), $context);

        if (!$prices) {
            return new PriceDefinitionCollection();
        }

        $prices = $this->sortByQuantity($prices);

        $definitions = [];

        foreach ($prices as $price) {
            $quantity = $price->getQuantityEnd() ?? $price->getQuantityStart();

            $definitions[] = new QuantityPriceDefinition(
                $this->getCurrencyPrice($price, $context),
                $taxRules,
                $context->getContext()->getCurrencyPrecision(),
                $quantity,
                true,
                $this->buildReferencePriceDefinition($product)
            );
        }

        return new PriceDefinitionCollection($definitions);
    }

    private function buildPriceDefinition(ProductEntity $product, SalesChannelContext $context): QuantityPriceDefinition
    {
        $price = $this->getProductCurrencyPrice($product, $context);

        return new QuantityPriceDefinition(
            $price,
            $context->buildTaxRules($product->getTaxId()),
            $context->getContext()->getCurrencyPrecision(),
            1,
            true,
            $this->buildReferencePriceDefinition($product),
            $this->getListPrice($product, $context)
        );
    }

    private function buildListingPriceDefinition(ProductEntity $product, SalesChannelContext $context): array
    {
        $taxRules = $context->buildTaxRules($product->getTaxId());

        $currencyPrecision = $context->getContext()->getCurrencyPrecision();

        if ($product->getListingPrices()) {
            $listingPrice = $product->getListingPrices()->getContextPrice($context->getContext());

            if ($listingPrice) {
                // indexed listing prices are indexed for each currency
                $from = $this->getPriceForTaxState($listingPrice->getFrom(), $context);
                $to = $this->getPriceForTaxState($listingPrice->getTo(), $context);

                return [
                    'from' => new QuantityPriceDefinition($from, $taxRules, $currencyPrecision, 1, true, $this->buildReferencePriceDefinition($product)),
                    'to' => new QuantityPriceDefinition($to, $taxRules, $currencyPrecision, 1, true, $this->buildReferencePriceDefinition($product)),
                ];
            }
        }

        $prices = $this->getFirstMatchingPriceRule($product->getPrices(), $context);

        if (!$prices || count($prices) <= 0) {
            $price = $this->getProductCurrencyPrice($product, $context);

            $definition = new QuantityPriceDefinition($price, $taxRules, $currencyPrecision, 1, true, $this->buildReferencePriceDefinition($product));

            return ['from' => $definition, 'to' => $definition];
        }

        $highest = $this->getCurrencyPrice($prices[0], $context);
        $lowest = $highest;

        foreach ($prices as $price) {
            $value = $this->getCurrencyPrice($price, $context);

            $highest = $value > $highest ? $value : $highest;
            $lowest = $value < $lowest ? $value : $lowest;
        }

        return [
            'from' => new QuantityPriceDefinition($lowest, $taxRules, $currencyPrecision, 1, true, $this->buildReferencePriceDefinition($product)),
            'to' => new QuantityPriceDefinition($highest, $taxRules, $currencyPrecision, 1, true, $this->buildReferencePriceDefinition($product)),
        ];
    }

    private function buildPriceDefinitionForQuantity(ProductEntity $product, SalesChannelContext $context, int $quantity): QuantityPriceDefinition
    {
        $taxRules = $context->buildTaxRules($product->getTaxId());

        /** @var ProductPriceEntity[]|null $prices */
        $prices = $this->getFirstMatchingPriceRule($product->getPrices(), $context);

        if (!$prices) {
            $price = $this->getProductCurrencyPrice($product, $context);

            return new QuantityPriceDefinition(
                $price,
                $taxRules,
                $context->getContext()->getCurrencyPrecision(),
                $quantity,
                true,
                $this->buildReferencePriceDefinition($product),
                $this->getListPrice($product, $context)
            );
        }

        $prices = $this->getQuantityPrices($prices, $quantity);

        return new QuantityPriceDefinition(
            $this->getCurrencyPrice($prices[0], $context),
            $taxRules,
            $context->getContext()->getCurrencyPrecision(),
            $quantity,
            true,
            $this->buildReferencePriceDefinition($product)
        );
    }

    private function getQuantityPrices(array $prices, int $quantity): array
    {
        $filtered = [];

        /** @var ProductPriceEntity $price */
        foreach ($prices as $price) {
            $end = $price->getQuantityEnd() ?? $quantity + 1;

            if ($price->getQuantityStart() <= $quantity && $end >= $quantity) {
                $filtered[] = $price;
            }
        }

        return $filtered;
    }

    private function getFirstMatchingPriceRule(ProductPriceCollection $rules, SalesChannelContext $context): ?array
    {
        foreach ($context->getRuleIds() as $ruleId) {
            $filtered = $this->filterByRuleId($rules->getElements(), $ruleId);

            if (count($filtered) > 0) {
                return $filtered;
            }
        }

        return null;
    }

    private function filterByRuleId(array $rules, string $ruleId): array
    {
        $filtered = [];
        /** @var PriceRuleEntity $priceRule */
        foreach ($rules as $priceRule) {
            if ($priceRule->getRuleId() === $ruleId) {
                $filtered[] = $priceRule;
            }
        }

        return $filtered;
    }

    private function getCurrencyPrice(PriceRuleEntity $rule, SalesChannelContext $context): Money
    {
        $price = $rule->getPrice()->getCurrencyPrice($context->getCurrency()->getId());

        if ($price === null) {
            return new Money(0, new Currency('EUR'));
        }

        return $this->getPriceForTaxState($price, $context);
    }

    private function getPriceForTaxState(Price $price, SalesChannelContext $context): Money
    {
        if ($context->getTaxState() === CartPrice::TAX_STATE_GROSS) {
            $amount = $price->getGross();
        } else {
            $amount = $price->getNet();
        }

        if ($price->getCurrencyId() !== $context->getCurrency()->getId()) {
            $amount->multiply($context->getContext()->getCurrencyFactor());
        }

        return $amount;
    }

    private function sortByQuantity(array $prices): array
    {
        usort($prices, function (ProductPriceEntity $a, ProductPriceEntity $b) {
            return $a->getQuantityStart() <=> $b->getQuantityStart();
        });

        return $prices;
    }

    private function buildReferencePriceDefinition(ProductEntity $product): ?ReferencePriceDefinition
    {
        $referencePrice = null;
        if (
            $product->getPurchaseUnit()
            && $product->getReferenceUnit()
            && $product->getUnit() !== null
            && $product->getPurchaseUnit() !== $product->getReferenceUnit()
        ) {
            $referencePrice = new ReferencePriceDefinition(
                $product->getPurchaseUnit(),
                $product->getReferenceUnit(),
                (string) $product->getUnit()->getTranslation('name')
            );
        }

        return $referencePrice;
    }

    private function getListPrice(ProductEntity $product, SalesChannelContext $context): Money
    {
        $price = $product->getPrice()->getCurrencyPrice($context->getCurrency()->getId());

        if (!$price || !$price->getListPrice()) {
            return new Money(0, new Currency('EUR'));
        }

        if ($context->getTaxState() === CartPrice::TAX_STATE_GROSS) {
            $value = $price->getListPrice()->getGross();
        } else {
            $value = $price->getListPrice()->getNet();
        }

        $precision = $context->getCurrency()->getDecimalPrecision();
        $amount = $value * (10 ** $precision);

        if ($price->getCurrencyId() !== $context->getCurrency()->getId()) {
            $amount *= $context->getContext()->getCurrencyFactor();
        }

        return new Money($amount, new Currency('EUR'));
    }

    private function getProductCurrencyPrice(ProductEntity $product, SalesChannelContext $context): Money
    {
        $price = $product->getPrice()->getCurrencyPrice($context->getCurrency()->getId());

        if (!$price) {
            return new Money(0, new Currency('EUR'));
        }

        return $this->getPriceForTaxState($price, $context);
    }
}
