<?php
// phpcs:disable Generic.Files.LineLength
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

namespace Shieldon\Firewall\Panel;

use Psr\Http\Message\ResponseInterface;
use Shieldon\Firewall\Panel\BaseController;
use Shieldon\Firewall\Kernel\Enum;
use ReflectionObject;
use function Shieldon\Firewall\__;
use function Shieldon\Firewall\get_request;
use function gethostbyaddr;
use function time;

/**
 * The data circle controller.
 */
class Circle extends BaseController
{
    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   rule                 | The page for rule table.
     *   filter               | The page for filter table.
     *   session              | The page for session table.
     *  ----------------------|---------------------------------------------
     */

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Rule table for current cycle.
     *
     * @return ResponseInterface
     */
    public function rule(): ResponseInterface
    {
        $postParams = get_request()->getParsedBody();

        if ($this->checkPostParamsExist('ip', 'action')) {
            $ip = $postParams['ip'];
            $action = $postParams['action'];

            $actionCode = [];

            $actionCode['temporarily_ban'] = Enum::ACTION_TEMPORARILY_DENY;
            $actionCode['permanently_ban'] = Enum::ACTION_DENY;
            $actionCode['allow'] = Enum::ACTION_ALLOW;

            switch ($action) {
                case 'temporarily_ban':
                case 'permanently_ban':
                case 'allow':
                    $logData = [];

                    $logData['log_ip']     = $ip;
                    $logData['ip_resolve'] = gethostbyaddr($ip);
                    $logData['time']       = time();
                    $logData['type']       = $actionCode[$action];
                    $logData['reason']     = Enum::REASON_MANUAL_BAN_DENIED;

                    $this->kernel->driver->save($ip, $logData, 'rule');
                    break;

                case 'remove':
                    $this->kernel->driver->delete($ip, 'rule');
                    break;
            }
        }

        $reasons = [
            Enum::REASON_MANUAL_BAN_DENIED              => __('panel', 'reason_manual_ban', 'Manually added by administrator'),
            Enum::REASON_IS_SEARCH_ENGINE_ALLOWED       => __('panel', 'reason_is_search_engine', 'Search engine bot'),
            Enum::REASON_IS_GOOGLE_ALLOWED              => __('panel', 'reason_is_google', 'Google bot'),
            Enum::REASON_IS_BING_ALLOWED                => __('panel', 'reason_is_bing', 'Bing bot'),
            Enum::REASON_IS_YAHOO_ALLOWED               => __('panel', 'reason_is_yahoo', 'Yahoo bot'),
            Enum::REASON_TOO_MANY_SESSIONS_DENIED       => __('panel', 'reason_too_many_sessions', 'Too many sessions'),
            Enum::REASON_TOO_MANY_ACCESSE_DENIED        => __('panel', 'reason_too_many_accesses', 'Too many accesses'),
            Enum::REASON_EMPTY_JS_COOKIE_DENIED         => __('panel', 'reason_empty_js_cookie', 'Unable to create JS cookies'),
            Enum::REASON_EMPTY_REFERER_DENIED           => __('panel', 'reason_empty_referer', 'Empty referrer'),
            Enum::REASON_REACH_DAILY_LIMIT_DENIED       => __('panel', 'reason_reached_limit_day', 'Daily limit reached'),
            Enum::REASON_REACH_HOURLY_LIMIT_DENIED      => __('panel', 'reason_reached_limit_hour', 'Hourly limit reached'),
            Enum::REASON_REACH_MINUTELY_LIMIT_DENIED    => __('panel', 'reason_reached_limit_minute', 'Minute limit reached'),
            Enum::REASON_REACH_SECONDLY_LIMIT_DENIED    => __('panel', 'reason_reached_limit_second', 'Second limit reached'),
            Enum::REASON_INVALID_IP_DENIED              => __('panel', 'reason_invalid_ip', 'Invalid IP address.'),
            Enum::REASON_DENY_IP_DENIED                 => __('panel', 'reason_deny_ip', 'Denied by IP component.'),
            Enum::REASON_ALLOW_IP_DENIED                => __('panel', 'reason_allow_ip', 'Allowed by IP component.'),
            Enum::REASON_COMPONENT_IP_DENIED            => __('panel', 'reason_component_ip', 'Denied by IP component.'),
            Enum::REASON_COMPONENT_RDNS_DENIED          => __('panel', 'reason_component_rdns', 'Denied by RDNS component.'),
            Enum::REASON_COMPONENT_HEADER_DENIED        => __('panel', 'reason_component_header', 'Denied by Header component.'),
            Enum::REASON_COMPONENT_USERAGENT_DENIED     => __('panel', 'reason_component_useragent', 'Denied by User Agent component.'),
            Enum::REASON_COMPONENT_TRUSTED_ROBOT_DENIED => __('panel', 'reason_component_trusted_robot', 'Identified as a fake search engine.'),
        ];

        $types = [
            Enum::ACTION_DENY             => 'DENY',
            Enum::ACTION_ALLOW            => 'ALLOW',
            Enum::ACTION_TEMPORARILY_DENY => 'CAPTCHA',
        ];

        $data = [];

        $data['rule_list'] = $this->kernel->driver->getAll('rule');

        $data['reason_mapping'] = $reasons;
        $data['type_mapping'] = $types;

        $data['title'] = __('panel', 'menu_data_circle', 'Data Circle') . ' - ';
        $data['title'] .= __('panel', 'title_circle_rule', 'Rule Table');

        return $this->renderPage('panel/table_rules', $data);
    }

    /**
     * IP filter table for current cycle.
     *
     * @return ResponseInterface
     */
    public function filter(): ResponseInterface
    {
        $data = [];

        $data['ip_log_list'] = $this->kernel->driver->getAll('filter');

        $data['title'] = __('panel', 'menu_data_circle', 'Data Circle') . ' - ';
        $data['title'] .= __('panel', 'title_circle_filter', 'Filter Table');

        return $this->renderPage('panel/table_filter_logs', $data);
    }

    /**
     * Session table for current cycle.
     *
     * @return ResponseInterface
     */
    public function session(): ResponseInterface
    {
        $data = [];

        $data['session_list'] = $this->kernel->driver->getAll('session');

        $data['is_session_limit']     = false;
        $data['session_limit_count']  = 0;
        $data['session_limit_period'] = 0;
        $data['online_count']         = 0;
        $data['expires']              = 0;

        $reflection = new ReflectionObject($this->kernel);
        $t = $reflection->getProperty('sessionLimit');
        $t->setAccessible(true);
        $sessionLimit = $t->getValue($this->kernel);

        $isLimitSession = false;
        $limitCount = 0;
        $limitPeriod = 0;

        if (!empty($sessionLimit['count'])) {
            $isLimitSession = true;
            $limitCount = $sessionLimit['count'];
            $limitPeriod = $sessionLimit['period'];
        }

        $data['is_session_limit']     = $isLimitSession;
        $data['session_limit_count']  = $limitCount;
        $data['session_limit_period'] = $limitPeriod;
        $data['online_count']         = count($data['session_list']);
        $data['expires']              = (int) $data['session_limit_period'] * 60;

        $data['title'] = __('panel', 'menu_data_circle', 'Data Circle') . ' - ';
        $data['title'] .= __('panel', 'title_circle_session', 'Session Table');

        return $this->renderPage('panel/table_sessions', $data);
    }
}
