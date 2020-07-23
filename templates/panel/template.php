<?php
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

defined('SHIELDON_VIEW') || die('Illegal access');

use function Shieldon\Firewall\_e;

// A 32x32 favicon for firewall panel.
$favicon = <<<EOF
AAABAAEAICAAAAEAIACoEAAAFgAAACgAAAAgAAAAQAAAAAEAIAAAAAAAABAAAAAAAAAAAAAAAAAAAAA
AAABA/wAAQP8AAED/AABA/wAAQP8AADX/AABQ/AoAo9A+AJXNOACF2y4Amsg7AJ7ONwCZzTcAlNAxAK
fAMXanvDzjn8Uy3ay5NGKcyj0Alsc8AKTHPACK3SwAm8lAAIvlHAA5/wAAPv8AAED/AABA/wAAQP8AA
ED/AABA/wAAQP8AAED/AABA/wAAQP8AAED/AABA/wAANf8AAFD8CgCj0D4Alc04AIXbLgCbyDsAoM82
AK21OH2kwDnwmco7/5nLO/+dyj3/m8o8/6PAOemsrzxipcg8AIrdKwCayUAAi+UcADn/AAA+/wAAQP8
AAED/AABA/wAAQP8AAED/AABA/wAAQP8AAED/AABA/wAAQP8AAED/AABEZQAARGUAAERlAABEZQAARG
UAAK+sOx+ovTjfl8w7/5fMO/+Yyjn/irc2/428N/+Xyzn/mMw6/5fLO/+stTzQssQsAjMzMwAzMzMAM
zMzADMzMwAzMzMAMzMzADMzMwAzMzMAQP8AAED/AABA/wAAQP8AAED/AABA/wAAQP8AAERlAABEZQAA
RGUAAERlAACtwzFnm8Y6/ZfLO/+YzDr/iLY1/3mfNP90lzX/dJc1/36oNf+PwDf/mMw6/5jLO/+dxzj
4s6dESDMzMwAzMzMAMzMzADMzMwAzMzMAMzMzADMzMwBA/wAAQP8AAED/AABA/wAAQP8AAED/AABA/w
AARGUAAERlAABEZQAAq7E7iprLO/+YzDr/kcI4/3ujM/90lzX/d5o4/3eaOP93mjj/dpk3/3SWNP+Dr
TT/lco5/5rKO/+ov0X/sM0eaDMzMwAzMzMAMzMzADMzMwAzMzMAMzMzAED/AABA/wAANv8AADb/AAA2
/wAANv8AADb/AABEZQAARGUAALW3PoScxz3/mMw6/4m3Nv90lzP/dpk3/3eaOP93mjj/d5o4/3eaOP9
3mjj/d5o4/3WYNv96oTP/ksQ3/5zKPP+WzDr/o9QhXTMzMwAzMzMAMzMzADMzMwAzMzMAPf8AAD3/AA
Bi1ygAYtcoAGLXKABi1ygAYdYoAERlAACkziZSm846/5rLO/+GsTb/c5Y0/3eaOP93mjj/d5o4/3eaO
P93mjj/d5o4/3eaOP93mjj/d5o4/3eZN/92mjP/jsI2/5vKPP+8tlH/rc0oLDMzMwAzMzMAMzMzADMz
MwA5/wAAOf8AAJK4RQCSuEUAkrhFAJK4RQCQt0UAp6BHCqa9RP2ZzTn/ibg2/3OWNP93mjj/d5o4/3e
aOP93mjj/d5o4/3eaOP93mjj/d5o4/3eaOP93mjj/d5o4/3eaOP92mjP/lMY4/5vKPP+ZyTX4MzMzAD
MzMwAzMzMAMzMzAJXZNgCV2TYAs8pGALPKRgCzykYAtctGAKfRPgCnwDfhmcw6/4y8N/9zljP/d5o4/
3eaOP93mjj/d5o4/3eaOP93mjj/d5o4/3eaOP93mjj/kLRr/7TKl/+NtWz/caJG/3aZN/96oDP/lcs3
/53KPf+yrkTHMzMzADMzMwAzMzMAp8FEAKfBRABq6xoAausaADMzMwAzMzMAsMA1YaHIQP+Xyzr/gqs
0/3aaN/93mjj/d5o4/3eaOP93mjj/d5o4/3eaOP93mjj/d5o4/4KtXf/Z48j/2ePI/9njyP/W4cX/nc
KF/3WYNv+ErzX/mMw6/5bNOP+uqzs8MzMzADMzMwCbwz0Am8M9AGrhJgBq4SYAMzMzADMzMwCRzS/4o
cg//469N/+GsTb/ibU5/4CoOf93mjj/d5o4/3eaOP93mjj/d5o4/3eaOP93mjj/yNm0/8TZs/95o0n/
p8OJ/9njyP/Z48j/psSL/3ebPf+Rwzf/l805/6W+PO4zMzMAMzMzAKLJQQCiyUEAh98xAIjfMQAzMzM
Asro+f6PEQf+Tyjf/hrI1/4q2Of+Ktjn/lcNg/7LRj/+avHb/jK9i/3uiR/93mjj/d5o4/3ebOf/Z48
j/udSp/3eaOP93mjj/fadR/9njyP/N3Lr/fKlU/3ylNP+Xyzr/l805/7GyO1wzMzMAncw7AJ3MOwCG2
CkAh9kpADMzMwCYyDH1nMo9/4u7Nf+Hsjb/irY5/5LCX//e68j/3uvI/97qyP/b5cj/2OLH/9bhw/+A
qlb/d5o4/4WsW//Z48j/kLp1/3eaOP+CplD/2ePI/9Lfwf+CrFr/dJc1/46/N/+WyT7/psI06jMzMwC
x6RgAsOgZAG7eJgBm3SYAqMYtVaPHP/+WzDn/irc4/4q2Of+KvEz/3uvI/97ryP/e68j/3uvI/97ryP
/b5sj/2ePI/4q4cf93mjj/d5o4/3ecO/91mzn/d5o4/7fLmf/Z48j/vtSq/3ynUP93mTf/faU0/5fKO
/+c0Df/qbA+N4K+RACGwEIAfrpGAHS4RgCavjrQns45/5LDOv+Ktjn/irY5/9Dir//e68j/3uvI/97r
yP/e68j/3uvI/97ryP/c5sj/o8iR/6DDh//F1q7/ibJn/3qhRv+Xu3j/2ePI/9njyP+jxIr/d59B/3e
aOP90lzX/kcQ3/5nMOf+htkO9jMJKAI7DSABu3iEAedslAKDFPP2Zyzv/jbw5/4q2Of+cxGP/3uvI/9
7ryP/e68j/3uvI/97ryP/e68j/3uvI/97ryP/b5sj/2ePI/9njyP/Z48j/2ePI/9njyP/Z48j/2ePI/
4q0av93mjj/d5o4/3aZN/+EsTX/l8s7/6PHOPqb0jQAmdE0AHjhJgCpzClimcs6/5fHO/+Ktjn/lLxK
/7rUi//e68j/3uvI/97ryP/e68j/3uvI/97ryP/e68j/3uvI/97ryP/b5cj/2ePI/9njyP/Z48j/2eP
I/9njyP/Z48j/halV/3eaOP93mjj/d5o4/3mfNP+Wyjn/mMw6/6yyN0qRzDYAqsRLALGxQ8GXzDn/ks
M6/4q2Of+gxF7/x9yg/97ryP/e68j/3uvI/97ryP/e68j/3uvI/97ryP/e68j/3uvI/97ryP/Z5Mf/2
ePI/9njyP/Z48j/2ePI/8zatv94mzr/d5o4/3eaOP93mjj/dJc1/5DCN/+YzDr/rLc7sZvNPACk3SoA
pME47ZfKPP+Ovjn/irY5/6HFYf/I3aP/3uvI/97ryP/e68j/3uvI/97ryP/e68j/3uvI/97ryP/e68j
/3uvI/6jJc/+QsWX/wdau/9njyP/Y48f/q8GF/3eaOP93mjj/d5o4/3eaOP91mDb/h7Q1/5jMOv+lvj
rmmc07AMW2Hgqdyjf+lso9/4u5Of+Ktjn/msBU/8HYlv/e68j/3uvI/97ryP/e68j/3uvI/97ryP/e6
8j/3uvI/97ryP/F25z/irY5/4OrOv+xz5//2ePI/9jjx/+Hpk//d5o4/3eaOP93mjj/d5o4/3eaOP9/
qDX/mMw6/5zHOfyfxD8Ara5EUJbMOv+XyTn/irY5/4q2Of+Ktjr/rs12/97ryP/e68j/3uvI/97ryP/
e68j/3uvI/97ryP/e68j/3uvI/5vBV/+qym//3OnG/9rkyP/Z48j/vM2d/3eaOP93mjj/d5o4/3eaOP
93mjj/d5o4/3meNf+XyTn/m8o8/6bNJT+sujeLmss7/5PFOv+Ktjn/irY5/4q2Of+VvUz/3erG/97ry
P/e68j/3uvI/97ryP/e68j/3uvI/97ryP/V5bj/yt6m/97ryP/e68j/3urI/9njyP+VsWT/d5o4/3ea
OP93mjj/d5o4/3eaOP93mjj/dJc1/5LDOP+ayjz/p8UrgZ/KKrWgyD//kcE6/4q2Of+Ktjn/irY5/4q
2Of+81Y3/3uvI/97ryP/e68j/3uvI/97ryP/e68j/3uvI/97ryP/e68j/3uvI/97ryP/e68j/wdOj/3
eaOP93mjj/d5o4/3eaOP93mjj/d5o4/3eaOP91mDb/jLo2/5vPN/+jrkavqLg70JfMOv+Pvzn/irY5/
4q2Of+Ktjn/irY5/424Pv/S47T/3uvI/97ryP/e68j/3uvI/97ryP/e68j/3uvI/97ryP/e68j/3uvI
/97ryP+UvVn/d5o4/3eaOP93mjj/d5o4/3eaOP93mjj/d5o4/3aZN/+Hszb/mc05/6S5PcumvDvemMw
6/428Of+Ktjn/irY5/4q2Of+Ktjn/irY5/5K7Rv/Q4rH/3OrG/97ryP/e68j/3uvI/97ryP/e68j/3u
vI/97ryP/e68j/s9eW/4q3Of95nDj/d5o4/3eaOP93mjj/d5o4/3eaOP93mjj/dZg2/4CqNf+ZzTn/p
rs7257IMemeyD//kMA5/4y5Of+LuDn/irY5/4q2Of+Ktjn/irY5/4y4Pf+szXf/y9+n/9Hisf+/15P/
nsVl/6LNff/Q47T/3uvI/93qxv+Lu0n/irY5/4CnOf93mjj/dpk3/3WYNv90lzX/dZg1/3idNf96oTX
/iLQ2/5XIPv+qwzPooMU6+5bMOv+YzDr/m8o8/5nLO/+WyTr/k8U6/5C/Of+MuTn/irY5/4q2Of+OuU
D/k7xJ/4y3PP+Ktjn/irY5/467Sv+Ww2H/kb5S/4q2Of+Ktjn/hK44/3WWOP99ozf/g602/4u6N/+Tx
Tn/mMo6/5vKPP+YzDr/k8g9/6bLNfuowTOdrLM/xKi5O9ChwzbnnsU5+ZfMOv+byjz/m8o8/5bMOf+W
yDr/kcE6/4y5Of+Ktjn/irY5/4q2Of+Ktjn/irY5/4q2Of+Ktjn/irY5/427Of+QwDr/jbs5/5bLOf+
cyjz/m8o8/5fMOf+exTn4ocM256i6Os6uuzfDprRAnn3eJwCqw0YAMzMzADMzMwAzMzMAsa0+P6i8M5
ijwzTaocQ9+5nMOv+ZzTr/mMw6/5XHOv+Pvjn/irc5/4q2Of+Ktjn/i7g5/5DBOf+WyTr/mMw6/5fMO
v+XzDv/oMQ8+6HDNNmpuzWXr607PpHQNgCC3CoAmcs7AKPSNQCLwUIAgdsqAKfERAAzMzMAMzMzADMz
MwAzMzMAMzMzADMzMwC3qkAEqbM6caW6OtScyTn+mso8/5vKPP+Wyjr/kME5/5LDOv+XzDr/m8o8/5r
LPP+byDr+q7w5066zOXGuqUACgdsqAIXXLQCcxj4AktA2AITaLACZyzsAodE2AI3DQQCB2yoAp8REAJ
rKPACE2isAltE2AKDHQACE1S0AitkvAKPLPwCOxzoAksg8ALWvOBmuujWZnsU27ZnJPf+YzDv/mMw7/
5nJPP+fxTbtqro2mLCwOxeh0ToAmcw4AJXFQACF2SwAiNUvAJvHPQCS0DYAhNosAJnLOwCh0TYAjcNB
AIHbKgCnxEQAmso8AITaKwCW0TYAoMdAAITVLQCK2S8Ao8s/AI/IOgCTyDwAo806AJDYLQCA2ioAua8
zVKa9ONyovTncurAzVYHbKQCJ1TAAmco8AKDQOgCZzDgAlsVAAIXZLACI1S8Am8c9AJLQNgCE2iwAmc
s7AKHRNgCNw0EA//w////wD///wAP//4AB//8AAP/+AAB//AAAP/gAAD/4AAAf8AAAD/AAAA/gAAAH4
AAAB8AAAAPAAAADwAAAA4AAAAGAAAABgAAAAQAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
AAAAAPgAAB//AAD//+AH///8P/8=
EOF;

