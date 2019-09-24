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
 * Display Shieldon's log in base64 format.
 *
 * @return string
 */
function logoBase64() 
{
    $img = <<< "EOF"
data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAVAAAABYCAYAAACjzK8AAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyFpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTQyIDc5LjE2MDkyNCwgMjAxNy8wNy8xMy0wMTowNjozOSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIChXaW5kb3dzKSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDoxNTdEMEQ2RDhGODAxMUU5OUJCOUU3MDdBRUEzODZCRiIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDoxNTdEMEQ2RThGODAxMUU5OUJCOUU3MDdBRUEzODZCRiI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjE1N0QwRDZCOEY4MDExRTk5QkI5RTcwN0FFQTM4NkJGIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOjE1N0QwRDZDOEY4MDExRTk5QkI5RTcwN0FFQTM4NkJGIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+rlibpgAALehJREFUeNrsfQmcXFWV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
EOF;
    return $img;
}

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

        <?php echo '<style>' . $inline_css . '</style>'; ?>
        <title><?php echo $title; ?></title>
    </head>
    <body>
        <div class="shieldon-info-bar">
            <div class="logo-info">
                <img src="<?php echo logoBase64(); ?>">
            </div>
            <div class="mode-info">
                <ul>
                    <li>Channel: <strong><?php echo $channel_name; ?></strong></li>
                    <li>Mode:  <strong><?php echo $mode_name; ?></strong></li>
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
                                    <a href="<?php echo $page_url; ?>?so_page=www_protection">Authentication</a>
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