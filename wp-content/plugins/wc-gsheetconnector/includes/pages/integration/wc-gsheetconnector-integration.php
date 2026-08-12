<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly
// phpcs:disable
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe: tab selection used for UI only, no sensitive action
$wcgsc_code = "";
$wcgsc_auth_method = get_option('wcgsc_manual_setting', '0');
/*$wcgsc_header = admin_url('admin.php?page=wc-gsheetconnector-config');*/
$wcgsc_header = admin_url('admin.php?page=wc-gsheetconnector-config&tab=integration');

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth callback, safe usage
if (isset($_GET['code']) && is_string($_GET['code'])) {
  $wcgsc_code = sanitize_text_field(wp_unslash($_GET['code']));
  update_option('is_new_client_secret_wcgsc', 1);
}

// phpcs:enable
$wcgsc_verify = get_option('wcgsc_verify');
?>
<div class="heading mt-0 mb-0"><?php echo esc_html__(' Google Sheets Integration for WooCommerce', 'wc-gsheetconnector'); ?></div>
<!-- save code, alert and css -->
<div class="card-wcgsc dropdownoption-wcgsc border-select-box row align-end shadow-box mt-40 p-30">

  <div class="col-6">
    <div class="form-group">
      <label for="wcgsc_dro_option"><?php echo esc_html__('Choose Google API Setting', 'wc-gsheetconnector'); ?></label>
      <div class="drop-down-select-btn">
        <select id="wcgsc_dro_option" name="wcgsc_dro_option" class="gsc-select">
          <option value="wcgsc_existing" <?php selected($wcgsc_auth_method, 'wcgsc_existing'); ?>>
            <?php echo esc_html__('Existing Client / Secret Key (Auto Setup)', 'wc-gsheetconnector'); ?>
          </option>
          <option value="wcgsc_manual" <?php selected($wcgsc_auth_method, 'wcgsc_manual'); ?>>
            <?php echo esc_html__('Manual Client/Secret Key (Use Your Google API Configuration)', 'wc-gsheetconnector'); ?>
          </option>
          <option value="wcgsc_service" <?php selected($wcgsc_auth_method, 'wcgsc_service'); ?>>
            <?php echo esc_html__('Service Account (Recommended)', 'wc-gsheetconnector'); ?>
          </option>
        </select>
      </div>
    </div>
  </div>
  <!-- col-6 #end  -->
</div>
<!--card wsgs #end -->
<input type="hidden" name="redirect_auth" id="redirect_auth"
  value="<?php echo (isset($wcgsc_header)) ? esc_attr($wcgsc_header) : ''; ?>">
