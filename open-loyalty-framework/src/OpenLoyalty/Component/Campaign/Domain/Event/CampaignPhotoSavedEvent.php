<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Event;

/**
 * Class CampaignPhotoSavedEvent.
 */
class CampaignPhotoSavedEvent
{
    const NAME = 'oloy.campaign.photo.saved';

    /**
     * @var array
     */
    private $filePath;

    /**
     * @var string
     */
    private $realPath;

    /**
     * CampaignPhotoSavedEvent constructor.
     *
     * @param string $filePath
     * @param string $realPath
     */
    public function __construct(string $filePath, string $realPath)
    {
        $this->filePath = $filePath;
        $this->realPath = $realPath;
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @return string
     */
    public function getRealPath(): string
    {
        return $this->realPath;
    }
}
