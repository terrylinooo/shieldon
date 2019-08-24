<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Panel;

/*
 * @since 3.0.0
 */
class Configure
{
    /**
     * The configuration file's absolute path.
     *
     * @var string
     */
    protected $configFile = '';

    /**
     * Constructor.
     *
     * @param string $configFile
     */
    public function __construct(string $configFile)
    {
        $this->configFile = $configFile;
    }
}