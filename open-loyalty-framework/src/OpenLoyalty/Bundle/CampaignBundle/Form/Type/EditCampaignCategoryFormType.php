<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

/**
 * Class EditCampaignCategoryFormType.
 */
class EditCampaignCategoryFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CampaignCategoryFormType::class;
    }
}
