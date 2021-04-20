<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Event;

/**
 * Class CampaignPhotoRemovedEvent.
 */
class CampaignPhotoRemovedEvent
{
    const NAME = 'oloy.campaign.photo.removed';

    /**
     * @var array
     */
    private $filePath;

    /**
     * CampaignPhotoSavedEvent constructor.
     *
     * @param string $filePath
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }
}
