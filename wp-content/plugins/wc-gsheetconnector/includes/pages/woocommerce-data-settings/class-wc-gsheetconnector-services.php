<?php

/*
 * Service class for woocommerce google sheet connector pro
 * @since 1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * wc_gsheetconnector_Service class
 *
 * @since 1.0
 */
class wc_gsheetconnector_Service
{

	public $class_name = 'wc_gsheetconnector_Service';
	public $status_and_sheets;
	public $sheet_headers;
	public $sheet_headers_pro;
	public $product_headers_pro;
	public $customer_headers_pro;
	public $product_variations_headers_pro;
	public $coupons_headers_pro;
	public $subscriptions_headers_pro;
	public $_gfgsc_googlesheet;

	public function __construct()
	{

		$this->status_and_sheets = array(
			'wc-pending' => 'Pending Orders',
			'wc-processing' => 'Processing Orders',
			'wc-on-hold' => 'On Hold Orders',
			'wc-failed' => 'Failed Orders',
			'wc-completed' => 'Completed Orders',
			'wc-cancelled' => 'Cancelled Orders',
			'wc-refunded' => 'Refunded Orders',
			'wc-trash' => 'Trashed Orders',
		);

		
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook kept for backward compatibility.
		$this->status_and_sheets = apply_filters( 'poolexpress_status_and_sheets', $this->status_and_sheets );

		$class_name = $this->class_name;
		$order_class_name = 'WC_Order';

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook kept for backward compatibility.
		$order_id_column_name = apply_filters('poolexpress_order_id_column_name', 'Order Id');

		$this->sheet_headers = array(
			$order_id_column_name => array(
				'class' => $order_class_name,
				'function_name' => 'get_id',
			),
			'Status' => array(
				'class' => $order_class_name,
				'function_name' => 'get_status',
			),
			'Product name(QTY)(SKU)' => array(
				'class' => $class_name,
				'function_name' => 'extract_product_qty_sku',
			),
			'Tax Total' => array(
				'class' => $order_class_name,
				'function_name' => 'get_total_tax',
			),
			'Order Total' => array(
				'class' => $order_class_name,
				'function_name' => 'get_total',
			),
			'Payment Method' => array(
				'class' => $order_class_name,
				'function_name' => 'get_payment_method_title',
			),
			'Billing First name' => array(
				'class' => $order_class_name,
				'function_name' => 'get_billing_first_name',
			),
			'Billing Last Name' => array(
				'class' => $order_class_name,
				'function_name' => 'get_billing_last_name',
			),
			'Billing Address 1' => array(
				'class' => $order_class_name,
				'function_name' => 'get_billing_address_1',
			),
			'Billing Address 2' => array(
				'class' => $order_class_name,
				'function_name' => 'get_billing_address_2',
			),
			'Billing City' => array(
				'class' => $order_class_name,
				'function_name' => 'get_billing_city',
			),
			'Billing State' => array(
				'class' => $order_class_name,
				'function_name' => 'get_billing_state',
			),
			'Billing Postcode' => array(
				'class' => $order_class_name,
				'function_name' => 'get_billing_postcode',
			),
			'Billing Country' => array(
				'class' => $order_class_name,
				'function_name' => 'get_billing_country',
			),
			'Billing Company Name' => array(
				'class' => $order_class_name,
				'function_name' => 'get_billing_company',
			),
			'Shipping First Name' => array(
				'class' => $order_class_name,
				'function_name' => 'get_shipping_first_name',
			),
			'Shipping Last Name' => array(
				'class' => $order_class_name,
				'function_name' => 'get_shipping_last_name',
			),
			'Shipping Address 1' => array(
				'class' => $order_class_name,
				'function_name' => 'get_shipping_address_1',
			),
			'Shipping Address 2' => array(
				'class' => $order_class_name,
				'function_name' => 'get_shipping_address_2',
			),
			'Shipping City' => array(
				'class' => $order_class_name,
				'function_name' => 'get_shipping_city',
			),
			'Shipping State' => array(
				'class' => $order_class_name,
				'function_name' => 'get_shipping_state',
			),
			'Shipping Postcode' => array(
				'class' => $order_class_name,
				'function_name' => 'get_shipping_postcode',
			),
			'Shipping Country' => array(
				'class' => $order_class_name,
				'function_name' => 'get_shipping_country',
			),
			'Shipping Method Title' => array(
				'class' => $order_class_name,
				'function_name' => 'get_shipping_to_display',
			),
			'Shipping Company Name' => array(
				'class' => $order_class_name,
				'function_name' => 'get_shipping_company',
			),
			'Coupons Codes' => array(
				'class' => $order_class_name,
				'function_name' => 'get_coupon_codes',
			),
			'Email' => array(
				'class' => $order_class_name,
				'function_name' => 'get_billing_email',
			),
			'Phone' => array(
				'class' => $order_class_name,
				'function_name' => 'get_billing_phone',
			),
			'Customer Note' => array(
				'class' => $order_class_name,
				'function_name' => 'get_customer_note',
			),
			'Created Date' => array(
				'class' => $order_class_name,
				'function_name' => 'get_date_created',
			),

		);

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook kept for backward compatibility.
		$this->sheet_headers = apply_filters('poolexpress_sheet_headers', $this->sheet_headers);

		$this->sheet_headers_pro = array(
			'Currency',
			'Product ID',
			'Product Image',
			'Product Meta',
			'Product Variation',
			'SKU',
			'Product Base Price',
			'Product Total',
			'Product Categories',
			'Partial Refund Amount',
			'Order URL',
			'Status Updated Date',
			'Transaction ID',
			'Customer ID',
			'Order Completion Date',
			'Order Paid Date',
			'Order Notes',
			'Order Status',
			'Discount Total',
			'Discount Tax',
			'Shipping Total',
			'Shipping Tax',
			'Cart Tax',
		);
		$this->product_headers_pro = array(
			'Product ID',
			'Product Name',
			'Product Status',
			'Product Short Description',
			'Product Description',
			'Product Slug',
			'Product Link',
			'Product Categories',
			'Product Type',
			'Product Image',
			'Product Tags',
			'Product Attribute',
			'Product Regular Price',
			'Product Sale Price Dates From',
			'Product Sale Price Dates To',
			'Product Stock',
			'Product Stock Status',
			'Product Weight',
			'Product Height',
			'Product Width',
			'Product Length',
			'Product Total Sale',
			'Product Purchase Note',
			'Product Dimensions',
			'Product Sold Individually',
			'Manage Product Stock',
			'Product Shipping Class',
			'Product Tax Status',
			'Product Tax Class',
			'Virtual Product',
			'Date Created',
			'Date Modified',
			'Product Downloadable',
			'Product Downloadable Files',
			'Product Downloadable File Names',
			'Product Download Limit',
			'Product Download Expiry',
			'Product Allow Backorders',
			'Product Variation SKU',
			'Product Variation Image',
			'Product Variation Regular Price',
			'Product Variation Sale Price',
			'Product Variation Sale Price Dates From',
			'Product Variation Sale Price Dates To',
			'Product Variation Stock',
			'Product Variation Stock Status',
			'Product Variation Weight',
			'Product Variation Dimensions',
			'Product Variation Manage Product Stock',
			'Product Variation Shipping Class',
			'Virtual Product Variation',
			'Product Variation Description',
			'Downloadable Product Variation',
			'Product Variation Downloadable Files',
			'Product Variation Download Limit',
			'Product Variation Download Expiry',
			'Grouped Products Ids',
			'Product Variation Allow Backorders',
			'Product Variation Total Sale',
			'External Product Button Text',
			'External Product Link',
		);
		$this->customer_headers_pro = array(

			'Customer ID',
			'Customer Username',
			'Customer Role',
			'Customer FirstName',
			'Customer LastName',
			'Customer Nickname',
			'Customer Display Name',
			'Customer EmailID',
			'Customer Website',
			'Customer Biographical Info',
			'Customer Profile Image',
			'Billing FirstName',
			'Billing LastName',
			'Billing Company Name',
			'Billing Address1',
			'Billing Address2',
			'Billing City',
			'Billing Postcode / ZIP',
			'Billing Country',
			'Billing State',
			'Billing Phone Number',
			'Billing EmailID',
			'Shipping FirstName',
			'Shipping LastName',
			'Shipping Company Name',
			'Shipping Address1',
			'Shipping Address2',
			'Shipping City',
			'Shipping Postcode / ZIP',
			'Shipping Country ',
			'Shipping State',

		);
		$this->product_variations_headers_pro = array(

			'Product ID',
			'Product Variation Name',
			'Product Variation Sale Price',
			'Product Variation Stock Status',
			'Product Variation Shipping Class',
			'Product Variation Downloadable Files',
			'Product Variation Total Sale',
			'Variation ID',
			'Product Variation SKU',
			'Product Variation Sales Price Dates From',
			'Product Variation Weight',
			'Virtual Product Variation',
			'Product Variation Download Limit',
			'Product Name',
			'Product Variation Image',
			'Product Variation Sales Price Dates To',
			'Product Variation Dimensions',
			'Product Variation Description',
			'Product Variation Download Expiry',
			'Product Type',
			'Product Variation Regular Price',
			'Product Variation Stock',
			'Product Variation Manage Product Stock',
			'Downloadable Product Variation',
			'Product Variation Allow Backorders',
		);

		$this->coupons_headers_pro = array(
			'Coupon ID',
			'Coupon Code',
			'Description',
			'Discount Type',
			'Coupon Amount',
			'Status',
			'Allow Free Shipping',
			'Coupon Expiry Date',
			'Minimum Spend',
			'Maximum Spend',
			'Individual Use Only',
			'Exclude Sale Items',
			'Products',
			'Exclude Products',
			'Applied Product Categories',
			'Exclude Categories',
			'Allowed Emails',
			'Usage Limit Per Coupon',
			'Limit Usage To X Items',
			'Usage Limit Per User',

		);

		$this->subscriptions_headers_pro = array(
			'Subscription ID',
			'Status',
			'Customer ID',
			'Customer Name',
			'Start Date',
			'End Date',
			'Billing Period',
			'Billing Interval',
			'Trial End Date',
			'Next Payment Date',
			'Renewal Enabled',
			'Total Order Count',
			'Total Shipped Items',
			'Total Earned',
			'Total Refunded',
			'Tax Amount',
			'Coupon Amount',
			'Product Names',
			'Product IDs',
			'Variation Names',
			'Variation IDs',
			'Cancellation Reason',
			'Notes',
			'Payment Method',
			'Shipping Address',
			'Coupon Code',
			'Switched Subscriptions',
			'New Subscriptions',
			'Number of Resubscribes',
			'Number of Renewals',
			'Subscriptions Ended',
			'Cancellations',
			'Signup Totals',
			'Resubscribe Totals',
			'Renewal Totals',
			'Switch Totals',

		);

		add_filter('gscwoo_tab_headers', array($this, 'add_status_header_in_all_orders'), 10, 2);
	}

