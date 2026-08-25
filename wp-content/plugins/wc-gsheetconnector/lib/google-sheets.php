<?php

if (!defined('ABSPATH'))
	exit;

class GSCWOO_googlesheet
{

	
	private $spreadsheet;
	private $worksheet;
	private $header_row_cache = array();



/**
* GET GOOGLE CREDS
*
* @since 1.0.0
*/
private static function creds()
{
	return is_multisite()
	? get_site_option('wcgsc_api_free_creds')
	: get_option('wcgsc_api_free_creds');
}

/**
 * Perform an HTTP request with a bounded retry on 429 (rate-limited) responses.
 *
 * Behaves identically to a single wp_remote_get()/wp_remote_post()/wp_remote_request()
 * call for any non-429 outcome (success or any other error) — same arguments, same
 * return shape. Only a 429 response triggers a short wait (honoring the Retry-After
 * header when present) and a retry, capped at $max_retries additional attempts.
 *
 * @since 1.4.10
 *
 * @param string $method      'get', 'post', or 'request' (for wp_remote_request, e.g. PUT).
 * @param string $url         Request URL.
 * @param array  $args        Arguments passed through to the underlying wp_remote_* call.
 * @param int    $max_retries Maximum number of retries after the initial attempt.
 * @return array|WP_Error Same return shape as the underlying wp_remote_* function.
 */
private function wp_remote_with_retry( $method, $url, $args = array(), $max_retries = 2 ) {
	for ( $attempt = 0; $attempt <= $max_retries; $attempt++ ) {
		if ( 'post' === $method ) {
			$response = wp_remote_post( $url, $args );
		} elseif ( 'get' === $method ) {
			$response = wp_remote_get( $url, $args );
		} else {
			$response = wp_remote_request( $url, $args );
		}

		if ( is_wp_error( $response ) || 429 !== wp_remote_retrieve_response_code( $response ) || $attempt === $max_retries ) {
			return $response;
		}

		$retry_after = wp_remote_retrieve_header( $response, 'retry-after' );
		sleep( min( is_numeric( $retry_after ) ? (int) $retry_after : ( 2 ** $attempt ), 5 ) );
	}

	return $response;
}

/**
 * Authenticate Google Client using authorization code.
 *
 * Fetches API credentials, initializes the Google Client,
 * exchanges the authorization code for an access token,
 * and stores the token data.
 *
 * @since 1.0
 *
 * @param string $code Google OAuth authorization code.
 * @return bool True when a valid access token was stored, false otherwise.
 */
public static function preauth($code)
{
	try {
		$creds = self::creds();
		if (!$creds) return;

		$response = wp_remote_post(
			'https://oauth2.googleapis.com/token',
			[
				'body' => [
					'code'          => $code,
					'client_id'     => $creds['client_id_web'],
					'client_secret' => $creds['client_secret_web'],
					'redirect_uri'  => 'https://oauth.gsheetconnector.com',
					'grant_type'    => 'authorization_code'
				]
			]
		);
		if (is_wp_error($response)) {
			return false;
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);
		if (!is_array($body)) {
			$body = [];
		}

		if (empty($body['access_token'])) {
			self::updateToken($body);
			return false;
		}

		self::updateToken($body);
		return true;
	} catch (Exception $e) {
		wc_gsheetconnector_utility::gs_debug_log('[Auth Exception]. ' . $e->getMessage());
		throw new LogicException('Auth error: ' . esc_html($e->getMessage()));
	}
}


/**
 * AUTHENTICATE GOOGLE CLIENT FOR API ACCESS
 *
 * This method handles authentication for Google API access by retrieving
 * a valid access token using the configured authentication method.
 *
 * Supported authentication modes:
 * 1. Manual OAuth authentication (client ID/secret based token)
 * 2. Service Account authentication (JSON credentials based token)
 * 3. Default OAuth authentication (plugin-managed token system)
 *
 * The method:
 * - Retrieves a valid access token from the active authentication flow
 * - Ensures token is refreshed automatically if required (handled internally)
 * - Returns the valid token for API usage
 *
 * If authentication fails, the error is logged and false is returned.
 *
 * @since 1.0.0
 * @return string|false Valid access token or false on failure
 */
public function auth()
{
	try {
		$token = $this->get_active_token();

		if (!$token) {
			return false;
		}

		return $token;
	} catch (Exception $e) {
		wc_gsheetconnector_utility::gs_debug_log(
			__METHOD__ . " Error in Auth: \n " . $e->getMessage()
		);
		return false;
	}
}


/**
 * REFRESH ACCESS TOKEN USING GOOGLE OAUTH
 *
 * This function refreshes the expired OAuth access token using the stored refresh token.
 * It calls Google's OAuth token endpoint and updates the WordPress option with the new token data.
 *
 * Steps:
 * 1. Validate refresh token exists
 * 2. Get client credentials
 * 3. Send request to Google OAuth API
 * 4. Validate response
 * 5. Store new access token + expiry time in database
 * 6. Return new access token
 *
 * @since 1.0.0
 */
private function refresh($token)
{
	try {
		if (empty($token['refresh_token'])) {
			return false;
		}
		$creds = self::creds();

		$response = wp_remote_post(
			'https://oauth2.googleapis.com/token',
			[
				'body' => [
					'client_id'     => $creds['client_id_web'],
					'client_secret' => $creds['client_secret_web'],
					'refresh_token' => $token['refresh_token'] ?? '',
					'grant_type'    => 'refresh_token'
				]
			]
		);
		if (is_wp_error($response)) {
			return false;
		}
		$body = json_decode(wp_remote_retrieve_body($response), true);


		if (!empty($body['access_token'])) {
			$body['refresh_token'] = $token['refresh_token'];
		}

		return $body;
	} catch (Exception $e) {
		wc_gsheetconnector_utility::gs_debug_log("Refresh Auto Token fail! - " . $e->getMessage());
	}
}

/**
 * Set spreadsheet ID.
 *
 * Stores the selected Google Spreadsheet ID.
 *
 * @since 1.0
 *
 * @param string $id Spreadsheet ID.
 * @return void
 */
public function setSpreadsheetId($id)
{
	$this->spreadsheet = $id;
}

/**
 * Get spreadsheet ID.
 *
 * Returns the currently selected Google Spreadsheet ID.
 *
 * @since 1.0
 *
 * @return string
 */
public function getSpreadsheetId()
{

	return $this->spreadsheet;
}

/**
 * Set worksheet tab ID.
 *
 * Stores the selected worksheet tab ID.
 *
 * @since 1.0
 *
 * @param string|int $id Worksheet tab ID.
 * @return void
 */
public function setWorkTabId($id)
{
	$this->worksheet = $id;
}

/**
 * Get worksheet tab ID.
 *
 * Returns the currently selected worksheet tab ID.
 *
 * @since 1.0
 *
 * @return string|int
 */
public function getWorkTabId()
{
	return $this->worksheet;
}


/**
 * Get the active Google API access token based on selected auth mode
 *
 * Modes:
 * Default = Existing/Auto token method
 * @since 1.0.0
 */
public function get_active_token()
{
	try {

		return $this->token();
	} catch (Exception $e) {
		wc_gsheetconnector_utility::gs_debug_log(
			__METHOD__ . " Error in Getting token: \n " . $e->getMessage()
		);
		return false;
	}
}


/**
 * GET ACCESS TOKEN
 *
 * @since 1.0.0
 */
private function token()
{
	try {
		$tokenJson = get_option('wcgsc_token');
		$tokenData = json_decode($tokenJson, true);

		if (empty($tokenData)) {
			return false;
		}


		if (!isset($tokenData['expire'])) {

			update_option('wcgsc_email_account', '');

			return false;
		}

		if (time() > intval($tokenData['expire'])) {

			$newToken = $this->refresh($tokenData);

			if (!empty($newToken['access_token'])) {

				self::updateToken($newToken);

				return $newToken['access_token'];
			}

			update_option('wcgsc_email_account', '');

			return false;
		}

		return $tokenData['access_token'];

	} catch (Exception $e) {
		wc_gsheetconnector_utility::gs_debug_log(
			"Error in getting auto token " . $e->getMessage()
		);

	}
}

/**
 * Store and update Google OAuth token for manual authentication.
 *
 * This function saves the token data returned by Google OAuth
 * and validates the permission scopes before storing them
 * in WordPress options.
 *
 * @since 3.0
 *
 * @param array $tokenData Google OAuth token data.
 *
 * @return void
 */
public static function updateToken( $tokenData ) {
	delete_transient( 'wcgsc_email_account_cache' );

  // Invalid token response
	if (empty($tokenData['access_token'])) {


		update_option('wcgsc_email_account', '');

		update_option(
			'wcgsc_token',
			wp_json_encode($tokenData)
		);

		if (class_exists('wcgsc_error_logs')) {

			wcgsc_error_logs::log_to_db(
				'Google_Access_Token_Invalid_Existing',
				403,
				'Google access token is invalid or expired (Existing Method)',
				[
					'error_type' => 'invalid_token',
					'authentication_method' => 'Existing',
					'message' => 'Authentication failed. The stored Google access token is invalid, expired, or refresh token is no longer valid. Please re-authenticate your Google account.',
				]
			);
		}

		return;
	}

	if ( isset( $tokenData['expires_in'] ) ) {
		$tokenData['expire'] = time() + intval( $tokenData['expires_in'] );
	}

	try {
		if(isset($tokenData['scope'])){
			$permission = explode(" ", $tokenData['scope']);
			if ( ( in_array("https://www.googleapis.com/auth/drive.metadata.readonly",$permission ) || in_array( 'https://www.googleapis.com/auth/drive.file', $permission ) ) && ( in_array( 'https://www.googleapis.com/auth/spreadsheets', $permission ) ) ) {
				update_option('wcgsc_verify', 'valid');
			}else{
				update_option('wcgsc_verify', 'invalid-auth');
           // Log permission error to error logs
				if (class_exists('wcgsc_error_logs')) {
					wcgsc_error_logs::log_to_db(
						'Google_Auth_Permission_Error',
						403,
						'Google Drive and Google Sheets permissions not granted',
						[
							'error_type' => 'Missing Permissions',
							'message' => 'User did not grant Google Drive and/or Google Sheets permissions during OAuth authentication',
							'granted_scopes' => $tokenData['scope'] ?? '',
							'required_drive_scope' => 'https://www.googleapis.com/auth/drive.file OR https://www.googleapis.com/auth/drive.metadata.readonly',
							'required_sheets_scope' => 'https://www.googleapis.com/auth/spreadsheets',
						]
					);
				}
			}
		}
		$tokenJson = json_encode( $tokenData );
		update_option( 'wcgsc_token', $tokenJson );
	} catch ( Exception $e ) {
		wc_gsheetconnector_utility::gs_debug_log($e->getMessage());
		return;
	}
}


/**
 * Get all Google spreadsheets.
 *
 * Fetches all spreadsheets available in the authenticated
 * Google Drive account.
 *
 * @since 1.0
 *
 * @return array|null List of spreadsheets or null on failure.
 */	
public function get_spreadsheets()
{
	try {
		$token = $this->get_active_token();

		if (!$token) {
			return [];
		}

		$response = wp_remote_get(
			'https://www.googleapis.com/drive/v3/files?q=mimeType="application/vnd.google-apps.spreadsheet"&fields=files(id,name)',
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $token
				]
			]
		);