<div class="oauth-method row justify-between shadow-box mt-40 p-30">
  <div class="col-7">
    <div class="existing-method mr-20">

      <div class="card-wp">
        <div class="wcgsc-in-fields">
          <div class="heading mt-0">
            <?php esc_html_e('Google Account Connection', 'wc-gsheetconnector'); ?> <span class="badge"><?php echo esc_html__('Auto Setup', 'wc-gsheetconnector'); ?></span>
          </div>
          <p><?php esc_html_e('Sign in with your Google account to connect WooCommerce with Google Sheets. Once connected, your data syncs based on your settings. Learn more in the documentation', 'wc-gsheetconnector'); ?> <a href="https://www.gsheetconnector.com/docs/woocommerce-gsheetconnector/integration-with-google-existing-method" target="_blank" rel="noopener noreferrer"><?php echo esc_html__('Click here', 'wc-gsheetconnector'); ?></a>.</p>

          <?php if (empty(get_option('wcgsc_token'))) { ?>
            <div class="gsc-auth-steps mt-30">
              <div class="authentication-heading">
                <?php echo esc_html(__('Authenticate with Your Google Account', 'wc-gsheetconnector')); ?>
              </div>
              <ul>
                <li>
                  <?php
                  echo wp_kses_post(
                    __('Click on the <strong>Sign in with Google</strong> button.', 'wc-gsheetconnector')
                  );
                  ?>
                </li>
                <li>
                  <?php echo esc_html(__('Log in using your Google account.', 'wc-gsheetconnector')); ?>
                </li>
                <li>
                  <?php echo esc_html(__('Select the Google account where your Sheets are stored.', 'wc-gsheetconnector')); ?>
                </li>
                <li>
                  <?php echo esc_html(__('Grant access to:', 'wc-gsheetconnector')); ?>
                  <ul>
                    <li>
                      <?php echo esc_html(__('Google Drive', 'wc-gsheetconnector')); ?>
                    </li>
                    <li>
                      <?php echo esc_html(__('Google Sheets', 'wc-gsheetconnector')); ?>
                    </li>
                  </ul>
                </li>
                <li>
                  <?php
                  echo wp_kses_post(
                    __('Click <strong>Allow</strong> to finish authorization.', 'wc-gsheetconnector')
                  );
                  ?>
                </li>
                <li>
                  <?php echo esc_html(__('Save the authentication code if prompted.', 'wc-gsheetconnector')); ?>
                </li>
              </ul>
              <p class="gsc-auth-note mb-0">
                <?php echo esc_html(__('This allows the plugin to securely sync your form data with Google Sheets.', 'wc-gsheetconnector')); ?>
              </p>
            </div>
          <?php } ?>


          <?php
          if (!empty($wcgsc_verify) && $wcgsc_verify == "invalid-auth") { ?>
            <div class="gsc-msg wcgsc-free-permission-error gsc-error  pt-10 pb-10 manual-margin">
              <?php
              echo wp_kses_post(
                __(
                  '<p class="fw-400 text-dark"><strong>Google Drive</strong> and <strong>Google Sheets</strong> permissions were not granted during authentication with Google.</p>
                  <p class="fw-400 text-dark"><strong>Refer to Step 4 in the Connection Guide shown alongside.</strong><br> Then, deactivate the connection and re-authorize it, ensuring both Google Drive and Google Sheets permissions are enabled.</p>',
                  'wc-gsheetconnector'
                )
              );
              ?>
            </div>


            <?php } else {
            // If no permission issues, show connected email account or error message
            $wcgsc_token = get_option('wcgsc_token');
            if (!empty($wcgsc_token) && $wcgsc_token !== "") {
              $wcgsc_google_sheet = new GSCWOO_googlesheet();
              $wcgsc_connected_email = $wcgsc_google_sheet->gsheet_print_google_account_email();
              if ($wcgsc_connected_email) {
            ?>
                <div class="gswc-free-integration-box">
                  <div class="gsc-google-auth-card d-flex flex-wrap gap-20 justify-between align-center mt-30 mb-30">
                    <div class="gsc-google-auth-left d-flex flex-wrap align-center gap-15">
                      <div class="gsc-google-icon">G</div>
                      <div class="connected-account">
                        <div class="gsc-connected-left d-flex">
                          <span class="gsc-connected-label"><?php echo esc_html__('Connected Google Account', 'wc-gsheetconnector'); ?></span>
                          <span class="connected-account-manual gsc-connected-email"> <?php printf(wp_kses('<u>%s </u>', 'wc-gsheetconnector'), esc_attr($wcgsc_connected_email)); ?></span>
                        </div>
                      </div>
                    </div>
                    <div class="gsc-google-auth-right">
                      <div class="gsc-connected-pill">
                        <span class="dot"></span>
                        <?php esc_html_e('Connected', 'wc-gsheetconnector'); ?>
                      </div>
                    </div>
                  </div>
                </div>

              <?php } else { ?>
                <div class="gsc-msg gsc-error fw-400 text-dark text-center pt-10 pb-10 manual-margin">
                  <?php echo esc_html(__('Authentication failed. Your Google access token may be expired or invalid. Please re-authenticate your account with the required permissions.', 'wc-gsheetconnector')); ?>
                </div>
          <?php
              }
            }
          } ?>

          <?php
          // If token exists, show disabled input and deactivate button
          $wcgsc_token = get_option('wcgsc_token');
          if (!empty($wcgsc_token) && $wcgsc_token !== "") {
          ?>
            <div class="button-container mt-30">
              <button
                type="button"
                name="wcgsc-deactivate-log"
                id="wcgsc-deactivate-log"
                class="gsc-btn gsc-btn-gray btn deactivate-btn">
                <?php echo esc_html__('Deactivate', 'wc-gsheetconnector'); ?>
              </button>
              <span class="loading-sign-deactive"></span>
            </div>

          <?php } else {
           $wcgsc_auth_url =  WC_GSHEETCONNECTOR_AUTH_URL . "?client_admin_url=" . WC_GSHEETCONNECTOR_AUTH_REDIRECT_URI . "&plugin=" . WC_GSHEETCONNECTOR_AUTH_PLUGIN_NAME;
          ?>
            <?php if (empty($wcgsc_code)) { ?>
              <div class="gswc-free-integration-box">
                <div class="gsc-google-auth-card d-flex flex-wrap gap-20 justify-between align-center mt-30 mb-30">

                  <div class="gsc-google-auth-left d-flex flex-wrap align-center gap-15">
                    <div class="gsc-google-icon">G</div>
                    <div class="gsc-google-auth-text">
                      <strong><?php echo esc_html__('Connect Your Google Account', 'wc-gsheetconnector'); ?></strong>
                      <p><?php echo esc_html__('Securely link your Google account to start syncing form entries automatically.', 'wc-gsheetconnector'); ?></p>
                    </div>
                  </div>


                  <div class="gsc-google-auth-right">
                    <!-- SIGN IN WITH GOOGLE -->
                    <a href="<?php echo esc_html($wcgsc_auth_url); ?>"
                      class="gsc-google-btn link-hover-white">
                      <img
                        src="<?php echo esc_url(WC_GSHEETCONNECTOR_URL); ?>/assets/img/g-logo.png"
                        alt="Google">
                      <?php echo esc_html__('Sign in with Google', 'wc-gsheetconnector'); ?>
                    </a>
                  </div>

                </div>
              </div>
            <?php } ?>

          <?php } ?>

          <?php if (!empty($wcgsc_code)) { ?>
            <div class="gswc-free-integration-box">
              <div class="gsc-google-auth-card d-flex flex-wrap gap-20 justify-between align-center mt-30 mb-30">
                <div class="gsc-google-auth-left d-flex flex-wrap align-center gap-15">
                  <div class="gsc-google-icon">G</div>
                  <div class="gsc-google-auth-text">
                    <strong><?php echo esc_html__('Client Token', 'wc-gsheetconnector'); ?></strong>
                  </div>
                </div>
                <div class="gsc-google-auth-right">
                  <div class="token-box-width-exist">
                    <input type="password" id="wcgsc-code" name="wcgsc-code" class="form-control"
                      value="<?php echo esc_attr($wcgsc_code); ?>" disabled />
                  </div>
                </div>
              </div>
            </div>
            <div class="button-container mt-30">
              <button
                type="button"
                name="wcgsc-save-code"
                id="wcgsc-save-code"
                class="gsc-btn gsc-btn-primary btn btn-primary btn-pulse">
                <?php echo esc_html__('Save Client Token', 'wc-gsheetconnector'); ?>
              </button>
              <span class="loading-sign"></span>
            </div>
          <?php } ?>

           <div>
            <div id="gs-woo-validation-message"></div>
            <div id="deactivate-msg"></div>
          </div>
          <!-- privacy note #start -->
          <div class="gsc-privacy-note mt-30 pt-10 pb-10 text-dark d-flex gap-5">
            <div class="gsc-privacy-note-image">
              <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 14.5V16.5M7 10.0288C7.47142 10 8.05259 10 8.8 10H15.2C15.9474 10 16.5286 10 17 10.0288M7 10.0288C6.41168 10.0647 5.99429 10.1455 5.63803 10.327C5.07354 10.6146 4.6146 11.0735 4.32698 11.638C4 12.2798 4 13.1198 4 14.8V16.2C4 17.8802 4 18.7202 4.32698 19.362C4.6146 19.9265 5.07354 20.3854 5.63803 20.673C6.27976 21 7.11984 21 8.8 21H15.2C16.8802 21 17.7202 21 18.362 20.673C18.9265 20.3854 19.3854 19.9265 19.673 19.362C20 18.7202 20 17.8802 20 16.2V14.8C20 13.1198 20 12.2798 19.673 11.638C19.3854 11.0735 18.9265 10.6146 18.362 10.327C18.0057 10.1455 17.5883 10.0647 17 10.0288M7 10.0288V8C7 5.23858 9.23858 3 12 3C14.7614 3 17 5.23858 17 8V10.0288" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
            </div>
            <div>
              <?php echo esc_html(__('We do not store any of the data from your Google account on our servers, everything is processed & stored on your server. We take your privacy extremely seriously and ensure it is never misused. Learn more in the documentation', 'wc-gsheetconnector')); ?>
              <a href="https://gsheetconnector.com/usage-tracking/" target="_blank"
                rel="noopener noreferrer"><?php echo esc_html(__('click here.', 'wc-gsheetconnector')); ?>
              </a>
            </div>
          </div>
          <!-- privacy note #end -->
          <?php
          $wcgsc_google_sheet = new GSCWOO_googlesheet();
          $wcgsc_email_account = $wcgsc_google_sheet->gsheet_print_google_account_email();

          if ($wcgsc_email_account) { ?>
            <div class="gsc-connection-box mt-20">
              <div class="heading mt-0">
                <?php echo esc_html__('Connection Status & Next Steps', 'wc-gsheetconnector'); ?>
              </div>
              <p class="gsc-desc">
                <?php
                echo esc_html__(
                  'Your Google account has been successfully connected. You are now ready to sync your WooCommerce data with Google Sheets securely and automatically.',
                  'wc-gsheetconnector'
                );
                ?>
              </p>
              <div class="gsc-steps mb-0">
                <div class="gsc-step d-flex align-center gap-5">
                  <svg fill:#999999; width="12px" height="12px" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 16q0-3.232 1.28-6.208t3.392-5.12 5.12-3.392 6.208-1.28q3.264 0 6.24 1.28t5.088 3.392 3.392 5.12 1.28 6.208q0 3.264-1.28 6.208t-3.392 5.12-5.12 3.424-6.208 1.248-6.208-1.248-5.12-3.424-3.392-5.12-1.28-6.208zM8 16q0 3.328 2.336 5.664t5.664 2.336 5.664-2.336 2.336-5.664-2.336-5.632-5.664-2.368-5.664 2.368-2.336 5.632z"></path>
                  </svg>
                  <?php echo esc_html__('Create New Feed', 'wc-gsheetconnector'); ?>
                </div>
                <div class="gsc-step d-flex align-center gap-5">
                  <svg fill:#999999; width="12px" height="12px" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 16q0-3.232 1.28-6.208t3.392-5.12 5.12-3.392 6.208-1.28q3.264 0 6.24 1.28t5.088 3.392 3.392 5.12 1.28 6.208q0 3.264-1.28 6.208t-3.392 5.12-5.12 3.424-6.208 1.248-6.208-1.248-5.12-3.424-3.392-5.12-1.28-6.208zM8 16q0 3.328 2.336 5.664t5.664 2.336 5.664-2.336 2.336-5.664-2.336-5.632-5.664-2.368-5.664 2.368-2.336 5.632z"></path>
                  </svg>
                  <?php echo esc_html__('Select Google Spreadsheet', 'wc-gsheetconnector'); ?>
                </div>
                <div class="gsc-step d-flex align-center gap-5">
                  <svg fill:#999999; width="12px" height="12px" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 16q0-3.232 1.28-6.208t3.392-5.12 5.12-3.392 6.208-1.28q3.264 0 6.24 1.28t5.088 3.392 3.392 5.12 1.28 6.208q0 3.264-1.28 6.208t-3.392 5.12-5.12 3.424-6.208 1.248-6.208-1.248-5.12-3.424-3.392-5.12-1.28-6.208zM8 16q0 3.328 2.336 5.664t5.664 2.336 5.664-2.336 2.336-5.664-2.336-5.632-5.664-2.368-5.664 2.368-2.336 5.632z"></path>
                  </svg>
                  <?php echo esc_html__('Map WooCommerce Fields', 'wc-gsheetconnector'); ?>
                </div>
                <div class="gsc-step d-flex align-center gap-5">
                  <svg fill:#999999; width="12px" height="12px" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 16q0-3.232 1.28-6.208t3.392-5.12 5.12-3.392 6.208-1.28q3.264 0 6.24 1.28t5.088 3.392 3.392 5.12 1.28 6.208q0 3.264-1.28 6.208t-3.392 5.12-5.12 3.424-6.208 1.248-6.208-1.248-5.12-3.424-3.392-5.12-1.28-6.208zM8 16q0 3.328 2.336 5.664t5.664 2.336 5.664-2.336 2.336-5.664-2.336-5.632-5.664-2.368-5.664 2.368-2.336 5.632z"></path>
                  </svg>
                  <?php echo esc_html__('Enable Order Sync', 'wc-gsheetconnector'); ?>
                  <span class="gsc-pro-badge spacing-bdg-pro"><?php echo esc_html__('PRO', 'wc-gsheetconnector'); ?></span>
                </div>
              </div>
              <a href="https://www.gsheetconnector.com/woocommerce-google-sheet-connector-pro" target="_blank" class="btn btn-primary text-decoration-none mt-30 link-hover-white">
                <?php echo esc_html__('Upgrade to unlock', 'wc-gsheetconnector'); ?>
              </a>
            </div>
          <?php } ?>
        </div>

      </div>

    </div> <!-- existing-method #end  -->

    <div id="wcgsc-confirm-popup" class="wcgsc-popup-overlay d-none">
      <div class="wcgsc-popup text-center">

        <div class="gsc-modal-title">
          <?php echo esc_html__('Deactivate Integration', 'wc-gsheetconnector'); ?>
        </div>

        <p class="gsc-modal-text">
          <?php echo esc_html__('Are you sure you want to deactivate Google Sheets integration?', 'wc-gsheetconnector'); ?>
        </p>

        <div class="popup-actions d-flex justify-center gap-10">

          <button type="button"
            class="btn deactivate-btn"
            id="wcgsc-popup-cancel">
            <?php echo esc_html__('Cancel', 'wc-gsheetconnector'); ?>
          </button>

          <button type="button"
            class="btn btn-primary"
            id="wcgsc-popup-confirm">
            <?php echo esc_html__('Deactivate', 'wc-gsheetconnector'); ?>
          </button>

        </div>

      </div>
    </div>
  </div><!-- col 7 #end -->


  <div class="col-5">
    <div class="step-guide-col ml-20">
      <div class="heading mt-0"> <?php echo esc_html(__('Connection Guide', 'wc-gsheetconnector')); ?>
        <span class="badge"><?php echo esc_html(__('Step-by-Step', 'wc-gsheetconnector')); ?></span>
      </div>
      <p><?php echo esc_html__('Follow these steps to connect your Google account and start syncing your form data with Google Sheets.', 'wc-gsheetconnector'); ?>
      </p>
      <div class="gsc-slider-wrapper wc-free-connection-guide-slider mt-30">
        <div class="gsc-slider">
          <div class="gsc-slide">
            <div class="gsc-slider-headers fw-600 mb-10 text-dark">
              <?php esc_html_e('Step-1 Connect Your Google Account', 'wc-gsheetconnector'); ?>
              <a href="#" class="i-help" hover-tooltip="<?php echo esc_html__('Sign in with your Google account to start the automatic Google Sheets integration.', 'wc-gsheetconnector'); ?>">
                <svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M12 19.5C16.1421 19.5 19.5 16.1421 19.5 12C19.5 7.85786 16.1421 4.5 12 4.5C7.85786 4.5 4.5 7.85786 4.5 12C4.5 16.1421 7.85786 19.5 12 19.5ZM12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21ZM12.75 15V16.5H11.25V15H12.75ZM10.5 10.4318C10.5 9.66263 11.1497 9 12 9C12.8503 9 13.5 9.66263 13.5 10.4318C13.5 10.739 13.3151 11.1031 12.9076 11.5159C12.5126 11.9161 12.0104 12.2593 11.5928 12.5292L11.25 12.7509V14.25H12.75V13.5623C13.1312 13.303 13.5828 12.9671 13.9752 12.5696C14.4818 12.0564 15 11.3296 15 10.4318C15 8.79103 13.6349 7.5 12 7.5C10.3651 7.5 9 8.79103 9 10.4318H10.5Z" fill="#080341" />
                </svg>
              </a>
            </div>
            <a href="https://gmail.com/" target="_blank" class="link"><?php echo esc_html(__('Sign in with Google', 'wc-gsheetconnector')); ?></a>
            <img src="<?php echo esc_url(WC_GSHEETCONNECTOR_URL); ?>/assets/img/existing-step1.png" alt="" />
          </div>
          <div class="gsc-slide">
            <div class="gsc-slider-headers fw-600 mb-10 text-dark">
              <?php esc_html_e('Step-2 Choose Google Account', 'wc-gsheetconnector'); ?>
              <a href="#" class="i-help" hover-tooltip="<?php echo esc_html__('Choose the Google account where your Sheets are stored.', 'wc-gsheetconnector'); ?>">
                <svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M12 19.5C16.1421 19.5 19.5 16.1421 19.5 12C19.5 7.85786 16.1421 4.5 12 4.5C7.85786 4.5 4.5 7.85786 4.5 12C4.5 16.1421 7.85786 19.5 12 19.5ZM12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21ZM12.75 15V16.5H11.25V15H12.75ZM10.5 10.4318C10.5 9.66263 11.1497 9 12 9C12.8503 9 13.5 9.66263 13.5 10.4318C13.5 10.739 13.3151 11.1031 12.9076 11.5159C12.5126 11.9161 12.0104 12.2593 11.5928 12.5292L11.25 12.7509V14.25H12.75V13.5623C13.1312 13.303 13.5828 12.9671 13.9752 12.5696C14.4818 12.0564 15 11.3296 15 10.4318C15 8.79103 13.6349 7.5 12 7.5C10.3651 7.5 9 8.79103 9 10.4318H10.5Z" fill="#080341" />
                </svg>
              </a>
            </div>
            <a href="https://www.gsheetconnector.com/docs/woocommerce-gsheetconnector/integration-with-google-existing-method" target="_blank" class="link">
              <?php esc_html_e('check our detailed guideline', 'wc-gsheetconnector'); ?></a>
            <img src="<?php echo esc_url(WC_GSHEETCONNECTOR_URL); ?>/assets/img/existing-step2.png" alt="Step-2 Choose Google Account" />
          </div>
          <div class="gsc-slide">
            <div class="gsc-slider-headers fw-600 mb-10 text-dark">
              <?php esc_html_e('Step-3 Review Access Information', 'wc-gsheetconnector'); ?>
              <a href="#" class="i-help" hover-tooltip="<?php echo esc_html__('Google will show what information the plugin can access.', 'wc-gsheetconnector'); ?>">
                <svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M12 19.5C16.1421 19.5 19.5 16.1421 19.5 12C19.5 7.85786 16.1421 4.5 12 4.5C7.85786 4.5 4.5 7.85786 4.5 12C4.5 16.1421 7.85786 19.5 12 19.5ZM12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21ZM12.75 15V16.5H11.25V15H12.75ZM10.5 10.4318C10.5 9.66263 11.1497 9 12 9C12.8503 9 13.5 9.66263 13.5 10.4318C13.5 10.739 13.3151 11.1031 12.9076 11.5159C12.5126 11.9161 12.0104 12.2593 11.5928 12.5292L11.25 12.7509V14.25H12.75V13.5623C13.1312 13.303 13.5828 12.9671 13.9752 12.5696C14.4818 12.0564 15 11.3296 15 10.4318C15 8.79103 13.6349 7.5 12 7.5C10.3651 7.5 9 8.79103 9 10.4318H10.5Z" fill="#080341" />
                </svg>
              </a>
            </div>
            <a href="https://www.gsheetconnector.com/docs/woocommerce-gsheetconnector/integration-with-google-existing-method" target="_blank" class="link">
              <?php esc_html_e('check our detailed guideline', 'wc-gsheetconnector'); ?></a>
            <img src="<?php echo esc_url(WC_GSHEETCONNECTOR_URL); ?>/assets/img/existing-step3.png" alt="Step-3 Review Access Information" />
          </div>
          <div class="gsc-slide">
            <div class="gsc-slider-headers fw-600 mb-10 text-dark">
              <?php esc_html_e('Step-4 Grant Required Permissions', 'wc-gsheetconnector'); ?>
              <a href="#" class="i-help" hover-tooltip="<?php echo esc_html__('Allow required permissions for Google Sheets and Drive access.', 'wc-gsheetconnector'); ?>">
                <svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M12 19.5C16.1421 19.5 19.5 16.1421 19.5 12C19.5 7.85786 16.1421 4.5 12 4.5C7.85786 4.5 4.5 7.85786 4.5 12C4.5 16.1421 7.85786 19.5 12 19.5ZM12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21ZM12.75 15V16.5H11.25V15H12.75ZM10.5 10.4318C10.5 9.66263 11.1497 9 12 9C12.8503 9 13.5 9.66263 13.5 10.4318C13.5 10.739 13.3151 11.1031 12.9076 11.5159C12.5126 11.9161 12.0104 12.2593 11.5928 12.5292L11.25 12.7509V14.25H12.75V13.5623C13.1312 13.303 13.5828 12.9671 13.9752 12.5696C14.4818 12.0564 15 11.3296 15 10.4318C15 8.79103 13.6349 7.5 12 7.5C10.3651 7.5 9 8.79103 9 10.4318H10.5Z" fill="#080341" />
                </svg>
              </a>
            </div>
            <a href="https://www.gsheetconnector.com/docs/woocommerce-gsheetconnector/integration-with-google-existing-method" target="_blank" class="link">
              <?php esc_html_e('check our detailed guideline', 'wc-gsheetconnector'); ?></a>
            <img src="<?php echo esc_url(WC_GSHEETCONNECTOR_URL); ?>/assets/img/existing-step4.png" alt="Step-4 Grant Required Permissions" />
          </div>
          <div class="gsc-slide">
            <div class="gsc-slider-headers fw-600 mb-10 text-dark">
              <?php esc_html_e('Step-5 Save Authentication Code', 'wc-gsheetconnector'); ?>
              <a href="#" class="i-help" hover-tooltip="<?php echo esc_html__('Save the authentication code to complete the Google account connection.', 'wc-gsheetconnector'); ?>">
                <svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M12 19.5C16.1421 19.5 19.5 16.1421 19.5 12C19.5 7.85786 16.1421 4.5 12 4.5C7.85786 4.5 4.5 7.85786 4.5 12C4.5 16.1421 7.85786 19.5 12 19.5ZM12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21ZM12.75 15V16.5H11.25V15H12.75ZM10.5 10.4318C10.5 9.66263 11.1497 9 12 9C12.8503 9 13.5 9.66263 13.5 10.4318C13.5 10.739 13.3151 11.1031 12.9076 11.5159C12.5126 11.9161 12.0104 12.2593 11.5928 12.5292L11.25 12.7509V14.25H12.75V13.5623C13.1312 13.303 13.5828 12.9671 13.9752 12.5696C14.4818 12.0564 15 11.3296 15 10.4318C15 8.79103 13.6349 7.5 12 7.5C10.3651 7.5 9 8.79103 9 10.4318H10.5Z" fill="#080341" />
                </svg>
              </a>
            </div>
            <a href="https://www.gsheetconnector.com/docs/woocommerce-gsheetconnector/integration-with-google-existing-method" target="_blank" class="link">
              <?php esc_html_e('check our detailed guideline', 'wc-gsheetconnector'); ?></a>
            <img src="<?php echo esc_url(WC_GSHEETCONNECTOR_URL); ?>/assets/img/existing-step5.png" alt="Step-5 Save Authentication Code" />
          </div>
          <div class="gsc-slide">
            <div class="gsc-slider-headers fw-600 mb-10 text-dark">
              <?php esc_html_e('Step-6 Integration Completed', 'wc-gsheetconnector'); ?>
              <a href="#" class="i-help" hover-tooltip="<?php echo esc_html__('Your Google account is now successfully connected.', 'wc-gsheetconnector'); ?>">
                <svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M12 19.5C16.1421 19.5 19.5 16.1421 19.5 12C19.5 7.85786 16.1421 4.5 12 4.5C7.85786 4.5 4.5 7.85786 4.5 12C4.5 16.1421 7.85786 19.5 12 19.5ZM12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21ZM12.75 15V16.5H11.25V15H12.75ZM10.5 10.4318C10.5 9.66263 11.1497 9 12 9C12.8503 9 13.5 9.66263 13.5 10.4318C13.5 10.739 13.3151 11.1031 12.9076 11.5159C12.5126 11.9161 12.0104 12.2593 11.5928 12.5292L11.25 12.7509V14.25H12.75V13.5623C13.1312 13.303 13.5828 12.9671 13.9752 12.5696C14.4818 12.0564 15 11.3296 15 10.4318C15 8.79103 13.6349 7.5 12 7.5C10.3651 7.5 9 8.79103 9 10.4318H10.5Z" fill="#080341" />
                </svg>
              </a>
            </div>
            <a href="https://www.gsheetconnector.com/docs/woocommerce-gsheetconnector/integration-with-google-existing-method" target="_blank" class="link">
              <?php esc_html_e('check our detailed guideline', 'wc-gsheetconnector'); ?></a>
            <img src="<?php echo esc_url(WC_GSHEETCONNECTOR_URL); ?>/assets/img/existing-step6.png" alt="Step-6 Integration Completed" />
          </div>
        </div>
        <button class="gsc-nav prev">❮</button>
        <button class="gsc-nav next">❯</button>
      </div>
    </div>
    <!-- step-guide-col #end -->
  </div>
  <!-- col-5 #end -->
