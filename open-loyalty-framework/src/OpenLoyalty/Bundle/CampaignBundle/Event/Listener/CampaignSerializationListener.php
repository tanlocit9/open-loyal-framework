<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Event\Listener;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use OpenLoyalty\Bundle\CampaignBundle\Service\CampaignProvider;
use OpenLoyalty\Bundle\CampaignBundle\Service\CampaignValidator;
use OpenLoyalty\Bundle\MarkDownBundle\Service\ContextMarkDownFormatter;
use OpenLoyalty\Bundle\MarkDownBundle\Service\FOSContextProvider;
use OpenLoyalty\Bundle\UserBundle\Status\CustomerStatusProvider;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignCategory;
use OpenLoyalty\Component\Campaign\Domain\CampaignCategoryId;
use OpenLoyalty\Component\Campaign\Domain\CampaignCategoryRepository;
use OpenLoyalty\Component\Campaign\Domain\CustomerId as CampaignCustomerId;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignUsageRepository;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CouponUsage;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CouponUsageRepository;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\EarningRule\Domain\EarningRule;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleId;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleRepository;
use OpenLoyalty\Component\Level\Domain\Level;
use OpenLoyalty\Component\Level\Domain\LevelId;
use OpenLoyalty\Component\Level\Domain\LevelRepository;
use OpenLoyalty\Component\Segment\Domain\Segment;
use OpenLoyalty\Component\Segment\Domain\SegmentId;
use OpenLoyalty\Component\Segment\Domain\SegmentRepository;
use PhpOption\None;

/**
 * Class CampaignSerializationListener.
 */
class CampaignSerializationListener implements EventSubscriberInterface
{
    /**
     * @var CampaignValidator
     */
    protected $campaignValidator;

    /**
     * @var SegmentRepository
     */
    protected $segmentRepository;

    /**
     * @var LevelRepository
     */
    protected $levelRepository;

    /**
     * @var CouponUsageRepository
     */
    protected $couponUsageRepository;

    /**
     * @var CampaignCategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var CampaignProvider
     */
    protected $campaignProvider;

    /**
     * @var CampaignUsageRepository
     */
    private $campaignUsageRepository;

    /**
     * @var CustomerStatusProvider
     */
    private $customerStatusProvider;

    /**
     * @var ContextMarkDownFormatter
     */
    protected $contextMarkDownFormatter;

    /**
     * @var EarningRuleRepository
     */
    protected $earningRuleRepository;

