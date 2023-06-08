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

namespace Shieldon\FirewallTest\Security;

class XssTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function testClean()
    {
        $xssInstance = new \Shieldon\Security\Xss();

        $text = 'javascript:/*--></title></style></textarea></script>';
        $text .= '</xmp><svg/onload=\'+/"/+/onmouseover=1/+/[*/[]/+alert(1)//\'>';

        $_POST['username'] = $text;

        $username = $xssInstance->clean($_POST['username'], false);

        $this->assertSame(
            $username,
            '[removed]/*--&gt;&lt;/title&gt;&lt;/style&gt;&lt;/textarea&gt;[removed]</xmp>&lt;svg/[removed]&gt;'
        );

        $_POST['products'] = [
            '<IMG SRC=javascript:alert(String.fromCharCode(88,83,83))>',
            '<IMG SRC="jav    ascript:alert(\'XSS\');">',
        ];

        $products = $xssInstance->clean($_POST['products'], false);

        $this->assertSame($products[0], '<IMG>');
        $this->assertSame($products[1], '<IMG>');
 
        unset($_POST['username'], $_POST['products']);
    }

    public function testBypassList()
    {
        $this->console('Test the XSS Bypass string list.', 'info');

        $xssInstance = new \Shieldon\Security\Xss();

        // You can add as many bybass string as you want for testing.
        $bypassList = file(__DIR__ . '/../../samples/xss_bypass_list/test_list.txt');

        $originStrArr = [];
        $filiteredStrArr = [];

        foreach ($bypassList as $num => $string) {
            $originStrArr[$num] = $string;
            $filiteredStrArr[$num] = $xssInstance->clean($string, false);

            if ($originStrArr[$num] === $filiteredStrArr[$num]) {
                echo "($num+1)" . $string . "\n";
                $this->assertTrue(false);
            } else {
                $this->assertTrue(true);
            }
        }
    }

    public function testImage()
    {
        $this->console('Test the XSS Bypass string list for image.', 'info');

        $file = exif_read_data(__DIR__ . '/../../samples/xss_bypass_list/test_sample.jpg');

        $this->assertSame($file['Software'], '"><script>alert(123)</script><"');

        $xssInstance = new \Shieldon\Security\Xss();
        $fileFiltered = $xssInstance->clean($file, true);

        $this->assertSame($fileFiltered['Software'], '">[removed]alert&#40;123&#41;[removed]<"');

        $file2 = exif_read_data(__DIR__ . '/../../samples/xss_bypass_list/test_sample_2.jpg');
    
        $this->assertSame($file2['Software'], '<?php php_info(); ?>');

        $fileFiltered2 = $xssInstance->clean($file2, true);

        $this->assertSame($fileFiltered2['Software'], '&lt;?php php_info(); ?&gt;');
    }

    public function testImage_part2()
    {
        $xssInstance = new \Shieldon\Security\Xss();

        $image = __DIR__ . '/../../samples/xss_bypass_list/test_sample_3.jpg';
        $result = $xssInstance->checkImage($image);

        $this->assertTrue($result);

        $image = __DIR__ . '/../../samples/xss_bypass_list/test_sample_2.jpg';
        $result = $xssInstance->checkImage($image);

        $this->assertFalse($result);

        $image = __DIR__ . '/../../samples/xss_bypass_list/test_sample.jpg';
        $result = $xssInstance->checkImage($image);

        $this->assertFalse($result);
    }

    public function testEntityDecode()
    {
        $xssInstance = new \Shieldon\Security\Xss();

        $string = 'I will "walk" the <b>dog</b> now. &lpar;ok)';

        $a = htmlentities($string);

        $b = html_entity_decode($a);

        $c = $xssInstance->entityDecode($a);

        $c1 = 'I will "walk" the <b>dog</b> now. (ok)';

        $string = 'nothing happpend';

        $d = $xssInstance->entityDecode($string);

        $string = '&tab;&nbsp;ok&tab;&nbsp;';

        $e = $xssInstance->entityDecode($string);

        $string = '<p>Hello world! Let A&lt;B and A=&#x222C;dxdy</p>';
        $f = $xssInstance->entityDecode($string);

        $this->assertSame($b, 'I will "walk" the <b>dog</b> now. &lpar;ok)');
        $this->assertSame($c, 'I will "walk" the <b>dog</b> now. (ok)');
        $this->assertSame($f, '<p>Hello world! Let A<B and A=∬dxdy</p>');
    }

    public function testSanitizeFilename()
    {
        $xssInstance = new \Shieldon\Security\Xss();
        $filename = $xssInstance->sanitizeFilename('something<?php exit;?>.jpg');
        $this->assertSame($filename, 'somethingphp exit.jpg');
    }
}
