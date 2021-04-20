<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyaltyPlugin\SalesManagoBundle\DataProvider;

use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerAgreementsUpdatedSystemEvent;
use OpenLoyalty\Component\Segment\Domain\Segment;
use OpenLoyalty\Component\Segment\Domain\SegmentRepository;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;

/**
 * Class TagsDataProvider.
 */
class TagsDataProvider implements DataProviderInterface
{
    /**
     * @var CustomerDetailsRepository
     */
    protected $customerDetailsRepository;

    /**
     * @var SegmentRepository
     */
    protected $segmentRepository;

    /**
     * SalesManagoContactTagsSender constructor.
     *
     * @param CustomerDetailsRepository $customerDetailsRepository
     * @param SegmentRepository         $segmentRepository
     */
    public function __construct(
        CustomerDetailsRepository $customerDetailsRepository,
        SegmentRepository $segmentRepository
    ) {
        $this->customerDetailsRepository = $customerDetailsRepository;
        $this->segmentRepository = $segmentRepository;
    }

    /**
     * @param CustomerDetails $data
     *
     * @return array
     */
    public function provideData($data)
    {
        $customer = $this->customerDetailsRepository->find($data->getCustomerId());

        /** @var Segment $segmentTag */
        $segmentTag = $this->segmentRepository->byId($data->getSegmentId());

        return ['email' => $customer->getEmail(), 'tag' => $segmentTag->getName()];
    }

    /**
     * @param CustomerAgreementsUpdatedSystemEvent $event
     *
     * @return array|void
     */
    public function getAgreementTags($event)
    {
        $changeSet = $event->getChangeSet();
        $customer = $this->customerDetailsRepository->find($event->getCustomerId());
        $tags = [];
        if (isset($changeSet['agreement1'])) {
            $tags['tags'][] = ($changeSet['agreement1']['new'] == true) ? 'OL-LP-SIGNEDIN' : 'OL-LP-SIGNEDOUT';
            $tags['removeTags'][] = ($changeSet['agreement1']['new'] == false) ? 'OL-LP-SIGNEDIN' : 'OL-LP-SIGNEDOUT';
        }
        if (isset($changeSet['agreement2'])) {
            $tags['tags'][] = ($changeSet['agreement2']['new'] == true) ? 'OL-NSL-SUBSCRIBE' : 'OL-NSL-UNSUBSCRIBE';
            $tags['removeTags'][] = ($changeSet['agreement2']['new'] == false) ? 'OL-NSL-SUBSCRIBE' : 'OL-NSL-UNSUBSCRIBE';
        }
        if ($tags) {
            return ['email' => $customer->getEmail(), 'tag' => $tags];
        }

        return;
    }
}
