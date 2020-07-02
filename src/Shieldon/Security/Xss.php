<?php
/*
 * Original code is from the Secruity class of Codeigniter 2 framework.
 * For more information, please check out the author section blow:
 *
 * @package		CodeIgniter
 * @author		EllisLab Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/security.html
 *
 * I make some modification and use it as a part of Shiendon library.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Shieldon\Security;

use function array_keys;
use function array_map;
use function get_html_translation_table;
use function html_entity_decode;
use function implode;
use function mt_rand;
use function mt_srand;
use function preg_match;
use function preg_match_all;
use function preg_replace;
use function preg_replace_callback;
use function rawurldecode;
use function str_ireplace;
use function str_replace;
use function stripslashes;
use function substr;
use function time;
use function version_compare;

 /**
  * Cross-Site Scripting protection.
  */
class Xss
{
    /**
     * Random Hash for protecting URLs.
     *
     * @var string
     */
    protected $hash = '';
    
    /**
     * List of never allowed strings.
     *
     * @var array
     */
    protected $deniedStringList = [];

    /**
     * List of never allowed regex replacement.
     *
     * @var array
     */
    protected $deniedRegexList = [];

    /**
     * Charset.
     *
     * @var string
     */
    protected $charset = 'UTF-8';

    /**
     * Constructor.
     *
     * @return	void
     */
    public function __construct()
    {
        $this->deniedStringList = [
            'document.cookie' => '[removed]',
            'document.write'  => '[removed]',
            '.parentNode'     => '[removed]',
            '.innerHTML'      => '[removed]',
            '-moz-binding'    => '[removed]',
            '<!--'            => '&lt;!--',
            '-->'             => '--&gt;',
            '<![CDATA['       => '&lt;![CDATA[',
            '<comment>'       => '&lt;comment&gt;'
        ];

        $this->deniedRegexList = [
            'javascript\s*:',
            '\bon\w+=\S+(?=.*>)',       // Inline JavaScript.
            '(document|(document\.)?window)\.(location|on\w*)',
            'expression\s*(\(|&\#40;)', // CSS and IE
            'vbscript\s*:',             // IE, surprise!
            'wscript\s*:',              // IE
            'jscript\s*:',              // IE
            'vbs\s*:',                  // IE
            'Redirect\s+30\d:',
            "([\"'])?data\s*:[^\\1]*?base64[^\\1]*?,[^\\1]*?\\1?",
        ];
    }

