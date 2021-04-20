<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Tests\Integration\Controller\Api;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseApiTest;
use OpenLoyalty\Bundle\EarningRuleBundle\Model\EarningRuleLimit;
use OpenLoyalty\Bundle\SettingsBundle\Service\LogoUploader;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Bundle\UserBundle\DataFixtures\ORM\LoadUserData;
use OpenLoyalty\Component\Account\Domain\SystemEvent\AccountSystemEvents;
use OpenLoyalty\Component\Customer\Domain\Model\AccountActivationMethod;
use OpenLoyalty\Component\Customer\Domain\Model\Status;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerSystemEvents;
use OpenLoyalty\Component\Customer\Infrastructure\TierAssignTypeProvider;
use OpenLoyalty\Component\EarningRule\Domain\ReferralEarningRule;
use OpenLoyalty\Component\Transaction\Domain\SystemEvent\TransactionSystemEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use OpenLoyalty\Bundle\SettingsBundle\Model\Settings;
use OpenLoyalty\Bundle\SettingsBundle\Entity\StringSettingEntry;

/**
 * Class SettingsControllerTest.
 */
class SettingsControllerTest extends BaseApiTest
{
    /**
     * @var SettingsManager
     */
    private $settingManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        static::bootKernel();

        $this->settingManager = static::$kernel->getContainer()->get('ol.settings.manager');
    }

    /**
     * @test
     * @expectedException \OpenLoyalty\Bundle\SettingsBundle\Exception\AlreadyExistException
     */
    public function test_create_settings_object_with_the_same_key(): void
    {
        $setting = new Settings();
        $stringEntity = new StringSettingEntry('test-key', 'test');
        $setting->addEntry($stringEntity);
        $this->settingManager->save($setting);

        $setting1 = new Settings();
        $stringEntity1 = new StringSettingEntry('test-key', 'test1');
        $setting1->addEntry($stringEntity1);
        $this->settingManager->save($setting1);
    }

    /**
     * @test
     */
    public function remove_test_key_from_settings()
    {
        $this->settingManager->removeSettingByKey('test-key');
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function it_creates_new_translations(): void
    {
        $client = $this->createAuthenticatedClient();
        $translationData = [
            'name' => 'Germany',
            'code' => 'de',
            'order' => 0,
            'default' => false,
            'content' => json_encode(['key' => 'value']),
        ];

        $client->request(
            'POST',
            '/api/admin/translations',
            ['translation' => $translationData]
        );

        $response = $client->getResponse();
        $this->assertEquals('200', $response->getStatusCode(), 'Cannot create new translation');
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('de', $responseData['code']);
    }

    /**
     * @test
     */
    public function it_removes_translations(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'DELETE',
            '/api/admin/translations/de'
        );

        $response = $client->getResponse();
        $this->assertEquals('200', $response->getStatusCode(), 'Cannot remove translation');
    }

    /**
     * @test
     */
    public function it_returns_css(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'GET',
            '/api/settings/css'
        );
        $response = $client->getResponse();
        $statusCode = $response->getStatusCode();
        $contentType = $response->headers->get('Content-Type');

        $this->assertTrue(mb_strlen($response) > 10, 'Content body less than 10B');
        $this->assertEquals(Response::HTTP_OK, $statusCode);
    }

    /**
     * @test
     * @dataProvider logoNamesDataProvider
     *
     * @param string $name
     */
    public function it_removes_a_logo(string $name)
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'DELETE',
            '/api/settings/'.$name
        );

        $deleteResponse = $client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $deleteResponse->getStatusCode());

        $client->request(
            'GET',
            '/api/settings/'.$name
        );
        $checkResponse = $client->getResponse();
        $this->assertEquals(404, $checkResponse->getStatusCode());
    }

    /**
     * @test
     * @dataProvider photoNamesDataProvider
     * @depends it_removes_a_logo
     *
     * @param string $name
     */
    public function it_removes_a_photo(string $name)
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'DELETE',
            '/api/settings/photo/'.$name
        );

        $deleteResponse = $client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $deleteResponse->getStatusCode());

        $client->request(
            'GET',
            '/api/settings/photo/'.$name
        );
        $checkResponse = $client->getResponse();
        $this->assertEquals(404, $checkResponse->getStatusCode());
    }

    /**
     * @test
     * @dataProvider invalidPhotoNamesDataProvider
     *
     * @param string $name
     */
    public function it_returns_404_when_removes_invalid_photo(string $name)
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'DELETE',
            '/api/settings/photo/'.$name
        );

        $deleteResponse = $client->getResponse();

        $this->assertEquals(404, $deleteResponse->getStatusCode());
    }

    /**
     * @test
     */
    public function it_return_css_settings_in_json_format(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/settings/css?json=1');
        $response = $client->getResponse();
        $contentType = $response->headers->get('Content-Type');

        $this->assertOkResponseStatus($response);
        $this->assertEquals('application/json', $contentType);

        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('accent_color', $data, 'Response should return array with field accent_color');
        $this->assertArrayHasKey('template_css', $data, 'Response should return array with field template_css');
    }

    /**
     * @test
     */
    public function it_return_css_settings_in_css_format(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/settings/css');
        $response = $client->getResponse();
        $contentType = $response->headers->get('Content-Type');

        $this->assertOkResponseStatus($response);
        $this->assertEquals('text/css; charset=utf-8', $contentType);
    }

    /**
     * @test
     *
     * @depends it_removes_a_photo
     */
    public function it_updates_required_settings_correctly(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/settings');
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), 'Cannot get settings');
        $originalSettings = json_decode($response->getContent(), true);

        $requiredSettings = [
            'currency' => 'pln',
            'customerStatusesEarning' => [Status::TYPE_ACTIVE],
            'customerStatusesSpending' => [Status::TYPE_ACTIVE],
            'accountActivationMethod' => AccountActivationMethod::METHOD_EMAIL,
            'timezone' => 'Europe/Warsaw',
            'programName' => 'Open Loyalty',
            'programPointsSingular' => 'Point',
            'programPointsPlural' => 'Points',
            'tierAssignType' => TierAssignTypeProvider::TYPE_TRANSACTIONS,
            'allTimeNotLocked' => true,
            'excludedLevelCategories' => [],
            'customersIdentificationPriority' => [],
            'returns' => true,
            'allowCustomersProfileEdits' => true,
            'programConditionsUrl' => '',
            'programFaqUrl' => '',
            'programUrl' => '',
            'helpEmailAddress' => '',
            'levelDowngradeMode' => 'none',
            'levelDowngradeBase' => 'none',
            'webhooks' => false,
            'uriWebhooks' => '',
            'webhookHeaderName' => '',
            'webhookHeaderValue' => '',
            'excludeDeliveryCostsFromTierAssignment' => false,
            'excludedDeliverySKUs' => [],
            'excludedLevelSKUs' => [],
            'accentColor' => '',
            'cssTemplate' => '',
            'marketingVendorsValue' => '',
            'pointsDaysExpiryAfter' => '',
            'pushySecretKey' => '00112233aabbccdd',
        ];

        $client->request(
            'POST',
            '/api/settings',
            ['settings' => $requiredSettings]
        );

        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), 'Cannot save required settings');
        $saved = json_decode($response->getContent(), true);
        $this->assertEquals(['settings' => $requiredSettings], $saved);

        // revert to previous original settings
        $client->request('POST', '/api/settings', $originalSettings);
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), 'Cannot restore original settings');
    }

    /**
     * @test
     * @dataProvider photoNamesDataProvider
     *
     * @param string $name
     */
    public function it_adds_a_new_photo(string $name)
    {
        $client = $this->createAuthenticatedClient();
        $filesystem = static::$kernel->getContainer()->get('filesystem');
        $filesystem->copy(
            __DIR__.'/../../../../Resources/images/logo/logo.png',
            __DIR__.'/../../../../Resources/images/logo/test_photo.png'
        );
        $uploadedFile = new UploadedFile(__DIR__.'/../../../../Resources/images/logo/test_photo.png', 'test_photo.png');

        $client->request(
            'POST',
            '/api/settings/photo/'.$name,
            [],
            [
                'photo' => ['file' => $uploadedFile],
            ]
        );

        $response = $client->getResponse();
        $this->assertOkResponseStatus($response);

        $getClient = $this->createAuthenticatedClient();
        $getClient->request(
            'GET',
            '/api/settings/photo/'.$name
        );
        $getResponse = $getClient->getResponse();
        $this->assertEquals(Response::HTTP_OK, $getResponse->getStatusCode());
    }

    /**
     * @test
     * @dataProvider invalidPhotoNamesDataProvider
     *
     * @param string $name
     */
    public function it_returns_404_when_adding_invalid_photo(string $name): void
    {
        $client = $this->createAuthenticatedClient();
        $uploadedFile = new UploadedFile(__DIR__.'/../../../../Resources/images/logo/logo.png', 'invalid_photo.png');

        $client->request(
            'POST',
            '/api/settings/photo/'.$name,
            [],
            [
                'photo' => ['file' => $uploadedFile],
            ]
        );

        $response = $client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @test
     * @dataProvider logoNamesDataProvider
     *
     * @param string $name
     */
    public function it_adds_a_new_logo(string $name)
    {
        $client = $this->createAuthenticatedClient();
        $filesystem = static::$kernel->getContainer()->get('filesystem');
        $filesystem->copy(
            __DIR__.'/../../../../Resources/images/logo/logo.png',
            __DIR__.'/../../../../Resources/images/logo/test_logo.png'
        );
        $uploadedFile = new UploadedFile(__DIR__.'/../../../../Resources/images/logo/test_logo.png', 'test_logo.png');

        $client->request(
            'POST',
            '/api/settings/'.$name,
            [],
            [
                'photo' => ['file' => $uploadedFile],
            ]
        );

        $response = $client->getResponse();

        $this->assertOkResponseStatus($response);

        $getClient = $this->createAuthenticatedClient();
        $getClient->request('GET', sprintf('/api/settings/%s', $name));
        $getResponse = $getClient->getResponse();

        $this->assertOkResponseStatus($getResponse);
    }

    /**
     * @test
     * @dataProvider logoSizeProvider
     *
     * @param string $type
     * @param array  $sizes
     */
    public function it_returns_resized_logo(string $type, array $sizes)
    {
        $client = $this->createClient();

        foreach ($sizes as $size) {
            $client->request(
                'GET',
                sprintf('/api/settings/%s/%s', $type, $size)
            );

            $response = $client->getResponse();

            $this->assertOkResponseStatus($response);
        }
    }

    /**
     * @test
     * @dataProvider logoSizeProvider
     *
     * @param string $type
     * @param array  $sizes
     */
    public function it_returns_resized_photo(string $type, array $sizes): void
    {
        $client = $this->createClient();

        foreach ($sizes as $size) {
            $client->request(
                'GET',
                sprintf('/api/settings/photo/%s/%s', $type, $size)
            );

            $response = $client->getResponse();

            $this->assertOkResponseStatus($response);
        }
    }

    /**
     * @test
     */
    public function it_returns_404_on_not_found_logo()
    {
        $client = $this->createClient();

        $client->request(
            'GET',
            '/api/settings/small-logo/1922x112345'
        );

        $response = $client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @test
     * @dataProvider photoNamesNotFoundDataProvider
     *
     * @param string     $name
     * @param array|null $sizes
     * @param bool       $authenticated
     */
    public function it_returns_404_on_not_found_photo(string $name, ?array $sizes = null, bool $authenticated = false)
    {
        $client = ($authenticated) ? $this->createAuthenticatedClient() : $this->createClient();

        if (!empty($sizes)) {
            foreach ($sizes as $size) {
                $client->request(
                    'GET',
                    '/api/settings/photo/'.$name.'/'.$size
                );
                $response = $client->getResponse();
                $this->assertEquals(404, $response->getStatusCode());
            }
        } else {
            $client->request(
                'GET',
                '/api/settings/photo/'.$name
            );
            $response = $client->getResponse();
            $this->assertEquals(404, $response->getStatusCode());
        }
    }

    /**
     * @test
     */
    public function it_returns_correct_logo_if_size_parameter_is_null()
    {
        $client = $this->createClient();

        $client->request(
            'GET',
            '/api/settings/small-logo'
        );

        $response = $client->getResponse();
        $this->assertOkResponseStatus($response);
    }

    /**
     * @test
     */
    public function it_returns_settings()
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'GET',
            '/api/settings'
        );

        $response = $client->getResponse();
        $this->assertOkResponseStatus($response);
        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('settings', $data);
        $settings = $data['settings'];

        $this->assertArrayHasKey('logo', $settings);
        $logo = $settings['logo'];
        $this->assertArrayHasKey('path', $logo);
        $this->assertArrayHasKey('mime', $logo);

        $this->assertArrayHasKey('small-logo', $settings);
        $smallLogo = $settings['small-logo'];
        $this->assertArrayHasKey('path', $smallLogo);
        $this->assertArrayHasKey('mime', $smallLogo);

        $this->assertArrayHasKey('hero-image', $settings);
        $heroLogo = $settings['hero-image'];
        $this->assertArrayHasKey('path', $heroLogo);
        $this->assertArrayHasKey('mime', $heroLogo);

        $this->assertArrayHasKey('admin-cockpit-logo', $settings);
        $adminCockpitLogo = $settings['admin-cockpit-logo'];
        $this->assertArrayHasKey('path', $adminCockpitLogo);
        $this->assertArrayHasKey('mime', $adminCockpitLogo);

        $this->assertArrayHasKey('client-cockpit-logo-big', $settings);
        $clientCockpitLogoBig = $settings['client-cockpit-logo-big'];
        $this->assertArrayHasKey('path', $clientCockpitLogoBig);
        $this->assertArrayHasKey('mime', $clientCockpitLogoBig);

        $this->assertArrayHasKey('client-cockpit-logo-small', $settings);
        $clientCockpitLogoSmall = $settings['client-cockpit-logo-small'];
        $this->assertArrayHasKey('path', $clientCockpitLogoSmall);
        $this->assertArrayHasKey('mime', $clientCockpitLogoSmall);

        $this->assertArrayHasKey('client-cockpit-hero-image', $settings);
        $clientCockpitHeroImage = $settings['client-cockpit-hero-image'];
        $this->assertArrayHasKey('path', $clientCockpitHeroImage);
        $this->assertArrayHasKey('mime', $clientCockpitHeroImage);

        $this->assertArrayHasKey('levelDowngradeMode', $settings);

        $this->assertArrayHasKey('excludedLevelCategories', $settings);
        $this->assertArrayHasKey('customerStatusesEarning', $settings);
        $this->assertArrayHasKey('customerStatusesSpending', $settings);
        $this->assertArrayHasKey('customersIdentificationPriority', $settings);
        $customersIdentificationPriority = $settings['customersIdentificationPriority'];
        foreach ($customersIdentificationPriority as $item) {
            $this->assertArrayHasKey('priority', $item);
            $this->assertArrayHasKey('field', $item);
        }

        $this->assertArrayHasKey('returns', $settings);
        $this->assertArrayHasKey('allowCustomersProfileEdits', $settings);
        $this->assertArrayHasKey('expirePointsNotificationDays', $settings);
        $this->assertArrayHasKey('expireCouponsNotificationDays', $settings);
        $this->assertArrayHasKey('expireLevelsNotificationDays', $settings);
        $this->assertArrayHasKey('currency', $settings);
        $this->assertArrayHasKey('timezone', $settings);
        $this->assertArrayHasKey('programName', $settings);
        $this->assertArrayHasKey('programPointsSingular', $settings);
        $this->assertArrayHasKey('programPointsPlural', $settings);
        $this->assertArrayHasKey('accountActivationMethod', $settings);

        $this->assertArrayHasKey('accentColor', $settings);
        $this->assertArrayHasKey('cssTemplate', $settings);

        $this->assertArrayHasKey('tierAssignType', $settings);
        $this->assertArrayHasKey('levelDowngradeMode', $settings);
        $this->assertArrayHasKey('levelDowngradeBase', $settings);
    }

    /**
     * @test
     */
    public function it_returns_translations_list_for_an_administrator(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'GET',
            '/api/admin/translations'
        );

        $response = $client->getResponse();
        $this->assertOkResponseStatus($response);
        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('translations', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertEquals(2, $data['total']);

        $translation = reset($data['translations']);
        $this->assertArrayHasKey('name', $translation);
        $this->assertEquals('English', $translation['name']);
        $this->assertArrayHasKey('code', $translation);
        $this->assertEquals('en', $translation['code']);
        $this->assertArrayHasKey('updatedAt', $translation);
    }

    /**
     * @test
     */
    public function it_returns_translation_file()
    {
        $client = $this->createAuthenticatedClient(LoadUserData::USER_USERNAME, LoadUserData::USER_PASSWORD, 'customer');
        $client->request(
            'GET',
            '/api/translations'
        );

        $response = $client->getResponse();
        $this->assertOkResponseStatus($response);
    }

    /**
     * @test
     */
    public function it_returns_customer_statuses()
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'GET',
            '/api/admin/customer-statuses'
        );

        $response = $client->getResponse();
        $this->assertOkResponseStatus($response);
        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('statuses', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertEquals(4, $data['total']);
        $this->assertEquals($data['statuses'], ['new', 'active', 'blocked', 'deleted']);
    }

    /**
     * @test
     */
    public function it_returns_translation_file_by_key(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'GET',
            '/api/admin/translations/en'
        );

        $response = $client->getResponse();
        $this->assertOkResponseStatus($response);
    }

    /**
     * @test
     */
    public function it_returns_exception_if_translation_file_does_not_exists(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'GET',
            '/api/admin/translations/english'
        );

        $response = $client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_returns_activation_method()
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'GET',
            '/api/settings/activation-method'
        );

        $response = $client->getResponse();
        $this->assertOkResponseStatus($response);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(['method' => 'email'], $data);
    }

    /**
     * @test
     */
    public function it_returns_promoted_events()
    {
        $client = $this->createAuthenticatedClient(LoadUserData::USER_USERNAME, LoadUserData::USER_PASSWORD, 'customer');
        $client->request(
            'GET',
            '/api/settings/choices/promotedEvents'
        );

        $response = $client->getResponse();

        $this->assertOkResponseStatus($response);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(
            ['choices' => [
                'Customer logged in' => CustomerSystemEvents::CUSTOMER_LOGGED_IN,
                'First purchase' => TransactionSystemEvents::CUSTOMER_FIRST_TRANSACTION,
                'Account created' => AccountSystemEvents::ACCOUNT_CREATED,
                'Newsletter subscription' => CustomerSystemEvents::NEWSLETTER_SUBSCRIPTION,
            ]],
            $data
        );
    }

    /**
     * @test
     */
    public function it_returns_languages()
    {
        $client = $this->createAuthenticatedClient(LoadUserData::USER_USERNAME, LoadUserData::USER_PASSWORD, 'customer');
        $client->request(
            'GET',
            '/api/settings/choices/language'
        );

        $response = $client->getResponse();

        $this->assertOkResponseStatus($response);

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('choices', $data);
        $this->assertArrayHasKey('Polish', $data['choices']);
        $this->assertEquals('pl', $data['choices']['Polish']);
    }

    /**
     * @test
     */
    public function it_returns_countries()
    {
        $client = $this->createAuthenticatedClient(LoadUserData::USER_USERNAME, LoadUserData::USER_PASSWORD, 'customer');
        $client->request(
            'GET',
            '/api/settings/choices/country'
        );

        $response = $client->getResponse();

        $this->assertOkResponseStatus($response);

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('choices', $data);
        $this->assertArrayHasKey('Belarus', $data['choices']);
        $this->assertEquals('BY', $data['choices']['Belarus']);
    }

    /**
     * @test
     */
    public function it_returns_timezones(): void
    {
        $client = $this->createAuthenticatedClient(LoadUserData::USER_USERNAME, LoadUserData::USER_PASSWORD, 'customer');
        $client->request('GET', '/api/settings/choices/timezone');

        $response = $client->getResponse();

        $this->assertOkResponseStatus($response);

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('choices', $data);
        $this->assertArrayHasKey('America', $data['choices']);
        $this->assertArrayHasKey('Atikokan', $data['choices']['America']);
        $this->assertEquals('America/Atikokan', $data['choices']['America']['Atikokan']);
    }

    /**
     * @test
     */
    public function it_returns_available_frontend_translations(): void
    {
        $client = $this->createAuthenticatedClient(LoadUserData::USER_USERNAME, LoadUserData::USER_PASSWORD, 'customer');
        $client->request(
            'GET',
            '/api/settings/choices/availableFrontendTranslations'
        );

        $response = $client->getResponse();

        $this->assertOkResponseStatus($response);

        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('choices', $data);

        $translation = reset($data['choices']);
        $this->assertArrayHasKey('name', $translation);
        $this->assertEquals('English', $translation['name']);
        $this->assertArrayHasKey('code', $translation);
        $this->assertEquals('en', $translation['code']);
        $this->assertArrayHasKey('updatedAt', $translation);
    }

    /**
     * @test
     */
    public function it_returns_available_customer_statuses()
    {
        $client = $this->createAuthenticatedClient(LoadUserData::USER_USERNAME, LoadUserData::USER_PASSWORD, 'customer');
        $client->request('GET', '/api/settings/choices/availableCustomerStatuses');

        $response = $client->getResponse();

        $this->assertOkResponseStatus($response);

        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('choices', $data);
        $this->assertEquals($data['choices'], ['new', 'active', 'blocked', 'deleted']);
    }

    /**
     * @test
     */
    public function it_returns_available_activation_methods()
    {
        $client = $this->createAuthenticatedClient(LoadUserData::USER_USERNAME, LoadUserData::USER_PASSWORD, 'customer');
        $client->request(
            'GET',
            '/api/settings/choices/availableAccountActivationMethods'
        );

        $response = $client->getResponse();

        $this->assertOkResponseStatus($response);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(['choices' => ['email', 'sms']], $data);
    }

    /**
     * @test
     */
    public function it_returns_sms_gateway_config(): void
    {
        $client = $this->createAuthenticatedClient(LoadUserData::USER_USERNAME, LoadUserData::USER_PASSWORD, 'customer');
        $client->request(
            'GET',
            '/api/settings/choices/smsGatewayConfig'
        );

        $response = $client->getResponse();

        $this->assertOkResponseStatus($response);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(['fields' => []], $data);
    }

    /**
     * @test
     */
    public function it_returns_earning_rule_limit_period(): void
    {
        $client = $this->createAuthenticatedClient(LoadUserData::USER_USERNAME, LoadUserData::USER_PASSWORD, 'customer');
        $client->request(
            'GET',
            '/api/settings/choices/earningRuleLimitPeriod'
        );

        $response = $client->getResponse();

        $this->assertOkResponseStatus($response);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(
            [
                'choices' => [
                    '1 day' => EarningRuleLimit::PERIOD_DAY,
                    '1 week' => EarningRuleLimit::PERIOD_WEEK,
                    '1 month' => EarningRuleLimit::PERIOD_MONTH,
                    '3 months' => EarningRuleLimit::PERIOD_3_MONTHS,
                    '6 months' => EarningRuleLimit::PERIOD_6_MONTHS,
                    '1 year' => EarningRuleLimit::PERIOD_YEAR,
                    'forever' => EarningRuleLimit::PERIOD_FOREVER,
                ],
            ],
            $data
        );
    }

    /**
     * @test
     */
    public function it_returns_referral_events(): void
    {
        $client = $this->createAuthenticatedClient(LoadUserData::USER_USERNAME, LoadUserData::USER_PASSWORD, 'customer');
        $client->request(
            'GET',
            '/api/settings/choices/referralEvents'
        );

        $response = $client->getResponse();

        $this->assertOkResponseStatus($response);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(
            [
                'choices' => [
                    ReferralEarningRule::EVENT_REGISTER => ReferralEarningRule::EVENT_REGISTER,
                    ReferralEarningRule::EVENT_FIRST_PURCHASE => ReferralEarningRule::EVENT_FIRST_PURCHASE,
                    ReferralEarningRule::EVENT_EVERY_PURCHASE => ReferralEarningRule::EVENT_EVERY_PURCHASE,
                ],
            ],
            $data
        );
    }

    /**
     * @test
     */
    public function it_returns_referral_types()
    {
        $client = $this->createAuthenticatedClient(LoadUserData::USER_USERNAME, LoadUserData::USER_PASSWORD, 'customer');
        $client->request(
            'GET',
            '/api/settings/choices/referralTypes'
        );

        $response = $client->getResponse();

        $this->assertOkResponseStatus($response);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(
            ['choices' => [
                ReferralEarningRule::TYPE_REFERRED => ReferralEarningRule::TYPE_REFERRED,
                ReferralEarningRule::TYPE_REFERRER => ReferralEarningRule::TYPE_REFERRER,
                ReferralEarningRule::TYPE_BOTH => ReferralEarningRule::TYPE_BOTH,
            ]],
            $data
        );
    }

    /**
     * @test
     */
    public function it_returns_exception_when_choice_not_found()
    {
        $client = $this->createAuthenticatedClient(LoadUserData::USER_USERNAME, LoadUserData::USER_PASSWORD, 'customer');
        $client->request(
            'GET',
            '/api/settings/choices/notFoundChoices'
        );

        $response = $client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @dataPovider
     */
    public function logoSizeProvider(): array
    {
        return [
            [LogoUploader::SMALL_LOGO, ['192x192', '512x512']],
        ];
    }

    /**
     * @return array
     */
    public function logoNamesDataProvider(): array
    {
        return [
            [LogoUploader::LOGO],
            [LogoUploader::SMALL_LOGO],
            [LogoUploader::HERO_IMAGE],
        ];
    }

    /**
     * @return array
     */
    public function photoNamesDataProvider(): array
    {
        return [
            [LogoUploader::LOGO],
            [LogoUploader::SMALL_LOGO],
            [LogoUploader::HERO_IMAGE],
            [LogoUploader::CLIENT_COCKPIT_HERO_IMAGE],
            [LogoUploader::CLIENT_COCKPIT_LOGO_BIG],
            [LogoUploader::CLIENT_COCKPIT_LOGO_SMALL],
            [LogoUploader::ADMIN_COCKPIT_LOGO],
        ];
    }

    /**
     * @return array
     */
    public function invalidPhotoNamesDataProvider(): array
    {
        return [
            ['logo2'],
            ['invalid-name'],
            ['hero-image-small'],
        ];
    }

    /**
     * @return array
     */
    public function photoNamesNotFoundDataProvider(): array
    {
        return [
            [LogoUploader::SMALL_LOGO, ['256x256', '1024x800']],
            [LogoUploader::LOGO, ['192x192', '512x512']],
            [LogoUploader::HERO_IMAGE, ['192x192', '512x512']],
            [LogoUploader::CLIENT_COCKPIT_HERO_IMAGE, ['192x192', '512x512']],
            [LogoUploader::CLIENT_COCKPIT_LOGO_BIG, ['192x192', '512x512']],
            [LogoUploader::CLIENT_COCKPIT_LOGO_SMALL, ['192x192', '512x512']],
            [LogoUploader::ADMIN_COCKPIT_LOGO, ['192x192', '512x512']],
            ['invalid-name', ['192x192', '512x512'], true],
            ['invalid-name', null, true],
        ];
    }
}
