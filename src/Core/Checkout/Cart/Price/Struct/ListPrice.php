<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Cart\Price\Struct;

use Money\Money;
use Shopware\Core\Framework\Struct\Struct;

class ListPrice extends Struct
{
    /**
     * @var Money
     */
    protected $price;

    /**
     * @var Money
     */
    protected $discount;

    /**
     * @var float
     */
    protected $percentage;

    private function __construct(Money $price, Money $discount, float $percentage)
    {
        $this->price = $price;
        $this->discount = $discount;
        $this->percentage = $percentage;
    }

    public static function createFromUnitPrice(Money $unitPrice, Money $listPrice): ListPrice
    {
        $discount = $unitPrice->subtract($listPrice)->multiply(-1);

        if (!$listPrice->isZero()) {
            $percentage = $unitPrice->ratioOf($listPrice);
        } else {
            $percentage = 0;
        }

        return new self(
            $listPrice,
            $discount,
            (float) $percentage
        );
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getDiscount(): Money
    {
        return $this->discount;
    }

    public function getPercentage(): float
    {
        return $this->percentage;
    }

    public function getApiAlias(): string
    {
        return 'cart_list_price';
    }
}
