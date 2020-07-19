<?php
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Shieldon\Firewall\Traits\Kernel;

use Shieldon\Firewall\Kernel;
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\get_session;
use function Shieldon\Firewall\unset_superglobal;

/*
 * This trait is used on Kernel only.
 */
trait FilterTrait
{
    /**
     * Enable or disable the filters.
     *
     * @var array
     */
    protected $filterStatus = [
        /**
         * Check how many pageviews an user made in a short period time.
         * For example, limit an user can only view 30 pages in 60 minutes.
         */
        'frequency' => true,

        /**
         * If an user checks any internal link on your website, the user's
         * browser will generate HTTP_REFERER information.
         * When a user view many pages without HTTP_REFERER information meaning
         * that the user MUST be a web crawler.
         */
        'referer' => false,

        /**
         * Most of web crawlers do not render JavaScript, they only get the 
         * content they want, so we can check whether the cookie can be created
         * by JavaScript or not.
         */
        'cookie' => false,

        /**
         * Every unique user should only has a unique session, but if a user
         * creates different sessions every connection... meaning that the 
         * user's browser doesn't support cookie.
         * It is almost impossible that modern browsers not support cookie,
         * therefore the user MUST be a web crawler.
         */
        'session' => false,
    ];

    /**
     * The status for Filters to reset.
     *
     * @var array
     */
    protected $filterResetStatus = [
        's' => false, // second.
        'm' => false, // minute.
        'h' => false, // hour.
        'd' => false, // day.
    ];

    /**
     * Detect and analyze an user's behavior.
     *
     * @return int The response code.
     */
    protected function filter(): int
    {
        $now = time();
        $isFlagged = false;

        // Fetch an IP data from Shieldon log table.
        $ipDetail = $this->driver->get($this->ip, 'filter');

        $ipDetail = $this->driver->parseData($ipDetail, 'filter');
        $logData = $ipDetail;

        // Counting user pageviews.
        foreach (array_keys($this->filterResetStatus) as $unit) {

            // Each time unit will increase by 1.
            $logData['pageviews_' . $unit] = $ipDetail['pageviews_' . $unit] + 1;
            $logData['first_time_' . $unit] = $ipDetail['first_time_' . $unit];
        }

        $logData['first_time_flag'] = $ipDetail['first_time_flag'];

        if (!empty($ipDetail['ip'])) {
            $logData['ip'] = $this->ip;
            $logData['session'] = get_session()->get('id');
            $logData['hostname'] = $this->rdns;
            $logData['last_time'] = $now;

            // Filter: HTTP referrer information.
            $filterReferer = $this->filterReferer($logData, $ipDetail, $isFlagged);
            $isFlagged = $filterReferer['is_flagged'];
            $logData = $filterReferer['log_data'];

            if ($filterReferer['is_reject']) {
                return kernel::RESPONSE_TEMPORARILY_DENY;
            }

            // Filter: Session.
            $filterSession = $this->filterSession($logData, $ipDetail, $isFlagged);
            $isFlagged = $filterSession['is_flagged'];
            $logData = $filterSession['log_data'];

            if ($filterSession['is_reject']) {
                return kernel::RESPONSE_TEMPORARILY_DENY;
            }

            // Filter: JavaScript produced cookie.
            $filterCookie = $this->filterCookie($logData, $ipDetail, $isFlagged);
            $isFlagged = $filterCookie['is_flagged'];
            $logData = $filterCookie['log_data'];

            if ($filterCookie['is_reject']) {
                return kernel::RESPONSE_TEMPORARILY_DENY;
            }

            // Filter: frequency.
            $filterFrequency = $this->filterFrequency($logData, $ipDetail, $isFlagged);
            $isFlagged = $filterFrequency['is_flagged'];
            $logData = $filterFrequency['log_data'];

            if ($filterFrequency['is_reject']) {
                return kernel::RESPONSE_TEMPORARILY_DENY;
            }

            // Is fagged as unusual beavior? Count the first time.
            if ($isFlagged) {
                $logData['first_time_flag'] = (!empty($logData['first_time_flag'])) ? $logData['first_time_flag'] : $now;
            }

            // Reset the flagged factor check.
            if (!empty($ipDetail['first_time_flag'])) {
                if ($now - $ipDetail['first_time_flag'] >= $this->properties['time_reset_limit']) {
                    $logData['flag_multi_session'] = 0;
                    $logData['flag_empty_referer'] = 0;
                    $logData['flag_js_cookie'] = 0;
                }
            }

            $this->driver->save($this->ip, $logData, 'filter');

        } else {

            // If $ipDetail[ip] is empty.
            // It means that the user is first time visiting our webiste.
            $this->InitializeFirstTimeFilter($logData);
        }

        return kernel::RESPONSE_ALLOW;
    }

