<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Campaign\Domain\ReadModel;

/**
 * Class CampaignShippingAddress.
 */
class CampaignShippingAddress
{
    /**
     * @var null|string
     */
    protected $street;

    /**
     * @var null|string
     */
    protected $address1;

    /**
     * @var null|string
     */
    protected $address2;

    /**
     * @var null|string
     */
    protected $province;

    /**
     * @var null|string
     */
    protected $city;

    /**
     * @var null|string
     */
    protected $postal;

    /**
     * @var null|string
     */
    protected $country;

    /**
     * CampaignShippingAddress constructor.
     *
     * @param string|null $street
     * @param string|null $address1
     * @param string|null $address2
     * @param string|null $province
     * @param string|null $city
     * @param string|null $postal
     * @param string|null $country
     */
    public function __construct(
        ?string $street,
        ?string $address1,
        ?string $address2,
        ?string $province,
        ?string $city,
        ?string $postal,
        ?string $country
    ) {
        $this->street = $street;
        $this->address1 = $address1;
        $this->address2 = $address2;
        $this->province = $province;
        $this->city = $city;
        $this->postal = $postal;
        $this->country = $country;
    }

    /**
     * @return string|null
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * @return string|null
     */
    public function getAddress1(): ?string
    {
        return $this->address1;
    }

    /**
     * @return string|null
     */
    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    /**
     * @return string|null
     */
    public function getProvince(): ?string
    {
        return $this->province;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @return string|null
     */
    public function getPostal(): ?string
    {
        return $this->postal;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }
}
