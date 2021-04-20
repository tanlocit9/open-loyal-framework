<?php

namespace OpenLoyalty\Component\Account\Infrastructure\Model;

use OpenLoyalty\Component\Customer\Domain\ReadModel\InvitationDetails;

/**
 * Class ReferralEvaluationResult.
 */
class ReferralEvaluationResult extends EvaluationResult
{
    /**
     * @var string
     */
    protected $rewardType;

    /**
     * @var InvitationDetails
     */
    protected $invitation;

    /**
     * ReferralEvaluationResult constructor.
     *
     * @param string            $earningRuleId
     * @param float             $points
     * @param string            $rewardType
     * @param InvitationDetails $invitationDetails
     * @param string            $name
     */
    public function __construct($earningRuleId, $points, $rewardType, InvitationDetails $invitationDetails, string $name = '')
    {
        parent::__construct($earningRuleId, $points, $name);
        $this->rewardType = $rewardType;
        $this->invitation = $invitationDetails;
    }

    /**
     * @return string
     */
    public function getRewardType()
    {
        return $this->rewardType;
    }

    /**
     * @return InvitationDetails
     */
    public function getInvitation()
    {
        return $this->invitation;
    }
}