	public function init()
	{
		try {
		      // notification slider
			add_action('wp_ajax_wcgsc_dismiss_notice', array($this, 'wcgsc_dismiss_notice_callback'));
			add_action('wp_ajax_wcgsc_snooze_notice', array($this, 'wcgsc_snooze_notice_callback'));

            // pro notice
			add_action('wp_ajax_wcgsc_dismiss_pro_notice', array($this, 'wcgsc_dismiss_pro_notice'));

            // unistall plugin settings
			add_action('wp_ajax_wcgsc_save_uninstall_settings', array($this, 'wcgsc_save_uninstall_settings'));

			// save woocommerce data settings
			add_action('admin_init', array($this, 'execute_post_data'));
			// order status changed
			add_action('woocommerce_order_status_changed', array($this, 'woocommerce_order_status_changed'), 10, 4);
			add_action('woocommerce_process_shop_order_meta', array($this, 'woocommerce_process_shop_order_meta'), 1000, 2);
			
			add_action('wp_trash_post', array($this, 'wp_trash_post'), 10, 1);
			add_action('transition_post_status', array($this, 'transition_post_status'), 10, 3);

			add_filter('gscwoo_row_values', array($this, 'change_status_to_uppercase'), 10, 2);

		} catch (Exception $e) {
			wc_gsheetconnector_utility::gs_debug_log($e->getMessage());
			return;
		}
	}

/**
* Handle AJAX request to dismiss the PRO notice.
*
* This function:
* - Verifies the AJAX nonce for security.
* - Sets a browser cookie to remember that the notice is dismissed.
* - Cookie is valid for 7 days.
* - Returns a JSON success or error response.
*
* @return void
*/
public function wcgsc_dismiss_pro_notice()
{

	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

	if (! wp_verify_nonce($nonce, 'wcgsc-ajax-nonce')) {
		wp_send_json_error('Invalid nonce');
	}

	setcookie(
		'wcgsc_pro_notice_dismissed',
		'1',
		time() + (7 * 24 * 60 * 60),
		COOKIEPATH,
		COOKIE_DOMAIN
	);

	wp_send_json_success();
}

/**
 * Dismiss an admin notice via AJAX.
 *
 * Verifies the AJAX nonce, validates the notice key,
 * and stores the dismissed status in the WordPress
 * options table to prevent the notice from being
 * displayed again.
 *
 * @since 1.0.0
 *
 * @return void Sends a JSON success or error response.
 */
public function wcgsc_dismiss_notice_callback()
{

	check_ajax_referer('wcgsc-ajax-nonce', 'security');

	if (! isset($_POST['key'])) {
		wp_send_json_error('Missing key');
		return;
	}

	$key = sanitize_text_field(wp_unslash($_POST['key']));
	update_option('wcgsc_free_notice_' . $key, 'dismissed');
	wp_send_json_success();
}

/**
 * Snooze an admin notice via AJAX.
 *
 * Verifies the AJAX nonce, validates the notice key,
 * and stores the current timestamp in the WordPress
 * options table to temporarily hide the notice until
 * the snooze period expires.
 *
 * @since 1.0.0
 *
 * @return void Sends a JSON success or error response.
 */
public function wcgsc_snooze_notice_callback()
{

	check_ajax_referer('wcgsc-ajax-nonce', 'security');

	if (! isset($_POST['key'])) {
		wp_send_json_error('Missing key');
		return;
	}

	$key = sanitize_text_field(wp_unslash($_POST['key']));
	update_option('wcgsc_free_notice_' . $key . '_time', time());
	wp_send_json_success();
}


/**
 * Save plugin uninstall settings via AJAX.
 *
 * This function handles the uninstall settings request from the admin panel,
 * validates the request using a nonce, checks user permissions, sanitizes
 * the submitted value, saves it to the WordPress options table, and returns
 * a JSON response.
 *
 * @since 1.0.0
 *
 * @return void Sends a JSON success or error response and exits.
 */
public function wcgsc_save_uninstall_settings() {

    // Verify AJAX nonce to protect against CSRF attacks.
	check_ajax_referer( 'wcgsc-uninstall-ajax-nonce', 'security' );

    // Ensure only administrators can update uninstall settings.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Permission denied' );
	}

