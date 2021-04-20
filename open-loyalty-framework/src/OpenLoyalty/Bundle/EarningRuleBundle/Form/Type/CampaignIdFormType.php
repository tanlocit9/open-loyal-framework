<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\Form\Type;

use OpenLoyalty\Bundle\EarningRuleBundle\Form\DataTransformer\CampaignIdDataTransformer;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\CampaignRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class CustomerIdFormType.
 */
class CampaignIdFormType extends AbstractType
{
    /**
     * @var CampaignRepository
     */
    private $campaignRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * CampaignIdFormType constructor.
     *
     * @param CampaignRepository $campaignRepository
     */
    public function __construct(CampaignRepository $campaignRepository, TranslatorInterface $translator)
    {
        $this->campaignRepository = $campaignRepository;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            if (is_null($data)) {
                $event->getForm()->addError(new FormError($this->translator->trans('earning_rule.campaign.required')));
            } else {
                $customer = $this->campaignRepository->byId(new CampaignId($data));
                if (!$customer instanceof Campaign) {
                    $event->getForm()->addError(new FormError($this->translator->trans('earning_rule.campaign.not_exists')));
                }
            }
        });
        $builder->addModelTransformer(new CampaignIdDataTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TextType::class;
    }
}
