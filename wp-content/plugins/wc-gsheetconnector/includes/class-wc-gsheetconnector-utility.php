<?php

/*
 * Utilities class for woocommerce google sheet connector pro
 * @since 1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * wc_gsheetconnector_utility class - singleton class
 * @since 1.0
 */
class wc_gsheetconnector_utility
{

    private function __construct()
    {
        // Do Nothing
    }

 /**
 * Retrieves the singleton instance of the utility class.
 *
 * Creates the instance on the first call and returns the existing
 * instance on subsequent calls.
 *
 * @return wc_gsheetconnector_utility Singleton instance of the utility class.
 */
 public static function instance()
 {

    static $instance = NULL;
    if (is_null($instance)) {
        $instance = new wc_gsheetconnector_utility();
    }
    return $instance;
}

/**
 * Logs a debug message when WordPress debugging is enabled.
 *
 * Passes the provided message to the plugin's debug logger only when
 * the WP_DEBUG constant is enabled.
 *
 * @param mixed $message Debug message or data to log.
 * @return void
 */
public function logger($message)
{
    if ( defined('WP_DEBUG') && WP_DEBUG === true ) {
        self::gs_debug_log($message);
    }
}


/**
 * Generates an admin notice based on the provided message type.
 *
 * Creates the appropriate WordPress admin notice markup for displaying
 * success, error, update, or upgrade messages in the admin area.
 *
 * @since 1.0
 *
 * @param array $data {
 *     Arguments used to generate the notice.
 *
 *     @type string $type    Notice type. Supported values: error, update, update-nag, upgrade.
 *     @type string $message Notice message to display.
 * }
 * @return string HTML markup for the admin notice.
 */
public function admin_notice($data = array())
{
    $message = isset($data['message']) ? $data['message'] : '';
    $message_type = isset($data['type']) ? $data['type'] : '';
    
    switch ($message_type) {
        case 'error':
        $admin_notice = '<div id="message" class="error notice is-dismissible">';
        break;
        case 'update':
        $admin_notice = '<div id="message" class="updated notice is-dismissible">';
        break;
        case 'update-nag':
        $admin_notice = '<div id="message" class="update-nag">';
        break;
        case 'upgrade':
        $admin_notice = '<div id="message" class="error notice wpforms-gs-upgrade is-dismissible">';
        break;
        default:
        $message = __('There\'s something wrong with your code...', 'wc-gsheetconnector');
        $admin_notice = "<div id=\"message\" class=\"error\">";
        break;
    }

    $admin_notice .= '<p>' . esc_html( $message ) . '</p>';
    $admin_notice .= "</div>\n";

    return $admin_notice;
}


/**
 * Retrieves the primary role of the currently logged-in user.
 *
 * Returns the first assigned WordPress role for the current user.
 * If no role is assigned, an empty string is returned.
 *
 * @since 1.0
 * @return string The current user's primary role, or an empty string if unavailable.
 */
public function get_current_user_role()
{
    $user = wp_get_current_user();

    if ( ! empty( $user->roles ) ) {
        return $user->roles[0];
    }

    return '';
}

/**
 * Fetches and stores the API credentials required for Auto Integration.
 *
 * Sends a secure request to the GSheetConnector API endpoint, validates
 * the response, and saves the returned credentials in the WordPress
 * options table (or network options for multisite installations).
 *
 * @since 2.3.19
 * @return void
 */
public function save_api_credentials()
{
        // Create a nonce
    $nonce = wp_create_nonce('wcgsc_api_free_creds');

        // Prepare parameters for the API call
    $params = array(
        'action' => 'get_data',
        'nonce' => $nonce,
        'plugin' => 'WOOGSC',
        'method' => 'get',
    );

        // Add nonce and any other security parameters to the API request
    $api_url = esc_url_raw(add_query_arg($params, WC_GSHEETCONNECTOR_API_URL));

        // Make the API call using wp_remote_get
    $response = wp_remote_get($api_url);

        // Check for errors
    if (is_wp_error($response)) {
       self::gs_debug_log($response->get_error_message());
       return;

   } else {
            // API call was successful, process the data
    $response = wp_remote_retrieve_body($response);

    $decoded_response = json_decode($response);

    if ( json_last_error() !== JSON_ERROR_NONE ) {
        self::gs_debug_log('Invalid API JSON response');
        return;
    }

    if (isset($decoded_response->api_creds) && (!empty($decoded_response->api_creds))) {
        $api_creds = wp_parse_args($decoded_response->api_creds);
        if (is_multisite()) {
                    // If it's a multisite, update the site option (network-wide)
            update_site_option('wcgsc_api_free_creds', $api_creds);
        } else {
                    // If it's not a multisite, update the regular option
            update_option('wcgsc_api_free_creds', $api_creds);
        }
    }
}
}

/**
 * Logs plugin debug messages to the WooCommerce GSheetConnector error log.
 *
 * This method acts as a centralized debug logger and stores log entries
 * in the plugin's database-based error log system when it is available.
 *
 * @param mixed $error Debug message or error data to be logged.
 * @return void
 */
public static function gs_debug_log( $error ) {
    // Route debug messages to the database error-log store when available.
    if (class_exists('wcgsc_error_logs')) {
        wcgsc_error_logs::log_from_debug($error);
    }
}

/**
 * Renders the role selection checkboxes for plugin settings.
 *
 * Displays all available WordPress user roles as toggle checkboxes.
 * The Administrator role is always enabled and cannot be deselected.
 *
 * @param string $setting_name   Settings field name used for the checkbox inputs.
 * @param array  $selected_roles Array of previously selected roles.
 * @return void
 */   
public function wcgsc_checkbox_roles_multi($setting_name, $selected_roles) {
    $selected_row = '';
    $checked = '';
    $roles = array();
    $system_roles = $this->get_system_roles();

    if (!empty($selected_roles)) {
        foreach ($selected_roles as $role => $display_name) {
            array_push($roles, $role);
        }
    }

    

		// Static Administrator role (always enabled)
    $selected_row .= "<div class='gsc-role-card mb-10'>
    <div class='custom-check d-flex justify-between alien-center'>
    <label class='role-label gsc-switch'>" . esc_html__("Administrator", "wc-gsheetconnector") . "</label>
    <input type='checkbox' class='check-toggle' disabled='disabled' checked='checked'>
    <label class='button-toggle'></label>
    </div>
    </div>";

    foreach ($system_roles as $role => $display_name) {

        if ($role === "administrator") {
            continue;
        }

        $checked = '';
        if (!empty($roles) && is_array($roles) && in_array(esc_attr($role), $roles)) {
            $checked = "checked='checked'";
        }

        $selected_row .= "<div class='gsc-role-card mb-10'>
        <div class='custom-check d-flex justify-between alien-center'>
        
        <label class='role-label gsc-switch'>" . esc_html($display_name) . "</label>
        
        <input 
        type='checkbox' 
        class='check-toggle' 
        name='" . esc_attr($setting_name) . "[]' 
        value='" . esc_attr($role) . "' 
        $checked
        >
        
        <label class='button-toggle'></label>
        </div>
        </div>";
    }

        //  Properly escaped final output
    echo wp_kses_post( $selected_row );
}

/**
 * Retrieves the editable WordPress user roles used by the plugin.
 *
 * Excludes roles that should not be configurable for plugin access,
 * such as Subscriber, Customer, and Shop Manager.
 *
 * @since 1.1
 * @return array Array of role slugs mapped to their display names.
 */
public function get_system_roles()
{

    $participating_roles = array();
    $editable_roles      = get_editable_roles();

    foreach ($editable_roles as $role => $details) {

            // Remove unwanted roles
        if ('subscriber' === $role || 'customer' === $role || 'shop_manager' === $role) {
            continue;
        }

        $participating_roles[$role] = $details['name'];
    }

    return $participating_roles;
}


}
