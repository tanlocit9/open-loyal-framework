<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Service;

use Doctrine\ORM\EntityManager;
use OpenLoyalty\Bundle\AuditBundle\Security\Voter\AuditVoter;
use OpenLoyalty\Bundle\CampaignBundle\Security\Voter\CampaignVoter;
use OpenLoyalty\Bundle\AnalyticsBundle\Security\Voter\AnalyticsVoter;
use OpenLoyalty\Bundle\EarningRuleBundle\Security\Voter\EarningRuleVoter;
use OpenLoyalty\Bundle\LevelBundle\Security\Voter\LevelVoter;
use OpenLoyalty\Bundle\TransactionBundle\Security\Voter\TransactionVoter;
use OpenLoyalty\Bundle\SegmentBundle\Security\Voter\SegmentVoter;
use OpenLoyalty\Bundle\SettingsBundle\Security\Voter\SettingsVoter;
use OpenLoyalty\Bundle\PointsBundle\Security\Voter\PointsTransferVoter;
use OpenLoyalty\Bundle\PosBundle\Security\Voter\PosVoter;
use OpenLoyalty\Bundle\UserBundle\Entity\Repository\RoleRepositoryInterface;
use OpenLoyalty\Bundle\UserBundle\Entity\Role;
use OpenLoyalty\Bundle\UserBundle\Model\AclAvailableObject;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use OpenLoyalty\Bundle\UserBundle\Security\Voter\SellerVoter;
use OpenLoyalty\Bundle\UserBundle\Security\Voter\AclVoter;
use OpenLoyalty\Bundle\UserBundle\Security\Voter\AdminVoter;
use OpenLoyalty\Bundle\UserBundle\Security\Voter\CustomerVoter;
use OpenLoyalty\Bundle\UtilityBundle\Security\Voter\UtilityVoter;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class AclManager.
 */
class AclManager implements AclManagerInterface
{
    /**
     * @var RoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * AclManager constructor.
     *
     * @param RoleRepositoryInterface $roleRepository
     * @param TranslatorInterface     $translator
     * @param EntityManager           $entityManager
     */
    public function __construct(RoleRepositoryInterface $roleRepository, TranslatorInterface $translator, EntityManager $entityManager)
    {
        $this->roleRepository = $roleRepository;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdminRoles(): array
    {
        return $this->roleRepository->findBy(['role' => 'ROLE_ADMIN']);
    }

    /**
     * {@inheritdoc}
     */
    public function getAdminMasterRole(): Role
    {
        return $this->roleRepository->findOneBy(['role' => 'ROLE_ADMIN', 'master' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleById(int $id): ?Role
    {
        return $this->roleRepository->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableAccesses(): array
    {
        return [
            new AclAvailableObject(
                PermissionAccess::VIEW,
                $this->translator->trans('user.acl.access.view')
            ),
            new AclAvailableObject(
                PermissionAccess::MODIFY,
                $this->translator->trans('user.acl.access.modify')
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableResources(): array
    {
        return [
            new AclAvailableObject(
                UtilityVoter::PERMISSION_RESOURCE,
                $this->translator->trans('user.acl.resource.utility')
            ),
            new AclAvailableObject(
                EarningRuleVoter::PERMISSION_RESOURCE,
                $this->translator->trans('user.acl.resource.earningRule')
            ),
            new AclAvailableObject(
                LevelVoter::PERMISSION_RESOURCE,
                $this->translator->trans('user.acl.resource.level')
            ),
            new AclAvailableObject(
                TransactionVoter::PERMISSION_RESOURCE,
                $this->translator->trans('user.acl.resource.transaction')
            ),
            new AclAvailableObject(
                SellerVoter::PERMISSION_RESOURCE,
                $this->translator->trans('user.acl.resource.seller')
            ),
            new AclAvailableObject(
                AdminVoter::PERMISSION_RESOURCE,
                $this->translator->trans('user.acl.resource.admin')
            ),
            new AclAvailableObject(
                AclVoter::PERMISSION_RESOURCE,
                $this->translator->trans('user.acl.resource.acl')
            ),
            new AclAvailableObject(
                AnalyticsVoter::PERMISSION_RESOURCE,
                $this->translator->trans('user.acl.resource.analytics')
            ),
            new AclAvailableObject(
                PosVoter::PERMISSION_RESOURCE,
                $this->translator->trans('user.acl.resource.pos')
            ),
            new AclAvailableObject(
                PointsTransferVoter::PERMISSION_RESOURCE,
                $this->translator->trans('user.acl.resource.pointsTransfer')
            ),
            new AclAvailableObject(
                SegmentVoter::PERMISSION_RESOURCE,
                $this->translator->trans('user.acl.resource.segment')
            ),
            new AclAvailableObject(
                SettingsVoter::PERMISSION_RESOURCE,
                $this->translator->trans('user.acl.resource.settings')
            ),
            new AclAvailableObject(
                CustomerVoter::PERMISSION_RESOURCE,
                $this->translator->trans('user.acl.resource.customer')
            ),
            new AclAvailableObject(
                CampaignVoter::PERMISSION_RESOURCE,
                $this->translator->trans('user.acl.resource.rewardCampaign')
            ),
            new AclAvailableObject(
                AuditVoter::PERMISSION_RESOURCE,
                $this->translator->trans('user.acl.resource.audit')
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function update(Role $role): void
    {
        $this->entityManager->persist($role);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $roleId): void
    {
        $role = $this->getRoleById($roleId);
        $this->entityManager->remove($role);
        $this->entityManager->flush();
    }
}
