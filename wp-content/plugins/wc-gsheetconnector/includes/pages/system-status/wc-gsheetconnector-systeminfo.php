<?php
  // Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// 🔒 Prevent Subscribers from seeing sensitive info
if (! current_user_can('manage_options')) {
    wp_die(
        esc_html__('You do not have permission to access this page.', 'wc-gsheetconnector')
    );
}
?>
<div class="wrap w-100 m-0">
    <div class="info-container inner-wrap w-100 bg-white p-40">
        <div class="heading mt-0"><?php esc_html_e('System Information', 'wc-gsheetconnector'); ?></div>
        <p><?php echo esc_html__('View detailed information about your plugin, server, and WordPress setup for troubleshooting and support.
        ', 'wc-gsheetconnector'); ?></p>
        <div class="text-right d-flex justify-end">
            <button id="wcgsc-copy-system-info-free" class="btn btn-primary d-flex align-center gap-10"><svg width="18"
                height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                class="gsc-copy-icon">
                <rect x="9" y="9" width="13" height="13" rx="2" stroke="currentColor" stroke-width="2"></rect>
                <rect x="2" y="2" width="13" height="13" rx="2" stroke="currentColor" stroke-width="2"></rect>
                </svg><?php esc_html_e('Copy System Info', 'wc-gsheetconnector'); ?>
            </button>
        </div>
        <div id="system-info-wrapper">
            <?php
            global $wpdb;

        // Get WordPress version.
            $wcgsc_wp_version = get_bloginfo('version');

        // Get theme info.
            $wcgsc_theme_data = wp_get_theme();
            $wcgsc_theme_name_version = $wcgsc_theme_data->get('Name') . ' ' . $wcgsc_theme_data->get('Version');
            $wcgsc_parent_theme = $wcgsc_theme_data->get('Template');

            if (!empty($wcgsc_parent_theme)) {
                $wcgsc_parent_theme_data = wp_get_theme($wcgsc_parent_theme);
                $wcgsc_parent_theme_name_version = $wcgsc_parent_theme_data->get('Name') . ' ' . $wcgsc_parent_theme_data->get('Version');
            } else {
                $wcgsc_parent_theme_name_version = 'N/A';
            }

        // Check plugin version and subscription plan.
            $wcgsc_plugin_version = defined('WC_GSHEETCONNECTOR_VERSION') ? WC_GSHEETCONNECTOR_VERSION : 'N/A';

            $wcgsc_subscription_plan = 'FREE';

            $wcgsc_api_token_auto = get_option('wcgsc_token');
            $wcgsc_auth = "";

            /*  Check Google Permission. */
            $wcgsc_verify_status = get_option('wcgsc_verify');

            if ((!empty($wcgsc_api_token_auto) && $wcgsc_verify_status == 'valid')) {
                $wcgsc_google_sheet_auto = new GSCWOO_googlesheet();
                $wcgsc_email_account_auto = $wcgsc_google_sheet_auto->gsheet_print_google_account_email();
                $wcgsc_connected_email = !empty($wcgsc_email_account_auto) ? esc_html($wcgsc_email_account_auto) : 'Not Auth';

                $wcgsc_auth = 'Authenticated Using Existing Method';
            } else {
            // Auto authentication is the  method available.
                $wcgsc_connected_email = 'Not Connected';
                $wcgsc_auth = 'No Method';
            }

            $wcgsc_search_permission = ($wcgsc_verify_status === 'valid') ? 'Granted' : 'Denied';
            /*  Create the system info HTML. */
            ?>
            <div class="mb-20 mt-20">
                <button id="wcgsc-show-info-button" class="info-button">
                    <?php echo esc_html__('GSheetConnector Status', 'wc-gsheetconnector'); ?>
                    <span class="dashicons dashicons-arrow-down"></span>
                </button>
            </div>

            <div id="info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30 d-none">
                <table>
                    <tr>
                        <td><?php echo esc_html__('Plugin Name', 'wc-gsheetconnector'); ?></td>
                        <td class="fw-600 common-badge-table info-name-blue">
                            <?php echo esc_html('GSheetConnector For WooCommerce'); ?> </td>
                        </tr>
                        <tr>
                            <td><?php echo esc_html__('Plugin Version', 'wc-gsheetconnector'); ?></td>
                            <td class="fw-600 common-badge-table info-name-blue">
                                <?php echo esc_html($wcgsc_plugin_version); ?>
                            </td>
                        </tr>

                        <tr>
                            <td><?php echo esc_html__('Plugin Subscription Plan', 'wc-gsheetconnector'); ?></td>
                            <td class="fw-600 common-badge-table pro-badge">
                                <?php echo esc_html($wcgsc_subscription_plan); ?>
                            </td>
                        </tr>

                        <tr>
                            <td><?php echo esc_html__('Connected Email Account', 'wc-gsheetconnector'); ?></td>
                            <td class="fw-600">
                                <?php echo esc_html($wcgsc_connected_email); ?>
                            </td>
                        </tr>

                        <tr>
                            <td><?php echo esc_html__('Authentication method for connecting to Google Sheets', 'wc-gsheetconnector'); ?>
                        </td>
                        <td class="fw-600">
                            <?php echo esc_html($wcgsc_auth); ?>
                        </td>
                    </tr>
                    <?php
                    $wcgsc_permission_class = ($wcgsc_search_permission === 'Granted') ? 'permission-given' : 'permission-not-given';
                    ?>

                    <tr>
                        <td> <?php echo esc_html__('Google Drive Permission', 'wc-gsheetconnector'); ?></td>
                        <td class="fw-700 permission-badge ' <?php echo esc_attr($wcgsc_permission_class); ?>">
                            <?php echo esc_html($wcgsc_search_permission); ?>
                        </td>
                    </tr>

                    <tr>
                        <td><?php echo esc_html__('Google Sheet Permission', 'wc-gsheetconnector'); ?></td>
                        <td class="fw-700 permission-badge ' . esc_attr($wcgsc_permission_class) . '">
                            <?php echo  esc_html($wcgsc_search_permission); ?>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="mb-20 mt-20">
                <button id="wcgsc-show-wordpress-info-button" class="info-button">
                    <?php echo esc_html__('WordPress', 'wc-gsheetconnector'); ?>
                    <span class="dashicons dashicons-arrow-down"></span>
                </button>
            </div>

            <div id="wordpress-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30"
            style="display:none;">
            <table>

                <tr>
                    <td><?php echo esc_html__('Version', 'wc-gsheetconnector'); ?></td>
                    <td class="fw-600 common-badge-table info-name-blue">
                        <?php echo esc_html(get_bloginfo('version')); ?>
                    </td>
                </tr>

                <tr>
                    <td><?php echo esc_html__('Site Language', 'wc-gsheetconnector'); ?></td>
                    <td class="fw-600">
                        <?php echo esc_html(get_bloginfo('language')); ?>
                    </td>
                </tr>

                <tr>
                    <td><?php echo esc_html__('Debug Mode', 'wc-gsheetconnector'); ?></td>
                    <td class="fw-600 common-badge-table info-name-yellow">
                        <?php echo esc_html(WP_DEBUG ? __('Enabled', 'wc-gsheetconnector') : __('Disabled', 'wc-gsheetconnector')); ?>
                    </td>
                </tr>

                <tr>
                    <td><?php echo esc_html__('Home URL', 'wc-gsheetconnector'); ?></td>
                    <td class="fw-600 common-badge-table info-name-blue">
                        <?php echo esc_url(get_home_url()); ?>
                    </td>
                </tr>

                <tr>
                    <td><?php echo esc_html__('Site URL', 'wc-gsheetconnector'); ?></td>
                    <td class="fw-600 common-badge-table info-name-blue">
                        <?php echo esc_url(get_site_url()); ?>
                    </td>
                </tr>

                <tr>
                    <td><?php echo esc_html__('Permalink structure', 'wc-gsheetconnector'); ?></td>
                    <td class="fw-600">
                        <?php echo esc_html(get_option('permalink_structure')); ?>
                    </td>
                </tr>

                <tr>
                    <td><?php echo esc_html__('Is this site using HTTPS?', 'wc-gsheetconnector'); ?></td>
                    <td class="fw-600">
                        <?php echo esc_html(is_ssl() ? __('Yes', 'wc-gsheetconnector') : __('No', 'wc-gsheetconnector')); ?>
                    </td>
                </tr>

                <tr>
                    <td><?php echo esc_html__('Is this a multisite?', 'wc-gsheetconnector'); ?></td>
                    <td class="fw-600">
                        <?php echo esc_html(is_multisite() ? __('Yes', 'wc-gsheetconnector') : __('No', 'wc-gsheetconnector')); ?>
                    </td>
                </tr>

                <tr>
                    <td><?php echo esc_html__('Can anyone register on this site?', 'wc-gsheetconnector'); ?>
                </td>
                <td class="fw-600">
                    <?php echo esc_html(get_option('users_can_register') ? __('Yes', 'wc-gsheetconnector') : __('No', 'wc-gsheetconnector')); ?>
                </td>
            </tr>

            <tr>
                <td><?php echo esc_html__('Is this site discouraging search engines?', 'wc-gsheetconnector'); ?>
            </td>
            <td class="fw-600">
                <?php echo esc_html(get_option('blog_public') ? __('No', 'wc-gsheetconnector') : __('Yes', 'wc-gsheetconnector')); ?>
            </td>
        </tr>

        <tr>
            <td><?php echo esc_html__('Default comment status', 'wc-gsheetconnector'); ?></td>
            <td class="fw-600">
                <?php echo esc_html(get_option('default_comment_status')); ?>
            </td>
        </tr>
        <?php
        $wcgsc_server_ip = isset($_SERVER['REMOTE_ADDR']) ? filter_var(wp_unslash($_SERVER['REMOTE_ADDR']), FILTER_VALIDATE_IP) : '';

        if (filter_var($wcgsc_server_ip, FILTER_VALIDATE_IP) === false) {
            $wcgsc_environment_type = __('Unknown', 'wc-gsheetconnector');
        } else {
            $wcgsc_known_local_ips = array('127.0.0.1', '::1');
            $wcgsc_isLocalhost = in_array($wcgsc_server_ip, $wcgsc_known_local_ips, true);
            $wcgsc_environment_type = $wcgsc_isLocalhost
            ? __('Localhost', 'wc-gsheetconnector')
            : __('Production', 'wc-gsheetconnector');
        }
        ?>

        <tr>
            <td><?php echo esc_html__('Environment type', 'wc-gsheetconnector'); ?></td>
            <td class="fw-600 common-badge-table info-name-yellow">
                <?php echo esc_html($wcgsc_environment_type); ?>
            </td>
        </tr>

        <?php
        $wcgsc_user_count  = count_users();
        $wcgsc_total_users = isset($wcgsc_user_count['total_users']) ? (int) $wcgsc_user_count['total_users'] : 0;
        ?>

        <tr>
            <td><?php echo esc_html__('User Count', 'wc-gsheetconnector'); ?></td>
            <td class="fw-600">
                <?php echo esc_html($wcgsc_total_users); ?>
            </td>
        </tr>

        <tr>
            <td><?php echo esc_html__('Communication with WordPress.org', 'wc-gsheetconnector'); ?>
        </td>
        <td class="fw-600">
            <?php echo esc_html(get_option('blog_publicize') ? __('Yes', 'wc-gsheetconnector') : __('No', 'wc-gsheetconnector')); ?>
        </td>
    </tr>

</table>
</div>

<!-- Active Theme Information -->
<?php
$wcgsc_active_theme = wp_get_theme();
?>
<div class="mb-20 mt-20">
    <button id="wcgsc-show-active-theme-button"
    class="info-button"><?php echo esc_html__('Active Theme', 'wc-gsheetconnector'); ?>
    <span class="dashicons dashicons-arrow-down"></span>
</button>
</div>

<div id="active-theme-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30"
style="display:none;">
<table>
    <tr>
        <td><?php echo esc_html__('Name', 'wc-gsheetconnector'); ?></td>
        <td class="fw-600 common-badge-table info-name-blue">
            <?php echo esc_html($wcgsc_active_theme->get('Name')); ?>
        </td>
    </tr>

    <tr>
        <td><?php echo esc_html__('Version', 'wc-gsheetconnector'); ?></td>
        <td class="fw-600 common-badge-table info-name-blue">
            <?php echo esc_html($wcgsc_active_theme->get('Version')); ?>
        </td>
    </tr>

    <tr>
        <td><?php echo esc_html__('Author', 'wc-gsheetconnector'); ?></td>
        <td class="fw-600">
            <?php echo wp_kses_post($wcgsc_active_theme->get('Author')); ?>
        </td>
    </tr>

    <tr>
        <td><?php echo esc_html__('Author website', 'wc-gsheetconnector'); ?></td>
        <td class="fw-600">
            <?php echo esc_url($wcgsc_active_theme->get('AuthorURI')); ?>
        </td>
    </tr>

    <tr>
        <td><?php echo esc_html__('Theme directory location', 'wc-gsheetconnector'); ?></td>
        <td class="fw-600">
            <?php echo esc_html($wcgsc_active_theme->get_template_directory()); ?>
        </td>
    </tr>

</table>
</div>
<?php
        // Get a list of other plugins you want to check compatibility with.
$wcgsc_other_plugins = array(
            'plugin-folder/plugin-file.php', // Replace with the actual plugin slug
            // Add more plugins as needed.
        );
$wcgsc_active_plugins = get_option('active_plugins', array());
        // Network Active Plugins.
if (is_multisite()) {
    $wcgsc_network_active_plugins = get_site_option('active_sitewide_plugins', array());
    if (!empty($wcgsc_network_active_plugins)) { ?>
        <div class="mb-20 mt-20"><button id="wcgsc-show-netplug-info-button" class="info-button"><?php echo esc_html__('Network Active plugins', 'wc-gsheetconnector'); ?>
        <span class="dashicons dashicons-arrow-down"></span></button></div>';
        <div id="netplug-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30 d-none">
            ';
            <table>
                <?php
                foreach ( $wcgsc_network_active_plugins as $wcgsc_plugin => $wcgsc_plugin_data ) {
                    $wcgsc_plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $wcgsc_plugin );
                    ?>
                    <tr>
                        <td><?php echo esc_html( $wcgsc_plugin_data['Name'] ); ?></td>
                        <td><?php echo esc_html( $wcgsc_plugin_data['Version'] ); ?></td>
                    </tr>
                    <?php
                } ?>
            </table>
        </div>
        <?php
    }
}

