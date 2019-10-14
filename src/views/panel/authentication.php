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

$timezone = '';

?>

<div class="so-dashboard">
    <div id="so-rule-table-form" class="so-datatables">
        <div class="so-datatable-heading">
            Authentication<br />
        </div>
        <div class="so-datatable-description">
            The HTTP WWW-Authenticate response header defines the authentication method that should be used to gain access to a resource.<br />
        </div>
        <div class="so-rule-form">
            <form method="post">
                <div class="d-inline-block align-top">
                    <label for="url-path">URL Path</label><br />
                    <input name="url" type="text" value="" id="url-path" class="regular-text"><br />
                    <span class="form-text text-muted">e.g. <code>/wp-admin/</code></span>
                </div>
                <div class="d-inline-block align-top">
                    <label for="username">Username</label><br />
                    <input name="user" type="text" value="" id="username" class="regular-text">
                </div>
                <div class="d-inline-block align-top">
                    <label for="password">Password</label><br />
                    <input name="pass" type="text" value="" id="password" class="regular-text">
                </div>
                <div class="d-inline-block align-top">
                    <label>&nbsp;</label><br />
                    <?php $this->_csrf(); ?>
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="order" value="">
                    <input type="submit" name="submit" id="btn-add-rule" class="button button-primary" value="Submit">
                </div>
            </form>
        </div>
    </div>
    <br />
    <div id="so-table-loading" class="so-datatables">
        <div class="lds-css ng-scope">
            <div class="lds-ripple">
                <div></div>
                <div></div>
            </div>
        </div>
    </div>
    <div id="so-table-container" class="so-datatables" style="display: none;">
        <table id="so-datalog" class="cell-border compact stripe" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>URL Path</th>
                    <th>Username</th>
                    <th>Password (encrypted)</th>
                    <th>Remove</th>
                </tr>
            </thead>
            <tbody>
                <?php if (! empty($authentication_list)) : ?>
                <?php foreach($authentication_list as $i => $authInfo) : ?>
                <tr>
                    <td><?php echo $authInfo['url']; ?></td>
                    <td><?php echo $authInfo['user']; ?></td>
                    <td><?php echo $authInfo['pass']; ?></td>
                    <td><button type="button" class="button btn-remove-ip" data-order="<?php echo $i; ?>"><i class="far fa-trash-alt"></i></button></td>
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