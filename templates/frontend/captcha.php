<?php defined('SHIELDON_VIEW') || exit('Life is short, why are you wasting time?');
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use function Shieldon\Firewall\_e;

$imgsrc = 'https://shieldon-io.github.io/static/icons/icon-secruity_96x96.png';

?>
<!DOCTYPE html>
<html lang="<?php echo $langCode ?>">
<head>
	<meta charset="utf-8">
	<link rel="icon" href="data:,">
	<meta name="robots" content="noindex, nofollow">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php _e('core', 'stop_title', 'Please solve CAPTCHA') ?></title>
	<?php echo '<style>' . $css . '</style>'; ?>
</head>
<body>
	<div id="wrapper" class="wrapper">
		<div class="inner">
			<div class="card">
				<div class="card-header"><?php _e('core', 'stop_heading', 'Unusual behavior detected') ?></div>
				<div class="card-body">
					<div class="status-container">
						<div class="status-icon">
							<img src="<?php echo $imgsrc; ?>">
						</div>
						<div class="status-message">
							<?php _e('core', 'stop_message', 'Please complete the CAPTCHA to confirm you are a human.') ?>
						</div>
			
					</div>
					<div class="captcha-container">
						<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
							<?php foreach ($this->captcha as $captcha) : ?>
								<?php echo $captcha->form(); ?>
							<?php endforeach; ?>
							<p><input type="submit" value="<?php _e('core', 'stop_submit', 'Submit') ?>" /></p>
						</form>
					</div>
					<?php if (! empty($dialoguserinfo)) : ?>
						<div class="status-user-info">
							<?php foreach($dialoguserinfo as $key => $userinfo) : ?>
								<div class="row">
									<strong><?php echo $key; ?></strong> <span><?php echo $userinfo; ?></span>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>	
				</div>
			</div>
		</div>
	</div>
</body>
</html>


			