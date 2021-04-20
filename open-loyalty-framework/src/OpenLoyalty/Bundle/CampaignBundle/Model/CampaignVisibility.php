<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Model;

use OpenLoyalty\Component\Campaign\Domain\Model\CampaignVisibility as BaseCampaignVisibility;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CampaignVisibility.
 */
class CampaignVisibility extends BaseCampaignVisibility
{
    /**
     * CampaignVisibility constructor.
     */
    public function __construct()
    {
        parent::__construct(true, null, null);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'allTimeVisible' => $this->allTimeVisible,
            'visibleFrom' => $this->visibleFrom,
            'visibleTo' => $this->visibleTo,
        ];
    }

    /**
     * @param ExecutionContextInterface $context
     * @Assert\Callback()
     */
    public function validate(ExecutionContextInterface $context)
    {
        if ($this->allTimeVisible) {
            return;
        }

        if (!$this->visibleFrom) {
            $context->buildViolation((new NotBlank())->message)->atPath('visibleFrom')->addViolation();
        }

        if (!$this->visibleTo) {
            $context->buildViolation((new NotBlank())->message)->atPath('visibleTo')->addViolation();
        }
    }
}
