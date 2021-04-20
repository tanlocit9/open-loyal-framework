<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use OpenLoyalty\Component\Campaign\Domain\CampaignCategoryId;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class CategoriesDataTransformer.
 */
class CategoriesDataTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if ($value == null) {
            return $value;
        }

        $tmp = [];

        if ($value instanceof ArrayCollection || is_array($value)) {
            foreach ($value as $v) {
                if ($v instanceof CampaignCategoryId) {
                    $tmp[] = $v->__toString();
                }
            }
        }

        return $tmp;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if ($value == null) {
            return $value;
        }

        $tmp = [];

        if ($value instanceof ArrayCollection || is_array($value)) {
            foreach ($value as $v) {
                $tmp[] = new CampaignCategoryId($v);
            }
        }

        return $tmp;
    }
}
