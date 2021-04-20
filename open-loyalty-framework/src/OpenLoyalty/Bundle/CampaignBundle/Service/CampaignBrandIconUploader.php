<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Service;

use OpenLoyalty\Bundle\CampaignBundle\Model\CampaignBrandIcon;
use OpenLoyalty\Component\Campaign\Domain\Model\CampaignFile;

/**
 * Class CampaignBrandIconUploader.
 */
class CampaignBrandIconUploader extends CampaignFileUploader
{
    const FOLDER_NAME = 'campaign_brand_icons';

    /**
     * {@inheritdoc}
     */
    public function getFolderName(): string
    {
        return self::FOLDER_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewInstance(): CampaignFile
    {
        return new CampaignBrandIcon();
    }
}
