<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Cart\Price\Struct;

use Money\Currency;
use Money\Money;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Framework\Struct\Struct;
use function in_array;

class CartPrice extends Struct
{
    public const TAX_STATE_GROSS = 'gross';
    public const TAX_STATE_NET = 'net';
    public const TAX_STATE_FREE = 'tax-free';

    /**
     * @var Money
     */
    protected $netPrice;

    /**
     * @var Money
     */
    protected $totalPrice;

    /**
     * @var Money
     */
    protected $positionPrice;

    /**
     * @var CalculatedTaxCollection
     */
    protected $calculatedTaxes;

    /**
     * @var TaxRuleCollection
     */
    protected $taxRules;

    /**
     * @var string
     */
    protected $taxStatus;

    public function __construct(
        Money $netPrice,
        Money $totalPrice,
        Money $positionPrice,
        CalculatedTaxCollection $calculatedTaxes,
        TaxRuleCollection $taxRules,
        string $taxStatus
    ) {
        $this->netPrice = $netPrice;
        $this->totalPrice = $totalPrice;
        $this->calculatedTaxes = $calculatedTaxes;
        $this->taxRules = $taxRules;
        $this->positionPrice = $positionPrice;
        $this->taxStatus = $taxStatus;
    }

    public function getNetPrice(): Money
    {
        return $this->netPrice;
    }

    public function getTotalPrice(): Money
    {
        return $this->totalPrice;
    }

    public function getPositionPrice(): Money
    {
        return $this->positionPrice;
    }

    public function getCalculatedTaxes(): CalculatedTaxCollection
    {
        return $this->calculatedTaxes;
    }

    public function getTaxRules(): TaxRuleCollection
    {
        return $this->taxRules;
    }

    public function getTaxStatus(): string
    {
        return $this->taxStatus;
    }

    public function hasNetPrices(): bool
    {
        return in_array($this->taxStatus, [self::TAX_STATE_NET, self::TAX_STATE_FREE], true);
    }

    public function isTaxFree(): bool
    {
        return $this->taxStatus === self::TAX_STATE_FREE;
    }

    public static function createEmpty(string $taxState = self::TAX_STATE_GROSS): CartPrice
    {
        $zero = new Money(0, new Currency('EUR'));

        return new self(
            $zero,
            $zero,
            $zero,
            new CalculatedTaxCollection(),
            new TaxRuleCollection(),
            $taxState
        );
    }

    public function getApiAlias(): string
    {
        return 'cart_price';
    }
}
