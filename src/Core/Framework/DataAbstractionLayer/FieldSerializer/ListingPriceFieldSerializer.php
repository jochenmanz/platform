<?php declare(strict_types=1);

namespace Shopware\Core\Framework\DataAbstractionLayer\FieldSerializer;

use Money\Currency;
use Money\Money;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\ListingPrice;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\ListingPriceCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\Price;
use Shopware\Core\Framework\DataAbstractionLayer\Write\DataStack\KeyValuePair;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteParameterBag;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ListingPriceFieldSerializer extends AbstractFieldSerializer
{
    /**
     * @var ListingPrice
     */
    private $listPrice;

    /**
     * @var Price
     */
    private $price;

    public function __construct(ValidatorInterface $validator, DefinitionInstanceRegistry $definitionRegistry)
    {
        parent::__construct($validator, $definitionRegistry);

        $zero = new Money(0, new Currency('EUR'));

        $this->listPrice = new ListingPrice();
        $this->price = new Price('', $zero, $zero, false);
    }

    public function encode(
        Field $field,
        EntityExistence $existence,
        KeyValuePair $data,
        WriteParameterBag $parameters
    ): \Generator {
        throw new \RuntimeException('Price rules json field will be set by indexer');
    }

    public function decode(Field $field, $value): ListingPriceCollection
    {
        if (!$value) {
            return new ListingPriceCollection();
        }

        $value = json_decode((string) $value, true);

        // @deprecated tag:v6.4.0 - old data structure are no longer supported, if will be removed
        if (isset($value['structs'])) {
            return new ListingPriceCollection();
        }

        $structs = [];
        foreach ($value as $ruleId => $rows) {
            if ($ruleId === 'default') {
                $ruleId = null;
            } else {
                $ruleId = substr($ruleId, 1);
            }

            foreach ($rows as $row) {
                $from = clone $this->price;
                $from->assign($this->normalizePrices($row['from']));

                $to = clone $this->price;
                $to->assign($this->normalizePrices($row['to']));

                $price = clone $this->listPrice;
                $price->assign([
                    'ruleId' => $ruleId,
                    'currencyId' => $row['currencyId'],
                    'from' => $from,
                    'to' => $to,
                ]);

                $structs[] = $price;
            }
        }

        return new ListingPriceCollection($structs);
    }

    private function normalizePrices(array $price): array
    {
        $price['net'] = new Money($price['net']['amount'], new Currency($price['net']['currency']));
        $price['gross'] = new Money($price['gross']['amount'], new Currency($price['gross']['currency']));

        return $price;
    }
}
