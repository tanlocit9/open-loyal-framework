<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Repository;

use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\Entity\CampaignPhoto;
use OpenLoyalty\Component\Campaign\Domain\PhotoId;

/**
 * Class CampaignPhotoRepository.
 */
interface CampaignPhotoRepositoryInterface
{
    /**
     * @param CampaignPhoto $photo
     */
    public function save(CampaignPhoto $photo): void;

    /**
     * @param CampaignPhoto $photo
     */
    public function remove(CampaignPhoto $photo): void;

    /**
     * @param PhotoId    $photoId
     * @param CampaignId $campaignId
     *
     * @return null|CampaignPhoto
     */
    public function findOneByIdCampaignId(PhotoId $photoId, CampaignId $campaignId): ?CampaignPhoto;
}