    /**
     * Get uninstall setting value from AJAX request.
     *
     * Expected values: 'Yes' or 'No'.
     * Defaults to 'No' if no value is provided.
     */
      // Get uninstall setting value
    $wcgsc_value = isset($_POST['gs_woo_unistall_settings'])
    ? intval(wp_unslash($_POST['gs_woo_unistall_settings']))
    : 0;

    // Convert value into Yes/No
    $wcgsc_setting = ($wcgsc_value === 1) ? 'Yes' : 'No';

    // Save the uninstall setting in the WordPress options table.
    update_option( 'wcgsc_unistall_plugin_settings', $wcgsc_setting );

    // Return a success response to the AJAX request.
    wp_send_json_success();
}

/**
 * Process WooCommerce order meta updates and trigger
 * order status synchronization with Google Sheets.
 *
 * @param int      $order_id WooCommerce order ID.
 * @param WC_Order $order    Order object.
 */
public function woocommerce_process_shop_order_meta($order_id, $order)
{
	try {
		$order = new WC_Order($order_id);
		$current_status = $order->get_status();
		$this->woocommerce_order_status_changed($order_id, $current_status, $current_status, $order);
	} catch (Exception $e) {
		wc_gsheetconnector_utility::gs_debug_log($e->getMessage());
		
	}
}

/**
 * Handle order status transitions when a trashed order
 * is restored and sync the status change.
 *
 * @param string  $new_status New post status.
 * @param string  $old_status Previous post status.
 * @param WP_Post $post       Post object.
 */
