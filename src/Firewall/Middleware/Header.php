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

/**
 * A PSR-15 middleware that denys requests without specific header inforamtion.
 */
class Header implements MiddlewareInterface
{
    /**
     * 406 - Not Acceptable.
     *
     * @var int
     */
    const HTTP_STATUS_CODE = 406;

    /**
     * Very common requests from normal users.
     *
     * @var array
     */
    protected $fieldList = [
        'Accept',
        'Accept-Language',
        'Accept-Encoding',
    ];

    /**
     * Constructor.
     *
     * @param array $fieldList The list that want to be denied.
     *
     * @return void
     */
    public function __construct(array $fieldList = [])
    {
        if (!empty($fieldList)) {
            $this->fieldList = $fieldList;
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
        foreach ($this->fieldList as $fieldName) {
            if (!$request->hasHeader($fieldName)) {
                return (new Response)->withStatus(self::HTTP_STATUS_CODE);
            }
        }
        return $handler->handle($request);
    }
}
