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

$imgsrc = 'https://shieldon-io.github.io/static/icons/icon-warning_96x96.png';

?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex, nofollow">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= _e('panel', 'login_heading_login', 'Login to Firewall Panel'); ?></title>
    <?php echo '<style>' . $css . '</style>'; ?>
    <style>.text-center{text-align:center}.form-input{width:100%;height:40px;line-height:40px;font-size:13px;box-sizing:border-box;padding:0 20px;background-color:#f1f1f1;border:1px #eee solid}.input-box{padding:5px 20px;overflow:hidden}.btn-submit{width:100%;height:40px;line-height:40px;font-size:13px;color:#fff;font-weight:700;box-sizing:border-box;box-shadow:inset 0 1px 0 0 #dcecfb;background:linear-gradient(to bottom,#61b0ff 5%,#4c99e0 100%);background-color:#61b0ff;border:1px solid #84bbf3;text-shadow:0 1px 0 #528ecc;cursor:pointer}.btn-submit:hover{background:linear-gradient(to bottom,#4c99e0 5%,#61b0ff 100%);background-color:#4c99e0}.btn-submit:active{position:relative;top:1px}.logo{height:30px}.main-content{padding:10px}.error-notice{border:1px #eb4141 solid;padding:10px;color:#eb4141;margin:20px;font-weight:700}html{height:100%}body{position:relative;background:linear-gradient(-45deg,#ee7752,#389a76,#23a6d5,#23d5ab);background-size:400% 400%;animation:gradient 15s ease infinite;height:100%}@keyframes gradient{0%{background-position:0 50%}50%{background-position:100% 50%}100%{background-position:0 50%}}.bg-bubbles{position:absolute;top:0;left:0;width:100%;height:100%;z-index:-1;overflow:hidden;padding:0;margin:0}.bg-bubbles li{position:absolute;list-style:none;display:block;width:40px;height:40px;background-color:rgba(255,255,255,.15);bottom:-160px;-webkit-animation:square 25s infinite;animation:square 25s infinite;-webkit-transition-timing-function:linear;transition-timing-function:linear}.bg-bubbles li:nth-child(1){left:10%}.bg-bubbles li:nth-child(2){left:20%;width:80px;height:80px;-webkit-animation-delay:2s;animation-delay:2s;-webkit-animation-duration:17s;animation-duration:17s}.bg-bubbles li:nth-child(3){left:25%;-webkit-animation-delay:4s;animation-delay:4s}.bg-bubbles li:nth-child(4){left:40%;width:60px;height:60px;-webkit-animation-duration:22s;animation-duration:22s;background-color:rgba(255,255,255,.25)}.bg-bubbles li:nth-child(5){left:70%}.bg-bubbles li:nth-child(6){left:80%;width:120px;height:120px;-webkit-animation-delay:3s;animation-delay:3s;background-color:rgba(255,255,255,.2)}.bg-bubbles li:nth-child(7){left:32%;width:160px;height:160px;-webkit-animation-delay:7s;animation-delay:7s}.bg-bubbles li:nth-child(8){left:55%;width:20px;height:20px;-webkit-animation-delay:15s;animation-delay:15s;-webkit-animation-duration:40s;animation-duration:40s}.bg-bubbles li:nth-child(9){left:25%;width:10px;height:10px;-webkit-animation-delay:2s;animation-delay:2s;-webkit-animation-duration:40s;animation-duration:40s;background-color:rgba(255,255,255,.3)}.bg-bubbles li:nth-child(10){left:90%;width:160px;height:160px;-webkit-animation-delay:11s;animation-delay:11s}@-webkit-keyframes square{0%{-webkit-transform:translateY(0);transform:translateY(0)}100%{-webkit-transform:translateY(-700px) rotate(600deg);transform:translateY(-700px) rotate(600deg)}}@keyframes square{0%{-webkit-transform:translateY(0);transform:translateY(0)}100%{-webkit-transform:translateY(-700px) rotate(600deg);transform:translateY(-700px) rotate(600deg)}}</style>
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
                    <form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post" autocomplete="off">
                        <div class="main-content">
                            <?php if (! empty($error)) : ?>
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
                            <?php if (! empty($this->captcha)) : ?>
                            <div class="input-box">
                                <?php foreach ($this->captcha as $captcha) : ?>
                                    <?php echo $captcha->form(); ?>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            <div class="input-box">
                                <button type="submit" class="btn-submit"><?= _e('panel', 'login_btn_login', 'Login'); ?></button>
                            </div>
                        </div>
                        <?php $this->_csrf(); ?>
                    </form>
				</div>
            </div>
        </div> 
        
    </div>
    <ul class="bg-bubbles">
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
    </ul>
</body>
</html>


