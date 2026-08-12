<?php
/**
 * Extension class for GS WooCommerce Google Sheet Connector extensions operations
 * @since 1.5.0
 */

/*  Exit if accessed directly */
if (!defined('ABSPATH')) {
    exit;
}

class wcgsc_free_extensions {

    /**
     * Constructor
     */
    public function __construct() {

        /*  Install Plugin */
        add_action('wp_ajax_wcgsc_free_extension_install_plugin', array($this, 'wcgsc_free_extension_install_plugin'));

        /*  Activate Plugin */
        add_action('wp_ajax_wcgsc_free_extension_activate_plugin', array($this, 'wcgsc_free_extension_activate_plugin'));

        /*  Deactivate Plugin */
        add_action('wp_ajax_wcgsc_free_extension_deactivate_plugin', array($this, 'wcgsc_free_extension_deactivate_plugin'));
    }

    /**
     * Deactivate Plugin
     */
    public function wcgsc_free_extension_deactivate_plugin() {

        check_ajax_referer('wcgsc-ajax-nonce', 'security');

        if (!current_user_can('activate_plugins')) {
            wp_send_json_error('You do not have permission to deactivate plugins.');
        }

        if (!isset($_POST['plugin_slug'])) {
            wp_send_json_error('Plugin slug is missing.');
        }

        $plugin_slug = sanitize_text_field(wp_unslash($_POST['plugin_slug']));

        if (empty($plugin_slug)) {
            wp_send_json_error('Invalid plugin.');
        }

        if (!file_exists(WP_PLUGIN_DIR . '/' . $plugin_slug)) {
            wp_send_json_error('Plugin not found.');
        }

        deactivate_plugins($plugin_slug);

        if (is_plugin_active($plugin_slug)) {
            wp_send_json_error('Failed to deactivate plugin.');
        }

        wp_send_json_success('Plugin deactivated successfully.');
    }

    /**
     * Install or Upgrade Plugin
     */
    public function wcgsc_free_extension_install_plugin() {

        check_ajax_referer('wcgsc-ajax-nonce', 'security');

         // Permission check
        if (!current_user_can('install_plugins')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to install plugin.','wc-gsheetconnector')
            ));
        }

        if (empty($_POST['plugin_slug']) || empty($_POST['download_url'])) {
            wp_send_json_error(array(
                'message' => __('Missing required parameters.','wc-gsheetconnector')
            ));
        }

        $plugin_slug  = sanitize_text_field(wp_unslash($_POST['plugin_slug']));
        $download_url = esc_url_raw(wp_unslash($_POST['download_url']));

        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/update.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $upgrader = new Plugin_Upgrader(new WP_Ajax_Upgrader_Skin());

        $result = $upgrader->install($download_url);

        if (is_wp_error($result)) {
            wp_send_json_error(array(
                'message' => __('Installation failed: ','wc-gsheetconnector') . $result->get_error_message()
            ));
        }

        wp_send_json_success(array(
            'message' => __('Plugin installed successfully.','wc-gsheetconnector')
        ));
    }

    /**
     * Activate Plugin
     */
    public function wcgsc_free_extension_activate_plugin() {
     // 🔐 Verify nonce
        if (! check_ajax_referer('wcgsc-ajax-nonce', 'security', false)) {
            wp_send_json_error(array(
                'message' => __('Invalid security token.','wc-gsheetconnector')
            ));
        }

        // 🔐 Permission check
        if (! current_user_can('activate_plugins')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to activate plugin.','wc-gsheetconnector')
            ));
        }

        // 🔎 Check plugin slug
        if (empty($_POST['plugin_slug'])) {
            wp_send_json_error(array(
                'message' => __('Plugin slug is missing.','wc-gsheetconnector')
            ));
        }

        $plugin_slug = sanitize_text_field(wp_unslash($_POST['plugin_slug']));

        // Load required file
        if (! function_exists('activate_plugin')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        //    Check if already active
        if (is_plugin_active($plugin_slug)) {
            wp_send_json_success(array(
                'message' => __('Plugin is already activated.','wc-gsheetconnector')
            ));
        }

        // 🚀 Activate plugin
        $result = activate_plugin($plugin_slug);

        if (is_wp_error($result)) {
            wp_send_json_error(array(
                'message' => $result->get_error_message()
            ));
        }

        wp_send_json_success(array(
            'message' => __('Plugin activated successfully.','wc-gsheetconnector')
        ));
    }
}

/*  Initialize */
new wcgsc_free_extensions();