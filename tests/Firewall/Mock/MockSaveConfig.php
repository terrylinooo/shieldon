<?php
/*
 * This file is part of the Messenger package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Shieldon\FirewallTest\Mock;

class MockSaveConfig
{
    public static function get()
    {
        $json = file_get_contents(__DIR__ . '/save_config_form_test.json');

        return json_decode($json, true);
    }
}
