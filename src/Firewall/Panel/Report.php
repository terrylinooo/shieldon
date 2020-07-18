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

namespace Shieldon\Firewall\Panel;

use Psr\Http\Message\ResponseInterface;
use Shieldon\Firewall\Panel\BaseController;
use Shieldon\Firewall\Log\ActionLogParsedCache;
use function Shieldon\Firewall\__;
use function Shieldon\Firewall\get_request;

use ReflectionObject;
use function date;

/**
 * The report controller.
 */
class Report extends BaseController
{
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
            $type = 'today';
        }

        $data['ip_details'] = [];
        $data['period_data'] = [];
        
        $lastCachedTime = '';

        if (!empty($this->parser)) {

            $logCacheHandler = new ActionLogParsedCache($this->parser->getDirectory());

            $ipDetailsCachedData = $logCacheHandler->get($type);

            // If we have cached data then we don't need to parse them again.
            // This will save a lot of time in parsing logs.
            if (!empty($ipDetailsCachedData)) {

                $data['ip_details'] = $ipDetailsCachedData['ip_details'];
                $data['period_data'] = $ipDetailsCachedData['period_data'];
                $lastCachedTime = date('Y-m-d H:i:s', $ipDetailsCachedData['time']);
    
                if ('today' === $type ) {
                    $ipDetailsCachedData = $logCacheHandler->get('past_seven_hours');
                    $data['past_seven_hours'] = $ipDetailsCachedData['period_data'];
                }

            } else {

                $this->parser->prepare($type);

                $data['ip_details'] = $this->parser->getIpData();
                $data['period_data'] = $this->parser->getParsedPeriodData();

                $logCacheHandler->save($type, $data);
    
                if ('today' === $type ) {
                    $this->parser->prepare('past_seven_hours');
                    $data['past_seven_hours'] = $this->parser->getParsedPeriodData();

                    $logCacheHandler->save('past_seven_hours', [
                        'period_data' => $data['past_seven_hours']
                    ]);
                }
            }
        }

        $data['page_availability'] = $this->pageAvailability['logs'];
        $data['last_cached_time'] = $lastCachedTime;

        $data['page_url'] = $this->url('report/actionLog');

        $data['title'] = __('panel', 'title_action_logs', 'Action Logs');

        return $this->renderPage('panel/action_log_' . $type, $data);
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

        $data['component_ip'] = 0;
        $data['component_trustedbot'] = 0;
        $data['component_rdns'] = 0;
        $data['component_header'] = 0;
        $data['component_useragent'] = 0;

        $data['filter_frequency'] = 0;
        $data['filter_referer'] = 0;
        $data['filter_cookie'] = 0;
        $data['filter_session'] = 0;

        // Components.
        $data['rule_list']['ip'] = [];
        $data['rule_list']['trustedbot'] = [];
        $data['rule_list']['rdns'] = [];
        $data['rule_list']['header'] = [];
        $data['rule_list']['useragent'] = [];

        // Filters.
        $data['rule_list']['frequency'] = [];
        $data['rule_list']['referer'] = [];
        $data['rule_list']['cookie'] = [];
        $data['rule_list']['session'] = [];

        foreach ($ruleList as $ruleInfo) {
    
            switch ($ruleInfo['reason']) {
                case $this->kernel::REASON_DENY_IP:
                case $this->kernel::REASON_COMPONENT_IP:
                    $data['component_ip']++;
                    $data['rule_list']['ip'][] = $ruleInfo;
                    break;

                case $this->kernel::REASON_COMPONENT_RDNS:
                    $data['component_rdns']++;
                    $data['rule_list']['rdns'][] = $ruleInfo;
                    break;
                
                case $this->kernel::REASON_COMPONENT_HEADER:
                    $data['component_header']++;
                    $data['rule_list']['header'][] = $ruleInfo;
                    break;

                case $this->kernel::REASON_COMPONENT_USERAGENT:
                    $data['component_useragent']++;
                    $data['rule_list']['useragent'][] = $ruleInfo;
                    break;

                case $this->kernel::REASON_COMPONENT_TRUSTED_ROBOT:
                    $data['component_trustedbot']++;
                    $data['rule_list']['trustedbot'][] = $ruleInfo;
                    break;

                case $this->kernel::REASON_TOO_MANY_ACCESSES:
                case $this->kernel::REASON_REACHED_LIMIT_DAY:
                case $this->kernel::REASON_REACHED_LIMIT_HOUR:
                case $this->kernel::REASON_REACHED_LIMIT_MINUTE:
                case $this->kernel::REASON_REACHED_LIMIT_SECOND:
                    $data['filter_frequency']++;
                    $data['rule_list']['frequency'][] = $ruleInfo;
                    break;

                case $this->kernel::REASON_EMPTY_REFERER:
                    $data['filter_referer']++;
                    $data['rule_list']['referer'][] = $ruleInfo;
                    break;

                case $this->kernel::REASON_EMPTY_JS_COOKIE:
                    $data['filter_cookie']++;
                    $data['rule_list']['cookie'][] = $ruleInfo;
                    break;

                case $this->kernel::REASON_TOO_MANY_SESSIONS:
                    $data['filter_session']++;
                    $data['rule_list']['session'][] = $ruleInfo;
                    break;
            }
        }

        return $data;
    }
}

