<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain;

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
     *
     * @throws \Assert\AssertionFailedException
     */
    public function __construct($campaignId)
    {
        Assert::string($campaignId);
        Assert::uuid($campaignId);

        $this->campaignId = $campaignId;
    }

    public function __toString()
    {
        return $this->campaignId;
    }
}
