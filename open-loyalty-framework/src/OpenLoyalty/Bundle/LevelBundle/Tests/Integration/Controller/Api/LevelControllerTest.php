<?php

namespace OpenLoyalty\Bundle\LevelBundle\Tests\Integration\Controller\Api;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseApiTest;
use OpenLoyalty\Bundle\LevelBundle\DataFixtures\ORM\LoadLevelData;
use OpenLoyalty\Component\Level\Domain\Level;
use OpenLoyalty\Component\Level\Domain\LevelId;
use OpenLoyalty\Component\Level\Domain\LevelRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class LevelControllerTest.
 */
class LevelControllerTest extends BaseApiTest
{
    /** @var LevelRepository */
    private $repository;

    protected function setUp()
    {
        parent::setUp();
        static::bootKernel();
        $this->repository = static::$kernel->getContainer()->get('oloy.level.repository');
    }

    /**
     * @test
     */
    public function it_creates_new_level()
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/level/create',
            [
                'level' => [
                    'conditionValue' => 15,
                    'reward' => [
                        'name' => 'new reward',
                        'value' => 15,
                        'code' => 'xyz',
                    ],
                    'translations' => [
                        'en' => [
                            'name' => 'test level',
                            'description' => 'test level',
                        ],
                        'pl' => [
                            'name' => 'test level pl',
                            'description' => 'test level pl',
                        ],
                    ],
                ],
            ]
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('id', $data);
    }

    /**
     * @test
     */
    public function it_returns_level_data()
    {
        static::$kernel->boot();
        /** @var LevelRepository $repo */
        $repo = static::$kernel->getContainer()->get('oloy.level.repository');
        /** @var Level $level */
        $level = $repo->findAll()[0];
        $client = $this->createAuthenticatedClient();
        $client->request(
            'GET',
            '/api/level/'.((string) $level->getLevelId())
        );
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200'.$response->getContent());
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('hasPhoto', $data);
        $this->assertInternalType('bool', $data['hasPhoto']);
        $this->assertEquals((string) $level->getLevelId(), $data['id']);

        //translations
        //en
        $this->assertCount(2, $data['translations']);
        $this->assertArrayHasKey('name', $data['translations'][0]);
        $this->assertArrayHasKey('description', $data['translations'][0]);
        $this->assertArrayHasKey('locale', $data['translations'][0]);
        //pl
        $this->assertArrayHasKey('name', $data['translations'][1]);
        $this->assertArrayHasKey('description', $data['translations'][1]);
        $this->assertArrayHasKey('locale', $data['translations'][1]);
    }

    /**
     * @test
     */
    public function it_creates_new_level_and_set_reward_data()
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/level/create',
            [
                'level' => [
                    'conditionValue' => 15,
                    'reward' => [
                        'name' => 'new reward',
                        'value' => 15,
                        'code' => 'xyz',
                    ],
                    'translations' => [
                        'en' => [
                            'name' => 'test level',
                            'description' => 'test level',
                        ],
                        'pl' => [
                            'name' => 'test level PL',
                            'description' => 'test level PL',
                        ],
                    ],
                ],
            ]
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('id', $data);
        $client->request(
            'GET',
            '/api/level/'.$data['id']
        );
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals('new reward', $data['reward']['name']);
        $this->assertEquals(0.15, $data['reward']['value']);
        $this->assertEquals('xyz', $data['reward']['code']);
    }

    /**
     * @test
     */
    public function it_creates_new_level_and_add_new_special_rewards()
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/level/create',
            [
                'level' => [
                    'conditionValue' => 15,
                    'reward' => [
                        'name' => 'new reward',
                        'value' => 15,
                        'code' => 'xyz',
                    ],
                    'specialRewards' => [
                        0 => [
                            'name' => 'special reward - added',
                            'value' => 20,
                            'code' => 'spec',
                            'startAt' => '2016-10-10',
                            'endAt' => '2016-11-10',
                            'active' => true,
                        ],
                        1 => [
                            'name' => 'special reward - added 2',
                            'value' => 10,
                            'code' => 'spec2',
                            'startAt' => '2016-09-10',
                            'endAt' => '2016-11-10',
                            'active' => false,
                        ],
                    ],
                    'translations' => [
                        'en' => [
                            'name' => 'test level',
                            'description' => 'test level',
                        ],
                        'pl' => [
                            'name' => 'test level pl',
                            'description' => 'test level pl',
                        ],
                    ],
                ],
            ]
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('id', $data);
        $level = $this->getLevel($data['id']);

        $this->assertTrue(count($level->getSpecialRewards()) == 2, 'There should be 2 special rewards');
        $specialRewards = $level->getSpecialRewards();
        $this->assertInstanceOf(\DateTime::class, $specialRewards[0]->getStartAt());
        $this->assertInstanceOf(\DateTime::class, $specialRewards[0]->getEndAt());
        $this->assertInstanceOf(\DateTime::class, $specialRewards[1]->getStartAt());
        $this->assertInstanceOf(\DateTime::class, $specialRewards[1]->getEndAt());
        $this->assertEquals(true, $specialRewards[0]->isActive());
        $this->assertEquals('special reward - added 2', $specialRewards[1]->getName());
    }

    /**
     * @test
     */
    public function it_returns_levels_list()
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'GET',
            '/api/level'
        );
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('levels', $data);
        $this->assertTrue(count($data['levels']) > 0, 'Contains at least one element');
    }

    /**
     * @test
     */
    public function it_updates_level_name()
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'PUT',
            '/api/level/'.LoadLevelData::LEVEL1_ID,
            [
                'level' => [
                    'conditionValue' => 20,
                    'reward' => [
                        'name' => 'new reward',
                        'value' => 5,
                        'code' => 'xyz',
                    ],
                    'translations' => [
                        'en' => [
                            'name' => 'updated level name EN',
                            'description' => 'updated level desc EN',
                        ],
                        'pl' => [
                            'name' => 'updated level name PL',
                            'description' => 'updated level desc PL',
                        ],
                    ],
                ],
            ]
        );
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals(LoadLevelData::LEVEL1_ID, $data['id']);
        $level = $this->getLevel(LoadLevelData::LEVEL1_ID);
        $this->assertEquals('updated level name EN', $level->getName(), 'Name should be now "updated level name"');
    }

    /**
     * @test
     */
    public function it_updates_level_special_rewards()
    {
        static::$kernel->boot();
        $client = $this->createAuthenticatedClient();
        $client->request(
            'PUT',
            '/api/level/'.LoadLevelData::LEVEL2_ID,
            [
                'level' => [
                    'conditionValue' => 200,
                    'reward' => [
                        'name' => 'new reward - edited',
                        'value' => 11,
                        'code' => 'xyz',
                    ],
                    'specialRewards' => [
                        0 => [
                            'name' => 'special reward - updated',
                            'value' => 90,
                            'code' => 'spec',
                            'startAt' => '2016-10-10',
                            'endAt' => '2016-11-10',
                            'active' => true,
                        ],
                    ],
                    'translations' => [
                        'en' => [
                            'name' => 'updated level name',
                            'description' => 'updated level desc',
                        ],
                        'pl' => [
                            'name' => 'updated level name PL',
                            'description' => 'updated level desc PL',
                        ],
                    ],
                ],
            ]
        );
        $response = $client->getResponse();

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals(LoadLevelData::LEVEL2_ID, $data['id']);
        $level = $this->getLevel(LoadLevelData::LEVEL2_ID);
        $this->assertTrue(count($level->getSpecialRewards()) == 1, 'There should be 1 special rewards');
        $specialRewards = $level->getSpecialRewards();
        $this->assertInstanceOf(\DateTime::class, $specialRewards[0]->getStartAt());
        $this->assertInstanceOf(\DateTime::class, $specialRewards[0]->getEndAt());
        $this->assertEquals(true, $specialRewards[0]->isActive());
        $this->assertEquals('special reward - updated', $specialRewards[0]->getName());
    }

    /**
     * @test
     */
    public function it_updates_reward()
    {
        static::$kernel->boot();
        $client = $this->createAuthenticatedClient();
        $client->request(
            'PUT',
            '/api/level/'.LoadLevelData::LEVEL1_ID,
            [
                'level' => [
                    'conditionValue' => 20,
                    'reward' => [
                        'name' => 'new reward - edited',
                        'value' => 7,
                        'code' => 'xyz',
                    ],
                    'translations' => [
                        'en' => [
                            'name' => 'updated level name',
                            'description' => 'updated level desc',
                        ],
                        'pl' => [
                            'name' => 'updated level name PL',
                            'description' => 'updated level desc PL',
                        ],
                    ],
                ],
            ]
        );
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals(LoadLevelData::LEVEL1_ID, $data['id']);
        $level = $this->getLevel(LoadLevelData::LEVEL1_ID);

        $this->assertEquals('new reward - edited', $level->getReward()->getName(), 'Name should be now "new reward - edited"');
        $this->assertEquals(0.07, $level->getReward()->getValue(), 'Value should be now "0.07"');
    }

    /**
     * @test
     */
    public function it_returns_bad_request_on_empty_name()
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/level/create',
            [
                'level' => [
                    'translations' => [
                        'en' => [
                            'description' => 'updated level desc',
                        ],
                    ],
                ],
            ]
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(400, $response->getStatusCode(), 'Response should have status 400');
        $this->assertTrue(count($data['form']['children']['translations']['children']['en']['children']['name']['errors']) > 0,
        'There should bean error on name field');
    }

    /**
     * @test
     */
    public function it_adds_new_photo()
    {
        $levels = $this->repository->findAllActive();
        /** @var Level $first */
        $first = reset($levels);
        $client = $this->createAuthenticatedClient();
        $filesystem = static::$kernel->getContainer()->get('filesystem');
        $filesystem->copy(__DIR__.'/../../../data/sample.png', __DIR__.'/../../../data/sample_test.png');
        $uploadedFile = new UploadedFile(__DIR__.'/../../../data/sample_test.png', 'sample_test.png');

        $client->request(
            'POST',
            '/api/level/'.$first->getLevelId().'/photo',
            [],
            [
                'photo' => ['file' => $uploadedFile],
            ]
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('200', $response->getStatusCode());

        $getClient = $this->createAuthenticatedClient();
        $getClient->request(
            'GET',
            '/api/level/'.$first->getLevelId().'/photo'
        );
        $getResponse = $getClient->getResponse();
        $this->assertEquals(200, $getResponse->getStatusCode());
    }

    /**
     * @test
     */
    public function it_removes_photo()
    {
        $levels = $this->repository->findAllActive();

        if (!count($levels)) {
            return;
        }

        /** @var Level $first */
        $first = reset($levels);

        $client = $this->createAuthenticatedClient();
        $filesystem = static::$kernel->getContainer()->get('filesystem');
        $filesystem->copy(__DIR__.'/../../../data/sample.png', __DIR__.'/../../../data/sample_test.png');
        $uploadedFile = new UploadedFile(__DIR__.'/../../../data/sample_test.png', 'sample_test.png');

        $client->request(
            'POST',
            '/api/level/'.$first->getLevelId().'/photo',
            [],
            [
                'photo' => ['file' => $uploadedFile],
            ]
        );

        $client->request(
            'GET',
            '/api/level/'.$first->getLevelId().'/photo'
        );
        $getResponse = $client->getResponse();
        $this->assertEquals(200, $getResponse->getStatusCode());

        $client->request(
            'DELETE',
            '/api/level/'.$first->getLevelId().'/photo'
        );

        $deleteResponse = $client->getResponse();

        $this->assertEquals(200, $deleteResponse->getStatusCode());

        $client->request(
            'GET',
            '/api/level/'.$first->getLevelId().'/photo'
        );
        $checkResponse = $client->getResponse();
        $this->assertEquals(404, $checkResponse->getStatusCode());
    }

    /**
     * @param $id
     *
     * @return Level
     */
    protected function getLevel($id)
    {
        static::$kernel->boot();
        /** @var LevelRepository $repo */
        $repo = static::$kernel->getContainer()->get('oloy.level.repository');
        /** @var Level $level */
        $level = $repo->byId(new LevelId($id));

        return $level;
    }
}
