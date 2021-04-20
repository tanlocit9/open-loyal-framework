<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Form\Type;

use OpenLoyalty\Bundle\ActivationCodeBundle\Provider\AvailableAccountActivationMethodsChoices;
use OpenLoyalty\Bundle\ActivationCodeBundle\Service\SmsSender;
use OpenLoyalty\Bundle\SettingsBundle\Entity\StringSettingEntry;
use OpenLoyalty\Bundle\SettingsBundle\Form\EventListener\ActivationMethodSubscriber;
use OpenLoyalty\Bundle\SettingsBundle\Form\EventListener\AllTimeActiveSubscriber;
use OpenLoyalty\Bundle\SettingsBundle\Form\EventListener\AllTimeNotLockedSubscriber;
use OpenLoyalty\Bundle\SettingsBundle\Form\EventListener\DowngradeModeNoneResetAfterDaysFieldSubscriber;
use OpenLoyalty\Bundle\SettingsBundle\Form\EventListener\DowngradeModeSubscriber;
use OpenLoyalty\Bundle\SettingsBundle\Form\EventListener\ExcludeDeliveryCostSubscriber;
use OpenLoyalty\Bundle\SettingsBundle\Form\EventListener\MarketingVendorSubscriber;
use OpenLoyalty\Bundle\SettingsBundle\Model\Settings;
use OpenLoyalty\Bundle\SettingsBundle\Provider\AvailableCustomerStatusesChoices;
use OpenLoyalty\Bundle\SettingsBundle\Provider\AvailableMarketingVendors;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Bundle\SettingsBundle\Service\TranslationsProvider;
use OpenLoyalty\Bundle\SettingsBundle\Validator\Constraints\NotEmptyValue;
use OpenLoyalty\Bundle\SettingsBundle\Validator\Constraints\ValidHexColor;
use OpenLoyalty\Component\Account\Domain\Model\AddPointsTransfer;
use OpenLoyalty\Component\Customer\Infrastructure\LevelDowngradeModeProvider;
use OpenLoyalty\Component\Customer\Infrastructure\TierAssignTypeProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\UrlValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class SettingsFormType.
 */
class SettingsFormType extends AbstractType
{
    const LEVEL_DOWNGRADE_MODE_SETTINGS_KEY = 'levelDowngradeMode';

    const LEVEL_DOWNGRADE_DAYS_SETTINGS_KEY = 'levelDowngradeDays';

    const LEVEL_DOWNGRADE_BASE_SETTINGS_KEY = 'levelDowngradeBase';

    const TIER_ASSIGN_TYPE_SETTINGS_KEY = 'tierAssignType';

    const LEVEL_RESET_POINTS_ON_DOWNGRADE_SETTINGS_KEY = 'levelResetPointsOnDowngrade';

    const ALLOW_CUSTOMERS_PROFILE_EDITS_SETTINGS_KEY = 'allowCustomersProfileEdits';

    /**
     * @var SettingsManager
     */
    private $settingsManager;

    /**
     * @var TranslationsProvider
     */
    private $translationsProvider;

    /**
     * @var SmsSender|null
     */
    private $smsGateway = null;

    /**
     * @var AvailableMarketingVendors
     */
    private $marketingVendors;

    /**
     * @var AvailableCustomerStatusesChoices
     */
    private $availableCustomerStatusesChoices;

    /**
     * @var AvailableAccountActivationMethodsChoices
     */
    private $accountActivationMethodsChoices;

    /**
     * SettingsFormType constructor.
     *
     * @param SettingsManager                          $settingsManager
     * @param TranslationsProvider                     $translationsProvider
     * @param AvailableMarketingVendors                $marketingVendors
     * @param AvailableCustomerStatusesChoices         $availableCustomerStatusesChoices
     * @param AvailableAccountActivationMethodsChoices $accountActivationMethodsChoices
     */
    public function __construct(
        SettingsManager $settingsManager,
        TranslationsProvider $translationsProvider,
        AvailableMarketingVendors $marketingVendors,
        AvailableCustomerStatusesChoices $availableCustomerStatusesChoices,
        AvailableAccountActivationMethodsChoices $accountActivationMethodsChoices
    ) {
        $this->settingsManager = $settingsManager;
        $this->translationsProvider = $translationsProvider;
        $this->marketingVendors = $marketingVendors;
        $this->availableCustomerStatusesChoices = $availableCustomerStatusesChoices;
        $this->accountActivationMethodsChoices = $accountActivationMethodsChoices;
    }

    /**
     * @param SmsSender $smsGateway
     */
    public function setSmsSender(SmsSender $smsGateway): void
    {
        $this->smsGateway = $smsGateway;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            $builder->create('currency', SettingsChoicesType::class, [
                'choices' => [
                    'PLN' => 'pln',
                    'USD' => 'usd',
                    'EUR' => 'eur',
                    'HKD' => 'hkd',
                    'PESO' => 'cop',
                ],
                'constraints' => [new NotEmptyValue()],
            ])
        );

