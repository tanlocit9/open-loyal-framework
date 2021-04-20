<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain;

use OpenLoyalty\Component\Core\Domain\Model\Identifier;
use Assert\Assertion as Assert;

/**
 * Class CampaignCategoryId.
 */
class CampaignCategoryId implements Identifier
{
    /**
     * @var string
     */
    protected $campaignCategoryId;

    /**
     * CampaignCategoryId constructor.
     *
     * @param string $campaignCategoryId
     *
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(string $campaignCategoryId)
    {
        Assert::string($campaignCategoryId);
        Assert::uuid($campaignCategoryId);

        $this->campaignCategoryId = $campaignCategoryId;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->campaignCategoryId;
    }
}
