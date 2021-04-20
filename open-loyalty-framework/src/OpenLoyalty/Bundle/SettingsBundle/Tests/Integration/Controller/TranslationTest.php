<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Tests\Integration\Controller;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseApiTest;
use OpenLoyalty\Bundle\SettingsBundle\Service\LocaleProvider;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TranslationTest.
 */
class TranslationTest extends BaseApiTest
{
    /**
     * @param string $defaultLocale
     *
     * @return Client
     */
    protected function getClientConfigurationDefaultLocale(string $defaultLocale): Client
    {
        $localeProvider = $this->getMockBuilder(LocaleProvider::class)->disableOriginalConstructor()->getMock();
        $localeProvider->expects($this->any())->method('getConfigurationDefaultLocale')->willReturn($defaultLocale);
        $localeProvider->expects($this->any())->method('getAvailableLocales')->willReturn(['en', 'pl']);
        $localeProvider->expects($this->any())->method('getDefaultLocale')->willReturn('en');

        self::bootKernel();
        $client = $this->createAuthenticatedClient();
        self::$kernel->getContainer()->set(LocaleProvider::class, $localeProvider);

        return $client;
    }

    /**
     * @test
     *
     * @dataProvider localeValidationProvider
     *
     * @param string $defaultLocale
     * @param string $locale
     * @param string $expectedString
     */
    public function it_returns_correct_validation_translations_by_locale(string $defaultLocale, ?string $locale, string $expectedString): void
    {
        $client = $this->getClientConfigurationDefaultLocale($defaultLocale);

        $uri = '/api/level/create';
        if ($locale) {
            $uri .= '?_locale='.$locale;
        }

        $client->request(
            'POST',
            $uri,
            [
                'level' => [
                    'conditionValue' => null,
                ],
            ]
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($expectedString, $content['form']['children']['reward']['children']['name']['errors'][0]);
    }

    /**
     * @dataProvider
     */
    public function localeValidationProvider(): array
    {
        return [
            ['pl', null, 'Ta wartość nie powinna być pusta.'],
            ['en', null, 'This value should not be blank.'],
            ['en', 'en', 'This value should not be blank.'],
            ['en', 'pl', 'Ta wartość nie powinna być pusta.'],
        ];
    }

    /**
     * @param Client $client
     * @param array  $translations
     *
     * @return string
     */
    protected function createLevelWithTranslations(Client $client, array $translations): string
    {
        $client->request(
            'POST',
            '/api/level/create',
            [
                'level' => [
                    'active' => 0,
                    'conditionValue' => 15,
                    'reward' => [
                        'name' => 'Super reward',
                        'value' => 0,
                        'code' => '12307291',
                    ],
                    'translations' => $translations,
                    'specialRewards' => [],
                ],
            ]
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('id', $data);

        return $data['id'];
    }

    /**
     * @test
     */
    public function it_persists_translations(): void
    {
        $client = $this->getClientConfigurationDefaultLocale('en');
        $levelId = $this->createLevelWithTranslations($client, [
            'en' => [
                'name' => 'NAME_EN',
                'description' => 'DESC_EN',
            ],
            'pl' => [
                'name' => 'NAME_PL',
                'description' => 'DESC_PL',
            ],
        ]);

        $client->request(
            'GET',
            sprintf('/api/level/%s', $levelId)
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');

        $this->assertArrayHasKey('name', $data);
        $this->assertEquals('NAME_EN', $data['name']);

        $this->assertArrayHasKey('description', $data);
        $this->assertEquals('DESC_EN', $data['description']);
    }

    /**
     * @test
     */
    public function it_retrieves_translations_by_using_default_locale(): void
    {
        $client = $this->getClientConfigurationDefaultLocale('en');
        $levelId = $this->createLevelWithTranslations($client, [
            'en' => [
                'name' => 'NAME_EN',
                'description' => 'DESC_EN',
            ],
            'pl' => [
                'name' => 'NAME_PL',
                'description' => 'DESC_PL',
            ],
        ]);

        $client = $this->getClientConfigurationDefaultLocale('pl');
        $client->request(
            'GET',
            '/api/level/'.$levelId
        );
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');

        $this->assertArrayHasKey('name', $data);
        $this->assertEquals('NAME_PL', $data['name']);

        $this->assertArrayHasKey('description', $data);
        $this->assertEquals('DESC_PL', $data['description']);
    }

    /**
     * @test
     */
    public function it_retrieves_translations_by_given_locale(): void
    {
        $client = $this->getClientConfigurationDefaultLocale('en');
        $levelId = $this->createLevelWithTranslations($client, [
            'en' => [
                'name' => 'NAME_EN',
                'description' => 'DESC_EN',
            ],
            'pl' => [
                'name' => 'NAME_PL',
                'description' => 'DESC_PL',
            ],
        ]);

        $client = $this->getClientConfigurationDefaultLocale('en');
        $client->request(
            'GET',
            '/api/level/'.$levelId.'?_locale=pl'
        );
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');

        $this->assertArrayHasKey('name', $data);
        $this->assertEquals('NAME_PL', $data['name']);

        $this->assertArrayHasKey('description', $data);
        $this->assertEquals('DESC_PL', $data['description']);
    }

    /**
     * @test
     */
    public function it_retrieves_on_field_fallback_translations_by_given_locale(): void
    {
        $client = $this->getClientConfigurationDefaultLocale('en');
        $levelId = $this->createLevelWithTranslations($client, [
            'en' => [
                'name' => 'NAME_EN',
                'description' => 'DESC_EN',
            ],
            'pl' => [
                'name' => '',
                'description' => 'DESC_PL',
            ],
        ]);

        $client = $this->getClientConfigurationDefaultLocale('en');
        $client->request(
            'GET',
            '/api/level/'.$levelId.'?_locale=pl'
        );
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');

        $this->assertArrayHasKey('name', $data);
        $this->assertEquals('NAME_EN', $data['name']);

        $this->assertArrayHasKey('description', $data);
        $this->assertEquals('DESC_PL', $data['description']);
    }

    /**
     * @test
     */
    public function it_retrieves_all_field_fallback_translations_by_given_locale(): void
    {
        $client = $this->getClientConfigurationDefaultLocale('en');
        $levelId = $this->createLevelWithTranslations($client, [
            'en' => [
                'name' => 'NAME_EN',
                'description' => 'DESC_EN',
            ],
        ]);

        $client = $this->getClientConfigurationDefaultLocale('en');
        $client->request(
            'GET',
            '/api/level/'.$levelId.'?_locale=pl'
        );
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');

        $this->assertArrayHasKey('name', $data);
        $this->assertEquals('NAME_EN', $data['name']);

        $this->assertArrayHasKey('description', $data);
        $this->assertEquals('DESC_EN', $data['description']);
    }
}
