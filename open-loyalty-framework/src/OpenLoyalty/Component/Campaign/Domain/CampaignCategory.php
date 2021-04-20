<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain;

use Assert\Assertion as Assert;
use OpenLoyalty\Bundle\TranslationBundle\Model\FallbackTranslatable;

/**
 * Class CampaignCategory.
 */
class CampaignCategory
{
    use FallbackTranslatable;

    /**
     * @var CampaignCategoryId
     */
    protected $campaignCategoryId;

    /**
     * @var bool
     */
    protected $active = true;

    /**
     * @var int|null
     */
    protected $sortOrder = 0;

    /**
     * CampaignCategory constructor.
     *
     * @param CampaignCategoryId $campaignCategoryId
     * @param array              $data
     */
    public function __construct(CampaignCategoryId $campaignCategoryId, array $data = [])
    {
        $this->campaignCategoryId = $campaignCategoryId;
        $this->setFromArray($data);
    }

    /**
     * @param array $data
     */
    public function setFromArray(array $data): void
    {
        if (isset($data['sortOrder'])) {
            $this->sortOrder = $data['sortOrder'];
        }

        if (isset($data['active'])) {
            $this->active = $data['active'];
        }

        if (array_key_exists('translations', $data)) {
            foreach ($data['translations'] as $locale => $transData) {
                if (array_key_exists('name', $transData)) {
                    $this->translate($locale, false)->setName($transData['name']);
                }
            }
            /** @var CampaignTranslation $translation */
            foreach ($this->getTranslations() as $translation) {
                if (!isset($data['translations'][$translation->getLocale()])) {
                    $this->removeTranslation($translation);
                }
            }
        }
    }

    /**
     * @param array $data
     *
     * @throws \Assert\AssertionFailedException
     */
    public static function validateRequiredData(array $data): void
    {
        Assert::keyIsset($data, 'sortOrder');
        Assert::integer($data['sortOrder']);
    }

    /**
     * @return CampaignCategoryId
     */
    public function getCampaignCategoryId(): CampaignCategoryId
    {
        return $this->campaignCategoryId;
    }

    /**
     * @param CampaignCategoryId $campaignCategoryId
     */
    public function setCampaignCategoryId(CampaignCategoryId $campaignCategoryId): void
    {
        $this->campaignCategoryId = $campaignCategoryId;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->translateFieldFallback(null, 'name')->getName();
    }

    /**
     * @param null|string $name
     */
    public function setName(?string $name)
    {
        $this->translate(null, false)->setName($name);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return int|null
     */
    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    /**
     * @param int|null $sortOrder
     */
    public function setSortOrder(?int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }
}