$wcgsc_total_active_plugins = is_array($wcgsc_active_plugins) ? count($wcgsc_active_plugins) : 0;
?>
<div class="mb-20 mt-20">
    <button id="wcgsc-show-acplug-info-button" class="info-button">
        <?php echo esc_html__('Active Plugins', 'wc-gsheetconnector'); ?>
        (<?php echo esc_html($wcgsc_total_active_plugins); ?>)
        <span class="dashicons dashicons-arrow-down"></span>
    </button>
</div>

<div id="acplug-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30" style="display:none;">
    <table>
        <?php
        /* Retrieve all active plugins data */
        $wcgsc_active_plugins_data = array();
        $wcgsc_active_plugins = get_option('active_plugins', array());

        foreach ($wcgsc_active_plugins as $wcgsc_plugin) {
            $wcgsc_plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $wcgsc_plugin);
            $wcgsc_active_plugins_data[$wcgsc_plugin] = array(
                'name' => $wcgsc_plugin_data['Name'],
                'version' => $wcgsc_plugin_data['Version'],
                'count' => 0, /* Initialize the count to zero */
            );
        }

        /* Count the number of active installations for each plugin */
        $wcgsc_all_plugins = get_plugins();
        foreach ($wcgsc_all_plugins as $wcgsc_plugin_file => $wcgsc_plugin_data) {
            if (array_key_exists($wcgsc_plugin_file, $wcgsc_active_plugins_data)) {
                $wcgsc_active_plugins_data[$wcgsc_plugin_file]['count']++;
            }
        }

        /* Sort plugins based on the number of active installations (descending order) */
        uasort($wcgsc_active_plugins_data, function ($a, $b) {
            return $b['count'] - $a['count'];
        });

        foreach ( $wcgsc_active_plugins_data as $wcgsc_plugin_data ) {
            ?>
            <tr>
                <td><?php echo esc_html( $wcgsc_plugin_data['name'] ); ?></td>
                <td class="fw-600 common-badge-table info-name-blue">
                    <?php echo esc_html( $wcgsc_plugin_data['version'] ); ?>
                </td>
            </tr>
            <?php
        }?>
    </table>