        $builder->add(
            $builder->create('customerStatusesEarning', SettingsChoicesType::class, [
                'choices' => $this->availableCustomerStatusesChoices->getChoices()['choices'],
                'multiple' => true,
                'required' => true,
                'constraints' => [new NotEmptyValue()],
                'transformTo' => 'json',
            ])
        );

        $builder->add(
            $builder->create('accountActivationMethod', SettingsChoicesType::class, [
                'choices' => $this->accountActivationMethodsChoices->getChoices()['choices'],
                'required' => true,
                'constraints' => [new NotBlank()],
            ])
        );

        $builder->add(
            $builder->create(
                'marketingVendorsValue',
                SettingsChoicesType::class,
                [
                    'choices' => array_keys($this->marketingVendors->getChoices()['choices']),
                    'required' => true,
                    'constraints' => [new NotBlank()],
                ]
            )
        );

        $builder->add(
            $builder->create('customerStatusesSpending', SettingsChoicesType::class, [
                'choices' => $this->availableCustomerStatusesChoices->getChoices()['choices'],
                'multiple' => true,
                'required' => true,
                'constraints' => [new NotEmptyValue()],
                'transformTo' => 'json',
            ])
        );

        $builder->add(
            $builder->create('timezone', SettingsTimezoneType::class, [
                'preferred_choices' => ['Europe/Warsaw'],
                'constraints' => [new NotEmptyValue()],
            ])
        );

        $builder->add($builder->create('programName', SettingsTextType::class, [
            'constraints' => [new NotEmptyValue()],
        ]));
        $builder->add($builder->create('programConditionsUrl', SettingsTextType::class, ['required' => false]));
        $builder->add($builder->create('programConditionsUrl', SettingsTextType::class, ['required' => false]));
        $builder->add($builder->create('programFaqUrl', SettingsTextType::class, ['required' => false]));
        $builder->add($builder->create('programUrl', SettingsTextType::class, ['required' => false]));
        $builder->add(
            $builder->create('programPointsSingular', SettingsTextType::class, [
                'constraints' => [new NotEmptyValue()],
            ])
        );
        $builder->add(
            $builder->create('programPointsPlural', SettingsTextType::class, [
                'constraints' => [
                    new NotEmptyValue(),
                ],
            ])
        );

        $builder->add($builder->create('helpEmailAddress', SettingsTextType::class, ['required' => false]));
        $builder->add($builder->create('returns', SettingsCheckboxType::class, ['required' => false]));

        $builder->add($builder->create(
            self::ALLOW_CUSTOMERS_PROFILE_EDITS_SETTINGS_KEY, SettingsCheckboxType::class, [
            'required' => false,
        ]));

        $builder->add(
            $builder->create('expirePointsNotificationDays', SettingsIntegerType::class, [
                'required' => false,
                'constraints' => [
                    new Optional(),
                ],
            ])
        );
        $builder->add(
            $builder->create('expireCouponsNotificationDays', SettingsIntegerType::class, [
                'required' => false,
                'constraints' => [
                    new Optional(),
                ],
            ])
        );
        $builder->add(
            $builder->create('expireLevelsNotificationDays', SettingsIntegerType::class, [
                'required' => false,
                'constraints' => [
                    new Optional(),
                ],
            ])
        );

        $builder->add($builder->create('allTimeNotLocked', SettingsCheckboxType::class, ['required' => false]));
        $builder->add(
            $builder->create('pointsDaysLocked', SettingsIntegerType::class, [
                'required' => false,
                'empty_data' => '',
            ])
        );

        $builder
            ->add(
                $builder->create(
                    'pointsDaysExpiryAfter',
                    SettingsChoicesType::class,
                    [
                        'choices' => [
                            AddPointsTransfer::TYPE_ALL_TIME_ACTIVE,
                            AddPointsTransfer::TYPE_AFTER_X_DAYS,
                            AddPointsTransfer::TYPE_AT_MONTH_END,
                            AddPointsTransfer::TYPE_AT_YEAR_END,
                        ],
                        'required' => true,
                        'empty_data' => '',
                    ]
                )
            )
            ->add(
                $builder->create(
                    'pointsDaysActiveCount',
                    SettingsIntegerType::class,
                    [
                        'empty_data' => '',
                        'required' => false,
                    ]
                )
            )
        ;

        $builder->add($builder->create('webhooks', SettingsCheckboxType::class, ['required' => false]));
        $builder->add(
            $builder->create('uriWebhooks', SettingsTextType::class, [
                'required' => false,
                'constraints' => [
                    new Callback([$this, 'checkUrl']),
                ],
            ])
        );
        $builder->add(
            $builder->create('webhookHeaderName', SettingsTextType::class, [
                'required' => false,
            ])
        );
        $builder->add(
            $builder->create('webhookHeaderValue', SettingsTextType::class, [
                'required' => false,
            ])
        );

        $builder->add(
            $builder->create('accentColor', SettingsTextType::class, [
                'constraints' => [
                    new ValidHexColor(),
                ],
            ])
        );
        $builder->add($builder->create('cssTemplate', SettingsTextType::class));