public function transition_post_status($new_status, $old_status, $post) {
	try {
		global $post_type;

		if ( $post_type !== 'shop_order' ) {
			return;
		}

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe usage, verifying action during trash restore
		$action = isset($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : '';

		if ($action !== 'untrash') {
			return;
		}

		if ( $old_status === 'trash' || $old_status === 'wc-trash' ) {
			$order_id = $post->ID;
			$order = wc_get_order($order_id);

			$old_status = str_replace('wc-', '', $old_status);
			$new_status = str_replace('wc-', '', $new_status);

			$this->woocommerce_order_status_changed($order_id, $old_status, $new_status, $order);
		}
	} catch ( Exception $e ) {
		wc_gsheetconnector_utility::gs_debug_log($e->getMessage());
		return;
		
	}
}

/**
 * Handle WooCommerce order trash action and trigger
 * order status synchronization.
 *
 * @param int $order_id WooCommerce order ID.
 */
public function wp_trash_post($order_id)
{
	try {
		if (get_post_type($order_id) !== 'shop_order') {
			return;
		}

		$order = wc_get_order($order_id);

		$new_status = 'trash';
		$current_status = $order->get_status();

		$this->woocommerce_order_status_changed($order_id, $current_status, $new_status, $order);
	} catch (Exception $e) {
		wc_gsheetconnector_utility::gs_debug_log($e->getMessage());
		return;
		
	}
}

/**
 * Display success admin notice after WooCommerce
 * Google Sheet settings are saved successfully.
 */
public function woocommerce_success_notice_free()
{
	$success_msg = wc_gsheetconnector_utility::instance()->admin_notice(array(
		'type' => 'update',
		'message' => __('WooCommerce Data Settings saved successfully.', 'wc-gsheetconnector')
	));
	
	echo wp_kses_post( $success_msg );
}

/**
 * Process and save plugin settings submitted from
 * the WooCommerce Google Sheet configuration page.
 *
 * Validates nonce, user permissions, selected sheet,
 * and configured order statuses before updating settings.
 */
public function execute_post_data() {
	try {
		if (isset($_POST['wcgsc-save-btn'])) {
				// Check if the nonce is set.
			if (!isset($_POST['wcgsc-nonce'])) {
				return;
			}

				// Verify the nonce.
			$nonce = sanitize_text_field(wp_unslash($_POST['wcgsc-nonce']));
			if (!wp_verify_nonce($nonce, 'wcgsc-nonce')) {
				return;
			}

				// Administrator or super admin check role.
			$current_role = wc_gsheetconnector_utility::instance()->get_current_user_role();
			if (!current_user_can('manage_options')) {
				return;
			}

				// Fetch dropdown fields
			$selected_sheet_id = isset($_POST['wcgsc-sheet-id']) ? sanitize_text_field(wp_unslash($_POST['wcgsc-sheet-id'])) : '';

			if ($selected_sheet_id !== '') {
				update_option('wcgsc_settings', $selected_sheet_id);

					// Get Spreadsheet name from ID
				$sheet_data = get_option('wcgsc_feeds');
					// $gscwoo_spreadsheetName = isset($sheet_data[$selected_sheet_id]['sheet_name']) ? $sheet_data[$selected_sheet_id]['sheet_name'] : '';
				if (is_array($sheet_data) && isset($sheet_data[$selected_sheet_id]['sheet_name'])) {
					$gscwoo_spreadsheetName = $sheet_data[$selected_sheet_id]['sheet_name'];
				} else {
					$gscwoo_spreadsheetName = '';
				}

					// Get order states and sanitize
				$order_states = isset($_POST['wcgsc_order_state']) ? array_map('sanitize_text_field', wp_unslash($_POST['wcgsc_order_state'])) : array();
				update_option('wcgsc_order_states', $order_states);

				if (!empty($order_states)) {
						// Create or remove tabs
					include_once WC_GSHEETCONNECTOR_ROOT . '/lib/google-sheets.php';
					$gscwoo_client = new GSCWOO_googlesheet();
					$gscwoo_client->auth();
					$gscwoo_client->setSpreadsheetId($selected_sheet_id);

					$this->create_remove_sheet_and_headers($selected_sheet_id, $order_states);
					add_action('admin_notices', array($this, 'woocommerce_success_notice_free'));
				} else {
					add_action('admin_notices', array($this, 'error_message'));
				}
			}
		} else {
			add_action('admin_notices', array($this, 'error_message'));
		}
	} catch (Exception $e) {
		wc_gsheetconnector_utility::gs_debug_log($e->getMessage());
		return;
		
	}
}

/**
 * Display an error admin notice when required
 * Google Sheet settings are not configured.
 */
public function error_message() {
	$page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

	if (is_admin() && $page === 'woocommerce-gsheet-config'){
			// Add nonce check before processing POST
		if (isset($_POST['wcgsc-save-btn']) && isset($_POST['wcgsc-nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wcgsc-nonce'])), 'wcgsc-nonce')) {
			$plugin_error = wc_gsheetconnector_utility::instance()->admin_notice(
				array(
					'type'    => 'error',
					'message' => __('Please select Google Sheet Name and Check Atleast on Google Sheet Tab !', 'wc-gsheetconnector'),
				)
			);
				// Escape output safely using wp_kses_post for HTML markup from admin_notice
			echo wp_kses_post($plugin_error);
		}
	}
}

/**
 * Create, remove, and update Google Sheet tabs
 * based on selected WooCommerce order statuses.
 *
 * Also adds column headers to newly created or
 * existing sheet tabs.
 *
 * @param string $spreadsheet_id Google Spreadsheet ID.
 * @param array  $order_states   Selected WooCommerce order statuses.
 */
public function create_remove_sheet_and_headers($spreadsheet_id, $order_states)
{

	try {
		$gscwoo_client = $this->get_googlesheet_object();
		$sheet_headers = $this->sheet_headers;

		$status_and_sheets = $this->status_and_sheets;

		$available_sheets = $gscwoo_client->get_sheet_tabs($spreadsheet_id);

		$removable_sheets = array();
		$add_sheets = array();
		$working_order_states_data = array();

		foreach ($status_and_sheets as $wc_status => $associated_tab) {

			if (in_array($wc_status, $order_states)) {

				if (!in_array($associated_tab, $available_sheets)) {
					$add_sheets[] = $associated_tab;
				}

				$working_order_states_data[$wc_status] = $associated_tab;
			} elseif (in_array($associated_tab, $available_sheets)) {
				$sheet_id = array_search($associated_tab, $available_sheets);
				$removable_sheets[$sheet_id] = $associated_tab;
			}
		}

		$sheet_update_requests = array();

		if ($add_sheets) {
			foreach ($add_sheets as $sheetName) {
				$sheet_update_requests[] = array(
					'addSheet' => array(
						'properties' => array(
							'title' => $sheetName,
						),
					),
				);
			}
		}

		if ($removable_sheets) {
			foreach ($removable_sheets as $sheet_id => $sheetName) {
				$sheet_update_requests[] = array(
					'deleteSheet' => array(
						'sheetId' => (int) $sheet_id,
					),
				);
			}
		}

		if ($sheet_update_requests) {
			$gscwoo_client->perform_sheet_tab_updates($spreadsheet_id, $sheet_update_requests);
		}

		/* NOW SET HEADERS */

		foreach ($working_order_states_data as $wc_status => $associated_tab) {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook kept for backward compatibility.
			$headers = apply_filters('gscwoo_tab_headers', $sheet_headers, $wc_status);
			$header_names = array_keys($headers);
			$gscwoo_client->add_row_to_sheet($spreadsheet_id, $associated_tab, $header_names, '', true);
		}
	} catch (Exception $e) {
		wc_gsheetconnector_utility::gs_debug_log($e->getMessage());
		return;
		
	}
}

