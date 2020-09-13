<?php declare(strict_types=1);

namespace Shopware\Core\Framework\DataAbstractionLayer\Pricing;

use Money\Money;
use Shopware\Core\Framework\Struct\Struct;

class Price extends Struct
{
    /**
     * @var string
     */
    protected $currencyId;

    /**
     * @var Money
     */
    protected $net;

    /**
     * @var Money
     */
    protected $gross;

    /**
     * @var bool
     */
    protected $linked;

    /**
     * @var Price|null
     */
    protected $listPrice;

    public function __construct(string $currencyId, Money $net, Money $gross, bool $linked, ?Price $listPrice = null)
    {
        $this->net = $net;
        $this->gross = $gross;
        $this->linked = $linked;
        $this->currencyId = $currencyId;
        $this->listPrice = $listPrice;
    }

    public function getNet(): Money
    {
        return $this->net;
    }

    public function setNet(Money $net): void
    {
        $this->net = $net;
    }

    public function getGross(): Money
    {
        return $this->gross;
    }

    public function setGross(Money $gross): void
    {
        $this->gross = $gross;
    }

    public function getLinked(): bool
    {
        return $this->linked;
    }

    public function setLinked(bool $linked): void
    {
        $this->linked = $linked;
    }

    public function add(self $price): void
    {
        $this->gross->add($price->getGross());
        $this->net->add($price->getNet());
    }

    public function getCurrencyId(): string
    {
        return $this->currencyId;
    }

    public function setCurrencyId(string $currencyId): void
    {
        $this->currencyId = $currencyId;
    }

    public function setListPrice(?Price $listPrice): void
    {
        $this->listPrice = $listPrice;
    }

    public function getListPrice(): ?Price
    {
        return $this->listPrice;
    }

    public function getApiAlias(): string
    {
        return 'price';
    }
}
