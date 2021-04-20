<?php

namespace OpenLoyalty\Bundle\SettingsBundle\Tests\Integration\Form\Type;

use OpenLoyalty\Bundle\ActivationCodeBundle\Provider\AvailableAccountActivationMethodsChoices;
use OpenLoyalty\Bundle\SettingsBundle\Entity\BooleanSettingEntry;
use OpenLoyalty\Bundle\SettingsBundle\Entity\IntegerSettingEntry;
use OpenLoyalty\Bundle\SettingsBundle\Entity\JsonSettingEntry;
use OpenLoyalty\Bundle\SettingsBundle\Entity\StringSettingEntry;
use OpenLoyalty\Bundle\SettingsBundle\Form\Type\SettingsCheckboxType;
use OpenLoyalty\Bundle\SettingsBundle\Form\Type\SettingsChoicesType;
use OpenLoyalty\Bundle\SettingsBundle\Form\Type\SettingsCollectionType;
use OpenLoyalty\Bundle\SettingsBundle\Form\Type\SettingsFormType;
use OpenLoyalty\Bundle\SettingsBundle\Form\Type\SettingsIntegerType;
use OpenLoyalty\Bundle\SettingsBundle\Form\Type\SettingsTextType;
use OpenLoyalty\Bundle\SettingsBundle\Form\Type\SettingsTimezoneType;
use OpenLoyalty\Bundle\SettingsBundle\Model\Settings;
use OpenLoyalty\Bundle\SettingsBundle\Model\TranslationsEntry;
use OpenLoyalty\Bundle\SettingsBundle\Provider\AvailableCustomerStatusesChoices;
use OpenLoyalty\Bundle\SettingsBundle\Provider\AvailableMarketingVendors;
use OpenLoyalty\Bundle\SettingsBundle\Service\TranslationsProvider;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Bundle\UserBundle\Entity\Status;
use OpenLoyalty\Component\Account\Domain\Model\AddPointsTransfer;
use OpenLoyalty\Component\Customer\Infrastructure\LevelDowngradeModeProvider;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class SettingsFormTypeTest.
 */
class SettingsFormTypeTest extends TypeTestCase
{
    protected $stringEntries = [
        'currency' => 'pln',
        'timezone' => 'Europe/Berlin',
        'programName' => 'test',
        'programConditionsUrl' => 'url',
        'programFaqUrl' => 'faq',
        'programUrl' => 'program url',
        'programPointsSingular' => 'p',
        'programPointsPlural' => 'ps',
        'helpEmailAddress' => 'email',
        'tierAssignType' => 'points',
        'levelDowngradeMode' => LevelDowngradeModeProvider::MODE_AUTO,
        'levelDowngradeBase' => 'none',
        'accountActivationMethod' => 'email',
        'uriWebhooks' => '',
        'webhookHeaderName' => '',
        'webhookHeaderValue' => '',
        'accentColor' => '#000abc',
        'cssTemplate' => 'body { color: red; }',
        'pointsDaysExpiryAfter' => AddPointsTransfer::TYPE_ALL_TIME_ACTIVE,
    ];

    protected $booleanEntries = [
        'returns' => true,
        'allowCustomersProfileEdits' => true,
        'allTimeNotLocked' => true,
        'excludeDeliveryCostsFromTierAssignment' => true,
        'webhooks' => false,
        'levelResetPointsOnDowngrade' => false,
    ];

    protected $integerEntries = [
        'pointsDaysActiveCount' => 10,
        'expirePointsNotificationDays' => 10,
        'expireCouponsNotificationDays' => 10,
        'expireLevelsNotificationDays' => 10,
        'levelDowngradeDays' => 0,
    ];

    private $settingsManager;

    /**
     * @var TranslationsProvider
     */
    private $translationProvider;

    /**
     * @var AvailableMarketingVendors
     */
    private $marketingVendorsProvider;

    /**
     * @var AvailableAccountActivationMethodsChoices
     */
    private $accountActivationMethodsChoices;

    /**
     * @var AvailableCustomerStatusesChoices
     */
    private $availableCustomerStatusesChoices;

    private $validator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->translationProvider = $this->getMockForAbstractClass(TranslationsProvider::class);

        $this->marketingVendorsProvider = $this->getMockBuilder(AvailableMarketingVendors::class)->getMock();
        $this->marketingVendorsProvider->method('getChoices')->willReturn([
            'choices' => [
                AvailableMarketingVendors::NONE => [
                    'name' => 'Disabled',
                    'config' => [],
                ],
            ],
        ]);