</div> <!-- oauth-method #end  -->
<?php
if (class_exists('wcgsc_error_logs')) {
  $wcgsc_logs = new wcgsc_error_logs();
  $wcgsc_logs->render_page_html();
}
?>
<div id="wcgsc-confirm-manual-popup-pro" class="wcgsc-popup-overlay d-none">
  <div class="wcgsc-popups position-relative-popup text-center">
    <button class="wcgsc-popup-close-pro gsc-pro-close">×</button>
    <div class="gsc-pro-section">
      <div class="gsc-pro-card">
        <div class="gsc-pro-headers">
          <span class="gsc-pro-badge">PRO</span>
          <div class="main-popup-heading mb-20 fw-600"><?php esc_html_e('Manual Google Sheets Integration', 'wc-gsheetconnector'); ?></div>
          <p class="mb-0 text-center"><?php echo esc_html__('Connect WooCommerce to Google Sheets using your own Google Cloud project. Ideal for advanced users who need full API control and custom OAuth setup.', 'wc-gsheetconnector'); ?></p>
        </div>
        <div class="gsc-pro-features">
          <!-- Feature 1 -->
          <div class="gsc-feature-item">
            <div class="gsc-feature-icon">
              <!-- API Key SVG -->
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                <path d="M21 10h-6l-2-2H3v8h10l2-2h6v-4z" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </div>
            <div class="gsc-feature-content">
              <div class="gsc-popup-header"><?php esc_html_e('Custom API Credentials', 'wc-gsheetconnector'); ?></div>
              <p><?php echo esc_html__('Connect using your own Google Cloud Client ID and Client Secret for full authentication control.', 'wc-gsheetconnector'); ?></p>
            </div>
          </div>
          <!-- Feature 2 -->
          <div class="gsc-feature-item">
            <div class="gsc-feature-icon">
              <!-- Shield OAuth SVG -->
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                <path d="M12 3l7 4v5c0 5-3.5 7.5-7 9-3.5-1.5-7-4-7-9V7l7-4z" stroke="#000" stroke-width="2" />
              </svg>
            </div>
            <div class="gsc-feature-content">
              <div class="gsc-popup-header"><?php esc_html_e('Secure Authentication', 'wc-gsheetconnector'); ?></div>
              <p><?php echo esc_html__('Authenticate directly with Google using a secure Authentication flow without third-party dependency.', 'wc-gsheetconnector'); ?></p>
            </div>
          </div>
          <!-- Feature 3 -->
          <div class="gsc-feature-item">
            <div class="gsc-feature-icon">
              <!-- Settings SVG -->
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                <path d="M12 8a4 4 0 100 8 4 4 0 000-8z" stroke="#000" stroke-width="2" />
                <path d="M2 12h2M20 12h2M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M19.1 4.9l-1.4 1.4M6.3 17.7l-1.4 1.4"
                  stroke="#000" stroke-width="2" />
              </svg>
            </div>
            <div class="gsc-feature-content">
              <div class="gsc-popup-header"><?php esc_html_e('Advanced Configuration', 'wc-gsheetconnector'); ?></div>
              <p><?php echo esc_html__('Set custom Redirect URIs, manage scopes, and configure API settings based on your project needs.', 'wc-gsheetconnector'); ?></p>
            </div>
          </div>
          <!-- Feature 4 -->
          <div class="gsc-feature-item">
            <div class="gsc-feature-icon">
              <!-- Support SVG -->
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                <path d="M18 8a6 6 0 10-12 0v4a2 2 0 002 2h1v-4H8a4 4 0 118 0h-1v4h1a2 2 0 002-2V8z"
                  stroke="#000" stroke-width="2" />
              </svg>
            </div>
            <div class="gsc-feature-content">
              <div class="gsc-popup-header"><?php esc_html_e('Priority Technical Support', 'wc-gsheetconnector'); ?></div>
              <p><?php echo esc_html__('Get fast assistance from our expert team for setup, troubleshooting, and optimization.', 'wc-gsheetconnector'); ?></p>
            </div>
          </div>
        </div>
        <div class="gsc-pro-actions justify-center d-flex flex-wrap gap-20 mt-20">
          <a href="https://www.gsheetconnector.com/woocommerce-google-sheet-connector-pro" target="_blank" class="btn btn-primary text-decoration-none link-hover-white"><?php echo esc_html__('Upgrade to Unlock', 'wc-gsheetconnector'); ?></a>
          <a href="https://www.gsheetconnector.com/docs/woocommerce-gsheetconnector/integration-with-google-manual-method" target="_blank" class="btn deactivate-btn text-decoration-none"><?php echo esc_html__('View Pro Features', 'wc-gsheetconnector'); ?></a>
        </div>
      </div>
    </div>
  </div>