		if (is_wp_error($response)) {
			return [];
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);

		$sheets = [];

		if (!empty($body['files'])) {
			foreach ($body['files'] as $file) {
				$sheets[] = [
					'id'    => $file['id'],
					'title' => $file['name'],
				];
			}
		}

		return $sheets;
	} catch (Exception $e) {
		wc_gsheetconnector_utility::gs_debug_log('[Fail To get Spreadsheet]. ' . $e->getMessage());
	}
}
/**
 * Get worksheet tabs from a spreadsheet.
 *
 * Fetches all worksheet tabs from the specified
 * Google Spreadsheet.
 *
 * @since 1.0
 *
 * @param string $spreadsheet_id Spreadsheet ID.
 * @return array|null List of worksheet tabs or null on failure.
 */
public function get_worktabs($spreadsheet_id)
{
	try {
		$token = $this->get_active_token();

		if (!$token) {
			return [];
		}

		$response = $this->wp_remote_with_retry(
			'get',
			"https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}",
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $token
				]
			]
		);

		if (is_wp_error($response)) {
			return [];
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);

		$tabs = [];

		if (!empty($body['sheets'])) {
			foreach ($body['sheets'] as $sheet) {
				$props = $sheet['properties'];

				$tabs[] = [
					'id'    => $props['sheetId'],
					'title' => $props['title'],
				];
			}
		}

		return $tabs;
	} catch (Exception $e) {
		wc_gsheetconnector_utility::gs_debug_log('[Fail To get Worktab]. ' . $e->getMessage());
	}
}

