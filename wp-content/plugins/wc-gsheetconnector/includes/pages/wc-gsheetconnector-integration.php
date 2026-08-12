<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// phpcs:disable
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe: tab selection used for UI only, no sensitive action
$Code = "";
$header = admin_url('admin.php?page=wc-gsheetconnector-config');
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth callback, safe usage
if ( isset( $_GET['code'] ) && is_string( $_GET['code'] ) ) {
  $Code = sanitize_text_field( wp_unslash( $_GET['code'] ) );
  update_option( 'is_new_client_secret_wcgsc', 1 );
}

// phpcs:enable
?>

<!-- save code, alert and css -->
<div class="card-wcgsc dropdownoption-wcgsc">
	
	<h2><?php echo esc_html__('Google Sheet Integration - GSheetConnector for WooCommerce', 'wc-gsheetconnector'); ?></h2>
	<p class="sub-desc">
    <?php 
    echo wp_kses_post(
      sprintf(
        /* translators: %s is the URL to the pricing page for upgrading to Pro. */
        __(
          'Choose your Google API Setting from the dropdown. In the Free version, only the <strong>Auto Google API Configuration (Use Existing Client/Secret Key)</strong> option is available.
          The other methods – <strong>Manual Client/Secret Key</strong> and <strong>Service Account</strong> – are exclusive features of the Pro version. 
          You can <a href="%s" target="_blank" rel="noopener noreferrer">upgrade to Pro</a> to unlock these advanced options.
          After saving your selected setting, the related integration options will appear, allowing you to complete the setup easily.',
          'wc-gsheetconnector'
        ),
        esc_url( 'https://www.gsheetconnector.com/pricing/' )
      )
    );
    ?>
  </p>
  
  <div class="row">
    <label for="wcgsc_dro_option"><?php echo esc_html__('Choose Google API Setting ', 'wc-gsheetconnector'); ?></label>
    
    <div class="drop-down-select-btn">
      <select id="wcgsc_dro_option" name="wcgsc_dro_option">
        <option value="wcgsc_existing" selected><?php echo esc_html__('Use Existing Client/Secret Key (Auto Google API Configuration)', 'wc-gsheetconnector'); ?>
      </option>
      <option value="wcgsc_manual" disabled=""><?php echo esc_html__('Use Manual Client/Secret Key (Use Your Google API Configuration) (Upgrade To PRO)', 'wc-gsheetconnector'); ?></option>
      <option value="wcgsc_service" disabled=""><?php echo esc_html__('Service Account (Recommended) (Upgrade To PRO)', 'wc-gsheetconnector'); ?></option>
    </select>
    <p class="int-meth-btn-wcgs"><a href="https://www.gsheetconnector.com/woocommerce-google-sheet-connector-pro" target="_blank" rel="noopener noreferrer"><input type="button" name="save-method-api-wcgs" id="save-method-api-wcgs"
      value="<?php esc_html_e('Upgrade To PRO', 'wc-gsheetconnector'); ?>" class="upgrade-btn" />
    </a>
    
  </p>
</div>
</div>

