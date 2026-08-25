<?php

/**
 * Plugin Name: GSheetConnector for WooCommerce
 * Plugin URI: https://wordpress.org/plugins/wc-gsheetconnector/
 * Description: Send your WooCommerce data to your Google Sheets spreadsheet.
 * Author: GSheetConnector
 * Author URI: https://www.gsheetconnector.com/
 * Version: 1.4.10
 * Text Domain: wc-gsheetconnector
 * Domain Path: /languages
 * WooCommerce requires at least: 3.2.0
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (wc_gsheetconnector_Init::wcgsc_is_plugin_active('wc_gsheetconnector_Init_Pro')) {
    return;
}

// Declare HPOS (High-Performance Order Storage) compatibility.
add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

/*freemius*/
if (function_exists('is_plugin_active') && is_plugin_active('wc-gsheetconnector/wc-gsheetconnector.php')) {
    if (!function_exists('gs_woofree')) {
        // Create a helper function for easy SDK access.
       // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Freemius helper function kept for backward compatibility.
        function gs_woofree()
        {
            global $gs_woofree;

            if (!isset($gs_woofree)) {
                // Activate multisite network integration.
                if (!defined('WP_FS__PRODUCT_9480_MULTISITE')) {
                    define('WP_FS__PRODUCT_9480_MULTISITE', true);
                }

                // Include Freemius SDK.
                require_once dirname(__FILE__) . '/lib/vendor/freemius/start.php';
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Freemius global variable kept for backward compatibility.
                $gs_woofree = fs_dynamic_init(array(
                    'id' => '9480',
                    'slug' => 'wc-gsheetconnector',
                    'type' => 'plugin',
                    'public_key' => 'pk_487f703ba4a974974c9d344111193',
                    'is_premium' => false,
                    'has_addons' => false,
                    'has_paid_plans' => false,
                    'is_org_compliant' => true,
                    'menu' => array(
                        'slug' => 'wc-gsheetconnector-config',
                        'first-path' => (!is_multisite() ? 'admin.php?page=wc-gsheetconnector-config' : 'plugins.php'),
                        'account' => false,
                    ),
                ));
            }
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Freemius hook kept for backward compatibility.
            return $gs_woofree;
        }

        // Init Freemius.
        gs_woofree();
        // Signal that SDK was initiated.
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Freemius hook kept for backward compatibility.
        do_action('gs_woofree_loaded');
    }
}
/*freemius*/

// Declare some global constants
define('WC_GSHEETCONNECTOR_VERSION', '1.4.10');
define('WC_GSHEETCONNECTOR_DB_VERSION', '1.4.10');
define('WC_GSHEETCONNECTOR_ROOT', dirname(__FILE__));
define('WC_GSHEETCONNECTOR_URL', plugins_url('/', __FILE__));
define('WC_GSHEETCONNECTOR_BASE_FILE', basename(dirname(__FILE__)) . '/wc-gsheetconnector.php');
define('WC_GSHEETCONNECTOR_BASE_NAME', plugin_basename(__FILE__));
define('WC_GSHEETCONNECTOR_PATH', plugin_dir_path(__FILE__)); //use for include files to other files
define('WC_GSHEETCONNECTOR_CURRENT_THEME', get_stylesheet_directory());
define('WC_GSHEETCONNECTOR_API_URL', 'https://oauth.gsheetconnector.com/api-cred.php');
define('WC_GSHEETCONNECTOR_AUTH_URL', 'https://oauth.gsheetconnector.com/index.php');
define('WC_GSHEETCONNECTOR_AUTH_REDIRECT_URI', admin_url('admin.php?page=wc-gsheetconnector-config&tab=integration'));
define('WC_GSHEETCONNECTOR_AUTH_PLUGIN_NAME', 'woocommercegsheetconnector');
/*
 * include utility classes
 */
if (!class_exists('wc_gsheetconnector_utility')) {
    include(WC_GSHEETCONNECTOR_ROOT . '/includes/class-wc-gsheetconnector-utility.php');
}

include_once(WC_GSHEETCONNECTOR_ROOT . '/lib/google-sheets.php');

require_once WC_GSHEETCONNECTOR_ROOT . '/includes/class-wc-gsheetconnector-error-logs.php';

if (!class_exists('wc_gsheetconnector_Service')) {
    include_once(WC_GSHEETCONNECTOR_PATH . 'includes/pages/woocommerce-data-settings/class-wc-gsheetconnector-services.php');
}

class wc_gsheetconnector_Init
{

