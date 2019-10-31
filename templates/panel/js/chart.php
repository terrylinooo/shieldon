<?php defined('SHIELDON_VIEW') || exit('Life is short, why are you wasting time?');
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
?>

<script>

    var captchaSuccessCount = <?php echo ($period_data['captcha_success_count'] ?? 0); ?>;
    var captchaFailureCount = <?php echo ($period_data['captcha_failure_count'] ?? 0); ?>;
    var pageviewCount = <?php echo ($period_data['pageview_count'] ?? 0); ?>;
    var captchaCount = <?php echo ($period_data['captcha_count'] ?? 0); ?>;

    // Today
    <?php if (! empty($past_seven_hour)) : ?>
    var pageviewChartString = [<?php echo ($past_seven_hour['pageview_chart_string'] ?? ''); ?>];
    var captchaChartString = [<?php echo ($past_seven_hour['captcha_chart_string'] ?? ''); ?>];
    var labelChartString = [<?php echo ($past_seven_hour['label_chart_string'] ?? ''); ?>];
    <?php else : ?>
    var pageviewChartString = [<?php echo ($period_data['pageview_chart_string'] ?? ''); ?>];
    var captchaChartString = [<?php echo ($period_data['captcha_chart_string'] ?? ''); ?>];
    var labelChartString = [<?php echo ($period_data['label_chart_string'] ?? ''); ?>];
    <?php endif; ?>

    var todayPieOptions = {
        legend: {
            show: false
        },
        chart: {
            type: 'donut',
        },
        series: [captchaSuccessCount, captchaFailureCount],
        labels: ['success', 'failure'],
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    var todayCaptchaPie = new ApexCharts(
        document.querySelector("#chart-1"),
        todayPieOptions
    );

    todayCaptchaPie.render();

    // Yesterday
    var yesterdayPieOptions = {
        legend: {
            show: false
        },
        chart: {
            type: 'donut',
        },
        series: [pageviewCount, captchaCount],
        labels: ['Pageviews', 'CAPTCHAs'],
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    }

    var yesterdayCaptchaPie = new ApexCharts(
        document.querySelector("#chart-2"),
        yesterdayPieOptions
    );

    yesterdayCaptchaPie.render();

    // This month
    var spark3 = {
        chart: {
            type: 'area',
            sparkline: {
                enabled: true
            },
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth'
        },
        fill: {
            opacity: 1,
        },
        series: [{
            name: 'pageview',
            data: pageviewChartString
        }, {
            name: 'captcha',
            data: captchaChartString
        }],
        labels: labelChartString,
        markers: {
            size: 5
        },
        xaxis: {
            type: 'category',
        },
        yaxis: {
            min: 0
        },
        tooltip: {
            followCursor: true,
            fixed: {
                enabled: false
            },
            x: {
                show: true
            },
            y: {
                title: {
                    formatter: function (seriesName) {
                        return seriesName;
                    }
                }
            },
            marker: {
                show: false
            }
        },
        title: {
            text: '',
            offsetX: 55,
            offsetY: 16,
            style: {
                fontSize: '16px',
                cssClass: 'apexcharts-yaxis-title',
            }
        },
        subtitle: {
            text: '',
            offsetX: 55,
            offsetY: 36,
            style: {
                fontSize: '13px',
                cssClass: 'apexcharts-yaxis-title'
            }
        }
    }

    var chart = new ApexCharts(
        document.querySelector("#chart-3"),
        spark3
    );

    chart.render();

    $(function() {
        $('#so-datalog').DataTable({
            'responsive': true,
            'pageLength': 25,
            'initComplete': function(settings, json ) {
                $('#so-table-loading').hide();
                $('#so-table-container').fadeOut(800);
                $('#so-table-container').fadeIn(800);
            }
        });
    });

</script>