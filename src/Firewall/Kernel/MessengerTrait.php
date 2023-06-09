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

use Shieldon\Messenger\Messenger\MessengerInterface;
use RuntimeException;

/*
 * Messenger Trait is loaded in Kernel instance only.
 */
trait MessengerTrait
{
    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   setMessenger         | Set a messenger
     *  ----------------------|---------------------------------------------
     */

    /**
     * The ways Shieldon send a message to when someone has been blocked.
     * The collection of \Shieldon\Messenger\Messenger\MessengerInterface
     *
     * @var array
     */
    protected $messenger = [];

    /**
     * The message that will be sent to the third-party API.
     *
     * @var string
     */
    protected $msgBody = '';

    /**
     * Get a class name without namespace string.
     *
     * @param object $instance Class
     *
     * @return string
     */
    abstract protected function getClassName($instance): string;

    /**
     * Set a messenger
     *
     * @param MessengerInterface $instance The messenger instance.
     *
     * @return void
     */
    public function setMessenger(MessengerInterface $instance): void
    {
        $class = $this->getClassName($instance);
        $this->messenger[$class] = $instance;
    }

    /**
     * Set the message body.
     *
     * @param string $message The message text.
     *
     * @return void
     */
    protected function setMessageBody(string $message = ''): void
    {
        $this->msgBody = $message;
    }

    // @codeCoverageIgnoreStart

    /**
     * Undocumented function
     *
     * @return void
     */
    protected function triggerMessengers(): void
    {
        if (empty($this->msgBody)) {
            return;
        }

        try {
            foreach ($this->messenger as $messenger) {
                $messenger->setTimeout(2);
                $messenger->send($this->msgBody);
            }
        // phpcs:ignore
        } catch (RuntimeException $e) {
            // Do not throw error, becasue the third-party services might be unavailable.
        }
    }

    // @codeCoverageIgnoreEnd
}
