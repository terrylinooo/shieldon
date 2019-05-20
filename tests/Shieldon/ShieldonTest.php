<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon;

use PHPUnit\Framework\TestCase;
use function saveTestingFile;
use function removeTestingFile;

class ShieldonTest extends TestCase
{
    public function testDetect()
    {
        $shieldon = new \Shieldon\Shieldon();

        $dbLocation = saveTestingFile('shieldon_unittest.sqlite3');

        $pdoInstance = new \PDO('sqlite:' . $dbLocation);
        $shieldon->setDriver(new \Shieldon\Driver\SqliteDriver($pdoInstance));

        $shieldon->setComponent(new \Shieldon\Component\Ip());
        $shieldon->setComponent(new \Shieldon\Component\Robot());

        $shieldon->setChannel('test_shieldon_detect');

        $result = $shieldon->run();

        if ($result) {
            $this->assertTrue(true);
        }

        return $result;
    }

}