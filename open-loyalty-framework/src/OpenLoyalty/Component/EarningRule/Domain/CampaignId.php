<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain;

use OpenLoyalty\Component\Core\Domain\Model\Identifier;
use Assert\Assertion as Assert;

/**
 * Class CampaignId.
 */
class CampaignId implements Identifier
{
    /**
     * @var string
     */
    protected $campaignId;

    /**
     * CampaignId constructor.
     *
     * @param string $campaignId
     */
    public function __construct(string $campaignId)
    {
        Assert::string($campaignId);
        Assert::uuid($campaignId);

        $this->campaignId = $campaignId;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->campaignId;
    }
}
