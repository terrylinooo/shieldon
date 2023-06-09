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
use Shieldon\Firewall\Log\ActionLogParsedCache;
use ReflectionObject;
use function Shieldon\Firewall\__;
use function Shieldon\Firewall\get_request;
use function array_merge;
use function date;

/**
 * The report controller.
 */
class Report extends BaseController
{
    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   operation            | The page for operating status.
     *   actionLog            | The page for displaying action logs.
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
     * Operation status.
     *
     * @return ResponseInterface
     */
    public function operation(): ResponseInterface
    {
        $data = [];

        $data = $this->operationTemplateVarsOfComponents($data);
        $data = $this->operationTemplateVarsOfFilters($data);
        $data = $this->operationTemplateVarsOfStatistics($data);

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

        $data['reason_mapping'] = $reasons;
        $data['type_mapping'] = $types;

        $data['title'] = __('panel', 'title_operation_status', 'Operation Status');

        return $this->renderPage('panel/operation_status', $data);
    }

    /**
     * Action logs
     *
     * @return ResponseInterface
     */
    public function actionLog(): ResponseInterface
    {
        $getParams = get_request()->getQueryParams();

        $type = $getParams['tab'] ?? 'today';

        $validTabs = [
            'yesterday',
            'this_month',
            'last_month',
            'past_seven_days',
            'today',
        ];

        if (!in_array($type, $validTabs)) {
            // @codeCoverageIgnoreStart
            $type = 'today';
            // @codeCoverageIgnoreEnd
        }

        $data = [];
        $data['last_cached_time'] = '';

        if (!empty($this->parser)) {
            $result = $this->fetchActionLogsData($type);
            $data = array_merge($data, $result);
        }

        $data['page_availability'] = $this->pageAvailability['logs'];
        $data['page_url'] = $this->url('report/actionLog');
        $data['title'] = __('panel', 'title_action_logs', 'Action Logs');

        return $this->renderPage('panel/action_log_' . $type, $data);
    }

    /**
     * Fetch the log data.
     *
     * @param string $type The date type.
     *
     * @return array
     */
    private function fetchActionLogsData($type = 'today'): array
    {
        $data = [];

        $logCacheHandler = new ActionLogParsedCache($this->parser->getDirectory());

        $ipDetailsCachedData = $logCacheHandler->get($type);

        // If we have cached data then we don't need to parse them again.
        // This will save a lot of time in parsing logs.
        if (!empty($ipDetailsCachedData)) {
            $data['ip_details'] = $ipDetailsCachedData['ip_details'];
            $data['period_data'] = $ipDetailsCachedData['period_data'];
            $data['last_cached_time'] = date('Y-m-d H:i:s', $ipDetailsCachedData['time']);

            if ('today' === $type) {
                $ipDetailsCachedData = $logCacheHandler->get('past_seven_hours');
                $data['past_seven_hours'] = $ipDetailsCachedData['period_data'];
            }
        } else {
            $this->parser->prepare($type);

            $data['ip_details'] = $this->parser->getIpData();
            $data['period_data'] = $this->parser->getParsedPeriodData();

            $logCacheHandler->save($type, $data);

            if ('today' === $type) {
                $this->parser->prepare('past_seven_hours');
                $data['past_seven_hours'] = $this->parser->getParsedPeriodData();

                $logCacheHandler->save(
                    'past_seven_hours',
                    [
                        'period_data' => $data['past_seven_hours'],
                    ]
                );
            }
        }

        return $data;
    }

    /**
     * Template variables of the section Components in page Operation.
     *
     * @param array $data The template varibles.
     *
     * @return array
     */
    private function operationTemplateVarsOfComponents(array $data = []): array
    {
        $data['components'] = [
            'Ip'         => !empty($this->kernel->component['Ip']),
            'TrustedBot' => !empty($this->kernel->component['TrustedBot']),
            'Header'     => !empty($this->kernel->component['Header']),
            'Rdns'       => !empty($this->kernel->component['Rdns']),
            'UserAgent'  => !empty($this->kernel->component['UserAgent']),
        ];

        return $data;
    }

