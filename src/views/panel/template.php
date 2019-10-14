<?php defined('SHIELDON_VIEW') || exit('Life is short, why are you wasting time?');
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


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

?><!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/jquery.dataTables.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.8.3/apexcharts.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/css/all.css">
        <link rel="stylesheet" href="https://shieldon-io.github.io/static/css/firewall-ui.css">
        <title><?php echo $title; ?></title>
    </head>
    <body>
        <div class="shieldon-info-bar">
            <div class="logo-info">
                <img src="https://shieldon-io.github.io/static/images/logo.png">
            </div>
            <div class="mode-info">
                <ul>
                    <li>Channel: <strong><?php echo $channel_name; ?></strong></li>
                    <li>Mode:  <strong><?php echo $mode_name; ?></strong></li>
                    <li><a href="<?php echo $page_url; ?>?so_page=logout">Logout</a></li>
                </ul>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-2 so-sidebar-menu">
                    <ul class="nav flex-column parent-menu">
                        <li>
                            <a href="#"><i class="fas fa-cog"></i> Status</a>
                            <ul class="nav child-menu">
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=overview">Overview</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a><i class="fas fa-fire-alt"></i> Firewall</a>
                            <ul class="nav child-menu">
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=settings">Settings</a>
                                </li>
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=ip_manager">IP Manager</a>
                                </li>
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=xss_protection">XSS Protection</a>
                                </li>
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=authentication">Authentication</a>
                                </li>
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=exclusion">Exclusion</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a><i class="fas fa-chart-area"></i> Logs</a>
                            <ul class="nav child-menu">
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=dashboard&tab=today">Today</a>
                                </li>
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=dashboard&tab=yesterday">Yesterday</a>
                                </li>
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=dashboard&tab=past_seven_days">Last 7 days</a>
                                </li>
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=dashboard&tab=this_month">This month</a>
                                </li>
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=dashboard&tab=last_month">Last month</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a><i class="fas fa-table"></i> Data Circle</a>
                            <ul class="nav child-menu">
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=ip_log_table">IP Logs</a>
                                </li>
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=ip_rule_table">IP Rules</a>
                                </li>
                                <li>
                                    <a href="<?php echo $page_url; ?>?so_page=session_table">Sessions</a>
                                </li>
                             </ul>
                        </li>
                    </ul>
                </div>
                <div class="col-md-10 so-content">
                    <?php echo $content; ?>
                </div>
            </div>
        </div>

        <?php if (! empty($this->messages)) : ?>
        <div id="message-modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Message</h5>
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
            });

        </script>
    </body>
</html>