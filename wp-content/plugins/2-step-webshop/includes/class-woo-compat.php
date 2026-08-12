<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TSW_Woo_Compat {

    public function __construct() {
        // Change Add to Cart Text to a Plus Symbol
        add_filter( 'woocommerce_product_single_add_to_cart_text', array( $this, 'custom_add_to_cart_icon' ) );
        add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'custom_add_to_cart_icon' ) );

        // Disable "View Cart" button redirect on add to cart
        add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'disable_redirect' ) );

        // Hide View Cart button after add to cart
        add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'disable_view_cart_button' ), 1 );

        // Localize checkout URL for Polylang multilingual support
        add_filter( 'woocommerce_get_checkout_url', array( $this, 'localized_checkout_url' ) );

        // Filter WooCommerce page IDs for Polylang multilingual compatibility
        add_filter( 'option_woocommerce_checkout_page_id', array( $this, 'localized_page_id' ) );
        add_filter( 'option_woocommerce_cart_page_id', array( $this, 'localized_page_id' ) );
        add_filter( 'option_woocommerce_myaccount_page_id', array( $this, 'localized_page_id' ) );
        add_filter( 'option_woocommerce_shop_page_id', array( $this, 'localized_page_id' ) );
        add_filter( 'option_wp_page_for_privacy_policy', array( $this, 'localized_page_id' ) );
        add_filter( 'option_woocommerce_privacy_policy_page_id', array( $this, 'localized_page_id' ) );

        // Filter checkout privacy policy text
        add_filter( 'option_woocommerce_checkout_privacy_policy_text', array( $this, 'localized_privacy_policy_text' ) );

        // Filter WooCommerce AJAX endpoints to append Polylang language parameter
        add_filter( 'woocommerce_ajax_get_endpoint', array( $this, 'localized_ajax_endpoint' ), 10, 2 );
    }

    public function custom_add_to_cart_icon() {
        return '&#43;'; // HTML entity for Plus (+)
    }

    public function disable_redirect() {
        return false; // Based on original theme's logic which returns false unconditionally here
    }

    public function disable_view_cart_button() {
        global $post;
        if ( is_a( $post, 'WP_Post' ) && ( has_shortcode( $post->post_content, 'custom_shop_layout' ) || has_shortcode( $post->post_content, 'two_step_webshop_layout' ) ) ) {
            echo '<style>a.wc-forward { display: none !important; }</style>';
        }
    }

    /**
     * Localize Checkout URL based on Polylang selected language.
     *
     * @param string $url Default checkout URL.
     * @return string Filtered checkout URL.
     */
    public function localized_checkout_url( $url ) {
        if ( function_exists( 'pll_get_post' ) ) {
            $checkout_page_id = wc_get_page_id( 'checkout' );
            if ( $checkout_page_id ) {
                $lang = function_exists( 'pll_current_language' ) ? pll_current_language() : '';
                if ( $lang ) {
                    $translated_id = pll_get_post( $checkout_page_id, $lang );
                    if ( $translated_id ) {
                        return get_permalink( $translated_id );
                    }
                }
            }
        }
        return $url;
    }

    /**
     * Map WooCommerce page IDs to their Polylang translated page IDs.
     *
     * @param int $id Base page ID from settings.
     * @return int Filtered page ID for current language context.
     */
    public function localized_page_id( $id ) {
        if ( is_admin() && ! wp_doing_ajax() ) {
            return $id;
        }
        if ( function_exists( 'pll_get_post' ) ) {
            $lang = function_exists( 'pll_current_language' ) ? pll_current_language() : '';
            if ( $lang ) {
                $translated_id = pll_get_post( $id, $lang );
                if ( $translated_id ) {
                    return $translated_id;
                }
            }
        }
        return $id;
    }

    /**
     * Translate the checkout privacy policy text option to the current language.
     *
     * @param string $text Original option text.
     * @return string Localized privacy policy text.
     */
    public function localized_privacy_policy_text( $text ) {
        if ( function_exists( 'pll_current_language' ) ) {
            $lang = pll_current_language();
            if ( $lang === 'de' ) {
                return 'Wir verwenden deine personenbezogenen Daten, um deine Bestellung durchführen zu können, eine möglichst gute Benutzererfahrung auf dieser Website zu ermöglichen und für weitere Zwecke, die in unserer [privacy_policy] beschrieben sind.';
            }
        }
        return $text;
    }

    /**
     * Append Polylang language directory prefix or query parameter to WooCommerce AJAX endpoints.
     *
     * @param string $endpoint URL of the endpoint.
     * @param string $request Request action name.
     * @return string Filtered endpoint URL.
     */
    public function localized_ajax_endpoint( $endpoint, $request ) {
        if ( function_exists( 'pll_current_language' ) ) {
            $lang = pll_current_language();
            $default_lang = function_exists( 'pll_default_language' ) ? pll_default_language() : 'en';
            if ( $lang && $lang !== $default_lang ) {
                // If it is a relative path starting with /
                if ( strpos( $endpoint, '/' ) === 0 ) {
                    if ( strpos( $endpoint, '/' . $lang . '/' ) !== 0 ) {
                        $endpoint = '/' . $lang . $endpoint;
                    }
                } else {
                    // Absolute URL
                    $home_url = home_url();
                    if ( strpos( $endpoint, $home_url ) === 0 ) {
                        $relative = substr( $endpoint, strlen( $home_url ) );
                        if ( strpos( $relative, '/' . $lang . '/' ) !== 0 ) {
                            $endpoint = $home_url . '/' . $lang . $relative;
                        }
                    }
                }
            }
        }
        return $endpoint;
    }
}
