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
            Exclusion<br />
        </div>
        <div class="so-datatable-description">
            Please enter the begin with URLs you want them excluded from Shieldon protection.<br />
        </div>
        <div class="so-rule-form">
            <form method="post">
                <div class="d-inline-block align-top">
                    <label for="url-path">URL Path</label><br />
                    <input name="url" id="url-path" type="text" value="" class="regular-text">
                    <span class="form-text text-muted">e.g. <code>/url-path/</code></span>
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
                    <th>Remove</th>
                </tr>
            </thead>
            <tbody>
                <?php if (! empty($exclusion_list)) : ?>
                <?php foreach($exclusion_list as $i => $urlInfo) : ?>
                <tr>
                    <td><?php echo $urlInfo['url']; ?></td>
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