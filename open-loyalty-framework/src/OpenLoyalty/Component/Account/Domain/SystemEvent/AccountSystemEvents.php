<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain\SystemEvent;

/**
 * Class AccountSystemEvents.
 */
class AccountSystemEvents
{
    const AVAILABLE_POINTS_AMOUNT_CHANGED = 'oloy.account.available_points_amount_changed';
    const ACCOUNT_CREATED = 'oloy.account.created';
    const CUSTOM_EVENT_OCCURRED = 'oloy.account.custom_event_occurred';
    const CUSTOM_EVENT_GEO_OCCURRED = 'oloy.account.custom_event_geo_occurred';
    const CUSTOM_EVENT_QRCODE_OCCURRED = 'oloy.account.custom_event_qrcode_occurred';
}
