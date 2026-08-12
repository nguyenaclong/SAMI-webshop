<?php
/**
 * Settings class for Gogglesheet Role settings
 * @since 1.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * WPF_Role_Settings Class
 * @since 1.0
 */
class wc_gsheetconnector_role_settings_free {

   /**
    * @var string group name
    */
   protected $gs_group_name = 'wcgsc-settings';

   /**
    * @var string roles that can access Google Sheet page
     @since 1.0
    */
     protected $gs_woo_page_roles_setting_option_name = 'wcgsc_page_roles_setting';

     
   /**
    * Set things up.
    * @since 1.0
    */
   public function __construct() {
    add_action('admin_init', array($this, 'init_settings'));
  }

   // White list our options using the Settings API
  public function init_settings() {
    register_setting('wcgsc-settings', $this->gs_woo_page_roles_setting_option_name, array($this, 'validate_wcgsc_access_roles'));
  }

   /**
    * do validate and sanitize selected participants
    * @param array $selected_roles
    * @return array $roles
    * @since 1.0
    */
   public function validate_wcgsc_access_roles($selected_roles) {
     $roles = array();
     $system_roles = wc_gsheetconnector_utility::instance()->get_system_roles();

     if ( ! empty($selected_roles) && is_array($selected_roles) ) {

      foreach ($system_roles as $role => $display_name) {
       if ( in_array($role, $selected_roles, true) ) {
        $roles[$role] = $display_name;
      }
    }
  }
  return $roles;
}

public function add_role_setting_page_free() {
  if ( ! current_user_can( 'manage_options' ) ){
   ?>
   <span class="per_not_allo"><?php echo esc_html( __( 'Permission Not Allowed', 'wc-gsheetconnector' ) ); ?></span>
   <?php
   return;
 }
 $gs_woo_page_roles = get_option( $this->gs_woo_page_roles_setting_option_name );
 ?>
 <form id="wcgsc_settings_form" method="post" action="options.php">
   <?php
   settings_fields( 'wcgsc-settings' );
   settings_errors();
   ?>
   <div class="wrap wcgsc-form" id="opener">
    <div class="card" id="googlesheet">
     <div class="wrap wcgsc-form">
      <div class="wcgsc-card">
       <h2> <?php esc_html_e( 'Roles that can access Google Sheet Page', 'wc-gsheetconnector' ); ?>
       <span class="pro-ver"><?php echo esc_html( __( 'PRO', 'wc-gsheetconnector' ) ); ?></span>
     </h2>
     <?php
     wc_gsheetconnector_utility::instance()->wcgsc_checkbox_roles_multi(
      $this->gs_woo_page_roles_setting_option_name . '[]',
      $gs_woo_page_roles
    );
    ?> 
    <div class="select-info">
      <input type="submit" class="button button-primary button-large" name="gs_woo_gs_settings"
      value="<?php echo esc_attr( __( 'Buy Pro', 'wc-gsheetconnector' ) ); ?>"/>
    </div>
  </div>
</div>
</div>
</div>
</form>

<?php include( WC_GSHEETCONNECTOR_PATH . 'includes/pages/pro-popup.php' );
}


}

$wcgsc_role_settings = new wc_gsheetconnector_role_settings_free();
