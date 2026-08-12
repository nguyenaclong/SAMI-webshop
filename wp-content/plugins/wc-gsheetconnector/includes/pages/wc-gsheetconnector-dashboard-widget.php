<?php
/*
 * GS Woocommerce Google sheet connector Dashboard Widget
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}
?>

<div class="dashboard-content">
 <?php
 $wcgsc_page_roles = get_option( 'wcgsc_page_roles_setting' );
 $wcgsc_sheet_data = get_option( 'wcgsc_sheet_feeds' );
 $wcgsc_settings   = get_option( 'wcgsc_settings' );

 $wcgsc_selected_sheet_key = ! empty( $wcgsc_settings ) ? $wcgsc_settings : '';

 $wcgsc_sheetName = __( 'Google Sheet Not Connected', 'wc-gsheetconnector' );

 if ( ! empty( $wcgsc_sheet_data ) && is_array( $wcgsc_sheet_data ) ) {
   foreach ( $wcgsc_sheet_data as $wcgsc_key => $wcgsc_value ) {
     if ( $wcgsc_selected_sheet_key !== '' && $wcgsc_key === $wcgsc_selected_sheet_key ) {
       $wcgsc_sheetName = isset( $wcgsc_value['sheet_name'] ) ? $wcgsc_value['sheet_name'] : '';
     }
   }
 }

 $wcgsc_sheet_url = '#';

 if ( is_string( $wcgsc_selected_sheet_key ) && ! empty( $wcgsc_selected_sheet_key ) ) {
  $wcgsc_sheet_url = 'https://docs.google.com/spreadsheets/d/' . $wcgsc_selected_sheet_key;
}
?>

<div class="main-content">
  <div class="wcgsc_dash_widget">
    <div class="wcgsc_conn_sheet">

      <table class="widget-table">
        <tbody>
          <tr>
            <th><?php esc_html_e( 'Sheet URL', 'wc-gsheetconnector' ); ?></th>
          </tr>
          <tr>
            <td>
              <a href="<?php echo esc_url( $wcgsc_sheet_url ); ?>" target="_blank" rel="noopener noreferrer">
                <?php echo esc_html( $wcgsc_sheetName ); ?>
              </a>
            </td>
          </tr>
        </tbody>
      </table>

      <table class="widget-table">
        <tbody>
          <tr>
            <th><?php esc_html_e( 'Permission To Access GSheetConnector WooCommerce', 'wc-gsheetconnector' ); ?></th>
          </tr>
          <tr>
            <td>
              <?php
              if ( ! empty( $wcgsc_page_roles ) && is_array( $wcgsc_page_roles ) ) {

                esc_html_e( 'Administrator', 'wc-gsheetconnector' );

                foreach ( $wcgsc_page_roles as $wcgsc_value ) {
                  echo ' ' . esc_html( $wcgsc_value );
                }

              } else {
               ?>
               <a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-gsheetconnector-config&tab=role_settings' ) ); ?>">
                 <?php esc_html_e( 'Administrator', 'wc-gsheetconnector' ); ?>
               </a>
               <?php
             }
             ?>
           </td>
         </tr>
       </tbody>
     </table>

   </div>
 </div>
</div>
</div>