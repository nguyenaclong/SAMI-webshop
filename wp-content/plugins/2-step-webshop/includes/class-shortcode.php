<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TSW_Shortcode {

    public function __construct() {
        add_shortcode( 'two_step_webshop_layout', array( $this, 'render_shortcode' ) );
    }

    public function render_shortcode( $atts ) {
        // Enqueue assets when shortcode is used
        wp_enqueue_script( 'wc-add-to-cart-variation' );

        wp_enqueue_style( 'custom-shop-styles', TSW_URL . 'assets/css/custom-shop.css', array(), TSW_VERSION );
        wp_enqueue_script( 'custom-shop-scripts', TSW_URL . 'assets/js/custom-shop.js', array('jquery'), TSW_VERSION, true );
        
        $pickup_address = tsw_get_store_address();

        wp_localize_script( 'custom-shop-scripts', 'customShopData', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'tsw_ajax_nonce' ),
            'i18n'    => array(
                'selectOptions'      => __( 'Please select all product options', '2-step-webshop' ),
                'selectValidOptions' => __( 'Please select valid product options.', '2-step-webshop' ),
                'errorAddToCart'     => __( 'Error adding to cart', '2-step-webshop' ),
                'confirmClearCart'   => __( 'Are you sure you want to clear the cart?', '2-step-webshop' ),
                'pickupAddress'      => $pickup_address,
                'pickupAt'           => __( 'Pickup at %s', '2-step-webshop' ),
                'deliveryToStuttgart'=> __( 'Delivery to Stuttgart', '2-step-webshop' ),
                'deliveryTo'         => __( 'Delivery to %s', '2-step-webshop' ),
                'scheduleForLater'   => __( 'Schedule for later', '2-step-webshop' ),
                'showMore'                => __( 'Show more', '2-step-webshop' ),
                'showLess'                => __( 'Show less', '2-step-webshop' ),
                'enterDeliveryAddress'    => __( 'Please enter a delivery address.', '2-step-webshop' ),
                'searchingAddress'        => __( 'Searching for address...', '2-step-webshop' ),
                'addressFound'            => __( 'Address found: %s (%s km).', '2-step-webshop' ),
                'addressNotFound'         => __( 'Address not found. Please try adding more details (street number, city).', '2-step-webshop' ),
                'geocoderError'           => __( 'Error connecting to geocoder. Please check your connection.', '2-step-webshop' ),
                'unableRetrieveLocation'  => __( 'Unable to retrieve your location', '2-step-webshop' ),
                'selectDateTime'          => __( 'Please select a date and time.', '2-step-webshop' ),
                'adding'                  => __( 'Adding...', '2-step-webshop' ),
                'added'                   => __( '✓ Added!', '2-step-webshop' ),
                'errorAddToCartRetry'     => __( 'Error adding to cart. Please try again.', '2-step-webshop' ),
            ),
        ) );

        // Generate inline styles from Customizer & Style settings
        $inline_styles = '';
        $var_rules = array();

        // 1. Buttons CSS overrides
        $btn_bg         = tsw_sanitize_css_value( tsw_get_option( 'custom_shop_btn_bg_color', 'var(--theme-palette-color-3)' ) );
        $btn_hover_bg   = tsw_sanitize_css_value( tsw_get_option( 'custom_shop_btn_hover_bg_color', 'var(--theme-palette-color-2)' ) );
        $btn_text       = tsw_sanitize_css_value( tsw_get_option( 'custom_shop_btn_text_color', 'var(--theme-palette-color-8)' ) );
        $btn_hover_text = tsw_sanitize_css_value( tsw_get_option( 'custom_shop_btn_hover_text_color', 'var(--theme-palette-color-8)' ) );
        $border_radius  = tsw_get_option( 'custom_shop_btn_border_radius' );

        if ( ! empty( $btn_bg ) ) {
            $var_rules[] = "--theme-button-background-initial-color: {$btn_bg} !important;";
            $var_rules[] = "--cs-highlight-color: {$btn_bg} !important;";
        }
        if ( ! empty( $btn_hover_bg ) ) {
            $var_rules[] = "--theme-button-background-hover-color: {$btn_hover_bg} !important;";
        }
        if ( ! empty( $btn_text ) ) {
            $var_rules[] = "--theme-button-text-initial-color: {$btn_text} !important;";
        }
        if ( ! empty( $btn_hover_text ) ) {
            $var_rules[] = "--theme-button-text-hover-color: {$btn_hover_text} !important;";
        }
        if ( $border_radius !== '' && $border_radius !== false && $border_radius !== null ) {
            $border_radius_int = intval( $border_radius );
            $var_rules[] = "--theme-button-border-radius: {$border_radius_int}px !important;";
        }

        // 2. Colors & Backgrounds
        $card_bg = tsw_sanitize_css_value( tsw_get_option( 'custom_shop_card_bg_color' ) );
        if ( ! empty( $card_bg ) ) {
            $var_rules[] = "--tsw-card-bg: {$card_bg} !important;";
        }

        $header_bg = tsw_sanitize_css_value( tsw_get_option( 'custom_shop_header_bg_color' ) );
        if ( ! empty( $header_bg ) ) {
            $var_rules[] = "--tsw-header-bg: {$header_bg} !important;";
        }

        $modal_bg = tsw_sanitize_css_value( tsw_get_option( 'custom_shop_modal_bg_color' ) );
        if ( ! empty( $modal_bg ) ) {
            $var_rules[] = "--tsw-modal-bg: {$modal_bg} !important;";
        }

        $price_color = tsw_sanitize_css_value( tsw_get_option( 'custom_shop_price_color' ) );
        if ( ! empty( $price_color ) ) {
            $var_rules[] = "--tsw-price-color: {$price_color} !important;";
        }

        // 3. Border Radii
        $card_radius = tsw_get_option( 'custom_shop_card_border_radius' );
        if ( $card_radius !== '' && $card_radius !== false && $card_radius !== null ) {
            $var_rules[] = "--tsw-card-border-radius: " . intval( $card_radius ) . "px !important;";
        }

        $modal_radius = tsw_get_option( 'custom_shop_modal_border_radius' );
        if ( $modal_radius !== '' && $modal_radius !== false && $modal_radius !== null ) {
            $var_rules[] = "--tsw-modal-border-radius: " . intval( $modal_radius ) . "px !important;";
        }

        $pill_radius = tsw_get_option( 'custom_shop_pill_border_radius' );
        if ( $pill_radius !== '' && $pill_radius !== false && $pill_radius !== null ) {
            $var_rules[] = "--tsw-pill-border-radius: " . intval( $pill_radius ) . "px !important;";
        }

        // 4. Typography & Font Sizes
        $header_font_size = tsw_get_option( 'custom_shop_header_font_size' );
        if ( $header_font_size !== '' && $header_font_size !== false && $header_font_size !== null ) {
            $var_rules[] = "--tsw-header-font-size: " . intval( $header_font_size ) . "px !important;";
        }

        $title_font_size = tsw_get_option( 'custom_shop_title_font_size' );
        if ( $title_font_size !== '' && $title_font_size !== false && $title_font_size !== null ) {
            $var_rules[] = "--tsw-title-font-size: " . intval( $title_font_size ) . "px !important;";
        }

        $price_font_size = tsw_get_option( 'custom_shop_price_font_size' );
        if ( $price_font_size !== '' && $price_font_size !== false && $price_font_size !== null ) {
            $var_rules[] = "--tsw-price-font-size: " . intval( $price_font_size ) . "px !important;";
        }

        $body_font_size = tsw_get_option( 'custom_shop_body_font_size' );
        if ( $body_font_size !== '' && $body_font_size !== false && $body_font_size !== null ) {
            $var_rules[] = "--tsw-body-font-size: " . intval( $body_font_size ) . "px !important;";
        }

        // 5. Container & Column Widths
        $container_max_width = tsw_get_option( 'custom_shop_container_max_width' );
        if ( $container_max_width !== '' && $container_max_width !== false && $container_max_width !== null ) {
            $var_rules[] = "--tsw-container-max-width: " . intval( $container_max_width ) . "px !important;";
        }

        $sidebar_width = tsw_get_option( 'custom_shop_sidebar_width' );
        if ( $sidebar_width !== '' && $sidebar_width !== false && $sidebar_width !== null ) {
            $var_rules[] = "--tsw-sidebar-width: " . intval( $sidebar_width ) . "px !important;";
        }

        $modal_max_width = tsw_get_option( 'custom_shop_modal_max_width' );
        if ( $modal_max_width !== '' && $modal_max_width !== false && $modal_max_width !== null ) {
            $var_rules[] = "--tsw-modal-max-width: " . intval( $modal_max_width ) . "px !important;";
        }

        $drawer_width = tsw_get_option( 'custom_shop_drawer_width' );
        if ( $drawer_width !== '' && $drawer_width !== false && $drawer_width !== null ) {
            $var_rules[] = "--tsw-drawer-width: " . intval( $drawer_width ) . "px !important;";
        }

        // Base text & link colors
        $text_color       = tsw_sanitize_css_value( tsw_get_option( 'custom_shop_text_color' ) );
        $link_color       = tsw_sanitize_css_value( tsw_get_option( 'custom_shop_link_color' ) );
        $link_hover_color = tsw_sanitize_css_value( tsw_get_option( 'custom_shop_link_hover_color' ) );

        if ( ! empty( $text_color ) ) {
            $var_rules[] = "color: {$text_color} !important;";
        }

        // Compile CSS rules onto container
        if ( ! empty( $var_rules ) ) {
            $rules_str = implode( ' ', $var_rules );
            $inline_styles .= "
                .custom-shop-container,
                .floating-cart-container,
                .mobile-floating-actions,
                .csp-modal-overlay,
                .csp-modal-container,
                .csp-cart-drawer-overlay,
                .csp-cart-drawer-container,
                .csp-floating-cart-drawer {
                    {$rules_str}
                }
            ";
        }

        if ( ! empty( $link_color ) ) {
            $inline_styles .= "
                .custom-shop-container a,
                .shop-categories-list a {
                    color: {$link_color} !important;
                }
            ";
        }

        if ( ! empty( $link_hover_color ) ) {
            $inline_styles .= "
                .custom-shop-container a:hover,
                .shop-categories-list a:hover {
                    color: {$link_hover_color} !important;
                }
            ";
        }

        if ( ! empty( $inline_styles ) ) {
            wp_add_inline_style( 'custom-shop-styles', $inline_styles );
        }

        // Set static parameters (since there is only one layout now)
        $preset          = 'horizontal-layout';
        $cat_pos         = 'n14-sidebar';
        $cart_pos        = 'right-sidebar';
        $prod_cols       = '1';
        $show_hero       = false;
        $header_style    = 'n14-restaurant';
        $show_pickup     = true;



        $open_time       = get_option( 'pickup_opening_time', '11:30' );
        $close_time      = get_option( 'pickup_closing_time', '22:00' );
        $day_keys        = [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ];
        $day_labels      = [ 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' ];
        $open_days       = [];
        foreach ( $day_keys as $i => $day ) {
            if ( get_option( 'pickup_open_' . $day, 'yes' ) === 'yes' ) {
                $open_days[] = $day_labels[$i];
            }
        }
        $days_str = empty( $open_days ) ? '' : implode( '–', [ $open_days[0], $open_days[ count($open_days) - 1 ] ] );
        $n14_opening_hours = $days_str . ': ' . $open_time . ' – ' . $close_time;

        $show_carousel   = true;
        $img_shape       = 'circle';
        $img_pos         = 'right';
        $search_look     = 'framed';
        $icon_size       = 'medium';
        $btn_size        = 'medium';
        $cart_layout     = 'compact';
        $show_qty        = 'yes';
        $add_to_cart_act = 'popup';
        $cart_click_act  = 'drawer';

        $hero_image = '';
        $hero_title = tsw_get_restaurant_name();
        $hero_desc  = '';

        // Build wrapper classes
        $wrapper_classes = array(
            'custom-shop-container',
            'custom-shop-page',
            'csp-preset-' . esc_attr( $preset ),
            'csp-cat-pos-' . esc_attr( $cat_pos ),
            'csp-cart-pos-' . esc_attr( $cart_pos ),
            'csp-cols-' . esc_attr( $prod_cols ),
            $show_hero ? 'csp-has-hero' : 'csp-no-hero',
            'csp-header-style-' . esc_attr( $header_style ),
            'csp-img-shape-' . esc_attr( $img_shape ),
            'csp-img-pos-' . esc_attr( $img_pos ),
            'csp-search-look-' . esc_attr( $search_look ),
            'csp-icon-size-' . esc_attr( $icon_size ),
            'csp-btn-size-' . esc_attr( $btn_size ),
            'csp-cart-layout-' . esc_attr( $cart_layout ),
            'csp-show-qty-' . esc_attr( $show_qty ),
            'csp-add-to-cart-act-' . esc_attr( $add_to_cart_act ),
            'csp-cart-click-act-' . esc_attr( $cart_click_act )
        );
        $container_class_str = implode( ' ', $wrapper_classes );

        ob_start();
        include TSW_DIR . 'templates/custom-shop-layout.php';
        return ob_get_clean();
    }
}