/**
 * Perform batch updates on spreadsheet tabs.
 *
 * Executes batch update requests on the specified
 * Google Spreadsheet.
 *
 * @since 1.0
 *
 * @param string $spreadsheet_id Spreadsheet ID.
 * @param array  $request_array  Batch update request data.
 * @return void
 */
public function perform_sheet_tab_updates( $spreadsheet_id, $request_array ) {

	try {

		$token = $this->get_active_token();

		if ( ! $token ) {

			wc_gsheetconnector_utility::gs_debug_log(
				'perform_sheet_tab_updates: Token not found'
			);

			return false;
		}

        /*
         * ===============================
         * VALIDATE REQUEST ARRAY
         * ===============================
         */
        if ( empty( $request_array ) || ! is_array( $request_array ) ) {

        	wc_gsheetconnector_utility::gs_debug_log(
        		'perform_sheet_tab_updates: Request array is empty'
        	);

        	return false;
        }

        /*
         * ===============================
         * BATCH UPDATE REQUEST
         * ===============================
         */
        $response = wp_remote_post(
        	"https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}:batchUpdate",
        	[
        		'headers' => [
        			'Authorization' => 'Bearer ' . $token,
        			'Content-Type'  => 'application/json',
        		],
        		'body'    => wp_json_encode(
        			[
        				'requests' => $request_array,
        			]
        		),
        	]
        );

        /*
         * ===============================
         * ERROR HANDLING
         * ===============================
         */
        if ( is_wp_error( $response ) ) {

        	wc_gsheetconnector_utility::gs_debug_log(
        		'Batch update failed: ' . $response->get_error_message()
        	);

        	return false;
        }

        $response_code = wp_remote_retrieve_response_code( $response );

        if ( ! in_array( $response_code, [ 200, 201 ], true ) ) {

        	wc_gsheetconnector_utility::gs_debug_log(
        		'Google API Error: ' . wp_remote_retrieve_body( $response )
        	);

        	return false;
        }

        return json_decode(
        	wp_remote_retrieve_body( $response ),
        	true
        );

    } catch ( Exception $e ) {

    	wc_gsheetconnector_utility::gs_debug_log(
    		'perform_sheet_tab_updates Error: ' . $e->getMessage()
    	);

    	return false;
    }
}

/**
 * Get worksheet tab ID by sheet name.
 *
 * Searches worksheet tabs in the spreadsheet
 * and returns the matching tab ID.
 *
 * @since 1.0
 *
 * @param string $selected_sheet_id Spreadsheet ID.
 * @param string $gscwoo_sheetname Worksheet tab name.
 * @return string|int
 */
public function getTabId( $selected_sheet_id, $gscwoo_sheetname ) {

	try {

		if ( empty( $selected_sheet_id ) ) {

			wc_gsheetconnector_utility::gs_debug_log(
				'getTabId: Spreadsheet ID is EMPTY'
			);

			return '';
		}

        /*
         * ===============================
         * GET WORKSHEET TABS
         * ===============================
         */
        $tabsArr = $this->get_worktabs( $selected_sheet_id );

        $tabId = '';

        /*
         * ===============================
         * FIND TAB ID
         * ===============================
         */
        if ( ! empty( $tabsArr ) && is_array( $tabsArr ) ) {

        	foreach ( $tabsArr as $value ) {

        		if (
        			isset( $value['title'] ) &&
        			$value['title'] == $gscwoo_sheetname
        		) {

        			$tabId = isset( $value['id'] )
        			? $value['id']
        			: '';

        			break;
        		}
        	}
        }

        return $tabId;

    } catch ( Exception $e ) {

    	wc_gsheetconnector_utility::gs_debug_log(
    		'getTabId Error: ' . $e->getMessage()
    	);

    	return '';
    }
}


