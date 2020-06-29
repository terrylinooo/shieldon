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

namespace Shieldon\Component;

use Psr\Http\Message\ServerRequestInterface;
use Shieldon\HttpFactory;
use Shieldon\IpTrait;

use function implode;
use function preg_match;
use function is_null;

/**
 * Robot
 */
class Header extends ComponentProvider
{
    use IpTrait;

    const STATUS_CODE = 83;

    /**
     *  Very common requests from normal users.
     * 
     * @var string
     */
    protected $commonHeaderFileds = [
        'Accept',
        'Accept-Language',
        'Accept-Encoding',
    ];

    /**
     * Header information.
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Header component constructor.
     */
    public function __construct(?ServerRequestInterface $request  = null)
    {
        parent::__construct($request);

        $this->headers = $this->request->getHeaders();
    }

    /**
     * {@inheritDoc}
     */
    public function isDenied(): bool
    {
        if ($this->strictMode) {

            foreach ($this->commonHeaderFileds as $fieldName) {

                // If strict mode is on, this value must be found.
                if (!isset($this->headers[$fieldName])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * All request headers.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Unique deny status code.
     *
     * @return int
     */
    public function getDenyStatusCode(): int
    {
        return self::STATUS_CODE;
    }
}