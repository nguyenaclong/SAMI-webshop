<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TSW_Settings {

    public function __construct() {
        // Register custom admin settings page
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Add settings page under Top-level Custom Shop menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __( '2 Step Webshop Settings', '2-step-webshop' ),
            __( '2 Step Webshop', '2-step-webshop' ),
            'manage_options',
            '2-step-webshop-settings',
            array( $this, 'render_settings_page' ),
            'dashicons-store',
            58
        );
    }

    /**
     * Register individual settings
     */
    public function register_settings() {
        // Core Layout & Scheduler Settings
        register_setting( 'custom_shop_settings_group', 'pickup_opening_time', array( 'default' => '11:30' ) );
        register_setting( 'custom_shop_settings_group', 'pickup_closing_time', array( 'default' => '22:00' ) );
        register_setting( 'custom_shop_settings_group', 'pickup_use_same_hours', array(
            'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
            'default'           => 'yes'
        ) );
        register_setting( 'custom_shop_settings_group', 'pickup_return_to_shop_url', array( 'default' => '' ) );
        
        $days = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
        foreach ( $days as $day ) {
            register_setting( 'custom_shop_settings_group', 'pickup_open_' . $day, array(
                'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
                'default'           => 'yes'
            ) );
            register_setting( 'custom_shop_settings_group', 'pickup_opening_time_' . $day, array( 'default' => '11:30' ) );
            register_setting( 'custom_shop_settings_group', 'pickup_closing_time_' . $day, array( 'default' => '22:00' ) );
        }

        register_setting( 'custom_shop_settings_group', 'pickup_time_interval', array( 'default' => '15' ) );
        register_setting( 'custom_shop_settings_group', 'pickup_lead_time_buffer', array( 'default' => '25' ) );
        register_setting( 'custom_shop_settings_group', 'pickup_min_payment_for_card', array( 'default' => '35' ) );
        register_setting( 'custom_shop_settings_group', 'pickup_floating_cart_position', array( 'default' => 'bottom-right' ) );

        // Pickup / Delivery Method Toggles
        register_setting( 'custom_shop_settings_group', 'pickup_enable_pickup', array(
            'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
            'default'           => 'yes'
        ) );
        register_setting( 'custom_shop_settings_group', 'pickup_enable_delivery', array(
            'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
            'default'           => 'yes'
        ) );

        // Email & Notification Settings
        register_setting( 'custom_shop_settings_group', 'tsw_admin_email_language', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'auto'
        ) );
        register_setting( 'custom_shop_settings_group', 'tsw_admin_notification_emails', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => ''
        ) );
        register_setting( 'custom_shop_settings_group', 'tsw_email_enable_custom_templates', array(
            'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
            'default'           => 'no'
        ) );
        register_setting( 'custom_shop_settings_group', 'tsw_customer_email_subject' );
        register_setting( 'custom_shop_settings_group', 'tsw_customer_email_body' );
        register_setting( 'custom_shop_settings_group', 'tsw_customer_email_subject_en' );
        register_setting( 'custom_shop_settings_group', 'tsw_customer_email_subject_de' );
        register_setting( 'custom_shop_settings_group', 'tsw_customer_email_body_en' );
        register_setting( 'custom_shop_settings_group', 'tsw_customer_email_body_de' );
        register_setting( 'custom_shop_settings_group', 'tsw_admin_email_subject' );
        register_setting( 'custom_shop_settings_group', 'tsw_admin_email_body' );
        register_setting( 'custom_shop_settings_group', 'tsw_admin_email_subject_en' );
        register_setting( 'custom_shop_settings_group', 'tsw_admin_email_subject_de' );
        register_setting( 'custom_shop_settings_group', 'tsw_admin_email_body_en' );
        register_setting( 'custom_shop_settings_group', 'tsw_admin_email_body_de' );

        // Header Banner & Store Media Settings
        register_setting( 'custom_shop_settings_group', 'custom_shop_variable_price_display', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'min'
        ) );
        register_setting( 'custom_shop_settings_group', 'custom_shop_show_cat_image', array(
            'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
            'default'           => 'no'
        ) );
        register_setting( 'custom_shop_settings_group', 'custom_shop_show_cat_desc', array(
            'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
            'default'           => 'yes'
        ) );
        register_setting( 'custom_shop_settings_group', 'custom_shop_cat_text_align', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'left'
        ) );
        register_setting( 'custom_shop_settings_group', 'tsw_custom_restaurant_name_toggle', array(
            'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
            'default'           => 'no'
        ) );
        register_setting( 'custom_shop_settings_group', 'tsw_custom_restaurant_name', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => ''
        ) );
        register_setting( 'custom_shop_settings_group', 'tsw_custom_store_address_toggle', array(
            'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
            'default'           => 'no'
        ) );
        register_setting( 'custom_shop_settings_group', 'tsw_custom_store_address', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => ''
        ) );
        register_setting( 'custom_shop_settings_group', 'tsw_custom_store_logo_toggle', array(
            'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
            'default'           => 'no'
        ) );
        register_setting( 'custom_shop_settings_group', 'tsw_custom_store_logo', array(
            'sanitize_callback' => 'esc_url_raw',
            'default'           => ''
        ) );
        register_setting( 'custom_shop_settings_group', 'custom_shop_hero_image', array(
            'sanitize_callback' => 'esc_url_raw',
            'default'           => ''
        ) );
        register_setting( 'custom_shop_settings_group', 'tsw_google_maps_url', array(
            'sanitize_callback' => 'esc_url_raw',
            'default'           => ''
        ) );
        register_setting( 'custom_shop_settings_group', 'tsw_delivery_zone_label', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => ''
        ) );
        register_setting( 'custom_shop_settings_group', 'tsw_delivery_zone_desc', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => ''
        ) );

        // Color, Radii, Typography & Layout Width Override Settings
        $style_settings = array(
            'custom_shop_btn_bg_color',
            'custom_shop_btn_hover_bg_color',
            'custom_shop_btn_text_color',
            'custom_shop_btn_hover_text_color',
            'custom_shop_btn_border_radius',
            'custom_shop_text_color',
            'custom_shop_link_color',
            'custom_shop_link_hover_color',
            'custom_shop_card_bg_color',
            'custom_shop_header_bg_color',
            'custom_shop_modal_bg_color',
            'custom_shop_price_color',
            'custom_shop_card_border_radius',
            'custom_shop_modal_border_radius',
            'custom_shop_pill_border_radius',
            'custom_shop_title_font_size',
            'custom_shop_body_font_size',
            'custom_shop_header_font_size',
            'custom_shop_price_font_size',
            'custom_shop_container_max_width',
            'custom_shop_sidebar_width',
            'custom_shop_modal_max_width',
            'custom_shop_drawer_width',
        );
        foreach ( $style_settings as $setting ) {
            register_setting( 'custom_shop_settings_group', $setting );
        }
    }

    /**
     * Sanitize checkbox inputs to return 'yes' or 'no'
     */
    public function sanitize_checkbox( $value ) {
        return ( $value === 'yes' ) ? 'yes' : 'no';
    }

    /**
     * Enqueue admin assets specifically for our settings page
     */
    public function enqueue_admin_assets( $hook ) {
        if ( 'toplevel_page_2-step-webshop-settings' !== $hook ) {
            return;
        }

        // Color picker core assets
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );

        // Media Library core assets
        wp_enqueue_media();

        // Custom settings styles and script
        wp_enqueue_style( 'csp-admin-settings-style', TSW_URL . 'assets/css/admin-settings.css', array(), TSW_VERSION );
        wp_enqueue_script( 'csp-admin-settings-script', TSW_URL . 'assets/js/admin-settings.js', array( 'jquery' ), TSW_VERSION, true );
    }

    /**
     * Helper to detect and fetch active theme color palette
     */
    public static function get_theme_palette() {
        $theme = wp_get_theme();
        $template = strtolower( $theme->get_template() );
        $palette = array();
        $theme_name = __( 'Default', '2-step-webshop' );

        if ( 'blocksy' === $template ) {
            $theme_name = 'Blocksy';
            if ( function_exists( 'blocksy_manager' ) ) {
                try {
                    $blocksy_palette = blocksy_manager()->colors->get_color_palette();
                    if ( is_array( $blocksy_palette ) ) {
                        foreach ( $blocksy_palette as $key => $data ) {
                            $num = str_replace( 'color', '', $key );
                            $palette["var(--theme-palette-color-{$num})"] = $data['color'];
                        }
                    }
                } catch ( \Exception $e ) {
                    // Fallback
                }
            }
            if ( empty( $palette ) ) {
                $palette = array(
                    'var(--theme-palette-color-1)' => '#2872fa',
                    'var(--theme-palette-color-2)' => '#1559ed',
                    'var(--theme-palette-color-3)' => '#3A4F66',
                    'var(--theme-palette-color-4)' => '#192a3d',
                    'var(--theme-palette-color-5)' => '#e1e8ed',
                    'var(--theme-palette-color-6)' => '#f2f5f7',
                    'var(--theme-palette-color-7)' => '#FAFBFC',
                    'var(--theme-palette-color-8)' => '#ffffff',
                );
            }
        } elseif ( 'astra' === $template ) {
            $theme_name = 'Astra';
            if ( function_exists( 'astra_get_option' ) ) {
                $astra_palette = astra_get_option( 'global-color-palette' );
                if ( isset( $astra_palette['palette'] ) && is_array( $astra_palette['palette'] ) ) {
                    $i = 0;
                    foreach ( $astra_palette['palette'] as $key => $hex ) {
                        $palette["var(--ast-global-color-{$i})"] = $hex;
                        $i++;
                    }
                }
            }
            if ( empty( $palette ) ) {
                $palette = array(
                    'var(--ast-global-color-0)' => '#0284c7',
                    'var(--ast-global-color-1)' => '#0369a1',
                    'var(--ast-global-color-2)' => '#1e293b',
                    'var(--ast-global-color-3)' => '#475569',
                    'var(--ast-global-color-4)' => '#f8fafc',
                    'var(--ast-global-color-5)' => '#ffffff',
                    'var(--ast-global-color-6)' => '#cbd5e1',
                    'var(--ast-global-color-7)' => '#f1f5f9',
                    'var(--ast-global-color-8)' => '#e2e8f0',
                );
            }
        } elseif ( 'generatepress' === $template ) {
            $theme_name = 'GeneratePress';
            $gp_settings = get_option( 'generate_settings' );
            if ( is_array( $gp_settings ) && isset( $gp_settings['global_colors'] ) && is_array( $gp_settings['global_colors'] ) ) {
                foreach ( $gp_settings['global_colors'] as $color_data ) {
                    if ( isset( $color_data['slug'] ) && isset( $color_data['color'] ) ) {
                        $palette["var(--{$color_data['slug']})"] = $color_data['color'];
                    }
                }
            }
            if ( empty( $palette ) ) {
                $palette = array(
                    'var(--contrast)'   => '#222222',
                    'var(--contrast-2)' => '#575760',
                    'var(--contrast-3)' => '#b2b2be',
                    'var(--contrast-4)' => '#e6e6e9',
                    'var(--contrast-5)' => '#f6f6f9',
                    'var(--contrast-6)' => '#fafafa',
                    'var(--contrast-7)' => '#ffffff',
                    'var(--accent)'     => '#ff5a1f',
                    'var(--accent-2)'   => '#e04c13',
                );
            }
        }

        return array(
            'name'    => $theme_name,
            'palette' => $palette,
        );
    }

    /**
     * Render a color picker field layout
     */
    protected function render_color_picker_field( $id, $label, $default, $desc = '', $palette = array(), $theme_name = 'Default' ) {
        $value = get_option( $id, $default );
        ?>
        <div class="csp-form-group">
            <label class="csp-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
            <div class="csp-color-picker-control">
                <div class="csp-palette-circles">
                    <span class="csp-palette-label"><?php printf( esc_html__( '%s Palette:', '2-step-webshop' ), esc_html( $theme_name ) ); ?></span>
                    <?php foreach ( $palette as $variable => $hex ) : ?>
                        <button type="button" 
                                class="csp-color-circle" 
                                data-variable="<?php echo esc_attr( $variable ); ?>" 
                                data-hex="<?php echo esc_attr( $hex ); ?>"
                                style="background-color: <?php echo esc_attr( $hex ); ?>;"
                                title="<?php echo esc_attr( $variable ); ?>">
                        </button>
                    <?php endforeach; ?>
                </div>
                <div class="csp-color-input-group">
                    <input type="text" 
                           name="<?php echo esc_attr( $id ); ?>" 
                           id="<?php echo esc_attr( $id ); ?>" 
                           class="csp-color-picker-input" 
                           value="<?php echo esc_attr( $value ); ?>">
                </div>
                <?php if ( $desc ) : ?>
                    <span class="csp-description"><?php echo esc_html( $desc ); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render settings page HTML
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Active tab handling
        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
        if ( isset( $_POST['tsw_active_tab'] ) ) {
            $active_tab = sanitize_text_field( $_POST['tsw_active_tab'] );
        }

        // Get detected theme palette info
        $theme_palette_info = self::get_theme_palette();
        $theme_name = $theme_palette_info['name'];
        $palette = $theme_palette_info['palette'];

        // Get dynamic defaults matching the active theme's defaults
        $theme_defaults = tsw_get_theme_defaults();
        ?>
        <div class="wrap csp-admin-wrap">
            <header class="csp-admin-header">
                <h1><?php esc_html_e( '2 Step Webshop Settings', '2-step-webshop' ); ?> <span>v<?php echo esc_html( TSW_VERSION ); ?></span></h1>
                <div class="csp-shortcode-badge">
                    <span class="csp-shortcode-label"><?php esc_html_e( 'Shortcode:', '2-step-webshop' ); ?></span>
                    <code id="csp-shortcode-code">[two_step_webshop_layout]</code>
                    <button type="button" class="button button-small csp-copy-shortcode-btn" id="csp-copy-shortcode-btn" title="<?php esc_attr_e( 'Copy shortcode to clipboard', '2-step-webshop' ); ?>" style="margin-left: 6px; display: inline-flex; align-items: center; gap: 4px; vertical-align: middle;">
                        <span class="dashicons dashicons-admin-page csp-btn-icon" style="font-size: 14px; width: 14px; height: 14px; line-height: 14px;"></span>
                        <span class="csp-copy-text"><?php esc_html_e( 'Copy', '2-step-webshop' ); ?></span>
                    </button>
                </div>
            </header>
            <div class="wp-header-end"></div>

            <?php if ( isset( $_GET['settings-updated'] ) ) : ?>
                <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
                    <p><strong><?php esc_html_e( 'Settings saved.', '2-step-webshop' ); ?></strong></p>
                </div>
            <?php endif; ?>

            <div class="csp-admin-container">
                <!-- Sidebar Nav Tabs -->
                <nav class="csp-admin-nav">
                    <button type="button" class="csp-nav-item <?php echo $active_tab === 'general' ? 'active' : ''; ?>" data-tab="general">
                        <span class="dashicons dashicons-admin-generic"></span>
                        <?php esc_html_e( 'General Settings', '2-step-webshop' ); ?>
                    </button>
                    <button type="button" class="csp-nav-item <?php echo $active_tab === 'style' ? 'active' : ''; ?>" data-tab="style">
                        <span class="dashicons dashicons-admin-appearance"></span>
                        <?php esc_html_e( 'Style & Colors', '2-step-webshop' ); ?>
                    </button>
                    <button type="button" class="csp-nav-item <?php echo $active_tab === 'email' ? 'active' : ''; ?>" data-tab="email">
                        <span class="dashicons dashicons-email-alt"></span>
                        <?php esc_html_e( 'Email Form & Templates', '2-step-webshop' ); ?>
                    </button>
                </nav>

                <!-- Settings Content Form -->
                <div class="csp-admin-content">
                    <form method="post" action="options.php">
                        <?php settings_fields( 'custom_shop_settings_group' ); ?>
                        <input type="hidden" name="tsw_active_tab" id="tsw_active_tab" value="<?php echo esc_attr( $active_tab ); ?>">

                        <!-- TAB 1: General Settings -->
                        <div id="csp-tab-general" class="csp-tab-panel <?php echo $active_tab === 'general' ? 'active' : ''; ?>">
                            
                            <?php $this->render_setup_checklist(); ?>

                            <!-- Fulfillment Methods & Services Card -->
                            <div class="csp-card" style="margin-bottom: 20px;">
                                <h2><?php esc_html_e( 'Fulfillment Methods & Services', '2-step-webshop' ); ?></h2>
                                <p class="csp-description"><?php esc_html_e( 'Control which fulfillment modes (Local Pickup and/or Delivery) are available for customers in the webshop interface.', '2-step-webshop' ); ?></p>
                                
                                <div class="csp-card-grid" style="margin-top: 16px;">
                                    <!-- Enable Local Pickup -->
                                    <div class="csp-form-group">
                                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                            <input type="checkbox" name="pickup_enable_pickup" value="yes" <?php checked( get_option( 'pickup_enable_pickup', 'yes' ), 'yes' ); ?>>
                                            <span><strong><?php esc_html_e( 'Enable Local Pickup (Abholung)', '2-step-webshop' ); ?></strong></span>
                                        </label>
                                        <span class="csp-description"><?php esc_html_e( 'Allow customers to select store pickup and pick a pickup timeslot.', '2-step-webshop' ); ?></span>
                                    </div>

                                    <!-- Enable Delivery -->
                                    <div class="csp-form-group">
                                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                            <input type="checkbox" name="pickup_enable_delivery" value="yes" <?php checked( get_option( 'pickup_enable_delivery', 'yes' ), 'yes' ); ?>>
                                            <span><strong><?php esc_html_e( 'Enable Delivery (Lieferung)', '2-step-webshop' ); ?></strong></span>
                                        </label>
                                        <span class="csp-description"><?php esc_html_e( 'Allow customers to select delivery to address and pick a delivery timeslot.', '2-step-webshop' ); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Restaurant Identity & Linked Settings -->
                            <div class="csp-card" style="margin-bottom: 20px;">
                                <h2><?php esc_html_e( 'Restaurant Information & Settings Links', '2-step-webshop' ); ?></h2>
                                <p class="csp-description"><?php esc_html_e( 'Core restaurant details are managed through standard WordPress and WooCommerce settings by default, or overridden below specifically for the webshop template.', '2-step-webshop' ); ?></p>
                                
                                <div class="csp-card-grid" style="margin-top: 16px;">
                                    <!-- Restaurant Name -->
                                    <div class="csp-form-group">
                                        <label class="csp-label"><?php esc_html_e( 'Restaurant Name', '2-step-webshop' ); ?></label>
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; flex-wrap: wrap;">
                                            <code><?php echo esc_html( get_bloginfo( 'name' ) ); ?></code>
                                            <a href="<?php echo esc_url( admin_url( 'options-general.php' ) ); ?>" class="button button-secondary button-small" target="_blank">
                                                <span class="dashicons dashicons-admin-settings csp-btn-icon"></span>
                                                <?php esc_html_e( 'Edit in WP Settings', '2-step-webshop' ); ?>
                                            </a>
                                        </div>
                                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin-top: 8px;">
                                            <input type="checkbox" class="tsw-custom-toggle" data-target="tsw_custom_name_wrapper" name="tsw_custom_restaurant_name_toggle" value="yes" <?php checked( get_option( 'tsw_custom_restaurant_name_toggle', 'no' ), 'yes' ); ?>>
                                            <span><strong><?php esc_html_e( 'Override with Custom Restaurant Name', '2-step-webshop' ); ?></strong></span>
                                        </label>
                                        <div id="tsw_custom_name_wrapper" style="margin-top: 8px; display: <?php echo get_option( 'tsw_custom_restaurant_name_toggle', 'no' ) === 'yes' ? 'block' : 'none'; ?>;">
                                            <input type="text" name="tsw_custom_restaurant_name" class="csp-input-text" value="<?php echo esc_attr( get_option( 'tsw_custom_restaurant_name', '' ) ); ?>" placeholder="<?php esc_attr_e( 'e.g. My Custom Restaurant', '2-step-webshop' ); ?>">
                                        </div>
                                    </div>

                                    <!-- Store Address -->
                                    <div class="csp-form-group">
                                        <label class="csp-label"><?php esc_html_e( 'Store Address', '2-step-webshop' ); ?></label>
                                        <?php
                                        $wc_address     = get_option( 'woocommerce_store_address', '' );
                                        $wc_city        = get_option( 'woocommerce_store_city', '' );
                                        $pickup_address = trim( $wc_address . ( $wc_city ? ', ' . $wc_city : '' ) );
                                        if ( empty( $pickup_address ) ) {
                                            $pickup_address = __( 'Not set yet', '2-step-webshop' );
                                        }
                                        ?>
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; flex-wrap: wrap;">
                                            <code><?php echo esc_html( $pickup_address ); ?></code>
                                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings' ) ); ?>" class="button button-secondary button-small" target="_blank">
                                                <span class="dashicons dashicons-location csp-btn-icon"></span>
                                                <?php esc_html_e( 'Edit in WooCommerce Settings', '2-step-webshop' ); ?>
                                            </a>
                                        </div>
                                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin-top: 8px;">
                                            <input type="checkbox" class="tsw-custom-toggle" data-target="tsw_custom_address_wrapper" name="tsw_custom_store_address_toggle" value="yes" <?php checked( get_option( 'tsw_custom_store_address_toggle', 'no' ), 'yes' ); ?>>
                                            <span><strong><?php esc_html_e( 'Override with Custom Store Address', '2-step-webshop' ); ?></strong></span>
                                        </label>
                                        <div id="tsw_custom_address_wrapper" style="margin-top: 8px; display: <?php echo get_option( 'tsw_custom_store_address_toggle', 'no' ) === 'yes' ? 'block' : 'none'; ?>;">
                                            <input type="text" name="tsw_custom_store_address" class="csp-input-text" value="<?php echo esc_attr( get_option( 'tsw_custom_store_address', '' ) ); ?>" placeholder="<?php esc_attr_e( 'e.g. Main Street 12, 10115 Berlin', '2-step-webshop' ); ?>">
                                        </div>
                                    </div>

                                    <!-- Store Logo -->
                                    <div class="csp-form-group">
                                        <label class="csp-label"><?php esc_html_e( 'Store Logo', '2-step-webshop' ); ?></label>
                                        <?php
                                        $custom_logo_id = get_theme_mod( 'custom_logo' );
                                        if ( $custom_logo_id ) {
                                            $logo_src = wp_get_attachment_image_src( $custom_logo_id, 'thumbnail' );
                                            if ( $logo_src ) {
                                                echo '<img src="' . esc_url( $logo_src[0] ) . '" style="max-height: 40px; display: block; margin-bottom: 5px;">';
                                            }
                                        }
                                        ?>
                                        <div style="margin-bottom: 8px;">
                                            <a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>" class="button button-secondary button-small" target="_blank">
                                                <span class="dashicons dashicons-format-image csp-btn-icon"></span>
                                                <?php esc_html_e( 'Change Store Logo in Customizer', '2-step-webshop' ); ?>
                                            </a>
                                        </div>
                                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin-top: 8px;">
                                            <input type="checkbox" class="tsw-custom-toggle" data-target="tsw_custom_logo_wrapper" name="tsw_custom_store_logo_toggle" value="yes" <?php checked( get_option( 'tsw_custom_store_logo_toggle', 'no' ), 'yes' ); ?>>
                                            <span><strong><?php esc_html_e( 'Override with Custom Store Logo', '2-step-webshop' ); ?></strong></span>
                                        </label>
                                        <?php $custom_logo_url = get_option( 'tsw_custom_store_logo', '' ); ?>
                                        <div id="tsw_custom_logo_wrapper" style="margin-top: 8px; display: <?php echo get_option( 'tsw_custom_store_logo_toggle', 'no' ) === 'yes' ? 'block' : 'none'; ?>;">
                                            <div style="display: flex; gap: 8px; align-items: center;">
                                                <input type="text" name="tsw_custom_store_logo" id="tsw_custom_store_logo" class="csp-input-text" style="flex: 1;" value="<?php echo esc_url( $custom_logo_url ); ?>" placeholder="https://example.com/logo.png">
                                                <button type="button" class="button button-secondary csp-media-upload-btn" data-target="tsw_custom_store_logo"><?php esc_html_e( 'Select', '2-step-webshop' ); ?></button>
                                                <button type="button" class="button button-secondary csp-media-remove-btn" data-target="tsw_custom_store_logo"><?php esc_html_e( 'Remove', '2-step-webshop' ); ?></button>
                                            </div>
                                            <div style="margin-top: 8px;">
                                                <img id="tsw_custom_store_logo_preview" src="<?php echo esc_url( $custom_logo_url ); ?>" style="max-height: 60px; border-radius: 4px; display: <?php echo empty( $custom_logo_url ) ? 'none' : 'block'; ?>;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Header Banner Image & Map Options Card -->
                            <div class="csp-card" style="margin-bottom: 20px;">
                                <h2><?php esc_html_e( 'Header Banner & Map Settings', '2-step-webshop' ); ?></h2>
                                <div class="csp-card-grid">

                                    <!-- Header Banner Image Uploader -->
                                    <div class="csp-form-group">
                                        <label class="csp-label" for="custom_shop_hero_image"><?php esc_html_e( 'Custom Header Banner Image', '2-step-webshop' ); ?></label>
                                        <?php $banner_img = get_option( 'custom_shop_hero_image', '' ); ?>
                                        <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 6px;">
                                            <input type="text" name="custom_shop_hero_image" id="custom_shop_hero_image" class="csp-input-text" style="flex: 1;" value="<?php echo esc_url( $banner_img ); ?>" placeholder="https://example.com/banner.jpg">
                                            <button type="button" class="button button-secondary csp-media-upload-btn" data-target="custom_shop_hero_image">
                                                <?php esc_html_e( 'Select', '2-step-webshop' ); ?>
                                            </button>
                                            <button type="button" class="button button-secondary csp-media-remove-btn" data-target="custom_shop_hero_image">
                                                <?php esc_html_e( 'Remove', '2-step-webshop' ); ?>
                                            </button>
                                        </div>
                                        <div>
                                            <img id="custom_shop_hero_image_preview" src="<?php echo esc_url( $banner_img ); ?>" style="max-height: 80px; border-radius: 6px; display: <?php echo empty( $banner_img ) ? 'none' : 'block'; ?>;">
                                        </div>
                                        <span class="csp-description"><?php esc_html_e( 'Upload or select a custom banner image displayed at the top.', '2-step-webshop' ); ?></span>
                                    </div>

                                    <!-- Custom Google Maps Embed URL -->
                                    <div class="csp-form-group">
                                        <label class="csp-label" for="tsw_google_maps_url"><?php esc_html_e( 'Google Maps Embed URL', '2-step-webshop' ); ?></label>
                                        <input type="text" name="tsw_google_maps_url" id="tsw_google_maps_url" class="csp-input-text" value="<?php echo esc_attr( get_option( 'tsw_google_maps_url', '' ) ); ?>" placeholder="e.g. https://maps.google.com/maps?q=...&output=embed">
                                        <span class="csp-description"><?php esc_html_e( 'Leave empty to automatically build map query from Store Address.', '2-step-webshop' ); ?></span>
                                    </div>

                                    <!-- Delivery Zone Label -->
                                    <div class="csp-form-group">
                                        <label class="csp-label" for="tsw_delivery_zone_label"><?php esc_html_e( 'Delivery Zone Label', '2-step-webshop' ); ?></label>
                                        <input type="text" name="tsw_delivery_zone_label" id="tsw_delivery_zone_label" class="csp-input-text" value="<?php echo esc_attr( get_option( 'tsw_delivery_zone_label', '' ) ); ?>" placeholder="<?php esc_attr_e( 'e.g. Delivery zone 1', '2-step-webshop' ); ?>">
                                        <span class="csp-description"><?php esc_html_e( 'Zone title shown in Store Info popup.', '2-step-webshop' ); ?></span>
                                    </div>

                                    <!-- Delivery Zone Description -->
                                    <div class="csp-form-group">
                                        <label class="csp-label" for="tsw_delivery_zone_desc"><?php esc_html_e( 'Delivery Zone Fee / Details', '2-step-webshop' ); ?></label>
                                        <input type="text" name="tsw_delivery_zone_desc" id="tsw_delivery_zone_desc" class="csp-input-text" value="<?php echo esc_attr( get_option( 'tsw_delivery_zone_desc', '' ) ); ?>" placeholder="<?php esc_attr_e( 'e.g. Min. amount - 30,00 €, Fee - 0,00 €', '2-step-webshop' ); ?>">
                                        <span class="csp-description"><?php esc_html_e( 'Zone details shown in Store Info popup.', '2-step-webshop' ); ?></span>
                                    </div>

                                    <!-- Variable Product Price Display Mode -->
                                    <div class="csp-form-group">
                                        <label class="csp-label" for="custom_shop_variable_price_display"><?php esc_html_e( 'Variable Product Price Display Mode', '2-step-webshop' ); ?></label>
                                        <?php $price_mode = get_option( 'custom_shop_variable_price_display', 'min' ); ?>
                                        <select name="custom_shop_variable_price_display" id="custom_shop_variable_price_display" class="csp-select">
                                            <option value="min" <?php selected( $price_mode, 'min' ); ?>><?php esc_html_e( 'Cheapest Price Only (e.g. 5,00 €)', '2-step-webshop' ); ?></option>
                                            <option value="range" <?php selected( $price_mode, 'range' ); ?>><?php esc_html_e( 'Full Price Range (e.g. 5,00 € – 12,00 €)', '2-step-webshop' ); ?></option>
                                        </select>
                                        <span class="csp-description"><?php esc_html_e( 'Choose how prices are formatted on product cards for items with options/variations.', '2-step-webshop' ); ?></span>
                                    </div>

                                </div>
                            </div>

                            <!-- Product Category Display & Alignment Card -->
                            <div class="csp-card" style="margin-top: 20px;">
                                <h2><?php esc_html_e( 'Product Category Display & Text Alignment', '2-step-webshop' ); ?></h2>
                                <p class="csp-description" style="margin-bottom: 12px;"><?php esc_html_e( 'Customize which category elements appear in the product list and set text alignment across categories.', '2-step-webshop' ); ?></p>
                                
                                <div class="csp-card-grid">
                                    <div class="csp-form-group">
                                        <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                                            <input type="checkbox" name="custom_shop_show_cat_image" value="yes" <?php checked( get_option( 'custom_shop_show_cat_image', 'no' ), 'yes' ); ?>>
                                            <span><strong><?php esc_html_e( 'Show Product Category Image', '2-step-webshop' ); ?></strong></span>
                                        </label>
                                        <span class="csp-description"><?php esc_html_e( 'Displays the category banner/thumbnail image above the category title.', '2-step-webshop' ); ?></span>
                                    </div>

                                    <div class="csp-form-group">
                                        <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                                            <input type="checkbox" name="custom_shop_show_cat_desc" value="yes" <?php checked( get_option( 'custom_shop_show_cat_desc', 'yes' ), 'yes' ); ?>>
                                            <span><strong><?php esc_html_e( 'Show Product Category Description', '2-step-webshop' ); ?></strong></span>
                                        </label>
                                        <span class="csp-description"><?php esc_html_e( 'Displays the category description below the category title.', '2-step-webshop' ); ?></span>
                                    </div>

                                    <div class="csp-form-group">
                                        <label class="csp-label" for="custom_shop_cat_text_align"><?php esc_html_e( 'Category Header & Text Alignment', '2-step-webshop' ); ?></label>
                                        <?php $cat_align = get_option( 'custom_shop_cat_text_align', 'left' ); ?>
                                        <select name="custom_shop_cat_text_align" id="custom_shop_cat_text_align" class="csp-select">
                                            <option value="left" <?php selected( $cat_align, 'left' ); ?>><?php esc_html_e( 'Left (Trái)', '2-step-webshop' ); ?></option>
                                            <option value="center" <?php selected( $cat_align, 'center' ); ?>><?php esc_html_e( 'Center (Giữa)', '2-step-webshop' ); ?></option>
                                            <option value="right" <?php selected( $cat_align, 'right' ); ?>><?php esc_html_e( 'Right (Phải)', '2-step-webshop' ); ?></option>
                                        </select>
                                        <span class="csp-description"><?php esc_html_e( 'Controls text alignment for category headers, descriptions, and product details.', '2-step-webshop' ); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Unified Card: Local Pickup & Opening Hours -->
                            <div class="csp-card" style="margin-top: 20px;">
                                <h2><?php esc_html_e( 'Local Pickup & Opening Hours', '2-step-webshop' ); ?></h2>

                                <!-- Global Opening & Closing Times -->
                                <div class="csp-form-group csp-flex-responsive" style="display: flex; gap: 20px; max-width: 100%;">
                                    <div style="flex: 1; min-width: 140px;">
                                        <label class="csp-label" for="pickup_opening_time"><?php esc_html_e( 'Global Opening Time', '2-step-webshop' ); ?></label>
                                        <input type="text" name="pickup_opening_time" id="pickup_opening_time" class="csp-input-text" style="width: 100%;" value="<?php echo esc_attr( get_option( 'pickup_opening_time', '11:30' ) ); ?>" placeholder="e.g. 11:30">
                                        <span class="csp-description"><?php esc_html_e( 'Default opening time (HH:MM).', '2-step-webshop' ); ?></span>
                                    </div>
                                    <div style="flex: 1; min-width: 140px;">
                                        <label class="csp-label" for="pickup_closing_time"><?php esc_html_e( 'Global Closing Time', '2-step-webshop' ); ?></label>
                                        <input type="text" name="pickup_closing_time" id="pickup_closing_time" class="csp-input-text" style="width: 100%;" value="<?php echo esc_attr( get_option( 'pickup_closing_time', '22:00' ) ); ?>" placeholder="e.g. 22:00">
                                        <span class="csp-description"><?php esc_html_e( 'Default closing time (HH:MM).', '2-step-webshop' ); ?></span>
                                    </div>
                                </div>

                                <!-- Opening Days & Per-Day Hours -->
                                <div class="csp-form-group" style="max-width: 100%; margin-top: 20px; margin-bottom: 20px; padding-top: 15px; border-top: 1px solid #f1f5f9;">
                                    <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; margin-bottom: 8px;">
                                        <input type="checkbox" name="pickup_use_same_hours" id="pickup_use_same_hours" value="yes" <?php checked( get_option( 'pickup_use_same_hours', 'yes' ), 'yes' ); ?>>
                                        <span><strong><?php esc_html_e( 'Use same opening & closing hours for all open days', '2-step-webshop' ); ?></strong></span>
                                    </label>
                                    <span class="csp-description"><?php esc_html_e( 'When checked, global Opening Time and Closing Time apply to every open day. Uncheck to set distinct opening/closing times per day.', '2-step-webshop' ); ?></span>

                                    <div class="csp-days-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 10px; margin-top: 15px;">
                                        <?php 
                                        $days_map = array(
                                            'monday'    => __( 'Montag (Monday)', '2-step-webshop' ),
                                            'tuesday'   => __( 'Dienstag (Tuesday)', '2-step-webshop' ),
                                            'wednesday' => __( 'Mittwoch (Wednesday)', '2-step-webshop' ),
                                            'thursday'  => __( 'Donnerstag (Thursday)', '2-step-webshop' ),
                                            'friday'    => __( 'Freitag (Friday)', '2-step-webshop' ),
                                            'saturday'  => __( 'Samstag (Saturday)', '2-step-webshop' ),
                                            'sunday'    => __( 'Sonntag (Sunday)', '2-step-webshop' )
                                        );
                                        $global_open  = get_option( 'pickup_opening_time', '11:30' );
                                        $global_close = get_option( 'pickup_closing_time', '22:00' );
                                        $use_same     = get_option( 'pickup_use_same_hours', 'yes' ) === 'yes';

                                        foreach ( $days_map as $day_key => $day_label ) :
                                            $is_checked = get_option( 'pickup_open_' . $day_key, 'yes' ) === 'yes';
                                            $day_open   = get_option( 'pickup_opening_time_' . $day_key, $global_open );
                                            $day_close  = get_option( 'pickup_closing_time_' . $day_key, $global_close );
                                            ?>
                                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; flex-wrap: wrap; gap: 12px;">
                                                <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; min-width: 140px;">
                                                    <input type="checkbox" name="pickup_open_<?php echo esc_attr( $day_key ); ?>" value="yes" <?php checked( $is_checked ); ?>>
                                                    <span><strong><?php echo esc_html( $day_label ); ?></strong></span>
                                                </label>
                                                
                                                <div class="csp-day-hours-inputs" style="display: <?php echo ( $is_checked && ! $use_same ) ? 'flex' : 'none'; ?>; align-items: center; gap: 6px;">
                                                    <input type="text" name="pickup_opening_time_<?php echo esc_attr( $day_key ); ?>" class="csp-input-text" style="width: 70px; height: 32px; padding: 0 6px; font-size: 13px; text-align: center;" value="<?php echo esc_attr( $day_open ); ?>">
                                                    <span style="color: #94a3b8;">-</span>
                                                    <input type="text" name="pickup_closing_time_<?php echo esc_attr( $day_key ); ?>" class="csp-input-text" style="width: 70px; height: 32px; padding: 0 6px; font-size: 13px; text-align: center;" value="<?php echo esc_attr( $day_close ); ?>">
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Time Slot Interval, Lead Time Buffer, Min Card Total -->
                                <div class="csp-form-group csp-grid-triplet" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-top: 20px; padding-top: 15px; border-top: 1px solid #f1f5f9; max-width: 100%;">
                                    <div>
                                        <label class="csp-label" for="pickup_time_interval"><?php esc_html_e( 'Time Slot Interval (Minutes)', '2-step-webshop' ); ?></label>
                                        <?php $interval = get_option( 'pickup_time_interval', '15' ); ?>
                                        <select name="pickup_time_interval" id="pickup_time_interval" class="csp-select" style="width: 100%;">
                                            <option value="15" <?php selected( $interval, '15' ); ?>>15 Minutes</option>
                                            <option value="30" <?php selected( $interval, '30' ); ?>>30 Minutes</option>
                                            <option value="45" <?php selected( $interval, '45' ); ?>>45 Minutes</option>
                                            <option value="60" <?php selected( $interval, '60' ); ?>>60 Minutes</option>
                                        </select>
                                        <span class="csp-description"><?php esc_html_e( 'Dropdown granularity.', '2-step-webshop' ); ?></span>
                                    </div>
                                    <div>
                                        <label class="csp-label" for="pickup_lead_time_buffer"><?php esc_html_e( 'Prep Buffer (Minutes)', '2-step-webshop' ); ?></label>
                                        <input type="number" name="pickup_lead_time_buffer" id="pickup_lead_time_buffer" class="csp-input-text" style="width: 100%;" value="<?php echo esc_attr( get_option( 'pickup_lead_time_buffer', '25' ) ); ?>" min="0" step="5">
                                        <span class="csp-description"><?php esc_html_e( 'Minimum future slot offset.', '2-step-webshop' ); ?></span>
                                    </div>
                                    <div>
                                        <label class="csp-label" for="pickup_min_payment_for_card"><?php esc_html_e( 'EC Card Min Total (€)', '2-step-webshop' ); ?></label>
                                        <input type="number" name="pickup_min_payment_for_card" id="pickup_min_payment_for_card" class="csp-input-text" style="width: 100%;" value="<?php echo esc_attr( get_option( 'pickup_min_payment_for_card', '35' ) ); ?>" min="0" step="1">
                                        <span class="csp-description"><?php esc_html_e( 'Minimum threshold for cards.', '2-step-webshop' ); ?></span>
                                    </div>
                                </div>

                                <!-- Shopping return Link -->
                                <div class="csp-form-group" style="margin-top: 15px;">
                                    <label class="csp-label" for="pickup_return_to_shop_url"><?php esc_html_e( 'Custom Shopping Link', '2-step-webshop' ); ?></label>
                                    <input type="text" name="pickup_return_to_shop_url" id="pickup_return_to_shop_url" class="csp-input-text" style="width: 100%;" value="<?php echo esc_attr( get_option( 'pickup_return_to_shop_url', '' ) ); ?>" placeholder="e.g. /webshop/">
                                    <span class="csp-description"><?php esc_html_e( 'Custom shopping URL redirect on empty cart page.', '2-step-webshop' ); ?></span>
                                </div>

                                <!-- Floating Cart Position -->
                                <div class="csp-form-group" style="margin-top: 15px;">
                                    <label class="csp-label" for="pickup_floating_cart_position"><?php esc_html_e( 'Floating Cart Position', '2-step-webshop' ); ?></label>
                                    <?php $cart_pos = get_option( 'pickup_floating_cart_position', 'bottom-right' ); ?>
                                    <select name="pickup_floating_cart_position" id="pickup_floating_cart_position" class="csp-select" style="width: 100%;">
                                        <option value="top-left" <?php selected( $cart_pos, 'top-left' ); ?>>Upper Left (Top-Left)</option>
                                        <option value="top-right" <?php selected( $cart_pos, 'top-right' ); ?>>Upper Right (Top-Right)</option>
                                        <option value="top-center" <?php selected( $cart_pos, 'top-center' ); ?>>Center Top (Top-Center)</option>
                                        <option value="middle-left" <?php selected( $cart_pos, 'middle-left' ); ?>>Center Left (Middle-Left)</option>
                                        <option value="middle-right" <?php selected( $cart_pos, 'middle-right' ); ?>>Center Right (Middle-Right)</option>
                                        <option value="bottom-left" <?php selected( $cart_pos, 'bottom-left' ); ?>>Lower Left (Bottom-Left)</option>
                                        <option value="bottom-right" <?php selected( $cart_pos, 'bottom-right' ); ?>>Lower Right (Bottom-Right)</option>
                                        <option value="bottom-center" <?php selected( $cart_pos, 'bottom-center' ); ?>>Center Bottom (Bottom-Center)</option>
                                    </select>
                                    <span class="csp-description"><?php esc_html_e( 'Choose the viewport position for the floating cart button.', '2-step-webshop' ); ?></span>
                                </div>
                            </div>

                        </div>

                        <!-- TAB 2: Style & Colors -->

                        <div id="csp-tab-style" class="csp-tab-panel <?php echo $active_tab === 'style' ? 'active' : ''; ?>">

                            <!-- Card 1: Buttons & Interactive Elements -->
                            <div class="csp-card">
                                <h2><?php esc_html_e( 'Buttons & Actions', '2-step-webshop' ); ?></h2>
                                <div class="csp-card-grid">
                                    <?php
                                    $this->render_color_picker_field(
                                        'custom_shop_btn_bg_color',
                                        __( 'Button Background Color', '2-step-webshop' ),
                                        $theme_defaults['custom_shop_btn_bg_color'],
                                        __( 'Primary action buttons background.', '2-step-webshop' ),
                                        $palette,
                                        $theme_name
                                    );

                                    $this->render_color_picker_field(
                                        'custom_shop_btn_hover_bg_color',
                                        __( 'Button Hover Background', '2-step-webshop' ),
                                        $theme_defaults['custom_shop_btn_hover_bg_color'],
                                        __( 'Hover state background for buttons.', '2-step-webshop' ),
                                        $palette,
                                        $theme_name
                                    );

                                    $this->render_color_picker_field(
                                        'custom_shop_btn_text_color',
                                        __( 'Button Text Color', '2-step-webshop' ),
                                        $theme_defaults['custom_shop_btn_text_color'],
                                        __( 'Text/Icon color inside buttons.', '2-step-webshop' ),
                                        $palette,
                                        $theme_name
                                    );

                                    $this->render_color_picker_field(
                                        'custom_shop_btn_hover_text_color',
                                        __( 'Button Hover Text Color', '2-step-webshop' ),
                                        $theme_defaults['custom_shop_btn_hover_text_color'],
                                        __( 'Hover text/icon color inside buttons.', '2-step-webshop' ),
                                        $palette,
                                        $theme_name
                                    );
                                    ?>
                                </div>
                            </div>

                            <!-- Card 2: Layout & Container Colors -->
                            <div class="csp-card">
                                <h2><?php esc_html_e( 'Colors & Backgrounds', '2-step-webshop' ); ?></h2>
                                <div class="csp-card-grid">
                                    <?php
                                    $this->render_color_picker_field(
                                        'custom_shop_text_color',
                                        __( 'Base Layout Text Color', '2-step-webshop' ),
                                        $theme_defaults['custom_shop_text_color'],
                                        __( 'General text color in webshop wrapper.', '2-step-webshop' ),
                                        $palette,
                                        $theme_name
                                    );

                                    $this->render_color_picker_field(
                                        'custom_shop_price_color',
                                        __( 'Product Price Color', '2-step-webshop' ),
                                        '#111111',
                                        __( 'Color for product prices.', '2-step-webshop' ),
                                        $palette,
                                        $theme_name
                                    );

                                    $this->render_color_picker_field(
                                        'custom_shop_link_color',
                                        __( 'Links & Category Text Color', '2-step-webshop' ),
                                        $theme_defaults['custom_shop_link_color'],
                                        __( 'Category text and link label color.', '2-step-webshop' ),
                                        $palette,
                                        $theme_name
                                    );

                                    $this->render_color_picker_field(
                                        'custom_shop_link_hover_color',
                                        __( 'Links & Category Hover Color', '2-step-webshop' ),
                                        $theme_defaults['custom_shop_link_hover_color'],
                                        __( 'Hover state color for links.', '2-step-webshop' ),
                                        $palette,
                                        $theme_name
                                    );

                                    $this->render_color_picker_field(
                                        'custom_shop_card_bg_color',
                                        __( 'Product Card Background', '2-step-webshop' ),
                                        '#ffffff',
                                        __( 'Background color for product cards.', '2-step-webshop' ),
                                        $palette,
                                        $theme_name
                                    );

                                    $this->render_color_picker_field(
                                        'custom_shop_header_bg_color',
                                        __( 'Sticky Header Background', '2-step-webshop' ),
                                        '#ffffff',
                                        __( 'Background color for top brand header.', '2-step-webshop' ),
                                        $palette,
                                        $theme_name
                                    );

                                    $this->render_color_picker_field(
                                        'custom_shop_modal_bg_color',
                                        __( 'Modal Popup Background', '2-step-webshop' ),
                                        '#ffffff',
                                        __( 'Background color for product modals.', '2-step-webshop' ),
                                        $palette,
                                        $theme_name
                                    );
                                    ?>
                                </div>
                            </div>

                            <!-- Card 3: Typography & Font Sizes -->
                            <div class="csp-card">
                                <h2><?php esc_html_e( 'Typography & Font Sizes', '2-step-webshop' ); ?></h2>
                                <div class="csp-card-grid">
                                    <div class="csp-form-group">
                                        <label class="csp-label" for="custom_shop_header_font_size"><?php esc_html_e( 'Header Title Font Size (px)', '2-step-webshop' ); ?></label>
                                        <input type="number" name="custom_shop_header_font_size" id="custom_shop_header_font_size" class="csp-input-text" value="<?php echo esc_attr( get_option( 'custom_shop_header_font_size', '' ) ); ?>" min="12" max="48" step="1" placeholder="e.g. 24">
                                        <span class="csp-description"><?php esc_html_e( 'Top brand header title font size.', '2-step-webshop' ); ?></span>
                                    </div>

                                    <div class="csp-form-group">
                                        <label class="csp-label" for="custom_shop_title_font_size"><?php esc_html_e( 'Product & Section Title Font Size (px)', '2-step-webshop' ); ?></label>
                                        <input type="number" name="custom_shop_title_font_size" id="custom_shop_title_font_size" class="csp-input-text" value="<?php echo esc_attr( get_option( 'custom_shop_title_font_size', '' ) ); ?>" min="10" max="36" step="1" placeholder="e.g. 16">
                                        <span class="csp-description"><?php esc_html_e( 'Product item title and section header font size.', '2-step-webshop' ); ?></span>
                                    </div>

                                    <div class="csp-form-group">
                                        <label class="csp-label" for="custom_shop_price_font_size"><?php esc_html_e( 'Price Font Size (px)', '2-step-webshop' ); ?></label>
                                        <input type="number" name="custom_shop_price_font_size" id="custom_shop_price_font_size" class="csp-input-text" value="<?php echo esc_attr( get_option( 'custom_shop_price_font_size', '' ) ); ?>" min="10" max="32" step="1" placeholder="e.g. 15">
                                        <span class="csp-description"><?php esc_html_e( 'Product price label font size.', '2-step-webshop' ); ?></span>
                                    </div>

                                    <div class="csp-form-group">
                                        <label class="csp-label" for="custom_shop_body_font_size"><?php esc_html_e( 'Description & Body Font Size (px)', '2-step-webshop' ); ?></label>
                                        <input type="number" name="custom_shop_body_font_size" id="custom_shop_body_font_size" class="csp-input-text" value="<?php echo esc_attr( get_option( 'custom_shop_body_font_size', '' ) ); ?>" min="10" max="24" step="1" placeholder="e.g. 13">
                                        <span class="csp-description"><?php esc_html_e( 'Product description & excerpt body text font size.', '2-step-webshop' ); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 4: Border Radii -->
                            <div class="csp-card">
                                <h2><?php esc_html_e( 'Border Radii Options', '2-step-webshop' ); ?></h2>
                                <div class="csp-card-grid">
                                    <div class="csp-form-group">
                                        <label class="csp-label" for="custom_shop_btn_border_radius"><?php esc_html_e( 'Button Border Radius (px)', '2-step-webshop' ); ?></label>
                                        <input type="number" name="custom_shop_btn_border_radius" id="custom_shop_btn_border_radius" class="csp-input-text" value="<?php echo esc_attr( get_option( 'custom_shop_btn_border_radius', '' ) ); ?>" min="0" max="100" step="1" placeholder="e.g. 8">
                                        <span class="csp-description"><?php esc_html_e( 'Corner radius for action buttons.', '2-step-webshop' ); ?></span>
                                    </div>

                                    <div class="csp-form-group">
                                        <label class="csp-label" for="custom_shop_card_border_radius"><?php esc_html_e( 'Product Card Border Radius (px)', '2-step-webshop' ); ?></label>
                                        <input type="number" name="custom_shop_card_border_radius" id="custom_shop_card_border_radius" class="csp-input-text" value="<?php echo esc_attr( get_option( 'custom_shop_card_border_radius', '' ) ); ?>" min="0" max="100" step="1" placeholder="e.g. 12">
                                        <span class="csp-description"><?php esc_html_e( 'Corner radius for product card containers.', '2-step-webshop' ); ?></span>
                                    </div>

                                    <div class="csp-form-group">
                                        <label class="csp-label" for="custom_shop_modal_border_radius"><?php esc_html_e( 'Modal & Drawer Border Radius (px)', '2-step-webshop' ); ?></label>
                                        <input type="number" name="custom_shop_modal_border_radius" id="custom_shop_modal_border_radius" class="csp-input-text" value="<?php echo esc_attr( get_option( 'custom_shop_modal_border_radius', '' ) ); ?>" min="0" max="100" step="1" placeholder="e.g. 16">
                                        <span class="csp-description"><?php esc_html_e( 'Corner radius for modal windows and popups.', '2-step-webshop' ); ?></span>
                                    </div>

                                    <div class="csp-form-group">
                                        <label class="csp-label" for="custom_shop_pill_border_radius"><?php esc_html_e( 'Pill & Capsule Border Radius (px)', '2-step-webshop' ); ?></label>
                                        <input type="number" name="custom_shop_pill_border_radius" id="custom_shop_pill_border_radius" class="csp-input-text" value="<?php echo esc_attr( get_option( 'custom_shop_pill_border_radius', '' ) ); ?>" min="0" max="100" step="1" placeholder="e.g. 30">
                                        <span class="csp-description"><?php esc_html_e( 'Corner radius for method switch capsules & pills.', '2-step-webshop' ); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 5: Layout & Container Widths -->
                            <div class="csp-card">
                                <h2><?php esc_html_e( 'Container & Column Widths', '2-step-webshop' ); ?></h2>
                                <div class="csp-card-grid">
                                    <div class="csp-form-group">
                                        <label class="csp-label" for="custom_shop_container_max_width"><?php esc_html_e( 'Container Max Width (px)', '2-step-webshop' ); ?></label>
                                        <input type="number" name="custom_shop_container_max_width" id="custom_shop_container_max_width" class="csp-input-text" value="<?php echo esc_attr( get_option( 'custom_shop_container_max_width', '' ) ); ?>" min="600" max="2400" step="10" placeholder="e.g. 1200">
                                        <span class="csp-description"><?php esc_html_e( 'Overall maximum width for the shop container.', '2-step-webshop' ); ?></span>
                                    </div>

                                    <div class="csp-form-group">
                                        <label class="csp-label" for="custom_shop_sidebar_width"><?php esc_html_e( 'Category Sidebar Width (px)', '2-step-webshop' ); ?></label>
                                        <input type="number" name="custom_shop_sidebar_width" id="custom_shop_sidebar_width" class="csp-input-text" value="<?php echo esc_attr( get_option( 'custom_shop_sidebar_width', '' ) ); ?>" min="150" max="500" step="5" placeholder="e.g. 260">
                                        <span class="csp-description"><?php esc_html_e( 'Width for the sticky category sidebar column.', '2-step-webshop' ); ?></span>
                                    </div>

                                    <div class="csp-form-group">
                                        <label class="csp-label" for="custom_shop_modal_max_width"><?php esc_html_e( 'Modal Max Width (px)', '2-step-webshop' ); ?></label>
                                        <input type="number" name="custom_shop_modal_max_width" id="custom_shop_modal_max_width" class="csp-input-text" value="<?php echo esc_attr( get_option( 'custom_shop_modal_max_width', '' ) ); ?>" min="300" max="1000" step="10" placeholder="e.g. 520">
                                        <span class="csp-description"><?php esc_html_e( 'Maximum width for product detail & info modals.', '2-step-webshop' ); ?></span>
                                    </div>

                                    <div class="csp-form-group">
                                        <label class="csp-label" for="custom_shop_drawer_width"><?php esc_html_e( 'Cart Drawer Width (px)', '2-step-webshop' ); ?></label>
                                        <input type="number" name="custom_shop_drawer_width" id="custom_shop_drawer_width" class="csp-input-text" value="<?php echo esc_attr( get_option( 'custom_shop_drawer_width', '' ) ); ?>" min="280" max="800" step="10" placeholder="e.g. 400">
                                        <span class="csp-description"><?php esc_html_e( 'Width for the slide-out cart drawer.', '2-step-webshop' ); ?></span>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- TAB 3: Email Form & Templates -->
                        <div id="csp-tab-email" class="csp-tab-panel <?php echo $active_tab === 'email' ? 'active' : ''; ?>">

                            <!-- Card 1: Admin Notification Emails & Language -->
                            <div class="csp-card">
                                <h2><?php esc_html_e( 'Admin Notification Recipients & Language', '2-step-webshop' ); ?></h2>
                                <div class="csp-card-grid">
                                    <div class="csp-form-group">
                                        <label class="csp-label" for="tsw_admin_notification_emails"><?php esc_html_e( 'Admin Notification Email Recipients', '2-step-webshop' ); ?></label>
                                        <?php
                                        $admin_emails = get_option( 'tsw_admin_notification_emails', '' );
                                        if ( empty( $admin_emails ) ) {
                                            $admin_emails = get_option( 'admin_email' );
                                        }
                                        ?>
                                        <input type="text" name="tsw_admin_notification_emails" id="tsw_admin_notification_emails" class="csp-input-text" value="<?php echo esc_attr( $admin_emails ); ?>" placeholder="admin@restaurant.de, manager@restaurant.de">
                                        <span class="csp-description"><?php esc_html_e( 'Enter one or multiple email addresses separated by commas to receive admin notifications.', '2-step-webshop' ); ?></span>
                                    </div>

                                    <div class="csp-form-group">
                                        <label class="csp-label" for="tsw_admin_email_language"><?php esc_html_e( 'Admin Notification Email Language', '2-step-webshop' ); ?></label>
                                        <?php $admin_lang = get_option( 'tsw_admin_email_language', 'auto' ); ?>
                                        <select name="tsw_admin_email_language" id="tsw_admin_email_language" class="csp-select">
                                            <option value="auto" <?php selected( $admin_lang, 'auto' ); ?>><?php esc_html_e( 'Follow order language (same as customer)', '2-step-webshop' ); ?></option>
                                            <option value="en_US" <?php selected( $admin_lang, 'en_US' ); ?>><?php esc_html_e( 'Always English (en_US)', '2-step-webshop' ); ?></option>
                                            <option value="de_DE" <?php selected( $admin_lang, 'de_DE' ); ?>><?php esc_html_e( 'Always German (de_DE)', '2-step-webshop' ); ?></option>
                                        </select>
                                        <span class="csp-description"><?php esc_html_e( 'Control language used for admin notifications.', '2-step-webshop' ); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 2: Dynamic Email Template Variables Legend -->
                            <div class="csp-card" style="background: #f8fafc; border-left: 4px solid #3b82f6;">
                                <h2><?php esc_html_e( 'Available Template Placeholders / Variables', '2-step-webshop' ); ?></h2>
                                <p class="csp-description" style="margin-bottom: 12px;"><?php esc_html_e( 'Use any of the following {} curly bracket placeholders inside your email subjects and body templates to dynamically insert order information:', '2-step-webshop' ); ?></p>
                                
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 10px; font-size: 13px;">
                                    <div><code>{restaurant_name}</code> - <?php esc_html_e( 'Store Name', '2-step-webshop' ); ?></div>
                                    <div><code>{restaurant_address}</code> - <?php esc_html_e( 'Store Address', '2-step-webshop' ); ?></div>
                                    <div><code>{restaurant_logo}</code> - <?php esc_html_e( 'Store Banner / Logo Image', '2-step-webshop' ); ?></div>
                                    <div><code>{customer_name}</code> - <?php esc_html_e( 'Customer Billing Name', '2-step-webshop' ); ?></div>
                                    <div><code>{customer_email}</code> - <?php esc_html_e( 'Customer Email Address', '2-step-webshop' ); ?></div>
                                    <div><code>{customer_phone}</code> - <?php esc_html_e( 'Customer Phone Number', '2-step-webshop' ); ?></div>
                                    <div><code>{order_number}</code> - <?php esc_html_e( 'Order ID / Number', '2-step-webshop' ); ?></div>
                                    <div><code>{order_date}</code> - <?php esc_html_e( 'Order Date', '2-step-webshop' ); ?></div>
                                    <div><code>{order_table}</code> - <?php esc_html_e( 'HTML Order Items Table & Totals', '2-step-webshop' ); ?></div>
                                    <div><code>{fulfillment_method}</code> - <?php esc_html_e( 'Pickup or Delivery', '2-step-webshop' ); ?></div>
                                    <div><code>{pickup_delivery_time}</code> - <?php esc_html_e( 'Scheduled Date & Time', '2-step-webshop' ); ?></div>
                                    <div><code>{special_request}</code> - <?php esc_html_e( 'Special Instructions / Notes', '2-step-webshop' ); ?></div>
                                </div>
                            </div>

                            <!-- Card 3: Custom Email Template Controls -->
                            <div class="csp-card">
                                <h2><?php esc_html_e( 'Custom Email Templates Enable/Disable', '2-step-webshop' ); ?></h2>
                                <div class="csp-form-group" style="max-width: 100%;">
                                    <label style="display: inline-flex; align-items: center; gap: 10px; cursor: pointer;">
                                        <input type="checkbox" name="tsw_email_enable_custom_templates" value="yes" <?php checked( get_option( 'tsw_email_enable_custom_templates', 'no' ), 'yes' ); ?>>
                                        <span><strong><?php esc_html_e( 'Enable Custom HTML Email Templates', '2-step-webshop' ); ?></strong></span>
                                    </label>
                                    <span class="csp-description"><?php esc_html_e( 'When enabled, the custom subjects and HTML body templates below will replace WooCommerce standard email templates.', '2-step-webshop' ); ?></span>
                                </div>
                            </div>

                            <!-- Card 4: Customer Order Email Template -->
                            <div class="csp-card">
                                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 16px;">
                                    <h2 style="margin: 0;"><?php esc_html_e( 'Customer Order Confirmation Email Template', '2-step-webshop' ); ?></h2>
                                    <div class="tsw-lang-tabs" data-target-group="tsw-customer-email-group">
                                        <button type="button" class="tsw-lang-tab-btn active" data-lang="en">🇬🇧 <?php esc_html_e( 'English (EN)', '2-step-webshop' ); ?></button>
                                        <button type="button" class="tsw-lang-tab-btn" data-lang="de">🇩🇪 <?php esc_html_e( 'German (DE)', '2-step-webshop' ); ?></button>
                                    </div>
                                </div>

                                <div class="tsw-customer-email-group tsw-lang-panel-en">
                                    <div class="csp-form-group" style="max-width: 100%; margin-bottom: 16px;">
                                        <label class="csp-label" for="tsw_customer_email_subject_en"><?php esc_html_e( 'Customer Email Subject (English)', '2-step-webshop' ); ?></label>
                                        <input type="text" name="tsw_customer_email_subject_en" id="tsw_customer_email_subject_en" class="csp-input-text" style="max-width: 100%;" value="<?php echo esc_attr( get_option( 'tsw_customer_email_subject_en', tsw_get_default_customer_email_subject( 'en' ) ) ); ?>">
                                    </div>

                                    <div class="csp-form-group" style="max-width: 100%;">
                                        <label class="csp-label" for="tsw_customer_email_body_en"><?php esc_html_e( 'Customer Email Body (English HTML)', '2-step-webshop' ); ?></label>
                                        <textarea name="tsw_customer_email_body_en" id="tsw_customer_email_body_en" rows="14" class="csp-input-text" style="max-width: 100%; height: auto; font-family: monospace; font-size: 13px; line-height: 1.5; padding: 12px;"><?php echo esc_textarea( get_option( 'tsw_customer_email_body_en', tsw_get_default_customer_email_body( 'en' ) ) ); ?></textarea>
                                        <span class="csp-description"><?php esc_html_e( 'English HTML body sent to customer. Include {order_table} to output itemized order table.', '2-step-webshop' ); ?></span>
                                    </div>
                                </div>

                                <div class="tsw-customer-email-group tsw-lang-panel-de" style="display: none;">
                                    <div class="csp-form-group" style="max-width: 100%; margin-bottom: 16px;">
                                        <label class="csp-label" for="tsw_customer_email_subject_de"><?php esc_html_e( 'Customer Email Subject (German)', '2-step-webshop' ); ?></label>
                                        <input type="text" name="tsw_customer_email_subject_de" id="tsw_customer_email_subject_de" class="csp-input-text" style="max-width: 100%;" value="<?php echo esc_attr( get_option( 'tsw_customer_email_subject_de', tsw_get_default_customer_email_subject( 'de' ) ) ); ?>">
                                    </div>

                                    <div class="csp-form-group" style="max-width: 100%;">
                                        <label class="csp-label" for="tsw_customer_email_body_de"><?php esc_html_e( 'Customer Email Body (German HTML)', '2-step-webshop' ); ?></label>
                                        <textarea name="tsw_customer_email_body_de" id="tsw_customer_email_body_de" rows="14" class="csp-input-text" style="max-width: 100%; height: auto; font-family: monospace; font-size: 13px; line-height: 1.5; padding: 12px;"><?php echo esc_textarea( get_option( 'tsw_customer_email_body_de', tsw_get_default_customer_email_body( 'de' ) ) ); ?></textarea>
                                        <span class="csp-description"><?php esc_html_e( 'German HTML body sent to customer. Include {order_table} to output itemized order table.', '2-step-webshop' ); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 5: Admin Order Notification Email Template -->
                            <div class="csp-card">
                                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 16px;">
                                    <h2 style="margin: 0;"><?php esc_html_e( 'Admin Order Notification Email Template', '2-step-webshop' ); ?></h2>
                                    <div class="tsw-lang-tabs" data-target-group="tsw-admin-email-group">
                                        <button type="button" class="tsw-lang-tab-btn active" data-lang="en">🇬🇧 <?php esc_html_e( 'English (EN)', '2-step-webshop' ); ?></button>
                                        <button type="button" class="tsw-lang-tab-btn" data-lang="de">🇩🇪 <?php esc_html_e( 'German (DE)', '2-step-webshop' ); ?></button>
                                    </div>
                                </div>
                                
                                <div class="tsw-admin-email-group tsw-lang-panel-en">
                                    <div class="csp-form-group" style="max-width: 100%; margin-bottom: 16px;">
                                        <label class="csp-label" for="tsw_admin_email_subject_en"><?php esc_html_e( 'Admin Email Subject (English)', '2-step-webshop' ); ?></label>
                                        <input type="text" name="tsw_admin_email_subject_en" id="tsw_admin_email_subject_en" class="csp-input-text" style="max-width: 100%;" value="<?php echo esc_attr( get_option( 'tsw_admin_email_subject_en', tsw_get_default_admin_email_subject( 'en' ) ) ); ?>">
                                    </div>

                                    <div class="csp-form-group" style="max-width: 100%;">
                                        <label class="csp-label" for="tsw_admin_email_body_en"><?php esc_html_e( 'Admin Email Body (English HTML)', '2-step-webshop' ); ?></label>
                                        <textarea name="tsw_admin_email_body_en" id="tsw_admin_email_body_en" rows="14" class="csp-input-text" style="max-width: 100%; height: auto; font-family: monospace; font-size: 13px; line-height: 1.5; padding: 12px;"><?php echo esc_textarea( get_option( 'tsw_admin_email_body_en', tsw_get_default_admin_email_body( 'en' ) ) ); ?></textarea>
                                        <span class="csp-description"><?php esc_html_e( 'English HTML body sent to admin(s). Include {order_table} to output itemized order table.', '2-step-webshop' ); ?></span>
                                    </div>
                                </div>

                                <div class="tsw-admin-email-group tsw-lang-panel-de" style="display: none;">
                                    <div class="csp-form-group" style="max-width: 100%; margin-bottom: 16px;">
                                        <label class="csp-label" for="tsw_admin_email_subject_de"><?php esc_html_e( 'Admin Email Subject (German)', '2-step-webshop' ); ?></label>
                                        <input type="text" name="tsw_admin_email_subject_de" id="tsw_admin_email_subject_de" class="csp-input-text" style="max-width: 100%;" value="<?php echo esc_attr( get_option( 'tsw_admin_email_subject_de', tsw_get_default_admin_email_subject( 'de' ) ) ); ?>">
                                    </div>

                                    <div class="csp-form-group" style="max-width: 100%;">
                                        <label class="csp-label" for="tsw_admin_email_body_de"><?php esc_html_e( 'Admin Email Body (German HTML)', '2-step-webshop' ); ?></label>
                                        <textarea name="tsw_admin_email_body_de" id="tsw_admin_email_body_de" rows="14" class="csp-input-text" style="max-width: 100%; height: auto; font-family: monospace; font-size: 13px; line-height: 1.5; padding: 12px;"><?php echo esc_textarea( get_option( 'tsw_admin_email_body_de', tsw_get_default_admin_email_body( 'de' ) ) ); ?></textarea>
                                        <span class="csp-description"><?php esc_html_e( 'German HTML body sent to admin(s). Include {order_table} to output itemized order table.', '2-step-webshop' ); ?></span>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Footer Actions Panel -->
                        <div class="csp-admin-footer">
                            <?php submit_button( __( 'Save Settings', '2-step-webshop' ), 'csp-btn csp-btn-primary', 'submit', false ); ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get setup checklist items with completion status and resolution links
     */
    private function get_setup_checklist_status() {
        $checklist = array();

        // 1. WooCommerce Plugin Active
        $wc_active = class_exists( 'WooCommerce' );
        $checklist[] = array(
            'id'        => 'wc_active',
            'title'     => __( 'WooCommerce Active', '2-step-webshop' ),
            'desc'      => $wc_active ? __( 'WooCommerce is installed and active.', '2-step-webshop' ) : __( 'WooCommerce is required for the webshop to function.', '2-step-webshop' ),
            'status'    => $wc_active,
            'fix_url'   => admin_url( 'plugins.php' ),
            'fix_label' => __( 'Plugins', '2-step-webshop' ),
        );

        // 2. Store Address Configured
        $wc_address = get_option( 'woocommerce_store_address', '' );
        $custom_addr_override = get_option( 'tsw_custom_store_address_toggle', 'no' ) === 'yes' && ! empty( get_option( 'tsw_custom_store_address', '' ) );
        $address_set = ! empty( $wc_address ) || $custom_addr_override;
        $checklist[] = array(
            'id'        => 'store_address',
            'title'     => __( 'Store Address Configured', '2-step-webshop' ),
            'desc'      => $address_set ? __( 'Store address is configured.', '2-step-webshop' ) : __( 'Store address is required for pickup info & geocoding.', '2-step-webshop' ),
            'status'    => $address_set,
            'fix_url'   => admin_url( 'admin.php?page=wc-settings' ),
            'fix_label' => __( 'Configure Address', '2-step-webshop' ),
        );

        // 3. Shipping Zones & Methods Check
        $has_zones = false;
        $has_local_pickup = false;
        $has_delivery_method = false;

        if ( $wc_active && class_exists( 'WC_Shipping_Zones' ) ) {
            $all_zones = WC_Shipping_Zones::get_zones();
            $default_zone = new WC_Shipping_Zone( 0 );
            $all_zones[] = array(
                'zone_id'          => 0,
                'shipping_methods' => $default_zone->get_shipping_methods(),
            );

            if ( ! empty( WC_Shipping_Zones::get_zones() ) ) {
                $has_zones = true;
            }

            foreach ( $all_zones as $zone_data ) {
                $methods = isset( $zone_data['shipping_methods'] ) ? $zone_data['shipping_methods'] : array();
                if ( empty( $methods ) && isset( $zone_data['zone_id'] ) ) {
                    $z_obj = new WC_Shipping_Zone( $zone_data['zone_id'] );
                    $methods = $z_obj->get_shipping_methods();
                }

                foreach ( $methods as $method ) {
                    if ( isset( $method->enabled ) && $method->enabled === 'yes' ) {
                        if ( strpos( $method->id, 'local_pickup' ) !== false ) {
                            $has_local_pickup = true;
                        }
                        if ( strpos( $method->id, 'flat_rate' ) !== false || strpos( $method->id, 'free_shipping' ) !== false || strpos( $method->id, 'delivery' ) !== false ) {
                            $has_delivery_method = true;
                        }
                    }
                }
            }
        }

        $is_pickup_enabled = get_option( 'pickup_enable_pickup', 'yes' ) === 'yes';
        $is_delivery_enabled = get_option( 'pickup_enable_delivery', 'yes' ) === 'yes';

        // Shipping Zone exists
        $checklist[] = array(
            'id'        => 'shipping_zone',
            'title'     => __( 'WooCommerce Shipping Zone Created', '2-step-webshop' ),
            'desc'      => $has_zones ? __( 'At least one WooCommerce shipping zone is configured.', '2-step-webshop' ) : __( 'Create a shipping zone in WooCommerce to enable rates.', '2-step-webshop' ),
            'status'    => $has_zones,
            'fix_url'   => admin_url( 'admin.php?page=wc-settings&tab=shipping' ),
            'fix_label' => __( 'Shipping Zones', '2-step-webshop' ),
        );

        // Local Pickup Method
        if ( $is_pickup_enabled ) {
            $checklist[] = array(
                'id'        => 'local_pickup_method',
                'title'     => __( 'Local Pickup Shipping Method Added', '2-step-webshop' ),
                'desc'      => $has_local_pickup ? __( 'Local Pickup shipping method is active in WooCommerce.', '2-step-webshop' ) : __( 'Add "Local Pickup" to your WooCommerce Shipping Zone.', '2-step-webshop' ),
                'status'    => $has_local_pickup,
                'fix_url'   => admin_url( 'admin.php?page=wc-settings&tab=shipping' ),
                'fix_label' => __( 'Add Local Pickup', '2-step-webshop' ),
            );
        }

        // Delivery Method
        if ( $is_delivery_enabled ) {
            $checklist[] = array(
                'id'        => 'delivery_method',
                'title'     => __( 'Delivery Shipping Method Added', '2-step-webshop' ),
                'desc'      => $has_delivery_method ? __( 'Flat Rate / Delivery shipping method is active in WooCommerce.', '2-step-webshop' ) : __( 'Add "Flat Rate" or shipping method to your WooCommerce Shipping Zone.', '2-step-webshop' ),
                'status'    => $has_delivery_method,
                'fix_url'   => admin_url( 'admin.php?page=wc-settings&tab=shipping' ),
                'fix_label' => __( 'Add Delivery Method', '2-step-webshop' ),
            );
        }

        // 4. Products Exist
        $product_count = 0;
        if ( function_exists( 'wp_count_posts' ) ) {
            $counts = wp_count_posts( 'product' );
            $product_count = isset( $counts->publish ) ? intval( $counts->publish ) : 0;
        }
        $has_products = $product_count > 0;
        $checklist[] = array(
            'id'        => 'published_products',
            'title'     => __( 'Products Published', '2-step-webshop' ),
            'desc'      => $has_products ? sprintf( __( '%d published product(s) found.', '2-step-webshop' ), $product_count ) : __( 'No published WooCommerce products found. Add products to display in shop.', '2-step-webshop' ),
            'status'    => $has_products,
            'fix_url'   => admin_url( 'edit.php?post_type=product' ),
            'fix_label' => __( 'Manage Products', '2-step-webshop' ),
        );

        // 5. Webshop Page with Shortcode
        $webshop_page = get_page_by_path( 'webshop' );
        $has_webshop_page = false;
        if ( $webshop_page && $webshop_page->post_status === 'publish' ) {
            $has_webshop_page = true;
        } else {
            global $wpdb;
            $page_with_shortcode = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status = 'publish' AND (post_content LIKE '%[two_step_webshop_layout]%' OR post_content LIKE '%[custom_shop]%') LIMIT 1" );
            if ( $page_with_shortcode ) {
                $has_webshop_page = true;
            }
        }
        $checklist[] = array(
            'id'        => 'webshop_page',
            'title'     => __( 'Webshop Page Created', '2-step-webshop' ),
            'desc'      => $has_webshop_page ? __( 'Webshop page with shortcode [two_step_webshop_layout] is published.', '2-step-webshop' ) : __( 'Create a page containing [two_step_webshop_layout] shortcode.', '2-step-webshop' ),
            'status'    => $has_webshop_page,
            'fix_url'   => admin_url( 'post-new.php?post_type=page' ),
            'fix_label' => __( 'Create Page', '2-step-webshop' ),
        );

        return $checklist;
    }

    /**
     * Render Setup Checklist Card
     */
    private function render_setup_checklist() {
        $checklist = $this->get_setup_checklist_status();
        $total_checks = count( $checklist );
        $passed_checks = 0;
        foreach ( $checklist as $item ) {
            if ( $item['status'] ) {
                $passed_checks++;
            }
        }
        $all_passed = ( $passed_checks === $total_checks );
        ?>
        <div class="csp-card csp-setup-checklist-card" style="margin-bottom: 20px; border-left: 4px solid <?php echo $all_passed ? '#10b981' : '#f59e0b'; ?>;">
            <div style="display: flex; justify-content: space-between; align-items: center; cursor: pointer;" onclick="jQuery('#tsw-checklist-body').slideToggle();">
                <div>
                    <h2 style="margin: 0; display: inline-flex; align-items: center; gap: 8px;">
                        <span><?php echo $all_passed ? '✅' : '📋'; ?></span>
                        <?php esc_html_e( 'Webshop Setup Checklist & Prerequisites', '2-step-webshop' ); ?>
                    </h2>
                    <span style="font-size: 13px; font-weight: 600; margin-left: 12px; color: <?php echo $all_passed ? '#059669' : '#d97706'; ?>;">
                        (<?php printf( esc_html__( '%d of %d completed', '2-step-webshop' ), $passed_checks, $total_checks ); ?>)
                    </span>
                </div>
                <button type="button" class="button button-small" style="line-height: 1;">
                    <?php esc_html_e( 'Toggle Details', '2-step-webshop' ); ?>
                </button>
            </div>
            
            <div id="tsw-checklist-body" style="margin-top: 16px; display: <?php echo $all_passed ? 'none' : 'block'; ?>;">
                <p class="csp-description" style="margin-bottom: 14px;">
                    <?php esc_html_e( 'Ensure all prerequisite WordPress & WooCommerce settings are configured correctly for seamless 2 Step Webshop operation.', '2-step-webshop' ); ?>
                </p>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <?php foreach ( $checklist as $item ) : ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: <?php echo $item['status'] ? '#f0fdf4' : '#fffbeb'; ?>; border: 1px solid <?php echo $item['status'] ? '#bbf7d0' : '#fde68a'; ?>; border-radius: 6px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-size: 16px; font-weight: bold; color: <?php echo $item['status'] ? '#16a34a' : '#d97706'; ?>;">
                                    <?php echo $item['status'] ? '✓' : '⚠️'; ?>
                                </span>
                                <div>
                                    <strong style="color: #1e293b; font-size: 14px;"><?php echo esc_html( $item['title'] ); ?></strong>
                                    <div style="font-size: 12px; color: #64748b; margin-top: 2px;"><?php echo esc_html( $item['desc'] ); ?></div>
                                </div>
                            </div>
                            <?php if ( ! $item['status'] && ! empty( $item['fix_url'] ) ) : ?>
                                <a href="<?php echo esc_url( $item['fix_url'] ); ?>" class="button button-secondary button-small" target="_blank" style="white-space: nowrap;">
                                    <?php echo esc_html( $item['fix_label'] ); ?> &rarr;
                                </a>
                            <?php elseif ( $item['status'] ) : ?>
                                <span style="font-size: 12px; color: #16a34a; font-weight: 600; white-space: nowrap;"><?php esc_html_e( 'Ready', '2-step-webshop' ); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
    }
}
