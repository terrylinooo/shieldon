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
            <?php _e('panel', 'auth_heading', 'Authentication'); ?>
            <br />
        </div>
        <div class="so-datatable-description">
            <?php
            _e(
                'panel',
                'auth_description',
                'The HTTP WWW-Authenticate response header defines the authentication method 
                that should be used to gain access to a resource.'
            );
            ?>
            <br />
        </div>
        <div class="so-rule-form iptables-form">
            <form method="post" onsubmit="freezeUI();">
                <div class="d-inline-block align-top">
                    <label for="url-path"><?php _e('panel', 'auth_label_url_path', 'URL Path'); ?></label><br />
                    <input name="url" type="text" value="" id="url-path" class="regular-text"><br />
                    <span class="form-text text-muted">e.g. <code>/wp-admin/</code></span>
                </div>
                <div class="d-inline-block align-top">
                    <label for="username"><?php _e('panel', 'auth_label_username', 'Username'); ?></label><br />
                    <input name="user" type="text" value="" id="username">
                </div>
                <div class="d-inline-block align-top">
                    <label for="password"><?php _e('panel', 'auth_label_password', 'Password'); ?></label><br />
                    <input name="pass" type="text" value="" id="password">
                </div>
                <div class="d-inline-block align-top">
                    <label>&nbsp;</label><br />
                    <?php echo $this->fieldCsrf(); ?>
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="order" value="">
                    <input type="submit"
                        name="submit"
                        id="btn-add-rule"
                        class="button button-primary"
                        value="<?php _e('panel', 'auth_btn_submit', 'Submit'); ?>"
                    >
                </div>
            </form>
        </div>
    </div>
    <br />
    <?php if (empty($authentication_list)) : ?>
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
                    <th>
                        <?php _e('panel', 'auth_label_url_path', 'URL Path'); ?>
                    </th>
                    <th>
                        <?php _e('panel', 'auth_label_username', 'Username'); ?>
                    </th>
                    <th>
                        <?php _e('panel', 'auth_label_password', 'Password'); ?>
                        (<?php _e('panel', 'auth_label_encrypted', 'encrypted'); ?>)
                    </th>
                    <th>
                        <?php _e('panel', 'auth_label_remove', 'Remove'); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($authentication_list)) : ?>
                    <?php foreach ($authentication_list as $i => $authInfo) : ?>
                    <tr>
                        <td>
                            <?php echo $authInfo['url']; ?>
                        </td>
                        <td>
                            <?php echo $authInfo['user']; ?>
                        </td>
                        <td>
                            <?php echo $authInfo['pass']; ?>
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

            $('[name=order]').val(order);
            $('[name=action]').val('remove');
            $('#btn-add-rule').trigger('click');
        });
    });

</script>