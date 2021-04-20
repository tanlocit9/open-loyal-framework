<?php

namespace OpenLoyalty\Bundle\CampaignBundle\Tests\Integration\Form\Type;

use A2lix\TranslationFormBundle\Form\EventListener\TranslationsListener;
use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use A2lix\TranslationFormBundle\Locale\LocaleProviderInterface as A2lixLocaleProviderInterface;
use OpenLoyalty\Bundle\CampaignBundle\Form\Type\CampaignActivityFormType;
use OpenLoyalty\Bundle\CampaignBundle\Form\Type\CampaignFormType;
use OpenLoyalty\Bundle\CampaignBundle\Form\Type\CampaignVisibilityFormType;
use OpenLoyalty\Bundle\CampaignBundle\Model\Campaign;
use OpenLoyalty\Bundle\LevelBundle\DataFixtures\ORM\LoadLevelData;
use OpenLoyalty\Bundle\SettingsBundle\Service\LocaleProviderInterface;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Class CampaignFormTypeTest.
 */
class CampaignFormTypeTest extends TypeTestCase
{
    private $validator;

    protected function setUp()
    {
        $this->validator = $this->getMockBuilder(
            'Symfony\Component\Validator\Validator\ValidatorInterface'
        )->getMock();
        $this->validator
            ->method('validate')
            ->will($this->returnValue(new ConstraintViolationList()));
        $metadata = $this->getMockBuilder('Symfony\Component\Validator\Mapping\ClassMetadata')
            ->disableOriginalConstructor()->getMock();
        $metadata->method('addConstraint')->willReturn(true);
        $metadata->method('addPropertyConstraint')->willReturn(true);

        $this->validator->method('getMetadataFor')->willReturn(
            $metadata
        );

        parent::setUp();
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        $translationListener = $this->getMockBuilder(TranslationsListener::class)->setMethods([
            'preSetData', 'submit', 'getFieldsOptions', 'getTranslationClass',
        ])->disableOriginalConstructor()->getMock();

        $localeProvider = $this->getMockBuilder(LocaleProviderInterface::class)
            ->disableOriginalConstructor()->getMock();

        $a2lixLocaleProvider = $this->getMockBuilder(A2lixLocaleProviderInterface::class)
            ->disableOriginalConstructor()->getMock();

        return [
            new PreloadedExtension([
                new CampaignFormType($localeProvider),
                new CampaignActivityFormType(),
                new CampaignVisibilityFormType(),
                new TranslationsType($translationListener, $a2lixLocaleProvider),
            ], []),
            new ValidatorExtension($this->validator),
        ];
    }

    /**
     * @test
     */
    public function it_has_valid_data()
    {
        $formData = $this->getMainData();

        $form = $this->factory->create(CampaignFormType::class);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }

        $data = $form->getData();
        $this->assertInstanceOf(Campaign::class, $data);
    }

    protected function getMainData()
    {
        return [
            'reward' => Campaign::REWARD_TYPE_GIFT_CODE,
            'levels' => [LoadLevelData::LEVEL2_ID],
            'segments' => [],
            'unlimited' => false,
            'limit' => 10,
            'limitPerUser' => 2,
            'singleCoupon' => false,
            'coupons' => ['123'],
            'campaignActivity' => [
                'allTimeActive' => false,
                'activeFrom' => (new \DateTime('2016-01-01'))->format('Y-m-d H:i'),
                'activeTo' => (new \DateTime('2037-01-11'))->format('Y-m-d H:i'),
            ],
            'campaignVisibility' => [
                'allTimeVisible' => false,
                'visibleFrom' => (new \DateTime('2016-02-01'))->format('Y-m-d H:i'),
                'visibleTo' => (new \DateTime('2037-02-11'))->format('Y-m-d H:i'),
            ],
            'translations' => [],
        ];
    }
}
