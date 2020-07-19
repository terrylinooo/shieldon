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

namespace Shieldon\Firewall\Traits\Panel;

/*
 * Tradit for demonstration.
 */
trait DemoModeTrait
{
    /**
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
     * In demo mode, user's submit will not take effect.
     *
     * @param string $user The user name.
     * @param string $pass The user password.
     *
     * @return void
     */
    public function demo(string $user = '', string $pass = ''): void
    {
        $this->demoUser['user'] = $user ?? 'demo';
        $this->demoUser['pass'] = $pass ?? 'demo';

        $this->mode = 'demo';
    }
}