        $builder->add(
            $builder->create('customersIdentificationPriority', SettingsCollectionType::class, [
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'entry_type' => CustomersIdentificationPriority::class,
                'transformTo' => 'json',
            ])
        );

        $builder->add(
            $builder
                ->create(
                    self::TIER_ASSIGN_TYPE_SETTINGS_KEY, SettingsChoicesType::class, [
                    'choices' => [
                        TierAssignTypeProvider::TYPE_POINTS => TierAssignTypeProvider::TYPE_POINTS,
                        TierAssignTypeProvider::TYPE_TRANSACTIONS => TierAssignTypeProvider::TYPE_TRANSACTIONS,
                    ],
                    'constraints' => [new NotBlank()],
                ])
        );
        $builder->add(
            $builder->create('excludeDeliveryCostsFromTierAssignment', SettingsCheckboxType::class, [
                'required' => false,
            ])
        );
        $builder->add(
            $builder->create('excludedDeliverySKUs', SettingsCollectionType::class, [
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'entry_type' => TextType::class,
                'error_bubbling' => false,
                'transformTo' => 'json',
            ])
        );
        $builder->add(
            $builder->create('excludedLevelSKUs', SettingsCollectionType::class, [
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'entry_type' => TextType::class,
                'transformTo' => 'json',
            ])
        );
        $builder->add(
            $builder->create('excludedLevelCategories', SettingsCollectionType::class, [
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'entry_type' => TextType::class,
                'transformTo' => 'json',
            ])
        );

        $builder->add(
            $builder->create(
                self::LEVEL_DOWNGRADE_MODE_SETTINGS_KEY, SettingsChoicesType::class, [
                'choices' => [
                    LevelDowngradeModeProvider::MODE_NONE,
                    LevelDowngradeModeProvider::MODE_AUTO,
                    LevelDowngradeModeProvider::MODE_X_DAYS,
                ],
                'constraints' => [new NotBlank()],
            ])
        );
        $builder->add(
            $builder->create(self::LEVEL_DOWNGRADE_DAYS_SETTINGS_KEY, SettingsIntegerType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
        );

        $builder->add(
            $builder->create(
                self::LEVEL_DOWNGRADE_BASE_SETTINGS_KEY, SettingsChoicesType::class, [
                'choices' => [
                    LevelDowngradeModeProvider::BASE_NONE,
                    LevelDowngradeModeProvider::BASE_ACTIVE_POINTS,
                    LevelDowngradeModeProvider::BASE_EARNED_POINTS,
                    LevelDowngradeModeProvider::BASE_EARNED_POINTS_SINCE_LAST_LEVEL_CHANGE,
                ],
            ])
        );

        $builder->add(
            $builder->create(
                self::LEVEL_RESET_POINTS_ON_DOWNGRADE_SETTINGS_KEY, SettingsCheckboxType::class, [
                'required' => false,
            ])
        );

        $builder->add(
            $builder->create('pushySecretKey', SettingsTextType::class, [
                'required' => false,
            ])
        );

        $builder->addEventSubscriber(new AllTimeActiveSubscriber());
        $builder->addEventSubscriber(new AllTimeNotLockedSubscriber());
        $builder->addEventSubscriber(new ExcludeDeliveryCostSubscriber());
        $builder->addEventSubscriber(new ActivationMethodSubscriber($this->smsGateway));
        $builder->addEventSubscriber(new MarketingVendorSubscriber($this->marketingVendors));
        $builder->addEventSubscriber(new DowngradeModeSubscriber());
        $builder->addEventSubscriber(new DowngradeModeNoneResetAfterDaysFieldSubscriber());

        $this->addSmsConfig($builder);
    }

    /**
     * @param StringSettingEntry        $field
     * @param ExecutionContextInterface $context
     */
    public function checkUrl($field, ExecutionContextInterface $context): void
    {
        if (!$field) {
            return;
        }

        $validator = new UrlValidator();
        $validator->initialize($context);
        $validator->validate($field->getValue(), new Url());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Settings::class,
            'allow_extra_fields' => true,
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     */
    private function addSmsConfig(FormBuilderInterface $builder): void
    {
        // no sms gateway
        if (null === $this->smsGateway) {
            return;
        }

        $fields = $this->smsGateway->getNeededSettings();

        foreach ($fields as $name => $type) {
            $builder->add($this->createField($builder, $name, $type));
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param string               $name
     * @param string               $type
     *
     * @return FormBuilderInterface
     */
    private function createField(FormBuilderInterface $builder, string $name, string $type): FormBuilderInterface
    {
        switch ($type) {
            case 'text':
                return $builder->create($name, SettingsTextType::class, []);
            case 'bool':
                return $builder->create($name, SettingsCheckboxType::class, []);
            case 'integer':
                return $builder->create($name, SettingsIntegerType::class, []);
        }

        throw new \InvalidArgumentException('Undefined field type');
    }
}