    /**
     *  Set things up.
     *  @since 1.0
     */
    public function __construct()
    {

        //run on activation of plugin
        register_activation_hook(__FILE__, array($this, 'wcgsc_activate'));

        //run on deactivation of plugin
        register_deactivation_hook(__FILE__, array($this, 'wcgsc_deactivate'));

        //run on uninstall
        register_uninstall_hook(__FILE__, array('wc_gsheetconnector_Init', 'wcgsc_free_uninstall'));

        // validate is woocommerce plugin exist
        add_action('admin_init', array($this, 'validate_parent_plugin_exists'));

        // register admin menu under "Contact" > "Integration"
        add_action('admin_menu', array($this, 'register_gs_menu_pages'), 70);

        // load the js and css files
        add_action('init', array($this, 'load_css_and_js_files'));

        // Load text domain
        add_action('init', array($this, 'wcgsc_load_plugin_textdomain'));

        // load the classes
        add_action('init', array($this, 'load_all_classes'));

         // run upgradation
        add_action('admin_init', array($this, 'run_on_upgrade'));

        add_filter('plugin_row_meta', [$this, 'plugin_row_meta'], 10, 2);
    }

/**
 * Load plugin textdomain for translations.
 *
 * Loads the translation files from the plugin's /languages directory
 * to make the plugin translatable into different languages.
 *
 * Translation files should be placed in:
 * wp-content/plugins/wc-gsheetconnector/languages/
 *
 * @since 1.0.0
 * @return void
 */
public function wcgsc_load_plugin_textdomain()
{

        // phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound
    load_plugin_textdomain(
        'wc-gsheetconnector',
        false,
        plugin_basename(dirname(__FILE__)) . '/languages'
    );
}

 /**
 * Add custom plugin row meta links.
 *
 * Adds additional links such as Documentation and Support
 * to the plugin row on the WordPress Plugins page.
 *
 * Hooks into the `plugin_row_meta` filter.
 *
 * @since 1.1.4
 * @access public
 *
 * @param array  $plugin_meta Existing plugin meta links.
 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
 *
 * @return array Modified plugin meta links.
 */
 public function plugin_row_meta($plugin_meta, $plugin_file)
 {
    if (WC_GSHEETCONNECTOR_BASE_NAME === $plugin_file) {
        $row_meta = [
            'docs' => '<a href="https://www.gsheetconnector.com/docs/woocommerce-gsheetconnector" aria-label="' . esc_attr(esc_html__('View Documentation', 'wc-gsheetconnector')) . '" target="_blank">' . esc_html__('Docs', 'wc-gsheetconnector') . '</a>',
            'support' => '<a href="https://www.gsheetconnector.com/support" aria-label="' . esc_attr(esc_html__('Get Support', 'wc-gsheetconnector')) . '" target="_blank">' . esc_html__('Support', 'wc-gsheetconnector') . '</a>',
        ];

        $plugin_meta = array_merge($plugin_meta, $row_meta);
    }

    return $plugin_meta;
}
/**
 * Run plugin activation tasks.
 *
 * Performs required setup operations when the plugin is activated.
 * Handles both single-site and multisite network activation.
 *
 * For multisite installations:
 * - If network activated, runs setup for all sites.
 * - If activated on a single site, runs setup only for the current site.
 *
 * @since 1.0
 *
 * @param bool $network_wide Whether the plugin is activated network-wide.
 * @return void
 */
public function wcgsc_activate($network_wide)
{
    global $wpdb;
    $this->run_on_activation();

    if (function_exists('is_multisite') && is_multisite()) {
        if ($network_wide) {
            $sites = get_sites(array('fields' => 'ids'));
            foreach ($sites as $blog_id) {
                switch_to_blog($blog_id);
                $this->run_for_site();
                restore_current_blog();
            }
            return;
        }
    }

    $this->run_for_site();
}


/**
 * Run tasks during plugin activation.
 *
 * Creates and initializes required site options for the plugin.
 * In multisite installations, these options are shared across all sites.
 *
 * Checks the stored plugin/database version:
 * - Creates default plugin information if it does not exist.
 * - Runs the upgrade process if the database version has changed.
 *
 * Also saves the required Google API credentials during activation.
 *
 * @since 1.0
 * @return void
 */
private function run_on_activation()
{
    try {
        $plugin_options = get_site_option('WC_GS_info');

        if (false === $plugin_options) {
            $Wc_GS_info = array(
                'version' => WC_GSHEETCONNECTOR_VERSION,
                'db_version' => WC_GSHEETCONNECTOR_DB_VERSION
            );
            update_site_option('WC_GS_info', $Wc_GS_info);
        } else if (WC_GSHEETCONNECTOR_DB_VERSION != $plugin_options['version']) {
            $this->run_on_upgrade();
        }

    // Fetch and save the API credentails.
        if ( class_exists( 'wc_gsheetconnector_utility' ) ) {
            wc_gsheetconnector_utility::instance()->save_api_credentials();
        }

    // create debug log table (delegated to the shared wcgsc_create_error_log_table() in error-logs.php)
        wcgsc_create_error_log_table();

    } catch (Exception $e) {
    }
}

/**
 * Run plugin upgrade tasks.
 *
 * Checks the currently installed plugin/database version
 * and applies the required upgrade routines for older versions.
 *
 * After completing the upgrade process, updates the stored
 * plugin version and database version information.
 *
 * @since 1.0
 * @return void
 */
public function run_on_upgrade()
{

    $plugin_options = get_site_option('WC_GS_info');

        // Ensure it's an array before accessing
    if (is_array($plugin_options) && isset($plugin_options['version'])) {

        if ($plugin_options['version'] === '1.3.18') {
            $this->upgrade_database_18();
        }
    }

        // update the version value
    $google_sheet_info = array(
        'version'    => WC_GSHEETCONNECTOR_VERSION,
        'db_version' => WC_GSHEETCONNECTOR_DB_VERSION
    );

    update_site_option('WC_GS_info', $google_sheet_info);
}
/**
 * Upgrade database structure and plugin data for version 1.3.18.
 *
 * Runs the required upgrade process for all sites in a multisite
 * installation and for the current site in a single-site setup.
 *
 * @since 1.3.18
 * @return void
 */
public function upgrade_database_18()
{
        // look through each of the blogs and upgrade the DB
    if (function_exists('is_multisite') && is_multisite()) {
            // Use core function to get all blog IDs (cached and safe)
        $blog_ids = get_sites(array('fields' => 'ids'));

        foreach ($blog_ids as $blog_id) {
            switch_to_blog($blog_id);
            $this->upgrade_helper_18();
            restore_current_blog();
        }
    }

        // Run on current site (non-multisite or base site)
    $this->upgrade_helper_18();
}

/**
 * Helper function for version 1.3.18 upgrade tasks.
 *
 * Executes site-specific upgrade operations such as
 * saving or refreshing Google API credentials.
 *
 * @since 1.3.18
 * @return void
 */
public function upgrade_helper_18()
{
        // Fetch and save the API credentails.
    wc_gsheetconnector_utility::instance()->save_api_credentials();
}