/**
 * Remove the Status column from sheet headers
 * for all tabs except the "all" orders tab.
 *
 * @param array  $headers   Sheet header columns.
 * @param string $wc_status WooCommerce order status.
 *
 * @return array Modified headers array.
 */
public function add_status_header_in_all_orders($headers, $wc_status)
{
	try {
		if ($wc_status != 'all') {
			unset($headers['Status']);
		}

		return $headers;
	} catch (Exception $e) {
		wc_gsheetconnector_utility::gs_debug_log($e->getMessage());
		return;
		
	}
}

/**
 * Format the Status column value by converting
 * the first letter of each word to uppercase.
 *
 * @param string $header_value Cell value.
 * @param string $cell_name    Header column name.
 *
 * @return string Formatted header value.
 */
public function change_status_to_uppercase($header_value, $cell_name)
{

	try {
		if ($cell_name == 'Status') {
			$header_value = ucwords($header_value);
		}

		return $header_value;
	} catch (Exception $e) {
		wc_gsheetconnector_utility::gs_debug_log($e->getMessage());
		return;
		
	}
}

/**
 * Create an ordered array of WooCommerce order data
 * matching the Google Sheet header structure.
 *
 * Retrieves values from order methods, custom callbacks,
 * and filters, then prepares the final row data for
 * insertion or update in Google Sheets.
 *
 * @param WC_Order $order         WooCommerce order object.
 * @param array    $header_cells  Sheet header columns.
 * @param string|bool $custom_status Optional custom status.
 *
 * @return array Prepared row data.
 */
public function create_save_array($order, $header_cells, $custom_status = false)
{

	try {
		$create_value_array = array();
		$send_row_data = array();

		$order_data = $order->get_data();
		$wc_status = $order->get_status();

		if ($custom_status) {
			$wc_status = $custom_status;
		}

		$sheet_headers = $this->sheet_headers;
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook kept for backward compatibility.
		$sheet_headers = apply_filters('gscwoo_tab_headers', $sheet_headers, $wc_status);

		foreach ($sheet_headers as $header_name => $header_data) {
			$class = $header_data['class'];
			$function_name = $header_data['function_name'];

			$header_value = $function_name;
			if ($class && class_exists($class)) {
				if (method_exists($class, $function_name)) {

					if ($class == 'WC_Order') {
						if ($function_name == "get_shipping_to_display") {
							$header_value = "";
								// Get the shipping methods
							$shipping_methods = $order->get_shipping_methods();
								// Loop through shipping methods and get the title
							if (!empty($shipping_methods)) {
								foreach ($shipping_methods as $shipping_method) {
									$shipping_method_title = $shipping_method->get_name();

									$clean_text = wp_strip_all_tags($shipping_method_title);
										// Decode HTML entities
									$clean_text = html_entity_decode($clean_text);

										// Remove special characters (if any remain)
									$header_value = preg_replace('/[^\w\s]/u', '', $clean_text);

								}
							}


						} else {
							$header_value = $order->$function_name();
						}

					} else {
						$header_value = $class::$function_name($order, $order_data);
					}
				}
			} elseif (function_exists($function_name)) {
				$header_value = $function_name($order, $order_data);
			}

			if (is_array($header_value)) {
				$header_value = implode(', ', $header_value);
			}
			if (is_a($header_value, 'WC_DateTime')) {
				$header_value = $header_value->date(get_option('date_format') . ' ' . get_option('time_format'));
			}
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook kept for backward compatibility.
			$create_value_array[$header_name] = apply_filters('gscwoo_row_values', $header_value, $header_name);

		}

		$entry_cells = $create_value_array;
		if ($entry_cells && $header_cells) {

			foreach ($header_cells as $index => $cellName) {

				if (isset($entry_cells[$cellName])) {
					$send_row_data[$index] = $entry_cells[$cellName];
				}
			}

			foreach ($header_cells as $index => $cellName) {
				if (!isset($send_row_data[$index])) {
					$send_row_data[$index] = '';
				}
			}
		}

		return $send_row_data;

	} catch (Exception $e) {
		wc_gsheetconnector_utility::gs_debug_log($e->getMessage());
		return;
		
	}
}

/**
 * Handle WooCommerce order status changes and synchronize
 * order data with the configured Google Sheet tabs.
 *
 * Adds, updates, or removes rows from status-specific tabs
 * and updates the "All Orders" tab when enabled.
 *
 * @param int      $order_id   WooCommerce order ID.
 * @param string   $old_status Previous order status.
 * @param string   $new_status New order status.
 * @param WC_Order $order      WooCommerce order object.
 *
 * @return void
 */
