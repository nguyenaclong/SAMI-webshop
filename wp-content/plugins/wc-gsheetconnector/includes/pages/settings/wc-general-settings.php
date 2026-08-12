<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
   exit;
}

// Get the saved value from the options table
$wcgsc_unistall_settings_saved_value = get_option('wcgsc_unistall_plugin_settings', 'No'); // Default to 'No' if option is not set
?>

<div class="wrap w-100 m-0">
   <div class="system-general_setting  inner-wrap w-100 bg-white p-40">
      <div class="ffinfo-container">
        
            <div class="gsc-access-wrapper">
               <div>
                  <div class="heading mt-0">
                     <?php echo esc_html__('Plugin Preferences', 'wc-gsheetconnector'); ?>
                  </div>
                  <p><?php echo esc_html(__('Manage how plugin settings and data are handled when the plugin is uninstalled.', 'wc-gsheetconnector')); ?>
                  <div class="gsc-setting-text d-flex flex-wrap gap-20 justify-between align-center pt-15 pb-15 mt-30 bg-white">
                     <div>
                        <div class="systemifo fw-600 text-dark">
                           <?php echo esc_html__("Delete Plugin Data on Uninstall", 'wc-gsheetconnector'); ?>
                        </div>
                        <label for="gs_woo_unistall_settings" class="fw-400">
                           <?php echo esc_html__("Removes all plugin data (options, metadata) when the plugin is deleted.", 'wc-gsheetconnector'); ?>
                        </label>
                     </div>
                     <div>
                        <input type="hidden" name="gs_woo_unistall_settings" value="No">
                        <div class="custom-check">
                           <input type="checkbox"
                              class="check-toggle"
                              id="gs_woo_unistall_settings"
                              name="gs_woo_unistall_settings"
                              value="Yes"
                              <?php echo ($wcgsc_unistall_settings_saved_value === 'Yes') ? 'checked' : ''; ?>>

                           <label for="gs_woo_unistall_settings" class="button-toggle"></label>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="gsc-access-info">
                  <div class='para-heading fw-600 mb-20'><?php esc_html_e('Uninstall Data Notice', 'wc-gsheetconnector'); ?></div>
                  <ul class="mb-0">
                     <li>
                        <?php echo esc_html__('Enable this option only if you want a complete cleanup', 'wc-gsheetconnector'); ?>
                     </li>
                     <li>
                        <?php echo esc_html__('All plugin settings and data will be permanently removed', 'wc-gsheetconnector'); ?>
                     </li>
                     <li class="mb-0">
                        <?php echo esc_html__('Incorrect settings can delete all sensitive form and user data', 'wc-gsheetconnector'); ?>
                     </li>
                  </ul>


               </div>
            </div>
            <div class="text-right mt-30">
               <span class="wcgsc-loading-uninstall"></span>
               <input type="button" class="btn btn-primary wcgsc-uninstall-settings-save"
                  name="gs_woo_save_unistall_settings" id="gs_woo_save_unistall_settings"
                  value="<?php echo esc_html__("Save Settings", 'wc-gsheetconnector'); ?>" />

               <div id="wcgsc-uninstall-msg" class="gsc-msg d-none fw-400 text-dark text-center pt-10 pb-10 manual-margin"></div>
               <input type="hidden"
                  name="wcgsc-uninstall-ajax-nonce"
                  id="wcgsc-uninstall-ajax-nonce"
                  value="<?php echo esc_attr(wp_create_nonce('wcgsc-uninstall-ajax-nonce')); ?>" />
            </div>
      </div>
     
   </div>


   <div id="wcgsc-uninstall-free" class="wcgsc-popup-overlay d-none">
      <div class="wcgsc-popup text-center free-to-pro-data">

         <div class="gsc-modal-title  gsc-uninstall-modal-title"><?php echo esc_html__('Confirm Data Deletion', 'wc-gsheetconnector'); ?></div>
         <p class="gsc-modal-text"><?php echo esc_html__('Enabling this option will permanently delete all plugin data, including settings and integrations, when the plugin is uninstalled.', 'wc-gsheetconnector'); ?>
         </p>
         <p class="gsc-modal-text"><?php echo esc_html__('If you plan to upgrade from Free to Pro, you may lose your existing configuration data.', 'wc-gsheetconnector'); ?>
         </p>

         <p class="gsc-modal-text"><?php echo esc_html__('Proceed only if you want a complete cleanup. This action cannot be undone.', 'wc-gsheetconnector'); ?>
         </p>



         <div class="popup-actions d-flex justify-center gap-10">
            <button class="btn deactivate-btn" id="wcgsc-free-uninstall-cancel">
               <?php esc_html_e('Cancel', 'wc-gsheetconnector'); ?>
            </button>
            <button class="btn btn-primary" id="wcgsc-confirm-enable-uninstall">
               <?php esc_html_e('Enable Deletion', 'wc-gsheetconnector'); ?>
            </button>
         </div>
      </div>
   </div>
