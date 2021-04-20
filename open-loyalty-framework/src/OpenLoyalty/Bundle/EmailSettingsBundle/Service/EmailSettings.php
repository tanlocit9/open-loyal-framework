<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EmailSettingsBundle\Service;

use OpenLoyalty\Component\Email\Domain\ReadModel\Email;
use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader;

/**
 * Class EmailSettings.
 */
class EmailSettings implements EmailSettingsInterface
{
    /**
     * @var array
     */
    private $emails = [];

    /**
     * @var FilesystemLoader
     */
    private $filesystemLoader;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var array
     */
    private $defaultSettingsBag = [];

    /**
     * EmailSettings constructor.
     *
     * @param array             $emails
     * @param FilesystemLoader  $filesystemLoader
     * @param \Twig_Environment $twig
     */
    public function __construct(array $emails, FilesystemLoader $filesystemLoader, \Twig_Environment $twig)
    {
        $this->emails = $emails;
        $this->filesystemLoader = $filesystemLoader;
        $this->twig = $twig;
    }

    /**
     * {@inheritdoc}
     */
    public function addDefaultSettings(string $key, string $value): void
    {
        $this->defaultSettingsBag[$key] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultSetting(string $key): string
    {
        if (!isset($this->defaultSettingsBag[$key])) {
            throw new \InvalidArgumentException(sprintf('Setting %s does not exists!', $key));
        }

        return $this->defaultSettingsBag[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailsParameter(): array
    {
        foreach ($this->emails as &$settings) {
            $settings['content'] = '';
            // fill with default template if empty
            if ($this->filesystemLoader->exists($settings['template'])) {
                $sourceContext = $this->filesystemLoader->getSourceContext($settings['template']);
                $settings['content'] = $sourceContext->getCode();
            }

            $settings['name'] = $this->getTemplateName($settings['template']);
        }

        return $this->emails;
    }

    /**
     * {@inheritdoc}
     */
    public function filterByName(string $templateName): array
    {
        $results = current(
            array_filter(
                $this->getEmailsParameter(),
                function (array $templateData) use ($templateName): bool {
                    return $templateName === $this->getTemplateName($templateData['template']);
                }
            )
        );

        return false === $results ? [] : $results;
    }

    /**
     * {@inheritdoc}
     */
    public function templateExistsByName(string $templateName): bool
    {
        $results = array_filter(
            $this->emails,
            function (array $templateData) use ($templateName): bool {
                return $templateName === $this->getTemplateName($templateData['template']);
            }
        );

        return count($results) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalParams(Email $email): array
    {
        $additionalParams = [];

        foreach ($this->emails as $emailConfig) {
            if ($emailConfig['template'] == $email->getKey()) {
                $additionalParams['variables'] = $emailConfig['variables'];

                $template = $this->twig->createTemplate($email->getContent());
                $additionalParams['preview'] = $template->render(
                    array_combine($additionalParams['variables'], $additionalParams['variables'])
                );
            }
        }

        return $additionalParams;
    }

    /**
     * @param string $template
     *
     * @return string
     */
    private function getTemplateName(string $template): string
    {
        return str_replace(['OpenLoyaltyUserBundle:email:', '.html.twig'], '', $template);
    }
}
