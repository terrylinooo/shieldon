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
     * The content output to be a CSS file.
     *
     * @return ResponseInterface
     */
    public function css(): ResponseInterface
    {
        $response = get_response();
        $response = $response->withHeader('Content-Type', 'text/css; charset=UTF-8');
        $stream = HttpFactory::createStream();
        $stream->write($this->loadCss());
        $stream->rewind();
        $response = $response->withBody($stream);

        return $this->withCacheHeader($response);
    }

    /**
     * The content output to be a JavaScript file.
     *
     * @return ResponseInterface
     */
    public function js(): ResponseInterface
    {
        $response = get_response();
        $response = $response->withHeader('Content-Type', 'text/javascript; charset=UTF-8');
        $stream = HttpFactory::createStream();
        $stream->write($this->loadJs());
        $stream->rewind();
        $response = $response->withBody($stream);

        return $this->withCacheHeader($response);
    }

    /**
     * The content output to be a favicon.
     *
     * @return ResponseInterface
     */
    public function favicon(): ResponseInterface
    {
        $response = get_response();
        $response = $response->withHeader('Content-Type', 'image/x-icon');
        $stream = HttpFactory::createStream();
        $stream->write($this->loadFavicon());
        $stream->rewind();
        $response = $response->withBody($stream);

        return $this->withCacheHeader($response);
    }

    /**
     * The content output to be a logo image.
     *
     * @return ResponseInterface
     */
    public function logo(): ResponseInterface
    {
        $response = get_response();
        $response = $response->withHeader('Content-Type', 'image/png');
        $stream = HttpFactory::createStream();
        $stream->write($this->loadLogo());
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
     * Load CSS content.
     *
     * @return string
     */
    private function loadJs(): string
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
    private function loadCss(): string
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
    private function loadFavicon(): string
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
    private function loadLogo(): string
    {
        ob_start();
        echo file_get_contents(self::PANEL_ASSET_DIR . '/src/images/logo.png');
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    /**
     * Remove the PHP syntax, prevent the possible security issues.
     *
     * @param sring $string
     *
     * @return string
     */
    private function filterString($string): string
    {
        return str_replace(['<?php', '<?', '?>'], '', $string);
    }
}