public function woocommerce_order_status_changed($order_id, $old_status, $new_status, $order)
{

	try {
		$new_wc_status = 'wc-' . $new_status;
		$old_wc_status = 'wc-' . $old_status;

		$adding_sheet = '';
		$removing_sheet = '';

		$data_update_only = false;
		if ($new_wc_status == $old_wc_status) {
			$data_update_only = true;
		}

		$gscwoo_client = $this->get_googlesheet_object();
		$spreadsheet_id = get_option('wcgsc_settings');

		if (!$spreadsheet_id) {
			return;
		}


		if (isset($this->status_and_sheets[$new_wc_status]) && $this->status_and_sheets[$new_wc_status] != '') {
			$adding_sheet = $this->status_and_sheets[$new_wc_status];
		}

		if (isset($this->status_and_sheets[$old_wc_status]) && $this->status_and_sheets[$old_wc_status] != '') {
			$removing_sheet = $this->status_and_sheets[$old_wc_status];
		}



		$header_row = $gscwoo_client->get_header_row($spreadsheet_id, $adding_sheet);
		$insert_row = $this->create_save_array($order, $header_row);

		if (!$data_update_only && $this->status_is_enabled($new_wc_status)) {
			$gscwoo_client->add_row_to_sheet($spreadsheet_id, $adding_sheet, $insert_row, $order);
		}

		if (!$data_update_only && $this->status_is_enabled($old_wc_status)) {
			$header_removing_row = $gscwoo_client->get_header_row($spreadsheet_id, $removing_sheet);
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook kept for backward compatibility.
			$order_id_column_name = apply_filters('poolexpress_order_id_column_name', 'Order Id');
			$order_id_key = array_search($order_id_column_name, $header_removing_row);
			$gscwoo_client->remove_row_by_order_id($spreadsheet_id, $removing_sheet, $order_id, $order_id_key);
		}

		if ($data_update_only && $this->status_is_enabled($new_wc_status)) {
			$header_removing_row = $gscwoo_client->get_header_row($spreadsheet_id, $adding_sheet);
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook kept for backward compatibility.
			$order_id_column_name = apply_filters('poolexpress_order_id_column_name', 'Order Id');
			$order_id_key = array_search($order_id_column_name, $header_removing_row);
			$gscwoo_client->update_row_by_order_id($spreadsheet_id, $adding_sheet, $insert_row, $order_id, $order_id_key);
		}

		if ($this->status_is_enabled('all')) {
			$all_sheet = $this->status_and_sheets['all'];
			$header_all_row = $gscwoo_client->get_header_row($spreadsheet_id, $all_sheet);
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook kept for backward compatibility.
			$order_id_column_name = apply_filters('poolexpress_order_id_column_name', 'Order Id');
			$order_id_key = array_search($order_id_column_name, $header_all_row);
			$insert_row = $this->create_save_array($order, $header_all_row, 'all');
			$gscwoo_client->update_row_by_order_id($spreadsheet_id, $all_sheet, $insert_row, $order_id, $order_id_key);
		}

		remove_action('woocommerce_process_shop_order_meta', array($this, 'woocommerce_process_shop_order_meta'), 1000, 2);
	} catch (Exception $e) {
		wc_gsheetconnector_utility::gs_debug_log($e->getMessage());
		return;
		
	}
}

/**
 * Check whether a WooCommerce order status
 * is enabled in plugin settings.
 *
 * @param string $wc_status WooCommerce order status.
 *
 * @return bool
 */
public function status_is_enabled($wc_status)
{

	try {
		$selected_order_states = get_option('wcgsc_order_states');
		if (in_array($wc_status, $selected_order_states)) {
			return true;
		}

		return;
	} catch (Exception $e) {
		wc_gsheetconnector_utility::gs_debug_log($e->getMessage());
		return;
		
	}
}

/**
 * Get the authenticated Google Sheet client instance.
 *
 * Uses a cached object when available to avoid
 * multiple authentication requests.
 *
 * @return GSCWOO_googlesheet|null
 */
public function get_googlesheet_object()
{

	try {
		if ($this->_gfgsc_googlesheet) {
			return $this->_gfgsc_googlesheet;
		}

		$google_sheet = new GSCWOO_googlesheet();
		$google_sheet->auth();

		$this->_gfgsc_googlesheet = $google_sheet;
		return $google_sheet;
	} catch (Exception $e) {
		wc_gsheetconnector_utility::gs_debug_log($e->getMessage());
		return;
	}
}

/**
 * Generate a formatted list of ordered products
 * including quantity and SKU information.
 *
 * Example:
 * Product Name (2) (SKU123)
 *
 * @param WC_Order $order      WooCommerce order object.
 * @param array|bool $order_data Optional order data.
 *
 * @return string Comma-separated product list.
 */
public function extract_product_qty_sku($order, $order_data = false)
{

	try {

		$value = array();
		$order_items = $order->get_items();

		foreach ($order_items as $item) {
			$sku = '';
			$variation_id = $item->get_variation_id();
			$product_id = $item->get_product_id();
			$product = $item->get_product();
			if ($product && method_exists($product, 'get_sku')){
				$sku = $product->get_sku();
			}
			$create_product_name = $item->get_name();
			$create_product_name .= '(' . $item->get_quantity() . ')';
			$create_product_name .= $sku ? '(' . $sku . ')' : '';

			$value[] = $create_product_name;
		}

		$value = implode(', ', $value);
		return $value;
	} catch (Exception $e) {
		wc_gsheetconnector_utility::gs_debug_log($e->getMessage());
		return;
	}
}

/**
 * Retrieve additional WooCommerce order meta keys
 * that can be used as extra columns in Google Sheets.
 *
 * Excludes predefined WooCommerce core meta fields,
 * scans recent orders for custom meta keys, and caches
 * the result for improved performance.
 *
 * @return array List of additional order meta keys.
 */
public function get_adding_extra_order_row() {

	$extra_rows = wp_cache_get( 'gsc_extra_order_meta_keys', 'gsc_cache' );
	if ( false !== $extra_rows ) {
		return $extra_rows;
	}

	$excluded_keys = array(
		'_billing_address_1', '_billing_address_2', '_billing_address_index',
		'_billing_city', '_billing_company', '_billing_country',
		'_billing_first_name', '_billing_last_name', '_billing_postcode',
		'_billing_state', '_cart_hash', '_cart_discount_tax',
		'_completed_date', '_date_completed', '_date_paid',
		'_order_currency', '_order_tax', '_order_total',
		'_paid_date', '_payment_method', '_pos',
		'_shipping_address_1', '_shipping_address_2', '_shipping_address_index',
		'_shipping_city', '_shipping_company', '_shipping_country',
		'_shipping_first_name', '_shipping_last_name', '_shipping_postcode',
		'_shipping_state', '_wc'
	);

	$meta_keys = array();

    // Fetch last 100 orders as an example to avoid direct SQL
	$orders = wc_get_orders(array(
		'limit' => 100,
		'orderby' => 'ID',
		'order' => 'DESC',
		'return' => 'ids',
	));

	foreach ( $orders as $order_id ) {
		$order_meta = get_post_meta( $order_id );
		foreach ( array_keys($order_meta) as $key ) {
			if ( ! in_array( $key, $excluded_keys, true ) ) {
				$meta_keys[ $key ] = true;
			}
		}
	}

	$extra_rows = array_keys( $meta_keys );

	wp_cache_set( 'gsc_extra_order_meta_keys', $extra_rows, 'gsc_cache', 1800 );

	return $extra_rows;
}