    /**
     * CampaignSerializationListener constructor.
     *
     * @param CampaignValidator          $campaignValidator
     * @param SegmentRepository          $segmentRepository
     * @param LevelRepository            $levelRepository
     * @param CouponUsageRepository      $couponUsageRepository
     * @param CampaignProvider           $campaignProvider
     * @param CampaignUsageRepository    $campaignUsageRepository
     * @param CustomerStatusProvider     $customerStatusProvider
     * @param ContextMarkDownFormatter   $contextMarkDownFormatter
     * @param CampaignCategoryRepository $categoryRepository
     * @param EarningRuleRepository      $earningRuleRepository
     */
    public function __construct(
        CampaignValidator $campaignValidator,
        SegmentRepository $segmentRepository,
        LevelRepository $levelRepository,
        CouponUsageRepository $couponUsageRepository,
        CampaignProvider $campaignProvider,
        CampaignUsageRepository $campaignUsageRepository,
        CustomerStatusProvider $customerStatusProvider,
        ContextMarkDownFormatter $contextMarkDownFormatter,
        CampaignCategoryRepository $categoryRepository,
        EarningRuleRepository $earningRuleRepository
    ) {
        $this->campaignValidator = $campaignValidator;
        $this->segmentRepository = $segmentRepository;
        $this->levelRepository = $levelRepository;
        $this->couponUsageRepository = $couponUsageRepository;
        $this->campaignProvider = $campaignProvider;
        $this->campaignUsageRepository = $campaignUsageRepository;
        $this->customerStatusProvider = $customerStatusProvider;
        $this->contextMarkDownFormatter = $contextMarkDownFormatter;
        $this->categoryRepository = $categoryRepository;
        $this->earningRuleRepository = $earningRuleRepository;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            [
                'event' => 'serializer.post_serialize',
                'method' => 'onPostSerialize',
            ],
        ];
    }

    /**
     * @param ObjectEvent $event
     */
    public function onPostSerialize(ObjectEvent $event): void
    {
        $context = $event->getContext();

        /** @var Campaign $campaign */
        $campaign = $event->getObject();

        if (!$campaign instanceof Campaign) {
            return;
        }

        $this->serializeEarningRule($event, $campaign);
        $this->serializeSegments($event, $campaign);
        $this->serializeLevelNames($event, $campaign);

        if (!$this->campaignValidator->isCampaignActive($campaign)) {
            if (!$campaign->getCampaignActivity()->isAllTimeActive()) {
                $event->getVisitor()->addData('will_be_active_from', $campaign->getCampaignActivity()->getActiveFrom()->format(\DateTime::ISO8601));
                $event->getVisitor()->addData('will_be_active_to', $campaign->getCampaignActivity()->getActiveTo()->format(\DateTime::ISO8601));
            }
        }

        $categoryNames = [];
        foreach ($campaign->getCategories() as $categoryId) {
            $category = $this->categoryRepository->byId(new CampaignCategoryId($categoryId->__toString()));
            if ($category instanceof CampaignCategory) {
                $categoryNames[$categoryId->__toString()] = $category->getName();
            }
        }
        $event->getVisitor()->addData('categoryNames', $categoryNames);

        $event->getVisitor()->addData('usageLeft', $this->campaignProvider->getUsageLeft($campaign));

        $customerId = $context->attributes->get('customerId');

        if ($customerId && !$customerId instanceof None) {
            $customerId = $context->attributes->get('customerId')->get();
            $usageLeftForCustomer = $this->campaignProvider->getUsageLeftForCustomer(
                $campaign,
                new CampaignCustomerId($customerId)
            );
            $event->getVisitor()->addData('usageLeftForCustomer', $usageLeftForCustomer);

            $customerStatus = $this->customerStatusProvider->getStatus(new CustomerId($customerId));
            $points = $customerStatus->getPoints();

            $canBuy = false;
            if ($points >= $campaign->getCostInPoints() && $this->campaignValidator->isCampaignActive($campaign)) {
                $canBuy = true;
            }

            $event->getVisitor()->setData('canBeBoughtByCustomer', $canBuy);
        }

        $event->getVisitor()->addData('visibleForCustomersCount', count($this->campaignProvider->visibleForCustomers($campaign)));
        $event->getVisitor()->addData('usersWhoUsedThisCampaignCount', $this->countUsersWhoUsedThisCampaign($campaign));

        $formatterContext = new FOSContextProvider($context);

        $event->getVisitor()->setData(
            'brandDescription',
            $this->contextMarkDownFormatter->format($campaign->getBrandDescription(), $formatterContext)
        );
        $event->getVisitor()->setData(
            'shortDescription',
            $this->contextMarkDownFormatter->format($campaign->getShortDescription(), $formatterContext)
        );
        $event->getVisitor()->setData(
            'conditionsDescription',
            $this->contextMarkDownFormatter->format($campaign->getConditionsDescription(), $formatterContext)
        );
        $event->getVisitor()->setData(
            'usageInstruction',
            $this->contextMarkDownFormatter->format($campaign->getUsageInstruction(), $formatterContext)
        );
    }

    /**
     * @param ObjectEvent $event
     * @param Campaign    $campaign
     */
    private function serializeSegments(ObjectEvent $event, Campaign $campaign): void
    {
        $segmentNames = [];

        foreach ($campaign->getSegments() as $segmentId) {
            $segment = $this->segmentRepository->byId(new SegmentId($segmentId->__toString()));
            if ($segment instanceof Segment) {
                $segmentNames[$segmentId->__toString()] = $segment->getName();
            }
        }

        $event->getVisitor()->addData('segmentNames', $segmentNames);
    }

    /**
     * @param ObjectEvent $event
     * @param Campaign    $campaign
     */
    private function serializeLevelNames(ObjectEvent $event, Campaign $campaign): void
    {
        $levelNames = [];

        foreach ($campaign->getLevels() as $levelId) {
            $level = $this->levelRepository->byId(new LevelId($levelId->__toString()));
            if ($level instanceof Level) {
                $levelNames[$levelId->__toString()] = $level->getName();
            }
        }

        $event->getVisitor()->addData('levelNames', $levelNames);
    }

    /**
     * @param Campaign $campaign
     *
     * @return int
     */
    protected function countUsersWhoUsedThisCampaign(Campaign $campaign): int
    {
        $usages = $this->couponUsageRepository->findByCampaign($campaign->getCampaignId());
        $users = [];

        /** @var CouponUsage $usage */
        foreach ($usages as $usage) {
            $users[$usage->getCustomerId()->__toString()] = true;
        }

        return count($users);
    }

    /**
     * @param ObjectEvent $event
     * @param Campaign    $campaign
     */
    private function serializeEarningRule(ObjectEvent $event, Campaign $campaign): void
    {
        if ($campaign->getEarningRuleId()) {
            $earningRuleId = new EarningRuleId($campaign->getEarningRuleId());
            $earningRule = $this->earningRuleRepository->byId($earningRuleId);

            $data['type'] = array_flip(EarningRule::TYPE_MAP)[get_class($earningRule)];
            $data['name'] = $earningRule->getName();
            $data['pointsAmount'] = $earningRule->getPointsAmount();

            $event->getVisitor()->addData('earningRule', $data);
        }
    }
}
