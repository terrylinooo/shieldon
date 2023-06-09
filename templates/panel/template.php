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

?><!doctype html>
<html lang="<?php echo $this->locate; ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="stylesheet" href="<?php echo $css_url; ?>">
        <script src="<?php echo $js_url; ?>"></script>
        <link rel="icon" type="image/x-icon" href="<?php echo $favicon_url; ?>">

        <title><?php echo $title; ?></title>
    </head>
    <body>
        <nav class="navbar navbar-expand-md navbar-dark shadow-md sticky-top">
            <a class="navbar-brand" href="#">
                <img src="<?php echo $logo_url; ?>" class="logo-image">
            </a>
            <button class="navbar-toggler"
                type="button"
                data-toggle="collapse"
                data-target="#top-navbar"
                aria-controls="top-navbar"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="top-navbar">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a href="<?php echo $this->url('user/logout') ?>" class="nav-link">
                            <span class="f-icon">
                                <i class="fas fa-sign-out-alt"></i>
                            </span>
                            <?php _e('panel', 'logout', 'Logout'); ?>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                            <span class="f-icon">
                                <i class="fas fa-globe-americas"></i>
                            </span>
                            <?php _e('panel', 'nav_locale', 'Locale'); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a href="#" data-lang="en" class="dropdown-item"
                                onclick="selectLanguage(this, event);" role="button"
                            >English</a>
                            <a href="#" data-lang="zh" class="dropdown-item"
                                onclick="selectLanguage(this, event);" role="button"
                            >中文</a>
                            <a href="#" data-lang="zh_CN" class="dropdown-item"
                                onclick="selectLanguage(this, event);" role="button"
                            >简体中文</a>
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
                                <span class="category-icon"><i class="fas fa-cog"></i></span>
                                <span><?php _e('panel', 'menu_status', 'Status'); ?></span>
                            </a>
                            <ul class="nav child-menu">
                                <li>
                                    <a href="<?php echo $this->url('home/overview'); ?>">
                                        <span class="subcategory-icon"><i class="fas fa-tachometer-alt"></i></span>
                                        <span><?php _e('panel', 'menu_overview', 'Overview'); ?></span>
                                    </a>
                                    <li>
                                    <a href="<?php echo $this->url('report/operation'); ?>">
                                        <span class="subcategory-icon"><i class="fas fa-fan"></i></span>
                                        <span><?php _e('panel', 'menu_operation_status', 'Operation Status'); ?></span>
                                    </a>
                                </li>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#">
                                <span class="category-icon"><i class="fas fa-table"></i></span>
                                <span><?php _e('panel', 'menu_data_circle', 'Data Circle'); ?></span>
                            </a>
                            <ul class="nav child-menu">
                                <li>
                                    <a href="<?php echo $this->url('circle/filter'); ?>">
                                        <span class="subcategory-icon"><i class="fas fa-chart-area"></i></span>
                                        <span><?php _e('panel', 'menu_ip_filter_logs', 'Filter Logs'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('circle/rule'); ?>">
                                        <span class="subcategory-icon"><i class="fas fa-fire-alt"></i></span>
                                        <span><?php _e('panel', 'menu_ip_rules', 'IP Rules'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('circle/session'); ?>">
                                        <span class="subcategory-icon"><i class="fas fa-user-clock"></i></span>
                                        <span><?php _e('panel', 'menu_sessions', 'Sessions'); ?>
                                    </a>
                                </li>
                             </ul>
                        </li>
                        
                        <li>
                            <a href="#">
                                <span class="category-icon"><i class="fas fa-fire-alt"></i></span>
                                <span><?php _e('panel', 'menu_firewall', 'Firewall'); ?></span>
                            </a>
                            <ul class="nav child-menu">
                                <li>
                                    <a href="<?php echo $this->url('setting/basic'); ?>">
                                        <span class="subcategory-icon"><i class="fas fa-cogs"></i></span>
                                        <span><?php _e('panel', 'menu_settings', 'Settings'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('setting/ipManager'); ?>">
                                        <span class="subcategory-icon"><i class="fas fa-globe"></i></span>
                                        <span><?php _e('panel', 'menu_ip_manager', 'IP Manager'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('security/xssProtection'); ?>">
                                        <span class="subcategory-icon"><i class="fas fa-umbrella"></i></span>
                                        <span><?php _e('panel', 'menu_xss_protection', 'XSS Protection'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('security/authentication'); ?>">
                                        <span class="subcategory-icon"><i class="fas fa-user-lock"></i></span>
                                        <span><?php _e('panel', 'menu_authentication', 'Authentication'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('setting/exclusion'); ?>">
                                        <span class="subcategory-icon"><i class="fas fa-eye-slash"></i></span>
                                        <span><?php _e('panel', 'menu_exclusion', 'Exclusion'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('setting/messenger'); ?>">
                                        <span class="subcategory-icon"><i class="fab fa-facebook-messenger"></i></span>
                                        <span><?php _e('panel', 'menu_messenger', 'Messenger'); ?></span>
                                    </a>
                                </li>   
                            </ul>
                        </li>
                      
                        <?php $iptablesStatus = ''; ?>
                        <?php if ($this->getConfig('iptables.enable') !== true) : ?>
                            <?php $iptablesStatus = 'inactive'; ?>
                        <?php endif; ?>

                        <li class="<?php echo $iptablesStatus; ?>">
                            <a href="#">
                                <span class="category-icon"><i class="fas fa-shield-alt"></i></span>
                                <span><?php _e('panel', 'menu_iptables_bridge', 'iptables Bridge'); ?></span>
                            </a>

                            <ul class="nav child-menu">
                                <li>
                                    <a href="<?php echo $this->url('iptables/ip4'); ?>">
                                        <span class="subcategory-icon"><i class="fas fa-dice-d20"></i></span>
                                        <span>IPv4 <?php _e('panel', 'menu_iptables_manager', 'Manager'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('iptables/ip4status'); ?>">
                                        <span class="subcategory-icon"><i class="far fa-question-circle"></i></span>
                                        <span>IPv4 <?php _e('panel', 'menu_iptables_status', 'Status'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('iptables/ip6'); ?>">
                                        <span class="subcategory-icon"><i class="fas fa-dice-d20"></i></span>
                                        <span>IPv6 <?php _e('panel', 'menu_iptables_manager', 'Manager'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('iptables/ip6status'); ?>">
                                        <span class="subcategory-icon"><i class="far fa-question-circle"></i></span>
                                        <span>IPv6 <?php _e('panel', 'menu_iptables_status', 'Status'); ?></span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <?php $loggerStatus = ''; ?>
                        <?php if ($this->getConfig('loggers.action.enable') !== true) : ?>
                            <?php $loggerStatus = 'inactive'; ?>
                        <?php endif; ?>
                      
                        <li class="<?php echo $loggerStatus; ?>">
                            <a href="#">
                                <span class="category-icon"><i class="fas fa-chart-area"></i></span>
                                <span><?php _e('panel', 'menu_action_logs', 'Logs'); ?></span>
                            </a>
                            <ul class="nav child-menu">
                                <li>
                                    <a href="<?php echo $this->url('report/actionLog'); ?>?tab=today">
                                        <span class="subcategory-icon"><i class="far fa-calendar-check"></i></span>
                                        <span><?php _e('panel', 'menu_today', 'Today'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('report/actionLog'); ?>?tab=yesterday">
                                        <span class="subcategory-icon"><i class="fas fa-calendar-day"></i></span>
                                        <span><?php _e('panel', 'menu_yesterday', 'Yesterday'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('report/actionLog'); ?>?tab=past_seven_days">
                                        <span class="subcategory-icon"><i class="fas fa-calendar-week"></i></span>
                                        <span><?php _e('panel', 'menu_last_7_days', 'Last 7 days'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('report/actionLog'); ?>?tab=this_month">
                                        <span class="subcategory-icon"><i class="far fa-calendar-alt"></i></span>
                                        <span><?php _e('panel', 'menu_this_month', 'This month'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $this->url('report/actionLog'); ?>?tab=last_month">
                                        <span class="subcategory-icon"><i class="fas fa-calendar-alt"></i></span>
                                        <span><?php _e('panel', 'menu_last_month', 'Last month'); ?></span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="col-md-10 col-sm-11 col-xs-11 so-content">
                    <?php echo $content; ?>

                    <div class="footer">
                        <div class="container-fluid">
                            <div class="col-md-12">
Powered by <a href="https://shieldon.io" target="_blank">Shieldon</a>
<?php echo SHIELDON_FIREWALL_VERSION; ?>
&copy; 2019-<?php echo date('Y'); ?>
<a href="https://terryl.in" target="_blank">Terry Lin</a>
<a href="https://github.com/terrylinooo/shieldon" target="_blank"><i class="fab fa-github"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php if (!empty($this->messages)) : ?>
        <div id="message-modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lightbox" role="document">
                <div class="modal-content">
                    <?php $className = count($this->messages) == 1 ? $this->messages[0]['class'] : 'info'; ?>
                    <div class="modal-header <?php echo $className; ?>">
                        <div class="icon-wrapper">
                            <?php if (count($this->messages) == 1) : ?>
                                <div class="icon-box">
                                    <span class="message-icon">
                                        <?php if ($this->messages[0]['type'] === 'success') : ?>
                                            <i class="fas fa-check-circle"></i>
                                        <?php endif; ?>
                                        <?php if ($this->messages[0]['type'] === 'error') : ?>
                                            <i class="fas fa-times-circle"></i>
                                        <?php endif; ?>
                                     </span>
                                </div>
                            <?php else : ?>
                                <div class="icon-box">
                                    <span class="message-icon">
                                        <i class="fas fa-exclamation-circle"></i>
                                    </span>
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
                $('.parent-menu li').each(function() {  
                    $(this).removeClass('active');
                    $(this).removeClass('current-page');
                    $('.child-menu').attr('style', '');
                });
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
        <div id="loader" data-status="waiting">
            <div class="cssload-box-loading"></div>
        </div>
    </body>
</html>