/**
 * Get all worksheet tabs for a spreadsheet.
 *
 * Retrieves worksheet tabs from the given spreadsheet
 * and returns them as an associative array with
 * sheet ID as the key and sheet title as the value.
 *
 * @since 1.0
 *
 * @param string $spreadsheet_id Google Spreadsheet ID.
 * @return array List of worksheet tabs.
 */	
public function get_sheet_tabs( $spreadsheet_id, $tabs = false ) {

	try {

		if ( empty( $spreadsheet_id ) ) {

			wc_gsheetconnector_utility::gs_debug_log(
				'get_sheet_tabs: Spreadsheet ID is EMPTY'
			);

			return [];
		}

        /*
         * ===============================
         * GET TABS
         * ===============================
         */
        if ( ! $tabs ) {

        	$tabs = $this->get_worktabs( $spreadsheet_id );
        }

        /*
         * ===============================
         * VALIDATE TABS
         * ===============================
         */
        if ( empty( $tabs ) || ! is_array( $tabs ) ) {
        	return [];
        }

        /*
         * ===============================
         * RETURN TABS
         * id => title
         * ===============================
         */
        return wp_list_pluck( $tabs, 'title', 'id' );

    } catch ( Exception $e ) {

    	wc_gsheetconnector_utility::gs_debug_log(
    		'get_sheet_tabs Error: ' . $e->getMessage()
    	);

    	return [];
    }
}

/**
 * Get worksheet name by spreadsheet ID and tab ID.
 *
 * Searches the saved spreadsheet data and returns
 * the worksheet/tab name matching the provided tab ID.
 *
 * @since 1.0
 *
 * @param string $spreadsheet_id Google Spreadsheet ID.
 * @param string $tab_id Worksheet tab ID.
 * @return string Worksheet tab name.
 */
public function get_sheet_name( $spreadsheet_id, $tab_id ) {

	try {

		$all_sheet_data = get_option( 'wcgsc_sheetId', [] );

		$tab_name = '';

		if ( ! empty( $all_sheet_data ) && is_array( $all_sheet_data ) ) {

			foreach ( $all_sheet_data as $spreadsheet ) {

				if (
					isset( $spreadsheet['id'] ) &&
					$spreadsheet['id'] == $spreadsheet_id
				) {

					$tabs = isset( $spreadsheet['tabId'] )
					? $spreadsheet['tabId']
					: [];

					if ( ! empty( $tabs ) && is_array( $tabs ) ) {

						foreach ( $tabs as $name => $id ) {

							if ( $id == $tab_id ) {

								$tab_name = $name;
								break 2;
							}
						}
					}
				}
			}
		}

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook kept for backward compatibility.
		$tab_name = apply_filters('gcwoo_filter_tab_name', $tab_name, $spreadsheet_id, $tab_id);

		return $tab_name;

	} catch ( Exception $e ) {

		wc_gsheetconnector_utility::gs_debug_log(
			'get_sheet_name Error: ' . $e->getMessage()
		);

		return '';
	}
}

/**
 * Get spreadsheet name by spreadsheet ID.
 *
 * Searches the saved spreadsheet data and returns
 * the spreadsheet name matching the provided ID.
 *
 * @since 1.0
 *
 * @param string $spreadsheet_id Google Spreadsheet ID.
 * @return string Spreadsheet name.
 */
public function get_spreadsheet_name( $spreadsheet_id ) {

	try {

		$all_sheet_data = get_option( 'wcgsc_sheetId', [] );

		$spreadsheetName = '';

		if ( ! empty( $all_sheet_data ) && is_array( $all_sheet_data ) ) {

			foreach ( $all_sheet_data as $spreadsheet_name => $spreadsheet ) {

				if (
					isset( $spreadsheet['id'] ) &&
					$spreadsheet['id'] == $spreadsheet_id
				) {

					$spreadsheetName = $spreadsheet_name;
					break;
				}
			}
		}

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook kept for backward compatibility.
		$spreadsheetName = apply_filters('gcwoo_filter_spreasheet_name', $spreadsheetName, $spreadsheet_id);

		return $spreadsheetName;

	} catch ( Exception $e ) {

		wc_gsheetconnector_utility::gs_debug_log(
			'get_spreadsheet_name Error: ' . $e->getMessage()
		);

		return '';
	}
}


/**
 * Remove a row from Google Sheet by order ID.
 *
 * Searches the worksheet for the matching order ID
 * and deletes the corresponding row from the sheet.
 *
 * @since 1.0
 *
 * @param string $spreadsheet_id Google Spreadsheet ID.
 * @param string $tab_name Worksheet tab name.
 * @param string $order_id WooCommerce order ID.
 * @param int    $order_id_index Column index of order ID.
 * @return bool False on failure.
 */