</div>

        <!-- Webserver Configuration
        -->
        <div class="mb-20 mt-20">
            <button id="wcgsc-show-server-info-button" class="info-button">
                <?php echo esc_html__('Server', 'wc-gsheetconnector'); ?>
                <span class="dashicons dashicons-arrow-down"></span>
            </button>
        </div>

        <div id="server-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30 d-none">
         <p class="text-dark">
            <b>
                <?php echo esc_html__(
                    'The options shown below relate to your server setup. If changes are required, you may need your web host’s assistance.',
                    'wc-gsheetconnector'
                ); ?>
            </b>
        </p>

        <table>
            <tr>
                <td><?php echo esc_html__('Server Architecture', 'wc-gsheetconnector'); ?></td>
                <td class="fw-600"><?php echo esc_html(php_uname('s')); ?></td>
            </tr>

            <?php
            $wcgsc_server_software = isset($_SERVER['SERVER_SOFTWARE'])
            ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE']))
            : '';
            ?>

            <tr>
                <td><?php echo esc_html__('Web Server', 'wc-gsheetconnector'); ?></td>
                <td class="fw-600"><?php echo esc_html($wcgsc_server_software); ?></td>
            </tr>

            <tr>
                <td><?php echo esc_html__('PHP Version', 'wc-gsheetconnector'); ?></td>
                <td class="fw-600 common-badge-table info-name-blue"><?php echo esc_html(PHP_VERSION); ?></td>
            </tr>

            <tr>
                <td><?php echo esc_html__('PHP SAPI', 'wc-gsheetconnector'); ?></td>
                <td class="fw-600"><?php echo esc_html(php_sapi_name()); ?></td>
            </tr>

            <tr>
                <td><?php echo esc_html__('PHP Max Input Variables', 'wc-gsheetconnector'); ?></td>
                <td class="fw-600"><?php echo esc_html(ini_get('max_input_vars')); ?></td>
            </tr>

            <tr>
                <td><?php echo esc_html__('PHP Time Limit', 'wc-gsheetconnector'); ?></td>
                <td class="fw-600">
                    <?php echo esc_html(ini_get('max_execution_time')); ?>
                    <?php echo esc_html__('seconds', 'wc-gsheetconnector'); ?>
                </td>
            </tr>

            <tr>
                <td><?php echo esc_html__('PHP Memory Limit', 'wc-gsheetconnector'); ?></td>
                <td class="fw-600"><?php echo esc_html(ini_get('memory_limit')); ?></td>
            </tr>

            <tr>
                <td><?php echo esc_html__('Max Input Time', 'wc-gsheetconnector'); ?></td>
                <td class="fw-600">
                    <?php echo esc_html(ini_get('max_input_time')); ?>
                    <?php echo esc_html__('seconds', 'wc-gsheetconnector'); ?>
                </td>
            </tr>

            <tr>
                <td><?php echo esc_html__('Upload Max Filesize', 'wc-gsheetconnector'); ?></td>
                <td class="fw-600"><?php echo esc_html(ini_get('upload_max_filesize')); ?></td>
            </tr>

            <tr>
                <td><?php echo esc_html__('PHP Post Max Size', 'wc-gsheetconnector'); ?></td>
                <td class="fw-600"><?php echo esc_html(ini_get('post_max_size')); ?></td>
            </tr>

            <?php
            $wcgsc_curl_version = function_exists('curl_version') ? curl_version() : array();
            $wcgsc_curl_display = isset($wcgsc_curl_version['version']) ? $wcgsc_curl_version['version'] : __('Not Installed', 'wc-gsheetconnector');
            ?>

            <tr>
                <td><?php echo esc_html__('cURL Version', 'wc-gsheetconnector'); ?></td>
                <td class="fw-600"><?php echo esc_html($wcgsc_curl_display); ?></td>
            </tr>

            <tr>
                <td><?php echo esc_html__('Is SUHOSIN Installed?', 'wc-gsheetconnector'); ?></td>
                <td class="fw-600">
                    <?php echo esc_html(extension_loaded('suhosin') ? __('Yes', 'wc-gsheetconnector') : __('No', 'wc-gsheetconnector')); ?>
                </td>
            </tr>

            <tr>
                <td><?php echo esc_html__('Is the Imagick Library Available?', 'wc-gsheetconnector'); ?>
            </td>
            <td class="fw-600">
                <?php echo esc_html(extension_loaded('imagick') ? __('Yes', 'wc-gsheetconnector') : __('No', 'wc-gsheetconnector')); ?>
            </td>
        </tr>

        <tr>
            <td><?php echo esc_html__('Are Pretty Permalinks Supported?', 'wc-gsheetconnector'); ?>
        </td>
        <td class="fw-600">
            <?php echo esc_html(get_option('permalink_structure') ? __('Yes', 'wc-gsheetconnector') : __('No', 'wc-gsheetconnector')); ?>
        </td>
    </tr>

    <?php
    global $wp_filesystem;

    if (empty($wp_filesystem)) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
    }

    $wcgsc_htaccess_path = ABSPATH . '.htaccess';
    $wcgsc_is_writable = (isset($wp_filesystem) && $wp_filesystem->exists($wcgsc_htaccess_path) && $wp_filesystem->is_writable($wcgsc_htaccess_path));
    ?>

    <tr>
        <td><?php echo esc_html__('.htaccess Rules', 'wc-gsheetconnector'); ?></td>
        <td class="fw-600">
            <?php echo esc_html($wcgsc_is_writable ? __('Writable', 'wc-gsheetconnector') : __('Not Writable', 'wc-gsheetconnector')); ?>
        </td>
    </tr>

    <tr>
        <td><?php echo esc_html__('Current Time', 'wc-gsheetconnector'); ?></td>
        <td class="fw-600"><?php echo esc_html(current_time('mysql')); ?></td>
    </tr>

    <tr>
        <td><?php echo esc_html__('Current UTC Time', 'wc-gsheetconnector'); ?></td>
        <td class="fw-600"><?php echo esc_html(current_time('mysql', true)); ?></td>
    </tr>

    <tr>
        <td><?php echo esc_html__('Current Server Time', 'wc-gsheetconnector'); ?></td>
        <td class="fw-600"><?php echo esc_html(gmdate('Y-m-d H:i:s')); ?></td>
    </tr>

