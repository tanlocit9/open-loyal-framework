<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EmailSettingsBundle\Command;

use Broadway\CommandHandling\SimpleCommandBus;
use Broadway\UuidGenerator\Rfc4122\Version4Generator;
use OpenLoyalty\Bundle\EmailSettingsBundle\Service\EmailSettings;
use OpenLoyalty\Bundle\EmailSettingsBundle\Service\EmailSettingsInterface;
use OpenLoyalty\Component\Email\Domain\Command\CreateEmail;
use OpenLoyalty\Component\Email\Domain\Command\UpdateEmail;
use OpenLoyalty\Component\Email\Domain\EmailId;
use OpenLoyalty\Component\Email\Domain\ReadModel\DoctrineEmailRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class AddEmailTemplate.
 */
final class AddEmailTemplate extends Command
{
    /**
     * @var DoctrineEmailRepositoryInterface
     */
    private $emailReadModel;

    /**
     * @var EmailSettingsInterface
     */
    private $emailSettingsService;

    /**
     * @var Version4Generator
     */
    private $uuidGenerator;

    /**
     * @var SimpleCommandBus
     */
    private $commandBus;

    /**
     * AddEmailTemplate constructor.
     *
     * @param DoctrineEmailRepositoryInterface $emailReadModel
     * @param EmailSettings                    $emailSettingsService
     * @param Version4Generator                $uuidGenerator
     * @param SimpleCommandBus                 $commandBus
     */
    public function __construct(
        DoctrineEmailRepositoryInterface $emailReadModel,
        EmailSettings $emailSettingsService,
        Version4Generator $uuidGenerator,
        SimpleCommandBus $commandBus
    ) {
        $this->emailReadModel = $emailReadModel;
        $this->emailSettingsService = $emailSettingsService;
        $this->uuidGenerator = $uuidGenerator;
        $this->commandBus = $commandBus;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function configure(): void
    {
        $this
            ->setName('oloy:email:template:add')
            ->addArgument('template', InputArgument::REQUIRED)
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Forces update and overrides existing template');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $templateName = $input->getArgument('template');

        if (!$this->emailSettingsService->templateExistsByName($templateName)) {
            $output->writeln('Invalid template name. Possible choices:');
            foreach ($this->emailSettingsService->getEmailsParameter() as $email) {
                $output->writeln('- '.$email['name']);
            }

            return;
        }

        $emailTemplate = $this->emailSettingsService->filterByName($templateName);
        $existingEmailTemplate = $this->emailReadModel->getByKey($emailTemplate['template']);

        $style = new SymfonyStyle($input, $output);
        switch (true) {
            case null == $existingEmailTemplate:
                $this->addEmailTemplate($emailTemplate);
                $style->success(sprintf('Added email %s to the database.', $templateName));
                break;
            case $existingEmailTemplate && $input->getOption('force'):
                $this->updateEmailTemplate($existingEmailTemplate->getId(), $emailTemplate);
                $style->success(sprintf('Updated email %s in database.', $templateName));
                break;
            case $existingEmailTemplate && !$input->getOption('force'):
                $style->warning('Email template %s already exists in database. Use --force to override it.');
                break;
        }
    }

    /**
     * @param array $email
     *
     * @throws \Exception
     */
    private function addEmailTemplate(array $email): void
    {
        $uuid = $this->uuidGenerator->generate();

        $this->commandBus->dispatch(
            new CreateEmail(
                new EmailId($uuid),
                [
                    'key' => $email['template'],
                    'subject' => $email['subject'],
                    'content' => $email['content'],
                    'sender_name' => $this->emailSettingsService->getDefaultSetting('mailer_from_name'),
                    'sender_email' => $this->emailSettingsService->getDefaultSetting('mailer_from_address'),
                ]
            )
        );
    }

    /**
     * @param string $emailId
     * @param array  $emailTemplate
     *
     * @throws \Exception
     */
    private function updateEmailTemplate(string $emailId, array $emailTemplate): void
    {
        $emailTemplateData = [
            'key' => $emailTemplate['template'],
            'subject' => $emailTemplate['subject'],
            'content' => $emailTemplate['content'],
            'sender_name' => $this->emailSettingsService->getDefaultSetting('mailer_from_name'),
            'sender_email' => $this->emailSettingsService->getDefaultSetting('mailer_from_address'),
        ];

        $this->commandBus->dispatch(new UpdateEmail(new EmailId($emailId), $emailTemplateData));
    }
}