public function remove_row_by_order_id( $spreadsheet_id, $tab_name, $order_id, $order_id_index ) {

	try {

        // 1. GET TOKEN
		$token = $this->get_active_token();

		if ( ! $token ) {
			wc_gsheetconnector_utility::gs_debug_log(
				'remove_row_by_order_id: Token not found'
			);
			return false;
		}

        // 2. GET SHEET ID
		$tab_id = $this->getTabId( $spreadsheet_id, $tab_name );

		if ( empty( $tab_id ) ) {
			wc_gsheetconnector_utility::gs_debug_log(
				'remove_row_by_order_id: Worksheet ID not found'
			);
			return false;
		}

        // 3. FETCH SHEET DATA (A1:ZZ)
		$url = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}/values/"
		. rawurlencode( $tab_name . '!A1:ZZ' );

		$response = $this->wp_remote_with_retry( 'get', $url, [
			'headers' => [
				'Authorization' => 'Bearer ' . $token,
			],

		] );

		if ( is_wp_error( $response ) ) {
			wc_gsheetconnector_utility::gs_debug_log(
				'Fetch rows failed: ' . $response->get_error_message()
			);
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$get_values = $body['values'] ?? [];

		if ( empty( $get_values ) ) {
			return false;
		}

        // 4. GET ORDER IDS COLUMN
		$order_ids = wp_list_pluck( $get_values, $order_id_index );

		$index = array_search( $order_id, $order_ids );

		if ( $index === false ) {
			return false;
		}

        // (safe endIndex same row delete)
		$end_index = $index + 1;

        // 5. DELETE ROW (batchUpdate)
		$delete_url = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}:batchUpdate";

		$payload = [
			'requests' => [
				[
					'deleteDimension' => [
						'range' => [
							'sheetId'     => (int) $tab_id,
							'dimension'   => 'ROWS',
							'startIndex'  => (int) $index,
							'endIndex'    => (int) $end_index,
						],
					],
				],
			],
		];

		$delete_response = $this->wp_remote_with_retry( 'post', $delete_url, [
			'headers' => [
				'Authorization' => 'Bearer ' . $token,
				'Content-Type'  => 'application/json',
			],
			'body'    => wp_json_encode( $payload ),

		] );

		if ( is_wp_error( $delete_response ) ) {
			wc_gsheetconnector_utility::gs_debug_log(
				'Delete row failed: ' . $delete_response->get_error_message()
			);
			return false;
		}

		$code = wp_remote_retrieve_response_code( $delete_response );

		if ( $code !== 200 ) {
			wc_gsheetconnector_utility::gs_debug_log(
				'Google API Error: ' . wp_remote_retrieve_body( $delete_response )
			);
			return false;
		}

		return true;

	} catch ( Exception $e ) {
		wc_gsheetconnector_utility::gs_debug_log( $e->getMessage() );
		return false;
	}
}

/**
 * Sanitize values before sending them to Google Sheets.
 *
 * This method ensures that values written to Google Sheets
 * are properly formatted. It recursively sanitizes array values
 * and trims string values.
 *
 * If a string starts with a "+" followed by a digit (for example
 * phone numbers like +919876543210), the value is prefixed with
 * a single quote to force Google Sheets to treat it as TEXT
 * instead of converting it to a formula or number.
 *
 * This prevents formatting issues when syncing WooCommerce data
 * such as phone numbers or special identifiers.
 *
 * @param mixed $value Value or array of values to sanitize.
 *
 * @return mixed Sanitized value or sanitized array.
 */
public function gs_sanitize_sheet_value( $value ) {

  if ( is_array( $value ) ) {
    foreach ( $value as $k => $v ) {
      $value[ $k ] = $this->gs_sanitize_sheet_value( $v );
    }
    return $value;
  }

  if ( is_string( $value ) ) {
    $value = trim( $value );
    if ( preg_match( '/^\+\d/', $value ) ) {
            return "'" . $value; // force TEXT in Google Sheets
          }
        }

        return $value;
      }

/**
 * Insert or update a row in Google Sheet by order ID.
 *
 * Updates the existing row if the order ID exists,
 * otherwise appends a new row to the worksheet.
 *
 * @since 1.0
 *
 * @param string $spreadsheet_id Google Spreadsheet ID.
 * @param string $tab_name Worksheet tab name.
 * @param array  $row_data Row data to insert/update.
 * @param string $order_id WooCommerce order ID.
 * @param int    $order_id_index Column index of order ID.
 * @return bool False on failure.
 */