    /**
     * Template variables of the section Filters in the page Operation.
     *
     * @param array $data The template varibles.
     *
     * @return array
     */
    private function operationTemplateVarsOfFilters(array $data = []): array
    {
        $reflection = new ReflectionObject($this->kernel);
        $t = $reflection->getProperty('filterStatus');
        $t->setAccessible(true);
        $filterStatus = $t->getValue($this->kernel);

        $data['filters'] = $filterStatus;

        return $data;
    }

    /**
     * Template variables of the counters for statistics in the page Operation.
     *
     * @param array $data The template varibles.
     *
     * @return array
     */
    private function operationTemplateVarsOfStatistics(array $data = []): array
    {
        $ruleList = $this->kernel->driver->getAll('rule');

        $counter = $this->getCounterDefault();
        $info = $this->getInfoDefault();

        // @codeCoverageIgnoreStart
        foreach ($ruleList as $ruleInfo) {
            $reason = $ruleInfo['reason'];

            $counter[$reason]++;
            $info[$reason][] = $ruleInfo;
        }
        // @codeCoverageIgnoreEnd

        $data = $this->getComponentsData($data, $counter, $info);
        $data = $this->getFiltersData($data, $counter, $info);

        return $data;
    }

    /**
     * Get filters' data.
     *
     * @param array $data    The data array.
     * @param array $counter The counter array.
     * @param array $info    The into array.
     *
     * @return array
     */
    private function getFiltersData(array $data, array $counter, array $info): array
    {
        $filters = ['cookie', 'referer', 'session', 'frequency'];

        foreach ($filters as $v) {
            $data["filter_$v"] = 0;
            $data['rule_list'][$v] = [];
        }

        $a = $counter[Enum::REASON_TOO_MANY_ACCESSE_DENIED];
        $b = $counter[Enum::REASON_REACH_DAILY_LIMIT_DENIED];
        $c = $counter[Enum::REASON_REACH_HOURLY_LIMIT_DENIED];
        $d = $counter[Enum::REASON_REACH_MINUTELY_LIMIT_DENIED];
        $e = $counter[Enum::REASON_REACH_SECONDLY_LIMIT_DENIED];
        $f = $info[Enum::REASON_DENY_IP_DENIED];
        $g = $info[Enum::REASON_REACH_DAILY_LIMIT_DENIED];
        $h = $info[Enum::REASON_REACH_HOURLY_LIMIT_DENIED];
        $i = $info[Enum::REASON_REACH_MINUTELY_LIMIT_DENIED];
        $j = $info[Enum::REASON_REACH_SECONDLY_LIMIT_DENIED];
        $data['filter_frequency'] = $a + $b + $c + $d + $e;
        $data['rule_list']['frequency'] = array_merge_recursive($f, $g, $h, $i, $j);

        $a = $counter[Enum::REASON_EMPTY_REFERER_DENIED];
        $b = $info[Enum::REASON_EMPTY_REFERER_DENIED];
        $data['filter_referer'] = $a;
        $data['rule_list']['referer'] = $b;

        $a = $counter[Enum::REASON_EMPTY_JS_COOKIE_DENIED];
        $b = $info[Enum::REASON_EMPTY_JS_COOKIE_DENIED];
        $data['filter_cookie'] = $a;
        $data['rule_list']['cookie'] = $b;

        $a = $counter[Enum::REASON_TOO_MANY_SESSIONS_DENIED];
        $b = $info[Enum::REASON_TOO_MANY_SESSIONS_DENIED];
        $data['filter_session'] = $a;
        $data['rule_list']['session'] = $b;

        return $data;
    }

