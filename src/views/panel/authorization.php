<?php defined('SHIELDON_VIEW') || exit('Life is short, why are you wasting time?');
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$timezone = '';

?>

<div class="so-dashboard">
	<div id="so-rule-table-form" class="so-datatables">
		<div class="so-datatable-heading">
            Authorization<br />
		</div>
		<div class="so-datatable-description">
            The HTTP WWW-Authenticate response header defines the authentication method that should be used to gain access to a resource.<br />
		</div>
		<div class="so-rule-form">
			<form method="post">
				<div class="form-group">
					<label for="url-path" class="col-sm-2 col-form-label text-right">URL Path</label>
					<input name="url" type="text" value="" id="url-path" class="regular-text">
					<span class="form-text text-muted offset-sm-2">e.g. <code>/wp-admin/</code></span>
				</div>
				<div class="form-group">
					<label for="ip-address" class="col-sm-2 col-form-label text-right">Username</label>
					<input name="ip" type="text" value="" id="ip-address" class="regular-text ">
				</div>
				<div class="form-group">
					<label for="ip-address" class="col-sm-2 col-form-label text-right">Password</label>
					<input name="ip" type="text" value="" id="ip-address" class="regular-text ">
				</div>
				<div class="form-group">
					<div class="col-sm-2 offset-sm-2">
						<input type="submit" name="submit" id="btn-add-rule" class="button button-primary" value="Submit">
					</div>
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
                    <th>Order</th>
                    <th>URL Path</th>
					<th>IP</th>
					<th>Rule</th>
					<th>Remove</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($ip_list as $i => $ipInfo) : ?>
				<tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo $ipInfo['url']; ?></td>
					<td><?php echo $ipInfo['ip']; ?></td>
					<td><?php echo $ipInfo['rule']; ?></td>
					<td><button type="button" class="button btn-remove-ip" data-ip="<?php echo $ipInfo['ip']; ?>" data-order="<?php echo ($i + 1); ?>"><i class="far fa-trash-alt"></i></button></td>
				</tr>
				<?php endforeach; ?>
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
            var ip = $(this).attr('data-ip');

            $('[name=ip]').val(ip);
			$('[name=order]').val(order);
			$('[name=action]').val('remove');
			$('#btn-add-rule').trigger('click');
		});
	});

</script>