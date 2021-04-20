<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UtilityBundle\Command;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\SimpleEventBus;
use Doctrine\DBAL\Connection;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetailsProjector;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class RecreateReadModelsCommand.
 */
class RecreateReadModelsCommand extends ContainerAwareCommand
{
    protected $projectors = [
        'customer_details' => 'oloy.user.customer.read_model.projector.customer_details',
        'invitation_details' => 'oloy.user.customer.read_model.projector.invitation_details',
        'seller_details' => 'oloy.user.customer.read_model.projector.seller_details',
        'customers_belonging_to_one_level' => 'oloy.user.customer.read_model.projector.customers_belonging_to_one_level',
        'account_details' => 'oloy.points.account.read_model.projector.account_details',
        'point_transfer_details' => 'oloy.points.account.read_model.projector.point_transfer_details',
        'transaction_details' => TransactionDetailsProjector::class,
        'coupon_usage' => 'oloy.campaign.read_model.projector.coupon_usage',
        'campaign_usage' => 'oloy.campaign.read_model.projector.campaign_usage',
        'campaign_bought' => 'oloy.campaign.read_model.projector.campaign_bought',
    ];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('oloy:utility:read-models:recreate');
        $this->addOption('force', 'force', InputOption::VALUE_NONE);
        $this->addOption('index', 'i', InputOption::VALUE_OPTIONAL);
        $this->addOption('uuid', 'uuid', InputOption::VALUE_OPTIONAL);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            'This method will create read models. Make sure that you dropped them earlier using command '
            .'oloy:user:projections:purge (backup of es data is recommended).'.PHP_EOL.'Do you want to continue?',
            true
        );
        if (!$input->getOption('force')) {
            if (!$helper->ask($input, $output, $question)) {
                return;
            }
        }

        /** @var Connection $connection */
        $connection = $this->getContainer()->get('doctrine')->getConnection();
        $metadataSerializer = $this->getContainer()->get('broadway.serializer.metadata');
        $payloadSerializer = $this->getContainer()->get('broadway.serializer.payload');
        $events = [];

        $query = 'SELECT * FROM events ORDER BY id ASC';
        $params = [];
        if ($input->getOption('uuid')) {
            $query = 'SELECT * FROM events WHERE UUID = :uuid LIMIT 1';
            $params[':uuid'] = $input->getOption('uuid');
        }

        foreach ($connection->fetchAll($query, $params) as $event) {
            $events[] = new DomainMessage(
                $event['uuid'],
                $event['playhead'],
                $metadataSerializer->deserialize(json_decode($event['metadata'], true)),
                $payloadSerializer->deserialize(json_decode($event['payload'], true)),
                DateTime::fromString($event['recorded_on'])
            );
        }

        $eventBus = new SimpleEventBus();

        $index = $input->getOption('index');
        if ($index) {
            if (!array_key_exists($index, $this->projectors)) {
                $output->writeln('Bad index, choose one of the following indexes:');
                foreach (array_keys($this->projectors) as $option) {
                    $output->writeln('- '.$option);
                }
            }

            $this->projectors = [$this->projectors[$index]];
        }

        foreach ($this->projectors as $indexName => $projector) {
            $eventBus->subscribe($this->getContainer()->get($projector));
        }

        $eventStream = new DomainEventStream($events);
        $eventBus->publish($eventStream);
    }
}