 /**
 * Run tasks during plugin deactivation.
 *
 * Executes cleanup or temporary shutdown tasks when the plugin
 * is deactivated. Supports both single-site and multisite setups.
 *
 * @since 1.0
 *
 * @param bool $network_wide Whether the plugin is deactivated network-wide.
 * @return void
 */
 public function wcgsc_deactivate($network_wide) {}


/**
 * Run plugin uninstall tasks.
 *
 * Removes plugin data and settings during plugin uninstall.
 * Handles cleanup for both single-site and multisite installations.
 *
 * In multisite setups:
 * - Deletes site-specific data for all sites.
 * - Removes shared network/site options.
 *
 * @since 1.0
 * @return void
 */
public static function wcgsc_free_uninstall()
{
    wc_gsheetconnector_Init::run_on_uninstall();

    if (function_exists('is_multisite') && is_multisite()) {
            // Use core WordPress function to safely get all blog IDs
        $blog_ids = get_sites(array('fields' => 'ids'));

        foreach ($blog_ids as $blog_id) {
            switch_to_blog($blog_id);
            wc_gsheetconnector_Init::delete_for_site();
            restore_current_blog();
        }
        return;
    }

    wc_gsheetconnector_Init::delete_for_site();
}


 /**
 * Delete shared site options during uninstall.
 *
 * Removes plugin-related network/site options that are
 * stored globally across the WordPress installation.
 *
 * @since 1.5
 * @return void
 */
 private static function run_on_uninstall()
 {
    if (!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN'))
        exit();

    delete_site_option('WC_GS_info');
}

/**
 * Delete site-specific plugin options during uninstall.
 *
 * Removes plugin settings, authentication data,
 * feed configuration, and related post meta
 * for the current site.
 *
 * @since 1.0
 * @return void
 */
private static function delete_for_site()
{
    // Get the saved value from the options table
    $saved_value = get_option('wcgsc_unistall_plugin_settings', 'No'); // Default to 'No' if option is not set
    if ($saved_value === 'Yes') {
        delete_site_option('WC_GS_info');

        delete_option('wcgsc_verify');
         // delete Google API Setting
        delete_option('wcgsc_manual_setting');

       // delete Auto method
        delete_option('wcgsc_access_code');
        delete_option('wcgsc_token');
        delete_option('wcgsc_email_account');
        delete_option('is_new_client_secret_wcgsc');

        // auto method verify
        delete_option('wcgsc_verify');

         // delete auto method fetch date
        delete_option('wcgsc_sheet_fetch_date');

        // delete Woocommerce data settings 
        delete_option('wcgsc_settings');
        delete_option('wcgsc_order_states');

        // delete sheet details
        delete_option('wcgsc_feeds');
        delete_option('wcgsc_sheetId');
        delete_option('wcgsc_sheet_feeds');

           // delete api credentails
        delete_option('wcgsc_api_free_creds');
        delete_site_option('wcgsc_api_free_creds');

        // delete notice slider
        delete_option('wcgsc_free_install_time');
        delete_option('wcgsc_free_notice_review');
        delete_option('wcgsc_free_notice_review_time');

        delete_option('wcgsc_free_notice_addons');
        delete_option('wcgsc_free_notice_addons_time');

        delete_option('wcgsc_free_notice_pro_upsell');
        delete_option('wcgsc_free_notice_pro_upsell_time');

        // delete debug log table
        global $wpdb;
        $error_log_table = $wpdb->prefix . 'wcgsc_error_logs';

            // SHOW TABLES LIKE safe way
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $error_log_table ) );

        if ( $table_exists === $error_log_table ) {
             // Table exists → delete safely
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->query(
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
                "DROP TABLE " . esc_sql( $error_log_table ) );
        }

    }

    
}

