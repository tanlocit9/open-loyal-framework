<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SegmentBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OpenLoyalty\Bundle\PosBundle\DataFixtures\ORM\LoadPosData;
use OpenLoyalty\Bundle\UserBundle\DataFixtures\ORM\LoadUserData;
use OpenLoyalty\Component\Segment\Domain\Command\ActivateSegment;
use OpenLoyalty\Component\Segment\Domain\Command\CreateSegment;
use OpenLoyalty\Component\Segment\Domain\Model\Criteria\Anniversary;
use OpenLoyalty\Component\Segment\Domain\Model\Criterion;
use OpenLoyalty\Component\Segment\Domain\SegmentId;
use Symfony\Bridge\Doctrine\Tests\Fixtures\ContainerAwareFixture;

/**
 * Class LoadSegmentData.
 */
class LoadSegmentData extends ContainerAwareFixture implements OrderedFixtureInterface
{
    const SEGMENT_ID = '00000000-0000-0000-0000-000000000000';
    const SEGMENT2_ID = '00000000-0000-0000-0000-000000000002';
    const SEGMENT3_ID = '00000000-0000-0000-0000-000000000003';
    const SEGMENT4_ID = '00000000-0000-0000-0000-000000000004';
    const SEGMENT5_ID = '00000000-0000-0000-0000-000000000005';
    const SEGMENT6_ID = '00000000-0000-0000-0000-000000000006';
    const SEGMENT7_ID = '00000000-0000-0000-0000-000000000007';
    const SEGMENT8_ID = '00000000-0000-0000-0000-000000000008';
    const SEGMENT9_ID = '00000000-0000-0000-0000-000000000009';
    const SEGMENT10_ID = '00000000-0000-0000-0000-000000000010';
    const SEGMENT11_ID = '00000000-0000-0000-0000-000000000011';

