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

namespace Shieldon\Firewall\Panel;

use Psr\Http\Message\ResponseInterface;
use Shieldon\Firewall\Panel\BaseController;
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
            $this->kernel::REASON_MANUAL_BAN              => __('panel', 'reason_manual_ban', 'Added manually by administrator'),
            $this->kernel::REASON_IS_SEARCH_ENGINE        => __('panel', 'reason_is_search_engine', 'Search engine bot'),
            $this->kernel::REASON_IS_GOOGLE               => __('panel', 'reason_is_google', 'Google bot'),
            $this->kernel::REASON_IS_BING                 => __('panel', 'reason_is_bing', 'Bing bot'),
            $this->kernel::REASON_IS_YAHOO                => __('panel', 'reason_is_yahoo', 'Yahoo bot'),
            $this->kernel::REASON_TOO_MANY_SESSIONS       => __('panel', 'reason_too_many_sessions', 'Too many sessions'),
            $this->kernel::REASON_TOO_MANY_ACCESSES       => __('panel', 'reason_too_many_accesses', 'Too many accesses'),
            $this->kernel::REASON_EMPTY_JS_COOKIE         => __('panel', 'reason_empty_js_cookie', 'Cannot create JS cookies'),
            $this->kernel::REASON_EMPTY_REFERER           => __('panel', 'reason_empty_referer', 'Empty referrer'),
            $this->kernel::REASON_REACHED_LIMIT_DAY       => __('panel', 'reason_reached_limit_day', 'Daily limit reached'),
            $this->kernel::REASON_REACHED_LIMIT_HOUR      => __('panel', 'reason_reached_limit_hour', 'Hourly limit reached'),
            $this->kernel::REASON_REACHED_LIMIT_MINUTE    => __('panel', 'reason_reached_limit_minute', 'Minutely limit reached'),
            $this->kernel::REASON_REACHED_LIMIT_SECOND    => __('panel', 'reason_reached_limit_second', 'Secondly limit reached'),
            $this->kernel::REASON_INVALID_IP              => __('panel', 'reason_invalid_ip', 'Invalid IP address.'),
            $this->kernel::REASON_DENY_IP                 => __('panel', 'reason_deny_ip', 'Denied by IP component.'),
            $this->kernel::REASON_ALLOW_IP                => __('panel', 'reason_allow_ip', 'Allowed by IP component.'),
            $this->kernel::REASON_COMPONENT_IP            => __('panel', 'reason_component_ip', 'Denied by IP component.'),
            $this->kernel::REASON_COMPONENT_RDNS          => __('panel', 'reason_component_rdns', 'Denied by RDNS component.'),
            $this->kernel::REASON_COMPONENT_HEADER        => __('panel', 'reason_component_header', 'Denied by Header component.'),
            $this->kernel::REASON_COMPONENT_USERAGENT     => __('panel', 'reason_component_useragent', 'Denied by User-agent component.'),
            $this->kernel::REASON_COMPONENT_TRUSTED_ROBOT => __('panel', 'reason_component_trusted_robot', 'Identified as fake search engine.'),
        ];

        $types = [
            $this->kernel::ACTION_DENY             => 'DENY',
            $this->kernel::ACTION_ALLOW            => 'ALLOW',
            $this->kernel::ACTION_TEMPORARILY_DENY => 'CAPTCHA',
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

        $data['page_availability'] = $this->pageAvailability['logs'];;

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
                        'period_data' => $data['past_seven_hours']
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
            'Ip'         => (!empty($this->kernel->component['Ip']))         ? true : false,
            'TrustedBot' => (!empty($this->kernel->component['TrustedBot'])) ? true : false,
            'Header'     => (!empty($this->kernel->component['Header']))     ? true : false,
            'Rdns'       => (!empty($this->kernel->component['Rdns']))       ? true : false,
            'UserAgent'  => (!empty($this->kernel->component['UserAgent']))  ? true : false,
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

        $a = $counter[$this->kernel::REASON_TOO_MANY_ACCESSES];
        $b = $counter[$this->kernel::REASON_REACHED_LIMIT_DAY];
        $c = $counter[$this->kernel::REASON_REACHED_LIMIT_HOUR];
        $d = $counter[$this->kernel::REASON_REACHED_LIMIT_MINUTE];
        $e = $counter[$this->kernel::REASON_REACHED_LIMIT_SECOND];
        $f = $info[$this->kernel::REASON_DENY_IP];
        $g = $info[$this->kernel::REASON_REACHED_LIMIT_DAY];
        $h = $info[$this->kernel::REASON_REACHED_LIMIT_HOUR];
        $i = $info[$this->kernel::REASON_REACHED_LIMIT_MINUTE];
        $j = $info[$this->kernel::REASON_REACHED_LIMIT_SECOND];
        $data['filter_frequency'] = $a + $b + $c + $d + $e;
        $data['rule_list']['frequency'] = array_merge_recursive($f, $g, $h, $i, $j);

        $a = $counter[$this->kernel::REASON_EMPTY_REFERER];
        $b = $info[$this->kernel::REASON_EMPTY_REFERER];
        $data['filter_referer'] = $a;
        $data['rule_list']['referer'] = $b;

        $a = $counter[$this->kernel::REASON_EMPTY_JS_COOKIE];
        $b = $info[$this->kernel::REASON_EMPTY_JS_COOKIE];
        $data['filter_cookie'] = $a;
        $data['rule_list']['cookie'] = $b;

        $a = $counter[$this->kernel::REASON_TOO_MANY_SESSIONS];
        $b = $info[$this->kernel::REASON_TOO_MANY_SESSIONS];
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

        $a = $counter[$this->kernel::REASON_DENY_IP];
        $b = $counter[$this->kernel::REASON_COMPONENT_IP];
        $c = $info[$this->kernel::REASON_DENY_IP];
        $d = $info[$this->kernel::REASON_COMPONENT_IP];
        $data['component_ip'] = $a + $b;
        $data['rule_list']['ip'] = array_merge_recursive($c, $d);

        $a = $counter[$this->kernel::REASON_COMPONENT_RDNS];
        $b = $info[$this->kernel::REASON_COMPONENT_RDNS];
        $data['component_rdns'] = $a;
        $data['rule_list']['rdns'] = $b;

        $a = $counter[$this->kernel::REASON_COMPONENT_HEADER];
        $b = $info[$this->kernel::REASON_COMPONENT_HEADER];
        $data['component_header'] = $a;
        $data['rule_list']['header'] = $b;

        $a = $counter[$this->kernel::REASON_COMPONENT_USERAGENT];
        $b = $info[$this->kernel::REASON_COMPONENT_USERAGENT];
        $data['component_useragent'] = $a;
        $data['rule_list']['useragent'] = $b;

        $a = $counter[$this->kernel::REASON_COMPONENT_TRUSTED_ROBOT];
        $b = $info[$this->kernel::REASON_COMPONENT_TRUSTED_ROBOT];
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

        $counter[$this->kernel::REASON_DENY_IP]                 = 0;
        $counter[$this->kernel::REASON_COMPONENT_IP]            = 0;
        $counter[$this->kernel::REASON_COMPONENT_RDNS]          = 0;
        $counter[$this->kernel::REASON_COMPONENT_HEADER]        = 0;
        $counter[$this->kernel::REASON_COMPONENT_USERAGENT]     = 0;
        $counter[$this->kernel::REASON_COMPONENT_TRUSTED_ROBOT] = 0;
        $counter[$this->kernel::REASON_TOO_MANY_ACCESSES]       = 0;
        $counter[$this->kernel::REASON_REACHED_LIMIT_DAY]       = 0;
        $counter[$this->kernel::REASON_REACHED_LIMIT_HOUR]      = 0;
        $counter[$this->kernel::REASON_REACHED_LIMIT_MINUTE]    = 0;
        $counter[$this->kernel::REASON_REACHED_LIMIT_SECOND]    = 0;
        $counter[$this->kernel::REASON_EMPTY_REFERER]           = 0;
        $counter[$this->kernel::REASON_EMPTY_JS_COOKIE]         = 0;
        $counter[$this->kernel::REASON_TOO_MANY_SESSIONS]       = 0;

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

        $info[$this->kernel::REASON_DENY_IP]                 = [];
        $info[$this->kernel::REASON_COMPONENT_IP]            = [];
        $info[$this->kernel::REASON_COMPONENT_RDNS]          = [];
        $info[$this->kernel::REASON_COMPONENT_HEADER]        = [];
        $info[$this->kernel::REASON_COMPONENT_USERAGENT]     = [];
        $info[$this->kernel::REASON_COMPONENT_TRUSTED_ROBOT] = [];
        $info[$this->kernel::REASON_DENY_IP]                 = [];
        $info[$this->kernel::REASON_REACHED_LIMIT_DAY]       = [];
        $info[$this->kernel::REASON_REACHED_LIMIT_HOUR]      = [];
        $info[$this->kernel::REASON_REACHED_LIMIT_MINUTE]    = [];
        $info[$this->kernel::REASON_REACHED_LIMIT_SECOND]    = [];
        $info[$this->kernel::REASON_EMPTY_REFERER]           = [];
        $info[$this->kernel::REASON_EMPTY_JS_COOKIE]         = [];
        $info[$this->kernel::REASON_TOO_MANY_SESSIONS]       = [];

        return $info;
    }
}
