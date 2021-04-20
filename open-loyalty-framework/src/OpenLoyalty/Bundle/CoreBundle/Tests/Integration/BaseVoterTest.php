<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CoreBundle\Tests\Integration;

use Doctrine\ORM\EntityManager;
use OpenLoyalty\Bundle\UserBundle\DataFixtures\ORM\LoadAdminData;
use OpenLoyalty\Bundle\UserBundle\Entity\Admin;
use OpenLoyalty\Bundle\UserBundle\Entity\Customer;
use OpenLoyalty\Bundle\UserBundle\Entity\Permission;
use OpenLoyalty\Bundle\UserBundle\Entity\Repository\RoleRepositoryInterface;
use OpenLoyalty\Bundle\UserBundle\Entity\Role;
use OpenLoyalty\Bundle\UserBundle\Entity\Seller;
use OpenLoyalty\Bundle\UserBundle\Model\AclAvailableObject;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use OpenLoyalty\Bundle\UserBundle\Service\AclManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class BaseVoterTest.
 */
abstract class BaseVoterTest extends TestCase
{
    protected const USER_ID = '00000000-0000-474c-b092-b0dd880c07e1';

    /**
     * @param array $permissions
     *
     * @return UsernamePasswordToken
     */
    protected function getAdminToken(array $permissions = []): UsernamePasswordToken
    {
        $adminMock = $this->getMockBuilder(Admin::class)->disableOriginalConstructor()
            ->setMethodsExcept(['hasPermission'])
            ->getMock();

        $adminMock
            ->method('hasRole')
            ->with($this->isType('string'))
            ->will($this->returnCallback(
                function (string $role): bool {
                    return $role === 'ROLE_ADMIN';
                }
            ))
        ;

        $adminMock
            ->method('getRoles')
            ->will($this->returnCallback(
                function () use ($permissions): array {
                    $role = new Role('ROLE_ADMIN');
                    foreach ($permissions as $permission) {
                        $role->addPermission($permission);
                    }

                    return [$role];
                }
            ));
        $adminMock
            ->method('getId')
            ->willReturn(LoadAdminData::ADMIN_ID)
        ;

        return new UsernamePasswordToken($adminMock, '', 'some_empty_string');
    }

    /**
     * @return UsernamePasswordToken
     */
    protected function getCustomerToken(): UsernamePasswordToken
    {
        $customerMock = $this->createMock(Customer::class);
        $customerMock
            ->method('hasRole')
            ->with($this->isType('string'))
            ->will($this->returnCallback(
                function (string $role): bool {
                    return $role === 'ROLE_PARTICIPANT';
                }
            ))
        ;
        $customerMock
            ->method('getId')
            ->willReturn(self::USER_ID)
        ;

        return new UsernamePasswordToken($customerMock, '', 'some_empty_string');
    }

    /**
     * @param bool $isAllowedPointTransfer
     *
     * @return UsernamePasswordToken
     */
    protected function getSellerToken(bool $isAllowedPointTransfer = false): UsernamePasswordToken
    {
        $sellerMock = $this->createMock(Seller::class);
        $sellerMock
            ->method('hasRole')
            ->with($this->isType('string'))
            ->will($this->returnCallback(
                function (string $role): string {
                    return $role === 'ROLE_SELLER';
                }
            ))
        ;
        $sellerMock
            ->method('getId')
            ->willReturn(self::USER_ID)
        ;
        $sellerMock
            ->method('isAllowPointTransfer')
            ->willReturn($isAllowedPointTransfer)
        ;

        return new UsernamePasswordToken($sellerMock, '', 'some_empty_string');
    }

    /**
     * @param Voter $voter
     * @param array $attributes
     *
     * @throws \ReflectionException
     */
    protected function assertVoterAttributes(Voter $voter, array $attributes): void
    {
        $translator = $this->getMockBuilder(TranslatorInterface::class)->getMock();
        $translator->method('trans')->willReturn('test');

        $aclManager = new AclManager(
            $this->createMock(RoleRepositoryInterface::class),
            $translator,
            $this->createMock(EntityManager::class)
        );
        $viewPermissions = [];
        $viewFullPermissions = [];

        /** @var AclAvailableObject $resource */
        foreach ($aclManager->getAvailableResources() as $resource) {
            /** @var AclAvailableObject $access */
            foreach ($aclManager->getAvailableAccesses() as $access) {
                if ($access->getCode() === PermissionAccess::VIEW) {
                    $viewPermissions[] = new Permission($resource->getCode(), PermissionAccess::VIEW);
                }
                $viewFullPermissions[] = new Permission($resource->getCode(), $access->getCode());
            }
        }

        foreach ($attributes as $attribute => $params) {
            $subject = isset($params['id']) ? $this->getSubjectById($params['id']) : null;

            // override with custom subject
            if (null === $subject && array_key_exists('subject', $params)) {
                $subject = $params['subject'];
            }
            $this->assertEquals(
                $params['customer'] ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED,
                $voter->vote($this->getCustomerToken(), $subject, [$attribute]),
                $attribute.' - customer'
            );
            $this->assertEquals(
                $params['admin'] ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED,
                $voter->vote(
                    $this->getAdminToken($viewFullPermissions),
                    $subject,
                    [$attribute]
                ),
                $attribute.' - admin'
            );

            if (isset($params['admin_reporter'])) {
                $this->assertEquals(
                    $params['admin_reporter'] ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED,
                    $voter->vote(
                        $this->getAdminToken($viewPermissions),
                        $subject,
                        [$attribute]
                    ),
                    $attribute.' - admin'
                );
            }

            if (isset($params['admin_custom'])) {
                foreach ($params['admin_custom'] as $customAdminCode => $customAdmin) {
                    $customViewPermissions = [];

                    foreach ($customAdmin['permissions'] as $code => $permissions) {
                        foreach ($permissions as $permission) {
                            $customViewPermissions[] = new Permission($code, $permission);
                        }
                    }

                    $this->assertEquals(
                        $customAdmin['expected'] ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED,
                        $voter->vote(
                            $this->getAdminToken($customViewPermissions),
                            $subject,
                            [$attribute]
                        ),
                        $attribute.' - AdminCustom['.$customAdminCode.']'
                    );
                }
            }

            $this->assertEquals(
                $params['seller'] ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED,
                $voter->vote($this->getSellerToken(), $subject, [$attribute]),
                $attribute.' - seller'
            );
        }
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    abstract protected function getSubjectById($id);
}
