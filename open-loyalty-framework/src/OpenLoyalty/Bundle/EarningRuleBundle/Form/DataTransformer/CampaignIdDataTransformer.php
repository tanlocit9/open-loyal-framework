<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\Form\DataTransformer;

use OpenLoyalty\Component\EarningRule\Domain\CampaignId;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class CampaignIdDataTransformer.
 */
class CampaignIdDataTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof CampaignId) {
            return (string) $value;
        }

        return (string) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (null === $value) {
            return null;
        }

        if (\is_string($value)) {
            return new CampaignId($value);
        }

        return $value;
    }
}
