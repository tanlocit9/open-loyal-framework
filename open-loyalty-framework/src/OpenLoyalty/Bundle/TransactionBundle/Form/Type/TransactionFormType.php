<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TransactionBundle\Form\Type;

use OpenLoyalty\Bundle\TransactionBundle\Validator\Constraints\UniqueDocumentNumber;
use OpenLoyalty\Component\Pos\Domain\Pos;
use OpenLoyalty\Component\Pos\Domain\PosRepository;
use OpenLoyalty\Component\Transaction\Domain\Transaction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;
use OpenLoyalty\Bundle\TransactionBundle\Validator\Constraints\TransactionReturnDocument;

/**
 * Class TransactionFormType.
 */
class TransactionFormType extends AbstractType
{
    /**
     * @var PosRepository
     */
    protected $posRepository;

    /**
     * TransactionFormType constructor.
     *
     * @param PosRepository $posRepository
     */
    public function __construct(PosRepository $posRepository)
    {
        $this->posRepository = $posRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = $this->posRepository->findAll();
        $choices = array_map(function (Pos $pos) {
            return (string) $pos->getPosId();
        }, $choices);
        $builder->add($this->buildTransactionDataForm($builder));
        $builder->add('revisedDocument', TextType::class, [
            'required' => false,
            'constraints' => [new TransactionReturnDocument(['isManually' => true])],
        ]);
        $builder->add('items', CollectionType::class, [
            'entry_type' => ItemFormType::class,
            'allow_delete' => true,
            'allow_add' => true,
            'error_bubbling' => false,
        ]);
        $builder->add('customerData', CustomerDetailsFormType::class, [
            'required' => true,
            'constraints' => [new NotBlank(), new Valid()],
        ]);
        $builder->add('pos', ChoiceType::class, [
            'required' => false,
            'choices' => array_combine($choices, $choices),
        ]);
        $builder->add('labels', CollectionType::class, [
            'allow_add' => true,
            'allow_delete' => true,
            'entry_type' => LabelFormType::class,
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     *
     * @return FormBuilderInterface
     */
    protected function buildTransactionDataForm(FormBuilderInterface $builder): FormBuilderInterface
    {
        $dataFrom = $builder->create('transactionData', FormType::class);
        $dataFrom->add('documentType', ChoiceType::class, [
            'empty_data' => Transaction::TYPE_SELL,
            'choices' => [
                Transaction::TYPE_SELL => Transaction::TYPE_SELL,
                Transaction::TYPE_RETURN => Transaction::TYPE_RETURN,
            ],
            'required' => false,
        ]);
        $dataFrom->add('documentNumber', TextType::class, [
            'required' => true,
            'constraints' => [new NotBlank(), new UniqueDocumentNumber()],
        ]);
        $dataFrom->add('purchasePlace', TextType::class);
        $dataFrom->add('purchaseDate', DateTimeType::class, [
            'required' => true,
            'widget' => 'single_text',
            'format' => DateTimeType::HTML5_FORMAT,
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        return $dataFrom;
    }
}