    /**
     * XSS Clean
     *
     * Sanitizes data so that Cross Site Scripting Hacks can be
     * prevented.  This function does a fair amount of work but
     * it is extremely thorough, designed to prevent even the
     * most obscure XSS attempts.  Nothing is ever 100% foolproof,
     * of course, but I haven't been able to get anything passed
     * the filter.
     *
     * Note: This function should only be used to deal with data
     * upon submission.  It's not something that should
     * be used for general runtime processing.
     *
     * This function was based in part on some code and ideas I
     * got from Bitflux: http://channel.bitflux.ch/wiki/XSS_Prevention
     *
     * To help develop this script I used this great list of
     * vulnerabilities along with a few other hacks I've
     * harvested from examining vulnerabilities in other programs:
     * http://ha.ckers.org/xss.html
     *
     * @param mixed $str     string or array
     * @param bool  $isImage Is checking an image?
     *
     * @return mixed array|string
     */
    public function clean($str, $isImage = false)
    {
        // Is the string an array?
        if (is_array($str)) {
  
            foreach ($str as $key => $value) {
                $str[$key] = $this->clean($str[$key], $isImage);
            }

            return $str;
        }

        // No need to clean if numeric characters...
        if (is_numeric($str)) {
            return $str;
        }

        // Remove Invisible Characters.
        if (false === $isImage) {

            // We cannot remove invisable characters because that Photoshop 2018 will add invisble value (URL-encode: %01)
            // to the fields, that breaks the checking result, so just ignore this check for that stupid...
            $str = $this->removeInvisibleCharacters($str);
        }

        /*
         * URL Decode
         *
         * Just in case stuff like this is submitted:
         *
         * <a href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">Google</a>
         *
         * Note: Use rawurldecode() so it does not remove plus signs
         */
        do {
            $str = rawurldecode($str);
        } while (preg_match('/%[0-9a-f]{2,}/i', $str));

        /*
         * Convert character entities to ASCII
         *
         * This permits our tests below to work reliably.
         * We only convert entities that are within tags since
         * these are the ones that will pose security problems.
         */
        $str = preg_replace_callback(
            "/[^a-z0-9>]+[a-z0-9]+=([\'\"]).*?\\1/si", 
            [$this, 'convertAttribute'], 
            $str
        );

        $str = preg_replace_callback(
            '/<\w+.*/si',
            [$this, 'decodeEntity'],
            $str
        );

        // Remove Invisible Characters Again!
        if (false === $isImage) {
            $str = $this->removeInvisibleCharacters($str);
        }

        /*
         * Convert all tabs to spaces
         *
         * This prevents strings like this: ja	vascript
         * NOTE: we deal with spaces between characters later.
         * NOTE: preg_replace was found to be amazingly slow here on
         * large blocks of data, so we use str_replace.
         */
        $str = str_replace("\t", ' ', $str);

        // Remove Strings that are never allowed
        $str = $this->doNeverAllowed($str);

        /*
         * Makes PHP tags safe
         *
         * Note: XML tags are inadvertently replaced too:
         *
         * <?xml
         *
         * But it doesn't seem to pose a problem.
         */
        $str = str_replace(['<?', '?>'], ['&lt;?', '?&gt;'], $str);

        /*
         * Compact any exploded words
         *
         * This corrects words like:  j a v a s c r i p t
         * These words are compacted back to their correct state.
         */
        $words = [
            'javascript', 'expression', 'vbscript', 'jscript', 'wscript',
            'vbs',        'script',     'base64',   'applet',  'alert', 
            'document',   'write',      'cookie',   'window',  'confirm',
            'prompt',     'eval',
        ];

        foreach ($words as $word) {
            $word = implode('\s*', str_split($word)) . '\s*';

            // We only want to do this when it is followed by a non-word character
            // That way valid stuff like "dealer to" does not become "dealerto"
            $str = preg_replace_callback(
                '#(' . substr($word, 0, -3) . ')(\W)#is', 
                [$this, 'compactExplodedWords'], 
                $str
            );
        }

        /*
         * Remove disallowed Javascript in links or img tags
         * We used to do some version comparisons and use of stripos(),
         * but it is dog slow compared to these simplified non-capturing
         * preg_match(), especially if the pattern exists in the string
         *
         * Note: It was reported that not only space characters, but all in
         * the following pattern can be parsed as separators between a tag name
         * and its attributes: [\d\s"\'`;,\/\=\(\x00\x0B\x09\x0C]
         * ... however, removeInvisibleCharacters() above already strips the
         * hex-encoded ones, so we'll skip them below.
         */
        do {
            $original = $str;

            if (preg_match('/<a/i', $str)) {

                $str = preg_replace_callback(
                    '#<a[^a-z0-9>]+([^>]*?)(?:>|$)#si',
                    [$this, 'jsLinkRemoval'],
                    $str
                );
            }

            if (preg_match('/<img/i', $str)) {
                $str = preg_replace_callback(
                    '#<img[^a-z0-9]+([^>]*?)(?:\s?/?>|$)#si',
                    [$this, 'jsImgRemoval'],
                    $str
                );
            }

            if (preg_match('/script|xss/i', $str)) {
                $str = preg_replace(
                    '#</*(?:script|xss).*?>#si',
                    '[removed]',
                    $str
                );
            }

        } while($original !== $str);

        unset($original);

        // Remove evil attributes such as style, onclick and xmlns
		$str = $this->removeEvilAttributes($str, $isImage);

        /*
         * Sanitize naughty HTML elements
         *
         * If a tag containing any of the words in the list
         * below is found, the tag gets converted to entities.
         *
         * So this: <blink>
         * Becomes: &lt;blink&gt;
         */
        $pattern = '#'

            // tag start and name, followed by a non-tag character
            . '<((?<slash>/*\s*)(?<tagName>[a-z0-9]+)(?=[^a-z0-9]|$)'

            // a valid attribute character immediately after the tag would count as a separator
            . '[^\s\042\047a-z0-9>/=]*'

            //--- optional attributes ---//

            // non-attribute characters, excluding > (tag close) for obvious reasons
            . '(?<attributes>(?:[\s\042\047/=]*'

            // attribute characters
            . '[^\s\042\047>/=]+'

            //--- optional attribute-value ---//

            // attribute-value separator
            . '(?:\s*='

            // single, double or non-quoted value
            . '(?:[^\s\042\047=><`]+|\s*\042[^\042]*\042|\s*\047[^\047]*\047|\s*(?U:[^\s\042\047=><`]*))'

            // end optional attribute-value group
            . ')?'

            // end optional attributes group
            . ')*)'
            . '[^>]*)(?<closeTag>\>)?' 
            . '#isS';

        /*
         * Note: It would be nice to optimize this for speed, BUT
         * only matching the naughty elements here results in
         * false positives and in turn - vulnerabilities!
         */
        do {

            $old_str = $str;
            $str = preg_replace_callback(
                $pattern,
                [$this, 'sanitizeNaughtyHtml'],
                $str
            );

        } while ($old_str !== $str);

        unset($old_str);

        /*
         * Sanitize naughty scripting elements
         *
         * Similar to above, only instead of looking for
         * tags it looks for PHP and JavaScript commands
         * that are disallowed.  Rather than removing the
         * code, it simply converts the parenthesis to entities
         * rendering the code un-executable.
         *
         * For example:	eval('some code')
         * Becomes:		eval&#40;'some code'&#41;
         */
        $str = preg_replace(
            '#(alert|prompt|confirm|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si',
            '\\1\\2&#40;\\3&#41;',
            $str
        );

        // Final clean up
        // This adds a bit of extra precaution in case
        // something got through the above filters
        $str = $this->doNeverAllowed($str);

        /*
         * Images are Handled in a Special Way
         * - Essentially, we want to know that after all of the character
         * conversion is done whether any unwanted, likely XSS, code was found.
         * If not, we return TRUE, as the image is clean.
         * However, if the string post-conversion does not matched the
         * string post-removal of XSS, then it fails, as there was unwanted XSS
         * code found and removed/changed during processing.
         */
        return $str;
    }

