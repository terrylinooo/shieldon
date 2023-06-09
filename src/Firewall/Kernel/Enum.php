<?php
/**
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * php version 7.1.0
 *
 * @category  Web-security
 * @package   Shieldon
 * @author    Terry Lin <contact@terryl.in>
 * @copyright 2019 terrylinooo
 * @license   https://github.com/terrylinooo/shieldon/blob/2.x/LICENSE MIT
 * @link      https://github.com/terrylinooo/shieldon
 * @see       https://shieldon.io
 */

declare(strict_types=1);

namespace Shieldon\Firewall\Kernel;

/**
 * The constants of Shieldon Firewall.
 */
class Enum
{
    /**
     * HTTP Status Codes
     */

    const HTTP_STATUS_OK                = 200;
    const HTTP_STATUS_SEE_OTHER         = 303;
    const HTTP_STATUS_BAD_REQUEST       = 400;
    const HTTP_STATUS_FORBIDDEN         = 403;
    const HTTP_STATUS_TOO_MANY_REQUESTS = 429;

    /**
     * Reason Codes (ALLOW)
     */

    const REASON_IS_SEARCH_ENGINE_ALLOWED  = 100;
    const REASON_IS_GOOGLE_ALLOWED         = 101;
    const REASON_IS_BING_ALLOWED           = 102;
    const REASON_IS_YAHOO_ALLOWED          = 103;
    const REASON_IS_SOCIAL_NETWORK_ALLOWED = 110;
    const REASON_IS_FACEBOOK_ALLOWED       = 111;
    const REASON_IS_TWITTER_ALLOWED        = 112;

    /**
     * Reason Codes (DENY)
     */

    const REASON_TOO_MANY_SESSIONS_DENIED       = 1;
    const REASON_TOO_MANY_ACCESSE_DENIED        = 2; // (not used)
    const REASON_EMPTY_JS_COOKIE_DENIED         = 3;
    const REASON_EMPTY_REFERER_DENIED           = 4;
    const REASON_REACH_DAILY_LIMIT_DENIED       = 11;
    const REASON_REACH_HOURLY_LIMIT_DENIED      = 12;
    const REASON_REACH_MINUTELY_LIMIT_DENIED    = 13;
    const REASON_REACH_SECONDLY_LIMIT_DENIED    = 14;
    const REASON_INVALID_IP_DENIED              = 40;
    const REASON_DENY_IP_DENIED                 = 41;
    const REASON_ALLOW_IP_DENIED                = 42;
    const REASON_COMPONENT_IP_DENIED            = 81;
    const REASON_COMPONENT_RDNS_DENIED          = 82;
    const REASON_COMPONENT_HEADER_DENIED        = 83;
    const REASON_COMPONENT_USERAGENT_DENIED     = 84;
    const REASON_COMPONENT_TRUSTED_ROBOT_DENIED = 85;
    const REASON_MANUAL_BAN_DENIED              = 99;

    /**
     * Action Codes
     */

    const ACTION_DENY             = 0;
    const ACTION_ALLOW            = 1;
    const ACTION_TEMPORARILY_DENY = 2;
    const ACTION_UNBAN            = 9;

    /**
     * Result Codes
     */
    const RESPONSE_DENY             = 0;
    const RESPONSE_ALLOW            = 1;
    const RESPONSE_TEMPORARILY_DENY = 2;
    const RESPONSE_LIMIT_SESSION    = 3;

    /**
     * Logger Codes
     */

    const LOG_LIMIT     = 3;
    const LOG_PAGEVIEW  = 11;
    const LOG_BLACKLIST = 98;
    const LOG_CAPTCHA   = 99;

    const KERNEL_DIR = __DIR__ . '/../';
}
