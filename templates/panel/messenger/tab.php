<?php defined('SHIELDON_VIEW') || exit('Life is short, why are you wasting time?');
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use function Shieldon\Helper\_e;

$tab = $_POST['tab'] ?? 'events';

?> 

<input id="tab1" type="radio" name="tabs" class="tab" value="events" <?php $this->checked($tab, 'events', false); ?> />
<label for="tab1" class="tab">
    <i class="fas fa-shield-alt"></i> <?php _e('panel', 'tab_heading_events', 'Events'); ?>
</label>

<input id="tab2" type="radio" name="tabs" class="tab" value="modules" <?php $this->checked($tab, 'moduless', false); ?> />
<label for="tab2" class="tab">
    <i class="fab fa-facebook-messenger"></i> <?php _e('panel', 'tab_heading_modules', 'Modules'); ?>
</label>