    public function load(ObjectManager $manager)
    {
        $this->container
            ->get('broadway.command_handling.command_bus')
            ->dispatch(
                new CreateSegment(new SegmentId(self::SEGMENT_ID), [
                    'name' => 'test',
                    'description' => 'desc',
                    'parts' => [
                        [
                            'segmentPartId' => '00000000-0000-0000-0000-000000000000',
                            'criteria' => [
                                [
                                    'type' => Criterion::TYPE_BOUGHT_IN_POS,
                                    'criterionId' => '00000000-0000-0000-0000-000000000000',
                                    'posIds' => [LoadPosData::POS_ID],
                                ],
                                [
                                    'type' => Criterion::TYPE_AVERAGE_TRANSACTION_AMOUNT,
                                    'criterionId' => '00000000-0000-0000-0000-000000000001',
                                    'fromAmount' => 1,
                                    'toAmount' => 10000,
                                ],
                                [
                                    'type' => Criterion::TYPE_TRANSACTION_COUNT,
                                    'criterionId' => '00000000-0000-0000-0000-000000000002',
                                    'min' => 10,
                                    'max' => 20,
                                ],
                            ],
                        ],
                    ],
                ])
            );
        $this->container
            ->get('broadway.command_handling.command_bus')
            ->dispatch(
                new CreateSegment(new SegmentId(self::SEGMENT2_ID), [
                    'name' => 'anniversary',
                    'description' => 'desc',
                    'parts' => [
                        [
                            'segmentPartId' => '00000000-0000-0000-0000-000000000001',
                            'criteria' => [
                                [
                                    'type' => Criterion::TYPE_ANNIVERSARY,
                                    'criterionId' => '00000000-0000-0000-0000-000000000011',
                                    'days' => 10,
                                    'anniversaryType' => Anniversary::TYPE_BIRTHDAY,
                                ],
                            ],
                        ],
                    ],
                ])
            );
        $this->container
            ->get('broadway.command_handling.command_bus')
            ->dispatch(
                new CreateSegment(new SegmentId(self::SEGMENT3_ID), [
                    'name' => 'purchase period',
                    'description' => 'desc',
                    'parts' => [
                        [
                            'segmentPartId' => '00000000-0000-0000-0000-000000000033',
                            'criteria' => [
                                [
                                    'type' => Criterion::TYPE_PURCHASE_PERIOD,
                                    'criterionId' => '00000000-0000-0000-0000-000000000033',
                                    'fromDate' => new \DateTime('2014-12-01'),
                                    'toDate' => new \DateTime('2015-01-01'),
                                ],
                            ],
                        ],
                    ],
                ])
            );
        $this->container
            ->get('broadway.command_handling.command_bus')
            ->dispatch(
                new CreateSegment(new SegmentId(self::SEGMENT4_ID), [
                    'name' => 'last purchase 10 days ago',
                    'description' => 'desc',
                    'parts' => [
                        [
                            'segmentPartId' => '00000000-0000-0000-0000-000000000044',
                            'criteria' => [
                                [
                                    'type' => Criterion::TYPE_LAST_PURCHASE_N_DAYS_BEFORE,
                                    'criterionId' => '00000000-0000-0000-0000-000000000045',
                                    'days' => 10,
                                ],
                            ],
                        ],
                    ],
                ])
            );
        $this->container
            ->get('broadway.command_handling.command_bus')
            ->dispatch(
                new CreateSegment(new SegmentId(self::SEGMENT5_ID), [
                    'name' => 'transaction amount 10-50',
                    'description' => 'desc',
                    'parts' => [
                        [
                            'segmentPartId' => '00000000-0000-0000-0000-000000000055',
                            'criteria' => [
                                [
                                    'type' => Criterion::TYPE_TRANSACTION_AMOUNT,
                                    'criterionId' => '00000000-0000-0000-0000-000000000055',
                                    'fromAmount' => 10,
                                    'toAmount' => 50,
                                ],
                            ],
                        ],
                    ],
                ])
            );
        $this->container
            ->get('broadway.command_handling.command_bus')
            ->dispatch(
                new CreateSegment(new SegmentId(self::SEGMENT6_ID), [
                    'name' => '10 percent in pos',
                    'description' => 'desc',
                    'parts' => [
                        [
                            'segmentPartId' => '00000000-0000-0000-0000-000000000066',
                            'criteria' => [
                                [
                                    'type' => Criterion::TYPE_TRANSACTION_PERCENT_IN_POS,
                                    'criterionId' => '00000000-0000-0000-0000-000000000066',
                                    'percent' => 0.10,
                                    'posId' => LoadPosData::POS_ID,
                                ],
                            ],
                        ],
                    ],
                ])
            );
        $this->container
            ->get('broadway.command_handling.command_bus')
            ->dispatch(
                new CreateSegment(new SegmentId(self::SEGMENT7_ID), [
                    'name' => 'bought skus',
                    'description' => 'desc',
                    'parts' => [
                        [
                            'segmentPartId' => '00000000-0000-0000-0000-000000000077',
                            'criteria' => [
                                [
                                    'type' => Criterion::TYPE_BOUGHT_SKUS,
                                    'criterionId' => '00000000-0000-0000-0000-000000000077',
                                    'skuIds' => ['SKU1'],
                                ],
                            ],
                        ],
                    ],
                ])
            );
        $this->container
            ->get('broadway.command_handling.command_bus')
            ->dispatch(
                new CreateSegment(new SegmentId(self::SEGMENT8_ID), [
                    'name' => 'bought makers',
                    'description' => 'desc',
                    'parts' => [
                        [
                            'segmentPartId' => '00000000-0000-0000-0000-000000000088',
                            'criteria' => [
                                [
                                    'type' => Criterion::TYPE_BOUGHT_MAKERS,
                                    'criterionId' => '00000000-0000-0000-0000-000000000099',
                                    'makers' => ['company'],
                                ],
                            ],
                        ],
                    ],
                ])
            );
        $this->container
            ->get('broadway.command_handling.command_bus')
            ->dispatch(
                new CreateSegment(new SegmentId(self::SEGMENT9_ID), [
                    'name' => 'bought labels',
                    'description' => 'desc',
                    'parts' => [
                        [
                            'segmentPartId' => '00000000-0000-0000-0000-000000000099',
                            'criteria' => [
                                [
                                    'type' => Criterion::TYPE_BOUGHT_LABELS,
                                    'criterionId' => '00000000-0000-0000-0000-000000000999',
                                    'labels' => [
                                        ['key' => 'test', 'value' => 'label'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ])
            );
        $this->container
            ->get('broadway.command_handling.command_bus')
            ->dispatch(
                new CreateSegment(new SegmentId(self::SEGMENT10_ID), [
                    'name' => 'customer list',
                    'description' => 'desc',
                    'parts' => [
                        [
                            'segmentPartId' => '00000000-0000-0000-0000-000000000100',
                            'criteria' => [
                                [
                                    'type' => Criterion::TYPE_CUSTOMER_LIST,
                                    'criterionId' => '00000000-0000-0000-0000-000000001000',
                                    'customers' => [
                                        LoadUserData::USER1_USER_ID,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ])
            );

        $this->container
            ->get('broadway.command_handling.command_bus')
            ->dispatch(
                new CreateSegment(new SegmentId(self::SEGMENT11_ID), [
                    'name' => 'customer list with label',
                    'description' => 'desc',
                    'parts' => [
                        [
                            'segmentPartId' => '00000000-0000-0000-0000-000000000111',
                            'criteria' => [
                                [
                                    'type' => Criterion::TYPE_CUSTOMER_LIST,
                                    'criterionId' => '00000000-0000-0000-0000-000000001111',
                                    'customers' => [
                                        LoadUserData::USER1_USER_ID,
                                    ],
                                ],
                            ],
                        ],
                        [
                            'segmentPartId' => '00000000-0000-0000-0000-000000000112',
                            'criteria' => [
                                [
                                    'type' => Criterion::TYPE_CUSTOMER_HAS_LABELS,
                                    'criterionId' => '00000000-0000-0000-0000-000000001112',
                                    'labels' => [
                                        ['key' => 'test'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ])
            );
        $this->container
            ->get('broadway.command_handling.command_bus')
            ->dispatch(new ActivateSegment(new SegmentId(self::SEGMENT11_ID)));
    }

    public function getOrder()
    {
        return 99;
    }
}