</table>
</div>

<!-- Database Configuration -->

<div class="mb-20 mt-20">
    <button id="wcgsc-show-database-info-button" class="info-button">
        <?php echo esc_html__('Database', 'wc-gsheetconnector'); ?>
        <span class="dashicons dashicons-arrow-down"></span>
    </button>
</div>

<div id="database-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30 d-none">
    <table>
        <?php
        $wcgsc_database_extension = 'mysqli';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Retrieving database server version for system information.
        $wcgsc_database_server_version = $wpdb->get_var( 'SELECT VERSION()' );
        $wcgsc_database_client_version = $wpdb->db_version();
        $wcgsc_database_username = DB_USER;
        $wcgsc_database_host = DB_HOST;
        $wcgsc_database_name = DB_NAME;
        $wcgsc_table_prefix = $wpdb->prefix;
        $wcgsc_database_charset = $wpdb->charset;
        $wcgsc_database_collation = $wpdb->collate;
          // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Retrieving database server version for system information.
        $wcgsc_max_allowed_packet_size = $wpdb->get_var("SHOW VARIABLES LIKE 'max_allowed_packet'");
          // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Retrieving database server version for system information.
        $wcgsc_max_connections_number = $wpdb->get_var("SHOW VARIABLES LIKE 'max_connections'");
        ?>
        <tr>
            <td><?php echo esc_html__('Extension', 'wc-gsheetconnector'); ?></td>
            <td class="fw-600">
                <?php echo esc_html($wcgsc_database_extension); ?>
            </td>
        </tr>

        <tr>
            <td><?php echo esc_html__('Server Version', 'wc-gsheetconnector'); ?></td>
            <td class="fw-600 common-badge-table info-name-blue">
                <?php echo esc_html($wcgsc_database_server_version); ?>
            </td>
        </tr>

        <tr>
            <td><?php echo esc_html__('Client Version', 'wc-gsheetconnector'); ?></td>
            <td class="fw-600 common-badge-table info-name-blue">
                <?php echo esc_html($wcgsc_database_client_version); ?>
            </td>
        </tr>

        <tr>
            <td><?php echo esc_html__('Database Username', 'wc-gsheetconnector'); ?></td>
            <td class="fw-600">
                <?php echo esc_html($wcgsc_database_username); ?>
            </td>
        </tr>

        <tr>
            <td><?php echo esc_html__('Database Host', 'wc-gsheetconnector'); ?></td>
            <td class="fw-600 common-badge-table info-name-yellow">
                <?php echo esc_html($wcgsc_database_host); ?>
            </td>
        </tr>

        <tr>
            <td><?php echo esc_html__('Database Name', 'wc-gsheetconnector'); ?></td>
            <td class="fw-600 common-badge-table info-name-blue">
                <?php echo esc_html($wcgsc_database_name); ?>
            </td>
        </tr>

        <tr>
            <td><?php echo esc_html__('Table Prefix', 'wc-gsheetconnector'); ?></td>
            <td class="fw-600">
                <?php echo esc_html($wcgsc_table_prefix); ?>
            </td>
        </tr>

        <tr>
            <td><?php echo esc_html__('Database Charset', 'wc-gsheetconnector'); ?></td>
            <td class="fw-600">
                <?php echo esc_html($wcgsc_database_charset); ?>
            </td>
        </tr>

        <tr>
            <td><?php echo esc_html__('Database Collation', 'wc-gsheetconnector'); ?></td>
            <td class="fw-600">
                <?php echo esc_html($wcgsc_database_collation); ?>
            </td>
        </tr>

        <tr>
            <td><?php echo esc_html__('Max Allowed Packet Size', 'wc-gsheetconnector'); ?></td>
            <td class="fw-600">
                <?php echo esc_html($wcgsc_max_allowed_packet_size); ?>
            </td>
        </tr>

        <tr>
            <td><?php echo esc_html__('Max Connections Number', 'wc-gsheetconnector'); ?></td>
            <td class="fw-600">
                <?php echo esc_html($wcgsc_max_connections_number); ?>
            </td>
        </tr>


    </table>

