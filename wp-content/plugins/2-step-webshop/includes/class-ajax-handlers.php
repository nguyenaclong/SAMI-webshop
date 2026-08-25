<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TSW_Ajax_Handlers {

    public function __construct() {
        add_action( 'wp_ajax_custom_clear_cart', array( $this, 'clear_cart' ) );
        add_action( 'wp_ajax_nopriv_custom_clear_cart', array( $this, 'clear_cart' ) );
        
        add_action( 'wp_ajax_tsw_update_cart_item_qty', array( $this, 'update_cart_item_qty' ) );
        add_action( 'wp_ajax_nopriv_tsw_update_cart_item_qty', array( $this, 'update_cart_item_qty' ) );

        add_action( 'wp_ajax_save_pickup_time_session', array( $this, 'save_pickup_time_session' ) );
        add_action( 'wp_ajax_nopriv_save_pickup_time_session', array( $this, 'save_pickup_time_session' ) );
        check_ajax_referer( 'tsw_ajax_nonce', 'security' );

            if ( isset( $_POST['shipping_method'] ) ) {
                $method = sanitize_text_field( $_POST['shipping_method'] );
                $matching_rate_id = '';
                $packages = WC()->shipping() ? WC()->shipping()->get_packages() : array();
                if ( ! empty( $packages ) ) {
                    foreach ( $packages as $package ) {
                        if ( isset( $package['rates'] ) && is_array( $package['rates'] ) ) {
                            foreach ( $package['rates'] as $rate_id => $rate ) {
                                if ( $method === 'delivery' && ( strpos( $rate->method_id, 'flat_rate' ) !== false || strpos( $rate_id, 'flat_rate' ) !== false ) ) {
                                    $matching_rate_id = $rate_id;
                                    break 2;
                                } elseif ( $method !== 'delivery' && ( strpos( $rate->method_id, 'local_pickup' ) !== false || strpos( $rate_id, 'local_pickup' ) !== false ) ) {
                                    $matching_rate_id = $rate_id;
                                    break 2;
                                }
                            }
                        }
                    }
                }

                if ( empty( $matching_rate_id ) ) {
                    $matching_rate_id = ( $method === 'delivery' ) ? 'flat_rate:fallback' : 'local_pickup:fallback';
                }

                WC()->session->set( 'chosen_shipping_methods', array( $matching_rate_id ) );
            }
            if ( isset( $_POST['delivery_address'] ) ) {
                $address = sanitize_text_field( $_POST['delivery_address'] );
                WC()->session->set( 'delivery_address', $address );
                if ( WC()->customer ) {
                    WC()->customer->set_shipping_address_1( $address );
                    WC()->customer->set_billing_address_1( $address );
                    // Let's also set the city to Stuttgart (as default for N14 area delivery)
                    WC()->customer->set_shipping_city( 'Stuttgart' );
                    WC()->customer->set_billing_city( 'Stuttgart' );
                    WC()->customer->save();
                }
            }
            if ( isset( $_POST['delivery_distance'] ) ) {
                WC()->session->set( 'delivery_distance', sanitize_text_field( $_POST['delivery_distance'] ) );
            }
            wp_send_json_success();
        }
        wp_send_json_error();
    }
}
