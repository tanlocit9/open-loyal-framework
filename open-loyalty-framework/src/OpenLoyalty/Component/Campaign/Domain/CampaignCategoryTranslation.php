<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain;

use OpenLoyalty\Bundle\TranslationBundle\Model\FallbackTranslation;

/**
 * Class CampaignCategoryTranslation.
 */
class CampaignCategoryTranslation
{
    use FallbackTranslation;

    /**
     * @var string|null
     */
    private $name;

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
}
