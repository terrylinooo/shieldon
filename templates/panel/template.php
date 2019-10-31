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

/**
 * Hightlight current page position in sidebar menu.
 *
 * @param string $key
 * @return void
 */
function showActive(string $key = '') 
{
    $page = $_GET['so_page'] ?? '';
    $tab  = $_GET['tab'] ?? '';

    $currentPage = $page . (! empty($tab) ? '_' . $tab : '');

    if ($currentPage === $key) {
        echo 'active';
    }
}

//$staticSrc = 'http://shieldon-doc.lo/static';
$staticSrc = 'http://shieldon-io.github.io/static';

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
                    <li class="nav-item"><a href="<?php echo $page_url; ?>?so_page=logout" class="nav-link"><?php _e('panel', 'logout', 'Logout'); ?></a></li>
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
                                    <a href="<?php echo $page_url; ?>?so_page=overview">
                                        <i class="fas fa-tachometer-alt"></i>
                                        <span><?php _e('panel', 'menu_overview', 'Overview'); ?></span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <?php if ($this->mode === 'managed') : ?>
                        <li>
                            <a href="#">
                                <i class="fas fa-fire-alt"></i>
                                <span><?php _e('panel', 'menu_firewall', 'Firewall'); ?></span>
                            </a>
                            <ul class="nav child-menu">
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=settings">
                                        <i class="fas fa-cogs"></i>
                                        <span><?php _e('panel', 'menu_settings', 'Settings'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=ip_manager">
                                        <i class="fas fa-globe"></i>
                                        <span><?php _e('panel', 'menu_ip_manager', 'IP Manager'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=xss_protection">
                                        <i class="fas fa-umbrella"></i>
                                        <span><?php _e('panel', 'menu_xss_protection', 'XSS Protection'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=authentication">
                                        <i class="fas fa-user-lock"></i>
                                        <span><?php _e('panel', 'menu_authentication', 'Authentication'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=exclusion">
                                        <i class="fas fa-eye-slash"></i>
                                        <span><?php _e('panel', 'menu_exclusion', 'Exclusion'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=messenger">
                                        <i class="fab fa-facebook-messenger"></i>
                                        <span><?php _e('panel', 'menu_messenger', 'Messenger'); ?></span>
                                    </a>
                                </li>   
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=system_layer_firewall">
                                        <i class="fas fa-skull"></i>
                                        <span><?php _e('panel', 'menu_system_layer', 'System Layer'); ?></span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <?php endif; ?>
                        <?php if (! empty($this->shieldon->logger)) : ?>
                        <li>
                            <a href="#">
                                <i class="fas fa-chart-area"></i>
                                <span><?php _e('panel', 'menu_logs', 'Logs'); ?></span>
                            </a>
                            <ul class="nav child-menu">
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=dashboard&tab=today">
                                        <i class="far fa-calendar-check"></i>
                                        <span><?php _e('panel', 'menu_today', 'Today'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=dashboard&tab=yesterday">
                                        <i class="fas fa-calendar-day"></i>
                                        <span><?php _e('panel', 'menu_yesterday', 'Yesterday'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=dashboard&tab=past_seven_days">
                                        <i class="fas fa-calendar-week"></i>
                                        <span><?php _e('panel', 'menu_last_7_days', 'Last 7 days'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=dashboard&tab=this_month">
                                        <i class="far fa-calendar-alt"></i>
                                        <span><?php _e('panel', 'menu_this_month', 'This month'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=dashboard&tab=last_month">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span><?php _e('panel', 'menu_last_month', 'Last month'); ?></span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <?php endif; ?>
                        <li>
                            <a href="#">
                                <i class="fas fa-table"></i>
                                <span><?php _e('panel', 'menu_data_circle', 'Data Circle'); ?></span>
                            </a>
                            <ul class="nav child-menu">
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=ip_log_table">
                                        <i class="fas fa-chart-area"></i>
                                        <span><?php _e('panel', 'menu_ip_logs', 'IP Logs'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=ip_rule_table">
                                        <i class="fas fa-fire-alt"></i>
                                        <span><?php _e('panel', 'menu_ip_rules', 'IP Rules'); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=session_table">
                                        <i class="fas fa-user-clock"></i>
                                        <span><?php _e('panel', 'menu_ip_sessions', 'Sessions'); ?>
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
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php _e('panel', 'logout', 'Message'); ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?php foreach ($this->messages as $msgInfo) : ?>
                            <p class="text-<?php echo $msgInfo['type']; ?>">
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
                    var url = window.location.href + '&so_page=ajax_change_locale';
  
                    $.ajax({
                        url: url,
                        type: 'GET',
                        data: {'langCode': langCode},
                        dataType: 'JSON',
                        cache: false,
                        success: function (data) { 
                            if (data.status = 'success') {
                                console.log(data);
                                location.reload();
                            }
                        }
                    }); 
                };

                window.selectLanguage = selectLanguage;
            });

        </script>
    </body>
</html>