    /**
     * Check if an image contains XSS code.
     *
     * @param string $imageAbsPath Absolute path of an image,
     *
     * @return bool
     */
    public function checkImage(string $imageAbsPath): bool
    {
        $originExif = @exif_read_data($imageAbsPath);
        $filteredExif = [];

        if (!empty($originExif)) {
            $filteredExif = $this->clean($originExif, true);
        }

        return ($originExif == $filteredExif);
    }

    /**
     * This prevents sandwiching null characters
     * between ascii characters, like Java\0script.
     *
     * @param string $str
     * @param bool   $urlEncoded
     *
     * @return string
     */
    protected function removeInvisibleCharacters($str, $urlEncoded = true): string
    {
        $nonDisplayables = [];
        
        // every control character except newline (dec 10)
        // carriage return (dec 13), and horizontal tab (dec 09)
        if ($urlEncoded) {

            // url encoded 00-08, 11, 12, 14, 15
            $nonDisplayables[] = '/%0[0-8bcef]/'; 

            // url encoded 16-31
            $nonDisplayables[] = '/%1[0-9a-f]/';  
        }
        
        // 00-08, 11, 12, 14-31, 127
        $nonDisplayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';

        do {
            $str = preg_replace($nonDisplayables, '', $str, -1, $count);
        } while ($count);

        return $str;
    }

