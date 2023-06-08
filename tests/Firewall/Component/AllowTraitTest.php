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

namespace Shieldon\FirewallTest\Component;

class AllowTraitTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function testGetAllowedItems()
    {
        $trustedbot = new \Shieldon\Firewall\Component\TrustedBot();
   
        $allowedList = $trustedbot->getAllowedItems();
        $truestedBotList = [

            // Search engline: Google.
            'google_1' => [
                'userAgent' => 'google',
                'rdns'      => '.googlebot.com',
            ],
    
            'google_2' => [
                'userAgent' => 'google',
                'rdns'      => '.google.com',
            ],
    
            // Search engline: Mircosoft.
            'bing_1' => [
                'userAgent' => 'live',
                'rdns'      => '.live.com',
            ],
    
            'bing_2' => [
                'userAgent' => 'msn',
                'rdns'      => '.msn.com',
            ],
    
            'bing_3' => [
                'userAgent' => 'bing',
                'rdns'      => '.bing.com',
            ],
    
            // Search engline: Yahoo.
            'yahoo_1' => [
                'userAgent' => 'inktomisearch',
                'rdns'      => '.inktomisearch.com',
            ],
    
            'yahoo_2' => [
                'userAgent' => 'yahoo',
                'rdns'      => '.yahoo.com',
            ],
    
            'yahoo_3' => [
                'userAgent' => 'yahoo',
                'rdns'      => '.yahoo.net',
            ],
    
            // Search engine: Yandex.
            'yandex_1' => [
                'userAgent' => 'yandex',
                'rdns'      => '.yandex.com',
            ],
    
            'yandex_2' => [
                'userAgent' => 'yandex',
                'rdns'      => '.yandex.net',
            ],
    
            'yandex_3' => [
                'userAgent' => 'yandex',
                'rdns'      => '.yandex.ru',
            ],
    
            // Facebook crawlers.
            'facebook' => [
                'userAgent' => 'facebook',
                'rdns'      => '.fbsv.net',
            ],
    
            // Twitter crawlers.
            'twitter' => [
                'userAgent' => 'Twitterbot',
                'rdns'      => '.twttr.com', // (not twitter.com)
            ],
    
            // W3C validation services.
            'w3' => [
                'userAgent' => 'w3.org',
                'rdns'      => '.w3.org',
            ],
    
            // Ask.com crawlers.
            'ask' => [
                'userAgent' => 'ask',
                'rdns'      => '.ask.com',
            ]
        ];

        $this->assertEquals($allowedList, $truestedBotList);
    }

    public function testGetAllowedItem()
    {
        $trustedbot = new \Shieldon\Firewall\Component\TrustedBot();
   
        $allowedList = $trustedbot->getAllowedItem('facebook');

        $truestedBotList = [
            'userAgent' => 'facebook',
            'rdns'      => '.fbsv.net',
        ];

        $this->assertEquals($allowedList, $truestedBotList);
    }

    public function testRemoveAllowedItem()
    {
        $trustedbot = new \Shieldon\Firewall\Component\TrustedBot();

        $this->assertTrue($trustedbot->hasAllowedItem('ask'));

        $trustedbot->removeAllowedItem('ask');

        $this->assertFalse($trustedbot->hasAllowedItem('ask'));

        $trustedbot->removeAllowedItem('yahoo_3');

        $truestedBotList = [

            // Search engline: Google.
            'google_1' => [
                'userAgent' => 'google',
                'rdns'      => '.googlebot.com',
            ],
    
            'google_2' => [
                'userAgent' => 'google',
                'rdns'      => '.google.com',
            ],
    
            // Search engline: Mircosoft.
            'bing_1' => [
                'userAgent' => 'live',
                'rdns'      => '.live.com',
            ],
    
            'bing_2' => [
                'userAgent' => 'msn',
                'rdns'      => '.msn.com',
            ],
    
            'bing_3' => [
                'userAgent' => 'bing',
                'rdns'      => '.bing.com',
            ],
    
            // Search engline: Yahoo.
            'yahoo_1' => [
                'userAgent' => 'inktomisearch',
                'rdns'      => '.inktomisearch.com',
            ],
    
            'yahoo_2' => [
                'userAgent' => 'yahoo',
                'rdns'      => '.yahoo.com',
            ],

            // Search engine: Yandex.
            'yandex_1' => [
                'userAgent' => 'yandex',
                'rdns'      => '.yandex.com',
            ],
    
            'yandex_2' => [
                'userAgent' => 'yandex',
                'rdns'      => '.yandex.net',
            ],
    
            'yandex_3' => [
                'userAgent' => 'yandex',
                'rdns'      => '.yandex.ru',
            ],
    
            // Facebook crawlers.
            'facebook' => [
                'userAgent' => 'facebook',
                'rdns'      => '.fbsv.net',
            ],
    
            // Twitter crawlers.
            'twitter' => [
                'userAgent' => 'Twitterbot',
                'rdns'      => '.twttr.com', // (not twitter.com)
            ],
    
            // W3C validation services.
            'w3' => [
                'userAgent' => 'w3.org',
                'rdns'      => '.w3.org',
            ],
        ];

        $allowedList = $trustedbot->getAllowedItems();

        $this->assertEquals($allowedList, $truestedBotList);
    }

    public function testRemoveAllowedItems()
    {
        $trustedbot = new \Shieldon\Firewall\Component\TrustedBot();

        $trustedbot->removeAllowedItems();

        $allowedList = $trustedbot->getAllowedItems();

        $this->assertEquals($allowedList, []);
    }

    public function testgetAllowByPrefix()
    {
        $trustedbot = new \Shieldon\Firewall\Component\TrustedBot();

        $allowedList = $trustedbot->getAllowByPrefix('google');

        $truestedBotList = [

            // Search engline: Google.
            'google_1' => [
                'userAgent' => 'google',
                'rdns'      => '.googlebot.com',
            ],
    
            'google_2' => [
                'userAgent' => 'google',
                'rdns'      => '.google.com',
            ],
        ];

        $this->assertEquals($allowedList, $truestedBotList);
    }

    // removeAllowByPrefix

    public function testremoveAllowByPrefix()
    {
        $trustedbot = new \Shieldon\Firewall\Component\TrustedBot();

        $trustedbot->removeAllowByPrefix('google');
        $trustedbot->removeAllowByPrefix('yandex');

        $truestedBotList = [

            // Search engline: Mircosoft.
            'bing_1' => [
                'userAgent' => 'live',
                'rdns'      => '.live.com',
            ],
    
            'bing_2' => [
                'userAgent' => 'msn',
                'rdns'      => '.msn.com',
            ],
    
            'bing_3' => [
                'userAgent' => 'bing',
                'rdns'      => '.bing.com',
            ],
    
            // Search engline: Yahoo.
            'yahoo_1' => [
                'userAgent' => 'inktomisearch',
                'rdns'      => '.inktomisearch.com',
            ],
    
            'yahoo_2' => [
                'userAgent' => 'yahoo',
                'rdns'      => '.yahoo.com',
            ],
    
            'yahoo_3' => [
                'userAgent' => 'yahoo',
                'rdns'      => '.yahoo.net',
            ],
    
            // Facebook crawlers.
            'facebook' => [
                'userAgent' => 'facebook',
                'rdns'      => '.fbsv.net',
            ],
    
            // Twitter crawlers.
            'twitter' => [
                'userAgent' => 'Twitterbot',
                'rdns'      => '.twttr.com', // (not twitter.com)
            ],
    
            // W3C validation services.
            'w3' => [
                'userAgent' => 'w3.org',
                'rdns'      => '.w3.org',
            ],
    
            // Ask.com crawlers.
            'ask' => [
                'userAgent' => 'ask',
                'rdns'      => '.ask.com',
            ]
        ];

        $allowedList = $trustedbot->getAllowedItems();

        $this->assertEquals($allowedList, $truestedBotList);
    }

    public function testIsAllowed()
    {
        $trustedbot = new \Shieldon\Firewall\Component\TrustedBot();
        $this->assertFalse($trustedbot->isAllowed());

        $mock = $this->getMockForTrait('Shieldon\Firewall\Component\AllowedTrait');
        $this->assertFalse($mock->isAllowed());
    }
}
