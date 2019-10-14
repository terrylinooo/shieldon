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
use function Shieldon\Helper\mask_string;

$tab = $_POST['tab'] ?? 'daemon';

?> 

<input id="tab1" type="radio" name="tabs" class="tab" value="daemon" <?php $this->checked($tab, 'daemon', false); ?> />
<label for="tab1" class="tab">
    <i class="fas fa-shield-alt"></i> Daemon
</label>
    
<input id="tab2" type="radio" name="tabs" class="tab" value="components" <?php $this->checked($tab, 'components', false); ?> />
<label for="tab2" class="tab">
    <i class="fas fa-cubes"></i> Components
</label>
    
<input id="tab3" type="radio" name="tabs" class="tab" value="filters" <?php $this->checked($tab, 'filters', false); ?> />
<label for="tab3" class="tab">
    <i class="fas fa-ring"></i> Filters
</label>
    
<input id="tab4" type="radio" name="tabs" class="tab" value="captchas" <?php $this->checked($tab, 'captchas', false); ?> />
<label for="tab4" class="tab">
    <i class="fas fa-puzzle-piece"></i> CAPTCHAs
</label>

<input id="tab5" type="radio" name="tabs" class="tab" value="dialog_ui" <?php $this->checked($tab, 'dialog_ui', false); ?> />
<label for="tab5" class="tab">
    <i class="far fa-window-maximize"></i> Dialog UI
</label>

<input id="tab6" type="radio" name="tabs" class="tab" value="admin_login" <?php $this->checked($tab, 'admin_login', false); ?> />
<label for="tab6" class="tab">
    <i class="fas fa-user-cog"></i> Admin Login
</label>