    /**
     * When the user is first time visiting our webiste.
     * Initialize the log data.
     * 
     * @param array $logData The user's log data.
     *
     * @return void
     */
    protected function InitializeFirstTimeFilter($logData)
    {
        $now = time();

        $logData['ip']        = $this->ip;
        $logData['session']   = get_session()->get('id');
        $logData['hostname']  = $this->rdns;
        $logData['last_time'] = $now;

        foreach (array_keys($this->filterResetStatus) as $unit) {
            $logData['first_time_' . $unit] = $now;
        }

        $this->driver->save($this->ip, $logData, 'filter');
    }

    /**
     * Filter - Referer.
     *
     * @param array $logData   IP data from Shieldon log table.
     * @param array $ipData    The IP log data.
     * @param bool  $isFlagged Is flagged as unusual behavior or not.
     *
     * @return array
     */
    protected function filterReferer(array $logData, array $ipDetail, bool $isFlagged): array
    {
        $isReject = false;

        if ($this->filterStatus['referer']) {

            if ($logData['last_time'] - $ipDetail['last_time'] > $this->properties['interval_check_referer']) {

                // Get values from data table. We will count it and save it back to data table.
                // If an user is already in your website, it is impossible no referer when he views other pages.
                $logData['flag_empty_referer'] = $ipDetail['flag_empty_referer'] ?? 0;

                if (empty(get_request()->getHeaderLine('referer'))) {
                    $logData['flag_empty_referer']++;
                    $isFlagged = true;
                }

                // Ban this IP if they reached the limit.
                if ($logData['flag_empty_referer'] > $this->properties['limit_unusual_behavior']['referer']) {
                    $this->action(
                        kernel::ACTION_TEMPORARILY_DENY,
                        kernel::REASON_EMPTY_REFERER
                    );
                    $isReject = true;
                }
            }
        }

        return [
            'is_flagged' => $isFlagged,
            'is_reject' => $isReject,
            'log_data' => $logData,
        ];
    }

    /**
     * Filter - Session
     *
     * @param array $logData   IP data from Shieldon log table.
     * @param array $ipData    The IP log data.
     * @param bool  $isFlagged Is flagged as unusual behavior or not.
     *
     * @return array
     */
    protected function filterSession(array $logData, array $ipDetail, bool $isFlagged): array
    {
        $isReject = false;

        if ($this->filterStatus['session']) {

            if ($logData['last_time'] - $ipDetail['last_time'] > $this->properties['interval_check_session']) {

                // Get values from data table. We will count it and save it back to data table.
                $logData['flag_multi_session'] = $ipDetail['flag_multi_session'] ?? 0;
                
                if (get_session()->get('id') !== $ipDetail['session']) {

                    // Is is possible because of direct access by the same user many times.
                    // Or they don't have session cookie set.
                    $logData['flag_multi_session']++;
                    $isFlagged = true;
                }

                // Ban this IP if they reached the limit.
                if ($logData['flag_multi_session'] > $this->properties['limit_unusual_behavior']['session']) {
                    $this->action(
                        kernel::ACTION_TEMPORARILY_DENY,
                        kernel::REASON_TOO_MANY_SESSIONS
                    );
                    $isReject = true;
                }
            }
        }


        return [
            'is_flagged' => $isFlagged,
            'is_reject' => $isReject,
            'log_data' => $logData,
        ];
    }

