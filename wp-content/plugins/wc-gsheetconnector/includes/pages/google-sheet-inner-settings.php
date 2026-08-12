<?php

/*
 * Google Sheet configuration and settings page
 * @since 1.0
 */
if (!defined('ABSPATH')) {
  exit;
}
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- safe, only reading tab value
$wcgsc_active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'integration';

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- safe, only reading tab value
$wcgsc_sub_tab = isset( $_GET['sub_tab'] ) ? sanitize_key( wp_unslash( $_GET['sub_tab'] ) ) : 'role_settings';

switch ($wcgsc_active_tab) {
  case 'wc_settings':
        // Render sub-navigation
  echo '</div><div class="sub-nav-bar">';
  $wcgsc_sub_tabs = array(
    'role_settings' => esc_html__( 'Role Settings', 'wc-gsheetconnector' ),
    'beta_version'  => esc_html__( 'Beta Version Controls', 'wc-gsheetconnector' ),
    'system_status' => esc_html__( 'System Status', 'wc-gsheetconnector' ),
  );

  foreach ($wcgsc_sub_tabs as $wcgsc_sub => $wcgsc_label) {
    $wcgsc_class = ($wcgsc_sub === $wcgsc_sub_tab) ? 'sub-nav-tab sub-nav-tab-active' : 'sub-nav-tab';
    echo '<a class="' . esc_attr( $wcgsc_class ) . '" href="' . esc_url( admin_url( 'admin.php?page=wc-gsheetconnector-config&tab=wc_settings&sub_tab=' . urlencode( $wcgsc_sub ) ) ) . '">' . esc_html( $wcgsc_label ) . '</a>';
  }
  echo '</div> <div class="wrap-gsc">';

        // Load correct sub-tab content
  switch ($wcgsc_sub_tab) {
    case 'role_settings':
    $wcgsc_role_settings = new wc_gsheetconnector_role_settings_free();
    $wcgsc_role_settings->add_role_setting_page_free();
    break;
    case 'beta_version':
    include_once WC_GSHEETCONNECTOR_PATH . 'includes/pages/wc-beta-version.php';
    break;
    case 'system_status':
    include_once WC_GSHEETCONNECTOR_PATH . 'includes/pages/wc-gsheetconnector-systeminfo.php';
    break;

  }
  break;

}


?>