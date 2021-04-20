<?php

namespace OpenLoyalty\Bundle\UserBundle\Form\Type;

use OpenLoyalty\Component\Customer\Domain\Invitation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type as Numeric;

/**
 * Class InvitationFormType.
 */
class InvitationFormType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * InvitationFormType constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', ChoiceType::class, [
            'choices' => [
                'Default' => '',
                'Email' => Invitation::EMAIL_TYPE,
                'Mobile' => Invitation::MOBILE_TYPE,
            ],
        ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'configureFieldsBasedOnType']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureFieldsBasedOnType(FormEvent $event): void
    {
        $data = $event->getData();
        $form = $event->getForm();

        $formType = $data['type'] ?? Invitation::EMAIL_TYPE;

        if ($formType === Invitation::MOBILE_TYPE) {
            $form->add(
                'recipientPhone',
                TextType::class,
                [
                    'constraints' => [
                        new NotBlank(),
                        new Numeric(
                            [
                                'type' => 'numeric',
                                'message' => $this->translator->trans('customer.registration.invalid_phone_number'),
                            ]
                        ),
                    ],
                ]
            );
        } else {
            $form->add('recipientEmail', EmailType::class, [
                'constraints' => [new NotBlank(), new Email()],
            ]);
        }
    }
}
