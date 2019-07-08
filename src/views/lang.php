<?php defined('SHIELDON_VIEW') || exit('Life is short, why are you wasting time?');

$langCode = $langCode ?? 'en';

switch ($langCode) {

    case 'zh_CN':

        $lang['deny.title']    = '禁止连线！';
        $lang['deny.heading']  = '禁止连线！';
        $lang['deny.message']  = '您的IP位址已被封锁。';
        $lang['limit.title']   = '请排队！';
        $lang['limit.heading'] = '请排队！';
        $lang['limit.message'] = '这个网站正限制在线浏览人数。请稍候。';
        $lang['stop.title']    = '请解决 CAPTCHA 验证';
        $lang['stop.heading']  = '偵測到不尋常的行為...';
        $lang['stop.message']  = '请完成 CAPTCHA 验证确认您是人类。';
        $lang['stop.submit']   = '送出';
        $lang['credit']        = '这个网站由 %s 开源专案防护。';
        $lang['lineup_info']   = '您的号码牌：%s。';
        $lang['online_info']   = '在线人数： %s。';
        break;

    case 'zh_HK':
    case 'zh_TW':
    case 'zh':

		$lang['deny.title']    = '禁止連線！';
		$lang['deny.heading']  = '禁止連線！';
        $lang['deny.message']  = '您的 IP 位址已被封鎖。';
        $lang['limit.title']   = '請排隊！';
		$lang['limit.heading'] = '請排隊！';
        $lang['limit.message'] = '這個網站正限制在線瀏覽人數。請稍候。';
        $lang['stop.title']    = '請解決 Captcha 驗證';
		$lang['stop.heading']  = '偵測到不尋常的行為...';
		$lang['stop.message']  = '請完成 CAPTCHA 驗證確認您是人類。';
        $lang['stop.submit']   = '送出';
        $lang['credit']        = '這個網站由 %s 開源專案防護。';
        $lang['lineup_info']   = '您的號碼牌：%s。';
        $lang['online_info']   = '線上人數： %s。';
        break;

    case 'en_UK':
    case 'en_US':
    case 'en':
    default:

		$lang['deny.title']    = 'Access denied!';
		$lang['deny.heading']  = 'Access denied!';
        $lang['deny.message']  = 'The IP address you are using has been blocked.';
        $lang['limit.title']   = 'Please line up!';
		$lang['limit.heading'] = 'Please line up!';
        $lang['limit.message'] = 'This page is limiting the number of people online. Please wait a moment.';
        $lang['stop.title']    = 'Please solve Captcha';
		$lang['stop.heading']  = 'Unusual behavior detected...';
		$lang['stop.message']  = 'Please complete the CAPTCHA to confirm you are a human.';
        $lang['stop.submit']   = 'Submit';
        $lang['credit']        = 'This website is protected by %s open source project.';
        $lang['lineup_info']   = 'Your number: %1s.';
        $lang['online_info']   = 'Online: %s.';
}

return $lang;