</div> 
<input type="hidden" name="redirect_auth" id="redirect_auth"
value="<?php echo (isset($header)) ? esc_attr($header) : ''; ?>">
<div class="card-wp">
  <div class="wcgsc-in-fields">
    <h2> <?php esc_html_e( 'Google Sheet Integration - Use Existing Client/Secret Key (Auto Google API Configuration)', 'wc-gsheetconnector' ); ?></h2>
    
    <p class="sub-desc"><?php esc_html_e( 'Automatic integration allows you to connect Woocommerce with Google Sheets using built-in Google API configuration. By authorizing your Google account, the plugin will handle API setup and authentication automatically, enabling seamless form data sync. Learn more in the documentation', 'wc-gsheetconnector' ); ?> <a href="https://www.gsheetconnector.com/docs/woocommerce-gsheetconnector/integration-with-google-existing-method" target="_blank" rel="noopener noreferrer"><?php echo esc_html__('Click here', 'wc-gsheetconnector'); ?></a>.</p>

    
    <?php if (empty($Code)) { ?>
      <div class="wcgsc-alert-kk" id="google-drive-msg">
        <p class="wcgsc-alert-heading"> <?php echo esc_html__('Authenticate with your Google account, follow these steps:', 'wc-gsheetconnector'); ?> </p>
        <ol class="wcgsc-alert-steps">
          <li><?php echo esc_html__('Click on the "Sign In With Google" button.', 'wc-gsheetconnector'); ?></li>
          <li><?php echo esc_html__('Grant permissions for the following:', 'wc-gsheetconnector'); ?>
          <ul class="wcgsc-alert-permissions">
            <li><?php echo esc_html__('Google Drive', 'wc-gsheetconnector'); ?></li>
            <li><?php echo esc_html__('Google Sheets', 'wc-gsheetconnector'); ?> <span class="wcgsc-alert-note"> <?php echo esc_html__('Ensure that you enable the checkbox for each of these services.', 'wc-gsheetconnector'); ?> </span></li>
          </ul>
        </li>
        <li><?php echo esc_html__('This will allow the integration to access your Google Drive and Google Sheets.', 'wc-gsheetconnector'); ?> </li>
      </ol>
    </div>
  <?php } ?>
  <div class="row">
    <label> <?php echo esc_html__('Google Access Code', 'wc-gsheetconnector'); ?> </label>
    <?php 
    $wcgsc_token = get_option('wcgsc_token');

    if ( ! empty( $wcgsc_token ) ) { ?>
      <input type="text" name="wcgsc-code" id="wcgsc-code" value=""
      placeholder="<?php esc_html_e('Currently Active', 'wc-gsheetconnector'); ?>" disabled />
      <input type="button" name="wcgsc-deactivate-log" id="wcgsc-deactivate-log"
      value="<?php esc_html_e('Deactivate', 'wc-gsheetconnector'); ?>" class="button button-primary" />
      
      
    </span> </span> <span class="loading-sign-deactive">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
  <?php } else {
    $wcgsc_redirct_uri = admin_url('admin.php?page=wc-gsheetconnector-config');
    ?>
    <input type="text" name="wcgsc-code" id="wcgsc-code" value="<?php echo esc_attr($Code); ?>" readonly placeholder="<?php echo esc_html__('Click on Sign In With Google', 'wc-gsheetconnector'); ?>" oncopy="return false;" onpaste="return false;" oncut="return false;" />
    <?php if (empty($Code)) { ?>
      <a href="<?php echo esc_url( 'https://oauth.gsheetconnector.com/index.php?client_admin_url=' . rawurlencode( $wcgsc_redirct_uri ) . '&plugin=woocommercegsheetconnector' ); ?>"
       class="wcgsc_button">
       <img class="wcgsc_button" src="<?php echo esc_url( WC_GSHEETCONNECTOR_URL . '/assets/img/btn_google_signin_dark_pressed_web.gif' ); ?>" />
     </a>

   <?php } ?>
 <?php } ?>
 
 <?php
      // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth callback, nonce not required
 if ( ! empty( $Code ) ) {
  ?>

  <button type="button" name="wcgsc-save-code" class="blinking-button-wc" id="wcgsc-save-code"><?php echo esc_html__('Click here to Save Authentication Code', 'wc-gsheetconnector'); ?></button>
<?php } ?>
<span class="loading-sign">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> </div>
<span id="deactivate-msg"></span>
<input type="hidden" name="wcgsc-ajax-nonce" id="wcgsc-ajax-nonce"
value="<?php echo esc_attr( wp_create_nonce( 'wcgsc-ajax-nonce' ) ); ?>" />

<?php
            //resolved - google sheet permission issues - START
