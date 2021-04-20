<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace OpenLoyalty\Component\Campaign\Domain\Entity;

use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\PhotoId;
use OpenLoyalty\Component\Campaign\Domain\PhotoMimeType;
use OpenLoyalty\Component\Campaign\Domain\PhotoOriginalName;
use OpenLoyalty\Component\Campaign\Domain\PhotoPath;

/**
 * Class CampaignPhoto.
 */
class CampaignPhoto
{
    /**
     * @var PhotoId
     */
    private $photoId;

    /**
     * @var Campaign
     */
    private $campaign;

    /**
     * @var PhotoPath
     */
    private $path;

    /**
     * @var PhotoOriginalName
     */
    private $originalName;

    /**
     * @var PhotoMimeType
     */
    private $mimeType;

    /**
     * CampaignPhoto constructor.
     *
     * @param Campaign          $campaign
     * @param PhotoId           $photoId
     * @param PhotoPath         $path
     * @param PhotoOriginalName $originalName
     * @param PhotoMimeType     $mimeType
     */
    public function __construct(
        Campaign $campaign,
        PhotoId $photoId,
        PhotoPath $path,
        PhotoOriginalName $originalName,
        PhotoMimeType $mimeType
    ) {
        $this->photoId = $photoId;
        $this->campaign = $campaign;
        $this->path = $path;
        $this->originalName = $originalName;
        $this->mimeType = $mimeType;
    }

    /**
     * @return PhotoPath
     */
    public function getPath(): PhotoPath
    {
        return $this->path;
    }

    /**
     * @return PhotoMimeType
     */
    public function getMimeType(): PhotoMimeType
    {
        return $this->mimeType;
    }
}
