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
use function Shieldon\Firewall\__;
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\get_response;
use function Shieldon\Firewall\get_session_instance;
use function Shieldon\Firewall\unset_superglobal;
use function array_keys;
use function array_values;
use function explode;
use function filter_var;
use function json_decode;
use function json_last_error;
use const JSON_PRETTY_PRINT;

/**
 * Home
 */
class Setting extends BaseController
{
    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   basic                | The page for managing page authentication.
     *   ipManager            | The page for managing XSS protection.
     *   exclusion            | The page for managing excluded list.
     *   export               | Export the settings as a JSON file.
     *   import               | Improt the setting by a JSON file.
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
     * Set up basic settings.
     *
     * @return ResponseInterface
     */
    public function basic(): ResponseInterface
    {
        $data = [];

        $postParams = get_request()->getParsedBody();

        if (isset($postParams['tab'])) {
            unset_superglobal('tab', 'post');
            $this->saveConfig();
        }

        $data['title'] = __('panel', 'title_basic_setting', 'Basic Settings');

        return $this->renderPage('panel/setting', $data);
    }

    /**
     * Set up basic settings.
     *
     * @return ResponseInterface
     */
    public function messenger(): ResponseInterface
    {
        $data = [];

        $postParams = get_request()->getParsedBody();

        $data['ajaxUrl'] = $this->url('ajax/tryMessenger');

        if (isset($postParams['tab'])) {
            unset_superglobal('tab', 'post');
            $this->saveConfig();
        }

        $data['title'] = __('panel', 'title_messenger', 'Messenger');

        return $this->renderPage('panel/messenger', $data);
    }

    /**
     * IP manager.
     *
     * @return ResponseInterface
     */
    public function ipManager(): ResponseInterface
    {
        $postParams = get_request()->getParsedBody();

        if (isset($postParams['ip']) &&
            filter_var(explode('/', $postParams['ip'])[0], FILTER_VALIDATE_IP)
        ) {
            $url = $postParams['url'];
            $ip = $postParams['ip'];
            $rule = $postParams['action'];
            $order = (int) $postParams['order'];

            if ($order > 0) {
                $order--;
            }

            $ipList = (array) $this->getConfig('ip_manager');

            if ('allow' === $rule || 'deny' === $rule) {
                $newIpList = [];
                $newIpList[$order]['url'] = $url;
                $newIpList[$order]['ip'] = $ip;
                $newIpList[$order]['rule'] = $rule;
                array_splice($ipList, $order, 0, $newIpList);
                $this->setConfig('ip_manager', $ipList);
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

        $data = [];

        $data['ip_list'] = $this->getConfig('ip_manager');

        $data['title'] = __('panel', 'title_ip_manager', 'IP Manager');

        return $this->renderPage('panel/ip_manager', $data);
    }

    /**
     * Exclude the URLs that they don't need protection.
     *
     * @return ResponseInterface
     */
    public function exclusion(): ResponseInterface
    {
        $postParams = get_request()->getParsedBody();

        if (isset($postParams['url'])) {
            $url = $postParams['url'];
            $action = $postParams['action'];
            $order = (int) $postParams['order'];

            $excludedUrls = (array) $this->getConfig('excluded_urls');

            if ('add' === $action) {
                array_push(
                    $excludedUrls,
                    [
                        'url' => $url,
                    ]
                );
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

        $data = [];

        $data['exclusion_list'] = $this->getConfig('excluded_urls');

        $data['title'] = __('panel', 'title_exclusion_list', 'Exclusion List');

        return $this->renderPage('panel/exclusion', $data);
    }

    /**
     * Export settings.
     *
     * @return ResponseInterface
     */
    public function export(): ResponseInterface
    {
        $response = get_response();

        $stream = $response->getBody();
        $stream->write(json_encode($this->configuration, JSON_PRETTY_PRINT));
        $stream->rewind();

        $filename = 'shieldon_' . date('Y-m-d-Hi') . '.json';

        $response = $response->withHeader('Content-Type', 'text/plain');
        $response = $response->withHeader('Content-Disposition', 'attachment; filename=' . $filename);
        $response = $response->withHeader('Expires', '0');
        $response = $response->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        $response = $response->withHeader('Pragma', 'public');
        $response = $response->withBody($stream);

        return $response;
    }

    /**
     * Import settings.
     *
     * @return ResponseInterface
     */
    public function import(): ResponseInterface
    {
        $request = get_request();
        $response = get_response();

        $uploadedFileArr = $request->getUploadedFiles();
        $importedFileContent = $uploadedFileArr['json_file']->getStream()->getContents();

        if (!empty($importedFileContent)) {
            $jsonData = json_decode($importedFileContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->pushMessage(
                    'error',
                    __(
                        'panel',
                        'error_invalid_json_file',
                        'Invalid JSON file.'
                    )
                );
                get_session_instance()->set('flash_messages', $this->messages);

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

                $this->pushMessage(
                    'success',
                    __(
                        'panel',
                        'success_json_imported',
                        'JSON file imported successfully.'
                    )
                );

                get_session_instance()->set('flash_messages', $this->messages);

                // Return succesfull result message.
                return $response->withHeader('Location', $this->url('setting/basic'));
            }
        }

        $this->pushMessage(
            'error',
            __(
                'panel',
                'error_invalid_config_file',
                'Invalid Shieldon configuration file.'
            )
        );

        get_session_instance()->set('flash_messages', $this->messages);

        return $response->withHeader('Location', $this->url('setting/basic'));
    }
}