    /*
	 * Remove Evil HTML Attributes (like evenhandlers and style)
	 *
	 * It removes the evil attribute and either:
	 * 	- Everything up until a space
	 *		For example, everything between the pipes:
	 *		<a |style=document.write('hello');alert('world');| class=link>
	 * 	- Everything inside the quotes
	 *		For example, everything between the pipes:
	 *		<a |style="document.write('hello'); alert('world');"| class="link">
	 *
	 * @param string $str The string to check
	 * @param boolean $is_image TRUE if this is an image
	 * @return string The string with the evil attributes removed
	 */
	protected function removeEvilAttributes($str, $is_image)
	{
		// All javascript event handlers (e.g. onload, onclick, onmouseover), style, and xmlns
		$evilAttributes = array('on\w*', 'style', 'xmlns', 'formaction', 'form', 'xlink:href');

		if ($is_image) {
			/*
			 * Adobe Photoshop puts XML metadata into JFIF images,
			 * including namespacing, so we have to allow this for images.
			 */
			unset($evilAttributes[array_search('xmlns', $evilAttributes)]);
		}

		do {
			$count = 0;
			$attribs = array();

			// find occurrences of illegal attribute strings with quotes (042 and 047 are octal quotes)
			preg_match_all(
                '/(?<!\w)(' . implode('|', $evilAttributes) . ')\s*=\s*(\042|\047)([^\\2]*?)(\\2)/is',
                $str,
                $matches,
                PREG_SET_ORDER
            );

			foreach ($matches as $attr) {
				$attribs[] = preg_quote($attr[0], '/');
			}

			// find occurrences of illegal attribute strings without quotes
			preg_match_all(
                '/(?<!\w)(' . implode('|', $evilAttributes) . ')\s*=\s*([^\s>]*)/is',
                $str,
                $matches,
                PREG_SET_ORDER
            );

			foreach ($matches as $attr) {
				$attribs[] = preg_quote($attr[0], '/');
			}

			// replace illegal attribute strings that are inside an html tag
			if (count($attribs) > 0) {
				$str = preg_replace(
                    '/(<?)(\/?[^><]+?)([^A-Za-z<>\-])(.*?)(' . implode('|', $attribs) . ')(.*?)([\s><]?)([><]*)/i',
                    '$1$2 $4$6$7$8',
                    $str,
                    -1,
                    $count
                );
			}

		} while ($count);

		return $str;
	}

    /**
     * Random Hash for protecting URLs
     *
     * @return	string
     */
    public function hash(): string
    {
        if ($this->hash == '') {
            mt_srand();
            $rand = time() + mt_rand(0, 1999999999);
            $this->hash = md5((string) $rand);
        }

        return $this->hash;
    }

    /**
     * HTML Entities Decode
     *
     * This function is a replacement for html_entity_decode()
     *
     * The reason we are not using html_entity_decode() by itself is because
     * while it is not technically correct to leave out the semicolon
     * at the end of an entity most browsers will still interpret the entity
     * correctly.  html_entity_decode() does not convert entities without
     * semicolons, so we are left with our own little solution here. Bummer.
     *
     * @param string $str
     * @param string $charset
     *
     * @return string
     */
    public function entityDecode(string $str, string $charset = 'UTF-8'): string
    {
        $this->charset = $charset;

        if (strpos($str, '&') === false) {
            return $str;
        }

        static $_entities;

        $flag = $this->isPHP('5.4')
            ? ENT_COMPAT | ENT_HTML5
            : ENT_COMPAT;

        do {

            $strCompare = $str;

            // Decode standard entities, avoiding false positives

            /*
            if (preg_match_all('/\&[a-z]{2,}(?![a-z;])/i', $str, $matches)) {
                if (!isset($_entities)) {
                    $_entities = array_map(
                        'strtolower',
                        $this->isPHP('5.3.4')
                            ? get_html_translation_table(HTML_ENTITIES, $flag, $this->charset)
                            : get_html_translation_table(HTML_ENTITIES, $flag)
                    );

                    // If we're not on PHP 5.4+, add the possibly dangerous HTML 5
                    // entities to the array manually
                    if ($flag === ENT_COMPAT) {
                        $_entities[':']  = '&colon;';
                        $_entities['(']  = '&lpar;';
                        $_entities[')']  = '&rpar;';
                        $_entities["\n"] = '&newline;';
                        $_entities["\t"] = '&tab;';
                    }
                }

                $replace = [];
                $matches = array_unique(array_map('strtolower', $matches[0]));

                foreach ($matches as &$match) {
                    if (($char = array_search($match.';', $_entities, true)) !== false) {
                        $replace[$match] = $char;
                    }
                }

                $str = str_ireplace(array_keys($replace), array_values($replace), $str);
            }

            */

            // Decode numeric & UTF16 two byte entities
            $str = html_entity_decode(
                preg_replace('/(&#(?:x0*[0-9a-f]{2,5}(?![0-9a-f;])|(?:0*\d{2,4}(?![0-9;]))))/iS', '$1;', $str),
                $flag,
                $this->charset
            );

        } while ($strCompare !== $str);

        return $str;
    }

