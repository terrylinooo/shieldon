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

namespace Shieldon\Firewall;

use Psr\Http\Message\ResponseInterface;
use function header;
use function headers_sent;
use function sprintf;
use function stripos;

/**
 * Display the final result.
 */
class HttpResolver
{
    /**
     * Invoker.
     *
     * @param ResponseInterface $response The PSR-7 response.
     * @param bool              $finally  Terminate current PHP proccess if
     *                                    this value is true.
     * @return void
     */
    public function __invoke(ResponseInterface $response, $finally = true): void
    {
        if (!headers_sent()) {
            foreach ($response->getHeaders() as $key => $values) {
                $replace = stripos($key, 'Set-Cookie') === 0 ? false : true;
                foreach ($values as $value) {
                    header(sprintf('%s: %s', $key, $value), $replace);
                    $replace = false;
                }
            }

            header(
                sprintf(
                    'HTTP/%s %s %s',
                    $response->getProtocolVersion(),
                    $response->getStatusCode(),
                    $response->getReasonPhrase()
                ),
                true,
                $response->getStatusCode()
            );
        }

        echo $response->getBody()->getContents();

        if ($finally && !defined('PHP_UNIT_TEST')) {
            // @codeCoverageIgnoreStart
            exit;
            // @codeCoverageIgnoreEnd
        }
    }
}
