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

<div class="so-dashboard">
    <div id="so-rule-table-form" class="so-datatables">
        <div class="so-datatable-heading">
            <?php _e('panel', 'xss_heading', 'XSS Protection'); ?>
        </div>
        <div class="so-datatable-description">
            <?php _e('panel', 'xss_description', 'Prevent Cross site scripting (XSS) attacks.'); ?>
        </div>
        <div class="so-setting-page">
            <table class="setting-table">
                <form method="post">
                <tbody>
                    <?php echo $this->fieldCsrf(); ?>
                    <tr>
                        <td class="r1">POST</td>
                        <td class="r2">
                            <br />
                            <label class="rocker rocker-sm">
                                <input type="hidden" name="xss_protection__post" value="off" />
                                <input type="checkbox"
                                    name="xss_protection__post"
                                    class="toggle-block"
                                    value="on" <?php $this->checked('xss_protection.post', true); ?>
                                >
                                <span class="switch-left"><i>ON</i></span>
                                <span class="switch-right"><i>OFF</i></span>
                            </label>
                            <p>
                                <?php
                                _e(
                                    'panel',
                                    'xss_text_filter_post_variables',
                                    'Filter all POST method variables.'
                                );
                                ?>
                            </p>
                        </td>
                    </tr>
                    <tr class="border-top">
                        <td class="r1">GET</td>
                        <td class="r2">
                            <br />
                            <label class="rocker rocker-sm">
                                <input type="hidden" name="xss_protection__get" value="off" />
                                <input type="checkbox"
                                    name="xss_protection__get"
                                    class="toggle-block" value="on" <?php $this->checked('xss_protection.get', true); ?>
                                >
                                <span class="switch-left"><i>ON</i></span>
                                <span class="switch-right"><i>OFF</i></span>
                            </label>
                            <p>
                                <?php
                                _e(
                                    'panel',
                                    'xss_text_filter_get_variables',
                                    'Filter all GET method variables.'
                                );
                                ?>
                            </p>
                        </td>
                    </tr>
                    <tr class="border-top">
                        <td class="r1">COOKIE</td>
                        <td class="r2">
                            <br />
                            <label class="rocker rocker-sm">
                                <input type="hidden" name="xss_protection__cookie" value="off" />
                                <?php // phpcs:ignore ?>
                                <input type="checkbox" name="xss_protection__cookie" class="toggle-block" value="on" <?php $this->checked('xss_protection.cookie', true); ?> />
                                <span class="switch-left"><i>ON</i></span>
                                <span class="switch-right"><i>OFF</i></span>
                            </label>
                            <p>
                                <?php
                                _e(
                                    'panel',
                                    'xss_text_filter_cookie_variables',
                                    'Filter all COOKIE method variables.'
                                );
                                ?>
                            </p>
                        </td>
                    </tr>
                    <tr class="border-top">
                        <td></td>
                        <td class="py-3">
                            <input type="hidden" name="xss_form_1" value="page">
                            <input type="hidden" name="order" value="">
                            <input type="submit" name="submit" id="btn-update" class="btn-shieldon" value="Update">
                            &nbsp;&nbsp;
                            <span class="text-muted">
                                <?php _e('panel', 'xss_text_update_above_settings', 'Update above settings.'); ?>
                            </span>
                        </td>
                    </tr>
                </tbody>
                </form>
                <tbody>
                    <tr class="border-top">
                        <td class="r1"><?php _e('panel', 'xss_label_single_variable', 'Single variable'); ?></td>
                        <td class="r2">
                            <br />
                            <form method="post">
                            <div class="so-rule-form">
                                <div class="d-inline-block align-top">
                                    <label for="variable">
                                        <?php _e('panel', 'xss_label_variable_name', 'Variable Name'); ?>
                                    </label>
                                    <br />
                                    <input name="variable" type="text" value="" id="variable" class="regular-text">
                                    <span class="form-text text-muted">e.g. <code>post_content</code></span>
                                </div>
                                <div class="d-inline-block align-top">
                                    <label for="type"><?php _e('panel', 'table_label_type', 'Type'); ?></label><br />
                                    <select name="type" class="regular" id="type">
                                        <option value="post">POST</option>
                                        <option value="get">GET</option>
                                        <option value="cookie">COOKIE</option>
                                    </select>
                                </div>
                                <div class="d-inline-block align-top">
                                    <label>&nbsp;</label><br />
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="order" value="">
                                    <input type="hidden" name="xss_form_2" value="page">
                                    <input type="submit"
                                        name="submit"
                                        id="btn-add-rule"
                                        class="button button-primary"
                                        value="<?php _e('panel', 'auth_btn_submit', 'Submit'); ?>">
                                </div>
                            </div>
                            </form>
                            <p>
                                <?php
                                _e(
                                    'panel',
                                    'xss_text_eradicate_injection',
                                    'Eradicate potential injection string for single variable.'
                                );
                                ?>
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <br />
    <?php if (empty($xss_protected_list)) : ?>
    <div id="so-table-container" class="so-datatables">
        <table id="so-datalog" class="cell-border compact stripe responsive" cellspacing="0" width="100%">
            <tbody>
                <tr>
                    <td>
                        <?php _e('panel', 'ipma_text_nodata', 'No data is available now.'); ?>
                    </td>
                </tr>
            </tbdoy>
        </table>
    </div>
    <?php else : ?>
    <div id="so-table-loading" class="so-datatables">
        <div class="lds-css ng-scope">
            <div class="lds-ripple">
                <div></div>
                <div></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div id="so-table-container" class="so-datatables" style="display: none;">
        <table id="so-datalog" class="cell-border compact stripe responsive" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th><?php _e('panel', 'table_label_type', 'Type'); ?></th>
                    <th><?php _e('panel', 'xss_label_variable', 'Variable'); ?></th>
                    <th><?php _e('panel', 'table_label_remove', 'Remove'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($xss_protected_list)) : ?>
                    <?php foreach ($xss_protected_list as $i => $info) : ?>
                    <tr>
                        <td>
                            <?php echo $info['type']; ?>
                        </td>
                        <td id="variable-text-<?php echo $i; ?>">
                            <?php echo $info['variable']; ?>
                        </td>
                        <td>
                            <button type="button" class="button btn-remove-ip" data-order="<?php echo $i; ?>">
                                <i class="far fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>   
        </table>
    </div>
</div>

<script>

    $(function() {
        
        $('#so-datalog').DataTable({
            'responsive': true,
            'pageLength': 25,
            'initComplete': function(settings, json) {
                $('#so-table-loading').hide();
                $('#so-table-container').fadeOut(800);
                $('#so-table-container').fadeIn(800);
            }
        });

        $('.so-dashboard').on('click', '.btn-remove-ip', function() {
            var order = $(this).attr('data-order');
            var variable = $('#variable-text-' + order).html();

            $('[name=order]').val(order);
            $('[name=variable]').val(variable);
            $('[name=action]').val('remove');
            $('#btn-add-rule').trigger('click');
        });
    });

</script>