    /**
     * Filename Security
     *
     * @param string $str
     * @param bool   $relativePath
     *
     * @return string
     */
    public function sanitizeFilename(string $str, bool $relativePath = false): string
    {
        $bad = [
            '../', '<!--', '-->', '<', '>',
            "'", '"', '&', '$', '#',
            '{', '}', '[', ']', '=',
            ';', '?', '%20', '%22',
            '%3c',		// <
            '%253c',	// <
            '%3e',		// >
            '%0e',		// >
            '%28',		// (
            '%29',		// )
            '%2528',	// (
            '%26',		// &
            '%24',		// $
            '%3f',		// ?
            '%3b',		// ;
            '%3d'		// =
        ];

        if (!$relativePath) {
            $bad[] = './';
            $bad[] = '/';
        }

        $str = $this->removeInvisibleCharacters($str, false);

        do {
            $old = $str;
            $str = str_replace($bad, '', $str);
        } while ($old !== $str);

        return stripslashes($str);
    }

    /**
     * Compact Exploded Words
     *
     * Callback function for clean() to remove whitespace from
     * things like j a v a s c r i p t
     *
     * @param array $matches
     *
     * @return string
     */
    protected function compactExplodedWords(array $matches): string
    {
        return preg_replace('/\s+/s', '', $matches[1]) . $matches[2];
    }

    /**
     * Sanitize Naughty HTML
     *
     * Callback function for clean() to remove naughty HTML elements
     *
     * @param array $matches
     *
     * @return string
     */
    protected function sanitizeNaughtyHtml(array $matches): string
    {
        static $naughtyTags = [
            'alert',    'prompt', 'confirm',    'applet',  'audio',
            'basefont', 'base',   'behavior',   'bgsound', 'blink', 
            'body',     'embed',  'expression', 'form',    'frameset', 
            'frame',    'head',   'html',       'ilayer',  'iframe', 
            'input',    'button', 'select',     'isindex', 'layer', 
            'link',     'meta',   'keygen',     'object',  'plaintext',
            'style',    'script', 'textarea',   'title',   'math',
            'video',    'svg',    'xml',        'xss',
        ];

        static $evilAttributes = [
            'on\w+', 'style',      'xmlns',     'seekSegmentTime', 
            'form',  'xlink:href', 'FSCommand', 'formaction',
        ];

        // First, escape unclosed tags
        if (empty($matches['closeTag'])) {
            return '&lt;' . $matches[1];

        } elseif (in_array(strtolower($matches['tagName']), $naughtyTags, true)) {
            // Is the element that we caught naughty? If so, escape it
            return '&lt;' . $matches[1] . '&gt;';

        } elseif (isset($matches['attributes'])) {
            // For other tags, see if their attributes are "evil" and strip those
            // We'll store the already fitlered attributes here
            $attributes = [];

            // Attribute-catching pattern
            $attributesPattern = '#'
                .'(?<name>[^\s\042\047>/=]+)' // attribute characters
                // optional attribute-value
                .'(?:\s*=(?<value>[^\s\042\047=><`]+|\s*\042[^\042]*\042|\s*\047[^\047]*\047|\s*(?U:[^\s\042\047=><`]*)))' // attribute-value separator
                .'#i';

            // Blacklist pattern for evil attribute names
            $isEvilPattern = '#^(' . implode('|', $evilAttributes) . ')$#i';

            // Each iteration filters a single attribute
            do {
                // Strip any non-alpha characters that may preceed an attribute.
                // Browsers often parse these incorrectly and that has been a
                // of numerous XSS issues we've had.
                $matches['attributes'] = preg_replace('#^[^a-z]+#i', '', $matches['attributes']);

                if (!preg_match($attributesPattern, $matches['attributes'], $attribute, PREG_OFFSET_CAPTURE)) {
                    // No (valid) attribute found? Discard everything else inside the tag
                    break;
                }

                if (
                    // Is it indeed an "evil" attribute?
                    preg_match($isEvilPattern, $attribute['name'][0]) ||
                    // Or does it have an equals sign, but no value and not quoted? Strip that too!
                    (trim($attribute['value'][0]) === '')            
                ) {
                    // @codeCoverageIgnoreStart
                    $attributes[] = 'xss=removed';
                    // @codeCoverageIgnoreEnd
                } else {
                    $attributes[] = $attribute[0][0];
                }

                $matches['attributes'] = substr($matches['attributes'], $attribute[0][1] + strlen($attribute[0][0]));
            }
            while ($matches['attributes'] !== '');

            $attributes = empty($attributes)
                ? ''
                : ' '.implode(' ', $attributes);
            return '<' . $matches['slash'] . $matches['tagName'] . $attributes . '>';
        }

        // @codeCoverageIgnoreStart
        return $matches[0];
        // @codeCoverageIgnoreEnd
    }

