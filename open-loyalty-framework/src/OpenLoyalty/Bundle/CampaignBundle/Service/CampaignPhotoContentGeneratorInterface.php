<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\CampaignBundle\Service;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class CampaignPhotoContentGenerator.
 */
interface CampaignPhotoContentGeneratorInterface
{
    /**
     * @param string $campaignId
     * @param string $photoId
     *
     * @return Response
     */
    public function getPhotoContent(string $campaignId, string $photoId): Response;
}