/**
 * Retrieve additional product item and product meta keys
 * that can be used as extra columns in Google Sheets.
 *
 * Scans recent order items and WooCommerce products,
 * excludes standard WooCommerce meta fields, and caches
 * the discovered meta keys for improved performance.
 *
 * @return array List of additional product item meta keys.
 */
public function get_adding_extra_product_item_row() {

	$cache_key = 'gsc_extra_product_item_meta_keys';
	$extra_rows = wp_cache_get( $cache_key, 'gsc_cache' );
	if ( false !== $extra_rows ) {
		return $extra_rows;
	}

	$excluded_keys = array(
		'_product_id', '_variation_id', '_qty',
		'_line_subtotal', '_line_subtotal_tax', '_line_total'
	);

	$meta_keys = array();

    // Fetch last 100 orders (adjust limit as needed)
	$orders = wc_get_orders(array(
		'limit' => 100,
		'orderby' => 'ID',
		'order' => 'DESC',
		'return' => 'ids',
	));

	foreach ( $orders as $order_id ) {
		$items = wc_get_order( $order_id )->get_items();
		foreach ( $items as $item ) {
			foreach ( $item->get_meta_data() as $meta ) {
				$key = $meta->key;
				if ( ! in_array( $key, $excluded_keys, true ) ) {
					$meta_keys[ $key ] = true;
				}
			}
		}
	}

    // Also fetch product post meta
	$products = wc_get_products(array(
		'limit' => 100,
		'return' => 'ids',
	));

	foreach ( $products as $product_id ) {
		$post_meta = get_post_meta( $product_id );
		foreach ( array_keys($post_meta) as $key ) {
			if ( ! in_array( $key, $excluded_keys, true ) ) {
				$meta_keys[ $key ] = true;
			}
		}
	}

	$extra_rows = array_keys( $meta_keys );
	wp_cache_set( $cache_key, $extra_rows, 'gsc_cache', 1800 );

	return $extra_rows;
}

/**
 * Retrieve additional WooCommerce product post meta keys
 * that can be used as extra columns in Google Sheets.
 *
 * Scans recent products for custom post meta fields,
 * excludes standard WooCommerce meta keys, and caches
 * the result for improved performance.
 *
 * @return array List of additional product meta keys.
 */
public function get_adding_extra_product_row() {

	$cache_key  = 'gsc_extra_product_postmeta_keys';
	$extra_rows = wp_cache_get( $cache_key, 'gsc_cache' );

	if ( false !== $extra_rows ) {
		return $extra_rows;
	}

	$excluded_keys = array(
		'_product_id', '_variation_id', '_qty',
		'_line_subtotal', '_line_subtotal_tax', '_line_total'
	);

	$meta_keys = array();

    // Get product IDs using WooCommerce API (no direct SQL)
	$products = wc_get_products([
		'limit' => 100,
		'return' => 'ids',
	]);

	foreach ( $products as $product_id ) {
		$post_meta = get_post_meta( $product_id );
		foreach ( array_keys( $post_meta ) as $key ) {
			if ( ! in_array( $key, $excluded_keys, true ) ) {
				$meta_keys[ $key ] = true;
			}
		}
	}

	$extra_rows = array_keys( $meta_keys );
	wp_cache_set( $cache_key, $extra_rows, 'gsc_cache', 1800 );

	return $extra_rows;
}