</div>

<!-- WordPress constants -->
<div class="mb-20 mt-20">
    <button id="wcgsc-show-wrcons-info-button" class="info-button">
        <?php echo esc_html__('WordPress Constants', 'wc-gsheetconnector'); ?>
        <span class="dashicons dashicons-arrow-down"></span>
    </button>
</div>

<div id="wrcons-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30 d-none">
    <table>

        <tr>
            <td>ABSPATH</td>
            <td class="fw-600"><?php echo esc_html(ABSPATH); ?></td>
        </tr>

        <tr>
            <td>WP_HOME</td>
            <td class="fw-600 common-badge-table info-name-blue">
                <?php echo esc_url(home_url()); ?>
            </td>
        </tr>

        <tr>
            <td>WP_SITEURL</td>
            <td class="fw-600"><?php echo esc_url(site_url()); ?></td>
        </tr>

        <tr>
            <td>WP_CONTENT_DIR</td>
            <td class="fw-600"><?php echo esc_html(WP_CONTENT_DIR); ?></td>
        </tr>

        <tr>
            <td>WP_PLUGIN_DIR</td>
            <td class="fw-600"><?php echo esc_html(WP_PLUGIN_DIR); ?></td>
        </tr>

        <tr>
            <td>WP_MEMORY_LIMIT</td>
            <td class="fw-600"><?php echo esc_html(WP_MEMORY_LIMIT); ?></td>
        </tr>

        <tr>
            <td>WP_MAX_MEMORY_LIMIT</td>
            <td class="fw-600"><?php echo esc_html(WP_MAX_MEMORY_LIMIT); ?></td>
        </tr>

        <tr>
            <td>WP_DEBUG</td>
            <td class="fw-600">
                <?php echo esc_html((defined('WP_DEBUG') && WP_DEBUG) ? __('Yes', 'wc-gsheetconnector') : __('No', 'wc-gsheetconnector')); ?>
            </td>
        </tr>

        <tr>
            <td>WP_DEBUG_DISPLAY</td>
            <td class="fw-600">
                <?php echo esc_html((defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY) ? __('Yes', 'wc-gsheetconnector') : __('No', 'wc-gsheetconnector')); ?>
            </td>
        </tr>

        <tr>
            <td>SCRIPT_DEBUG</td>
            <td class="fw-600">
                <?php echo esc_html((defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? __('Yes', 'wc-gsheetconnector') : __('No', 'wc-gsheetconnector')); ?>
            </td>
        </tr>

        <tr>
            <td>WP_CACHE</td>
            <td class="fw-600">
                <?php echo esc_html((defined('WP_CACHE') && WP_CACHE) ? __('Yes', 'wc-gsheetconnector') : __('No', 'wc-gsheetconnector')); ?>
            </td>
        </tr>

        <tr>
            <td>CONCATENATE_SCRIPTS</td>
            <td class="fw-600">
                <?php echo esc_html((defined('CONCATENATE_SCRIPTS') && CONCATENATE_SCRIPTS) ? __('Yes', 'wc-gsheetconnector') : __('No', 'wc-gsheetconnector')); ?>
            </td>
        </tr>

        <tr>
            <td>COMPRESS_SCRIPTS</td>
            <td class="fw-600">
                <?php echo esc_html((defined('COMPRESS_SCRIPTS') && COMPRESS_SCRIPTS) ? __('Yes', 'wc-gsheetconnector') : __('No', 'wc-gsheetconnector')); ?>
            </td>
        </tr>

        <tr>
            <td>COMPRESS_CSS</td>
            <td class="fw-600">
                <?php echo esc_html((defined('COMPRESS_CSS') && COMPRESS_CSS) ? __('Yes', 'wc-gsheetconnector') : __('No', 'wc-gsheetconnector')); ?>
            </td>
        </tr>

        <?php
        $wcgsc_environment_type = defined( 'WP_ENVIRONMENT_TYPE' )
        ? WP_ENVIRONMENT_TYPE
        : 'production';
        ?>

        <tr>
            <td>WP_ENVIRONMENT_TYPE</td>
            <td class="fw-600"><?php echo esc_html($wcgsc_environment_type); ?></td>
        </tr>

        <tr>
            <td>WP_DEVELOPMENT_MODE</td>
            <td class="fw-600">
                <?php echo esc_html((defined('WP_DEVELOPMENT_MODE') && WP_DEVELOPMENT_MODE) ? __('Yes', 'wc-gsheetconnector') : __('No', 'wc-gsheetconnector')); ?>
            </td>
        </tr>

        <tr>
            <td>DB_CHARSET</td>
            <td class="fw-600"><?php echo esc_html(DB_CHARSET); ?></td>
        </tr>

        <tr>
            <td>DB_COLLATE</td>
            <td class="fw-600"><?php echo esc_html(DB_COLLATE); ?></td>
        </tr>

    </table>
</div>

<!--Filesystem Permission -->


<div class="mb-20 mt-20">
    <button id="wcgsc-show-ftps-info-button" class="info-button">
        <?php echo esc_html__('Filesystem Permission', 'wc-gsheetconnector'); ?>
        <span class="dashicons dashicons-arrow-down"></span>
    </button>
</div>

<div id="ftps-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30 d-none">

    <p class="text-dark"><b><?php echo
    esc_html__(
        'Shows whether WordPress is able to write to the directories it needs access to.',
        'wc-gsheetconnector'
    ); ?>
</b></p>

<table>
    <?php
    global $wp_filesystem;

    if (empty($wp_filesystem)) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
    }

    /* Safer uploads directory */
    $wcgsc_upload_dir = wp_get_upload_dir();
    $wcgsc_uploads_path = isset($wcgsc_upload_dir['basedir']) ? $wcgsc_upload_dir['basedir'] : '';

    $wcgsc_paths = array(
        __('The main WordPress directory', 'wc-gsheetconnector') => ABSPATH,
        __('The wp-content directory', 'wc-gsheetconnector') => WP_CONTENT_DIR,
        __('The uploads directory', 'wc-gsheetconnector') => $wcgsc_uploads_path,
        __('The plugins directory', 'wc-gsheetconnector') => WP_PLUGIN_DIR,
        __('The themes directory', 'wc-gsheetconnector') => get_theme_root(),
    );

    foreach ($wcgsc_paths as $wcgsc_label => $wcgsc_path) {

        $wcgsc_writable = (
            isset($wp_filesystem) &&
            $wp_filesystem->exists($wcgsc_path) &&
            $wp_filesystem->is_writable($wcgsc_path)
        );
        ?>
        <tr>
            <td><?php echo esc_html($wcgsc_label); ?></td>
            <td><?php echo  esc_html($wcgsc_path); ?></td>
            <td class="fw-600"><?php echo
            esc_html(
                $wcgsc_writable
                ? __('Writable', 'wc-gsheetconnector')
                : __('Not Writable', 'wc-gsheetconnector')
            ); ?>
        </td>
    </tr>
<?php } ?>
</table>
</div>
</div>
</div>