</div>
<div id="wcgsc-confirm-service-popup-pro" class="wcgsc-popup-overlay d-none">
  <div class="wcgsc-popups position-relative-popup text-center">
    <button class="wcgsc-popup-service-close-pro gsc-pro-close">×</button>
    <div class="gsc-pro-section">
      <div class="gsc-pro-card">
        <div class="gsc-pro-headers">
          <span class="gsc-pro-badge">PRO</span>
          <div class="main-popup-heading mb-20 fw-600"><?php esc_html_e('Service Account Google Sheets Integration', 'wc-gsheetconnector'); ?></div>
          <p class="mb-0 text-center"><?php echo esc_html__('Connect WooCommerce to Google Sheets using a Service Account for automated syncing without manual login.', 'wc-gsheetconnector'); ?></p>
        </div>
        <div class="gsc-pro-features">
          <!-- Feature 1 -->
          <div class="gsc-feature-item">
            <div class="gsc-feature-icon">
              <!-- API Key SVG -->
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2l7 4v6c0 5-3.5 9-7 10-3.5-1-7-5-7-10V6l7-4z" />
                <circle cx="12" cy="12" r="2" />
                <path d="M14 12h4" />
              </svg>
            </div>
            <div class="gsc-feature-content">
              <div class="gsc-popup-header"><?php esc_html_e('Secure JSON Authentication', 'wc-gsheetconnector'); ?></div>
              <p><?php echo esc_html__('Authenticate using a secure Google Cloud JSON key file for direct and encrypted communication with Google Sheets.', 'wc-gsheetconnector'); ?></p>
            </div>
          </div>
          <!-- Feature 2 -->
          <div class="gsc-feature-item">
            <div class="gsc-feature-icon">
              <!-- Shield OAuth SVG -->
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2l7 4v6c0 5-3.5 9-7 10-3.5-1-7-5-7-10V6l7-4z" />
                <circle cx="12" cy="10" r="3" />
                <path d="M9 16c1-1 5-1 6 0" />
              </svg>
            </div>
            <div class="gsc-feature-content">
              <div class="gsc-popup-header"><?php esc_html_e('No Authentication Login Required', 'wc-gsheetconnector'); ?></div>
              <p><?php echo esc_html__('Enable automatic background syncing without requiring Google account sign-in during setup.', 'wc-gsheetconnector'); ?></p>
            </div>
          </div>
          <!-- Feature 3 -->
          <div class="gsc-feature-item">
            <div class="gsc-feature-icon">
              <!-- Settings SVG -->
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 3h12l4 4v14H4z" />
                <path d="M16 3v4h4" />
                <line x1="8" y1="13" x2="16" y2="13" />
                <line x1="8" y1="17" x2="16" y2="17" />
              </svg>
            </div>
            <div class="gsc-feature-content">
              <div class="gsc-popup-header"><?php esc_html_e('Direct Spreadsheet Access', 'wc-gsheetconnector'); ?></div>
              <p><?php echo esc_html__('Share your Google Sheet with the service account email to enable automatic data transfer.', 'wc-gsheetconnector'); ?></p>
            </div>
          </div>
          <!-- Feature 4 -->
          <div class="gsc-feature-item">
            <div class="gsc-feature-icon">
              <!-- Support SVG -->
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="6" rx="2" />
                <rect x="3" y="14" width="18" height="6" rx="2" />
                <circle cx="7" cy="7" r="1" />
                <circle cx="7" cy="17" r="1" />
              </svg>
            </div>
            <div class="gsc-feature-content">
              <div class="gsc-popup-header"><?php esc_html_e('Production-Ready & Reliable', 'wc-gsheetconnector'); ?></div>
              <p><?php echo esc_html__('Built for stable, uninterrupted syncing in professional and high-traffic environments.', 'wc-gsheetconnector'); ?></p>
            </div>
          </div>
        </div>
        <div class="gsc-pro-actions justify-center d-flex flex-wrap gap-20 mt-20">
          <a href="https://www.gsheetconnector.com/woocommerce-google-sheet-connector-pro" target="_blank" class="btn btn-primary text-decoration-none link-hover-white"><?php echo esc_html__('Upgrade to Unlock', 'wc-gsheetconnector'); ?></a>
          <a href="https://www.gsheetconnector.com/docs/woocommerce-gsheetconnector/service-account-setting-pro-version" target="_blank" class="btn deactivate-btn text-decoration-none"><?php echo esc_html__('View Pro Features', 'wc-gsheetconnector'); ?></a>
        </div>
      </div>
    </div>
  </div>
</div>