        $this->availableCustomerStatusesChoices = $this
            ->getMockBuilder(AvailableCustomerStatusesChoices::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->availableCustomerStatusesChoices->method('getChoices')->willReturn(
            ['choices' => ['new', 'active', 'blocked', 'deleted']]
        );

        $this->accountActivationMethodsChoices = $this->getMockBuilder(AvailableAccountActivationMethodsChoices::class)
            ->disableOriginalConstructor()->getMock();

        $this->accountActivationMethodsChoices->method('getChoices')->willReturn(
            ['choices' => ['sms', 'email']]
        );

        $this->translationProvider->method('getAvailableTranslationsList')->willReturn([
            new TranslationsEntry('en'),
        ]);

        $this->settingsManager = $this->getMockForAbstractClass(SettingsManager::class);
        $this->settingsManager->method('getSettingByKey')->willReturn(null);

        $this->validator = $this->getMockForAbstractClass(ValidatorInterface::class);
        $this->validator
            ->method('validate')
            ->will($this->returnValue(new ConstraintViolationList()))
        ;

        $metadata = $this
            ->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $metadata->method('addConstraint')->willReturn(true);
        $metadata->method('addPropertyConstraint')->willReturn(true);

        $this->validator->method('getMetadataFor')->willReturn($metadata);

        parent::setUp();
    }

    protected function getExtensions(): array
    {
        $type = new SettingsFormType(
            $this->settingsManager,
            $this->translationProvider,
            $this->marketingVendorsProvider,
            $this->availableCustomerStatusesChoices,
            $this->accountActivationMethodsChoices
        );

        $settingsCheckboxType = new SettingsCheckboxType($this->settingsManager);
        $settingsChoicesType = new SettingsChoicesType($this->settingsManager);
        $settingsCollectionType = new SettingsCollectionType($this->settingsManager);
        $settingsIntegerType = new SettingsIntegerType($this->settingsManager);
        $settingsTextType = new SettingsTextType($this->settingsManager);
        $settingsTimezoneType = new SettingsTimezoneType($this->settingsManager);

        return [
            new PreloadedExtension([$type], []),
            new PreloadedExtension([$settingsCheckboxType], []),
            new PreloadedExtension([$settingsChoicesType], []),
            new PreloadedExtension([$settingsCollectionType], []),
            new PreloadedExtension([$settingsIntegerType], []),
            new PreloadedExtension([$settingsTextType], []),
            new PreloadedExtension([$settingsTimezoneType], []),
            new ValidatorExtension($this->validator),
        ];
    }

    /**
     * @test
     */
    public function it_has_valid_data_after_submit(): void
    {
        $form = $this->factory->create(SettingsFormType::class);
        $object = new Settings();

        foreach ($this->stringEntries as $key => $value) {
            $entry = new StringSettingEntry($key);
            $entry->setValue($value);
            $object->addEntry($entry);
        }

        foreach ($this->booleanEntries as $key => $value) {
            $entry = new BooleanSettingEntry($key);
            $entry->setValue($value);
            $object->addEntry($entry);
        }

        foreach ($this->integerEntries as $key => $value) {
            $entry = new IntegerSettingEntry($key);
            $entry->setValue($value);
            $object->addEntry($entry);
        }

        $entry = new JsonSettingEntry('customersIdentificationPriority');
        $entry->setValue([['field' => 'email', 'priority' => 1]]);
        $object->addEntry($entry);

        $entry = new JsonSettingEntry('excludedDeliverySKUs');
        $entry->setValue(['123']);
        $object->addEntry($entry);

        $entry = new JsonSettingEntry('excludedLevelSKUs');
        $entry->setValue(['123']);
        $object->addEntry($entry);

        $entry = new JsonSettingEntry('excludedLevelCategories');
        $entry->setValue(['123']);
        $object->addEntry($entry);

        $entry = new JsonSettingEntry('customerStatusesEarning');
        $entry->setValue([Status::TYPE_ACTIVE]);
        $object->addEntry($entry);

        $entry = new JsonSettingEntry('customerStatusesSpending');
        $entry->setValue([Status::TYPE_ACTIVE]);
        $object->addEntry($entry);

        $entry = new StringSettingEntry('marketingVendorsValue');
        $entry->setValue('');
        $object->addEntry($entry);

        $entry = new IntegerSettingEntry('pointsDaysLocked');
        $entry->setValue(null);
        $object->addEntry($entry);

        $entry = new IntegerSettingEntry('expirePointsNotificationDays');
        $entry->setValue(10);
        $object->addEntry($entry);

        $entry = new IntegerSettingEntry('expireCouponsNotificationDays');
        $entry->setValue(10);
        $object->addEntry($entry);

        $entry = new IntegerSettingEntry('expireLevelsNotificationDays');
        $entry->setValue(10);
        $object->addEntry($entry);

        $entry = new StringSettingEntry('pushySecretKey');
        $entry->setValue('');
        $object->addEntry($entry);

        $formData = array_merge($this->stringEntries, $this->booleanEntries, $this->integerEntries, [
            'customersIdentificationPriority' => [
                ['field' => 'email', 'priority' => 1],
            ],
            'excludedDeliverySKUs' => [
                '123',
            ],
            'excludedLevelSKUs' => [
                '123',
            ],
            'excludedLevelCategories' => [
                '123',
            ],
            'customerStatusesEarning' => [
                Status::TYPE_ACTIVE,
            ],
            'customerStatusesSpending' => [
                Status::TYPE_ACTIVE,
            ],
        ]);

        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($object, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
