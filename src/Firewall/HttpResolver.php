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

namespace Shieldon\Firewall;

use Psr\Http\Message\ResponseInterface;
use function header;
use function headers_sent;
use function sprintf;
use function stripos;

/*
 * Display the final result.
 */
class HttpResolver
{
    /**
     * Constructor.
     *
     * @param ResponseInterface $response The PSR-7 response.
     *
     * @return void
     */
    public function __construct(ResponseInterface $response)
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
        exit;
    }
}