/**
 * Validate whether the WooCommerce plugin is installed and active.
 *
 * Checks if WooCommerce exists and is activated before allowing
 * this plugin to run. If WooCommerce is missing or inactive:
 * - Displays an admin notice.
 * - Deactivates the current plugin automatically.
 *
 * Supports both single-site and multisite admin notices.
 *
 * @access public
 * @since 1.0
 * @return void
 */
public function validate_parent_plugin_exists()
{
    $plugin = plugin_basename(__FILE__);

    if ((!is_plugin_active('woocommerce/woocommerce.php')) || (!file_exists(plugin_dir_path(__DIR__) . 'woocommerce/woocommerce.php'))) {
        add_action('admin_notices', array($this, 'wc_gsheetconnector_missing_notice'));
        add_action('network_admin_notices', array($this, 'wc_gsheetconnector_missing_notice'));

        deactivate_plugins($plugin);

            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Activation context, handled by WordPress core
        $activate = isset($_GET['activate']) ? sanitize_text_field(wp_unslash($_GET['activate'])) : '';

        if ($activate === 'true') {
            unset($_GET['activate']);
        }

            // Optional: redirect safely
            // wp_safe_redirect(admin_url('plugins.php'));
            // exit;
    }
}

/**
 * Display admin notice when WooCommerce is missing or inactive.
 *
 * Shows an error message in the WordPress admin area
 * informing the user that WooCommerce is required
 * for this plugin to function properly.
 *
 * @access public
 * @since 1.0
 * @return void
 */
public function wc_gsheetconnector_missing_notice()
{
    $plugin_error = wc_gsheetconnector_utility::instance()->admin_notice(array(
        'type' => 'error',
        'message' => __('GSheetConnector WooCommerce Add-on requires WooCommerce plugin to be installed and activated.', 'wc-gsheetconnector')
    ));

    echo wp_kses_post($plugin_error);
}

