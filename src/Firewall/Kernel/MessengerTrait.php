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

use Shieldon\Messenger\Messenger\MessengerInterface;
use RuntimeException;

/*
 * Messenger Trait is loaded in Kernel instance only.
 */
trait MessengerTrait
{
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
     * Set a messenger
     *
     * @param MessengerInterfa $instance
     *
     * @return void
     */
    public function setMessenger(MessengerInterface $instance): void
    {
        $class = $this->getClassName($instance);
        $this->messengers[$class] = $instance;
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
        } catch (RuntimeException $e) {
            // Do not throw error, becasue the third-party services might be unavailable.
        }
    }

    // @codeCoverageIgnoreEnd
}