</div>
<!-- uninstall plugin settings end -->

<!-- Row Input Format Option for Product description and Product Short Description -->
<div class="wrap w-100 m-0 blur-pro-feature">
   <div class="system-general_setting  inner-wrap w-100 bg-white p-40">
      <div class="ffinfo-container">
         <form method="post">
            <div class="gsc-access-wrapper">
               <div>
                  <div class="heading mt-0">
                     <?php echo esc_html__('Description Input Format Settings', 'wc-gsheetconnector'); ?>
                     <span class="pro-ver"><?php echo esc_html__('PRO', 'wc-gsheetconnector'); ?></span>
                  </div>
                  <p><?php echo esc_html(__('Enable this option to allow HTML formatting in Product Description and Short Description fields. When enabled, HTML content from the sheet will be imported as is. Disable it to use plain text only.', 'wc-gsheetconnector')); ?>
                  <div class="gsc-setting-text d-flex flex-wrap gap-20 justify-between align-center pt-15 pb-15 mt-30 bg-white">
                     <div>
                        <div class="systemifo fw-600 text-dark">
                           <?php echo esc_html__("Enable HTML Formatting for Description Fields", 'wc-gsheetconnector'); ?>
                        </div>
                        <label for="gs_woo_preserve_html_fields" class="fw-400">
                           <?php echo esc_html__("Allow HTML content in Product Description and Short Description fields during import.", 'wc-gsheetconnector'); ?>
                        </label>
                     </div>
                     <div>
                        <input type="hidden" name="gs_woo_preserve_html_fields" value="No">
                        <div class="custom-check">
                           <input type="checkbox"
                              class="check-toggle"
                              id="gs_woo_preserve_html_fields"
                              name="gs_woo_preserve_html_fields"
                              value="Yes"><label for="gs_woo_preserve_html_fields" class="button-toggle"></label>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="gsc-access-info">
                  <div class='para-heading fw-600 mb-20'>
                     <?php esc_html_e('HTML Formatting Guide', 'wc-gsheetconnector'); ?>
                  </div>
                  <ul class="mb-0">
                     <li>
                        <?php echo esc_html__('Enable this option to allow HTML content in Product Description and Short Description fields.', 'wc-gsheetconnector'); ?>
                     </li>
                     <li>
                        <?php echo esc_html__('When enabled, HTML tags from the sheet (e.g., <p>, <br>, <strong>) will be imported as is.', 'wc-gsheetconnector'); ?>
                     </li>
                     <li>
                        <?php echo esc_html__('Disable this option to import plain text without HTML formatting.', 'wc-gsheetconnector'); ?>
                     </li>
                     <li class="mb-0">
                        <?php echo esc_html__('Use this option only if your sheet contains valid HTML content.', 'wc-gsheetconnector'); ?>
                     </li>
                  </ul>
               </div>
            </div>
            <div class="text-right mt-30">
               <span class="wcgsc-loading-preserve-html-fields"></span>
               <input type="button" class="btn btn-primary preserve-html-fields-save"
                  name="gs_woo_save_preserve_html_fields" id="gs_woo_save_preserve_html_fields"
                  value="<?php echo esc_html__("Save Settings", 'wc-gsheetconnector'); ?>" />

               <div id="wcgsc-preserve-html-fields-msg" class="gsc-msg d-none fw-400 text-dark text-center pt-10 pb-10 manual-margin"></div>
               <input type="hidden"
                  name="wcgsc-preserve-html-ajax-nonce"
                  id="wcgsc-preserve-html-ajax-nonce"
                  value="<?php echo esc_attr(wp_create_nonce('wcgsc-preserve-html-ajax-nonce')); ?>" />
            </div>
      </div>
      </form>
   </div>
</div>
<!-- row input data option-->