/**
 * Run site-specific setup tasks during plugin activation.
 *
 * Creates and initializes the default plugin options
 * required for the current site.
 *
 * In multisite installations, this method runs individually
 * for each site during network activation.
 *
 * @since 1.0
 * @return void
 */
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- This function does not process user input.
private function run_for_site()
{
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Not processing user input, just setting defaults.
    if (!get_option('wcgsc_access_code')) {
        update_option('wcgsc_access_code', '');
    }
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if (!get_option('wcgsc_verify')) {
        update_option('wcgsc_verify', 'invalid');
    }
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if (!get_option('wcgsc_token')) {
        update_option('wcgsc_token', '');
    }
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if (!get_option('wcgsc_feeds')) {
        update_option('wcgsc_feeds', '');
    }
    if (!get_option('wcgsc_sheetId')) {
        update_option('wcgsc_sheetId', '');
    }
    if (!get_option('wcgsc_settings')) {
        update_option('wcgsc_settings', '');
    }
    if (!get_option('wcgsc_checkbox_settings')) {
        update_option('wcgsc_checkbox_settings', array());
    }
    if (!get_option('wcgsc_tab_roles_setting')) {
        update_option("wcgsc_tab_roles_setting", array());
    }
    if (!get_option('wcgsc_free_install_time')) {
        update_option('wcgsc_free_install_time', time());
    }

}


/**
 * Register plugin admin CSS and JavaScript files.
 *
 * Hooks the required CSS and JavaScript asset loading
 * methods into the WordPress admin area.
 *
 * @since 1.0
 * @return void
 */
public function load_css_and_js_files()
{
    add_action('admin_print_styles', array($this, 'add_css_files'));
    add_action('admin_print_scripts', array($this, 'add_js_files'));
}

/**
 * Enqueue admin CSS files for the plugin.
 *
 * Loads the required stylesheet files only on the
 * plugin configuration/admin settings page.
 *
 * Includes:
 * - Main plugin styles
 * - Font Awesome icons
 * - Header/Footer styles
 * - Responsive styles
 * - Additional UI and feature styles
 *
 * @since 1.0
 * @return void
 */
public function add_css_files()
{
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- safe because this is just an admin page check
    if (is_admin() && isset($_GET['page']) && sanitize_text_field(wp_unslash($_GET['page'])) === 'wc-gsheetconnector-config') {
     wp_enqueue_style(
        'gsc-fluentform.css',
        WC_GSHEETCONNECTOR_URL . 'assets/css/gs-woocommerce-connector.css',
        [],
        WC_GSHEETCONNECTOR_VERSION,
        'all'
    );

     wp_enqueue_style(
        'gsc-connector-font-awesome',
        WC_GSHEETCONNECTOR_URL . 'assets/css/fontawesome.css',
        [],
        '6.5.0',
        'all'
    );
     wp_enqueue_style(
        'gsc-connector-header',
        WC_GSHEETCONNECTOR_URL . 'assets/css/header.css',
        [],
        '6.5.0',
        'all'
    );
     wp_enqueue_style(
        'gsc-connector-footer',
        WC_GSHEETCONNECTOR_URL . 'assets/css/footer.css',
        [],
        '6.5.0',
        'all'
    );
     wp_enqueue_style(
        'gsc-connector-extra-style',
        WC_GSHEETCONNECTOR_URL . 'assets/css/extra-style.css',
        [],
        '6.5.0',
        'all'
    );
     wp_enqueue_style(
        'gsc-connector-pro-features',
        WC_GSHEETCONNECTOR_URL . 'assets/css/pro-feature.css',
        [],
        '6.5.0',
        'all'
    );
     wp_enqueue_style(
        'gsc-connector-global',
        WC_GSHEETCONNECTOR_URL . 'assets/css/global.css',
        [],
        '6.5.0',
        'all'
    );

     wp_enqueue_style(
        'gsc-connector-responsive',
        WC_GSHEETCONNECTOR_URL . 'assets/css/responsive.css',
        [],
        '6.5.0',
        'all'
    );
 }
}

/**
 * Enqueue admin JavaScript files for the plugin.
 *
 * Loads the required JavaScript files only on the
 * plugin configuration/admin settings page.
 *
 * Includes:
 * - Main plugin scripts
 * - Popup functionality
 * - Extension-related scripts
 * - System debug scripts
 *
 * @since 1.0
 * @return void
 */
public function add_js_files()
{
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- safe because this is just an admin page check
    if (is_admin() && isset($_GET['page']) && sanitize_text_field(wp_unslash($_GET['page'])) === 'wc-gsheetconnector-config') {
        
        wp_enqueue_script(
            'gs-connector-js',
            WC_GSHEETCONNECTOR_URL . 'assets/js/gs-connector.js',
            array('jquery'),
            WC_GSHEETCONNECTOR_VERSION,
            true
        );

        wp_enqueue_script(
            'wc-gsheetconnector-popup-js',
            WC_GSHEETCONNECTOR_URL . 'assets/js/wc-gsheet-popup.js',
            array('jquery'),
            WC_GSHEETCONNECTOR_VERSION,
            true
        );

        wp_enqueue_script(
            'wc-gsheetconnector-extensions-js',
            WC_GSHEETCONNECTOR_URL . 'assets/js/gs-connector-extensions.js',
            array('jquery'),
            WC_GSHEETCONNECTOR_VERSION,
            true
        );

        wp_enqueue_script(
            'wc-gsheetconnector-debug-js',
            WC_GSHEETCONNECTOR_URL . 'assets/js/system-debug.js',
            array('jquery'),
            WC_GSHEETCONNECTOR_VERSION,
            true
        );

    }
}

