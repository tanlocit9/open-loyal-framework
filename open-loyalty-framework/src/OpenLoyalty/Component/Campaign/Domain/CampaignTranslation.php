<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain;

use OpenLoyalty\Bundle\TranslationBundle\Model\FallbackTranslation;

/**
 * Class CampaignTranslation.
 */
class CampaignTranslation
{
    use FallbackTranslation;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $shortDescription;

    /**
     * @var string|null
     */
    private $conditionsDescription;

    /**
     * @var string|null
     */
    private $usageInstruction;

    /**
     * @var string|null
     */
    private $brandDescription;

    /**
     * @var string|null
     */
    private $brandName;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return null|string
     */
    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    /**
     * @param null|string $shortDescription
     */
    public function setShortDescription(?string $shortDescription): void
    {
        $this->shortDescription = $shortDescription;
    }

    /**
     * @return null|string
     */
    public function getConditionsDescription(): ?string
    {
        return $this->conditionsDescription;
    }

    /**
     * @param null|string $conditionsDescription
     */
    public function setConditionsDescription(?string $conditionsDescription): void
    {
        $this->conditionsDescription = $conditionsDescription;
    }

    /**
     * @return null|string
     */
    public function getUsageInstruction(): ?string
    {
        return $this->usageInstruction;
    }

    /**
     * @param null|string $usageInstruction
     */
    public function setUsageInstruction(?string $usageInstruction): void
    {
        $this->usageInstruction = $usageInstruction;
    }

    /**
     * @return null|string
     */
    public function getBrandDescription(): ?string
    {
        return $this->brandDescription;
    }

    /**
     * @param null|string $brandDescription
     */
    public function setBrandDescription(?string $brandDescription): void
    {
        $this->brandDescription = $brandDescription;
    }

    /**
     * @return null|string
     */
    public function getBrandName(): ?string
    {
        return $this->brandName;
    }

    /**
     * @param null|string $brandName
     */
    public function setBrandName(?string $brandName): void
    {
        $this->brandName = $brandName;
    }
}
