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
<html lang="en-US">
<head>
    <meta charset="utf-8">
    <link rel="icon" href="data:,">
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $title; ?></title>
    <?php echo '<style>' . $css . '</style>'; ?>

    <style>
        
        .text-center {
            text-align: center;
        }

        .form-input {
            width: 100%;
            height: 40px;
            line-height: 40px;
            font-size: 13px;
            box-sizing: border-box;
            padding: 0 20px;
            background-color: #f1f1f1;
            border: 1px #eeeeee solid;
        }

        .input-box {
            padding: 5px 20px;
            overflow: hidden;
        }

        .btn-submit {
            width: 100%;
            height: 40px;
            line-height: 40px;
            font-size: 13px;
            color: #fff;
            font-weight: bold;
            box-sizing: border-box;
            box-shadow: inset 0px 1px 0px 0px #dcecfb;
            background: linear-gradient(to bottom, #61b0ff 5%, #4c99e0 100%);
            background-color: #61b0ff;
            border: 1px solid #84bbf3;
            text-shadow: 0px 1px 0px #528ecc;
            cursor:pointer;
        }

        .btn-submit:hover {
            background:linear-gradient(to bottom, #4c99e0 5%, #61b0ff 100%);
            background-color:#4c99e0;
        }

        .btn-submit:active {
            position: relative;
            top: 1px;
        }

        .logo {
            height: 30px;
            padding-right: 5px;
        }

        .main-content {
            padding: 10px;
        }
        
        .error-notice {
            border: 1px #eb4141 solid;
            padding: 10px;
            color: #eb4141;
            margin: 20px;
            font-weight: bold;
        }

        html {
            height: 100%;
        }

        body {
            position: relative;
            background: #23a6d5;
            height: 100%;
        }

    </style>
</head>
<body>
    <div id="wrapper" class="wrapper">
        <div class="inner">
            <div class="card">
                <div class="card-header">
                    <div class="logo-wrapper">
                        <img src="https://shieldon-io.github.io/static/images/logo.png" class="logo">
                    </div>
                </div>
                <div class="card-body">
                    <form action="<?php echo $form ?>" method="post" autocomplete="off">
                        <div class="main-content">
                            <?php if (!empty($error)) : ?>
                            <div class="error-notice">
                                <?php echo $error; ?>
                            </div>
                            <?php endif; ?>
                            <div class="input-box">
                                <input type="text" name="s_user" placeholder="Username" class="form-input" />
                            </div>
                            <div class="input-box">
                                <input type="password" name="s_pass" placeholder="Password" class="form-input" />
                            </div>
                            <?php if (!empty($captchas)) : ?>
                            <div class="input-box">
                                <?php foreach ($captchas as $captcha) : ?>
                                    <?php echo $captcha->form(); ?>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            <div class="input-box">
                                <button type="submit" class="btn-submit">
                                    <?php _e('panel', 'login_btn_login', 'Login'); ?>
                                </button>
                            </div>
                        </div>
                        <?php echo $csrf; ?>
                    </form>
                </div>
            </div>
        </div> 
    </div>
</body>
</html>
