<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Campaign\Infrastructure\Service;

use OpenLoyalty\Bundle\EmailBundle\DTO\EmailParameter;
use OpenLoyalty\Bundle\EmailBundle\Service\EmailMessageSenderInterface;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBought;
use OpenLoyalty\Component\Email\Domain\ReadModel\DoctrineEmailRepositoryInterface;

/**
 * Class CampaignRewardRedeemedEmailSettingsProvider.
 */
class CampaignRewardRedeemedEmailSender implements CampaignRewardRedeemedEmailSenderInterface
{
    private const TEMPLATE_NAME = 'OpenLoyaltyUserBundle:email:reward_redeemed.html.twig';

    /**
     * @var DoctrineEmailRepositoryInterface
     */
    private $repository;

    /**
     * @var EmailMessageSenderInterface
     */
    private $sender;

    /**
     * @var CampaignRewardRedeemedTemplateParameterCreatorInterface
     */
    private $parameterCreator;

    /**
     * @var string
     */
    private $senderEmail;

    /**
     * @var string
     */
    private $senderName;

    /**
     * CampaignRewardRedeemedEmailSettingsProvider constructor.
     *
     * @param DoctrineEmailRepositoryInterface                        $repository
     * @param EmailMessageSenderInterface                             $sender
     * @param CampaignRewardRedeemedTemplateParameterCreatorInterface $parameterCreator
     * @param string                                                  $senderEmail
     * @param string                                                  $senderName
     */
    public function __construct(
        DoctrineEmailRepositoryInterface $repository,
        EmailMessageSenderInterface $sender,
        CampaignRewardRedeemedTemplateParameterCreatorInterface $parameterCreator,
        string $senderEmail,
        string $senderName
    ) {
        $this->repository = $repository;
        $this->sender = $sender;
        $this->parameterCreator = $parameterCreator;
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
    }

    /**
     * {@inheritdoc}
     */
    public function send(CampaignBought $campaignBought): void
    {
        $emailSettings = $this->repository->getByKey(self::TEMPLATE_NAME);
        if (null === $emailSettings) {
            throw new \InvalidArgumentException(
                sprintf('No email settings found for %s', self::TEMPLATE_NAME)
            );
        }

        $receiverEmail = $emailSettings->getReceiverEmail();
        if (null !== $receiverEmail) {
            $templateParameter = $this->parameterCreator->parameters($campaignBought, self::TEMPLATE_NAME);

            foreach (explode(',', $receiverEmail) as $email) {
                $emailParameter = new EmailParameter(
                    $this->senderEmail,
                    $this->senderName,
                    $email,
                    $emailSettings->getSubject()
                );
                $this->sender->sendMessage($emailParameter, $templateParameter);
            }
        }
    }
}
