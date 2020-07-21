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

namespace Shieldon\Firewall\Kernel;

use function Shieldon\Firewall\get_session;
use function microtime;
use function str_replace;
use function time;

/*
 * The main functionality for this trait is to limit the online session amount.
 */
trait SessionTrait
{
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
        if (self::RESPONSE_ALLOW !== $statusCode) {
            return $statusCode;
        }

        // If you don't enable `limit traffic`, ignore the following steps.
        if (empty($this->sessionLimit['count'])) {
            return self::RESPONSE_ALLOW;

        } else {

            // Get the proerties.
            $limit = (int) ($this->sessionLimit['count'] ?? 0);
            $period = (int) ($this->sessionLimit['period'] ?? 300);
            $now = time();

            $sessionData = $this->driver->getAll('session');
            $sessionPools = [];

            $i = 1;
            $sessionOrder = 0;

            if (!empty($sessionData)) {
                foreach ($sessionData as $v) {
                    $sessionPools[] = $v['id'];
                    $lasttime = (int) $v['time'];
    
                    if (get_session()->get('id') === $v['id']) {
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

            if (!in_array(get_session()->get('id'), $sessionPools)) {
                $this->sessionStatus['count']++;

                // New session, record this data.
                $data['id'] = get_session()->get('id');
                $data['ip'] = $this->ip;
                $data['time'] = $now;

                $microtimesamp = explode(' ', microtime());
                $microtimesamp = $microtimesamp[1] . str_replace('0.', '', $microtimesamp[0]);
                $data['microtimesamp'] = $microtimesamp;

                $this->driver->save(get_session()->get('id'), $data, 'session');
            }

            // Online session count reached the limit. So return RESPONSE_LIMIT_SESSION response code.
            if ($sessionOrder >= $limit) {
                return self::RESPONSE_LIMIT_SESSION;
            }
        }

        return self::RESPONSE_ALLOW;
    }



    // @codeCoverageIgnoreStart

    /**
     * For testing propose.
     *
     * @param string $sessionId
     *
     * @return void
     */
    protected function setSessionId(string $sessionId = ''): void
    {
        if ('' !== $sessionId) {
            get_session()->set('id', $sessionId);
        }
    }
}
