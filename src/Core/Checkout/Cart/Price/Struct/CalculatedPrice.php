<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Cart\Price\Struct;

use Money\Money;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Framework\Struct\Struct;

class CalculatedPrice extends Struct
{
    /**
     * @var Money
     */
    protected $unitPrice;

    /**
     * @var int
     */
    protected $quantity;

    /**
     * @var Money
     */
    protected $totalPrice;

    /**
     * @var CalculatedTaxCollection
     */
    protected $calculatedTaxes;

    /**
     * @var TaxRuleCollection
     */
    protected $taxRules;

    /**
     * @var ReferencePrice
     */
    protected $referencePrice;

    /**
     * @var ListPrice|null
     */
    protected $listPrice;

    public function __construct(
        Money $unitPrice,
        Money $totalPrice,
        CalculatedTaxCollection $calculatedTaxes,
        TaxRuleCollection $taxRules,
        int $quantity = 1,
        ?ReferencePrice $referencePrice = null,
        ?ListPrice $listPrice = null
    ) {
        $this->unitPrice = $unitPrice;
        $this->totalPrice = $totalPrice;
        $this->calculatedTaxes = $calculatedTaxes;
        $this->taxRules = $taxRules;
        $this->quantity = $quantity;
        $this->referencePrice = $referencePrice;
        $this->listPrice = $listPrice;
    }

    public function getTotalPrice(): Money
    {
        return $this->totalPrice;
    }

    public function getCalculatedTaxes(): CalculatedTaxCollection
    {
        return $this->calculatedTaxes;
    }

    public function getTaxRules(): TaxRuleCollection
    {
        return $this->taxRules;
    }

    public function getUnitPrice(): Money
    {
        return $this->unitPrice;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getReferencePrice(): ?ReferencePrice
    {
        return $this->referencePrice;
    }

    public function getListPrice(): ?ListPrice
    {
        return $this->listPrice;
    }

    public function getApiAlias(): string
    {
        return 'calculated_price';
    }
}