/**
 * Register plugin admin menu pages.
 *
 * Adds the Google Sheets submenu page under the
 * WooCommerce admin menu for authorized users.
 *
 * The menu is accessible to administrators
 * and super admins only.
 *
 * @since 1.0
 * @return void
 */
public function register_gs_menu_pages()
{
    $current_role = wc_gsheetconnector_utility::instance()->get_current_user_role();
    $gs_woo_roles = get_option('wcgsc_page_roles_setting');

    if ($current_role === "administrator" || is_super_admin()) {
            // if (( is_array($gs_woo_roles) && array_key_exists($current_role, $gs_woo_roles) ) || $current_role === "administrator" || is_super_admin()) {
        add_submenu_page('woocommerce', 'Google Sheet', 'Google Sheet', 'manage_options', 'wc-gsheetconnector-config', array($this, 'google_sheet_configuration'));
    }
}

/**
 * Display the Google Sheets configuration page.
 *
 * Loads the main plugin settings/configuration page
 * when the "Google Sheets" admin menu is accessed.
 *
 * @since 1.0
 * @return void
 */
public function google_sheet_configuration()
{
    include(WC_GSHEETCONNECTOR_PATH . "includes/pages/google-sheet-settings.php");
}

/**
 * Load required plugin classes.
 *
 * Includes additional class files required for
 * plugin functionality during initialization.
 *
 * @since 1.0
 * @return void
 */
public function load_all_classes()
{
    if (!class_exists('GS_Processes')) {
        include(WC_GSHEETCONNECTOR_PATH . 'includes/pages/integration/class-wc-gsheetconnector-processes.php');
    }
    if (!class_exists('wc_gsheetconnector_role_settings_free')) {
        include(WC_GSHEETCONNECTOR_PATH . 'includes/pages/settings/class-wc-gsheetconnector-role-settings-free.php');
    }

    if (!class_exists('wcgsc_free_extensions')) {
        include(WC_GSHEETCONNECTOR_PATH . 'includes/pages/extensions/wcgsc-extension-service.php');
    }
}

/**
 * Add custom action links to the plugin listing page.
 *
 * Adds quick access links such as Settings
 * and Upgrade to PRO below the plugin name
 * on the WordPress Plugins page.
 *
 * @since 1.5
 *
 * @param array $links Existing plugin action links.
 * @return array Modified plugin action links.
 */
public function wc_gsheet_setting_link($links)
{
    unset($links['edit']);

    $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=wc-gsheetconnector-config')) . '">' .
    esc_html__('Settings', 'wc-gsheetconnector') .
    '</a>';

    array_unshift($links, $settings_link);

    // Check if Pro version is active
    if (!is_plugin_active('wc-gsheetconnector-pro/wc-gsheetconnector-pro.php')) {
        $links['go_pro'] = sprintf(
            '<a href="%s" target="_blank" class="gsheetconnector-pro-link" style="color: green;">%s</a>',
            esc_url('https://www.gsheetconnector.com/woocommerce-google-sheet-connector-pro'),
            esc_html__('Upgrade to Pro', 'wc-gsheetconnector')
        );
    }

    return $links;
}

/**
 * Check whether a plugin class exists and is active.
 *
 * Used to verify if a specific plugin
 * is installed and activated.
 *
 * @since 2.0.2
 *
 * @param string $class Plugin main class name.
 * @return bool True if plugin class exists, otherwise false.
 */
public static function wcgsc_is_plugin_active($class)
{
    if (class_exists($class)) {
        return true;
    }
    return false;
}
}


// Initialize the google sheet connector class
$wcgsc_init = new wc_gsheetconnector_Init();

add_filter('plugin_action_links_' . WC_GSHEETCONNECTOR_BASE_NAME, array($wcgsc_init, 'wc_gsheet_setting_link'));


register_activation_hook(__FILE__, 'wcgsc_create_error_log_table');
