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

use Shieldon\Firewall\Kernel\Enum;
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\get_session_instance;
use function Shieldon\Firewall\unset_superglobal;
use function time;
use function array_keys;

/*
 * This trait is used on Kernel only.
 */
trait FilterTrait
{
    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   setFilters           | Set the filters.
     *   setFilter            | Set a filter.
     *   disableFilters       | Disable all filters.
     *  ----------------------|---------------------------------------------
     */

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
     * Start an action for this IP address, allow or deny, and give a reason for it.
     *
     * @param int    $actionCode The action code. - 0: deny, 1: allow, 9: unban.
     * @param string $reasonCode The response code.
     * @param string $assignIp   The IP address.
     *
     * @return void
     */
    abstract public function action(int $actionCode, int $reasonCode, string $assignIp = ''): void;

    /**
     * Set the filters.
     *
     * @param array $settings filter settings.
     *
     * @return void
     */
    public function setFilters(array $settings)
    {
        foreach (array_keys($this->filterStatus) as $k) {
            if (isset($settings[$k])) {
                $this->filterStatus[$k] = $settings[$k] ?? false;
            }
        }
    }

    /**
     * Set a filter.
     *
     * @param string $filterName The filter's name.
     * @param bool   $value      True for enabling the filter, overwise.
     *
     * @return void
     */
    public function setFilter(string $filterName, bool $value): void
    {
        if (isset($this->filterStatus[$filterName])) {
            $this->filterStatus[$filterName] = $value;
        }
    }

    /**
     * Disable filters.
     *
     * @return void
     */
    public function disableFilters(): void
    {
        $this->setFilters(
            [
                'session'   => false,
                'cookie'    => false,
                'referer'   => false,
                'frequency' => false,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Stage in Kernel
    |--------------------------------------------------------------------------
    | The below methods are used in "process" method in Kernel.
    */

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
            $logData['session'] = get_session_instance()->getId();
            $logData['hostname'] = $this->rdns;
            $logData['last_time'] = $now;

            // Start checking...
            foreach (array_keys($this->filterStatus) as $filter) {
                // For example: filterSession
                $method = 'filter' . ucfirst($filter);

                // For example: call $this->filterSession
                $filterReturnData = $this->{$method}($logData, $ipDetail, $isFlagged);

                // The log data will be updated by the filter.
                $logData = $filterReturnData['log_data'];

                // The flag will be passed to the next Filter.
                $isFlagged = $filterReturnData['is_flagged'];

                // If we find this session reached the filter limit, reject it.
                $isReject = $filterReturnData['is_reject'];

                if ($isReject) {
                    return Enum::RESPONSE_TEMPORARILY_DENY;
                }
            }

            // Is fagged as unusual beavior? Count the first time.
            if ($isFlagged) {
                $logData['first_time_flag'] = !empty($logData['first_time_flag'])
                    ? $logData['first_time_flag']
                    : $now;
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

        return Enum::RESPONSE_ALLOW;
    }

    /*
    |--------------------------------------------------------------------------
    | The below methods are used only in "filter" method in current Trait.
    | See "Start checking..."
    |--------------------------------------------------------------------------
    */

    /**
     * When the user is first time visiting our webiste.
     * Initialize the log data.
     *
     * @param array $logData The user's log data.
     *
     * @return void
     */
    protected function InitializeFirstTimeFilter($logData): void
    {
        $now = time();

        $logData['ip']        = $this->ip;
        $logData['session']   = get_session_instance()->getId();
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
     * @param array $ipDetail  The IP log data.
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
                $logData['flag_empty_referer'] = $ipDetail['flag_empty_referer'];

                if (empty(get_request()->getHeaderLine('referer'))) {
                    $logData['flag_empty_referer']++;
                    $isFlagged = true;
                }

                // Ban this IP if they reached the limit.
                if ($logData['flag_empty_referer'] > $this->properties['limit_unusual_behavior']['referer']) {
                    $this->action(
                        Enum::ACTION_TEMPORARILY_DENY,
                        Enum::REASON_EMPTY_REFERER_DENIED
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
     * @param array $ipDetail  The IP log data.
     * @param bool  $isFlagged Is flagged as unusual behavior or not.
     *
     * @return array
     */
    protected function filterSession(array $logData, array $ipDetail, bool $isFlagged): array
    {
        $isReject = false;
        $sessionId = get_session_instance()->getId();

        if ($this->filterStatus['session']) {
            // Get values from data table. We will count it and save it back to data table.
            $logData['flag_multi_session'] = $ipDetail['flag_multi_session'];

            if ($sessionId !== $ipDetail['session']) {
                // Is is possible because of direct access by the same user many times.
                // Or they don't have session cookie set.
                $logData['flag_multi_session']++;
                $isFlagged = true;
            }

            // Ban this IP if they reached the limit.
            if ($logData['flag_multi_session'] > $this->properties['limit_unusual_behavior']['session']) {
                $this->action(
                    Enum::ACTION_TEMPORARILY_DENY,
                    Enum::REASON_TOO_MANY_SESSIONS_DENIED
                );
                $isReject = true;
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
     * @param array $ipDetail  The IP log data.
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
            $logData['flag_js_cookie'] = $ipDetail['flag_js_cookie'];
            $logData['pageviews_cookie'] = $ipDetail['pageviews_cookie'];

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
                    Enum::ACTION_TEMPORARILY_DENY,
                    Enum::REASON_EMPTY_JS_COOKIE_DENIED
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
     * @param array $ipDetail  The IP log data.
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
                        $actionReason = [
                            's' => Enum::REASON_REACH_SECONDLY_LIMIT_DENIED,
                            'm' => Enum::REASON_REACH_MINUTELY_LIMIT_DENIED,
                            'h' => Enum::REASON_REACH_HOURLY_LIMIT_DENIED,
                            'd' => Enum::REASON_REACH_DAILY_LIMIT_DENIED,
                        ];

                        $this->action(
                            Enum::ACTION_TEMPORARILY_DENY,
                            $actionReason[$unit]
                        );

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
