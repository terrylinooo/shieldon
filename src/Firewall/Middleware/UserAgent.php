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

namespace Shieldon\Firewall\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Shieldon\Psr7\Response;
use function implode;
use function preg_match;

/**
 * A PSR-15 middleware that denys all malicious user-agent requests.
 */
class UserAgent implements MiddlewareInterface
{
    /**
     * 400 - Bad Request.
     *
     * @var int
     */
    const HTTP_STATUS_CODE = 400;

    /**
     * The URL list that you want to protect.
     *
     * @var array
     */
    protected $deniedList = [

        // Backlink crawlers
        'Ahrefs',     // http://ahrefs.com/robot/
        'roger',      // rogerbot (SEOMOZ)
        'moz.com',    // SEOMOZ crawlers
        'MJ12bot',    // Majestic crawlers
        'findlinks',  // http://wortschatz.uni-leipzig.de/findlinks
        'Semrush',    // http://www.semrush.com/bot.html

        // Web information crawlers
        'domain',     // Domain name information crawlers.
        'copyright',  // Copyright information crawlers.

        // Others
        'archive',    // Wayback machine
    ];

    /**
     * Constructor.
     *
     * @param array $deniedList The list that want to be denied.
     *
     * @return void
     */
    public function __construct(array $deniedList = [])
    {
        if (!empty($deniedList)) {
            $this->deniedList = $deniedList;
        }
    }

    /**
     * Invoker.
     *
     * @param ServerRequestInterface  $request The PSR-7 server request.
     * @param RequestHandlerInterface $handler The PSR-15 request handler.
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $userAgent = $request->getHeaderLine('user-agent');

        if (empty($userAgent)) {
            return (new Response)->withStatus(self::HTTP_STATUS_CODE);
        }

        if (!empty($this->deniedList)) {
            if (preg_match('/(' . implode('|', $this->deniedList). ')/i', $userAgent)) {
                return (new Response)->withStatus(self::HTTP_STATUS_CODE);
            }
        }

        return $handler->handle($request);
    }
}
