<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Command;

use Gaufrette\Filesystem;
use OpenLoyalty\Bundle\SettingsBundle\Model\TranslationsEntry;
use OpenLoyalty\Bundle\SettingsBundle\Service\TranslationsProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MigrateTranslationCommand.
 */
class MigrateTranslationCommand extends Command
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var TranslationsProvider
     */
    protected $translationProvider;

    /**
     * MigrateTranslationCommand constructor.
     *
     * @param Filesystem           $filesystem
     * @param TranslationsProvider $translationsProvider
     */
    public function __construct(Filesystem $filesystem, TranslationsProvider $translationsProvider)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
        $this->translationProvider = $translationsProvider;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('oloy:translation:migrate2database');
        $this->setDescription('Migrate translation files (located in app/Resources/frontend_translations) to database');
        $this->addArgument('filename', InputArgument::REQUIRED, 'Translations file name');
        $this->addArgument('code', InputArgument::REQUIRED, 'Translation code which will be used in database');
        $this->addOption('force');
        $this->addOption('setAsDefault');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        if (!$input->getOption('force')) {
            $output->writeln('<comment>Nothing to do. Run this command with force parameter.</comment>');

            return;
        }

        $filename = $input->getArgument('filename');
        $code = $input->getArgument('code');

        if (!$this->filesystem->has($filename)) {
            $output->writeln(sprintf('<error>Translation file "%s" does not exist in app/Resources/frontend_translations.</error>', $filename));

            return;
        }

        if ($this->translationProvider->hasTranslation($code)) {
            $output->writeln(sprintf('<error>Translation code "%s" exists on database. Overriding is not possible.</error>', $code));

            return;
        }

        $setAsDefault = !empty($input->getOption('setAsDefault'));
        $content = $this->filesystem->get($filename)->getContent();
        $translationEntry = new TranslationsEntry($code, sprintf('%s (%s)', $code, $filename), $content, null, 0, $setAsDefault);
        $this->translationProvider->create($translationEntry);

        $output->writeln('<info>Translation has been migrated to database successfully. Go to settings and set it as default.</info>');
    }
}
