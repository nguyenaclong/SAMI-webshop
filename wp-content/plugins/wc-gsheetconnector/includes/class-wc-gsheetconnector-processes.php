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
	}

    /**
    * AJAX function - clear log file for system status tab
    * @since 2.1
    */
    public function wcgsc_log_systeminfo() {
        // nonce check
    	check_ajax_referer( 'wcgsc-ajax-nonce', 'security' );

        // Initialize WP_Filesystem
    	if ( ! function_exists( 'WP_Filesystem' ) ) {
    		require_once ABSPATH . 'wp-admin/includes/file.php';
    	}
    	global $wp_filesystem;
    	WP_Filesystem();

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
     * AJAX function - clear log file
     * @since 1.0
     */
    public function wcgsc_clear_logs() {
	    // nonce check
    	check_ajax_referer( 'wcgsc-ajax-nonce', 'security' );

    	$wcexistDebugFile = get_option('wcfgs_debug_log_file');
    	$clear_file_msg = '';

    	if ( ! empty( $wcexistDebugFile ) && file_exists( $wcexistDebugFile ) ) {

	        // Load WP_Filesystem API
    		if ( ! function_exists( 'WP_Filesystem' ) ) {
    			require_once ABSPATH . 'wp-admin/includes/file.php';
    		}
    		global $wp_filesystem;
    		WP_Filesystem();

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
	 * Add widget to the dashboard
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
	 * Display widget conetents
	 *
	 * @since 1.0
	 */
	public function wcgsc_summary_dashboard() {
		include_once WC_GSHEETCONNECTOR_ROOT . '/includes/pages/wc-gsheetconnector-dashboard-widget.php';
	}

    /**
     * AJAX function - verifies the token
     *
     * @since 1.0
     */
    public function wcgsc_verify_integration($Code = "") {
			// nonce check
    	check_ajax_referer( 'wcgsc-ajax-nonce', 'security' );

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
     * AJAX function - deactivate activation
     * @since 1.2
     */
    public function wcgsc_deactivate_integration() {
		// nonce check
    	check_ajax_referer( 'wcgsc-ajax-nonce', 'security' );

    	if ( get_option( 'wcgsc_token' ) !== '' ) {
    		delete_option( 'wcgsc_feeds' );
    		delete_option( 'wcgsc_sheetId' );
    		delete_option( 'wcgsc_token' );
    		delete_option( 'wcgsc_access_code' );
    		delete_option( 'wcgsc_verify' );

    		wp_send_json_success();
    	} else {
    		wp_send_json_error();
    	}
    }

    /**
     * Function - sync with google account to fetch sheet and tab name
     * @since 1.0
     */
    public function wcgsc_sync_google_account() {
    	$return_ajax = false;
    	$init = '';

    	if ( isset( $_POST['isajax'] ) && sanitize_text_field( wp_unslash( $_POST['isajax'] ) ) === 'yes' ) {
				// nonce check
    		check_ajax_referer( 'wcgsc-ajax-nonce', 'security' );

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

    	if ( $return_ajax === true ) {
    		if ( $init === 'yes' ) {
    			wp_send_json_success( array( 'success' => 'yes' ) );
    		} else {
    			wp_send_json_success( array( 'success' => 'no' ) );
    		}
    	}
    }


    /**
     * AJAX function - Fetch tab list by sheet name
     * @since 1.0
     */
    public function wcgsc_get_tab_list_by_sheetname() {
		// nonce check
    	check_ajax_referer( 'wcgsc-ajax-nonce', 'security' );

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
}

$wcgsc_gsheetconnector_processes = new wc_gsheetconnector_processes();
