/**
 * Custom Shop Plugin Admin Settings JS
 */
jQuery(document).ready(function($) {

    // 1. Tab Switching
    $('.csp-nav-item').on('click', function(e) {
        e.preventDefault();
        var targetTab = $(this).data('tab');

        // Toggle active nav class
        $('.csp-nav-item').removeClass('active');
        $(this).addClass('active');

        // Show/hide panels
        $('.csp-tab-panel').removeClass('active');
        $('#csp-tab-' + targetTab).addClass('active');

        // Update hidden field for active tab so redirect lands on same tab
        $('#tsw_active_tab').val(targetTab);
    });

    // 2. Per-Day Opening Hours Toggle
    $('#pickup_use_same_hours').on('change', function() {
        if ($(this).is(':checked')) {
            $('.csp-per-day-hours').hide();
        } else {
            $('.csp-per-day-hours').css('display', 'flex');
        }
    });

    // 3. Custom Override Toggles for Name, Address, and Logo
    $('.tsw-custom-toggle').on('change', function() {
        var targetId = $(this).data('target');
        if ($(this).is(':checked')) {
            $('#' + targetId).slideDown(200);
        } else {
            $('#' + targetId).slideUp(200);
        }
    });



    // 5. Color Picker with Theme Palette Presets
    $('.csp-color-picker-input').each(function() {
        var $input = $(this);
        var $wrapper = $input.closest('.csp-color-picker-control');
        var initialValue = $input.val().trim();

        // Find initial color to send to wpColorPicker
        var pickerColor = '#ffffff'; // Default fallback
        if (initialValue !== '') {
            if (initialValue.startsWith('#') || initialValue.startsWith('rgb')) {
                pickerColor = initialValue;
            } else if (initialValue.startsWith('var(')) {
                // If it is a CSS variable, find the matching circle and use its hex
                var $matchCircle = $wrapper.find('.csp-color-circle[data-variable="' + initialValue + '"]');
                if ($matchCircle.length > 0) {
                    pickerColor = $matchCircle.data('hex');
                    $matchCircle.addClass('active');
                }
            }
        }

        // Initialize wpColorPicker
        $input.wpColorPicker({
            color: pickerColor,
            change: function(event, ui) {
                var colorHex = ui.color.toString();
                
                // Set the value as the hex code since the user selected a custom color
                $input.val(colorHex);

                // Unselect all circle buttons in this group
                $wrapper.find('.csp-color-circle').removeClass('active');
            },
            clear: function() {
                $input.val('');
                $wrapper.find('.csp-color-circle').removeClass('active');
            }
        });

        // Handle palette circle clicks
        $wrapper.find('.csp-color-circle').on('click', function(e) {
            e.preventDefault();
            var variable = $(this).data('variable');
            var hex = $(this).data('hex');

            // Deactivate other circles in group
            $wrapper.find('.csp-color-circle').removeClass('active');
            $(this).addClass('active');

            // Update color picker display to hex, but keep input value as CSS var
            if ($input.wpColorPicker('instance')) {
                $input.wpColorPicker('color', hex);
            }
            $input.val(variable);
        });
    });

    // 6. Media Library Uploader for Hero/Banner Image
    $('.csp-media-upload-btn').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var targetId = $btn.data('target');
        var $input = $('#' + targetId);
        var $preview = $('#' + targetId + '_preview');

        if (typeof wp !== 'undefined' && wp.media) {
            var frame = wp.media({
                title: 'Select or Upload Banner Image',
                button: { text: 'Use this image' },
                multiple: false
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $input.val(attachment.url);
                if ($preview.length) {
                    $preview.attr('src', attachment.url).show();
                }
            });

            frame.open();
        }
    });

    $('.csp-media-remove-btn').on('click', function(e) {
        e.preventDefault();
        var targetId = $(this).data('target');
        $('#' + targetId).val('');
        $('#' + targetId + '_preview').hide();
    });

    // 7. Copy Shortcode Button
    $('#csp-copy-shortcode-btn').on('click', function(e) {
        e.preventDefault();
        var codeText = $('#csp-shortcode-code').text().trim();
        var $btn = $(this);
        var $textSpan = $btn.find('.csp-copy-text');
        
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(codeText).then(function() {
                $textSpan.text('Copied!');
                setTimeout(function() {
                    $textSpan.text('Copy');
                }, 2000);
            });
        } else {
            var $temp = $('<input>');
            $('body').append($temp);
            $temp.val(codeText).select();
            document.execCommand('copy');
            $temp.remove();
            $textSpan.text('Copied!');
            setTimeout(function() {
                $textSpan.text('Copy');
            }, 2000);
        }
    });

    // 8. EN / DE Email Language Sub-Tabs
    $('.tsw-lang-tab-btn').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var lang = $btn.data('lang');
        var groupClass = $btn.closest('.tsw-lang-tabs').data('target-group');
        
        $btn.siblings('.tsw-lang-tab-btn').removeClass('active');
        $btn.addClass('active');

        if (groupClass) {
            $('.' + groupClass).hide();
            $('.' + groupClass + '.tsw-lang-panel-' + lang).show();
        }
    });

    // 9. Fulfillment Checkbox Safeguard Warning
    function checkFulfillmentToggles() {
        var pickupOn = $('input[name="pickup_enable_pickup"]').is(':checked');
        var deliveryOn = $('input[name="pickup_enable_delivery"]').is(':checked');
        var $warn = $('#csp-fulfillment-warning');

        if (!pickupOn && !deliveryOn) {
            if (!$warn.length) {
                var warningHtml = '<div id="csp-fulfillment-warning" class="csp-fulfillment-warning">' +
                    '<span class="dashicons dashicons-warning csp-btn-icon"></span>' +
                    '<span><strong>Warning:</strong> Both Local Pickup and Delivery are disabled. Customers will not be able to place orders.</span>' +
                    '</div>';
                $('input[name="pickup_enable_delivery"]').closest('.csp-form-group').append(warningHtml);
            }
        } else {
            if ($warn.length) {
                $warn.remove();
            }
        }
    }
    $('input[name="pickup_enable_pickup"], input[name="pickup_enable_delivery"]').on('change', checkFulfillmentToggles);
    checkFulfillmentToggles();
});
