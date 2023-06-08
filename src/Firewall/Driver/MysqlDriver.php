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

namespace Shieldon\Firewall\Driver;

use Shieldon\Firewall\Driver\SqlDriverProvider;
use PDO;

/**
 * Mysql Driver.
 */
class MysqlDriver extends SqlDriverProvider
{
    /**
     * Constructor.
     *
     * @param PDO  $pdo   The PDO instance.
     * @param bool $debug The option to enable debugging or not.
     *
     * @return void
     */
    public function __construct(PDO $pdo, bool $debug = false)
    {
        parent::__construct($pdo, $debug);
    }
}
