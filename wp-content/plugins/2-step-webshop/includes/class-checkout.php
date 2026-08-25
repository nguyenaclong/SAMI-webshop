<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TSW_Checkout {

    public function __construct() {
        // Checkout layout fields overrides
        add_filter( 'woocommerce_checkout_fields', array( $this, 'customize_checkout_fields' ) );

        // Force shipping package destination (Bug #13 fix: uses WC store options, not hardcoded)
        add_filter( 'woocommerce_cart_shipping_packages', array( $this, 'force_shipping_package_destination' ) );

        // Disable WooCommerce shipping calculation and shipping rows during Local Pickup / Single Restaurant mode
        add_filter( 'woocommerce_cart_needs_shipping', array( $this, 'filter_cart_needs_shipping' ) );
        add_filter( 'woocommerce_cart_needs_shipping_address', array( $this, 'filter_needs_shipping_address' ) );
        add_filter( 'woocommerce_ship_to_different_address_checked', array( $this, 'filter_ship_to_different_address_checked' ) );
        add_filter( 'woocommerce_package_rates', array( $this, 'filter_package_rates' ), 10, 2 );
        add_action( 'woocommerce_checkout_process', array( $this, 'ensure_chosen_shipping_method' ), 1 );

        // Save metadata and display fields on order views
        add_action( 'woocommerce_checkout_create_order', array( $this, 'save_pickup_fields_to_order' ), 10, 2 );
        add_action( 'woocommerce_checkout_create_order', array( $this, 'save_order_language' ), 10, 2 );
        add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_pickup_fields_in_admin' ), 10, 1 );
        add_action( 'woocommerce_order_details_after_order_table', array( $this, 'display_pickup_fields_on_order_details' ), 10, 1 );
        add_action( 'woocommerce_email_after_order_table', array( $this, 'display_pickup_time_in_emails' ), 10, 4 );
        add_filter( 'woocommerce_order_formatted_billing_address', array( $this, 'clear_street_address_fields' ), 10, 2 );

        // Email locale switching — switch locale before email body is generated, restore after
        add_action( 'woocommerce_email_before_send', array( $this, 'switch_email_locale_by_order' ), 5, 3 );
        add_action( 'woocommerce_email_after_send', array( $this, 'restore_email_locale' ), 999, 3 );

        // Multiple Admin Email Recipients filter
        add_filter( 'woocommerce_email_recipient_new_order', array( $this, 'custom_admin_email_recipients' ), 10, 3 );
        add_filter( 'woocommerce_email_recipient_cancelled_order', array( $this, 'custom_admin_email_recipients' ), 10, 3 );
        add_filter( 'woocommerce_email_recipient_failed_order', array( $this, 'custom_admin_email_recipients' ), 10, 3 );

        // Custom Email Template Filters
        add_filter( 'woocommerce_email_subject_customer_processing_order', array( $this, 'filter_customer_email_subject' ), 10, 2 );
        add_filter( 'woocommerce_email_subject_customer_completed_order', array( $this, 'filter_customer_email_subject' ), 10, 2 );
        add_filter( 'woocommerce_email_subject_new_order', array( $this, 'filter_admin_email_subject' ), 10, 2 );
        add_filter( 'woocommerce_email_content_customer_processing_order', array( $this, 'filter_customer_email_body' ), 10, 2 );
        add_filter( 'woocommerce_email_content_customer_completed_order', array( $this, 'filter_customer_email_body' ), 10, 2 );
        add_filter( 'woocommerce_email_content_new_order', array( $this, 'filter_admin_email_body' ), 10, 2 );

        // Payment gateways overrides
        add_filter( 'woocommerce_gateway_title', array( $this, 'override_gateway_titles' ), 999, 2 );
        // Bug #10 fix: only remove description for specific payment methods
        // A blanket filter breaks Stripe/PayPal which render inputs inside description
        add_filter( 'woocommerce_gateway_description', array( $this, 'maybe_hide_gateway_description' ), 999, 2 );

        // Cart and Checkout Buttons
        add_action( 'woocommerce_proceed_to_checkout', array( $this, 'display_pickup_time_on_cart_page' ), 5 );
        add_action( 'woocommerce_proceed_to_checkout', array( $this, 'add_continue_shopping_button_to_cart' ), 25 );
        add_filter( 'woocommerce_return_to_shop_redirect', array( $this, 'custom_empty_cart_return_url' ), 999 );
        add_filter( 'woocommerce_return_to_shop_text', array( $this, 'custom_empty_cart_return_text' ), 999 );
        add_action( 'woocommerce_review_order_after_submit', array( $this, 'add_back_button_to_checkout' ) );

        // Checkout Notice
        add_action( 'woocommerce_review_order_before_submit', array( $this, 'add_custom_checkout_notice' ) );

        // Server-side Checkout Validations
        add_action( 'woocommerce_after_checkout_validation', array( $this, 'validate_custom_billing_fields' ), 10, 2 );
        add_action( 'woocommerce_checkout_process', array( $this, 'validate_card_payment_minimum_total' ) );
        add_filter( 'body_class', array( $this, 'add_checkout_body_class_for_card_payment' ) );
        add_filter( 'woocommerce_form_field_select', array( $this, 'render_pickup_time_select_disabled_options' ), 10, 4 );

        // Privacy Policy statement
        add_filter( 'woocommerce_get_privacy_policy_text', array( $this, 'german_privacy_policy_text' ), 99, 2 );

        // Enqueue Assets for Cart/Checkout
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_checkout_assets' ) );

        // Redirect Cart page to checkout / shop for strict 2-step process
        add_action( 'template_redirect', array( $this, 'redirect_cart_page' ) );
    }


    /**
     * Enqueue separate CSS/JS files specifically on Cart and Checkout pages
     */
    public function enqueue_checkout_assets() {
        if ( is_cart() || is_checkout() ) {
            wp_enqueue_style( 'tsw-checkout-style', TSW_URL . 'assets/css/checkout.css', array(), TSW_VERSION );
            wp_enqueue_script( 'tsw-checkout-script', TSW_URL . 'assets/js/checkout.js', array( 'jquery' ), TSW_VERSION, true );
            wp_localize_script( 'tsw-checkout-script', 'tswCheckoutData', array(
                'nonce' => wp_create_nonce( 'tsw_ajax_nonce' ),
            ) );
        }
    }

    /**
     * Customize checkout fields layout (billing address default parameters setup)
     */
    public function customize_checkout_fields( $fields ) {
        // Remove unused billing fields
        unset( $fields['billing']['billing_company'] );
        unset( $fields['billing']['billing_address_2'] );
        unset( $fields['shipping'] );

        $chosen_method = 'pickup';
        $saved_address = '';
        if ( WC()->session ) {
            $saved_address = WC()->session->get( 'delivery_address' );
            $chosen_shipping = WC()->session->get( 'chosen_shipping_methods' );
            if ( is_array( $chosen_shipping ) ) {
                foreach ( $chosen_shipping as $method ) {
                    if ( strpos( $method, 'flat_rate' ) !== false ) {
                        $chosen_method = 'delivery';
                        break;
                    }
                }
            }
        }

        // Get store address info
        $store_country  = get_option( 'woocommerce_default_country', 'DE' );
        $store_postcode = get_option( 'woocommerce_store_postcode', '70173' );
        $store_city     = get_option( 'woocommerce_store_city', 'Stuttgart' );
        $store_address  = get_option( 'woocommerce_store_address', 'Nadlerstraße 14' );

        if ( $chosen_method === 'pickup' ) {
            // Force/hide address details for local pickup
            $fields['billing']['billing_country']['default']   = substr( $store_country, 0, 2 );
            $fields['billing']['billing_country']['class']     = array( 'hidden-field' );
            $fields['billing']['billing_country']['required']  = false;

            $fields['billing']['billing_state']['default']     = 'BW';
            $fields['billing']['billing_state']['class']       = array( 'hidden-field' );
            $fields['billing']['billing_state']['required']    = false;

            $fields['billing']['billing_postcode']['default']  = $store_postcode;
            $fields['billing']['billing_postcode']['class']    = array( 'hidden-field' );
            $fields['billing']['billing_postcode']['required'] = false;

            $fields['billing']['billing_city']['default']      = $store_city;
            $fields['billing']['billing_city']['class']        = array( 'hidden-field' );
            $fields['billing']['billing_city']['required']     = false;

            $fields['billing']['billing_address_1']['default'] = $store_address;
            $fields['billing']['billing_address_1']['class']    = array( 'hidden-field' );
            $fields['billing']['billing_address_1']['required'] = false;
        } else {
            // Delivery mode: show address fields and make them required
            $fields['billing']['billing_country']['default']   = substr( $store_country, 0, 2 );
            $fields['billing']['billing_country']['required']  = true;
            // Let's hide country and state as we deliver only inside Stuttgart/BW
            $fields['billing']['billing_country']['class']     = array( 'hidden-field' );

            $fields['billing']['billing_state']['default']     = 'BW';
            $fields['billing']['billing_state']['class']       = array( 'hidden-field' );
            $fields['billing']['billing_state']['required']    = false;

            $fields['billing']['billing_postcode']['required'] = true;
            if ( isset( $fields['billing']['billing_postcode']['class'] ) ) {
                $fields['billing']['billing_postcode']['class'] = array_diff( $fields['billing']['billing_postcode']['class'], array( 'hidden-field' ) );
            }

            $fields['billing']['billing_city']['required']     = true;
            $fields['billing']['billing_city']['default']      = $store_city;
            if ( isset( $fields['billing']['billing_city']['class'] ) ) {
                $fields['billing']['billing_city']['class'] = array_diff( $fields['billing']['billing_city']['class'], array( 'hidden-field' ) );
            }

            $fields['billing']['billing_address_1']['required'] = true;
            $fields['billing']['billing_address_1']['placeholder'] = __( 'Street name and house number *', '2-step-webshop' );
            if ( isset( $fields['billing']['billing_address_1']['class'] ) ) {
                $fields['billing']['billing_address_1']['class'] = array_diff( $fields['billing']['billing_address_1']['class'], array( 'hidden-field' ) );
            }
            if ( ! empty( $saved_address ) ) {
                $fields['billing']['billing_address_1']['default'] = $saved_address;
            }
        }

        // Visual fields priority adjustments
        if ( isset( $fields['billing']['billing_last_name'] ) ) {
            $fields['billing']['billing_last_name']['label']       = __( 'Last Name', '2-step-webshop' );
            $fields['billing']['billing_last_name']['placeholder'] = __( 'Last Name *', '2-step-webshop' );
            $fields['billing']['billing_last_name']['required']    = true;
            $fields['billing']['billing_last_name']['class']       = array( 'form-row-wide' );
            $fields['billing']['billing_last_name']['clear']       = true;
            $fields['billing']['billing_last_name']['priority']    = 10;
        }

        if ( isset( $fields['billing']['billing_first_name'] ) ) {
            $fields['billing']['billing_first_name']['label']       = __( 'First Name', '2-step-webshop' );
            $fields['billing']['billing_first_name']['placeholder'] = __( 'First Name *', '2-step-webshop' );
            $fields['billing']['billing_first_name']['required']    = true;
            $fields['billing']['billing_first_name']['class']       = array( 'form-row-wide' );
            $fields['billing']['billing_first_name']['clear']       = true;
            $fields['billing']['billing_first_name']['priority']    = 20;
        }

        if ( isset( $fields['billing']['billing_phone'] ) ) {
            $fields['billing']['billing_phone']['label']       = __( 'Phone Number', '2-step-webshop' );
            $fields['billing']['billing_phone']['placeholder'] = __( 'Phone Number *', '2-step-webshop' );
            $fields['billing']['billing_phone']['required']    = true;
            $fields['billing']['billing_phone']['class']       = array( 'form-row-wide' );
            $fields['billing']['billing_phone']['clear']       = true;
            $fields['billing']['billing_phone']['priority']    = 30;
        }

        if ( isset( $fields['billing']['billing_email'] ) ) {
            $fields['billing']['billing_email']['label']       = __( 'Email Address', '2-step-webshop' );
            $fields['billing']['billing_email']['placeholder'] = __( 'Email Address *', '2-step-webshop' );
            $fields['billing']['billing_email']['required']    = true;
            $fields['billing']['billing_email']['class']       = array( 'form-row-wide' );
            $fields['billing']['billing_email']['clear']       = true;
            $fields['billing']['billing_email']['priority']    = 40;
        }

        // Pull selected date and timeslot from session
        $selected_date = WC()->session ? WC()->session->get( 'pickup_date' ) : '';
        $selected_time = WC()->session ? WC()->session->get( 'pickup_time' ) : '';

        // Add Pickup Date field as a dropdown
        $fields['billing']['billing_pickup_date'] = array(
            'type'     => 'select',
            'label'    => __( 'Order Date', '2-step-webshop' ),
            'required' => true,
            'class'    => array( 'form-row-wide' ),
            'clear'    => true,
            'priority' => 45,
            'options'  => TSW_Pickup_Scheduler::get_available_order_dates(),
            'default'  => $selected_date, 
        );

        // Add Pickup Time field as a dropdown
        $fields['billing']['billing_pickup_time'] = array(
            'type'     => 'select',
            'label'    => __( 'Order Time', '2-step-webshop' ),
            'required' => true,
            'class'    => array( 'form-row-wide' ),
            'clear'    => true,
            'priority' => 50,
            'options'  => TSW_Pickup_Scheduler::get_pickup_time_choices(),
            'default'  => $selected_time, 
        );

        // Edit Order Notes placeholder dynamically
        if ( isset( $fields['order']['order_comments'] ) ) {
            $fields['order']['order_comments']['placeholder'] = __( 'Please let us know about any special dietary needs, allergies, or request notes.', '2-step-webshop' );
        }

        // Sort billing fields array by priority key
        uasort( $fields['billing'], function( $a, $b ) {
            $a_priority = isset( $a['priority'] ) ? $a['priority'] : 100;
            $b_priority = isset( $b['priority'] ) ? $b['priority'] : 100;
            return $a_priority - $b_priority;
        });

        return $fields;
    }

    /**
     * Bug #10 fix: only hide description for our custom payment methods
     */
    public function maybe_hide_gateway_description( $description, $gateway_id ) {
        if ( in_array( $gateway_id, array( 'bacs', 'cheque', 'cod' ), true ) ) {
            return '';
        }
        return $description;
    }

    /**
     * Bug #13 fix: use WC store options for destination only if shipping method is local pickup
     */
    public function force_shipping_package_destination( $packages ) {
        $chosen_method = 'pickup';
        if ( WC()->session ) {
            $chosen_shipping = WC()->session->get( 'chosen_shipping_methods' );
            if ( is_array( $chosen_shipping ) ) {
                foreach ( $chosen_shipping as $method ) {
                    if ( strpos( $method, 'flat_rate' ) !== false ) {
                        $chosen_method = 'delivery';
                        break;
                    }
                }
            }
        }

        if ( $chosen_method === 'pickup' ) {
            $country  = get_option( 'woocommerce_default_country', 'DE' );
            $postcode = get_option( 'woocommerce_store_postcode', '70173' );
            $city     = get_option( 'woocommerce_store_city', 'Stuttgart' );
            foreach ( $packages as $i => $package ) {
                $packages[$i]['destination']['country']  = substr( $country, 0, 2 );
                $packages[$i]['destination']['state']    = '';
                $packages[$i]['destination']['postcode'] = $postcode;
                $packages[$i]['destination']['city']     = $city;
            }
        }
        return $packages;
    }

    /**
     * Disable WooCommerce shipping calculations and shipping tables during Local Pickup / Single Restaurant mode
     */
    public function filter_cart_needs_shipping( $needs_shipping ) {
        $chosen_method = 'pickup';
        if ( WC()->session ) {
            $chosen_shipping = WC()->session->get( 'chosen_shipping_methods' );
            if ( is_array( $chosen_shipping ) ) {
                foreach ( $chosen_shipping as $method ) {
                    if ( strpos( $method, 'flat_rate' ) !== false || strpos( $method, 'delivery' ) !== false ) {
                        $chosen_method = 'delivery';
                        break;
                    }
                }
            }
        }
        if ( isset( $_POST['tsw_fulfillment_type'] ) ) {
            $chosen_method = sanitize_text_field( $_POST['tsw_fulfillment_type'] );
        }

        $delivery_enabled = get_option( 'custom_shop_enable_delivery', 'yes' );
        if ( $delivery_enabled === 'no' || $chosen_method === 'pickup' ) {
            return false;
        }

        return $needs_shipping;
    }

    /**
     * Disable separate shipping address requirement during Local Pickup
     */
    public function filter_needs_shipping_address( $needs ) {
        $chosen_method = 'pickup';
        if ( WC()->session ) {
            $chosen_shipping = WC()->session->get( 'chosen_shipping_methods' );
            if ( is_array( $chosen_shipping ) ) {
                foreach ( $chosen_shipping as $method ) {
                    if ( strpos( $method, 'flat_rate' ) !== false || strpos( $method, 'delivery' ) !== false ) {
                        $chosen_method = 'delivery';
                        break;
                    }
                }
            }
        }
        if ( isset( $_POST['tsw_fulfillment_type'] ) ) {
            $chosen_method = sanitize_text_field( $_POST['tsw_fulfillment_type'] );
        }

        if ( $chosen_method === 'pickup' ) {
            return false;
        }
        return $needs;
    }

    /**
     * Ensure 'Ship to a different address' checkbox defaults to unchecked during Local Pickup
     */
    public function filter_ship_to_different_address_checked( $checked ) {
        $chosen_method = 'pickup';
        if ( WC()->session ) {
            $chosen_shipping = WC()->session->get( 'chosen_shipping_methods' );
            if ( is_array( $chosen_shipping ) ) {
                foreach ( $chosen_shipping as $method ) {
                    if ( strpos( $method, 'flat_rate' ) !== false || strpos( $method, 'delivery' ) !== false ) {
                        $chosen_method = 'delivery';
                        break;
                    }
                }
            }
        }
        if ( isset( $_POST['tsw_fulfillment_type'] ) ) {
            $chosen_method = sanitize_text_field( $_POST['tsw_fulfillment_type'] );
        }

        if ( $chosen_method === 'pickup' ) {
            return false;
        }
        return $checked;
    }

    /**
     * Ensure matching shipping rates exist for Pickup and Delivery (injects fallback rate if WooCommerce shipping zones lack rate)
     */
    public function filter_package_rates( $rates, $package ) {
        $chosen_method = 'pickup';
        if ( WC()->session ) {
            $chosen_shipping = WC()->session->get( 'chosen_shipping_methods' );
            if ( is_array( $chosen_shipping ) ) {
                foreach ( $chosen_shipping as $method ) {
                    if ( strpos( $method, 'flat_rate' ) !== false || strpos( $method, 'delivery' ) !== false ) {
                        $chosen_method = 'delivery';
                        break;
                    }
                }
            }
        }
        if ( isset( $_POST['tsw_fulfillment_type'] ) ) {
            $chosen_method = sanitize_text_field( $_POST['tsw_fulfillment_type'] );
        }

        if ( $chosen_method === 'pickup' ) {
            $has_pickup = false;
            foreach ( $rates as $rate_id => $rate ) {
                if ( strpos( $rate->method_id, 'local_pickup' ) !== false || strpos( $rate_id, 'local_pickup' ) !== false ) {
                    $has_pickup = true;
                    break;
                }
            }

            if ( ! $has_pickup ) {
                $rates['local_pickup:fallback'] = new WC_Shipping_Rate(
                    'local_pickup:fallback',
                    __( 'Local Pickup', '2-step-webshop' ),
                    0,
                    array(),
                    'local_pickup'
                );
            }
        } else {
            $has_delivery = false;
            foreach ( $rates as $rate_id => $rate ) {
                if ( strpos( $rate->method_id, 'flat_rate' ) !== false || strpos( $rate_id, 'flat_rate' ) !== false ) {
                    $has_delivery = true;
                    break;
                }
            }

            if ( ! $has_delivery ) {
                $delivery_fee = (float) get_option( 'custom_shop_delivery_fee', 0 );
                $rates['flat_rate:fallback'] = new WC_Shipping_Rate(
                    'flat_rate:fallback',
                    __( 'Delivery', '2-step-webshop' ),
                    $delivery_fee,
                    array(),
                    'flat_rate'
                );
            }
        }

        return $rates;
    }

    /**
     * Ensure a valid matching shipping rate ID is set in WooCommerce session prior to checkout validation
     */
    public function ensure_chosen_shipping_method() {
        if ( ! WC()->session ) {
            return;
        }

        $chosen_method = 'pickup';
        if ( isset( $_POST['tsw_fulfillment_type'] ) ) {
            $chosen_method = sanitize_text_field( $_POST['tsw_fulfillment_type'] );
        } else {
            $chosen_shipping = WC()->session->get( 'chosen_shipping_methods' );
            if ( is_array( $chosen_shipping ) ) {
                foreach ( $chosen_shipping as $method ) {
                    if ( strpos( $method, 'flat_rate' ) !== false || strpos( $method, 'delivery' ) !== false ) {
                        $chosen_method = 'delivery';
                        break;
                    }
                }
            }
        }

        $matching_rate_id = '';
        $packages = WC()->shipping() ? WC()->shipping()->get_packages() : array();
        if ( ! empty( $packages ) ) {
            foreach ( $packages as $package ) {
                if ( isset( $package['rates'] ) && is_array( $package['rates'] ) ) {
                    foreach ( $package['rates'] as $rate_id => $rate ) {
                        if ( $chosen_method === 'pickup' && ( strpos( $rate->method_id, 'local_pickup' ) !== false || strpos( $rate_id, 'local_pickup' ) !== false ) ) {
                            $matching_rate_id = $rate_id;
                            break 2;
                        } elseif ( $chosen_method === 'delivery' && ( strpos( $rate->method_id, 'flat_rate' ) !== false || strpos( $rate_id, 'flat_rate' ) !== false ) ) {
                            $matching_rate_id = $rate_id;
                            break 2;
                        }
                    }
                }
            }
        }

        if ( empty( $matching_rate_id ) ) {
            $matching_rate_id = ( $chosen_method === 'pickup' ) ? 'local_pickup:fallback' : 'flat_rate:fallback';
        }

        WC()->session->set( 'chosen_shipping_methods', array( $matching_rate_id ) );
    }

    /**
     * Save pickup selection to order meta
     */
    public function save_pickup_fields_to_order( $order, $data = null ) {
        $pickup_date = '';
        if ( isset( $_POST['billing_pickup_date'] ) ) {
            $pickup_date = sanitize_text_field( $_POST['billing_pickup_date'] );
        } elseif ( WC()->session ) {
            $pickup_date = sanitize_text_field( WC()->session->get( 'pickup_date' ) );
        }
        if ( ! empty( $pickup_date ) ) {
            $order->update_meta_data( '_pickup_date', $pickup_date );
        }

        $pickup_time = '';
        if ( isset( $_POST['billing_pickup_time'] ) ) {
            $pickup_time = sanitize_text_field( $_POST['billing_pickup_time'] );
        } elseif ( WC()->session ) {
            $pickup_time = sanitize_text_field( WC()->session->get( 'pickup_time' ) );
        }
        if ( ! empty( $pickup_time ) ) {
            $order->update_meta_data( '_pickup_time', $pickup_time );
        }
    }

    /**
     * Save the active Polylang/site language at the time the order is placed.
     * Stored as _order_language (e.g. 'en', 'de') for use when generating emails.
     */
    public function save_order_language( $order, $data = null ) {
        // Polylang: pll_current_language() returns 'en', 'de', etc.
        if ( function_exists( 'pll_current_language' ) ) {
            $lang = pll_current_language( 'locale' ); // returns 'en_US', 'de_DE', etc.
        } else {
            $lang = get_locale();
        }
        if ( $lang ) {
            $order->update_meta_data( '_order_language', sanitize_text_field( $lang ) );
        }
    }

    /**
     * Switch the WordPress/plugin locale before a WooCommerce email is generated.
     *
     * For customer emails  → use the locale stored on the order (_order_language).
     * For admin-only emails → use the tsw_admin_email_language setting:
     *   'auto'  = follow the order language (same as customer)
     *   'en_US' = always English
     *   'de_DE' = always German
     *
     * Admin email IDs: new_order, cancelled_order, failed_order.
     *
     * @param WC_Email $email      The email object being sent.
     * @param bool     $sent_to_admin  Whether this is an admin-addressed email.
     * @param WC_Order $order      The related order (may be null).
     */
    public function switch_email_locale_by_order( $email, $sent_to_admin, $order ) {
        if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
            return;
        }

        // Determine the target locale.
        $admin_email_ids = array( 'new_order', 'cancelled_order', 'failed_order' );
        $is_admin_email  = isset( $email->id ) && in_array( $email->id, $admin_email_ids, true );

        if ( $is_admin_email ) {
            $admin_lang_setting = get_option( 'tsw_admin_email_language', 'auto' );
            if ( 'auto' === $admin_lang_setting ) {
                $target_locale = $order->get_meta( '_order_language' );
            } else {
                $target_locale = $admin_lang_setting; // 'en_US' or 'de_DE'
            }
        } else {
            // Customer email: always follow the order's language.
            $target_locale = $order->get_meta( '_order_language' );
        }

        // Fallback: use site default.
        if ( empty( $target_locale ) ) {
            $target_locale = get_locale();
        }

        // Switch locale if it differs from the current one.
        if ( $target_locale !== get_locale() ) {
            switch_to_locale( $target_locale );
            // Re-load the plugin textdomain in the new locale.
            load_plugin_textdomain( '2-step-webshop', false, dirname( plugin_basename( TSW_DIR . '2-step-webshop.php' ) ) . '/languages' );
            // Re-load WooCommerce textdomain so WC strings also translate correctly.
            WC()->load_cart();
            load_plugin_textdomain( 'woocommerce', false, dirname( plugin_basename( WC_PLUGIN_FILE ) ) . '/i18n/languages' );
        }
    }

    /**
     * Restore the previous locale after a WooCommerce email has been sent.
     *
     * @param WC_Email $email
     * @param bool     $sent_to_admin
     * @param WC_Order $order
     */
    public function restore_email_locale( $email, $sent_to_admin, $order ) {
        if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
            return;
        }
        restore_previous_locale();
        // Re-load textdomains after restoring locale.
        load_plugin_textdomain( '2-step-webshop', false, dirname( plugin_basename( TSW_DIR . '2-step-webshop.php' ) ) . '/languages' );
    }

    /**
     * Display details on the WooCommerce Admin Order edit page
     */
    public function display_pickup_fields_in_admin( $order ) {
        $pickup_date = $order->get_meta( '_pickup_date' );
        $pickup_time = $order->get_meta( '_pickup_time' );
        if ( $pickup_date ) {
            echo '<p><strong>' . __( 'Order Date', '2-step-webshop' ) . ':</strong> ' . esc_html( $pickup_date ) . '</p>';
        }
        if ( $pickup_time ) {
            echo '<p><strong>' . __( 'Order Time', '2-step-webshop' ) . ':</strong> ' . esc_html( $pickup_time ) . '</p>';
        }
    }

    /**
     * Display details on the customer order confirm (thank you) & view order pages
     */
    public function display_pickup_fields_on_order_details( $order ) {
        if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
            return;
        }
        $pickup_date = $order->get_meta( '_pickup_date' );
        $pickup_time = $order->get_meta( '_pickup_time' );
        ?>
        <table class="woocommerce-table shop_table order_details custom-pickup-time-table" style="margin-top: 20px; width: 100%;">
            <tbody>
                <?php if ( $pickup_date ) : ?>
                    <tr>
                        <th scope="row" style="text-align: left; font-weight: bold; width: 50%;"><?php _e( 'Order Date', '2-step-webshop' ); ?>:</th>
                        <td style="text-align: left;"><?php echo esc_html( $pickup_date ); ?></td>
                    </tr>
                <?php endif; ?>
                <?php if ( $pickup_time ) : ?>
                    <tr>
                        <th scope="row" style="text-align: left; font-weight: bold; width: 50%;"><?php _e( 'Order Time', '2-step-webshop' ); ?>:</th>
                        <td style="text-align: left;"><?php echo esc_html( $pickup_time ); ?></td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <!-- Bug #14 fix: use dynamic store name from WP/WC settings -->
                    <th scope="row" style="text-align: left; font-weight: bold; width: 50%;"><?php _e( 'Restaurant', '2-step-webshop' ); ?>:</th>
                    <td style="text-align: left;"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align: left; font-weight: bold; width: 50%;"><?php _e( 'Address', '2-step-webshop' ); ?>:</th>
                    <td style="text-align: left;"><?php
                        $addr = trim( get_option( 'woocommerce_store_address', '' ) );
                        $city = trim( get_option( 'woocommerce_store_city', '' ) );
                        $post = trim( get_option( 'woocommerce_store_postcode', '' ) );
                        echo esc_html( $addr . ( $city ? ', ' . $post . ' ' . $city : '' ) );
                    ?></td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * Display pickup time and restaurant details in emails after order table
     */
    public function display_pickup_time_in_emails( $order, $sent_to_admin, $plain_text, $email ) {
        if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
            return;
        }
        
        $pickup_date = $order->get_meta( '_pickup_date' );
        $pickup_time = $order->get_meta( '_pickup_time' );

        if ( $plain_text ) {
            if ( $pickup_date ) {
                echo "\n" . __( 'Order Date', '2-step-webshop' ) . ': ' . $pickup_date . "\n";
            }
            if ( $pickup_time ) {
                echo __( 'Order Time', '2-step-webshop' ) . ': ' . $pickup_time . "\n";
            }
            // Bug #14 fix: use dynamic store name/address
            echo tsw_get_restaurant_name() . "\n";
            echo tsw_get_store_address() . "\n\n";
        } else {
            ?>
            <div style="margin-top: 20px; margin-bottom: 20px; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; padding: 15px; border: 1px solid #eee; border-radius: 8px; background-color: #fafafa; line-height: 1.5em;">
                <?php if ( $pickup_date ) : ?>
                    <p style="margin: 0 0 5px 0;"><strong><?php _e( 'Order Date', '2-step-webshop' ); ?>:</strong> <?php echo esc_html( $pickup_date ); ?></p>
                <?php endif; ?>
                <?php if ( $pickup_time ) : ?>
                    <p style="margin: 0 0 10px 0;"><strong><?php _e( 'Order Time', '2-step-webshop' ); ?>:</strong> <?php echo esc_html( $pickup_time ); ?></p>
                <?php endif; ?>
                <hr style="border: 0; border-top: 1px solid #ddd; margin: 10px 0;" />
                <p style="margin: 0 0 5px 0;"><strong><?php echo esc_html( tsw_get_restaurant_name() ); ?></strong></p>
                <p style="margin: 0;"><?php echo esc_html( tsw_get_store_address() ); ?></p>
            </div>
            <?php
        }
    }

    /**
     * Clear physical address fields from formatted billing address only for local pickup orders
     */
    public function clear_street_address_fields( $address, $order ) {
        if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
            return $address;
        }
        $shipping_methods = $order->get_shipping_methods();
        $is_delivery = false;
        foreach ( $shipping_methods as $method ) {
            if ( strpos( $method->get_method_id(), 'flat_rate' ) !== false ) {
                $is_delivery = true;
                break;
            }
        }
        if ( ! $is_delivery ) {
            $address['company']   = '';
            $address['address_1'] = '';
            $address['address_2'] = '';
            $address['city']      = '';
            $address['state']     = '';
            $address['postcode']  = '';
            $address['country']   = '';
        }
        return $address;
    }

    /**
     * Force-rename payment gateways and make them uppercase with sub-notice under card label
     */
    public function override_gateway_titles( $title, $gateway_id = '' ) {
        if ( ! $gateway_id ) {
            return $title;
        }
        if ( 'cod' === $gateway_id ) {
            return __( 'Cash payment', '2-step-webshop' );
        }
        if ( 'bacs' === $gateway_id || 'cheque' === $gateway_id ) {
            $min_val = intval( get_option( 'pickup_min_payment_for_card', '35' ) );
            $custom_title = __( 'Card payment (at restaurant)', '2-step-webshop' );
            $custom_title .= ' <span class="gateway-subnotice" style="display:block; font-size: 0.852em; text-transform: none; margin-top: 5px; font-weight: normal; line-height: 1.4em;">' . sprintf( __( 'Card payment is only available for orders over %d € and only with EC-Card', '2-step-webshop' ), $min_val ) . '</span>';
            return $custom_title;
        }
        return $title;
    }

    /**
     * Display Pickup Time select dropdown on Cart Page with disabled past times & auto-selection
     */
    public function display_pickup_time_on_cart_page() {
        $dates = TSW_Pickup_Scheduler::get_available_order_dates();
        $choices = TSW_Pickup_Scheduler::get_pickup_time_choices();
        
        $selected_date = WC()->session ? WC()->session->get( 'pickup_date' ) : '';
        $selected_time = WC()->session ? WC()->session->get( 'pickup_time' ) : '';
        
        // Pick first available date if empty
        if ( empty( $selected_date ) && ! empty( $dates ) ) {
            $selected_date = array_key_first( $dates );
            if ( WC()->session ) {
                WC()->session->set( 'pickup_date', $selected_date );
            }
        }
        
        // Auto-select the first available future time if selected is empty or already in the past
        if ( empty( $selected_time ) || TSW_Pickup_Scheduler::is_pickup_time_passed( $selected_time, $selected_date ) ) {
            $selected_time = TSW_Pickup_Scheduler::get_first_available_pickup_time();
            if ( WC()->session && ! empty( $selected_time ) ) {
                WC()->session->set( 'pickup_time', $selected_time );
            }
        }
        
        echo '<div class="cart-pickup-time-select" style="margin-bottom: 20px;">';
        
        // Render Date Selection on Cart Page
        echo '<label for="cart_pickup_date" style="display:block; font-weight:bold; margin-bottom: 5px;">' . __( 'Order Date', '2-step-webshop' ) . ' *</label>';
        echo '<select name="cart_pickup_date" id="cart_pickup_date" style="width: 100%; margin-bottom: 12px;" required>';
        foreach ( $dates as $val => $lbl ) {
            echo '<option value="' . esc_attr( $val ) . '" ' . selected( $selected_date, $val, false ) . '>' . esc_html( $lbl ) . '</option>';
        }
        echo '</select>';

        // Render Time Selection on Cart Page
        echo '<label for="cart_pickup_time" style="display:block; font-weight:bold; margin-bottom: 5px;">' . __( 'Order Time', '2-step-webshop' ) . ' *</label>';
        echo '<select name="cart_pickup_time" id="cart_pickup_time" style="width: 100%;" required>';
        foreach ( $choices as $value => $label ) {
            $selected_attr = selected( $selected_time, $value, false );
            
            $disabled_attr = '';
            if ( $value !== '' && TSW_Pickup_Scheduler::is_pickup_time_passed( $value, $selected_date ) ) {
                $disabled_attr = ' disabled="disabled" style="color: gray;"';
            }
            
            echo '<option value="' . esc_attr( $value ) . '" ' . $selected_attr . $disabled_attr . '>' . esc_html( $label ) . '</option>';
        }
        echo '</select>';
        echo '</div>';
    }

    /**
     * Append 'Back to Shopping' button to the Cart page (Utilizing custom admin URL field if set)
     */
    public function add_continue_shopping_button_to_cart() {
        $shop_url = self::get_translated_shop_url();
        echo '<a href="' . esc_url( $shop_url ) . '" class="button continue-shopping-button">' . __( 'Continue Shopping', '2-step-webshop' ) . '</a>';
    }

    /**
     * Modify Empty Cart Button URL & Text
     */
    public function custom_empty_cart_return_url() {
        return self::get_translated_shop_url();
    }

    public function custom_empty_cart_return_text() {
        return __( 'Continue Shopping', '2-step-webshop' );
    }

    /**
     * Append 'Back' button to the Checkout page (Next to place order button) redirecting to Webshop
     */
    public function add_back_button_to_checkout() {
        $shop_url = self::get_translated_shop_url();
        echo '<a href="' . esc_url( $shop_url ) . '" class="button checkout-back-button">' . __( 'Back', '2-step-webshop' ) . '</a>';
    }

    /**
     * Output custom billing / invoice notice inside the payment box right before submit
     */
    public function add_custom_checkout_notice() {
        $min_val = intval( get_option( 'pickup_min_payment_for_card', '35' ) );
        $card_disabled = ( WC()->cart && WC()->cart->total < $min_val ) ? 'yes' : 'no';
        ?>
        <div class="custom-checkout-options" data-card-payment-disabled="<?php echo esc_attr( $card_disabled ); ?>">
            <p class="checkout-custom-notice" style="margin-bottom: 20px; font-weight: 500; line-height: 1.5em;">
                <?php _e( 'Receipt and hospitality receipt will be issued upon payment at the restaurant.', '2-step-webshop' ); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Mandatory validation of pickup date, time and opening days during checkout submission
     */
    public function validate_custom_billing_fields( $data, $errors ) {
        if ( ! $errors || ! is_a( $errors, 'WP_Error' ) ) {
            return;
        }

        $pickup_date = '';
        if ( isset( $data['billing_pickup_date'] ) ) {
            $pickup_date = sanitize_text_field( $data['billing_pickup_date'] );
        } elseif ( ! empty( $_POST['billing_pickup_date'] ) ) {
            $pickup_date = sanitize_text_field( $_POST['billing_pickup_date'] );
        } elseif ( WC()->session ) {
            $pickup_date = sanitize_text_field( WC()->session->get( 'pickup_date' ) );
        }

        if ( empty( $pickup_date ) ) {
            $errors->add( 'required-field', __( 'Please choose a date.', '2-step-webshop' ) );
            return;
        }

        // For today orders only, validate business day and passed slots
        $timezone = new DateTimeZone('Europe/Berlin');
        $now = new DateTime('now', $timezone);
        $today_str = $now->format('Y-m-d');

        if ( $pickup_date === $today_str ) {
            // Block orders completely if the store is configured as closed today
            if ( ! TSW_Pickup_Scheduler::is_store_open_today() ) {
                $errors->add( 'required-field', __( 'Our restaurant is closed today. Orders are not possible.', '2-step-webshop' ) );
                return;
            }

            // Prevent ordering if all time slots for today have already passed
            if ( TSW_Pickup_Scheduler::is_store_open_today() ) {
                $first_available = TSW_Pickup_Scheduler::get_first_available_pickup_time();
                if ( empty( $first_available ) ) {
                    $errors->add( 'required-field', __( 'All time slots for today have passed. Local pickup is no longer possible today.', '2-step-webshop' ) );
                    return;
                }
            }
        }

        // Prevent past/invalid pickup times
        $pickup_time = '';
        if ( isset( $data['billing_pickup_time'] ) ) {
            $pickup_time = sanitize_text_field( $data['billing_pickup_time'] );
        } elseif ( ! empty( $_POST['billing_pickup_time'] ) ) {
            $pickup_time = sanitize_text_field( $_POST['billing_pickup_time'] );
        } elseif ( WC()->session ) {
            $pickup_time = sanitize_text_field( WC()->session->get( 'pickup_time' ) );
        }

        if ( empty( $pickup_time ) ) {
            $errors->add( 'required-field', __( 'Please choose a time.', '2-step-webshop' ) );
        } elseif ( TSW_Pickup_Scheduler::is_pickup_time_passed( $pickup_time, $pickup_date ) ) {
            $errors->add( 'required-field', __( 'The selected order time is in the past. Please choose another time.', '2-step-webshop' ) );
        }
    }

    /**
     * PHP Server-Side Validation: Restrict card payment selection if total is below dynamic minimum card setting
     */
    public function validate_card_payment_minimum_total() {
        $chosen_payment_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( $_POST['payment_method'] ) : '';
        if ( in_array( $chosen_payment_method, array( 'bacs', 'cheque' ), true ) ) {
            $min_val = intval( get_option( 'pickup_min_payment_for_card', '35' ) );
            if ( WC()->cart && WC()->cart->total < $min_val ) {
                wc_add_notice( sprintf( __( 'Card payment is only available for orders over %d € and only with EC-Card', '2-step-webshop' ), $min_val ), 'error' );
            }
        }
    }

    /**
     * Inject CSS target helper body class onto Checkout if cart is under dynamic minimum card setting
     */
    public function add_checkout_body_class_for_card_payment( $classes ) {
        if ( is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) {
            $min_val = intval( get_option( 'pickup_min_payment_for_card', '35' ) );
            if ( WC()->cart && WC()->cart->total < $min_val ) {
                $classes[] = 'card-payment-disabled';
            }
        }
        return $classes;
    }

    /**
     * Intercept the checkout select rendering to inject the HTML disabled attributes programmatically
     */
    public function render_pickup_time_select_disabled_options( $field, $key = '', $args = array(), $value = '' ) {
        $args_list = func_get_args();
        $key   = isset( $args_list[1] ) ? $args_list[1] : '';
        $args  = isset( $args_list[2] ) ? $args_list[2] : array();
        $value = isset( $args_list[3] ) ? $args_list[3] : '';

        if ( 'billing_pickup_time' === $key ) {
            $selected_date = WC()->session ? WC()->session->get( 'pickup_date' ) : '';
            
            // Auto-select the first available future time if selected is empty or already in the past
            if ( empty( $value ) || TSW_Pickup_Scheduler::is_pickup_time_passed( $value, $selected_date ) ) {
                $value = TSW_Pickup_Scheduler::get_first_available_pickup_time();
            }

            $options = '';
            if ( ! empty( $args['options'] ) ) {
                foreach ( $args['options'] as $option_key => $option_text ) {
                    $selected_attr = selected( $value, $option_key, false );
                    
                    $disabled_attr = '';
                    if ( $option_key !== '' && TSW_Pickup_Scheduler::is_pickup_time_passed( $option_key, $selected_date ) ) {
                        $disabled_attr = ' disabled="disabled" style="color: gray;"';
                    }
                    
                    $options .= '<option value="' . esc_attr( $option_key ) . '" ' . $selected_attr . $disabled_attr . '>' . esc_html( $option_text ) . '</option>';
                }
            }
            
            $field_id = esc_attr( isset( $args['id'] ) ? $args['id'] : $key );
            $field_name = esc_attr( isset( $args['name'] ) ? $args['name'] : $key );
            
            $class_list       = isset( $args['class'] ) && is_array( $args['class'] ) ? implode( ' ', $args['class'] ) : '';
            $label_class_list = isset( $args['label_class'] ) && is_array( $args['label_class'] ) ? implode( ' ', $args['label_class'] ) : '';
            $input_class_list = isset( $args['input_class'] ) && is_array( $args['input_class'] ) ? implode( ' ', $args['input_class'] ) : '';

            $field_priority   = isset( $args['priority'] ) ? $args['priority'] : 50;
            $field_html = '<p class="form-row ' . esc_attr( $class_list ) . '" id="' . $field_id . '_field" data-priority="' . esc_attr( $field_priority ) . '">';
            if ( ! empty( $args['label'] ) ) {
                $field_html .= '<label for="' . $field_id . '" class="' . esc_attr( $label_class_list ) . '">' . $args['label'] . ( $args['required'] ? ' <abbr class="required" title="' . esc_attr__( 'required', '2-step-webshop' ) . '">*</abbr>' : '' ) . '</label>';
            }
            $field_html .= '<span class="woocommerce-input-wrapper">';
            $field_html .= '<select name="' . $field_name . '" id="' . $field_id . '" class="select ' . esc_attr( $input_class_list ) . '" style="width: 100%;">';
            $field_html .= $options;
            $field_html .= '</select>';
            $field_html .= '</span>';
            $field_html .= '</p>';
            
            return $field_html;
        }
        return $field;
    }

    /**
     * Translate the checkout data privacy statement programmatically
     */
    public function german_privacy_policy_text( $text, $type = '' ) {
        $args = func_get_args();
        $type = isset( $args[1] ) ? $args[1] : '';
        
        if ( 'checkout' === $type ) {
            $privacy_page_id = function_exists( 'wc_privacy_policy_page_id' ) ? wc_privacy_policy_page_id() : 0;
            $privacy_link = $privacy_page_id ? esc_url( get_permalink( $privacy_page_id ) ) : '';
            if ( empty( $privacy_link ) ) {
                $privacy_link = '#';
            }
            
            return sprintf(
                __( 'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our <a href="%s" class="woocommerce-privacy-policy-link" target="_blank">privacy policy</a>.', '2-step-webshop' ),
                $privacy_link
            );
        }
        return $text;
    }

    /**
     * Redirect Cart page to checkout / shop for strict 2-step process
     */
    public function redirect_cart_page() {
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return;
        }
        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
            return;
        }
        if ( is_cart() ) {
            $shop_url = self::get_translated_shop_url();

            if ( WC()->cart && ! WC()->cart->is_empty() ) {
                wp_safe_redirect( wc_get_checkout_url() );
                exit;
            } else {
                wp_safe_redirect( $shop_url );
                exit;
            }
        }
    }

    /**
     * Retrieve the translated Polylang URL for the webshop page
     */
    public static function get_translated_shop_url() {
        $lang = function_exists( 'pll_current_language' ) ? pll_current_language() : '';
        if ( function_exists( 'pll_get_post' ) && $lang ) {
        if ( ! empty( $custom_url ) ) {
                    return get_permalink( $translated_id );
                }
            return get_permalink( $page->ID );
        }

            return user_trailingslashit( pll_home_url() . 'webshop' );
        }
        return home_url( '/webshop/' );
    }

    /**
    protected function tsw_get_order_language_code( $order ) {
        $locale = get_locale();
            $order_lang = $order->get_meta( '_order_language' );
            if ( ! empty( $order_lang ) ) {
                $locale = $order_lang;
            }
        }
        return ( strpos( strtolower( $locale ), 'de' ) === 0 ) ? 'de' : 'en';
    }

    /**
     * Custom customer email subject filter
     */
    public function filter_customer_email_subject( $subject, $order ) {
        if ( get_option( 'tsw_email_enable_custom_templates', 'no' ) === 'yes' ) {
            if ( empty( $custom_subject ) ) {
                $custom_subject = get_option( 'tsw_customer_email_subject', tsw_get_default_customer_email_subject( $lang ) );
            }
            if ( ! empty( $custom_subject ) ) {
                return $this->tsw_parse_email_placeholders( $custom_subject, $order, false );
            }
        }
        return $subject;
    }

    /**
     * Custom admin email subject filter
     */
    public function filter_admin_email_subject( $subject, $order ) {
        if ( get_option( 'tsw_email_enable_custom_templates', 'no' ) === 'yes' ) {
            $admin_lang_setting = get_option( 'tsw_admin_email_language', 'auto' );
            if ( 'auto' === $admin_lang_setting ) {
                $lang = $this->tsw_get_order_language_code( $order );
            } else {
                $lang = ( strpos( strtolower( $admin_lang_setting ), 'de' ) === 0 ) ? 'de' : 'en';
            }
            $custom_subject = get_option( 'tsw_admin_email_subject_' . $lang );
            if ( empty( $custom_subject ) ) {
                $custom_subject = get_option( 'tsw_admin_email_subject', tsw_get_default_admin_email_subject( $lang ) );
            }
            if ( ! empty( $custom_subject ) ) {
                return $this->tsw_parse_email_placeholders( $custom_subject, $order, true );
            }
        }
        return $subject;
    }

    /**
     * Custom customer email body filter
     */
    public function filter_customer_email_body( $content, $order ) {
        if ( get_option( 'tsw_email_enable_custom_templates', 'no' ) === 'yes' ) {
            $lang = $this->tsw_get_order_language_code( $order );
            $custom_body = get_option( 'tsw_customer_email_body_' . $lang );
            if ( empty( $custom_body ) ) {
                $custom_body = get_option( 'tsw_customer_email_body', tsw_get_default_customer_email_body( $lang ) );
            }
            if ( ! empty( $custom_body ) ) {
                return $this->tsw_parse_email_placeholders( $custom_body, $order, false );
            }
        }
        return $content;
    }

    /**
     * Custom admin email body filter
     */
    public function filter_admin_email_body( $content, $order ) {
        if ( get_option( 'tsw_email_enable_custom_templates', 'no' ) === 'yes' ) {
            $admin_lang_setting = get_option( 'tsw_admin_email_language', 'auto' );
            if ( 'auto' === $admin_lang_setting ) {
                $lang = $this->tsw_get_order_language_code( $order );
            } else {
                $lang = ( strpos( strtolower( $admin_lang_setting ), 'de' ) === 0 ) ? 'de' : 'en';
            }
            $custom_body = get_option( 'tsw_admin_email_body_' . $lang );
            if ( empty( $custom_body ) ) {
                $custom_body = get_option( 'tsw_admin_email_body', tsw_get_default_admin_email_body( $lang ) );
            }
            if ( ! empty( $custom_body ) ) {
                return $this->tsw_parse_email_placeholders( $custom_body, $order, true );
            }
        }
        return $content;
    }

    /**
     * Parse {} template variables for custom emails
     */
    public function tsw_parse_email_placeholders( $content, $order, $sent_to_admin = false ) {
        if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
            return $content;
        }

        $store_name    = tsw_get_restaurant_name();
        $store_address = tsw_get_store_address();

        // Logo / Banner HTML
        $store_logo_url = tsw_get_store_logo_url();
        $hero_img       = get_option( 'custom_shop_hero_image', '' );
        $logo_html      = '';
        if ( ! empty( $store_logo_url ) ) {
            $logo_html = '<img src="' . esc_url( $store_logo_url ) . '" alt="' . esc_attr( $store_name ) . '" style="max-height: 80px; width: auto; display: block; margin: 0 auto;">';
        } elseif ( ! empty( $hero_img ) ) {
            $logo_html = '<img src="' . esc_url( $hero_img ) . '" alt="' . esc_attr( $store_name ) . '" style="max-height: 80px; width: auto; display: block; margin: 0 auto;">';
        }

        $customer_name  = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
        $customer_email = $order->get_billing_email();
        $customer_phone = $order->get_billing_phone();
        $order_number   = $order->get_order_number();
        $order_date     = wc_format_datetime( $order->get_date_created() );

        // Fulfillment method & scheduled time
        $pickup_date = $order->get_meta( '_pickup_date' );
        $pickup_time = $order->get_meta( '_pickup_time' );
        $time_str    = trim( $pickup_date . ' ' . $pickup_time );
        
        $shipping_methods = $order->get_shipping_methods();
        $is_delivery = false;
        foreach ( $shipping_methods as $method ) {
            if ( strpos( $method->get_method_id(), 'flat_rate' ) !== false ) {
                $is_delivery = true;
                break;
            }
        }
        $fulfillment_method = $is_delivery ? __( 'Delivery', '2-step-webshop' ) : __( 'Pickup', '2-step-webshop' );

        // Special request
        $special_request = $order->get_meta( '_special_request_note' );
        if ( empty( $special_request ) ) {
            $special_request = $order->get_customer_note();
        }

        // Order Table HTML
        $order_table_html = $this->build_email_order_table_html( $order );

        $replacements = array(
            '{restaurant_name}'      => $store_name,
            '{restaurant_address}'   => $store_address,
            '{restaurant_logo}'      => $logo_html,
            '{customer_name}'        => $customer_name,
            '{customer_email}'       => $customer_email,
            '{customer_phone}'       => $customer_phone,
            '{order_number}'         => $order_number,
            '{order_date}'           => $order_date,
            '{order_table}'          => $order_table_html,
            '{fulfillment_method}'   => $fulfillment_method,
            '{pickup_delivery_time}' => $time_str,
            '{special_request}'      => ! empty( $special_request ) ? esc_html( $special_request ) : 'N/A',
        );

        return str_replace( array_keys( $replacements ), array_values( $replacements ), $content );
    }

    /**
     * Helper to render itemized order table for HTML emails
     */
    protected function build_email_order_table_html( $order ) {
        ob_start();
        ?>
        <table cellspacing="0" cellpadding="8" border="0" style="width: 100%; border: 1px solid #e5e7eb; border-collapse: collapse; margin-top: 15px; margin-bottom: 15px; font-size: 14px;">
            <thead>
                <tr style="background-color: #f8fafc; text-align: left;">
                    <th style="border-bottom: 2px solid #e5e7eb; padding: 10px;"><?php esc_html_e( 'Product', '2-step-webshop' ); ?></th>
                    <th style="border-bottom: 2px solid #e5e7eb; padding: 10px; text-align: center;"><?php esc_html_e( 'Qty', '2-step-webshop' ); ?></th>
                    <th style="border-bottom: 2px solid #e5e7eb; padding: 10px; text-align: right;"><?php esc_html_e( 'Price', '2-step-webshop' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ( $order->get_items() as $item_id => $item ) :
                    ?>
                    <tr>
                        <td style="border-bottom: 1px solid #f1f5f9; padding: 10px;">
                            <strong><?php echo esc_html( $item->get_name() ); ?></strong>
                            <?php
                            $meta_data = $item->get_formatted_meta_data( '_' );
                            if ( ! empty( $meta_data ) ) {
                                echo '<ul style="margin: 4px 0 0 0; padding-left: 16px; font-size: 12px; color: #64748b;">';
                                foreach ( $meta_data as $meta ) {
                                    echo '<li>' . wp_kses_post( $meta->display_key . ': ' . $meta->display_value ) . '</li>';
                                }
                                echo '</ul>';
                            }
                            ?>
                        </td>
                        <td style="border-bottom: 1px solid #f1f5f9; padding: 10px; text-align: center;">
                            <?php echo intval( $item->get_quantity() ); ?>
                        </td>
                        <td style="border-bottom: 1px solid #f1f5f9; padding: 10px; text-align: right;">
                            <?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <?php foreach ( $order->get_order_item_totals() as $total ) : ?>
                    <tr>
                        <th colspan="2" style="text-align: right; border-top: 1px solid #e5e7eb; padding: 8px; font-weight: 600;">
                            <?php echo esc_html( $total['label'] ); ?>
                        </th>
                        <td style="text-align: right; border-top: 1px solid #e5e7eb; padding: 8px; font-weight: 600;">
                            <?php echo wp_kses_post( $total['value'] ); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tfoot>
        </table>
        <?php
        return ob_get_clean();
    }
}
