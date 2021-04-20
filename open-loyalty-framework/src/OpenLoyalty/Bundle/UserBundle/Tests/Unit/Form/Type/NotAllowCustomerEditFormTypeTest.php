<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Tests\Unit\Form\Type;

use OpenLoyalty\Bundle\UserBundle\Form\Type\CustomerEditFormType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class NotAllowCustomerEditFormTypeTest.
 */
class NotAllowCustomerEditFormTypeTest extends CustomerEditFormTypeCase
{
    /**
     * {@inheritdoc}
     */
    protected function isAllowCustomersProfileEdits(): bool
    {
        return false;
    }

    /**
     * @test
     */
    public function it_does_not_allow_to_change_profile_when_customer_edit_not_allowed_fields(): void
    {
        $options = [
            'method' => Request::METHOD_PUT,
        ];

        $form = $this->factory->create(CustomerEditFormType::class, [], $options);
        $data = [
            'lastName' => 'Jan',
            'firstName' => 'Kowalski',
        ];

        $form->submit($data, false);
        $this->assertFalse($form->isValid());
    }

    /**
     * @test
     */
    public function it_allows_to_change_profile_when_customer_edit_allowed_fields(): void
    {
        $options = [
            'method' => Request::METHOD_PUT,
        ];

        $form = $this->factory->create(CustomerEditFormType::class, [], $options);
        $data = [
            'agreement1' => false,
            'agreement2' => false,
            'agreement3' => false,
        ];

        $form->submit($data, false);
        $this->assertTrue($form->isValid());
    }
}
