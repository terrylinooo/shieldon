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
    <div class="so-flex">
		<div class="so-board">
			<div class="board-field left icon icon-1">
                <i class="fas fa-clipboard-check"></i>
			</div>
			<div class="board-field right">
				<div class="heading">Limit</div>
				<div class="nums"><?php echo $session_limit_count; ?></div>
				<div class="note">Online session limit.</div>
			</div>
        </div>

		<div class="so-board">
			<div class="board-field left icon icon-2">
                <i class="far fa-clock"></i>
			</div>
			<div class="board-field right">
				<div class="heading">Period</div>
				<div class="nums"><?php echo number_format($session_limit_period ); ?></div>
				<div class="note">Keep-alive period. (minutes)</div>
			</div>
		</div>
		<div class="so-board">
			<div class="board-field left icon icon-3">
                <i class="fas fa-street-view"></i>
			</div>
			<div class="board-field right">
				<div class="heading">Online</div>
				<div class="nums"><?php echo number_format($online_count ); ?></div>
				<div class="note">Online session amount.</div>
			</div>
		</div>
    </div>
	<div id="so-table-loading" class="so-datatables">
		<div class="lds-css ng-scope">
			<div class="lds-ripple">
				<div></div>
				<div></div>
			</div>
		</div>
	</div>
	<div id="so-table-container" class="so-datatables" style="display: none;">
		<div class="so-databable-heading">
            Session Table
        </div>
		<div class="so-datatable-description">
            Read-time logs for <strong>Online Session Controll</strong>. All processes are automatic and instant, you can ignore that.<br />
			Notice this is only working when you have enabled that function.
		</div>
		<table id="so-datalog" class="cell-border compact stripe" cellspacing="0" width="100%">
			<thead>
				<tr>
                    <th>Priority</th>
                    <th>Status</th>
					<th>Session ID</th>
					<th>IP</th>
                    <th>Time</th>
                    <th>Remain seconds</th>
				</tr>
			</thead>
			<tbody>

                <?php $i = 1; ?>
                <?php foreach($session_list as $key => $session_info ) : ?>
                    <?php

                        $remains_time = $expires - (time() - $session_info['time']);

                        if ($remains_time < 1 ) {
                            $remains_time = 0;
                        }

                        if ($i < $session_limit_count ) {
                            $satus_name = 'Allowable';

                            if ($remains_time < 1 ) {
                                $satus_name = 'Expired';
                            }
                        } else {
                            $satus_name = 'Waiting';
                        }

                    ?>
                    <tr>
                        <td title="Key: <?php echo $key ?>"><?php echo $i; ?></td>
                        <td><?php echo $satus_name; ?></td>
                        <td><?php echo $session_info['id']; ?></td>
                        <td><?php echo $session_info['ip']; ?></td>
                        <td><?php echo date('Y-m-d H:i:s', $session_info['time']); ?></td>
                        <th><?php echo $remains_time; ?></th>
                    </tr>
                    <?php $i++; ?>
				<?php endforeach; ?>
			</tbody>   
		</table>
    </div>
</div>

<script>

    $(function() {
        $('#so-datalog').DataTable({
            'pageLength': 25,
            'initComplete': function(settings, json ) {
                $('#so-table-loading').hide();
                $('#so-table-container').fadeOut(800);
                $('#so-table-container').fadeIn(800);
            }
        });
    });

</script>