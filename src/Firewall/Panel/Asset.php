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
use Shieldon\Firewall\HttpFactory;
use function Shieldon\Firewall\get_response;

/**
 * The static asset files such as CSS, JavaScript.
 */
class Asset extends BaseController
{
    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   css                  | Output the content contains CSS.
     *   js                   | Output the content contains JavaScript.
     *   favicon              | Output the content contains favicon's binary string.
     *   logo                 | Output the content contains logo's binary string.
     *  ----------------------|---------------------------------------------
     */

    /**
     * The directory in where the static assets of the firewall panel are placed.
     */
    const PANEL_ASSET_DIR = __DIR__ . '/../../../assets';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Output the content contains CSS to the browser.
     *
     * @return ResponseInterface
     */
    public function css(): ResponseInterface
    {
        return $this->getResponseWithContentType(
            'text/css; charset=UTF-8',
            $this->loadCss()
        );
    }

    /**
     * Output the content contains JavaScript to the browser.
     *
     * @return ResponseInterface
     */
    public function js(): ResponseInterface
    {
        return $this->getResponseWithContentType(
            'text/javascript; charset=UTF-8',
            $this->loadJs()
        );
    }

    /**
     * Output the content contains image binary string to the browser.
     *
     * @return ResponseInterface
     */
    public function favicon(): ResponseInterface
    {
        return $this->getResponseWithContentType(
            'image/x-icon',
            $this->loadFavicon()
        );
    }

    /**
     * Output the content contains logo's binary string to the browser.
     *
     * @return ResponseInterface
     */
    public function logo(): ResponseInterface
    {
        return $this->getResponseWithContentType(
            'image/png',
            $this->loadLogo()
        );
    }

    /**
     * Load CSS content.
     *
     * @return string
     */
    protected function loadJs(): string
    {
        ob_start();
        echo file_get_contents(self::PANEL_ASSET_DIR . '/dist/app-packed.js');
        $output = ob_get_contents();
        ob_end_clean();
    
        return $this->filterString($output);
    }

    /**
     * Load CSS content.
     *
     * @return string
     */
    protected function loadCss(): string
    {
        ob_start();
        echo file_get_contents(self::PANEL_ASSET_DIR . '/dist/app-packed.css');
        $output = ob_get_contents();
        ob_end_clean();
    
        return $this->filterString($output);
    }

    /**
     * Load Shieldon's favicon.
     *
     * @return string
     */
    protected function loadFavicon(): string
    {
        ob_start();
        echo file_get_contents(self::PANEL_ASSET_DIR . '/src/images/favicon.ico');
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    /**
     * Load Shieldon's logo.
     *
     * @return string
     */
    protected function loadLogo(): string
    {
        ob_start();
        echo file_get_contents(self::PANEL_ASSET_DIR . '/src/images/logo.png');
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    /**
     * Get server response with content.
     *
     * @param string $contentType The content type.
     * @param string $body        The data sring.
     *
     * @return ResponseInterface
     */
    private function getResponseWithContentType(string $contentType, string $body): ResponseInterface
    {
        $response = get_response();
        $response = $response->withHeader('Content-Type', $contentType);
        $stream = HttpFactory::createStream();
        $stream->write($body);
        $stream->rewind();
        $response = $response->withBody($stream);

        return $this->withCacheHeader($response);
    }

    /**
     * Return the header with cache parameters.
     *
     * @param ResponseInterface $response The PSR-7 server response.
     *
     * @return ResponseInterface
     */
    private function withCacheHeader(ResponseInterface $response): ResponseInterface
    {
        $seconds = 86400; // 24 hours
        $response = $response->withHeader('Expires', gmdate('D, d M Y H:i:s', time() + $seconds) . ' GMT');
        $response = $response->withHeader('Pragma', 'cache');
        $response = $response->withHeader('Cache-Control', 'max-age=' . $seconds);

        return $response;
    }

    /**
     * Remove the PHP syntax, prevent the possible security issues.
     *
     * @param string $string
     *
     * @return string
     */
    private function filterString(string $string): string
    {
        return str_replace(['<?php', '<?', '?>'], '', $string);
    }
}