public function update_row_by_order_id( $spreadsheet_id, $tab_name, $row_data, $order_id, $order_id_index ) {

	try {

		$token = $this->get_active_token();

		if ( ! $token ) {

			wc_gsheetconnector_utility::gs_debug_log(
				'update_row_by_order_id: Token not found'
			);

			return false;
		}

        /*
         * ===============================
         * GET SHEET DATA
         * ===============================
         */
        $full_range_response = $this->wp_remote_with_retry(
        	'get',
        	"https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}/values/" . urlencode( $tab_name . '!A1:ZZ' ),
        	[
        		'headers' => [
        			'Authorization' => 'Bearer ' . $token,
        		],
        	]
        );

        if ( is_wp_error( $full_range_response ) ) {

        	wc_gsheetconnector_utility::gs_debug_log(
        		'Fetch rows failed: ' . $full_range_response->get_error_message()
        	);

        	return false;
        }

        $full_body  = json_decode( wp_remote_retrieve_body( $full_range_response ), true );
        $get_values = $full_body['values'] ?? [];

        $asc_desc_sorting = get_option( 'asc_desc_sorting', 'ASC' );

        /*
         * ===============================
         * FIND ORDER ID ROW
         * ===============================
         */
        $order_ids = wp_list_pluck( $get_values, $order_id_index );

        $row       = array_search( $order_id, $order_ids );
        $end_index = 0;

        foreach ( $order_ids as $key => $value ) {

        	if ( $order_id == $value ) {
        		$end_index = $key;
        	}
        }

        /*
         * ===============================
         * SANITIZE FULL ROW DATA
         * ===============================
         */
        foreach ( $row_data as &$data ) {

        	$data = str_replace( '{row}', $row, $data );
        	$data = $this->gs_sanitize_sheet_value( $data );

            // Force phone numbers as text
        	if ( is_string( $data ) ) {

        		$data = trim( $data );

        		if ( preg_match( '/^\+\d/', $data ) ) {
        			$data = "'" . $data;
        		}
        	}
        }

        unset( $data );

        /*
         * ===============================
         * INSERT IF ROW NOT FOUND
         * ===============================
         */
        if ( $row === false ) {

        	$row = ! empty( $get_values )
        	? count( $get_values ) + 1
        	: 1;

            /*
             * DESC INSERT
             */
            if ( $asc_desc_sorting === 'DESC' ) {
            	$range = $tab_name . '!A2:ZZ';

            	$response = $this->wp_remote_with_retry(
            		'request',
            		"https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}/values/" . urlencode( $range ) . '?valueInputOption=USER_ENTERED',
            		[
            			'method'  => 'PUT',
            			'headers' => [
            				'Authorization' => 'Bearer ' . $token,
            				'Content-Type'  => 'application/json',
            			],
            			'body'    => wp_json_encode(
            				[
            					'values' => [ array_values( $row_data ) ],
            				]
            			),
            		]
            	);

            } else {

                /*
                 * ASC INSERT
                 */
                $range = $tab_name;

                $response = $this->wp_remote_with_retry(
                	'post',
                	"https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}/values/" . urlencode( $range ) . ":append?valueInputOption=USER_ENTERED&insertDataOption=INSERT_ROWS",
                	[
                		'headers' => [
                			'Authorization' => 'Bearer ' . $token,
                			'Content-Type'  => 'application/json',
                		],
                		'body'    => wp_json_encode(
                			[
                				'values' => [ array_values( $row_data ) ],
                			]
                		),
                	]
                );
            }

            if ( is_wp_error( $response ) ) {

            	wc_gsheetconnector_utility::gs_debug_log(
            		'Insert row failed: ' . $response->get_error_message()
            	);

            	return false;
            }
        }

        /*
         * ===============================
         * UPDATE EXISTING ROW
         * ===============================
         */
        else {

        	if ( $end_index + 1 != $row ) {
        		$row++;
        	}

        	$range = $tab_name . '!A' . $row . ':ZZ';

        	$response = $this->wp_remote_with_retry(
        		'request',
        		"https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}/values/" . urlencode( $range ) . '?valueInputOption=USER_ENTERED',
        		[
        			'method'  => 'PUT',
        			'headers' => [
        				'Authorization' => 'Bearer ' . $token,
        				'Content-Type'  => 'application/json',
        			],
        			'body'    => wp_json_encode(
        				[
        					'values' => [ array_values( $row_data ) ],
        				]
        			),
        		]
        	);

        	if ( is_wp_error( $response ) ) {

        		wc_gsheetconnector_utility::gs_debug_log(
        			'Update row failed: ' . $response->get_error_message()
        		);

        		return false;
        	}
        }

        return true;

    } catch ( Exception $e ) {

    	wc_gsheetconnector_utility::gs_debug_log( $e->getMessage() );

    	return false;
    }
}


/**
 * Add a new row or header row to a Google Sheet.
 *
 * Inserts order data into the selected worksheet tab.
 * Can also update the header row when `$is_header` is true.
 *
 * @since 1.0
 *
 * @param string   $spreadsheet_id Google Spreadsheet ID.
 * @param string   $tab_name Worksheet tab name.
 * @param array    $row_data Row data to insert.
 * @param WC_Order $order WooCommerce order object.
 * @param bool     $is_header Whether the row is a header row.
 * @return bool True on success, false on failure.
 */
