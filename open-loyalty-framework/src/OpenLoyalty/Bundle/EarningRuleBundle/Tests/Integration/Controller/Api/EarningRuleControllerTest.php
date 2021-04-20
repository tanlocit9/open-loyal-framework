<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace OpenLoyalty\Bundle\EarningRuleBundle\Tests\Integration\Controller\Api;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseApiTest;
use OpenLoyalty\Bundle\EarningRuleBundle\DataFixtures\ORM\LoadEarningRuleData;
use OpenLoyalty\Bundle\EarningRuleBundle\Model\EarningRule;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleId;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleRepository;
use OpenLoyalty\Component\EarningRule\Domain\EventEarningRule;
use OpenLoyalty\Component\EarningRule\Domain\PointsEarningRule;
use OpenLoyalty\Component\EarningRule\Domain\ProductPurchaseEarningRule;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class EarningRuleControllerTest.
 */
class EarningRuleControllerTest extends BaseApiTest
{
    const LEVEL_ID = 'f99748f2-bf86-11e6-a4a6-cec0c932ce01';
    const POS_ID = '00000000-0000-474c-1111-b0dd880c07e2';

    /**
     * @var EarningRuleRepository
     */
    protected $repository;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        static::bootKernel();
        $this->repository = static::$kernel->getContainer()->get('oloy.earning_rule.repository');
    }

    /**
     * @test
     */
    public function it_creates_event_rule(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/earningRule',
            [
                'earningRule' => array_merge($this->getMainData(), [
                    'name' => 'Custom event assigned to level - test event - 100',
                    'type' => EarningRule::TYPE_EVENT,
                    'eventName' => 'test event',
                    'pointsAmount' => 100,
                    'levels' => [
                        self::LEVEL_ID,
                    ],
                ]),
            ]
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('earningRuleId', $data);
        $rule = $this->repository->byId(new EarningRuleId($data['earningRuleId']));
        $this->assertInstanceOf(EventEarningRule::class, $rule);
    }

    /**
     * @test
     */
    public function test_try_creates_geo_rule_with_fault_results(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/earningRule',
            [
                'earningRule' => array_merge($this->getMainData(), [
                    'name' => 'Geo rule for UnitTest',
                    'type' => EarningRule::TYPE_GEOLOCATION,
                    'latitude' => 'geo-latitude',
                    'longitude' => 51.11,
                    'radius' => '50',
                    'pointsAmount' => 12,
                    'levels' => [
                        self::LEVEL_ID,
                    ],
                    'pos' => [
                        self::POS_ID,
                    ],
                ]),
            ]
        );
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('form', $content, 'Response should return form key');
        $content = $content['form']['children'];

        $key = 'latitude';
        foreach (array_keys($content) as $item) {
            if ($item === $key) {
                $this->assertArrayHasKey('errors', $content[$key]);
                $this->assertEquals(1, count($content[$key]['errors']));
            }
        }
    }

    /**
     * @test
     */
    public function it_creates_event_rule_with_assign_to_pos(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/earningRule',
            [
                'earningRule' => array_merge($this->getMainData(), [
                    'name' => 'Custom event assigned to POS - test event - 100',
                    'type' => EarningRule::TYPE_EVENT,
                    'eventName' => 'test event',
                    'pointsAmount' => 100,
                    'levels' => [
                        self::LEVEL_ID,
                    ],
                    'pos' => [
                        self::POS_ID,
                    ],
                ]),
            ]
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('earningRuleId', $data);
        $rule = $this->repository->byId(new EarningRuleId($data['earningRuleId']));
        $this->assertInstanceOf(EventEarningRule::class, $rule);
        $this->assertEquals([self::POS_ID], $rule->getPos());
    }

    /**
     * @test
     */
    public function it_run_qr_code_rule(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/earningRule/qrcode/customer/00000000-0000-474c-b092-b0dd880c07e2',
            [
                'earningRule' => [
                    'code' => 'qrcodeabcd',
                    'earningRuleId' => 'e378c813-2116-448a-b125-564cef15f932',
                ],
            ]
        );
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(10, $data['points']);
    }

    /**
     * @test
     */
    public function it_runs_geo_rule_with_earning_rule_id(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            'api/earningRule/geolocation/customer/00000000-0000-474c-b092-b0dd880c07e2',
            [
                'earningRule' => [
                    'latitude' => 50.013992,
                    'longitude' => 15.046411,
                    'earningRuleId' => '00000001-0000-474c-b092-b0dd880c07e9',
                ],
            ]
        );
        $response = $client->getResponse();

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('points', $data);
        $this->assertEquals(2, $data['points']);
    }

    /**
     * @test
     */
    public function it_run_geo_rule(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            'api/earningRule/geolocation/customer/00000000-0000-474c-b092-b0dd880c07e2',
            [
               'earningRule' => [
                   'latitude' => 50.013992,
                   'longitude' => 15.046411,
               ],
            ]
        );
        $response = $client->getResponse();

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('points', $data);
        $this->assertEquals(2, $data['points']);
    }

    /**
     * @test
     */
    public function it_does_not_run_geo_rule_while_outside_radius(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            'api/earningRule/geolocation/customer/00000000-0000-474c-b092-b0dd880c07e2',
            [
                'earningRule' => [
                    'latitude' => 4.013992,
                    'longitude' => 5.046411,
                ],
            ]
        );
        $response = $client->getResponse();
        $this->assertEquals(400, $response->getStatusCode(), 'Response should have status 200');
    }

    /**
     * @test
     */
    public function it_creates_purchase_product_rule(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/earningRule',
            [
                'earningRule' => array_merge($this->getMainData(), [
                    'name' => 'Product purchase with test_sku - 100',
                    'type' => EarningRule::TYPE_PRODUCT_PURCHASE,
                    'skuIds' => ['test sku'],
                    'pointsAmount' => 100,
                    'levels' => [
                        self::LEVEL_ID,
                    ],
                ]),
            ]
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('earningRuleId', $data);
        $rule = $this->repository->byId(new EarningRuleId($data['earningRuleId']));
        $this->assertInstanceOf(ProductPurchaseEarningRule::class, $rule);
    }

    /**
     * @test
     */
    public function it_creates_custom_rule_using_limit(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/earningRule',
            self::getDataForCustomEventLimit('3 months')
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('earningRuleId', $data);
    }

    /**
     * @test
     */
    public function it_creates_points_rule(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/earningRule',
            [
                'earningRule' => array_merge($this->getMainData(), [
                    'name' => 'General spending rule - 1.1',
                    'type' => EarningRule::TYPE_POINTS,
                    'pointValue' => 1.1,
                    'excludedSKUs' => '123;222;111',
                    'labelsInclusionType' => 'exclude_labels',
                    'excludedLabels' => 'asas:aaa;ccc:eee',
                    'excludeDeliveryCost' => true,
                    'minOrderValue' => 111.11,
                    'levels' => [
                        self::LEVEL_ID,
                    ],
                ]),
            ]
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('earningRuleId', $data);
        /** @var PointsEarningRule $rule */
        $rule = $this->repository->byId(new EarningRuleId($data['earningRuleId']));
        $this->assertInstanceOf(PointsEarningRule::class, $rule);
        $this->assertEquals('exclude_labels', $rule->getLabelsInclusionType());
        $this->assertCount(2, $rule->getExcludedLabels());
        $this->assertCount(3, $rule->getExcludedSKUs());
        $this->assertEquals(111.11, $rule->getMinOrderValue());
    }

    /**
     * @test
     */
    public function it_creates_points_rule_with_included_labels(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/earningRule',
            [
                'earningRule' => array_merge($this->getMainData(), [
                    'name' => 'General spending rule - 1.1',
                    'type' => EarningRule::TYPE_POINTS,
                    'pointValue' => 1.1,
                    'excludedSKUs' => '123;222;111',
                    'labelsInclusionType' => 'include_labels',
                    'includedLabels' => 'asas:aaa;ccc:eee',
                    'excludeDeliveryCost' => true,
                    'minOrderValue' => 111.11,
                    'levels' => [
                        self::LEVEL_ID,
                    ],
                ]),
            ]
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('earningRuleId', $data);
        /** @var PointsEarningRule $rule */
        $rule = $this->repository->byId(new EarningRuleId($data['earningRuleId']));
        $this->assertInstanceOf(PointsEarningRule::class, $rule);
        $this->assertEquals('include_labels', $rule->getLabelsInclusionType());
        $this->assertCount(2, $rule->getIncludedLabels());
        $this->assertCount(3, $rule->getExcludedSKUs());
        $this->assertEquals(111.11, $rule->getMinOrderValue());
    }

    /**
     * @test
     */
    public function it_returns_earning_rule_with_proper_type(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'GET',
            '/api/earningRule/'.LoadEarningRuleData::EVENT_RULE_ID
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('hasPhoto', $data);
        $this->assertInternalType('bool', $data['hasPhoto']);
        $this->assertEquals(EarningRule::TYPE_EVENT, $data['type']);
    }

    /**
     * @test
     */
    public function it_returns_earning_rules(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'GET',
            '/api/earningRule'
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('earningRules', $data);
        $this->assertTrue(count($data['earningRules']) > 0, 'There should be at least one earning rule');
    }

    /**
     * @test
     */
    public function it_allows_to_edit_rule(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'PUT',
            '/api/earningRule/'.LoadEarningRuleData::EVENT_RULE_ID,
            [
                'earningRule' => array_merge($this->getMainData(), [
                    'eventName' => 'test event - edited',
                    'pointsAmount' => 100,
                    'levels' => [
                        self::LEVEL_ID,
                    ],
                ]),
            ]
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('earningRuleId', $data);
        $rule = $this->repository->byId(new EarningRuleId($data['earningRuleId']));
        $this->assertInstanceOf(EventEarningRule::class, $rule);
        $this->assertEquals('test event - edited', $rule->getEventName());
    }

    /**
     * @test
     */
    public function it_adds_new_photo_to_earning_rule(): void
    {
        $rules = $this->repository->findAllActive();
        /** @var EarningRule $first */
        $first = reset($rules);
        $client = $this->createAuthenticatedClient();
        $filesystem = static::$kernel->getContainer()->get('filesystem');
        $filesystem->copy(__DIR__.'/../../../data/sample.png', __DIR__.'/../../../data/sample_test.png');
        $uploadedFile = new UploadedFile(__DIR__.'/../../../data/sample_test.png', 'sample_test.png');

        $client->request(
            'POST',
            '/api/earningRule/'.$first->getEarningRuleId().'/photo',
            [],
            [
                'photo' => ['file' => $uploadedFile],
            ]
        );

        $response = $client->getResponse();
        $this->assertEquals('200', $response->getStatusCode());

        $getClient = $this->createAuthenticatedClient();
        $getClient->request(
            'GET',
            '/api/earningRule/'.$first->getEarningRuleId().'/photo'
        );
        $getResponse = $getClient->getResponse();
        $this->assertEquals(200, $getResponse->getStatusCode());
    }

    /**
     * @test
     */
    public function it_removes_photo_from_earning_rule(): void
    {
        $rules = $this->repository->findAllActive();

        if (!count($rules)) {
            return;
        }

        /** @var EarningRule $first */
        $first = reset($rules);

        $client = $this->createAuthenticatedClient();
        $filesystem = static::$kernel->getContainer()->get('filesystem');
        $filesystem->copy(__DIR__.'/../../../data/sample.png', __DIR__.'/../../../data/sample_test.png');
        $uploadedFile = new UploadedFile(__DIR__.'/../../../data/sample_test.png', 'sample_test.png');

        $client->request(
            'POST',
            '/api/earningRule/'.$first->getEarningRuleId().'/photo',
            [],
            [
                'photo' => ['file' => $uploadedFile],
            ]
        );

        $client->request(
            'GET',
            '/api/earningRule/'.$first->getEarningRuleId().'/photo'
        );
        $getResponse = $client->getResponse();
        $this->assertEquals(200, $getResponse->getStatusCode());

        $client->request(
            'DELETE',
            '/api/earningRule/'.$first->getEarningRuleId().'/photo'
        );

        $deleteResponse = $client->getResponse();

        $this->assertEquals(200, $deleteResponse->getStatusCode());

        $client->request(
            'GET',
            '/api/earningRule/'.$first->getEarningRuleId().'/photo'
        );
        $checkResponse = $client->getResponse();
        $this->assertEquals(404, $checkResponse->getStatusCode());
    }

    /**
     * @param string $name
     *
     * @return array
     */
    protected function getMainData($name = 'test'): array
    {
        return [
            'name' => $name,
            'description' => 'sth',
            'startAt' => '2016-08-01',
            'endAt' => '2016-10-10',
            'active' => false,
            'allTimeActive' => false,
        ];
    }

    /**
     * @param string $name
     *
     * @return array
     */
    protected function getDataForCustomEventLimit(string $name = 'year'): array
    {
        return [
            'earningRule' => [
                'type' => EarningRule::TYPE_CUSTOM_EVENT,
                'active' => true,
                'allTimeActive' => true,
                'description' => 'sth',
                'eventName' => str_replace(' ', '_', $name),
                'target' => 'level',
                'levels' => [
                    'e82c96cf-32a3-43bd-9034-4df343e50000',
                ],
                'limit' => [
                    'active' => true,
                    'limit' => 10,
                    'period' => $name,
                ],
                'name' => $name,
                'pointsAmount' => 10,
                'pos' => [
                    '517c1372-d845-493c-ae8e-91b449ff13f8',
                ],
            ],
        ];
    }
}
