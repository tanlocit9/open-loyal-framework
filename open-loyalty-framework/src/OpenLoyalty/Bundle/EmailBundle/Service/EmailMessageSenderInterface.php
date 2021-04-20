<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\EmailBundle\Service;

use OpenLoyalty\Bundle\EmailBundle\DTO\EmailParameter;
use OpenLoyalty\Bundle\EmailBundle\DTO\EmailTemplateParameter;

/**
 * Class RewardRedeemedEmailSender.
 */
interface EmailMessageSenderInterface
{
    /**
     * @param EmailParameter         $emailParameter
     * @param EmailTemplateParameter $templateParameter
     *
     * @return bool
     */
    public function sendMessage(EmailParameter $emailParameter, EmailTemplateParameter $templateParameter): bool;
}