public function add_row_to_sheet( $spreadsheet_id, $tab_name, $row_data, $order, $is_header = false ) {

	if ( ! $row_data ) {
		return false;
	}

	ksort( $row_data );

	try {

		$token = $this->get_active_token();

		if ( ! $token ) {
			wc_gsheetconnector_utility::gs_debug_log(
				'add_row_to_sheet: Token not found'
			);
			return false;
		}

		$asc_desc_sorting = get_option( 'asc_desc_sorting', 'ASC' );

    /*
     * =========================
     * GET HEADER ROW
     * =========================
     */
    $header_response = $this->wp_remote_with_retry(
    	'get',
    	"https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}/values/" . urlencode( $tab_name . '!1:1' ),
    	[
    		'headers' => [
    			'Authorization' => 'Bearer ' . $token,
    		],
    	]
    );

    if ( is_wp_error( $header_response ) ) {
    	wc_gsheetconnector_utility::gs_debug_log(
    		'Header fetch failed: ' . $header_response->get_error_message()
    	);
    	return false;
    }

    $header_body = json_decode( wp_remote_retrieve_body( $header_response ), true );
    $header_row_values = $header_body['values'][0] ?? [];

    /*
     * =========================
     * HEADER UPDATE
     * =========================
     */
    if ( $is_header ) {

    	if ( ! empty( $header_row_values ) && count( $header_row_values ) > count( $row_data ) ) {

    		$new_raw_data = [];

    		foreach ( $header_row_values as $key => $val ) {
    			$new_raw_data[] = isset( $row_data[$key] ) ? $row_data[$key] : '';
    		}

    		$row_data = $new_raw_data;
    	}

    	$row_data = str_replace( '&#039;', "'", $row_data );

    	$response = $this->wp_remote_with_retry(
    		'request',
    		"https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}/values/" .
    		urlencode( $tab_name . '!1:1' ) .
    		'?valueInputOption=RAW',
    		[
    			'method'  => 'PUT',
    			'headers' => [
    				'Authorization' => 'Bearer ' . $token,
    				'Content-Type'  => 'application/json',
    			],
    			'body' => wp_json_encode([
    				'values' => [ array_values( $row_data ) ],
    			]),
    		]
    	);

    	if ( is_wp_error( $response ) ) {
    		wc_gsheetconnector_utility::gs_debug_log(
    			'Header update failed: ' . $response->get_error_message()
    		);
    		return false;
    	}
     // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook kept for backward compatibility.
    	do_action( 'gcwoo_header_updated', $row_data );

    	return true;
    }

    /*
     * =========================
     * GET SHEET DATA
     * =========================
     */
    $full_range_response = $this->wp_remote_with_retry(
    	'get',
    	"https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}/values/" .
    	urlencode( $tab_name . '!A1:ZZ' ),
    	[
    		'headers' => [
    			'Authorization' => 'Bearer ' . $token,
    		],
    	]
    );

    if ( is_wp_error( $full_range_response ) ) {
    	wc_gsheetconnector_utility::gs_debug_log(
    		'Fetch rows failed: ' . $full_range_response->get_error_message()
    	);
    	return false;
    }

    $full_body  = json_decode( wp_remote_retrieve_body( $full_range_response ), true );
    $get_values = $full_body['values'] ?? [];

    /*
     * =========================
     * ROW INDEX CALCULATION
     * =========================
     */
    if ( $asc_desc_sorting === 'DESC' ) {
    	$row = 2;
    } else {
    	$row = ! empty( $get_values ) ? count( $get_values ) + 1 : 1;
    }

    /*
     * =========================
     * PREPARE DATA
     * =========================
     */
    foreach ( $row_data as &$data ) {

    	$data = str_replace( '{row}', $row, $data );

    	if ( is_string( $data ) ) {
    		$data = trim( $data );

    		if ( preg_match( '/^\+\d/', $data ) ) {
    			$data = "'" . $data;
    		}
    	}
    }
    unset( $data );

    /*
     * =========================
     * DESC MODE (INSERT ROW FIRST)
     * =========================
     */
    if ( $asc_desc_sorting === 'DESC' ) {

    	$tab_id = $this->getTabId( $spreadsheet_id, $tab_name );

    	$insert_dimension_response = $this->wp_remote_with_retry(
    		'post',
    		"https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}:batchUpdate",
    		[
    			'headers' => [
    				'Authorization' => 'Bearer ' . $token,
    				'Content-Type'  => 'application/json',
    			],
    			'body' => wp_json_encode([
    				'requests' => [
    					[
    						'insertDimension' => [
    							'range' => [
    								'sheetId'    => (int) $tab_id,
    								'dimension'  => 'ROWS',
    								'startIndex' => 1,
    								'endIndex'   => 2,
    							],
    							'inheritFromBefore' => false,
    						],
    					],
    				],
    			]),
    		]
    	);

    	if ( is_wp_error( $insert_dimension_response ) ) {
    		wc_gsheetconnector_utility::gs_debug_log(
    			'Insert dimension failed: ' . $insert_dimension_response->get_error_message()
    		);
    		return false;
    	}

    	$insert_dimension_code = wp_remote_retrieve_response_code( $insert_dimension_response );

    	if ( ! in_array( $insert_dimension_code, [ 200, 201 ], true ) ) {
    		wc_gsheetconnector_utility::gs_debug_log(
    			'Insert dimension failed: ' . wp_remote_retrieve_body( $insert_dimension_response )
    		);
    		return false;
    	}
    }

    /*
     * =========================
     * INSERT DATA
     * =========================
     */
    if ( $asc_desc_sorting === 'DESC' ) {

    	$range = $tab_name . '!A2:ZZ';

    	$response = $this->wp_remote_with_retry(
    		'request',
    		"https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}/values/" .
    		urlencode( $range ) .
    		'?valueInputOption=USER_ENTERED',
    		[
    			'method'  => 'PUT',
    			'headers' => [
    				'Authorization' => 'Bearer ' . $token,
    				'Content-Type'  => 'application/json',
    			],
    			'body' => wp_json_encode([
    				'values' => [ array_values( $row_data ) ],
    			]),
    		]
    	);

    } else {

    	$range = $tab_name;

    	$response = $this->wp_remote_with_retry(
    		'post',
    		"https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}/values/" .
    		urlencode( $range ) .
    		":append?valueInputOption=USER_ENTERED&insertDataOption=INSERT_ROWS",
    		[
    			'headers' => [
    				'Authorization' => 'Bearer ' . $token,
    				'Content-Type'  => 'application/json',
    			],
    			'body' => wp_json_encode([
    				'values' => [ array_values( $row_data ) ],
    			]),
    		]
    	);
    }

    /*
     * =========================
     * ERROR CHECK
     * =========================
     */
    if ( is_wp_error( $response ) ) {
    	wc_gsheetconnector_utility::gs_debug_log(
    		'Insert row failed: ' . $response->get_error_message()
    	);
    	return false;
    }

    $code = wp_remote_retrieve_response_code( $response );

    if ( ! in_array( $code, [ 200, 201 ], true ) ) {
    	wc_gsheetconnector_utility::gs_debug_log(
    		'Google API Error: ' . wp_remote_retrieve_body( $response )
    	);
    	return false;
    }
   // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook kept for backward compatibility.
    do_action( 'gcwoo_entry_added', $row_data, $order );

    return true;

} catch ( Exception $e ) {

	wc_gsheetconnector_utility::gs_debug_log( $e->getMessage() );
	return false;
}
}

/**
 * Get header row cells from a worksheet tab.
 *
 * Fetches the first row from the selected worksheet
 * and returns the header cell values.
 *
 * @since 1.0
 *
 * @param string $spreadsheet_id Google Spreadsheet ID.
 * @param string $tab_id Worksheet tab title/ID.
 * @return array Header row values.
 */
