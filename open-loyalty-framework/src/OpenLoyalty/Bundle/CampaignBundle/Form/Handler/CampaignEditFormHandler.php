<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\CampaignBundle\Form\Handler;

use Broadway\CommandHandling\CommandBus;
use OpenLoyalty\Bundle\CampaignBundle\Service\CampaignProvider;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\Command\UpdateCampaign;
use OpenLoyalty\Component\Campaign\Domain\Model\Coupon;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class CampaignEditFormHandler.
 */
class CampaignEditFormHandler
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var CampaignProvider
     */
    private $campaignProvider;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * CampaignEditFormHandler constructor.
     *
     * @param CommandBus          $commandBus
     * @param CampaignProvider    $campaignProvider
     * @param TranslatorInterface $translator
     */
    public function __construct(CommandBus $commandBus, CampaignProvider $campaignProvider, TranslatorInterface $translator)
    {
        $this->commandBus = $commandBus;
        $this->campaignProvider = $campaignProvider;
        $this->translator = $translator;
    }

    /**
     * @param Campaign      $campaign
     * @param FormInterface $form
     *
     * @return bool
     */
    public function onSuccess(Campaign $campaign, FormInterface $form): bool
    {
        /** @var Campaign $data */
        $data = $form->getData();

        $deletedAndUsedCoupons = $this->campaignProvider->getDeletedAndUsedCoupons($campaign, $data->getCoupons());

        if (!$deletedAndUsedCoupons) {
            $this->commandBus->dispatch(new UpdateCampaign($campaign->getCampaignId(), $data->toArray()));

            return true;
        }

        $couponCodes = implode(', ', array_map(function (Coupon $coupon): string {
            return $coupon->getCode();
        }, $deletedAndUsedCoupons));

        $form->get('coupons')->addError(new FormError(sprintf(
            $this->translator->trans('campaign.removing_used_coupons_is_not_permitted'),
            $couponCodes
        )));

        return false;
    }
}
