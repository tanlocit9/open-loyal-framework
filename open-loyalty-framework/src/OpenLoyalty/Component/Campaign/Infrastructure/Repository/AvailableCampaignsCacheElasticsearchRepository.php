<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Infrastructure\Repository;

use OpenLoyalty\Component\Campaign\Domain\ReadModel\AvailableCampaignsCacheRepository;
use OpenLoyalty\Component\Core\Infrastructure\Repository\OloyElasticsearchRepository;

/**
 * Class AvailableCampaignsCacheElasticsearchRepository.
 */
class AvailableCampaignsCacheElasticsearchRepository extends OloyElasticsearchRepository implements AvailableCampaignsCacheRepository
{
}
