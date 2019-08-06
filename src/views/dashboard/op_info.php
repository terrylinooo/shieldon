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
    <div id="so-table-container" class="so-datatables">
        <div class="so-databable-heading">
            Operation Infomation
        </div>
		<div class="row">
            <div class="col-sm-4">
                
            </div>
            <div class="col-sm-4">

            </div>
            <div class="col-sm-4">

            </div>
        </div>
    </div>
</div>
<div class="so-dashboard">
    <div id="so-table-container-2" class="so-datatables">
        <div class="so-databable-heading">
            Data Circle
        </div>
    </div>
</div>
<div class="so-dashboard">
    <div id="so-table-container-2" class="so-datatables">
        <div class="so-databable-heading">
            Logger
        </div>
        <div>
            Since: <?php echo $logger_started_working_date; ?><br />
            Days: <?php echo $logger_work_days; ?><br />
            Size: <?php echo $logger_total_size; ?><br />
        </div>
    </div>
</div>