// ----------------------------------------------------------------------
// Get the human-readable language name from a locale code
// This is useful for displaying language names in UI instead of codes
// ----------------------------------------------------------------------
public function wcgsc_get_language_display_name($locale, $wp_languages = array())
{

        // 1️⃣ WordPress official language name (if available)
	if (isset($wp_languages[$locale]['english_name'])) {
		return $wp_languages[$locale]['english_name'];
	}

        // 2️⃣ Manual fallback for common & missing locales (like en_US)
	$fallback_map = array(
		'en_US' => 'English - United States (en_US)',
		'en_GB' => 'English - United Kingdom (en_GB)',
		'it_IT' => 'Italian - Italy (it_IT)',
		'fr_FR' => 'French - France (fr_FR)',
		'de_DE' => 'German - Germany (de_DE)',
		'id_ID' => 'Indonesian - Indonesia (id_ID)',
		'es_ES' => 'Spanish - Spain (es_ES)',
		'pt_BR' => 'Portuguese - Brazil (pt_BR)',
		'nl_NL' => 'Dutch – Netherlands (nl_NL)',
		'ja_JP' => 'Japanese - Japan (ja_JP)',
		'zh_CN' => 'Chinese – china (zh_CN)',
		'af' => 'Afrikaans (af)',
		'sq' => 'Albanian (sq)',
		'am' => 'Amharic (am)',
		'ar' => 'Arabic (ar)',
		'ar_001' => 'Arabic - World (ar)',
		'arg' => 'Aragonese (arg)',
		'hy' => 'Armenian (hy)',
		'as' => 'Assamese (as)',
		'az' => 'Azerbaijani (az)',
		'eu' => 'Basque (eu)',
		'bel' => 'Belarusian (bel)',
		'bn_BD' => 'Bengali (bn_BD)',
		'bs_BA' => 'Bosnian (bs_BA)',
		'bg_BG' => 'Bulgarian (bg_BG)',
		'ca' => 'Catalan (ca)',
		'ceb' => 'Cebuano (ceb)',
		'zh_HK' => 'Chinese – Hong Kong (zh_HK)',
		'zh_TW' => 'Chinese – Traditional (zh_TW)',
		'hr' => 'Croatian (hr)',
		'cs_CZ' => 'Czech (cs_CZ)',
		'da_DK' => 'Danish (da_DK)',
		'nl_BE' => 'Dutch – Belgium (nl_BE)',
		'nl_NL_formal' => 'Dutch – Formal (nl_NL_formal)',
		'dzo' => 'Dzongkha (dzo)',
		'en_AU' => 'English – Australia (en_AU)',
		'en_CA' => 'English – Canada (en_CA)',
		'en_NZ' => 'English – New Zealand (en_NZ)',
		'en_ZA' => 'English – South Africa (en_ZA)',
		'eo' => 'Esperanto (eo)',
		'et' => 'Estonian (et)',
		'fi' => 'Finnish (fi)',
		'fr_BE' => 'French – Belgium (fr_BE)',
		'fr_CA' => 'French – Canada (fr_CA)',
		'fy' => 'Frisian (fy)',
		'fur' => 'Friulian (fur)',
		'gl_ES' => 'Galician (gl_ES)',
		'ka_GE' => 'Georgian (ka_GE)',
		'de_AT' => 'German – Austria (de_AT)',
		'de_DE_formal' => 'German – Formal (de_DE_formal)',
		'de_CH' => 'German – Switzerland (de_CH)',
		'el' => 'Greek (el)',
		'gu' => 'Gujarati (gu)',
		'haz' => 'Hazaragi (haz)',
		'he_IL' => 'Hebrew (he_IL)',
		'hi_IN' => 'Hindi (hi_IN)',
		'hu_HU' => 'Hungarian (hu_HU)',
		'is_IS' => 'Icelandic (is_IS)',
		'ja' => 'Japanese (ja)',
		'jv_ID' => 'Javanese (jv_ID)',
		'kab' => 'Kabyle (kab)',
		'kn' => 'Kannada (kn)',
		'kk' => 'Kazakh (kk)',
		'km' => 'Khmer (km)',
		'ko_KR' => 'Korean (ko_KR)',
		'ckb' => 'Kurdish – Sorani (ckb)',
		'kir' => 'Kyrgyz (kir)',
		'lo' => 'Lao (lo)',
		'lv' => 'Latvian (lv)',
		'lt_LT' => 'Lithuanian (lt_LT)',
		'dsb' => 'Lower Sorbian (dsb)',
		'mk_MK' => 'Macedonian (mk_MK)',
		'ms_MY' => 'Malay (ms_MY)',
		'ml_IN' => 'Malayalam (ml_IN)',
		'mr' => 'Marathi (mr)',
		'mn' => 'Mongolian (mn)',
		'ary' => 'Moroccan Arabic (ary)',
		'my_MM' => 'Myanmar – Burmese (my_MM)',
		'ne_NP' => 'Nepali (ne_NP)',
		'nb_NO' => 'Norwegian – Bokmål (nb_NO)',
		'nn_NO' => 'Norwegian – Nynorsk (nn_NO)',
		'oci' => 'Occitan (oci)',
		'ps' => 'Pashto (ps)',
		'fa_AF' => 'Persian – Afghanistan (fa_AF)',
		'fa_IR' => 'Persian (fa_IR)',
		'pl_PL' => 'Polish - poland (pl_PL)',
		'pt_AO' => 'Portuguese – Angola (pt_AO)',
		'pt_PT_ao90' => 'Portuguese – AO90 (pt_PT_ao90)',
		'pt_PT' => 'Portuguese - Portugal (pt_PT)',
		'pa_IN' => 'Punjabi (pa_IN)',
		'rhg' => 'Rohingya (rhg)',
		'ro_RO' => 'Romanian (ro_RO)',
		'ru_RU' => 'Russian - Russia (ru_RU)',
		'sah' => 'Sakha (sah)',
		'skr' => 'Saraiki (skr)',
		'gd' => 'Scottish Gaelic (gd)',
		'sr_RS' => 'Serbian (sr_RS)',
		'szl' => 'Silesian (szl)',
		'snd' => 'Sindhi (snd)',
		'si_LK' => 'Sinhala (si_LK)',
		'sk_SK' => 'Slovak (sk_SK)',
		'sl_SI' => 'Slovenian (sl_SI)',
		'azb' => 'South Azerbaijani (azb)',
		'es_AR' => 'Spanish – Argentina (es_AR)',
		'es_CL' => 'Spanish – Chile (es_CL)',
		'es_CO' => 'Spanish – Colombia (es_CO)',
		'es_CR' => 'Spanish – Costa Rica (es_CR)',
		'es_DO' => 'Spanish – Dominican Republic (es_DO)',
		'es_EC' => 'Spanish – Ecuador (es_EC)',
		'es_GT' => 'Spanish – Guatemala (es_GT)',
		'es_MX' => 'Spanish – Mexico (es_MX)',
		'es_PE' => 'Spanish – Peru (es_PE)',
		'es_PR' => 'Spanish – Puerto Rico (es_PR)',
		'es_UY' => 'Spanish – Uruguay (es_UY)',
		'es_VE' => 'Spanish – Venezuela (es_VE)',
		'sw' => 'Swahili (sw)',
		'sv_SE' => 'Swedish - Sweden (sv_SE)',
		'tl' => 'Tagalog (tl)',
		'tah' => 'Tahitian (tah)',
		'ta_IN' => 'Tamil – India (ta_IN)',
		'ta_LK' => 'Tamil – Sri Lanka (ta_LK)',
		'tt_RU' => 'Tatar (tt_RU)',
		'te' => 'Telugu (te)',
		'th' => 'Thai (th)',
		'bo' => 'Tibetan (bo)',
		'tr_TR' => 'Turkish - Turkey (tr_TR)',
		'uk' => 'Ukrainian (uk)',
		'hsb' => 'Upper Sorbian (hsb)',
		'ur' => 'Urdu (ur)',
		'ug_CN' => 'Uzbek (uz_UZ)',
		'vi' => 'Vietnamese (vi)',
		'cy' => 'Welsh (cy)',
	);

if (isset($fallback_map[$locale])) {
	return $fallback_map[$locale];
}

        // 3️⃣ Safe generic fallback (never breaks UI)
$parts = explode('_', $locale);

return ucfirst(strtolower($parts[0]));
}

}

$wcgsc_gsheetconnector_service = new wc_gsheetconnector_Service();
$wcgsc_gsheetconnector_service->init();