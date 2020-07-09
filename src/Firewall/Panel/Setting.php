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
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\unset_superglobal;
use function Shieldon\Firewall\__;

/**
 * Home
 */
class Setting extends BaseController
{
    /**
     * Constructor
     */
    public function __construct() 
    {
        parent::__construct();
    }

    /**
     * Set up basic settings.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function basic(): ResponseInterface
    {
        $data[] = [];

        $postParams = get_request()->getParsedBody();

        if (isset($postParams['tab'])) {
            unset_superglobal('tab', 'post');
            $this->saveConfig();
        }

        return $this->renderPage('panel/setting', $data);
    }

    /**
     * Set up basic settings.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function messenger(): ResponseInterface
    {
        $data[] = [];

        $postParams = get_request()->getParsedBody();

        $data['ajaxUrl'] = $this->url('ajax/tryMessenger');

        if (isset($postParams['tab'])) {
            unset_superglobal('tab', 'post');
            $this->saveConfig();
        }

        return $this->renderPage('panel/messenger', $data);
    }

    /**
     * IP manager.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function ipManager(): ResponseInterface
    {
        $postParams = get_request()->getParsedBody();

        if (
            isset($postParams['ip']) &&
            filter_var(explode('/', $postParams['ip'])[0], FILTER_VALIDATE_IP)
        ) {

            $url = $postParams['url'];
            $ip = $postParams['ip'];
            $rule = $postParams['action'];
            $order = (int) $postParams['order'];

            if ($order > 0) {
                $order--;
            }

            $ipList = $this->getConfig('ip_manager');

            if ('allow' === $rule || 'deny' === $rule) {

                $newIpList = [];

                if (!empty($ipList)) {
                    foreach ($ipList as $i => $ipInfo) {
                        $key = $i + 1;
                        if ($order === $i) {
                            $newIpList[$key] = $ipInfo;

                            $newIpList[$i]['url'] = $url;
                            $newIpList[$i]['ip'] = $ip;
                            $newIpList[$i]['rule'] = $rule;
                        } else {
                            $newIpList[$key] = $ipInfo;
                        }
                    }
                } else {
                    $newIpList[0]['url'] = $url;
                    $newIpList[0]['ip'] = $ip;
                    $newIpList[0]['rule'] = $rule;
                }

                $newIpList = array_values($newIpList);

                $this->setConfig('ip_manager', $newIpList);

            } elseif ('remove' === $rule) {
                unset($ipList[$order]);
                $ipList = array_values($ipList);
                $this->setConfig('ip_manager', $ipList);
            }

            unset_superglobal('url', 'post');
            unset_superglobal('ip', 'post');
            unset_superglobal('action', 'post');
            unset_superglobal('order', 'post');

            $this->saveConfig();
        }

        $data['ip_list'] = $this->getConfig('ip_manager');

        return $this->renderPage('panel/ip_manager', $data);
    }

    /**
     * Exclude the URLs that they don't need protection.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function exclusion(): ResponseInterface
    {
        $postParams = get_request()->getParsedBody();

        if (isset($postParams['url'])) {

            $url = $postParams['url'] ?? '';
            $action = $postParams['action'] ?? '';
            $order = (int) $postParams['order'];

            $excludedUrls = $this->getConfig('excluded_urls');

            if ('add' === $action) {
                array_push($excludedUrls, [
                    'url' => $url
                ]);

            } elseif ('remove' === $action) {
                unset($excludedUrls[$order]);

                $excludedUrls = array_values($excludedUrls);
            }

            $this->setConfig('excluded_urls', $excludedUrls);

            unset_superglobal('url', 'post');
            unset_superglobal('action', 'post');
            unset_superglobal('order', 'post');

            $this->saveConfig();
        }

        $data['exclusion_list'] = $this->getConfig('excluded_urls');

        return $this->renderPage('panel/exclusion', $data);
    }

    /**
     * Export settings.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function export(): ResponseInterface
    {
        $response = get_response();

        $stream = $response->getBody();
        $stream->write(json_encode($this->configuration));
        $stream->rewind();

        $response = $response->withdHeader('Content-Type', 'text/plain');
        $response = $response->withdHeader('Content-Disposition', 'attachment');
        $response = $response->withdAddedHeader('Content-Disposition', 'filename=shieldon-' . date('YmdHis') . '.json');
        $response = $response->withdHeader('Expires', '0');
        $response = $response->withdHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        $response = $response->withdHeader('Pragma', 'public');
        $response = $response->withBody($stream);

        return $response;
    }

    /**
     * Import settings.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function import(): ResponseInterface
    {
        $request = get_request();
        $response = get_response();

        $importedFileContent = $request->
            getUploadedFiles('json_file')->
            getStream()->
            getContents();

        if (!empty($importedFileContent)) {
            $jsonData = json_decode($importedFileContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->pushMessage('error',
                    __(
                        'panel',
                        'error_invalid_json_file',
                        'Invalid JSON file.'
                    )
                );
                get_session()->set('flash_messages', $this->messages);

                // Return failed result message.
                return $response->withHeader('Location', $this->url('setting/basic'));
            }

            $checkFileVaild = true;

            foreach (array_keys($this->configuration) as $key) {
                if (!isset($jsonData[$key])) {
                    $checkFileVaild = false;
                }
            }

            if ($checkFileVaild) {
                foreach (array_keys($jsonData) as $key) {
                    if (isset($this->configuration[$key])) {
                        unset($this->configuration[$key]);
                    }
                }

                $this->configuration = $this->configuration + $jsonData;

                // Save settings into a configuration file.
                $configFilePath = $this->directory . '/' . $this->filename;
                file_put_contents($configFilePath, json_encode($this->configuration));

                $this->pushMessage('success',
                    __(
                        'panel',
                        'success_json_imported',
                        'JSON file imported successfully.'
                    )
                );

                get_session()->set('flash_messages', $this->messages);

                // Return succesfull result message.
                return $response->withHeader('Location', $this->url('setting/basic'));
            }
        }

        $this->pushMessage('error',
            __(
                'panel',
                'error_invalid_config_file',
                'Invalid Shieldon configuration file.'
            )
        );

        get_session()->set('flash_messages', $this->messages);

        return $response->withHeader('Location', $this->url('setting/basic'));
    }
}

