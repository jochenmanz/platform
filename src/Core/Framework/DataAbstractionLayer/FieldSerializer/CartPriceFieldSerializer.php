<?php declare(strict_types=1);

namespace Shopware\Core\Framework\DataAbstractionLayer\FieldSerializer;

use Money\Currency;
use Money\Money;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTax;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRule;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\Write\DataStack\KeyValuePair;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteParameterBag;

class CartPriceFieldSerializer extends JsonFieldSerializer
{
    public function encode(
        Field $field,
        EntityExistence $existence,
        KeyValuePair $data,
        WriteParameterBag $parameters
    ): \Generator {
        $value = json_decode(json_encode($data->getValue(), JSON_PRESERVE_ZERO_FRACTION), true);

        unset($value['extensions']);

        $data->setValue($value);

        yield from parent::encode($field, $existence, $data, $parameters);
    }

    public function decode(Field $field, $value)
    {
        if ($value === null) {
            return null;
        }

        $value = parent::decode($field, $value);

        $taxRules = array_map(
            static function (array $tax) {
                return new TaxRule(
                    (float) $tax['taxRate'],
                    (float) $tax['percentage']
                );
            },
            $value['taxRules']
        );

        $calculatedTaxes = array_map(
            static function (array $tax) {
                return new CalculatedTax(
                    new Money($tax['tax']['amount'], new Currency($tax['tax']['currency'])),
                    (float) $tax['taxRate'],
                    new Money($tax['price']['amount'], new Currency($tax['price']['currency']))
                );
            },
            $value['calculatedTaxes']
        );

        return new CartPrice(
            new Money($value['netPrice']['amount'], new Currency($value['netPrice']['currency'])),
            new Money($value['totalPrice']['amount'], new Currency($value['totalPrice']['currency'])),
            new Money($value['positionPrice']['amount'], new Currency($value['positionPrice']['currency'])),
            new CalculatedTaxCollection($calculatedTaxes),
            new TaxRuleCollection($taxRules),
            (string) $value['taxStatus']
        );
    }
}
