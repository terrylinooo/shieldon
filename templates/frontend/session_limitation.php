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

$imgsrc = 'https://shieldon-io.github.io/static/icons/icon-clock_96x96.png';

?>
<!DOCTYPE html>
<html lang="<?php echo $langCode ?>">
<head>
    <meta charset="utf-8">
	<link rel="icon" href="data:,">
    <meta name="robots" content="noindex, nofollow">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php _e('core', 'limit_title', 'Please line up'); ?></title>
    <?php echo '<style>' . $css . '</style>'; ?>
</head>
<body>
    <div id="wrapper" class="wrapper">
		<div class="inner">
			<div class="card">
				<div class="card-header"><?php _e('core', 'limit_heading', 'Please line up') ?></div>
				<div class="card-body">
					<div class="status-container">
						<div class="status-icon">
							<img src="<?php echo $imgsrc; ?>">
						</div>
						<div class="status-message">
                            <?php _e('core', 'limit_message', 'This page is limiting the number of people online. Please wait a moment.'); ?>
						</div>
					</div>
 
                    <div class="status-info">
                        <?php _e('core', 'lineup_info', '', ['<strong>' . $this->sessionStatus['queue'] . '</strong>']); ?><br />

                        <?php if ($showOnlineInformation) : ?>
                            <?php _e('core', 'online_info', '', ['<strong>' . $this->sessionStatus['count'] . '</strong>']); ?><br />
                        <?php endif; ?>

                        <br /><small><?php _e('core', 'keepalive_info', '', [$this->isLimitSession[1]]); ?></small>

                    </div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>