public function get_header_row( $spreadsheet_id, $tab_name ) {

	$cache_key = $spreadsheet_id . '|' . $tab_name;

	if ( array_key_exists( $cache_key, $this->header_row_cache ) ) {
		return $this->header_row_cache[ $cache_key ];
	}

	$header_cells = $this->fetch_header_row( $spreadsheet_id, $tab_name );

	$this->header_row_cache[ $cache_key ] = $header_cells;

	return $header_cells;
}

/**
 * Fetch header row cells from a worksheet tab via the Google Sheets API.
 *
 * Unmemoized implementation; called by get_header_row() on a cache miss.
 *
 * @param string $spreadsheet_id Google Spreadsheet ID.
 * @param string $tab_name Worksheet tab title/ID.
 * @return array Header row values.
 */
private function fetch_header_row( $spreadsheet_id, $tab_name ) {

	$header_cells = [];

	try {

		$token = $this->get_active_token();

		if ( ! $token ) {
			return [];
		}

    /*
     * STEP 1: GET SHEET META
     */
    $meta_response = $this->wp_remote_with_retry(
    	'get',
    	"https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}",
    	[
    		'headers' => [
    			'Authorization' => 'Bearer ' . $token
    		]
    	]
    );

    if ( is_wp_error( $meta_response ) ) {
    	return [];
    }

    $meta_body = json_decode( wp_remote_retrieve_body( $meta_response ), true );

    if ( empty( $meta_body['sheets'] ) ) {
    	return [];
    }

    /*
     * STEP 2: FIND TAB BY TITLE (IMPORTANT FIX)
     */
    $found = false;

    foreach ( $meta_body['sheets'] as $sheet ) {

    	$title = $sheet['properties']['title'];

    	if ( $title === $tab_name ) {

    		$found = true;

        /*
         * STEP 3: GET HEADER ROW
         */
        $range = $title . '!1:1';

        $header_response = $this->wp_remote_with_retry(
        	'get',
        	"https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}/values/" . urlencode($range),
        	[
        		'headers' => [
        			'Authorization' => 'Bearer ' . $token
        		]
        	]
        );

        if ( is_wp_error( $header_response ) ) {
        	return [];
        }

        $header_body = json_decode( wp_remote_retrieve_body( $header_response ), true );

        if ( ! empty( $header_body['values'][0] ) ) {
        	$header_cells = $header_body['values'][0];
        }

        break;
    }
}

if ( ! $found ) {
	return [];
}

} catch ( Exception $e ) {

	wc_gsheetconnector_utility::gs_debug_log(
		"Header Fetch Error: " . $e->getMessage()
	);

	return [];
}

 // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing hook kept for backward compatibility.
return apply_filters("gcwoo_fetched_header_cells", $header_cells, $spreadsheet_id, $tab_name);
}

/**
 * Get and store the authenticated Google account email.
 *
 * Authenticates the Google client, fetches the connected
 * Google account email address, and stores it in the
 * WordPress options table.
 *
 * @since 3.1
 *
 * @return string|false Google account email on success, false on failure.
 */
public function gsheet_print_google_account_email()
{
	try {

		$cached_email = get_transient( 'wcgsc_email_account_cache' );

		if ( false !== $cached_email ) {
			return $cached_email;
		}

		$token = $this->token();

		if (!$token) {

			update_option('wcgsc_email_account', '');

			return false;
		}

		$email = $this->get_google_user_email($token);

		if (empty($email)) {

			$auth_method = get_option(
				'wcgsc_manual_setting',
				'0'
			);

			if ($auth_method === '0') {

				update_option(
					'wcgsc_email_account',
					''
				);

				if (class_exists('wcgsc_error_logs')) {

					wcgsc_error_logs::log_to_db(
						'Google_User_Email_Empty',
						403,
						'Google user email could not be retrieved (Existing Method)',
						[
							'error_type' => 'connected_email_empty',
							'authentication_method' => 'Existing',
							'message' => 'Failed to retrieve the connected Google account email address.',
						]
					);
				}
			}

			return false;
		}

		update_option(
			'wcgsc_email_account',
			$email
		);

		set_transient( 'wcgsc_email_account_cache', $email, HOUR_IN_SECONDS );

		return $email;

	} catch (Exception $e) {

		update_option(
			'wcgsc_email_account',
			''
		);

		wc_gsheetconnector_utility::gs_debug_log(
			__METHOD__ .
			' Error fetching email: ' .
			$e->getMessage()
		);

		return false;
	}
}

/**
 * GET GOOGLE USER EMAIL FROM ACCESS TOKEN
 *
 * This method retrieves the authenticated Google user's email address
 * using the provided OAuth access token.
 *
 * Workflow:
 * 1. Validate access token exists
 * 2. Send request to Google UserInfo API
 * 3. Decode API response
 * 4. Extract and return user email if available
 *
 * If the request fails or email is not found, an empty string is returned.
 *
 * @since 1.0.0
 * @param string $token Google OAuth access token
 * @return string User email or empty string on failure
 */
private function get_google_user_email($token)
{
	try {
		if (!$token) {
			return '';
		}

		$response = wp_remote_get(
			'https://www.googleapis.com/oauth2/v2/userinfo',
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $token
				]
			]
		);

		if (is_wp_error($response)) {
			return '';
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);

		if (!empty($body['email'])) {
			return $body['email'];
		}

		return '';
	} catch (Exception $e) {
		wc_gsheetconnector_utility::gs_debug_log(
			__METHOD__ . " Error in fetching user info: \n " . $e->getMessage()
		);
		return false;
	}
}


}
