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
    });

</script>