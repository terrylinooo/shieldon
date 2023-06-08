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

$timezone = '';

?>
<form method="post">

<div class="so-setting-page">
    <div class="so-tab">
        <?php $this->loadViewPart('panel/messenger/tab'); ?>
            
        <section id="content1" class="tab-section">
            <?php $this->loadViewPart('panel/messenger/events'); ?>
        </section>

        <section id="content2" class="tab-section">
            <?php $this->loadViewPart('panel/messenger/modules'); ?>
        </section>
    </div>
    <div class="d-flex justify-content-center py-2">
        <button type="submit" class="btn btn-enter">
            <i class="fas fa-fire-alt"></i> <?php _e('panel', 'overview_btn_save', 'SAVE'); ?>
        </button>
    </div>
</div>
<?php echo $this->fieldCsrf(); ?>
<input type="hidden" name="tab" value="messenger-setting">
<input type="hidden" name="managed_by" value="firewall">

</form>

<?php

$this->loadViewPart('panel/js/common');