    /**
     * Filter - Cookie
     *
     * @param array $logData   IP data from Shieldon log table.
     * @param array $ipData    The IP log data.
     * @param bool  $isFlagged Is flagged as unusual behavior or not.
     *
     * @return array
     */
    protected function filterCookie(array $logData, array $ipDetail, bool $isFlagged): array
    {
        $isReject = false;

        // Let's checking cookie created by javascript..
        if ($this->filterStatus['cookie']) {

            // Get values from data table. We will count it and save it back to data table.
            $logData['flag_js_cookie'] = $ipDetail['flag_js_cookie'] ?? 0;
            $logData['pageviews_cookie'] = $ipDetail['pageviews_cookie'] ?? 0;

            $c = $this->properties['cookie_name'];

            $jsCookie = get_request()->getCookieParams()[$c] ?? 0;

            // Checking if a cookie is created by JavaScript.
            if (!empty($jsCookie)) {

                if ($jsCookie == '1') {
                    $logData['pageviews_cookie']++;

                } else {
                    // Flag it if the value is not 1.
                    $logData['flag_js_cookie']++;
                    $isFlagged = true;
                }
            } else {
                // If we cannot find the cookie, flag it.
                $logData['flag_js_cookie']++;
                $isFlagged = true;
            }

            if ($logData['flag_js_cookie'] > $this->properties['limit_unusual_behavior']['cookie']) {

                // Ban this IP if they reached the limit.
                $this->action(
                    kernel::ACTION_TEMPORARILY_DENY,
                    kernel::REASON_EMPTY_JS_COOKIE
                );
                $isReject = true;
            }

            // Remove JS cookie and reset.
            if ($logData['pageviews_cookie'] > $this->properties['limit_unusual_behavior']['cookie']) {
                $logData['pageviews_cookie'] = 0; // Reset to 0.
                $logData['flag_js_cookie'] = 0;
                unset_superglobal($c, 'cookie');
            }
        }

        return [
            'is_flagged' => $isFlagged,
            'is_reject' => $isReject,
            'log_data' => $logData,
        ];
    }

    /**
     * Filter - Frequency
     *
     * @param array $logData   IP data from Shieldon log table.
     * @param array $ipData    The IP log data.
     * @param bool  $isFlagged Is flagged as unusual behavior or not.
     *
     * @return array
     */
    protected function filterFrequency(array $logData, array $ipDetail, bool $isFlagged): array
    {
        $isReject = false;

        if ($this->filterStatus['frequency']) {
            $timeSecond = [];
            $timeSecond['s'] = 1;
            $timeSecond['m'] = 60;
            $timeSecond['h'] = 3600;
            $timeSecond['d'] = 86400;

            foreach (array_keys($this->properties['time_unit_quota']) as $unit) {

                if (($logData['last_time'] - $ipDetail['first_time_' . $unit]) >= ($timeSecond[$unit] + 1)) {

                    // For example:
                    // (1) minutely: now > first_time_m about 61, (2) hourly: now > first_time_h about 3601, 
                    // Let's prepare to rest the the pageview count.
                    $this->filterResetStatus[$unit] = true;

                } else {

                    // If an user's pageview count is more than the time period limit
                    // He or she will get banned.
                    if ($logData['pageviews_' . $unit] > $this->properties['time_unit_quota'][$unit]) {

                        if ($unit === 's') {
                            $this->action(
                                kernel::ACTION_TEMPORARILY_DENY,
                                kernel::REASON_REACHED_LIMIT_SECOND
                            );
                        }

                        if ($unit === 'm') {
                            $this->action(
                                kernel::ACTION_TEMPORARILY_DENY,
                                kernel::REASON_REACHED_LIMIT_MINUTE
                            );
                        }

                        if ($unit === 'h') {
                            $this->action(
                                kernel::ACTION_TEMPORARILY_DENY,
                                kernel::REASON_REACHED_LIMIT_HOUR
                            );
                        }

                        if ($unit === 'd') {
                            $this->action(
                                kernel::ACTION_TEMPORARILY_DENY,
                                kernel::REASON_REACHED_LIMIT_DAY
                            );
                        }

                        $isReject = true;
                    }
                }
            }

            foreach ($this->filterResetStatus as $unit => $status) {
                // Reset the pageview check for specfic time unit.
                if ($status) {
                    $logData['first_time_' . $unit] = $logData['last_time'];
                    $logData['pageviews_' . $unit] = 0;
                }
            }
        }

        return [
            'is_flagged' => $isFlagged,
            'is_reject' => $isReject,
            'log_data' => $logData,
        ];
    }
}
