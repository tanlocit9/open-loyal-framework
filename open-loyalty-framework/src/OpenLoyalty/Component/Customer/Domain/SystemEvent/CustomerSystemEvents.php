<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\SystemEvent;

/**
 * Class CustomerSystemEvents.
 */
class CustomerSystemEvents
{
    const CUSTOMER_REGISTERED = 'oloy.customer.registered';
    const CUSTOMER_DEACTIVATED = 'oloy.customer.deactivated';
    const CUSTOMER_ACTIVATED = 'oloy.customer.activated';
    const CUSTOMER_UPDATED = 'oloy.customer.updated';
    const CUSTOMER_AGREEMENTS_UPDATED = 'oloy.customer.agreements_updated';
    const CUSTOMER_LOGGED_IN = 'oloy.customer.logged_in';
    const CUSTOMER_REFERRAL = 'oloy.customer.referral';
    const NEWSLETTER_SUBSCRIPTION = 'oloy.customer.newsletter_subscription';
    const CUSTOMER_LEVEL_CHANGED_AUTOMATICALLY = 'oloy.customer.level_changed_automatically';
    const CUSTOMER_LEVEL_CHANGED = 'oloy.customer.level_changed';
    const CUSTOMER_MANUALLY_LEVEL_REMOVED = 'oloy.customer.manually_level_removed';
    const CUSTOMER_RECALCULATE_LEVEL_REQUESTED = 'oloy.customer.recalculate_level_requested';
    const CUSTOMER_CAMPAIGN_USAGE_WAS_CHANGED = 'oloy.campaign.campaign_usage_was_changed';
}
