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

namespace Shieldon\Firewall\Kernel;

use Shieldon\Firewall\Kernel\Enum;
use function Shieldon\Firewall\get_session_instance;
use function Shieldon\Firewall\create_new_session_instance;
use function time;

/*
 * The main functionality for this trait is to limit the online session amount.
 */
trait SessionTrait
{
    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   limitSession         | Limit the amount of the online users.
     *   getSessionCount      | Get the amount of the sessions.
     *   removeSessionsByIp   | Remove sessions using the same IP address.
     *  ----------------------|---------------------------------------------
     */

    /**
     * Are you willing to limit the online session amount?
     *
     * @var array
     */
    protected $sessionLimit = [

        // How many sessions will be available?
        // 0 = no limit.
        'count' => 0,

        // How many minutes will a session be availe to visit?
        // 0 = no limit.
        'period' => 0,

        // Only allow one session per IP address.
        // If this option is set to true, user with multiple sessions will be
        // removed from the session table.
        'unique_only' => false,
    ];

    /**
     * Record the online session status.
     * This will be enabled when $sessionLimit[count] > 0
     *
     * This array is recording a live data, not a setting value.
     *
     * @var array
     */
    protected $sessionStatus = [

        // Online session count.
        'count' => 0,

        // Current session order.
        'order' => 0,

        // Current waiting queue.
        'queue' => 0,
    ];


    /**
     * Current user's session data.
     *
     * @var array
     */
    protected $sessionData = [];

    /**
     * Limt online sessions.
     *
     * @param int $count   The amount of online users. If reached, users will be
     *                     in queue.
     * @param int $period  The period of time allows users browsing.
     *                     (unit: second)
     * @param bool $unique Allow only one session per IP address.
     *
     * @return void
     */
    public function limitSession(int $count = 1000, int $period = 300, bool $unique = false): void
    {
        $this->sessionLimit = [
            'count' => $count,
            'period' => $period,
            'unique_only' => $unique,
        ];
    }

    /**
     * Get online people count. If enable limitSession.
     *
     * @return int
     */
    public function getSessionCount(): int
    {
        return $this->sessionStatus['count'];
    }

    /**
     * Deal with online sessions.
     *
     * @param int $statusCode The response code.
     *
     * @return int The response code.
     */
    protected function sessionHandler($statusCode): int
    {
        if (Enum::RESPONSE_ALLOW !== $statusCode) {
            return $statusCode;
        }

        // If you don't enable `limit traffic`, ignore the following steps.
        if (empty($this->sessionLimit['count'])) {
            return Enum::RESPONSE_ALLOW;
        } else {
            // Get the proerties.
            $limit = (int) ($this->sessionLimit['count'] ?? 0);
            $period = (int) ($this->sessionLimit['period'] ?? 300);
            $now = time();

            $this->sessionData = $this->driver->getAll('session');

            $sessionPools = [];

            $i = 1;
            $sessionOrder = 0;

            $sessionId = get_session_instance()->getId();

            if (!empty($this->sessionData)) {
                foreach ($this->sessionData as $v) {
                    $sessionPools[] = $v['id'];
                    $lasttime = (int) $v['time'];

                    if ($sessionId === $v['id']) {
                        $sessionOrder = $i;
                    }
    
                    // Remove session if it expires.
                    if ($now - $lasttime > $period) {
                        $this->driver->delete($v['id'], 'session');
                    }
                    $i++;
                }

                if (0 === $sessionOrder) {
                    $sessionOrder = $i;
                }
            } else {
                $sessionOrder = 0;
            }

            // Count the online sessions.
            $this->sessionStatus['count'] = count($sessionPools);
            $this->sessionStatus['order'] = $sessionOrder;
            $this->sessionStatus['queue'] = $sessionOrder - $limit;

            if (!in_array($sessionId, $sessionPools)) {
                $this->sessionStatus['count']++;
            }

            /*
            if (!in_array($sessionId, $sessionPools)) {
                $this->sessionStatus['count']++;

                $data = [];

                // New session, record this data.
                $data['id'] = $sessionId;
                $data['ip'] = $this->ip;
                $data['time'] = $now;
                $data['microtimestamp'] = get_microtimestamp();

                $this->driver->save($sessionId, $data, 'session');
            }*/

            // Online session count reached the limit. So return RESPONSE_LIMIT_SESSION response code.
            if ($sessionOrder >= $limit) {
                return Enum::RESPONSE_LIMIT_SESSION;
            }
        }

        return Enum::RESPONSE_ALLOW;
    }

    /**
     * Remove sessions using the same IP address.
     * This method must be run after `sessionHandler`.
     *
     * @param string $ip An IP address
     *
     * @return void
     */
    protected function removeSessionsByIp(string $ip): void
    {
        if ($this->sessionLimit['unique_only']) {
            foreach ($this->sessionData as $v) {
                if ($v['ip'] === $ip) {
                    $this->driver->delete($v['id'], 'session');
                }
            }
        }
    }

    // @codeCoverageIgnoreStart

    /**
     * For testing propose. This method will create new Session.
     *
     * @param string $sessionId The session Id.
     *
     * @return void
     */
    protected function setSessionId(string $sessionId = ''): void
    {
        if ('' !== $sessionId) {
            create_new_session_instance($sessionId);
        }
    }
    // @codeCoverageIgnoreEnd
}
