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

$timezone = '';

?>

<div class="so-dashboard">
    <div class="so-datatables">
        <div class="so-datatable-heading">
            <?php _e('panel', 'overview_heading_filters', 'Filters'); ?>
        </div>
        <br />
        <div class="row">
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_cookie', 'Cookie'); ?></div>
                    <div class="nums">
                        <?php echo $filter_cookie; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_cookie', 'Check whether visitors can create cookie by JavaScript.'); ?></div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_session', 'Session'); ?></div>
                    <div class="nums">
                        <?php echo $filter_session; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_session', 'Detect whether multiple sessions created by the same visitor.'); ?></div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_frequency', 'Frequency'); ?></div>
                    <div class="nums">
                        <?php echo $filter_frequency; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_frequency', 'Check how often does a visitor view the pages.'); ?></div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_referer', 'Referrer'); ?></div>
                    <div class="nums">
                        <?php echo $filter_referer; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_referer', 'Check HTTP referrer information.'); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="so-dashboard">
    <div class="so-datatables">
        <div class="so-datatable-heading">
            <?php _e('panel', 'overview_heading_components', 'Components'); ?>
        </div>
        <br />
        <div class="row">
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_ip', 'IP'); ?></div>
                    <div class="nums">
                        <?php echo $component_ip; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_ip', 'Advanced IP address mangement.'); ?></div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_trustedbot', 'Trusted Bot'); ?></div>
                    <div class="nums">
                        <?php echo $component_trustedbot; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_trustedbot', 'Allow popular search engines crawl your website.'); ?></div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_header', 'Header'); ?></div>
                    <div class="nums">
                        <?php echo $component_header; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_header', 'Analyze header information from visitors.'); ?></div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_rdns', 'RDNS'); ?></div>
                    <div class="nums">
                        <?php echo $component_rdns; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_rdns', 'Identify IP resolved hostname (RDNS) from visitors.'); ?></div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_useragent', 'User Agent'); ?></div>
                    <div class="nums">
                        <?php echo $component_useragent; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_useragent', 'Analysis user-agent information from visitors.'); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>