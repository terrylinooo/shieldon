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

$tab = $_POST['tab'] ?? 'manager';

?> 

<input id="tab1" type="radio" name="tabs" class="tab" value="manager" <?php $this->checked($tab, 'manager', false); ?> />
<label for="tab1" class="tab">
    <i class="fas fa-dice-d20"></i> <?php _e('panel', 'tab_heading_iptables_manager', 'Manager'); ?>
</label>

<input id="tab2" type="radio" name="tabs" class="tab" value="status" <?php $this->checked($tab, 'status', false); ?> />
<label for="tab2" class="tab">
<i class="far fa-eye"></i> <?php _e('panel', 'tab_heading_iptables_status', 'Status'); ?>
</label>

