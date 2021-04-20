<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Factory;

use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\Entity\CampaignPhoto;
use OpenLoyalty\Component\Campaign\Domain\PhotoId;
use OpenLoyalty\Component\Campaign\Domain\PhotoMimeType;
use OpenLoyalty\Component\Campaign\Domain\PhotoOriginalName;
use OpenLoyalty\Component\Campaign\Domain\PhotoPath;

/**
 * Class PhotoEntityFactory.
 */
class PhotoEntityFactory implements PhotoEntityFactoryInterface
{
    /**
     * @param Campaign          $campaign
     * @param PhotoId           $photoId
     * @param PhotoPath         $photoPath
     * @param PhotoOriginalName $originalName
     * @param PhotoMimeType     $mimeType
     *
     * @return CampaignPhoto
     */
    public function crete(
        Campaign $campaign,
        PhotoId $photoId,
        PhotoPath $photoPath,
        PhotoOriginalName $originalName,
        PhotoMimeType $mimeType
    ): CampaignPhoto {
        return new CampaignPhoto($campaign, $photoId, $photoPath, $originalName, $mimeType);
    }
}
