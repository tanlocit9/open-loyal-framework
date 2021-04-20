<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Tests\Unit\Form\Type;

use OpenLoyalty\Bundle\UserBundle\Form\Type\ConfigurablePasswordType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Class CustomerRegistrationFormTypeTest.
 */
final class ConfigurablePasswordTypeTest extends TypeTestCase
{
    /**
     * @return array
     */
    protected function getExtensions(): array
    {
        return array(
            new PreloadedExtension(
                [
                    new ConfigurablePasswordType('simple'),
                ],
                []
            ),
        );
    }

    /**
     * @test
     */
    public function it_contains_valid_form_validation(): void
    {
        $builder = $this->factory->createBuilder();
        $builder->add('password', ConfigurablePasswordType::class);
        $form = $builder->getForm();
        $form->submit(['password' => 'password']);
        $this->assertTrue($form->isValid());
        $errors = $form->getErrors(true);
        $this->assertEmpty($errors, 'Simple validation shouldn\'t go with errors');
    }
}