    /**
     * JS Link Removal
     *
     * Callback function for clean() to sanitize links
     * This limits the PCRE backtracks, making it more performance friendly
     * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
     * PHP 5.2+ on link-heavy strings
     *
     * @param array $match
     *
     * @return string
     */
    protected function jsLinkRemoval(array $match): string
    {
        return str_replace(
            $match[1],
            preg_replace(
                '#href=.*?(?:(?:alert|prompt|confirm)(?:\(|&\#40;)|javascript:|livescript:|mocha:|charset=|window\.|document\.|\.cookie|<script|<xss|data\s*:)#si',
                '',
                $this->filterAttributes($match[1])
            ),
            $match[0]
        );
    }

    /**
     * JS Image Removal
     *
     * Callback function for clean() to sanitize image tags
     * This limits the PCRE backtracks, making it more performance friendly
     * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
     * PHP 5.2+ on image tag heavy strings
     *
     * @param array $match
     *
     * @return string
     */
    protected function jsImgRemoval(array $match): string
    {
        return str_replace(
            $match[1],
            preg_replace(
                '#src=.*?(?:(?:alert|prompt|confirm|eval)(?:\(|&\#40;)|javascript:|livescript:|mocha:|charset=|window\.|document\.|\.cookie|<script|<xss|base64\s*,)#si',
                '',
                $this->filterAttributes($match[1])
            ),
            $match[0]
        );
    }

    /**
     * Attribute Conversion
     *
     * Used as a callback for XSS Clean
     *
     * @param array
     *
     * @return string
     */
    protected function convertAttribute(array $match): string
    {
        return str_replace(['>', '<', '\\'], ['&gt;', '&lt;', '\\\\'], $match[0]);
    }

    /**
     * Filter Attributes
     *
     * Filters tag attributes for consistency and safety
     *
     * @param string $str
     *
     * @return string
     */
    protected function filterAttributes(string $str): string
    {
        $out = '';

        if (preg_match_all('#\s*[a-z\-]+\s*=\s*(\042|\047)([^\\1]*?)\\1#is', $str, $matches)) {
            foreach ($matches[0] as $match) {
                $out .= preg_replace("#/\*.*?\*/#s", '', $match);
            }
        }

        return $out;
    }

    /**
     * HTML Entity Decode Callback
     *
     * Used as a callback for XSS Clean
     *
     * @param array $match
     *
     * @return string
     */
    protected function decodeEntity(array $match): string
    {
        // Protect GET variables in URLs
        // 901119URL5918AMP18930PROTECT8198
        $match = preg_replace('|\&([a-z\_0-9\-]+)\=([a-z\_0-9\-/]+)|i', $this->hash() . '\\1=\\2', $match[0]);

        // Decode, then un-protect URL GET vars
        return str_replace(
            $this->hash(),
            '&',
            $this->entityDecode($match, strtoupper($this->charset))
        );
    }

    /**
     * Do Never Allowed.
     *
     * A utility function for clean()
     *
     * @param string $str
     *
     * @return string
     */
    protected function doNeverAllowed(string $str): string
    {
        $str = str_replace(
            array_keys($this->deniedStringList),
            $this->deniedStringList,
            $str
        );

        foreach ($this->deniedRegexList as $regex) {
            $str = preg_replace('#' . $regex . '#is', '[removed]', $str);
        }

        return $str;
    }

    /**
     * Determines if the PHP version being used is greater than the supplied version number.
     *
     * @param string $version
     *
     * @return bool
     */
    protected function isPHP($version): bool
	{
        static $_isPHP;

		$version = (string) $version;

		if (!isset($_isPHP[$version])) {
			$_isPHP[$version] = version_compare(PHP_VERSION, $version, '>=');
		}

		return $_isPHP[$version];
	}
}

