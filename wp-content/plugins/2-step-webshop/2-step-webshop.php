<?php
/**
 * Plugin Name: 2 Step Webshop
 * Description: Standalone plugin for the 2 Step Webshop Layout, independent of themes.
 * Version: 1.4.9
 * Author: AI Assistant
 * Requires Plugins: woocommerce
 * Text Domain: 2-step-webshop
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'TSW_VERSION', '1.4.9' );
define( 'TSW_DIR', plugin_dir_path( __FILE__ ) );
define( 'TSW_URL', plugin_dir_url( __FILE__ ) );

if ( ! defined( 'K84_PICKUP_LEAD_MIN' ) ) {
    define( 'K84_PICKUP_LEAD_MIN', 25 );
}

// Load classes
require_once TSW_DIR . 'includes/class-pickup-scheduler.php';
require_once TSW_DIR . 'includes/class-checkout.php';
require_once TSW_DIR . 'includes/class-shortcode.php';
require_once TSW_DIR . 'includes/class-ajax-handlers.php';
require_once TSW_DIR . 'includes/class-woo-fragments.php';
require_once TSW_DIR . 'includes/class-settings.php';
require_once TSW_DIR . 'includes/class-woo-compat.php';

// Helper to retrieve option with theme mod fallback and default value
// Bug #15 fix: treat empty string '' as missing so defaults are applied correctly
function tsw_get_option( $name, $default = '' ) {
    $value = get_option( $name );
    if ( false === $value || '' === $value ) {
        // Fallback to theme mod
        $theme_mod = get_theme_mod( $name );
        if ( false !== $theme_mod && '' !== $theme_mod ) {
            return $theme_mod;
        }
        return $default;
    }
    return $value;
}

/**
 * Sanitize a CSS color/value to prevent CSS injection.
 * Allows hex colors, rgb/rgba/hsl/hsla functions, numeric/pixel values, and CSS var() references.
 * Returns empty string for anything else.
 */
function tsw_sanitize_css_value( $value ) {
    $value = trim( $value );
    if ( $value === '' ) {
        return '';
    }
    // Allow hex colors: #abc, #aabbcc, #aabbccdd
    if ( preg_match( '/^#[0-9a-fA-F]{3,8}$/', $value ) ) {
        return $value;
    }
    // Allow rgb(), rgba(), hsl(), hsla() with numeric/comma/dot/space/% content
    if ( preg_match( '/^(rgb|rgba|hsl|hsla)\(\s*[\d\s%,.\-\/]+\s*\)$/i', $value ) ) {
        return $value;
    }
    // Allow CSS var() references: var(--anything-valid)
    if ( preg_match( '/^var\(\s*--[a-zA-Z0-9\-]+\s*\)$/', $value ) ) {
        return $value;
    }
    // Reject everything else (could be injection payload)
    return '';
}

// Initialize on plugins loaded
add_action( 'plugins_loaded', 'tsw_init_plugin' );
function tsw_init_plugin() {
    new TSW_Shortcode();
    new TSW_Ajax_Handlers();
    new TSW_Woo_Fragments();
    new TSW_Settings();
    new TSW_Woo_Compat();
    new TSW_Checkout();
}

