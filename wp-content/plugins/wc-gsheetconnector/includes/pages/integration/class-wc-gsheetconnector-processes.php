<?php

/*
 * Process class for woocommerce google sheet connector pro
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * wc_gsheetconnector_Service class
 * @since 1.0
 */
class wc_gsheetconnector_processes {

	public function __construct() {
		if ( ! get_option( 'wcgsc_options_migrated' ) ) {
			$this->maybe_migrate_old_options();
		}

    if (! get_option('wcgsc_debug_migrated_pro')) {
      $this->maybe_migrate_wcgsc_debug_log();
    }

    add_action( 'wp_ajax_wcgsc_verify_integration', array( $this, 'wcgsc_verify_integration' ) );

		//deactivate google sheet integration
    add_action( 'wp_ajax_wcgsc_deactivate_integration', array( $this, 'wcgsc_deactivate_integration' ) );

		// get sheet name and tab name
    add_action( 'wp_ajax_wcgsc_sync_google_account', array( $this, 'wcgsc_sync_google_account' ) );

		// clear debug log data
    add_action( 'wp_ajax_wcgsc_clear_log', array( $this, 'wcgsc_clear_logs' ) );

		// get sheet names
    add_action( 'wp_ajax_wcgsc_get_tab_list', array( $this, 'wcgsc_get_tab_list_by_sheetname' ) );

	    // Display widget to dashboard
    add_action( 'wp_dashboard_setup', array( $this, 'wcgsc_add_summary_widget' ) );

    add_action('wp_ajax_wcgsc_log_systeminfo', array($this, 'wcgsc_log_systeminfo'));

    //pagination
    add_action('wp_ajax_wcgsc_free_paginate_feed_list', array($this, 'wcgsc_free_paginate_feed_list'));

  }

/**
 * Handles the AJAX request for paginating the WooCommerce dashboard feed list.
 *
 * @since 1.4.9
 *
 * @return void
 */
public function wcgsc_free_paginate_feed_list() {

  check_ajax_referer( 'wcgsc-pagination', 'security' );

  if ( ! current_user_can( 'manage_options' ) ) {
    wp_send_json_error( 'Unauthorized', 403 );
  }

  $result = $this->wcgsc_render_feed_page();

  wp_send_json_success( $result );
}

/**
 * Render WooCommerce dashboard feed list with pagination.
 *
 * @since 1.4.9
 *
 * @return array
 */
public function wcgsc_render_feed_page() {


 $wcgsc_sheet_data = get_option( 'wcgsc_sheet_feeds' );
 $wcgsc_settings = get_option( 'wcgsc_settings', '' );

 if ( is_array( $wcgsc_settings ) ) {
  $wcgsc_selected_sheet_key = $wcgsc_settings['spreadsheet_id'] ?? '';
} else {
  $wcgsc_selected_sheet_key = $wcgsc_settings;
}

$wcgsc_sheet_name = __( 'Google Sheet Not Connected', 'wc-gsheetconnector' );

if (
  ! empty( $wcgsc_selected_sheet_key ) &&
  isset( $wcgsc_sheet_data[ $wcgsc_selected_sheet_key ] )
) {
  $wcgsc_sheet_name = $wcgsc_sheet_data[ $wcgsc_selected_sheet_key ]['sheet_name'];
}

ob_start();

?>

<tr>
  <td><?php esc_html_e( 'WooCommerce Data Settings', 'wc-gsheetconnector' ); ?></td>

  <td>
    <?php if ( ! empty( $wcgsc_selected_sheet_key ) ) : ?>

      <a target="_blank"
      href="<?php echo esc_url( 'https://docs.google.com/spreadsheets/d/' . $wcgsc_selected_sheet_key ); ?>">
      <?php echo esc_html( $wcgsc_sheet_name ); ?>
    </a>

  <?php else : ?>

    <span class="wcgsc-not-connected">
      <?php esc_html_e( 'Not connected', 'wc-gsheetconnector' ); ?>
    </span>

  <?php endif; ?>
</td>
</tr>
<?php 

$rows_html = ob_get_clean();

return array(
  'rows_html'       => $rows_html,
  'pagination_html' => '',
  'has_feeds'       => true,
);
}

/**
 * Clear the WordPress debug.log file via AJAX.
 *
 * Verifies the AJAX nonce, initializes the WordPress
 * Filesystem API, and removes all contents from the
 * debug.log file used for system status diagnostics.
 *
 * @since 2.1
 *
 * @return void Sends a JSON success or error response.
 */
public function wcgsc_log_systeminfo() {
        // nonce check
 check_ajax_referer( 'wcgsc-ajax-nonce', 'security' );

 if ( ! current_user_can( 'manage_options' ) ) {
  wp_send_json_error( 'Unauthorized', 403 );
 }

 $wp_filesystem = $this->wcgsc_init_filesystem();

 $log_file = WP_CONTENT_DIR . '/debug.log';

        // Clear the log file using WP_Filesystem
 if ( $wp_filesystem->exists( $log_file ) ) {
  $wp_filesystem->put_contents( $log_file, '', FS_CHMOD_FILE );
} else {
  wp_send_json_error( 'Log file not found.' );
}

wp_send_json_success();
}

/**
 * Initialize and return the WordPress Filesystem API instance.
 *
 * Loads the filesystem helpers if required and bootstraps
 * the global $wp_filesystem object for safe file operations.
 *
 * @since 2.1
 *
 * @return WP_Filesystem_Base The initialized filesystem instance.
 */
private function wcgsc_init_filesystem() {
 if ( ! function_exists( 'WP_Filesystem' ) ) {
  require_once ABSPATH . 'wp-admin/includes/file.php';
}
global $wp_filesystem;
WP_Filesystem();
return $wp_filesystem;
}

/**
 * Clear the plugin debug log file via AJAX.
 *
 * Verifies the AJAX nonce, checks whether the configured
 * log file exists, and clears its contents using the
 * WordPress Filesystem API.
 *
 * @since 1.0
 *
 * @return void Sends a JSON success response with a status message.
 */
public function wcgsc_clear_logs() {
	    // nonce check
 check_ajax_referer( 'wcgsc-ajax-nonce', 'security' );

 if ( ! current_user_can( 'manage_options' ) ) {
  wp_send_json_error( 'Unauthorized', 403 );
 }

 $wcexistDebugFile = get_option('wcfgs_debug_log_file');
 $clear_file_msg = '';

 if ( ! empty( $wcexistDebugFile ) && file_exists( $wcexistDebugFile ) ) {

	        // Load WP_Filesystem API
   $wp_filesystem = $this->wcgsc_init_filesystem();

	        // Clear file using WP_Filesystem
   if ( $wp_filesystem->put_contents( $wcexistDebugFile, '', FS_CHMOD_FILE ) ) {
     $clear_file_msg = 'Logs are cleared.';
   } else {
     $clear_file_msg = 'Failed to clear log file.';
   }

 } else {
  $clear_file_msg = 'No log file exists to clear logs.';
}

wp_send_json_success( $clear_file_msg );
}


/**
 * Register the WooCommerce GSheetConnector
 * dashboard widget in the WordPress admin dashboard.
 *
 * Creates a custom dashboard widget with plugin branding
 * and displays a summary of integration information.
 *
 * @since 1.0
 */
public function wcgsc_add_summary_widget() {
  $image_url = esc_url( WC_GSHEETCONNECTOR_URL . 'assets/img/woo-gsc.svg' );
		// phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Plugin-bundled static image, not a Media Library attachment
  $title = "<img style='width:30px;margin-right: 10px; vertical-align:middle;' src='{$image_url}' alt='WooCommerce GSheetConnector' />";

  $title .= '<span style="vertical-align:middle;">' . esc_html__( 'WooCommerce - GSheetConnector', 'wc-gsheetconnector' ) . '</span>';

  wp_add_dashboard_widget(
   'wc_gsheetconnector_dashboard',
   $title,
   array( $this, 'wcgsc_summary_dashboard' )
 );
}

/**
 * Display the contents of the WooCommerce
 * GSheetConnector dashboard widget.
 *
 * Loads the dashboard widget template file.
 *
 * @since 1.0
 */
public function wcgsc_summary_dashboard() {
  include_once WC_GSHEETCONNECTOR_ROOT . '/includes/pages/wc-gsheetconnector-dashboard-widget.php';
}

/**
 * Verify the Google Sheets integration access code via AJAX.
 *
 * Validates the provided access code, stores it in the
 * database, initiates Google authentication, and updates
 * the integration verification status.
 *
 * @since 1.0
 *
 * @param string $Code Google authorization code.
 *
 * @return void Sends a JSON success or error response.
 */
public function wcgsc_verify_integration($Code = "") {
			// nonce check
 check_ajax_referer( 'wcgsc-ajax-nonce', 'security' );

 if ( ! current_user_can( 'manage_options' ) ) {
  wp_send_json_error( 'Unauthorized', 403 );
 }

 /* validate and sanitize incoming data */
 if ( isset( $_POST['code'] ) ) {
  $Code = sanitize_text_field( wp_unslash( $_POST['code'] ) );
} else {
  wp_send_json_error( 'Missing code.' );
  return;
}

if ( ! empty( $Code ) ) {
  update_option( 'wcgsc_access_code', $Code );
} else {
  wp_send_json_error( 'Empty code.' );
  return;
}

if ( get_option( 'wcgsc_access_code' ) !== '' ) {
  $file = WC_GSHEETCONNECTOR_ROOT . '/lib/google-sheets.php';

  if ( file_exists( $file ) ) {
   include_once $file;
   GSCWOO_googlesheet::preauth( get_option( 'wcgsc_access_code' ) );
   update_option( 'wcgsc_manual_setting', '0' );
   wp_send_json_success();
 } else {
   wp_send_json_error( 'Required file missing.' );
 }
} else {
  update_option( 'wcgsc_verify', 'invalid' );
  wp_send_json_error();
}
}

/**
 * Deactivate the Google Sheets integration via AJAX.
 *
 * Removes stored authentication tokens, feed data,
 * and integration settings from the WordPress database.
 *
 * @since 1.2
 *
 * @return void Sends a JSON success or error response.
 */
public function wcgsc_deactivate_integration() {
		// nonce check
 check_ajax_referer( 'wcgsc-ajax-nonce', 'security' );

 if ( ! current_user_can( 'manage_options' ) ) {
  wp_send_json_error( 'Unauthorized', 403 );
 }

 if ( get_option( 'wcgsc_token' ) !== '' ) {
  delete_option( 'wcgsc_feeds' );
  delete_option( 'wcgsc_sheetId' );
  delete_option( 'wcgsc_token' );
  delete_option( 'wcgsc_access_code' );
  delete_option( 'wcgsc_verify' );
  delete_transient( 'wcgsc_email_account_cache' );

  wp_send_json_success();
} else {
  wp_send_json_error();
}
}

/**
 * Synchronize the connected Google account and
 * fetch available Google Sheets.
 *
 * Retrieves spreadsheet information from the connected
 * Google account and stores the sheet list in plugin settings.
 *
 * @since 1.0
 *
 * @return void
 */
public function wcgsc_sync_google_account() {
 $return_ajax = false;
 $init = '';

 if ( isset( $_POST['isajax'] ) && sanitize_text_field( wp_unslash( $_POST['isajax'] ) ) === 'yes' ) {
				// nonce check
  check_ajax_referer( 'wcgsc-ajax-nonce', 'security' );

  if ( ! current_user_can( 'manage_options' ) ) {
   wp_send_json_error( 'Unauthorized', 403 );
  }

  if ( isset( $_POST['isinit'] ) ) {
   $init = sanitize_text_field( wp_unslash( $_POST['isinit'] ) );
 }

 $return_ajax = true;
}

$file = WC_GSHEETCONNECTOR_ROOT . '/lib/google-sheets.php';
if ( file_exists( $file ) ) {
  include_once $file;
}

if ( class_exists( 'GSCWOO_googlesheet' ) ) {
  $doc = new GSCWOO_googlesheet();
  $doc->auth();
} else {
  wp_send_json_error( 'Google Sheet class not found.' );
  return;
}

			// Get all spreadsheets
$spreadsheetFeed = $doc->get_spreadsheets();
$sheet_array = array();

foreach ( $spreadsheetFeed as $sheetfeeds ) {
  $sheetId   = $sheetfeeds['id'];
  $sheetname = $sheetfeeds['title'];

  $sheet_array[ $sheetId ] = array(
   'sheet_name' => $sheetname,
 );
}

update_option( 'wcgsc_sheet_feeds', $sheet_array );

$cdate = current_time( 'Y-m-d H:i:s' ); // date + time in WP local timezone
update_option( 'wcgsc_sheet_fetch_date', $cdate );
if ( $return_ajax === true ) {
  if ( $init === 'yes' ) {
   wp_send_json_success( array( 'success' => 'yes' ) );
 } else {
   wp_send_json_success( array( 'success' => 'no' ) );
 }
}
}

/**
 * Retrieve the list of sheet tabs for a selected
 * Google Spreadsheet via AJAX.
 *
 * Generates HTML option elements containing the
 * available worksheet tabs for the selected spreadsheet.
 *
 * @since 1.0
 *
 * @return void Sends a JSON success response with tab options.
 */
public function wcgsc_get_tab_list_by_sheetname() {
		// nonce check
 check_ajax_referer( 'wcgsc-ajax-nonce', 'security' );

 if ( ! current_user_can( 'manage_options' ) ) {
  wp_send_json_error( 'Unauthorized', 403 );
 }

 if ( ! isset( $_POST['sheetname'] ) ) {
  wp_send_json_error( 'Missing sheetname' );
  return;
}

$sheetname = sanitize_text_field( wp_unslash( $_POST['sheetname'] ) );
$sheet_data = get_option( 'wcgsc_feeds' );
$html = '';
$tablist = '';

if ( ! empty( $sheet_data ) && array_key_exists( $sheetname, $sheet_data ) ) {
  $tablist = $sheet_data[ $sheetname ];
}

if ( ! empty( $tablist ) ) {
  $html = '<option value="">' . esc_html__( "Select", "wc-gsheetconnector" ) . '</option>';
  foreach ( $tablist as $tab ) {
   $html .= '<option value="' . esc_attr( $tab ) . '">' . esc_html( $tab ) . '</option>';
 }
}

wp_send_json_success( $html );
}

/**
 * Migrate legacy plugin option names to the new option keys.
 *
 * Copies existing values from old option names to their
 * corresponding new option names when the new option
 * does not already exist. After a successful migration,
 * old options are removed and a migration flag is stored
 * to prevent duplicate migrations.
 *
 * @since 1.0.0
 *
 * @return void
 */
private function maybe_migrate_old_options() {
  $option_map = array(
    'gs_woo_token'        => 'wcgsc_token',
    'gs_woo_feeds'        => 'wcgsc_feeds',
    'gs_woo_sheetId'      => 'wcgsc_sheetId',
    'gs_woo_access_code'  => 'wcgsc_access_code',
    'gs_woo_verify'       => 'wcgsc_verify',
    'gs_woo_settings'     => 'wcgsc_settings',
    'gs_woo_sheet_feeds'  => 'wcgsc_sheet_feeds',
    'woogsc_api_free_creds'  => 'wcgsc_api_free_creds',
    'gscwc_order_states' => 'wcgsc_order_states',
    'wpgs_email_account' => 'wcgsc_email_account',
    'is_new_client_secret_woogsc'  => 'is_new_client_secret_wcgsc',
  );

  foreach ( $option_map as $old_key => $new_key ) {
            // Only copy if new key doesn't already exist
    if ( get_option( $new_key, '' ) === '' ) {
      $old_value = get_option( $old_key );
      if ( $old_value !== false ) {
        update_option( $new_key, $old_value );
      }
    }
  }

        // Optional: delete old options after successful migration
  foreach ( array_keys( $option_map ) as $old_key ) {
    delete_option( $old_key );
  }

        // Set migration complete flag
  update_option( 'wcgsc_options_migrated', 1 );
}

/**
* Ensure the error-logs table exists for upgraded installs.
*
* Creates the wp_wcgsc_error_logs table (via the shared
* wcgsc_create_error_log_table() helper) for sites that were
* upgraded before the table was introduced, then flags the
* migration as complete to avoid repeating it.
*
* @return void
*/
private function maybe_migrate_wcgsc_debug_log()
{
  // Delegated to the shared wcgsc_create_error_log_table() in error-logs.php (dbDelta is idempotent).
  wcgsc_create_error_log_table();

  update_option('wcgsc_debug_migrated_pro', 1);
}
}

$wcgsc_gsheetconnector_processes = new wc_gsheetconnector_processes();