<?php
$wcgsc_debug_log_file = WP_CONTENT_DIR . '/debug.log';

$wcgsc_log_lines = [];

if (file_exists($wcgsc_debug_log_file)) {
    $wcgsc_log_lines = file(
        $wcgsc_debug_log_file,
        FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
    );

    $wcgsc_log_lines = array_slice(array_reverse($wcgsc_log_lines), 0, 100);
}

$wcgsc_has_logs = !empty($wcgsc_log_lines);
?>

<div class="system-error shadow-box mt-40 p-30">
    <div class="error-container">

        <div class="error-log-head flex-wrap gap-20">
            <div class="heading mt-0 mb-0">
                <?php esc_html_e('Error Log', 'wc-gsheetconnector'); ?>
            </div>

            <?php if ($wcgsc_has_logs) { ?>
                <div class="errorlog-button-list">
                    <span class="clear-loading-sign"></span>

                    <button type="button" class="button btn-logs wcgsc-clear-content-logs">
                      <?php esc_html_e('Clear Logs', 'wc-gsheetconnector'); ?>
                  </button>


                  <button type="button" class="button button-primary" id="wcgsc-csv-info">
                      <?php esc_html_e('Download CSV', 'wc-gsheetconnector'); ?>
                  </button>


                  <button type="button" class="button btn-logs" id="wcgsc-copy-logs-info">
                      <?php esc_html_e('Copy Logs', 'wc-gsheetconnector'); ?>
                  </button>
                  <div class="wcgsc-copy-msg d-none"></div>
              </div>
          <?php } ?>
      </div>

      <!-- <div class="gscgff-validation-message"></div> -->

      <input type="hidden"
      name="wcgsc-ajax-nonce"
      id="wcgsc-ajax-nonce"
      value="<?php echo esc_attr(wp_create_nonce('wcgsc-ajax-nonce')); ?>" />

      <!-- Log Start -->
      <?php

      echo '<div style="max-height:500px; overflow:auto;">';
      echo '<table class="widefat striped mt-30">';

      echo '<thead>
      <tr>
      <th>Date</th>
      <th>Type</th>
      <th>Message</th>
      <th>File</th>
      </tr>
      </thead>';

      echo '<tbody>';

      if ($wcgsc_has_logs) {

        foreach ($wcgsc_log_lines as $wcgsc_line) {

            if (preg_match('/\[(.*?)\]\s(.*?):\s(.*)/', $wcgsc_line, $wcgsc_matches)) {

                $wcgsc_date    = str_replace(' UTC', '', $wcgsc_matches[1]);
                $wcgsc_type    = str_replace('PHP ', '', $wcgsc_matches[2]);
                $wcgsc_message = $wcgsc_matches[3];

                $wcgsc_file = '-';

                if (preg_match('/in (.*?) on line/', $wcgsc_message, $wcgsc_file_match)) {
                    $wcgsc_file = $wcgsc_file_match[1];
                }

                echo '<tr>';
                echo '<td>' . esc_html($wcgsc_date) . '</td>';
                echo '<td>' . esc_html($wcgsc_type) . '</td>';
                echo '<td>' . esc_html($wcgsc_message) . '</td>';
                echo '<td>' . esc_html($wcgsc_file) . '</td>';
                echo '</tr>';
            }
        }

    } else {

        echo '<tr>';
        echo '<td colspan="4" class="text-center">'
        . esc_html__('No error logs found.', 'wc-gsheetconnector')
        . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    ?>
    <!-- Log End -->

</div>
</div>
</div>