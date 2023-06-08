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

$imgsrc = 'https://shieldon-io.github.io/static/icons/icon-warning_96x96.png';

?>
<!DOCTYPE html>
<html lang="<?php echo $langCode ?>">
<head>
    <meta charset="utf-8">
    <link rel="icon" href="data:,">
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php _e('core', 'deny_title', 'Access denied') ?></title>
    <?php echo '<style>' . $css . '</style>'; ?>
</head>
<body>
    <div id="wrapper" class="wrapper">
        <div class="inner">
            <div class="card">
                <div class="card-header"><?php _e('core', 'deny_heading', 'Access denied'); ?></div>
                <div class="card-body">
                    <div class="status-container">
                        <div class="status-icon">
                            <img src="<?php echo $imgsrc; ?>">
                        </div>
                        <div class="status-message">
                            <p>
                                <?php _e('core', 'deny_message', 'The IP address you are using has been blocked.') ?>
                            </p>
                            <div>
                                <?php if (!empty($uiInfo['is_display_display_http_code'])) : ?>
                                    <span class="http-status-code"><?php echo $uiInfo['http_status_code']; ?></span>
                                <?php endif; ?>
                                <?php if (!empty($uiInfo['is_display_display_reason_code'])) : ?>
                                    <span class="reason-code"><?php echo $uiInfo['reason_code']; ?></span>
                                <?php endif; ?>
                                <?php if (!empty($uiInfo['is_display_display_reason_text'])) : ?>
                                    <?php echo $uiInfo['reason_text']; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($uiInfo['is_display_user_information'])) : ?>
                        <div class="status-user-info">
                            <?php foreach ($dialoguserinfo as $key => $userinfo) : ?>
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
    <?php echo $performanceReport; ?>
</body>
</html>


