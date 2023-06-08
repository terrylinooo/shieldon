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

?>
<script>

    $(function() {

        var checkToggleStatus = function() {
            $('.toggle-block').each(function() {
                var target = $(this).attr('data-target');

                if (this.checked) {
                    $('[data-parent="' + target + '"]').fadeIn(500);
                } else {
                    $('[data-parent="' + target + '"]').hide();
                }
            });
        };

        var dataDriverFiles = function() {
            let value = $('input[name="driver_type"]:checked').val();
            $('.data-driver-options').hide();
            $('.data-driver-options-' + value).fadeIn(500);
        };

        $('.data-driver-options').hide();

        checkToggleStatus();
        dataDriverFiles();

        $('.toggle-block').change(function() {
            checkToggleStatus();
        });

        $('input[name="driver_type"]').change(function() {
            dataDriverFiles();
        });

        $('input[name="tabs"]').change(function() {
            $('input[name="tab"]').val($(this).val());
        });

        $('#iptables-watch-folder').html($('input[name="iptables__config__watching_folder"]').val());

        $('input[name="iptables__config__watching_folder"]').keyup(function() {
            $('#iptables-watch-folder').html($(this).val());
            $('#code2').val($('#code1').text());
        });

        $('#code2').val($('#code1').text());

        // Keep tabl position after refreshing page.
        var hash = window.location.hash;

        $('input:radio[name="tabs"]').on('change', function() {
            window.location.hash = $(this).val();
            $('form').attr('action', window.location.hash);
        });

        if (window.location.hash !== '') {
            $('input:radio[name="tabs"]').prop('checked', false);
            $('input:radio[name="tabs"]').each(function() {
                var thisHash = '#' + $(this).val();
                if (hash === thisHash) {
                    $(this).prop('checked', true);
                } else {
                    $(this).prop('checked', false);
                }
            });
        }
    });

</script>