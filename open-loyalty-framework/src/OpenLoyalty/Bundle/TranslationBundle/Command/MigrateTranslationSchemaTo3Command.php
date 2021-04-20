<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TranslationBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MigrateTranslationSchemaTo3Command.
 */
class MigrateTranslationSchemaTo3Command extends Command
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * MigrateTranslationSchemaTo3Command constructor.
     *
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        parent::__construct();
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('oloy:translation:upgrade_to_3');
        $this->setDescription('Migrate database translations schema to version 3');
        $this->addOption('force');
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

        /** @var Connection $connection */
        $connection = $this->registry->getConnection();

        $this->changeIndexes($connection);
        $this->createSchema($connection);
        $this->migrateData($connection);

        $output->writeln('<info>Translation database schema has been updated successfully.</info>');
    }

    /**
     * @param Connection $connection
     *
     * @throws DBALException
     */
    private function changeIndexes(Connection $connection): void
    {
        // changes column name of primary indexes.
        $connection->exec('
            ALTER TABLE ol__level RENAME COLUMN level_id TO id;
            ALTER TABLE ol__campaign RENAME COLUMN campaign_id TO id;
        ');
    }

    /**
     * @param Connection $connection
     *
     * @throws DBALException
     */
    private function createSchema(Connection $connection): void
    {
        // creates level translation table
        $connection->exec("
            CREATE SEQUENCE level_translation_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
            CREATE TABLE level_translation (id INT NOT NULL, translatable_id UUID DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, description TEXT DEFAULT NULL, locale VARCHAR(255) NOT NULL, PRIMARY KEY(id));
            CREATE INDEX IDX_459A23322C2AC5D3 ON level_translation (translatable_id);
            CREATE UNIQUE INDEX level_translation_unique_translation ON level_translation (translatable_id, locale);
            COMMENT ON COLUMN level_translation.translatable_id IS '(DC2Type:level_id)';
            ALTER TABLE level_translation ADD CONSTRAINT FK_459A23322C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES ol__level (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
        ");

        // creates campaign translation table
        $connection->exec("
            CREATE SEQUENCE campaign_translation_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
            CREATE TABLE campaign_translation (id INT NOT NULL, translatable_id UUID DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, short_description TEXT DEFAULT NULL, conditions_description TEXT DEFAULT NULL, usage_instruction TEXT DEFAULT NULL, brand_description TEXT DEFAULT NULL, locale VARCHAR(255) NOT NULL, PRIMARY KEY(id));
            CREATE INDEX IDX_6CA379952C2AC5D3 ON campaign_translation (translatable_id);
            CREATE UNIQUE INDEX campaign_translation_unique_translation ON campaign_translation (translatable_id, locale);
            COMMENT ON COLUMN campaign_translation.translatable_id IS '(DC2Type:campaign_id)';
            ALTER TABLE campaign_translation ADD CONSTRAINT FK_6CA379952C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES ol__campaign (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
        ");

        // creates campaign category translation table
        $connection->exec("
            CREATE SEQUENCE campaign_category_translation_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
            CREATE TABLE campaign_category_translation (id INT NOT NULL, translatable_id UUID DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) NOT NULL, PRIMARY KEY(id));
            CREATE INDEX IDX_444856B52C2AC5D3 ON campaign_category_translation (translatable_id);
            CREATE UNIQUE INDEX campaign_category_translation_unique_translation ON campaign_category_translation (translatable_id, locale);
            COMMENT ON COLUMN campaign_category_translation.translatable_id IS '(DC2Type:campaign_category_id)';
            ALTER TABLE campaign_category_translation ADD CONSTRAINT FK_444856B52C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES ol__campaign_categories (id) ON DELETE CASCADE NOT DEFERRABLE 
        ");
    }

    /**
     * @param Connection $connection
     *
     * @throws DBALException
     */
    private function migrateData(Connection $connection): void
    {
        // migrate levels data
        $connection->exec("
            INSERT INTO LEVEL_TRANSLATION(id, translatable_id, name, description, locale)
            SELECT nextval('level_translation_id_seq'), id, name, description, 'en' FROM ol__level
        ");

        // migrate campaigns data
        $connection->exec("
            INSERT INTO campaign_translation(id, translatable_id, name, short_description, conditions_description, usage_instruction, brand_description, locale)
            SELECT nextval('campaign_translation_id_seq'), id, name, short_description, conditions_description, usage_instruction, brand_description, 'en' FROM ol__campaign
        ");

        // migrate campaign categories data
        $connection->exec("
            INSERT INTO campaign_category_translation(id, translatable_id, name, locale)
            SELECT nextval('campaign_category_translation_id_seq'), id, name, 'en' FROM ol__campaign_categories
        ");
    }
}
