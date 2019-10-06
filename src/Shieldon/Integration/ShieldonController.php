<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 */

namespace Shieldon\Integrationl;

use Shieldon\Firewall;
use Shieldon\FirewallPanel;

/**
 * The most easy way for implementing Shieldon Firewall in your PHP project.
 * This way does not follow the design pattern whatever what framwork you are using.
 * it is why it is so easy...
 * 
 * @since 3.0.1
 */
class ShieldonController
{
    /**
     * Shieldon controller invokable class
     *
     * @param string $storage         The absolute path of the storage where stores Shieldon generated data.
     * @param string $panelRequestURI The entry URL of Firewall Panel.
     *
     * @return null|\Shieldon\Firewall
     */
    public function __invoke($storagePath = '', $panelRequestURI = '/Glory-to-Hong-Kong/')
    {
        // Prevent possible issues occur in CLI command line.
        if (isset($_SERVER['REQUEST_URI'])) {

            if ('' === $storagePath) {

                // shieldon folder is placed above wwwroot for best security, this folder must be writable.
                $storagePath = dirname($_SERVER['SCRIPT_FILENAME']) . '/../shieldon';
            }

            $firewall = new Firewall($storagePath);

            if (0 === strpos($firewall->getShieldon()->getCurrentUrl(), $panelRequestURI)) {

                // Get into the Firewall Panel.
                $controlPanel = new FirewallPanel($firewall);
                $controlPanel->entry();
            }

            $firewall->restful();
            $firewall->run();
        }
    }
}
