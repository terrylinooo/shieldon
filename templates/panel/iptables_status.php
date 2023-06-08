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

?>

<div class="so-dashboard">
    <div id="so-rule-table-form" class="so-datatables">
        <div class="so-datatable-heading">
            <?php echo $type; ?> <?php _e('panel', 'tab_heading_iptables_status', 'Status'); ?>
        </div>
        <?php if ('IPv4' === $type) : ?>
        <div class="so-datatable-description">
            <?php
                _e(
                    'panel',
                    'iptable_status_description',
                    'The following text is the result of command <code>iptables -L</code>.'
                );
            ?>
        </div>
        <?php endif; ?>
        <?php if ('IPv6' === $type) : ?>
        <div class="so-datatable-description">
            <?php
                _e(
                    'panel',
                    'ip6table_status_description',
                    'The following text is the result of command <code>ip6tables -L</code>.'
                );
            ?>
        </div>
        <?php endif; ?>
    </div>
    <br />
    <div class="so-datatables">
        <?php if (!empty($last_cached_time)) : ?>
            <?php _e('panel', 'log_label_cache_time', 'Report generated time'); ?>:
            <strong class="text-info">
                <?php echo $last_cached_time; ?>
            </strong>
            &nbsp;&nbsp;&nbsp;&nbsp; 
        <?php endif; ?>
        <?php if (!empty($ipStatus)) : ?>
            <pre><?php echo nl2br($ipStatus); ?></pre>
        <?php endif; ?>
    </div>
</div>