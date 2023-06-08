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

defined('SHIELDON_VIEW') || die('Illegal access');

use function Shieldon\Firewall\_e;

$tab = $_POST['tab'] ?? 'events';

?> 

<input id="tab1" type="radio" name="tabs" class="tab" value="events" <?php $this->checked($tab, 'events', false); ?> />
<label for="tab1" class="tab">
    <i class="fas fa-shield-alt"></i> <?php _e('panel', 'tab_heading_events', 'Events'); ?>
</label>

<input id="tab2" type="radio" name="tabs" class="tab" 
    value="modules" <?php $this->checked($tab, 'modules', false); ?> 
/>
<label for="tab2" class="tab">
    <i class="fab fa-facebook-messenger"></i> <?php _e('panel', 'tab_heading_modules', 'Modules'); ?>
</label>