    /**
     * Get components' data.
     *
     * @param array $data    The data array.
     * @param array $counter The counter array.
     * @param array $info    The into array.
     *
     * @return array
     */
    private function getComponentsData(array $data, array $counter, array $info): array
    {
        $components = ['ip', 'rdns', 'header', 'useragent', 'trustedbot'];

        foreach ($components as $v) {
            $data["component_$v"] = 0;
            $data['rule_list'][$v] = [];
        }

        $a = $counter[Enum::REASON_DENY_IP_DENIED];
        $b = $counter[Enum::REASON_COMPONENT_IP_DENIED];
        $c = $info[Enum::REASON_DENY_IP_DENIED];
        $d = $info[Enum::REASON_COMPONENT_IP_DENIED];
        $data['component_ip'] = $a + $b;
        $data['rule_list']['ip'] = array_merge_recursive($c, $d);

        $a = $counter[Enum::REASON_COMPONENT_RDNS_DENIED];
        $b = $info[Enum::REASON_COMPONENT_RDNS_DENIED];
        $data['component_rdns'] = $a;
        $data['rule_list']['rdns'] = $b;

        $a = $counter[Enum::REASON_COMPONENT_HEADER_DENIED];
        $b = $info[Enum::REASON_COMPONENT_HEADER_DENIED];
        $data['component_header'] = $a;
        $data['rule_list']['header'] = $b;

        $a = $counter[Enum::REASON_COMPONENT_USERAGENT_DENIED];
        $b = $info[Enum::REASON_COMPONENT_USERAGENT_DENIED];
        $data['component_useragent'] = $a;
        $data['rule_list']['useragent'] = $b;

        $a = $counter[Enum::REASON_COMPONENT_TRUSTED_ROBOT_DENIED];
        $b = $info[Enum::REASON_COMPONENT_TRUSTED_ROBOT_DENIED];
        $data['component_trustedbot'] = $a;
        $data['rule_list']['trustedbot'] = $b;

        return $data;
    }

    /**
     * Get counter default.
     *
     * @return array
     */
    private function getCounterDefault(): array
    {
        $counter = [];

        $counter[Enum::REASON_DENY_IP_DENIED]                 = 0;
        $counter[Enum::REASON_COMPONENT_IP_DENIED]            = 0;
        $counter[Enum::REASON_COMPONENT_RDNS_DENIED]          = 0;
        $counter[Enum::REASON_COMPONENT_HEADER_DENIED]        = 0;
        $counter[Enum::REASON_COMPONENT_USERAGENT_DENIED]     = 0;
        $counter[Enum::REASON_COMPONENT_TRUSTED_ROBOT_DENIED] = 0;
        $counter[Enum::REASON_TOO_MANY_ACCESSE_DENIED]       = 0;
        $counter[Enum::REASON_REACH_DAILY_LIMIT_DENIED]       = 0;
        $counter[Enum::REASON_REACH_HOURLY_LIMIT_DENIED]      = 0;
        $counter[Enum::REASON_REACH_MINUTELY_LIMIT_DENIED]    = 0;
        $counter[Enum::REASON_REACH_SECONDLY_LIMIT_DENIED]    = 0;
        $counter[Enum::REASON_EMPTY_REFERER_DENIED]           = 0;
        $counter[Enum::REASON_EMPTY_JS_COOKIE_DENIED]         = 0;
        $counter[Enum::REASON_TOO_MANY_SESSIONS_DENIED]       = 0;

        return $counter;
    }

    /**
     * Get info default.
     *
     * @return array
     */
    private function getInfoDefault(): array
    {
        $info = [];

        $info[Enum::REASON_DENY_IP_DENIED]                 = [];
        $info[Enum::REASON_COMPONENT_IP_DENIED]            = [];
        $info[Enum::REASON_COMPONENT_RDNS_DENIED]          = [];
        $info[Enum::REASON_COMPONENT_HEADER_DENIED]        = [];
        $info[Enum::REASON_COMPONENT_USERAGENT_DENIED]     = [];
        $info[Enum::REASON_COMPONENT_TRUSTED_ROBOT_DENIED] = [];
        $info[Enum::REASON_DENY_IP_DENIED]                 = [];
        $info[Enum::REASON_REACH_DAILY_LIMIT_DENIED]       = [];
        $info[Enum::REASON_REACH_HOURLY_LIMIT_DENIED]      = [];
        $info[Enum::REASON_REACH_MINUTELY_LIMIT_DENIED]    = [];
        $info[Enum::REASON_REACH_SECONDLY_LIMIT_DENIED]    = [];
        $info[Enum::REASON_EMPTY_REFERER_DENIED]           = [];
        $info[Enum::REASON_EMPTY_JS_COOKIE_DENIED]         = [];
        $info[Enum::REASON_TOO_MANY_SESSIONS_DENIED]       = [];

        return $info;
    }
}
