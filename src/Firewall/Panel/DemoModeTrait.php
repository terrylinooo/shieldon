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

namespace Shieldon\Firewall\Panel;

use Shieldon\Firewall\Container;

/*
 * Tradit for demonstration.
 */
trait DemoModeTrait
{
    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   demo                 | Start a demo mode. Setting fields are hidden.
     *  ----------------------|---------------------------------------------
     */

    /**
     * The mode of the firewall control panel.
     * self: Shieldon | managed: Firewall | demo: Demo
     *
     * @var string
     */
    protected $mode = 'self';

    /**
     * Login as a demo user.
     *
     * @var array
     */
    protected $demoUser = [
        'user' => 'demo',
        'pass' => 'demo',
    ];

    /**
     * Mark as demo.
     *
     * @var string
     */
    protected $markAsDemo = '';

    /**
     * In demo mode, user's submit will not take effect.
     *
     * @param string $user The user name.
     * @param string $pass The user password.
     *
     * @return void
     */
    public function demo(string $user = '', string $pass = ''): void
    {
        $this->demoUser['user'] = $user ?: 'demo';
        $this->demoUser['pass'] = $pass ?: 'demo';

        $this->mode = 'demo';
        $this->markAsDemo = ' (DEMO)';

        Container::get('shieldon')->managedBy('demo');
    }
}
