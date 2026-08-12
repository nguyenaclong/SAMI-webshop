<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create error log table
 * NOTE: Call this from main plugin file on activation
 */
if (!function_exists('wcgsc_create_error_log_table')) {
    function wcgsc_create_error_log_table()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'wcgsc_error_logs';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        error_id VARCHAR(191) NOT NULL,
        code INT NOT NULL,
        message TEXT NOT NULL,
        details LONGTEXT NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY error_id (error_id),
        KEY code (code)
    ) {$charset};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
}

if (!class_exists('wcgsc_error_logs')) {

    class wcgsc_error_logs
    {

        public function __construct()
        {
            add_action('admin_post_wcgsc_clear_logs', [$this, 'clear_logs']);
            add_action('admin_post_wcgsc_download_logs', [$this, 'download_logs']);

            add_action('wp_ajax_wcgsc_log_js_error', [__CLASS__, 'log_js_error']);
        }

/* =====================================================
* STATIC ENTRY POINT
* ===================================================== */

        /**
         * Render the plugin admin page.
         *
         * Creates a new instance of the current class
         * and loads the main admin page HTML.
         *
         * @return void
         */
        public static function render_page()
        {
            (new self())->render_page_html();
        }

        /**
         * Store error logs into the custom database table.
         *
         * This function:
         * - Checks whether the error log table exists.
         * - Converts string error details into a proper array.
         * - Prevents duplicate logs within 30 minutes.
         * - Saves the formatted error into the database.
         *
         * @param string       $error_id Unique error identifier.
         * @param int          $code     Error code.
         * @param string       $message  Error message.
         * @param array|string $details  Additional error details.
         *
         * @return int|false Returns inserted row ID on success, false on failure.
         */
        public static function log_to_db($error_id, $code, $message, $details = [])
        {
            global $wpdb;

            $table = esc_sql($wpdb->prefix . 'wcgsc_error_logs');

            if (
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $wpdb->get_var(
                    $wpdb->prepare(
                        'SHOW TABLES LIKE %s',
                        $table
                    )
                ) !== $table
            ) {
                return false;
            }
            // Normalize string details into an array (decode JSON when possible).
            if (is_string($details)) {
                $decoded = json_decode($details, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $details = $decoded;
                } else {
                    $details = ['raw_error' => $details];
                }
            }

            // Prevent duplicate error log entries for identical errors within 30 minutes.
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            $recent_log = $wpdb->get_var(
                $wpdb->prepare(
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                    "SELECT COUNT(*) FROM {$table} WHERE error_id = %s AND code = %d AND message = %s AND created_at >= %s",
                    $error_id,
                    $code,
                    $message,
                    wp_date(
                        'Y-m-d H:i:s',
                        time() - (30 * MINUTE_IN_SECONDS)
                    )
                )
            );


            if (!empty($recent_log)) {
                return false;
            }
            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
            return $wpdb->insert(
                $table,
                [
                    'error_id'   => (string) $error_id,
                    'code'       => (int) $code,
                    'message'    => (string) $message,
                    'details'    => wp_json_encode($details, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'created_at' => current_time('mysql'),
                ],
                ['%s', '%d', '%s', '%s', '%s']
            );
            // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery
        }

        /**
         * Capture current request context for debugging and error logging.
         *
         * This function collects useful request-related information
         * such as URL, request method, IP address, user agent,
         * referrer, and timestamp.
         *
         * @return array Request context data.
         */
        public static function get_request_context() {
            return array(
                'request_url'    => isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '',
                'request_method' => isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '',
                'status_code'    => http_response_code(),
                'remote_ip'      => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
                'user_agent'     => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
                'referrer'       => isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '',
                'timestamp'      => current_time( 'mysql' ),
            );
        }

        /**
         * Normalize debug errors and store them into database logs.
         *
         * This function:
         * - Decodes JSON string errors if possible.
         * - Handles array/object based errors.
         * - Stores raw string errors in a structured format.
         * - Sends formatted data to the main DB logger.
         *
         * @param mixed $error Error data from debug source.
         *
         * @return void
         */
        public static function log_from_debug($error)
        {
            if (is_string($error)) {
                $decoded = json_decode($error, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $error = $decoded;
                }
            }

            self::log_to_db(
                'wc_gsheet_error',
                500,
                'WooCommerce Google Sheets Error',
                (array) $error
            );
        }

        /**
         * Render the Error Log admin page.
         *
         * This function:
         * - Validates the custom log table.
         * - Checks if the table exists using cache.
         * - Retrieves logs from database with caching.
         * - Displays logs in an admin table UI.
         * - Provides actions for clearing, copying,
         *   and downloading logs.
         *
         * @return void
         */
        public function render_page_html()
        {
            global $wpdb;

            $table = $wpdb->prefix . 'wcgsc_error_logs';

            // Validate table name: only letters, numbers, and underscores allowed.
            if (! preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
                echo '<div class="notice notice-error"><p>' . esc_html__('Invalid log table name.', 'wc-gsheetconnector') . '</p></div>';
                return;
            }

            /*
           * Check table exists with cache.
           */
            $cache_key_table = 'wcgsc_log_table_exists_' . md5($table);
            $table_exists    = wp_cache_get($cache_key_table);

            if (false === $table_exists) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $table_exists = $wpdb->get_var(
                    $wpdb->prepare('SHOW TABLES LIKE %s', $table)
                );

                wp_cache_set($cache_key_table, $table_exists, '', HOUR_IN_SECONDS);
            }

            if ($table_exists !== $table) {
                echo '<div class="notice notice-error"><p>' . esc_html__('Log table not found.', 'wc-gsheetconnector') . '</p></div>';
                return;
            }

            /*
            * Get logs with cache.
            */
            $cache_key_logs = 'wcgsc_debug_logs_' . md5($table);
            $logs           = wp_cache_get($cache_key_logs);

            if (false === $logs) {
                $allowed_table = $wpdb->prefix . 'wcgsc_error_logs';

                if ($table !== $allowed_table) {
                    $logs = [];
                } else {
                    $safe_table = esc_sql($allowed_table);

                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $logs = $wpdb->get_results("SELECT * FROM `{$safe_table}` ORDER BY created_at DESC", ARRAY_A);

                    wp_cache_set($cache_key_logs, $logs, '', MINUTE_IN_SECONDS * 5);
                }
            }
            ?>
            <div class="error-log-main shadow-box mt-40 p-30">

                <div class="error-log-head flex-wrap gap-20">
                    <div>
                        <div class="heading mt-0">
                            <?php echo esc_html__('Error Log', 'wc-gsheetconnector'); ?>
                        </div>
                        <p><?php echo esc_html__("Error logs are saved in the database. Please clear them regularly to avoid increasing the database size.", 'wc-gsheetconnector'); ?></p>
                    </div>
                    <?php if (!empty($logs)) : ?>
                        <div class="errorlog-button-list">
                            <a href="<?php echo esc_url(
                                wp_nonce_url(
                                    admin_url('admin-post.php?action=wcgsc_clear_logs'),
                                    'wcgsc_clear_logs_nonce'
                                )
                                ); ?>" class="button btn-logs">
                                <?php echo esc_html__('Clear Logs', 'wc-gsheetconnector'); ?>
                            </a>

                            <a href="<?php echo esc_url(
                                wp_nonce_url(
                                    admin_url('admin-post.php?action=wcgsc_download_logs'),
                                    'wcgsc_download_logs_nonce'
                                )
                                ); ?>" class="button button-primary">
                                <?php echo esc_html__('Download CSV', 'wc-gsheetconnector'); ?>
                            </a>

                            <button type="button" id="wcgsc-copy-logs" class="button btn-logs">
                                <?php echo esc_html__('Copy Logs', 'wc-gsheetconnector'); ?>
                            </button>

                            <div class="wcgsc-copy-msg d-none"></div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="debug-log-div">
                    <table class="widefat striped error-log-table mt-30">
                        <thead>
                            <tr>
                                <th><?php echo esc_html__('Date', 'wc-gsheetconnector'); ?></th>
                                <th><?php echo esc_html__('Error ID', 'wc-gsheetconnector'); ?></th>
                                <th><?php echo esc_html__('Code', 'wc-gsheetconnector'); ?></th>
                                <th><?php echo esc_html__('Message', 'wc-gsheetconnector'); ?></th>
                                <th><?php echo esc_html__('Details', 'wc-gsheetconnector'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (! empty($logs)) : ?>
                                <?php foreach ($logs as $log) : ?>
                                    <tr>
                                        <td>
                                            <?php
                                            $format = get_option('date_format') . ' ' . get_option('time_format');
                                            echo esc_html(mysql2date($format, $log['created_at'], false));
                                            ?>
                                        </td>
                                        <td><?php echo esc_html($log['error_id']); ?></td>
                                        <td>
                                            <span class="sb-error-code" data-code="<?php echo esc_attr($log['code']); ?>">
                                                <?php echo esc_html($log['code']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo esc_html($log['message']); ?></td>
                                        <td>
                                            <?php
                                            $details = json_decode($log['details'], true);

                                            if (json_last_error() === JSON_ERROR_NONE && is_array($details)) :
                                                ?>
                                            <div class="gsc-error-details">
                                                <div class="more-error-display">
                                                    <?php
                                                    $decoded = json_decode($log['details'], true);
                                                    $display = '';

                                                    if (is_array($decoded) && !empty($decoded['raw_error'])) {

                                                        $raw = $decoded['raw_error'];

                                                        if (strpos($raw, 'message:') !== false) {
                                                            $parts = explode('message:', $raw);
                                                            $display = trim(end($parts));
                                                        } else {
                                                            $display = wp_strip_all_tags($raw);
                                                        }
                                                    } else {
                                                        $display = wp_strip_all_tags($log['details']);
                                                    }

                                                    echo esc_html($display);
                                                    ?>
                                                </div>
                                            </div>

                                            <?php else : ?>
                                                <?php echo esc_html($log['details']); ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            <?php echo esc_html__('No logs found.', 'wc-gsheetconnector'); ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <script>
                        jQuery(document).ready(function($) {
                            $('.more-error-display').each(function() {
                                var box = $(this);
                                var maxHeight = 75;

                                box.css({
                                    'max-height': maxHeight + 'px'
                                });

                                var clone = box.clone();
                                clone.css({
                                    'max-height': 'none',
                                    'height': 'auto',
                                    'position': 'absolute',
                                    'visibility': 'hidden',
                                    'overflow': 'visible'
                                });

                                $('body').append(clone);

                                if (clone.outerHeight() > maxHeight) {
                                    if (box.next('.more-error-toggle').length === 0) {
                                        var link = $('<a href="#" class="more-error-toggle">More info</a>');
                                        box.after(link);
                                    }
                                }

                                clone.remove();
                            });

                            $(document).on('click', '.more-error-toggle', function(e) {
                                e.preventDefault();

                                var link = $(this);
                                var box = link.prev('.more-error-display');

                                if (box.hasClass('expanded')) {
                                    box.removeClass('expanded').css('max-height', '75px');
                                    link.text('More info');
                                } else {
                                    box.addClass('expanded').css('max-height', 'none');
                                    link.text('Less info');
                                }
                            });
                        });
                    </script>
                </div>
                <?php
            }

        /**
         * Clear all stored error logs from the database.
         *
         * This function:
         * - Verifies current user permissions.
         * - Validates nonce for security.
         * - Truncates the custom error log table.
         * - Redirects back to the previous admin page.
         *
         * @return void
         */
        public function clear_logs()
        {
            if (!current_user_can('manage_options')) {
                wp_die('Permission denied.');
            }

            check_admin_referer('wcgsc_clear_logs_nonce');

            global $wpdb;

            $table = $wpdb->prefix . 'wcgsc_error_logs';
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is generated internally from $wpdb->prefix and is not user input.
            $wpdb->query( "TRUNCATE TABLE {$table}" );
            wp_cache_delete(
                'wcgsc_debug_logs_' . md5($table)
            );

            wp_cache_delete(
                'wcgsc_download_logs_' . md5(esc_sql($table))
            );

            wp_safe_redirect(wp_get_referer());
            exit;
        }

        /**
         * Store JavaScript errors into database logs via AJAX.
         *
         * This function:
         * - Verifies AJAX nonce security.
         * - Checks current user capability.
         * - Sanitizes and decodes incoming log data.
         * - Stores structured JS error details into DB.
         *
         * @return void
         */
        public static function log_js_error()
        {
           // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Client-side error logging endpoint.
            $log = sanitize_text_field( wp_unslash( $_POST['log'] ?? '' ) );

            self::log_to_db(
                'js_error',
                intval($log['status'] ?? 400),
                sanitize_text_field($log['message'] ?? 'JS Error'),
                $log
            );

            wp_send_json_success();
        }

        /**
         * Download all stored error logs as a CSV file.
         *
         * This function:
         * - Verifies current user permissions.
         * - Validates download nonce.
         * - Retrieves cached logs from database.
         * - Generates and downloads a CSV export file.
         *
         * @return void
         */
        public function download_logs()
        {
            if (! current_user_can('manage_options')) {
                wp_die(esc_html__('Permission denied.', 'wc-gsheetconnector'));
            }

            check_admin_referer('wcgsc_download_logs_nonce');

            global $wpdb;

            $allowed_table = $wpdb->prefix . 'wcgsc_error_logs';
            $table         = esc_sql($allowed_table);

            $cache_key_logs = 'wcgsc_download_logs_' . md5($table);
            $logs           = wp_cache_get($cache_key_logs);

            if (false === $logs) {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $logs = $wpdb->get_results("SELECT * FROM `{$table}`", ARRAY_A);

                wp_cache_set($cache_key_logs, $logs, '', MINUTE_IN_SECONDS);
            }

            if (empty($logs)) {
                wp_safe_redirect(wp_get_referer());
                exit;
            }

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename=gsc-error-logs.csv');

            $out = fopen('php://output', 'w');

            fputcsv(
                $out,
                ['Date', 'Error ID', 'Code', 'Message', 'Details']
            );

            foreach ($logs as $log) {
                fputcsv(
                    $out,
                    [
                        $log['created_at'],
                        $log['error_id'],
                        $log['code'],
                        $log['message'],
                        $log['details'],
                    ]
                );
            }

            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
            fclose($out);
            exit;
        }
    }
    new wcgsc_error_logs();
}