// Load plugin text domain for translation
add_action( 'init', 'tsw_load_textdomain' );
function tsw_load_textdomain() {
    load_plugin_textdomain( '2-step-webshop', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

// Register custom shop layout strings for Polylang translation
add_action( 'init', 'tsw_register_polylang_strings' );
function tsw_register_polylang_strings() {
    if ( function_exists( 'pll_register_string' ) ) {
        pll_register_string( 'Go to Cart', 'Go to Cart', '2 Step Webshop' );
        pll_register_string( 'Cart', 'Cart', '2 Step Webshop' );
        pll_register_string( 'Your cart is empty.', 'Your cart is empty.', '2 Step Webshop' );
        pll_register_string( 'Most Popular', 'Most Popular', '2 Step Webshop' );
        pll_register_string( 'Open', 'Open', '2 Step Webshop' );
        pll_register_string( 'Opens at %s', 'Opens at %s', '2 Step Webshop' );
        pll_register_string( 'Select Order Date & Time', 'Select Order Date & Time', '2 Step Webshop' );
        pll_register_string( 'Please select your order method, date and time to continue.', 'Please select your order method, date and time to continue.', '2 Step Webshop' );
        pll_register_string( 'Order Date', 'Order Date', '2 Step Webshop' );
        pll_register_string( 'Order Time', 'Order Time', '2 Step Webshop' );
        pll_register_string( 'More Dates...', 'More Dates...', '2 Step Webshop' );
        pll_register_string( 'Save & Continue', 'Save & Continue', '2 Step Webshop' );
        pll_register_string( 'Select Time...', 'Select Time...', '2 Step Webshop' );
        pll_register_string( 'Closed today', 'Closed today', '2 Step Webshop' );
        pll_register_string( 'Please choose a date.', 'Please choose a date.', '2 Step Webshop' );
        pll_register_string( 'Our restaurant is closed today. Orders are not possible.', 'Our restaurant is closed today. Orders are not possible.', '2 Step Webshop' );
        pll_register_string( 'All time slots for today have passed. Local pickup is no longer possible today.', 'All time slots for today have passed. Local pickup is no longer possible today.', '2 Step Webshop' );
        pll_register_string( 'Please choose a time.', 'Please choose a time.', '2 Step Webshop' );
        pll_register_string( 'The selected order time is in the past. Please choose another time.', 'The selected order time is in the past. Please choose another time.', '2 Step Webshop' );
        pll_register_string( 'Today', 'Today', '2 Step Webshop' );
        pll_register_string( 'Tomorrow', 'Tomorrow', '2 Step Webshop' );
        pll_register_string( 'Sunday', 'Sunday', '2 Step Webshop' );
        pll_register_string( 'Monday', 'Monday', '2 Step Webshop' );
        pll_register_string( 'Tuesday', 'Tuesday', '2 Step Webshop' );
        pll_register_string( 'Wednesday', 'Wednesday', '2 Step Webshop' );
        pll_register_string( 'Thursday', 'Thursday', '2 Step Webshop' );
        pll_register_string( 'Friday', 'Friday', '2 Step Webshop' );
        pll_register_string( 'Saturday', 'Saturday', '2 Step Webshop' );
        pll_register_string( 'Select location', 'Select location', '2 Step Webshop' );
        pll_register_string( 'Schedule for later', 'Schedule for later', '2 Step Webshop' );
        pll_register_string( 'Change', 'Change', '2 Step Webshop' );
        pll_register_string( 'View Menu', 'View Menu', '2 Step Webshop' );
        pll_register_string( 'Scheduled Order', 'Scheduled Order', '2 Step Webshop' );
        pll_register_string( 'Show more', 'Show more', '2 Step Webshop' );
        pll_register_string( 'Show less', 'Show less', '2 Step Webshop' );
        pll_register_string( 'Schedule Order', 'Schedule Order', '2 Step Webshop' );
        pll_register_string( 'Information', 'Information', '2 Step Webshop' );
        pll_register_string( 'Pickup at %s', 'Pickup at %s', '2 Step Webshop' );
        pll_register_string( 'Delivery to Stuttgart', 'Delivery to Stuttgart', '2 Step Webshop' );
        pll_register_string( 'Delivery to %s', 'Delivery to %s', '2 Step Webshop' );
        pll_register_string( 'Your basket is empty', 'Your basket is empty', '2 Step Webshop' );
        pll_register_string( 'Add items to get started', 'Add items to get started', '2 Step Webshop' );
        pll_register_string( 'Please enter a delivery address.', 'Please enter a delivery address.', '2 Step Webshop' );
        pll_register_string( 'Searching for address...', 'Searching for address...', '2 Step Webshop' );
        pll_register_string( 'Address found: %s (%s km).', 'Address found: %s (%s km).', '2 Step Webshop' );
        pll_register_string( 'Address not found. Please try adding more details (street number, city).', 'Address not found. Please try adding more details (street number, city).', '2 Step Webshop' );
        pll_register_string( 'Error connecting to geocoder. Please check your connection.', 'Error connecting to geocoder. Please check your connection.', '2 Step Webshop' );
        pll_register_string( 'Unable to retrieve your location', 'Unable to retrieve your location', '2 Step Webshop' );
        pll_register_string( 'Please select a date and time.', 'Please select a date and time.', '2 Step Webshop' );
        pll_register_string( 'Adding...', 'Adding...', '2 Step Webshop' );
        pll_register_string( '✓ Added!', '✓ Added!', '2 Step Webshop' );
        pll_register_string( 'Error adding to cart. Please try again.', 'Error adding to cart. Please try again.', '2 Step Webshop' );
        pll_register_string( 'Enter your delivery address', 'Enter your delivery address', '2 Step Webshop' );
        pll_register_string( 'Use my current position', 'Use my current position', '2 Step Webshop' );
        pll_register_string( 'Pickup', 'Pickup', '2 Step Webshop' );
        pll_register_string( 'Delivery', 'Delivery', '2 Step Webshop' );
        pll_register_string( 'Back', 'Back', '2 Step Webshop' );
    }
}

// Global helper for printing translated strings
function tsw_e( $string ) {
    if ( function_exists( 'pll__' ) ) {
        echo esc_html( pll__( $string ) );
    } else {
        echo esc_html( __( $string, '2-step-webshop' ) );
    }
}

// Global helper for returning translated strings
function tsw__( $string ) {
    if ( function_exists( 'pll__' ) ) {
        return pll__( $string );
    } else {
        return __( $string, '2-step-webshop' );
    }
}

// Translate custom settings option dynamically for multi-language plugins
add_filter( 'option_custom_shop_sidebar_text', 'tsw_translate_sidebar_text' );
function tsw_translate_sidebar_text( $value ) {
    if ( ! $value ) {
        $value = __( 'Your Daily Fresh Products', '2-step-webshop' );
    }
    
    // Polylang translation compatibility
    if ( function_exists( 'pll__' ) ) {
        return pll__( $value );
    }
    
    // WPML translation compatibility
    if ( function_exists( 'wpml_translate_single_string' ) ) {
        return wpml_translate_single_string( '2-step-webshop', 'custom_shop_sidebar_text', $value );
    }
    
    return apply_filters( 'wpml_translate_single_string', $value, '2-step-webshop', 'custom_shop_sidebar_text' );
}

// Helper to get active theme color defaults
function tsw_get_theme_defaults() {
    $theme = wp_get_theme();
    $template = strtolower( $theme->get_template() );

    $defaults = array(
        'custom_shop_btn_bg_color'          => '#2563eb',
        'custom_shop_btn_hover_bg_color'    => '#1d4ed8',
        'custom_shop_btn_text_color'        => '#ffffff',
        'custom_shop_btn_hover_text_color'  => '#ffffff',
        'custom_shop_text_color'            => '',
        'custom_shop_link_color'            => '',
        'custom_shop_link_hover_color'      => '',
    );

    if ( 'blocksy' === $template ) {
        $defaults['custom_shop_btn_bg_color']         = 'var(--theme-palette-color-3)';
        $defaults['custom_shop_btn_hover_bg_color']   = 'var(--theme-palette-color-2)';
        $defaults['custom_shop_btn_text_color']       = 'var(--theme-palette-color-8)';
        $defaults['custom_shop_btn_hover_text_color'] = 'var(--theme-palette-color-8)';
    } elseif ( 'astra' === $template ) {
        $defaults['custom_shop_btn_bg_color']         = 'var(--ast-global-color-0)';
        $defaults['custom_shop_btn_hover_bg_color']   = 'var(--ast-global-color-1)';
        $defaults['custom_shop_btn_text_color']       = '#ffffff';
        $defaults['custom_shop_btn_hover_text_color'] = '#ffffff';
    } elseif ( 'generatepress' === $template ) {
        $defaults['custom_shop_btn_bg_color']         = 'var(--accent)';
        $defaults['custom_shop_btn_hover_bg_color']   = 'var(--contrast)';
        $defaults['custom_shop_btn_text_color']       = '#ffffff';
        $defaults['custom_shop_btn_hover_text_color'] = '#ffffff';
    }

    return $defaults;
}

// Activation hook for migration
register_activation_hook( __FILE__, 'tsw_activate_plugin' );
function tsw_activate_plugin() {
    $settings = array(
        'custom_shop_layout_preset'            => 'horizontal-layout',
        'custom_shop_category_menu_pos'        => 'n14-sidebar',
        'custom_shop_cart_pos'                 => 'right-sidebar',
        'custom_shop_product_columns'          => '1',
        'custom_shop_always_expand_categories' => 'yes',
        'custom_shop_product_image_shape'      => 'circle',
        'custom_shop_product_image_position'   => 'right',
        'custom_shop_search_bar_look'          => 'framed',
        'custom_shop_icon_size'                => 'medium',
        'custom_shop_button_size'              => 'medium',
        'custom_shop_cart_layout'              => 'compact',
        'custom_shop_show_quantity'            => 'yes',
        'custom_shop_add_to_cart_action'       => 'popup',
        'custom_shop_cart_click_action'        => 'drawer',
        'custom_shop_sidebar_text'             => 'Your Daily Fresh Products',
        'custom_shop_btn_bg_color'             => '#39b54a',
        'custom_shop_btn_hover_bg_color'       => '#2d8f3a',
        'custom_shop_btn_text_color'           => '#ffffff',
        'custom_shop_btn_hover_text_color'     => '#ffffff',
        'custom_shop_btn_border_radius'        => '8',
        'custom_shop_text_color'               => '',
        'custom_shop_link_color'               => '',
        'custom_shop_link_hover_color'         => '',
        'custom_shop_show_hero'                => 'no',
        'custom_shop_hero_image'               => '',
        'custom_shop_hero_title'               => '',
        'custom_shop_hero_desc'                => '',
        'custom_shop_card_bg_color'            => '',
        'custom_shop_header_bg_color'          => '',
        'custom_shop_modal_bg_color'           => '',
        'custom_shop_price_color'              => '',
        'custom_shop_card_border_radius'       => '',
        'custom_shop_modal_border_radius'      => '',
        'custom_shop_pill_border_radius'       => '',
        'custom_shop_title_font_size'          => '',
        'custom_shop_body_font_size'           => '',
        'custom_shop_header_font_size'         => '',
        'custom_shop_price_font_size'          => '',
        'custom_shop_container_max_width'      => '',
        'custom_shop_sidebar_width'            => '',
        'custom_shop_modal_max_width'          => '',
        'custom_shop_drawer_width'             => '',
        'custom_shop_variable_price_display'   => 'min',
        'custom_shop_show_cat_image'           => 'no',
        'custom_shop_show_cat_desc'            => 'yes',
        'custom_shop_cat_text_align'           => 'left',
        'pickup_use_same_hours'                => 'yes',
        'tsw_admin_notification_emails'        => '',
        'tsw_email_enable_custom_templates'    => 'no',
        'tsw_customer_email_subject_en'        => tsw_get_default_customer_email_subject( 'en' ),
        'tsw_customer_email_subject_de'        => tsw_get_default_customer_email_subject( 'de' ),
        'tsw_customer_email_body_en'           => tsw_get_default_customer_email_body( 'en' ),
        'tsw_customer_email_body_de'           => tsw_get_default_customer_email_body( 'de' ),
        'tsw_admin_email_subject_en'           => tsw_get_default_admin_email_subject( 'en' ),
        'tsw_admin_email_subject_de'           => tsw_get_default_admin_email_subject( 'de' ),
        'tsw_admin_email_body_en'              => tsw_get_default_admin_email_body( 'en' ),
        'tsw_admin_email_body_de'              => tsw_get_default_admin_email_body( 'de' ),
        'tsw_custom_restaurant_name_toggle'    => 'no',
        'tsw_custom_restaurant_name'           => '',
        'tsw_custom_store_address_toggle'       => 'no',
        'tsw_custom_store_address'              => '',
        'tsw_custom_store_logo_toggle'          => 'no',
        'tsw_custom_store_logo'                 => '',
    );

    foreach ( $settings as $key => $default ) {
        if ( get_option( $key ) === false ) {
            $existing = get_theme_mod( $key );
            if ( $existing !== false && $existing !== '' ) {
                update_option( $key, sanitize_text_field( $existing ) );
            } else {
                update_option( $key, sanitize_text_field( $default ) );
            }
        }
    }

    // Auto-create Webshop page if missing on clean install
    if ( null === get_page_by_path( 'webshop' ) ) {
        wp_insert_post( array(
            'post_title'     => 'Webshop',
            'post_name'      => 'webshop',
            'post_content'   => '[two_step_webshop_layout]',
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'comment_status' => 'closed',
        ) );
    }

    // Auto-enable Cash on Delivery (COD) gateway if no gateways enabled
    $cod_settings = get_option( 'woocommerce_cod_settings', array() );
    if ( empty( $cod_settings ) || ! isset( $cod_settings['enabled'] ) || $cod_settings['enabled'] !== 'yes' ) {
        $cod_settings['enabled'] = 'yes';
        update_option( 'woocommerce_cod_settings', $cod_settings );
    }
}

/**
 * Format variation attributes into a comma-separated string of value names.
 */
function tsw_get_formatted_variation_options( $cart_item ) {
    if ( ! isset( $cart_item['data'] ) ) {
        return '';
    }
    $_product = $cart_item['data'];
    if ( ! $_product->is_type( 'variation' ) ) {
        return '';
    }
    $formatted_attributes = array();
    $variation_data = isset( $cart_item['variation'] ) ? $cart_item['variation'] : array();
    
    foreach ( $variation_data as $attribute_name => $attribute_value ) {
        $taxonomy = str_replace( 'attribute_', '', $attribute_name );
        $value = $attribute_value;
        if ( taxonomy_exists( $taxonomy ) ) {
            $term = get_term_by( 'slug', $attribute_value, $taxonomy );
            if ( $term && ! is_wp_error( $term ) ) {
                $value = $term->name;
            }
        } else {
            $value = ucwords( str_replace( array('-', '_'), ' ', $attribute_value ) );
        }
        $formatted_attributes[] = $value;
    }
    return implode( ', ', $formatted_attributes );
}

// Add special request note to cart item data
add_filter( 'woocommerce_add_cart_item_data', 'tsw_add_cart_item_data', 10, 3 );
function tsw_add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
    if ( isset( $_POST['special_request_note'] ) && ! empty( $_POST['special_request_note'] ) ) {
        $cart_item_data['special_request_note'] = sanitize_text_field( $_POST['special_request_note'] );
    }
    return $cart_item_data;
}

// Display the special request note in cart and checkout
add_filter( 'woocommerce_get_item_data', 'tsw_get_item_data', 10, 2 );
function tsw_get_item_data( $item_data, $cart_item ) {
    if ( isset( $cart_item['special_request_note'] ) && ! empty( $cart_item['special_request_note'] ) ) {
        $item_data[] = array(
            'key'     => __( 'Special Request', '2-step-webshop' ),
            'value'   => $cart_item['special_request_note'],
            'display' => '',
        );
    }
    return $item_data;
}

// Save the special request note as order item meta
add_action( 'woocommerce_checkout_create_order_line_item', 'tsw_checkout_create_order_line_item', 10, 4 );
function tsw_checkout_create_order_line_item( $item, $cart_item_key, $values, $order ) {
    if ( isset( $values['special_request_note'] ) && ! empty( $values['special_request_note'] ) ) {
        $item->add_meta_data( __( 'Special Request', '2-step-webshop' ), $values['special_request_note'] );
    }
}

/**
 * Helper to retrieve opening & closing hours for a specific day of week
 */
function tsw_get_day_opening_hours( $day_key = '' ) {
    if ( empty( $day_key ) ) {
        $day_key = strtolower( date( 'l' ) );
    } else {
        $day_key = strtolower( trim( $day_key ) );
    }

    $use_same     = get_option( 'pickup_use_same_hours', 'yes' ) === 'yes';
    $global_open  = get_option( 'pickup_opening_time', '11:30' );
    $global_close = get_option( 'pickup_closing_time', '22:00' );

    if ( $use_same ) {
        return array(
            'open'  => $global_open,
            'close' => $global_close,
        );
    }

    $day_open  = get_option( 'pickup_opening_time_' . $day_key, $global_open );
    $day_close = get_option( 'pickup_closing_time_' . $day_key, $global_close );

    return array(
        'open'  => ! empty( $day_open ) ? $day_open : $global_open,
        'close' => ! empty( $day_close ) ? $day_close : $global_close,
    );
}

/**
 * Get Restaurant Name (respects custom override toggle)
 */
function tsw_get_restaurant_name() {
    if ( get_option( 'tsw_custom_restaurant_name_toggle', 'no' ) === 'yes' ) {
        $custom_name = get_option( 'tsw_custom_restaurant_name', '' );
        if ( ! empty( $custom_name ) ) {
            return $custom_name;
        }
    }
    return get_bloginfo( 'name' );
}

/**
 * Get Store Address (respects custom override toggle)
 */
function tsw_get_store_address() {
    if ( get_option( 'tsw_custom_store_address_toggle', 'no' ) === 'yes' ) {
        $custom_addr = get_option( 'tsw_custom_store_address', '' );
        if ( ! empty( $custom_addr ) ) {
            return $custom_addr;
        }
    }
    $wc_address     = get_option( 'woocommerce_store_address', '' );
    $wc_city        = get_option( 'woocommerce_store_city', '' );
    $wc_postcode    = get_option( 'woocommerce_store_postcode', '' );
    $pickup_address = trim( $wc_address . ( $wc_city ? ', ' . ( $wc_postcode ? $wc_postcode . ' ' : '' ) . $wc_city : '' ) );
    return ! empty( $pickup_address ) ? $pickup_address : __( 'Not set yet', '2-step-webshop' );
}

/**
 * Get Store Logo URL (respects custom override toggle)
 */
function tsw_get_store_logo_url() {
    if ( get_option( 'tsw_custom_store_logo_toggle', 'no' ) === 'yes' ) {
        $custom_logo = get_option( 'tsw_custom_store_logo', '' );
        if ( ! empty( $custom_logo ) ) {
            return $custom_logo;
        }
    }
    $custom_logo_id = get_theme_mod( 'custom_logo' );
    if ( $custom_logo_id ) {
        $logo_src = wp_get_attachment_image_src( $custom_logo_id, 'medium' );
        if ( $logo_src ) {
            return $logo_src[0];
        }
    }
    return '';
}

/**
 * Default Customer Email Subject (EN / DE)
 */
function tsw_get_default_customer_email_subject( $lang = 'en' ) {
    if ( 'de' === strtolower( $lang ) ) {
        return 'Bestellbestätigung #{order_number} - {restaurant_name}';
    }
    return 'Order Confirmation #{order_number} - {restaurant_name}';
}

/**
 * Default Customer Email Body HTML (EN / DE)
 */
function tsw_get_default_customer_email_body( $lang = 'en' ) {
    if ( 'de' === strtolower( $lang ) ) {
        return '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333333; line-height: 1.6; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
    <div style="background-color: #2563eb; padding: 20px; text-align: center; color: #ffffff;">
        {restaurant_logo}
        <h1 style="margin: 10px 0 0 0; font-size: 24px; color: #ffffff;">{restaurant_name}</h1>
    </div>
    <div style="padding: 24px;">
        <h2 style="color: #1e293b; margin-top: 0;">Vielen Dank für Ihre Bestellung, {customer_name}!</h2>
        <p>Wir haben Ihre Bestellung <strong>#{order_number}</strong> vom {order_date} erhalten. Nachfolgend finden Sie Ihre Bestellübersicht:</p>
        
        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; margin: 20px 0;">
            <p style="margin: 0 0 8px 0;"><strong>Bestellart:</strong> {fulfillment_method}</p>
            <p style="margin: 0 0 8px 0;"><strong>Gewünschter Zeitpunkt:</strong> {pickup_delivery_time}</p>
            <p style="margin: 0 0 8px 0;"><strong>Adresse des Geschäfts:</strong> {restaurant_address}</p>
            <p style="margin: 0;"><strong>Anmerkungen / Wünsche:</strong> {special_request}</p>
        </div>

        {order_table}

        <p style="margin-top: 20px;">Falls Sie Fragen zu Ihrer Bestellung haben, antworten Sie einfach auf diese E-Mail oder kontaktieren Sie uns.</p>
    </div>
    <div style="background-color: #f1f5f9; padding: 16px; text-align: center; font-size: 13px; color: #64748b;">
        {restaurant_name} &bull; {restaurant_address}
    </div>
</div>';
    }

    return '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333333; line-height: 1.6; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
    <div style="background-color: #2563eb; padding: 20px; text-align: center; color: #ffffff;">
        {restaurant_logo}
        <h1 style="margin: 10px 0 0 0; font-size: 24px; color: #ffffff;">{restaurant_name}</h1>
    </div>
    <div style="padding: 24px;">
        <h2 style="color: #1e293b; margin-top: 0;">Thank you for your order, {customer_name}!</h2>
        <p>We have received your order <strong>#{order_number}</strong> placed on {order_date}. Below are your order details:</p>
        
        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; margin: 20px 0;">
            <p style="margin: 0 0 8px 0;"><strong>Fulfillment Method:</strong> {fulfillment_method}</p>
            <p style="margin: 0 0 8px 0;"><strong>Scheduled Time:</strong> {pickup_delivery_time}</p>
            <p style="margin: 0 0 8px 0;"><strong>Store Address:</strong> {restaurant_address}</p>
            <p style="margin: 0;"><strong>Special Instructions:</strong> {special_request}</p>
        </div>

        {order_table}

        <p style="margin-top: 20px;">If you have any questions about your order, please reply to this email or contact us.</p>
    </div>
    <div style="background-color: #f1f5f9; padding: 16px; text-align: center; font-size: 13px; color: #64748b;">
        {restaurant_name} &bull; {restaurant_address}
    </div>
</div>';
}

/**
 * Default Admin Email Subject (EN / DE)
 */
function tsw_get_default_admin_email_subject( $lang = 'en' ) {
    if ( 'de' === strtolower( $lang ) ) {
        return 'Neue Bestellung #{order_number} von {customer_name} [{fulfillment_method}]';
    }
    return 'New Order #{order_number} from {customer_name} [{fulfillment_method}]';
}

/**
 * Default Admin Email Body HTML (EN / DE)
 */
function tsw_get_default_admin_email_body( $lang = 'en' ) {
    if ( 'de' === strtolower( $lang ) ) {
        return '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333333; line-height: 1.6; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
    <div style="background-color: #0f172a; padding: 20px; text-align: center; color: #ffffff;">
        {restaurant_logo}
        <h1 style="margin: 10px 0 0 0; font-size: 22px; color: #ffffff;">Neue Bestellung erhalten #{order_number}</h1>
    </div>
    <div style="padding: 24px;">
        <h2 style="color: #0f172a; margin-top: 0;">Bestellung #{order_number} - {fulfillment_method}</h2>
        <p>Eine neue Bestellung ist am {order_date} eingegangen.</p>
        
        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; margin: 20px 0;">
            <p style="margin: 0 0 8px 0;"><strong>Kundenname:</strong> {customer_name}</p>
            <p style="margin: 0 0 8px 0;"><strong>E-Mail:</strong> {customer_email}</p>
            <p style="margin: 0 0 8px 0;"><strong>Telefon:</strong> {customer_phone}</p>
            <p style="margin: 0 0 8px 0;"><strong>Bestellart:</strong> {fulfillment_method}</p>
            <p style="margin: 0 0 8px 0;"><strong>Gewünschter Zeitpunkt:</strong> {pickup_delivery_time}</p>
            <p style="margin: 0;"><strong>Anmerkungen / Wünsche:</strong> {special_request}</p>
        </div>

        {order_table}
    </div>
    <div style="background-color: #f1f5f9; padding: 16px; text-align: center; font-size: 13px; color: #64748b;">
        {restaurant_name} Admin-Benachrichtigung
    </div>
</div>';
    }

    return '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333333; line-height: 1.6; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
    <div style="background-color: #0f172a; padding: 20px; text-align: center; color: #ffffff;">
        {restaurant_logo}
        <h1 style="margin: 10px 0 0 0; font-size: 22px; color: #ffffff;">New Order Received #{order_number}</h1>
    </div>
    <div style="padding: 24px;">
        <h2 style="color: #0f172a; margin-top: 0;">Order #{order_number} - {fulfillment_method}</h2>
        <p>A new order has been placed on {order_date}.</p>
        
        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; margin: 20px 0;">
            <p style="margin: 0 0 8px 0;"><strong>Customer Name:</strong> {customer_name}</p>
            <p style="margin: 0 0 8px 0;"><strong>Email:</strong> {customer_email}</p>
            <p style="margin: 0 0 8px 0;"><strong>Phone:</strong> {customer_phone}</p>
            <p style="margin: 0 0 8px 0;"><strong>Fulfillment Method:</strong> {fulfillment_method}</p>
            <p style="margin: 0 0 8px 0;"><strong>Scheduled Time:</strong> {pickup_delivery_time}</p>
            <p style="margin: 0;"><strong>Special Request:</strong> {special_request}</p>
        </div>

        {order_table}
    </div>
    <div style="background-color: #f1f5f9; padding: 16px; text-align: center; font-size: 13px; color: #64748b;">
        {restaurant_name} Admin Notification
    </div>
</div>';
}

