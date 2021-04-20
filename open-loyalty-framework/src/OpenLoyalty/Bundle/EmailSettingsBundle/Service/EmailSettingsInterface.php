<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EmailSettingsBundle\Service;

use OpenLoyalty\Component\Email\Domain\ReadModel\Email;

/**
 * Class EmailSettings.
 */
interface EmailSettingsInterface
{
    /**
     * @param string $key
     * @param string $value
     */
    public function addDefaultSettings(string $key, string $value): void;

    /**
     * @param string $key
     *
     * @return string
     */
    public function getDefaultSetting(string $key): string;

    /**
     * @return array
     *
     * @throws \Twig_Error_Loader
     */
    public function getEmailsParameter(): array;

    /**
     * @param string $templateName
     *
     * @return array
     *
     * @throws \Twig_Error_Loader
     */
    public function filterByName(string $templateName): array;

    /**
     * @param string $templateName
     *
     * @return bool
     */
    public function templateExistsByName(string $templateName): bool;

    /**
     * @param Email $email
     *
     * @return array
     *
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Syntax
     */
    public function getAdditionalParams(Email $email): array;
}
