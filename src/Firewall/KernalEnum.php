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

namespace Shieldon\Firewall;

/**
 * The primary Shiendon class.
 */
class Kernel
{
    /**
     * HTTP Status Codes
     */
    const HTTP_STATUS_OK                 = 200;
    const HTTP_STATUS_SEE_OTHER          = 303;
    const HTTP_STATUS_BAD_REQUEST        = 400;
    const HTTP_STATUS_FORBIDDEN          = 403;
    const HTTP_STATUS_TOO_MANY_REQUESTS  = 429;

    /**
     * Reason Codes (ALLOW)
     */
    const REASON_IS_SEARCH_ENGINE        = 100;
    const REASON_IS_GOOGLE               = 101;
    const REASON_IS_BING                 = 102;
    const REASON_IS_YAHOO                = 103;
    const REASON_IS_SOCIAL_NETWORK       = 110;
    const REASON_IS_FACEBOOK             = 111;
    const REASON_IS_TWITTER              = 112;

    /**
     * Reason Codes (DENY)
     */
    const REASON_TOO_MANY_SESSIONS       = 1;
    const REASON_TOO_MANY_ACCESSES       = 2; // (not used)
    const REASON_EMPTY_JS_COOKIE         = 3;
    const REASON_EMPTY_REFERER           = 4;
    const REASON_REACHED_LIMIT_DAY       = 11;
    const REASON_REACHED_LIMIT_HOUR      = 12;
    const REASON_REACHED_LIMIT_MINUTE    = 13;
    const REASON_REACHED_LIMIT_SECOND    = 14;
    const REASON_INVALID_IP              = 40;
    const REASON_DENY_IP                 = 41;
    const REASON_ALLOW_IP                = 42;
    const REASON_COMPONENT_IP            = 81;
    const REASON_COMPONENT_RDNS          = 82;
    const REASON_COMPONENT_HEADER        = 83;
    const REASON_COMPONENT_USERAGENT     = 84;
    const REASON_COMPONENT_TRUSTED_ROBOT = 85;
    const REASON_MANUAL_BAN              = 99;

    /**
     * Action Codes
     */
    const ACTION_DENY                    = 0;
    const ACTION_ALLOW                   = 1;
    const ACTION_TEMPORARILY_DENY        = 2;
    const ACTION_UNBAN                   = 9;

    /**
     * Result Codes
     */
    const RESPONSE_DENY                  = 0;
    const RESPONSE_ALLOW                 = 1;
    const RESPONSE_TEMPORARILY_DENY      = 2;
    const RESPONSE_LIMIT_SESSION         = 3;

    /**
     * Logger Codes
     */
    const LOG_LIMIT                      = 3;
    const LOG_PAGEVIEW                   = 11;
    const LOG_BLACKLIST                  = 98;
    const LOG_CAPTCHA                    = 99;

    const KERNEL_DIR                     = __DIR__;
}