$wcgsc_verify = get_option('wcgsc_verify');
if (!empty($wcgsc_verify) && $wcgsc_verify == "invalid-auth") {
  ?>
  <p style="color:#c80d0d; font-size: 14px; border: 1px solid;padding: 8px;"> 
    <?php echo esc_html(__('Something went wrong! It looks you have not given the permission of Google Drive and Google Sheets from your google account.Please Deactivate Auth and Re-Authenticate again with the permissions.','wc-gsheetconnector')); ?></p>
    <p style="color:#c80d0d;border: 1px solid;padding: 8px;"><img width="350px"
      src="<?php echo esc_url( WC_GSHEETCONNECTOR_URL . 'assets/img/permission_screen.png' ); ?>"></p>
      <p style="color:#c80d0d; font-size: 14px; border: 1px solid;padding: 8px;"> <?php echo esc_html(__('Also,', 'wc-gsheetconnector')); ?><a href="https://myaccount.google.com/permissions"
        target="_blank" rel="noopener noreferrer"> <?php echo esc_html(__('Click Here ', 'wc-gsheetconnector')); ?></a> <?php echo esc_html(__(' and if it displays "GSheetConnector for WooCommerce" under Third-party apps with account access then remove it.', 'wc-gsheetconnector')); ?> </p>
        <?php
            } // Close the if condition
            //resolved - google sheet permission issues - END
            else {
              $wcgsc_token = get_option('wcgsc_token');

              if ( ! empty( $wcgsc_token ) ) {
                $google_sheet = new GSCWOO_googlesheet();
                $wcgsc_email_account = $google_sheet->gsheet_print_google_account_email();
                if ($wcgsc_email_account) {
                  ?>
                  <div class="connected-account row">
                    <label>
                      <?php
                      echo esc_html__( 'Connected Email Account', 'wc-gsheetconnector' );
                      ?>
                    </label>

                    <span class='gfgsc-service-email'>
                      <?php
                      echo esc_html( $wcgsc_email_account );
                      ?>
                    </span>
                  </div>

                  <?php
                } else {
                  ?>
                  <p style="color:red"> <?php echo esc_html(__('Something went wrong! Your Auth code may be wrong or expired. Please Deactivate and Re-Authenticate.', 'wc-gsheetconnector')); ?> </p>
                  <?php
                }
              }
            }
            ?>
            <?php 
            $wcgsc_verify = get_option('wcgsc_verify');

            if ( ! empty( $wcgsc_verify ) && $wcgsc_verify === "valid" ) { ?>
              <p class="wcgsc-sync-row">
                <?php
                printf(
            // translators: %s is the HTML <a> link for syncing WooCommerce settings.
                  esc_html__( 'Spreadsheet Name and URL not showing? %s to fetch sheets', 'wc-gsheetconnector' ),
                  '&nbsp;<a id="wcgsc-sync" data-init="yes">' . esc_html__( ' Click here ', 'wc-gsheetconnector' ) . '</a>&nbsp;'
                );
                ?>
                <span class="loading-sign">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
              </p> 
              

            <?php } 
            ?>

            <p>
             <label><?php esc_html_e('Debug Log', 'wc-gsheetconnector'); ?></label>
             <button class="wcgsc-logs"><?php echo esc_html__('View', 'wc-gsheetconnector'); ?></button>

             <label><a class="debug-clear"><?php echo esc_html__('Clear', 'wc-gsheetconnector'); ?></a></label>
             <span
             class="clear-loading-sign">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
             <p id="wcgsc-validation-message"></p>
           </p>
           
           
           
           
           <div class="msg success-msg">
            <i class="fa-solid fa-lock"></i>
            <p><?php echo esc_html__('We do not store any of the data from your Google account on our servers, everything is processed &amp; stored on your server. We take your privacy extremely seriously and ensure it is never misused.', 'wc-gsheetconnector'); ?>
            <a href="https://gsheetconnector.com/usage-tracking/" target="_blank" rel="noopener noreferrer">
              <?php echo esc_html__('Learn more', 'wc-gsheetconnector'); ?>. </a>
            </p>
          </div>
          
          
          
          
        </div>
      </div>

      <!-- display content error logs -->
      <div class="wc-system-Error-logs">
        <button id="copy-logs-btn" onclick="copyLogs()"><?php echo esc_html__('Copy Logs', 'wc-gsheetconnector'); ?></button>
        <div class="wcdisplayLogs" id="log-content">
          <?php
          $wcgscexistDebugFile = get_option('wcfgs_debug_log_file');
        // check if debug unique log file exists or not
          if (!empty($wcgscexistDebugFile) && file_exists($wcgscexistDebugFile)) {
            if ( ! function_exists( 'WP_Filesystem' ) ) {
              require_once ABSPATH . 'wp-admin/includes/file.php';
            }

            global $wp_filesystem;
            WP_Filesystem();

            $wcgscdisplayfreeLogs = $wp_filesystem->get_contents( $wcgscexistDebugFile );
            if (!empty($wcgscdisplayfreeLogs)) {
              // Escape output properly while preserving new lines (convert new lines to <br> after escaping)
             echo wp_kses_post( nl2br( esc_html( $wcgscdisplayfreeLogs ) ) );
           } else {
            echo esc_html(__('No errors found.', 'wc-gsheetconnector'));
          }
        } else {
            // check if debug unique log file does not exist
          echo esc_html(__('No log file exists as no errors are generated.', 'wc-gsheetconnector'));
        }
        ?>
      </div>
    </div>

    <div class="two-col wc-free-box-help12">
      <div class="col wc-free-box12">
        <header>
          <h3>
            <?php
        // Translators: Section heading "Next steps…"
            echo esc_html__( 'Next steps…', 'wc-gsheetconnector' );
            ?>
          </h3>
        </header>

        <div class="wc-free-box-content12">
          <ul class="wc-free-list-icon12">
            <li>
              <a href="https://www.gsheetconnector.com/woocommerce-google-sheet-connector-pro" target="_blank" rel="noopener noreferrer">
                <div>
                  <button class="icon-button">
                    <span class="dashicons dashicons-star-filled"></span>
                  </button>
                  <strong><?php echo esc_html__( 'Upgrade to PRO', 'wc-gsheetconnector' ); ?></strong>
                  <p><?php echo esc_html__( 'Sync Orders, Order wise data and much more...', 'wc-gsheetconnector' ); ?></p>
                </div>
              </a>
            </li>

            <li>
              <a href="https://www.gsheetconnector.com/docs/woocommerce-google-sheet-connector-pro/requirements" target="_blank" rel="noopener noreferrer">
                <div>
                  <button class="icon-button">
                    <span class="dashicons dashicons-download"></span>
                  </button>
                  <strong><?php echo esc_html__( 'Compatibility', 'wc-gsheetconnector' ); ?></strong>
                  <p><?php echo esc_html__( 'Compatibility with WooCommerce Third-Party Plugins', 'wc-gsheetconnector' ); ?></p>
                </div>
              </a>
            </li>

            <li>
              <a href="https://www.gsheetconnector.com/docs/woocommerce-gsheetconnector/woocommerce-data-settings-pro-version" target="_blank" rel="noopener noreferrer">
                <div>
                  <button class="icon-button">
                    <span class="dashicons dashicons-chart-bar"></span>
                  </button>
                  <strong><?php echo esc_html__( 'Multi Languages', 'wc-gsheetconnector' ); ?></strong>
                  <p><?php echo esc_html__( 'This plugin supports multi-languages as well!', 'wc-gsheetconnector' ); ?></p>
                </div>
              </a>
            </li>

            <li>
              <a href="https://www.gsheetconnector.com/docs/woocommerce-gsheetconnector/woocommerce-data-settings-free-version" target="_blank" rel="noopener noreferrer">
                <div>
                  <button class="icon-button">
                    <span class="dashicons dashicons-download"></span>
                  </button>
                  <strong><?php echo esc_html__( 'Support WordPress multisites', 'wc-gsheetconnector' ); ?></strong>
                  <p><?php echo esc_html__( 'With the use of a Multisite, you’ll also have a new level of user-available: the Super Admin.', 'wc-gsheetconnector' ); ?></p>
                </div>
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- 2nd column -->
      <div class="col wc-free-box13">
        <header>
          <h3>
            <?php
        // Translators: Section heading "Product Support"
            echo esc_html__( 'Product Support', 'wc-gsheetconnector' );
            ?>
          </h3>
        </header>

        <div class="wc-free-box-content13">
          <ul class="wc-free-list-icon13">
            <li>
              <a href="https://www.gsheetconnector.com/docs/woocommerce-gsheetconnector" target="_blank" rel="noopener noreferrer">
                <div>
                  <span class="dashicons dashicons-book"></span>
                  <strong><?php echo esc_html__( 'Online Documentation', 'wc-gsheetconnector' ); ?></strong>
                  <p><?php echo esc_html__( 'Understand all the capabilities of Woocommerce GsheetConnector', 'wc-gsheetconnector' ); ?></p>
                </div>
              </a>
            </li>

            <li>
              <a href="https://www.gsheetconnector.com/support" target="_blank" rel="noopener noreferrer">
                <div>
                  <span class="dashicons dashicons-sos"></span>
                  <strong><?php echo esc_html__( 'Ticket Support', 'wc-gsheetconnector' ); ?></strong>
                  <p><?php echo esc_html__( 'Direct help from our qualified support team', 'wc-gsheetconnector' ); ?></p>
                </div>
              </a>
            </li>

            <li>
              <a href="https://www.gsheetconnector.com/affiliates" target="_blank" rel="noopener noreferrer">
                <div>
                  <span class="dashicons dashicons-admin-links"></span>
                  <strong><?php echo esc_html__( 'Affiliate Program', 'wc-gsheetconnector' ); ?></strong>
                  <p><?php echo esc_html__( 'Earn flat 30% on every sale!', 'wc-gsheetconnector' ); ?></p>
                </div>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
