jQuery(document).ready(function($) {

    // --- 1. Cart Page: td.actions children wrapper ---
    function wrapActions() {
        var actionsCell = $('.woocommerce-cart td.actions');
        if (actionsCell.length && !actionsCell.children('.actions-wrapper').length) {
            actionsCell.wrapInner('<div class="actions-wrapper"></div>');
        }
    }
    
    // Run on cart load
    wrapActions();
    
    // Run when cart updates dynamically
    $(document).on('updated_cart_totals updated_wc_div', function() {
        wrapActions();
    });

    // --- 2. Cart Page: AJAX save pickup time selection ---
    $(document).on('change', '#cart_pickup_time', function() {
        var selected_time = $(this).val();
        $.ajax({
            type: 'POST',
            url: (typeof wc_checkout_params !== 'undefined' && wc_checkout_params.ajax_url) ? wc_checkout_params.ajax_url : '/wp-admin/admin-ajax.php',
            data: {
                action: 'save_pickup_time_session',
                security: (typeof tswCheckoutData !== 'undefined') ? tswCheckoutData.nonce : '',
                pickup_time: selected_time
            },
            success: function(response) {}
        });
    });

    // --- 3. Checkout Page: Card Payment Minimum Check & Auto-COD Check ---
    function update_card_payment_status() {
        var $options = $('.custom-checkout-options');
        if (!$options.length) return;
        
        var card_disabled = $options.data('card-payment-disabled') === 'yes';
        if (card_disabled) {
            $('body').addClass('card-payment-disabled');
            $('#payment_method_bacs, #payment_method_cheque').prop('disabled', true);
            
            // If one of the card inputs is active, automatically check Cash on Delivery (cod)
            if ($('#payment_method_bacs').is(':checked') || $('#payment_method_cheque').is(':checked')) {
                $('#payment_method_cod').prop('checked', true).trigger('click');
            }
        } else {
            $('body').removeClass('card-payment-disabled');
            $('#payment_method_bacs, #payment_method_cheque').prop('disabled', false);
        }
    }

    // Run on checkout page load
    update_card_payment_status();

    // Run when checkout refreshes via AJAX
    $(document).on('updated_checkout', function() {
        update_card_payment_status();
    });

});