$staticSrc = 'https://shieldon-io.github.io/static';

// `project.lo` is the virtual domain that Terry is using to test Shieldon library.
if (
    isset($_SERVER['HTTP_HOST']) && 
    strpos($_SERVER['HTTP_HOST'], 'project.lo') !== false
)  {
    // `shieldon-doc.lo` is the virtual domain that Terry is using to design CSS for Firewall Panel UI.
    $staticSrc = 'http://shieldon-doc.lo/static';
}

?><!doctype html>
<html lang="<?php echo $this->locate; ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <script src="<?php echo $staticSrc; ?>/third-party/jquery/jquery.min.js"></script>
        <script src="<?php echo $staticSrc; ?>/third-party/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="<?php echo $staticSrc; ?>/third-party/datatables/datatables.min.js"></script>
        <script src="<?php echo $staticSrc; ?>/third-party/apexcharts/apexcharts.min.js"></script>
        <link rel="stylesheet" href="<?php echo $staticSrc; ?>/third-party/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?php echo $staticSrc; ?>/third-party/datatables/datatables.min.css">
        <link rel="stylesheet" href="<?php echo $staticSrc; ?>/third-party/fontawesome/css/all.css">
        <link rel="stylesheet" href="<?php echo $staticSrc; ?>/css/firewall-ui.css?v=<?php echo date('Ymd'); ?>">
        <link rel="icon" type="image/x-icon" href="data:image/x-icon;base64,<?php echo $favicon; ?>">
        <title><?php echo $title; ?></title>
    </head>
    <body>

        <nav class="navbar navbar-expand-md navbar-dark shadow-md">
            
            <a class="navbar-brand" href="#">
                <img src="https://shieldon-io.github.io/static/images/logo.png" class="logo-image">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#top-navbar" aria-controls="top-navbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="top-navbar">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item"><?php _e('panel', 'channel', 'Channel'); ?> <strong class="status-field"><?php echo $channel_name; ?></strong></li>
                    <li class="nav-item"><?php _e('panel', 'mode', 'Mode'); ?> <strong class="status-field"><?php echo $mode_name; ?></strong></li>
                    <li class="nav-item"><a href="<?php echo $this->url('user/logout') ?>" class="nav-link"><?php _e('panel', 'logout', 'Logout'); ?></a></li>
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown"><?php _e('panel', 'nav_locale', 'Locale'); ?></a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a href="#" data-lang="en" class="dropdown-item" onclick="selectLanguage(this, event);" role="button">English</a>
                            <a href="#" data-lang="zh" class="dropdown-item" onclick="selectLanguage(this, event);" role="button">中文</a>
                            <a href="#" data-lang="zh_CN" class="dropdown-item" onclick="selectLanguage(this, event);" role="button">简体中文</a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="container-fluid">
            <div class="row">
                <div class="col-md-2 col-sm-1 col-xs-1 so-sidebar-menu">
                    <ul class="nav flex-column parent-menu">
                        <li>
                            <a href="#">
                                <i class="fas fa-cog"></i>
                                <span><?php _e('panel', 'menu_status', 'Status'); ?></span>
                            </a>
                            <ul class="nav child-menu">
                                <li>
                                    <a href="<?php echo $this->url('home/overview'); ?>">
                                        <i class="fas fa-tachometer-alt"></i>
                                        <span><?php _e('panel', 'menu_overview', 'Overview'); ?></span>
                                    </a>
                                    <li>
                                    <a href="<?php echo $this->url('report/operation'); ?>">
                                        <i class="fas fa-fan"></i>
                                        <span><?php _e('panel', 'menu_operation_status', 'Operation'); ?></span>
                                    </a>
                                </li>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#">
                                <i class="fas fa-table"></i>
                                <span><?php _e('panel', 'menu_data_circle', 'Data Circle'); ?></span>
                            </a>
                            <ul class="nav child-menu">
                                <li>
                                    <a href="<?php echo $this->url('circle/filter'); ?>">
                                        <i class="fas fa-chart-area"></i>
                                        <span><?php _e('panel', 'menu_ip_filter_logs', 'Filter Logs'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('circle/rule'); ?>">
                                        <i class="fas fa-fire-alt"></i>
                                        <span><?php _e('panel', 'menu_ip_rules', 'IP Rules'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('circle/session'); ?>">
                                        <i class="fas fa-user-clock"></i>
                                        <span><?php _e('panel', 'menu_sessions', 'Sessions'); ?>
                                    </a>
                                </li>
                             </ul>
                        </li>
                        
                        <?php if ($this->getConfig('iptables.enable') === true) : ?>
                        <li>
                            <a href="#">
                                <i class="fas fa-shield-alt"></i>
                                <span><?php _e('panel', 'menu_iptables_ipv4', 'IPv4 iptables'); ?></span>
                            </a>
                            <ul class="nav child-menu">
                                <li>
                                    <a href="<?php echo $this->url('iptables/ip4'); ?>">
                                        <i class="fas fa-dice-d20"></i>
                                        <span><?php _e('panel', 'menu_iptables_manager', 'Manager'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('iptables/ip4status'); ?>">
                                        <i class="far fa-question-circle"></i>
                                        <span><?php _e('panel', 'menu_iptables_status', 'Status'); ?></span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#">
                                <i class="fas fa-shield-alt"></i>
                                <span><?php _e('panel', 'menu_iptables_ipv6', 'IPv6 iptables'); ?></span>
                            </a>
                            <ul class="nav child-menu">
                                <li>
                                    <a href="<?php echo $this->url('iptables/ip6'); ?>">
                                     <i class="fas fa-dice-d20"></i>
                                        <span><?php _e('panel', 'menu_iptables_manager', 'Manager'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('iptables/ip6status'); ?>">
                                        <i class="far fa-question-circle"></i>
                                        <span><?php _e('panel', 'menu_iptables_status', 'Status'); ?></span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <?php endif; ?>
                        <?php if ($this->getConfig('loggers.action.enable') === true) : ?>
                        <li>
                            <a href="#">
                                <i class="fas fa-chart-area"></i>
                                <span><?php _e('panel', 'menu_action_logs', 'Logs'); ?></span>
                            </a>
                            <ul class="nav child-menu">
                                <li>
                                    <a href="<?php echo $this->url('report/actionLog'); ?>?tab=today">
                                        <i class="far fa-calendar-check"></i>
                                        <span><?php _e('panel', 'menu_today', 'Today'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('report/actionLog'); ?>?tab=yesterday">
                                        <i class="fas fa-calendar-day"></i>
                                        <span><?php _e('panel', 'menu_yesterday', 'Yesterday'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('report/actionLog'); ?>?tab=past_seven_days">
                                        <i class="fas fa-calendar-week"></i>
                                        <span><?php _e('panel', 'menu_last_7_days', 'Last 7 days'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('report/actionLog'); ?>?tab=this_month">
                                        <i class="far fa-calendar-alt"></i>
                                        <span><?php _e('panel', 'menu_this_month', 'This month'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('report/actionLog'); ?>?tab=last_month">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span><?php _e('panel', 'menu_last_month', 'Last month'); ?></span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <?php endif; ?>
                        <li>
                            <a href="#">
                                <i class="fas fa-fire-alt"></i>
                                <span><?php _e('panel', 'menu_firewall', 'Firewall'); ?></span>
                            </a>
                            <ul class="nav child-menu">
                                <li>
                                    <a href="<?php echo $this->url('setting/basic'); ?>">
                                        <i class="fas fa-cogs"></i>
                                        <span><?php _e('panel', 'menu_settings', 'Settings'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('setting/ipManager'); ?>">
                                        <i class="fas fa-globe"></i>
                                        <span><?php _e('panel', 'menu_ip_manager', 'IP Manager'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('security/xssProtection'); ?>">
                                        <i class="fas fa-umbrella"></i>
                                        <span><?php _e('panel', 'menu_xss_protection', 'XSS Protection'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('security/authentication'); ?>">
                                        <i class="fas fa-user-lock"></i>
                                        <span><?php _e('panel', 'menu_authentication', 'Authentication'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('setting/exclusion'); ?>">
                                        <i class="fas fa-eye-slash"></i>
                                        <span><?php _e('panel', 'menu_exclusion', 'Exclusion'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('setting/messenger'); ?>">
                                        <i class="fab fa-facebook-messenger"></i>
                                        <span><?php _e('panel', 'menu_messenger', 'Messenger'); ?></span>
                                    </a>
                                </li>   
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="col-md-10 col-sm-11 col-xs-11 so-content">
                    <?php echo $content; ?>
                </div>
            </div>
        </div>
        <?php if (! empty($this->messages)) : ?>
        <div id="message-modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lightbox" role="document">
                <div class="modal-content">
                    <div class="modal-header <?php echo (count($this->messages) == 1 ? $this->messages[0]['class'] : 'info'); ?>">
                        <div class="icon-wrapper">
                            <?php if (count($this->messages) == 1) : ?>
                                <div class="icon-box">
                                <?php if ($this->messages[0]['type'] === 'success') : ?>
                                    <i class="fas fa-check-circle"></i>
                                <?php endif; ?>
                                <?php if ($this->messages[0]['type'] === 'error') : ?>
                                    <i class="fas fa-times-circle"></i>
                                <?php endif; ?>
                            </div>
                            <?php else: ?>
                                <div class="icon-box">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?php foreach ($this->messages as $msgInfo) : ?>
                            <p class="text-<?php echo $msgInfo['class']; ?>">
                                <?php echo $msgInfo['text']; ?>
                            </p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <script> $('#message-modal').modal(); </script>
        <?php endif; ?>

        <script>

            function freezeUI() {
                $('#loader').attr('data-status', 'loading');
            }

            function unFreezeUI() {
                $('#loader').attr('data-status', 'waiting');
            }

            $(function() {

                var currentUrl = window.location.href.split('#')[0];

                $('.so-sidebar-menu').find('a[href="' + currentUrl + '"]').parent('li').addClass('active');
                $('.so-sidebar-menu').find('a').filter(function () {
                    return this.href == currentUrl;
                }).parent('li').addClass('active').parents('ul').slideDown().parent().addClass('current-page');

                $('.so-sidebar-menu a').click(function () {
                    if ($(this).parent('li').hasClass('active')) {
                        $(this).parent().removeClass('active');
                        if ($(this).closest('ul').hasClass('child-menu')) {
                            $(this).closest('ul').slideUp(500);
                        }
                    } else {
                        $(this).parent('li').addClass('active').parents('ul').slideDown(500).parent().addClass('active');
                    }
                });

                var selectLanguage = function (obj, event) {
                    event.preventDefault();
                    var langCode = $(obj).attr('data-lang');
                    var url = '<?php echo $this->url('ajax/changeLocale'); ?>';
  
                    $.ajax({
                        url: url,
                        type: 'get',
                        data: {'langCode': langCode},
                        dataType: 'json',
                        cache: false,
                        success: function (data) { 
                            if (data.status === 'success') {
                                console.log(data);
                                location.reload();
                            }
                        }
                    }); 
                };

                window.selectLanguage = selectLanguage;
            });

        </script>
        <div class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-2">
                        <a href="https://github.com/terrylinooo/shieldon" target="_blank"><i class="fab fa-github"></i></a>
                        <?php echo SHIELDON_FIREWALL_VERSION; ?>
                    </div>
                    <div class="col-md-10">
                        Powered by <a href="https://shieldon.io" target="_blank">Shieldon</a> 
                        &copy; 2019-<?php echo date('Y'); ?> <a href="https://terryl.in" target="_blank">Terry Lin</a>
                    </div>
                </div>
            </div>
        </div>
        <div id="loader" data-status="waiting">
            <div class="cssload-box-loading"></div>
        </div>
    </body>
</html>
