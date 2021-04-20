<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Command;

use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\PhotoId;

/**
 * Class RemovePhotoCommand.
 */
class RemovePhotoCommand
{
    /**
     * @var string
     */
    private $campaignId;

    /**
     * @var string
     */
    private $photoId;

    /**
     * RemovePhotoCommand constructor.
     *
     * @param string $campaignId
     * @param string $photoId
     */
    public function __construct(string $campaignId, string $photoId)
    {
        $this->campaignId = $campaignId;
        $this->photoId = $photoId;
    }

    /**
     * @param CampaignId $campaignId
     * @param PhotoId    $photoId
     *
     * @return RemovePhotoCommand
     */
    public static function byCampaignIdAndPhotoId(CampaignId $campaignId, PhotoId $photoId): self
    {
        return new self((string) $campaignId, (string) $photoId);
    }

    /**
     * @return CampaignId
     *
     * @throws \Assert\AssertionFailedException
     */
    public function getCampaignId(): CampaignId
    {
        return new CampaignId($this->campaignId);
    }

    /**
     * @return PhotoId
     */
    public function getPhotoId(): PhotoId
    {
        return new PhotoId($this->photoId);
    }
}
