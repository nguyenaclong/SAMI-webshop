<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

if (! function_exists('is_plugin_active')) {
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
}
?>
<?php
// Get selected Sheet
$wcgsc_selected_sheet_key = get_option('wcgsc_settings');
// Get all sheet details of the connected account
$wcgsc_sheet_data = get_option('wcgsc_sheet_feeds');
// Get order states/ Tab names
$wcgsc_selected_order_states = get_option('wcgsc_order_states');
$wcgsc_service = new wc_gsheetconnector_Service();

$wcgsc_adding_extra_order_row = $wcgsc_service->get_adding_extra_order_row();
$wcgsc_adding_extra_product_item_row = $wcgsc_service->get_adding_extra_product_item_row();
$wcgsc_adding_extra_product_row = $wcgsc_service->get_adding_extra_product_row();


// Check if the user is authenticated
$wcgsc_authenticated = get_option('wcgsc_token');

$wcgsc_per = get_option('wcgsc_verify');
// check user is authenticated when save existing api method
$wcgsc_show_setting = 0;

if ((!empty($wcgsc_authenticated) && $wcgsc_per == "valid")) {
    $wcgsc_show_setting = 1;
} else {
    ?>

    <div class="gsc-setup-alert wcgsc-display-note">
        <div class="gsc-alert-icon">
            <svg width="30px" height="30px" viewBox="-0.5 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M18.2202 21.25H5.78015C5.14217 21.2775 4.50834 21.1347 3.94373 20.8364C3.37911 20.5381 2.90402 20.095 2.56714 19.5526C2.23026 19.0101 2.04372 18.3877 2.02667 17.7494C2.00963 17.111 2.1627 16.4797 2.47015 15.92L8.69013 5.10999C9.03495 4.54078 9.52077 4.07013 10.1006 3.74347C10.6804 3.41681 11.3346 3.24518 12.0001 3.24518C12.6656 3.24518 13.3199 3.41681 13.8997 3.74347C14.4795 4.07013 14.9654 4.54078 15.3102 5.10999L21.5302 15.92C21.8376 16.4797 21.9907 17.111 21.9736 17.7494C21.9566 18.3877 21.7701 19.0101 21.4332 19.5526C21.0963 20.095 20.6211 20.5381 20.0565 20.8364C19.4919 21.1347 18.8581 21.2775 18.2202 21.25V21.25Z" stroke="#9a3412" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M10.8809 17.15C10.8809 17.0021 10.9102 16.8556 10.9671 16.7191C11.024 16.5825 11.1074 16.4586 11.2125 16.3545C11.3175 16.2504 11.4422 16.1681 11.5792 16.1124C11.7163 16.0567 11.8629 16.0287 12.0109 16.03C12.2291 16.034 12.4413 16.1021 12.621 16.226C12.8006 16.3499 12.9398 16.5241 13.0211 16.7266C13.1023 16.9292 13.122 17.1512 13.0778 17.3649C13.0335 17.5786 12.9272 17.7745 12.7722 17.9282C12.6172 18.0818 12.4203 18.1863 12.2062 18.2287C11.9921 18.2711 11.7703 18.2494 11.5685 18.1663C11.3666 18.0833 11.1938 17.9426 11.0715 17.7618C10.9492 17.5811 10.8829 17.3683 10.8809 17.15ZM11.2409 14.42L11.1009 9.20001C11.0876 9.07453 11.1008 8.94766 11.1398 8.82764C11.1787 8.70761 11.2424 8.5971 11.3268 8.5033C11.4112 8.40949 11.5144 8.33449 11.6296 8.28314C11.7449 8.2318 11.8697 8.20526 11.9959 8.20526C12.1221 8.20526 12.2469 8.2318 12.3621 8.28314C12.4774 8.33449 12.5805 8.40949 12.6649 8.5033C12.7493 8.5971 12.8131 8.70761 12.852 8.82764C12.8909 8.94766 12.9042 9.07453 12.8909 9.20001L12.7609 14.42C12.7609 14.6215 12.6808 14.8149 12.5383 14.9574C12.3957 15.0999 12.2024 15.18 12.0009 15.18C11.7993 15.18 11.606 15.0999 11.4635 14.9574C11.321 14.8149 11.2409 14.6215 11.2409 14.42Z" fill="#9a3412" />
            </svg>
        </div>
        <div class="gsc-alert-content">
            <div class="feed-alert-header">
                <?php esc_html_e('Google Sheets Setup Required', 'wc-gsheetconnector'); ?>
            </div>

            <p>
                <?php esc_html_e('Your selected method is: ', 'wc-gsheetconnector'); ?>
                <?php echo esc_html('Existing Client / Secret Key (Auto Setup).', 'wc-gsheetconnector'); ?>
            </p>

            <p><?php echo esc_html('To start sending form entries to Google Sheets, please connect your Google account first.', 'wc-gsheetconnector'); ?></p>

            <ul>
                <li><?php echo esc_html('✔ Click on the Sign in with Google button', 'wc-gsheetconnector'); ?></li>
                <li><?php echo esc_html('✔ Log in using your Google account', 'wc-gsheetconnector'); ?></li>
                <li><?php echo esc_html('✔ Select the Google account where your Sheets are stored', 'wc-gsheetconnector'); ?></li>
                <li><?php echo esc_html('✔ Grant access to: Google Drive & Google Sheets', 'wc-gsheetconnector'); ?></li>
                <li><?php echo esc_html('✔ Save the authentication code if prompted', 'wc-gsheetconnector'); ?></li>
            </ul>

            <a href="<?php echo esc_url(admin_url('admin.php?page=wc-gsheetconnector-config&tab=integration')); ?>" class="gsc-alert-btn link-hover-white">
                <?php esc_html_e('Go to Integration Setup', 'wc-gsheetconnector'); ?>
            </a>
        </div>
    </div>
    <?php
}

if ($wcgsc_show_setting == 1) {
    ?>
    <form method="post" id="gsSettingFormFree">

        <div class="wcgsc-fields shadow-box p-30">
            <div class="wc-heading mt-0"><?php echo esc_html(__('Choose Google Spreadsheet', 'wc-gsheetconnector')); ?></div>
            <p class="mt-10 mb-30">
                <?php esc_html_e("Select a Google Sheets to connect your data. If your sheet is not listed, fetch the latest sheets or create a new one. Then complete the setup and save your changes to start syncing.", 'wc-gsheetconnector'); ?>
            </p>

            <div class="wcgsc-in-fields">
                <div class="sheet-details">
                    <div class="row">
                        <div class="col-4">
                            <div class="form-group mr-10">
                                <label><?php echo esc_html__('Sheet Name', 'wc-gsheetconnector'); ?></label>
                                <select name="wcgsc-sheet-id" id="wcgsc-sheet-id">
                                    <option value=""><?php echo esc_html__('Select', 'wc-gsheetconnector'); ?></option>

                                    <?php
                                    if (! empty($wcgsc_sheet_data)) {
                                        foreach ($wcgsc_sheet_data as $wcgsc_key => $wcgsc_value) {
                                            $wcgsc_selected = '';
                                            if ($wcgsc_selected_sheet_key !== '' && $wcgsc_key == $wcgsc_selected_sheet_key) {
                                                $wcgsc_selected = 'selected';
                                            }
                                            ?>
                                            <option value="<?php echo esc_attr($wcgsc_key); ?>" <?php echo esc_attr($wcgsc_selected); ?>>
                                                <?php echo esc_html($wcgsc_value['sheet_name']); ?>
                                            </option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                                <span class="error_msg" id="error_spread"></span>
                                <span class="loading-sign"></span>
                                <input type="hidden" name="wcgsc-ajax-nonce" id="wcgsc-ajax-nonce"
                                value="<?php echo esc_attr(wp_create_nonce('wcgsc-ajax-nonce')); ?>" />
                            </div>
                        </div>

                        <div class="col-4 mt-25">
                            <div class="sheet-url row" id="sheet-url">
                                <?php
                                $wcgsc_sheet_id = '';
                                if (! empty($wcgsc_selected_sheet_key)) {
                                    $wcgsc_sheet_id = $wcgsc_selected_sheet_key;
                                    ?>
                                    <a href="https://docs.google.com/spreadsheets/d/<?php echo esc_attr($wcgsc_sheet_id); ?>" target="_blank" class="sheet-url-wcgsc" hover-tooltip="<?php echo esc_attr__('View Spreadsheet', 'wc-gsheetconnector'); ?>">
                                        <!-- <input type="button" id="viewsheet" class="btn btn-primary blinking-button" name="viewsheet" value="< ?php echo esc_attr__('View Spreadsheet', 'wc-gsheetconnector'); ?>"> -->
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 5C6.5 5 2 12 2 12C2 12 6.5 19 12 19C17.5 19 22 12 22 12C22 12 17.5 5 12 5Z"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" />
                                        </svg>
                                    </a>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>


                    <div class="custom-check manually-method-add d-flex flex-wrap justify-between align-center mt-30">
                        <div>
                            <div class="heading mt-0"><?php echo esc_html(__('Fetch Sheets', 'wc-gsheetconnector')); ?></div>
                            <div class="wcgsc-sync-row gsc-fetch-text">
                                <?php
                                printf(
                                    // translators: %s is the HTML <a> link for syncing WooCommerce settings.
                                    esc_html__('If your sheets is not listed,  %s to fetch all Google Sheets. ', 'wc-gsheetconnector'),
                                    '<a id="wcgsc-sync" data-init="yes">' . esc_html__('Click here', 'wc-gsheetconnector') . '</a>'
                                );
                                ?>

                                <span class="loading-sign"></span>
                            </div>
                        </div>
                        <?php 
                        if ((!empty($wcgsc_per) && $wcgsc_per == "valid")) {
                            $wcgsc_sheet_fetch_date = get_option('wcgsc_sheet_fetch_date');
                            if (! empty($wcgsc_sheet_fetch_date)) :

                               // Convert saved datetime string into timestamp.
                                $wcgsc_timestamp = strtotime($wcgsc_sheet_fetch_date);

                                 // Get WordPress date + time format from General Settings.
                                $wcgsc_date_time_format = get_option('date_format') . ' ' . get_option('time_format');

                               // Format according to WordPress settings + site timezone.
                                $wcgsc_formatted_date = wp_date($wcgsc_date_time_format, $wcgsc_timestamp); ?>

                                <div class="last-fetch-date gsc-fetch-time">
                                  <?php
                                  echo esc_html__(
                                    'Last synced :',
                                    'wc-gsheetconnector'
                                ) . ' ' . esc_html($wcgsc_formatted_date) . '.';
                                ?>
                            </div>
                        <?php  endif;
                    }
                    ?>
                </div>

                <p id="wcgsc-validation-message"></p>
            </div>
        </div>

    </div>


    <div class="wcgsc-tabs-set shadow-box mt-40 p-30">
        <div>
            <div class="wc-heading mt-0"><?php echo esc_html(__('Order Status Sheet Tabs', 'wc-gsheetconnector')); ?></div>
            <p class="mt-10 mb-20">
                <?php esc_html_e("Choose which order statuses should create separate tabs in your Google Sheet; when enabled, a new tab will be created for each selected status to help organize your data more clearly.", 'wc-gsheetconnector'); ?>
            </p>
        </div>
        <span class="error_msg" id="error_gsTabName"></span>
        <?php

        $wcgsc_order_state_list = array(
            'wc-pending' => 'Pending Orders',
            'wc-processing' => 'Processing Orders',
            'wc-on-hold' => 'On Hold Orders',
            'wc-failed' => 'Failed Orders',
            'wc-completed' => 'Completed Orders',
            'wc-cancelled' => 'Cancelled Orders',
            'wc-refunded' => 'Refunded Orders',
            'wc-trash' => 'Trashed Orders',
        );

        foreach ($wcgsc_order_state_list as $wcgsc_key => $wcgsc_state_name) {
            $wcgsc_order_state_checked = "";
            if (!empty($wcgsc_selected_order_states)) {
                if (in_array($wcgsc_key, $wcgsc_selected_order_states)) {
                    $wcgsc_order_state_checked = "checked";
                }
            }
            ?>
            <div class="wcgsc-cards">
                <span class="wcgsc-pointer">
                    <input type="checkbox" class="wcgsc_order_state check-toggle" name="wcgsc_order_state[]"
                    value="<?php echo esc_attr($wcgsc_key); ?>"
                    <?php echo checked($wcgsc_order_state_checked, 'checked', false); ?>
                    id="<?php echo esc_attr($wcgsc_key); ?>"
                    class="d-none">
                    <?php echo esc_html($wcgsc_state_name); ?>
                    <label for="<?php echo esc_attr($wcgsc_key); ?>" class="button-wcgsc-toggle"></label>
                </span>
            </div>

        <?php } ?>

        <div class="wcgsc-cards1">
            <span class="wcgsc-pointer">
                <div>
                    <?php echo esc_html__('All Orders', 'wc-gsheetconnector'); ?>
                    <span class="pro-ver"><?php echo esc_html__('Pro', 'wc-gsheetconnector'); ?></span>
                </div>
                <label for="pro" class="button-wcgsc-toggle tooltip11">
                    <span class="tooltiptext11"><?php esc_html_e('Upgrade To Pro', 'wc-gsheetconnector'); ?></span>
                </label>
            </span>
        </div>
    </div>
    <!-- Enable Language Translation -->

    <div class="wcgsc-google-set feed-informtion-inner shadow-box mt-40 p-30">
        <a class="wcgsc-list-set text-decoration-none" data-id="21" href="#0">
            <p class="maxi_mize maxi_mize21">
                <i class="fa fa-plus" aria-hidden="true"></i>
            </p>
            <p class="mini_mize mini_mize21">
                <i class="fa fa-minus" aria-hidden="true"></i>
            </p>

            <div class="wc-heading mt-0">
                <?php echo esc_html(__('Translate Google Sheets Tab Names', 'wc-gsheetconnector')); ?>
                <span class="pro-ver">
                    <?php esc_html_e('PRO', 'wc-gsheetconnector'); ?>
                </span>
            </div>
        </a>
        <p class="mt-10"><?php echo esc_html(__('This setting displays tab names according to your WordPress site language.', 'wc-gsheetconnector')); ?></p>

        <div class="wcgsc-list-set21">
            <?php
            $wcgsc_languages_change = 0;
            $wcgsc_plugin_languages_dir = WC_GSHEETCONNECTOR_PATH . 'languages/';
            $wcgsc_plugin_lang_files    = glob($wcgsc_plugin_languages_dir . '*.mo');
            $wcgsc_site_locale          = get_locale();
            $wcgsc_compatible_languages = array();
            if (! empty($wcgsc_plugin_lang_files)) {
                    // Load WordPress translation API
                if (! function_exists('wp_get_available_translations')) {
                    require_once ABSPATH . 'wp-admin/includes/translation-install.php';
                }

                $wcgsc_languages = wp_get_available_translations();

                foreach ($wcgsc_plugin_lang_files as $wcgsc_file) {
                    $wcgsc_filename  = basename($wcgsc_file);
                    $wcgsc_lang_code = preg_replace('/wc-gsheetconnector-|\.mo$/', '', $wcgsc_filename);

                        // Show current site language (future-proof)
                    if ($wcgsc_lang_code === $wcgsc_site_locale) {
                        $wcgsc_display_name = $wcgsc_service->wcgsc_get_language_display_name(
                            $wcgsc_lang_code,
                            $wcgsc_languages
                        );
                        $wcgsc_compatible_languages[$wcgsc_lang_code] = $wcgsc_display_name;
                    }
                }
            }
            ?>

            <?php if (! empty($wcgsc_compatible_languages)) : ?>
                <span class="woo-gsc-pointer gs-main-toggle mt-25">
                    <div class="laguage-heading">
                        <strong class="fw-700"><?php echo esc_html__('Enable Language Translation', 'wc-gsheetconnector'); ?></strong>
                        <p class="mt-5 mb-0"><?php echo esc_html__('When enabled, Google Sheet tab names will be displayed in the language selected for your WordPress site.', 'wc-gsheetconnector'); ?></p>
                    </div>
                    <label for="pro" class="button-wcgsc-toggle tooltip11">
                        <span class="tooltiptext11"><?php esc_html_e('Upgrade To Pro', 'wc-gsheetconnector'); ?></span>
                    </label>
                </span>

                <div id="wcgsc_languages_list"
                style="<?php echo (1 === (int) $wcgsc_languages_change) ? 'margin-top:10px; padding-left:20px;' : 'display:none; margin-top:10px; padding-left:20px;'; ?>">
                <ul>
                    <?php foreach ($wcgsc_compatible_languages as $wcgsc_code => $wcgsc_name) : ?>
                        <li>
                            <?php echo esc_html($wcgsc_name . "(" . $wcgsc_code . ")"); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- Enable Language Translation -->

<div class="wcgsc-header1" hidden>
    <div><?php echo esc_html(__('Headers', 'wc-gsheetconnector')); ?></div>

    <ul>
        <?php
        $wcgsc_header_list = $wcgsc_service->sheet_headers;
        foreach ($wcgsc_header_list as $wcgsc_header => $wcgsc_data) { ?>
            <li class="li-wcgsc-header1 li-woo-header">
                <i class="fa fa-sort sort-icon1"></i>
                <div class="switch-label1">
                    <label>
                        <span class='label1'>
                            <div class='label_text1'><?php echo esc_html($wcgsc_header); ?></div>
                            <div class="edit_col_name1">
                                <span class="tooltip11">
                                    <span class="tooltiptext11"><?php esc_html_e('Upgrade To Pro', 'wc-gsheetconnector'); ?></span>
                                    <i class="fa fa-pencil"></i>
                                </span>
                            </div>
                        </span>
                    </label>
                </div>
                <div class="toggle-buttom-pos">
                    <span class="tooltip11">
                        <span class="tooltiptext11"><?php esc_html_e('Upgrade To Pro', 'wc-gsheetconnector'); ?></span>

                        <label for="<?php echo esc_attr($wcgsc_header); ?>-one"
                            class="button-woo-toggle1 button-tog-active product_headers-lbl"
                            id="button-woo-toggle1-click"></label>
                        </span>
                    </div>
                </span>
            </li>
        <?php } ?>
    </ul>
</div>

<div id="opener">
    <div class="wcgsc-google-set shadow-box mt-40 p-30">
        <a class="wcgsc-list-set" data-id="12" href="#0">
            <p class="maxi_mize maxi_mize12"><i class="fa fa-plus" aria-hidden="true"></i></p>
            <p class="mini_mize mini_mize12"><i class="fa fa-minus" aria-hidden="true"></i></p>
            <div class="wc-heading"><?php echo esc_html(__('Custom Order Sheet Tabs', 'wc-gsheetconnector')); ?>
            <span class="pro-ver"><?php echo esc_html__('PRO', 'wc-gsheetconnector'); ?></span>
        </div>
        <p class="mt-10 mb-30">
            <?php esc_html_e("Custom order statuses will appear here. Turn them on to create a new tab for each in your Google Sheet.", 'wc-gsheetconnector'); ?>
        </p>
    </a>

    <div class="wcgsc-list-set12">
        <?php
        $wcgsc_corder_statuses = wc_get_order_statuses();
        unset($wcgsc_corder_statuses['wc-pending']);
        unset($wcgsc_corder_statuses['wc-processing']);
        unset($wcgsc_corder_statuses['wc-on-hold']);

        unset($wcgsc_corder_statuses['wc-completed']);
        unset($wcgsc_corder_statuses['wc-cancelled']);
        unset($wcgsc_corder_statuses['wc-failed']);
        unset($wcgsc_corder_statuses['wc-refunded']);
        unset($wcgsc_corder_statuses['wc-checkout-draft']);
        unset($wcgsc_corder_statuses['wc-trash']);

        if (! empty($wcgsc_corder_statuses)) {
            foreach ($wcgsc_corder_statuses as $wcgsc_key => $wcgsc_state_name) {
                ?>
                <span class="wcgsc-cards1">
                    <span class="wcgsc-pointer">
                        <?php echo esc_html($wcgsc_state_name); ?>
                        <label for="pro" class="button-wcgsc-toggle tooltip11">
                            <span class="tooltiptext11"><?php esc_html_e('Upgrade To Pro', 'wc-gsheetconnector'); ?></span>

                        </label>
                    </span>
                </span>
                <?php
            }
        } else {
            ?>
            <div class="gs-info-box mb-0">
                <?php echo esc_html(__("No custom order statuses are currently available.", 'wc-gsheetconnector')); ?>
            </div>
            <?php
        }
        ?>
    </div>
</div>

<div class="wcgsc-google-set shadow-box mt-40 p-30">
    <a class="wcgsc-list-set text-decoration-none" data-id="13" href="#0">
        <p class="maxi_mize maxi_mize13"><i class="fa fa-plus" aria-hidden="true"></i></p>
        <p class="mini_mize mini_mize13"><i class="fa fa-minus" aria-hidden="true"></i></p>
        <div class="wc-heading mt-0"> <?php echo esc_html(__('Sync Additional Sheet Tabs', 'wc-gsheetconnector')); ?>
        <span class="pro-ver"><?php echo esc_html(__('PRO', 'wc-gsheetconnector')); ?></span>
    </div>
    <p class="mt-10 mb-30">
        <?php esc_html_e("Enable additional Sheet tabs to sync more WooCommerce information to your Google Sheet. Each selected option will create and update its own tab automatically.", 'wc-gsheetconnector'); ?>
    </p>
</a>
<!-- Other Sheet Tabs to Enable -->
<div class="wcgsc-list-set13">

    <div class="wcgsc-cards1">
        <span class="wcgsc-pointer">
            <?php echo esc_html(__('All Products', 'wc-gsheetconnector')); ?>
            <label for="pro" class="button-wcgsc-toggle tooltip11">
                <span class="tooltiptext11"><?php esc_html_e('Upgrade To Pro', 'wc-gsheetconnector'); ?></span>

            </label>
        </span>
    </div>

    <div class="wcgsc-cards1">
        <span class="wcgsc-pointer">
            <?php echo esc_html(__('All Products Variation', 'wc-gsheetconnector')); ?>
            <label for="pro" class="button-wcgsc-toggle tooltip11">
                <span class="tooltiptext11"><?php esc_html_e('Upgrade To Pro', 'wc-gsheetconnector'); ?></span>

            </label>
        </span>
    </div>

    <div class="wcgsc-cards1">
        <span class="wcgsc-pointer">
            <?php echo esc_html(__('All Customers', 'wc-gsheetconnector')); ?>
            <label for="pro" class="button-wcgsc-toggle tooltip11">
                <span class="tooltiptext11"><?php esc_html_e('Upgrade To Pro', 'wc-gsheetconnector'); ?></span>

            </label>
        </span>
    </div>

    <div class="wcgsc-cards1">
        <span class="wcgsc-pointer">
            <?php echo esc_html(__('All Coupons', 'wc-gsheetconnector')); ?>
            <label for="pro" class="button-wcgsc-toggle tooltip11">
                <span class="tooltiptext11"><?php esc_html_e('Upgrade To Pro', 'wc-gsheetconnector'); ?></span>

            </label>
        </span>
    </div>

    <?php if (is_plugin_active('woocommerce-subscriptions/woocommerce-subscriptions.php')) { ?>
        <div class="wcgsc-cards1">
            <span class="wcgsc-pointer">
                <?php echo esc_html(__('All Subscriptions', 'wc-gsheetconnector')); ?>
                <label for="pro" class="button-wcgsc-toggle tooltip11">
                    <span class="tooltiptext11"><?php esc_html_e('Upgrade To Pro', 'wc-gsheetconnector'); ?></span>

                </label>
            </span>
        </div>
    <?php } ?>
</div>
</div>

<!-- sorting -->
<div class="wcgsc-header-set shadow-box mt-40 p-30">
    <a class="wcgsc-list-set" data-id="6" href="#0">
        <p class="maxi_mize maxi_mize6"><i class="fa fa-plus" aria-hidden="true"></i></p>
        <p class="mini_mize mini_mize6"><i class="fa fa-minus" aria-hidden="true"></i></p>
        <div class="wc-heading mt-0"> <?php echo esc_html(__("How Orders Are Synced", 'wc-gsheetconnector')); ?>
        <span class="pro-ver"><?php echo esc_html(__("PRO", 'wc-gsheetconnector')); ?></span>
    </div>
</a>

<div class="wcgsc-list-set6">
    <div class="mt-30">

        <!--Start Info Box-->
        <div class="gs-info-box">
            <p class="notes mt-0 mb-20">
                <strong class="fw-500 text-dark">
                    <?php echo esc_html__('Choose how orders should appear in your Google Sheet:', 'wc-gsheetconnector'); ?>
                </strong>
            </p>

            <ul>
                <li>
                    <strong class="fw-700">
                        <?php echo esc_html__('Order Wise :', 'wc-gsheetconnector'); ?>
                    </strong>
                    <?php echo esc_html__('Each order is added as a single row, even if it contains multiple products.', 'wc-gsheetconnector'); ?>
                </li>

                <li>
                    <strong class="fw-700">
                        <?php echo esc_html__('Product Wise :', 'wc-gsheetconnector'); ?>
                    </strong>
                    <?php
                    /* translators: Explains that each product in an order will be saved separately with the same order ID */
                    echo esc_html__('Each product in an order is added as a separate row, so you can see details for every item.', 'wc-gsheetconnector');
                    ?>
                </li>
            </ul>

            <p class="mt-20 mb-0">
                <?php
                echo esc_html__('You can also choose how the entries are sorted (ascending or descending). Your selected format and sorting will be applied across all tabs in the sheet.', 'wc-gsheetconnector');
                ?>
            </p>
        </div>
        <!--End Info Box-->

        <div class="gs-options">

            <div class="gs-option-group">
                <label class="gs-label"><?php echo esc_html(__("Select Sync Format", 'wc-gsheetconnector')); ?> </label>

                <div class="gs-radio-group">
                    <label class="gs-radio-card d-flex align-center">
                        <input
                        type="radio"
                        name="order_wise_product_wise"
                        value="orderwise"
                        id="order_wise" />
                        <span>
                            <?php echo esc_html__('Order Wise', 'wc-gsheetconnector'); ?>
                        </span>
                    </label>
                    <label class="gs-radio-card">
                        <input
                        type="radio"
                        name="order_wise_product_wise"
                        value="productwise"
                        id="product_wise" disabled />
                        <span>
                            <?php echo esc_html__('Product Wise', 'wc-gsheetconnector'); ?>
                        </span>
                        <span class="pro-ver"><?php echo esc_html(__("PRO", 'wc-gsheetconnector')); ?></span>
                    </label>
                </div>
            </div>
            <div class="gs-option-group">
                <label class="gs-label"> <?php echo esc_html(__("Sorting", 'wc-gsheetconnector')); ?></label>
                <div class="gs-radio-group">

                    <label class="gs-radio-card">
                        <input
                        type="radio"
                        name="asc_desc_sorting"
                        value="ASC"
                        id="asc_sorting" disabled />
                        <span>
                            <?php echo esc_html__('Ascending', 'wc-gsheetconnector'); ?>
                        </span>
                    </label>

                    <label class="gs-radio-card">
                        <input
                        type="radio"
                        name="asc_desc_sorting"
                        value="DESC"
                        id="desc_sorting"
                        disabled />
                        <span>
                            <?php echo esc_html__('Descending', 'wc-gsheetconnector'); ?>
                        </span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- order category filter start-->
<div class="wcgsc-google-set shadow-box mt-40 p-30">
    <a class="wcgsc-list-set" data-id="8" href="#0">
        <p class="maxi_mize maxi_mize8"><i class="fa fa-plus" aria-hidden="true"></i></p>
        <p class="mini_mize mini_mize8"><i class="fa fa-minus" aria-hidden="true"></i></p>
        <div class="wc-heading"> <?php echo esc_html(__('Order Category Filter', 'wc-gsheetconnector')); ?>
        <span class="pro-ver"><?php echo esc_html(__('PRO', 'wc-gsheetconnector')); ?></span>
    </div>
    <p class="mt-10 mb-30">
        <?php esc_html_e("Filter orders by category before syncing to your Google Sheet. When enabled, only selected categories will be synced. When disabled, all orders will be included. This applies to adding, updating, and syncing orders.", 'wc-gsheetconnector'); ?>
    </p>
</a>

<?php
// Get all order categories
$wcgsc_order_categories = get_terms(array(
    'taxonomy'   => 'product_cat',
    'orderby'    => 'name',
    'order'      => 'ASC',
    'hide_empty' => false
));

if (!empty($wcgsc_order_categories)) {
    ?>

    <div class="wcgsc-list-set8">
        <div>
            <span class="master-option d-flex fw-500 mb-15">
                <?php echo esc_html(__('Select Category', 'wc-gsheetconnector')); ?>
                <label class="switch" for="download-toggle-checkbox">
                    <input type="checkbox" id="download-toggle-checkbox" name="wcgsc[download_spreadsheet]" value="1" checked="" disabled="">
                    <span class="slider round button-toggle"></span>
                </label>
            </span>
        </div>

        <?php
        foreach ($wcgsc_order_categories as $wcgsc_key => $wcgsc_category) {
            ?>
            <div class="wcgsc-cards1">
                <span class="wcgsc-pointer">
                    <?php echo esc_html($wcgsc_category->name); ?>
                    <label for="pro" class="button-wcgsc-toggle tooltip11">
                        <span class="tooltiptext11"><?php echo esc_html__('Upgrade To Pro', 'wc-gsheetconnector'); ?></span>
                    </label>
                </span>

            </div>

        <?php }

        ?>
    </div>
<?php } ?>
</div>

<!-- order category filter end-->


<!-- product category filter start-->
<div class="wcgsc-google-set shadow-box mt-40 p-30">
    <a class="wcgsc-list-set" data-id="7" href="#0">
        <p class="maxi_mize maxi_mize7"><i class="fa fa-plus" aria-hidden="true"></i></p>
        <p class="mini_mize mini_mize7"><i class="fa fa-minus" aria-hidden="true"></i></p>
        <div class="wc-heading"> <?php echo esc_html(__('Product Category Filter', 'wc-gsheetconnector')); ?>
        <span class="pro-ver">
            <?php esc_html_e('PRO', 'wc-gsheetconnector'); ?>
        </span>
    </div>
    <p class="mt-10 mb-30">
        <?php esc_html_e("Filter products by category before syncing to your Google Sheet. When enabled, only selected categories will be synced. When disabled, all products will be included. This applies to adding, updating, and syncing products.", 'wc-gsheetconnector'); ?>
    </p>
</a>

<?php
                // get all product categories
$wcgsc_product_categories = get_terms(array(
    'taxonomy'   => 'product_cat',
    'orderby'    => 'name',
    'order'      => 'ASC',
    'hide_empty' => false
));

if (!empty($wcgsc_product_categories)) {
    ?>

    <div class="wcgsc-list-set7">
        <div>
            <span class="master-option d-flex fw-500 mb-15">
                <?php echo esc_html(__('Select Category', 'wc-gsheetconnector')); ?>
                <label class="switch" for="download-toggle-checkbox">
                    <input type="checkbox" id="download-toggle-checkbox" name="wcgsc[download_spreadsheet]" value="1" checked="" disabled="">
                    <span class="slider round button-toggle"></span>
                </label>
            </span>
        </div>

        <?php
        foreach ($wcgsc_product_categories as $wcgsc_key => $wcgsc_category) {
            ?>
            <div class="wcgsc-cards1">
                <span class="wcgsc-pointer">
                    <?php echo esc_html($wcgsc_category->name); ?>
                    <label for="pro" class="button-wcgsc-toggle tooltip11">
                        <span class="tooltiptext11"><?php echo esc_html__('Upgrade To Pro', 'wc-gsheetconnector'); ?></span>
                    </label>
                </span>


            </div>

        <?php }

        ?>
    </div>
<?php } ?>
</div>


<!-- 2-way sync products -->
<div class="wcgsc-google-set feed-informtion-inner shadow-box mt-40 p-30">
    <a class="wcgsc-list-set text-decoration-none" data-id="37" href="#0">
        <p class="maxi_mize maxi_mize37"><i class="fa fa-plus" aria-hidden="true"></i></p>
        <p class="mini_mize mini_mize37"><i class="fa fa-minus" aria-hidden="true"></i></p>
        <div class="wc-heading mt-0"> <?php echo esc_html(__('2-Way Sync', 'wc-gsheetconnector')); ?>
        <span class="pro-ver"><?php echo esc_html(__("PRO", 'wc-gsheetconnector')); ?></span>
    </div>
</a>
<p class="mt-10 mb-30"> <?php esc_html_e('Sync data between WooCommerce and Google Sheets in real-time. Enable this feature to manage orders, products, and more directly from your spreadsheet.', 'wc-gsheetconnector'); ?></p>
<div class="wcgsc-list-set37">
    <div class="section">
        <div class="gs-main-toggle">
            <div>
                <strong class="fw-700"><?php echo esc_html__('Enable 2-Way Sync', 'wc-gsheetconnector'); ?></strong>
                <p class="mt-5 mb-0"><?php echo esc_html__('Allow Google Sheets to update WooCommerce data.', 'wc-gsheetconnector'); ?></p>
            </div>
            <!-- Toggle -->
            <span class="toggle">
                <input type="checkbox" id="wcgsc_sheet_cron_formatting-row-checkbox" class="check-toggle d-none" name="wcgsc_cron_setting" value="1" checked="">
                <span class="button-woo-toggle button-toggle" for="wcgsc_sheet_cron_formatting-row-checkbox"></span>
            </span>
        </div>

        <p class="gs-info-box-blue"><?php echo wp_kses_post('Once enabled, you can insert, update, or delete WooCommerce data directly from your Google Sheet. Make sure your sheet is properly connected before starting the sync. Learn More <a href="https://www.gsheetconnector.com/docs/woocommerce-gsheetconnector/2-way-sync-pro-version" target="_blank" rel="noopener noreferrer"> How to configure and use 2-way orders sync?</a>', 'wc-gsheetconnector'); ?>
    </p>

    <!-- Hidden section -->
    <div class="import-options" id="wcgscimportOptions">

        <!-- Entity Checkboxes -->
        <!-- Entity Radio Buttons -->
        <div class="wcgsc-sync-entities-wrapper">
            <div class="select-modules"><strong><?php esc_html_e('Select Sync Modules', 'wc-gsheetconnector'); ?></strong></div>

            <div class="wcgsc-sync-entities">
                <label for="orders-2waysync">
                    <input type="radio" id="orders-2waysync" class="wcgsc-sync-entity" name="wcgsc_sync_module" value="orders" checked="checked">
                Orders </label>
                <label for="products-2waysync">
                    <input type="radio" id="products-2waysync" class="wcgsc-sync-entity" name="wcgsc_sync_module" value="products" disabled>
                Products </label>
                <label for="variations-2waysync">
                    <input type="radio" id="variations-2waysync" class="wcgsc-sync-entity" name="wcgsc_sync_module" value="variations" disabled>
                Products Variation </label>
                <label for="customers-2waysync">
                    <input type="radio" id="customers-2waysync" class="wcgsc-sync-entity" name="wcgsc_sync_module" value="customers" disabled>
                Customers </label>
                <label for="coupons-2waysync">
                    <input type="radio" id="coupons-2waysync" class="wcgsc-sync-entity" name="wcgsc_sync_module" value="coupons" disabled>
                Coupons </label>
            </div>
        </div>

        <div class="select-modules"><strong><?php esc_html_e('Allowed Actions', 'wc-gsheetconnector'); ?></strong></div>
        <div class="d-flex align-center gap-20 flex-wrap modification-sync-way mt-20">
            <div class="option-box"><?php esc_html_e('Insert', 'wc-gsheetconnector'); ?>
            <span class="toggle">
                <label for="pro" class="button-wcgsc-toggle tooltip11"></label>
            </span>
        </div>

        <div class="option-box"><?php esc_html_e('Update', 'wc-gsheetconnector'); ?>
        <span class="toggle">
            <label for="pro" class="button-wcgsc-toggle tooltip11"></label>
        </span>
    </div>

    <div class="option-box"><?php esc_html_e('Delete', 'wc-gsheetconnector'); ?>
    <span class="toggle">
        <label for="pro" class="button-wcgsc-toggle tooltip11"></label>
    </span>
</div>
</div>
<!-- import order sync -->
</div>
</div>
</div>
</div>
<!--End 2 way Sync-->

<div id="gform_setting_gsheet_field_maps" class="gform-settings-field gform-settings-field__map_form_fields"
titlea="Upgrade to Pro">

<div class="wcgsc-google-set shadow-box mt-40 p-30">

    <a class="wcgsc-list-set" data-id="3" href="#0">
        <p class="maxi_mize maxi_mize3"><i class="fa fa-plus" aria-hidden="true"></i></p>
        <p class="mini_mize mini_mize3"><i class="fa fa-minus" aria-hidden="true"></i></p>
        <div class="wc-heading"> <?php echo esc_html(__('Add Extra Google Sheet Columns', 'wc-gsheetconnector')); ?>
        <span class="pro-ver"><?php echo esc_html(__('PRO', 'wc-gsheetconnector')); ?></span>
    </div>
    <p class="mt-10 mb-30">
        <?php esc_html_e("Select fields to add as new columns in your sheet. Enter a column name for each field. These columns will be included when your data syncs.", 'wc-gsheetconnector'); ?>
    </p>
</a>

<div class="wcgsc-header-wrapper wcgsc-list-set3">

    <div class="tabs-gs-back">
        <div class="tabs-gs">

            <a class="wcgsc-list-set active-t-gs" data-id="31" href="#0"><?php echo esc_html(__('Orders', 'wc-gsheetconnector')); ?></a>
            <a class="wcgsc-list-set" data-id="32" href="#0"><?php echo esc_html(__('Products', 'wc-gsheetconnector')); ?></a>
            <a class="wcgsc-list-set" data-id="34" href="#0"><?php echo esc_html(__('Product Variation', 'wc-gsheetconnector')); ?></a>
            <a class="wcgsc-list-set" data-id="33" href="#0"><?php echo esc_html(__('Customers', 'wc-gsheetconnector')); ?></a>
            <a class="wcgsc-list-set" data-id="35" href="#0"><?php echo esc_html(__('Coupons', 'wc-gsheetconnector')); ?></a>
            <?php if (is_plugin_active('woocommerce-subscriptions/woocommerce-subscriptions.php')) { ?>
                <a class="wcgsc-list-set" data-id="36" href="#0"><?php echo esc_html(__('Subscriptions', 'wc-gsheetconnector')); ?></a>
            <?php } ?>
        </div>
    </div>
    <div class="wcgsc-header-wrapper wcgsc-list-set31" id="extra-field">
        <div class="checkallmaindiv">
            <div class="extra-all-main">
                <table class="table table-light adding_extra_table">
                    <tbody>
                        <tr>
                            <td><label class="check-all-lbl"><?php echo esc_html(__('Additional Order Columns', 'wc-gsheetconnector')); ?></label></td>
                            <td>
                                <select class="adding_extra_order_row adding_extra_css"
                                id="adding_extra_order_row">
                                <option value=""><?php echo esc_html('Select field', 'wc-gsheetconnector'); ?></option>
                                <?php if (!empty($wcgsc_adding_extra_order_row)) {
                                    foreach ($wcgsc_adding_extra_order_row as $wcgsc_key => $wcgsc_value) {
                                        ?>
                                        <option value="<?php echo esc_attr($wcgsc_value); ?>" disabled>
                                            <?php echo esc_html($wcgsc_value); ?>
                                        </option>

                                        <?php
                                    }
                                }
                                ?>
                            </select>
                        </td>
                        <td>
                            <div class="gs-floating-field">

                                <input
                                type="text"
                                name="ext_row_label_order"
                                id="ext_row_label_order"
                                class="gs-input ext_row_label_order"
                                placeholder="" disabled />

                                <label for="ext_row_label_order" class="check-all-lbl" disabled>
                                    <?php echo esc_html__('Enter Column Name', 'wc-gsheetconnector'); ?>
                                </label>

                            </div>
                        </td>
                        <td><button type="button" id="btn_extra_order_row"
                            class="btn_extra_order_row tooltip11 free-add-btn-table">
                            <?php echo esc_html(__('Add', 'wc-gsheetconnector')); ?>
                            <span class="tooltiptext11"><?php echo esc_html(__(' Upgrade To Pro', 'wc-gsheetconnector')); ?></span>
                        </button>
                    </td>
                    <td>
                        <span
                        class="loading-btn-extra-order-row"></span>
                    </td>
                </tr>
                <tr>
                    <td><label class="check-all-lbl"><?php echo esc_html(__("Additional Product Columns", 'wc-gsheetconnector')); ?></label>
                    </td>
                    <td>
                        <select class="adding_extra_product_item_row adding_extra_css"
                        id="adding_extra_product_item_row">
                        <option value=""><?php echo esc_html('Select field', 'wc-gsheetconnector'); ?></option>
                        <?php if (!empty($wcgsc_adding_extra_product_item_row)) {
                            foreach ($wcgsc_adding_extra_product_item_row as $wcgsc_key => $wcgsc_value) {
                                ?>
                                <option value="<?php echo esc_attr($wcgsc_value); ?>" disabled>
                                    <?php echo esc_html($wcgsc_value); ?>
                                </option>

                                <?php
                            }
                        }
                        ?>
                    </select>
                </td>

                <td>
                    <div class="gs-floating-field">

                        <input
                        type="text"
                        name="ext_row_label_order_item_row"
                        id="ext_row_label_order_item_row"
                        class="gs-input ext_row_label_order_item_row"
                        placeholder="" disabled />

                        <label class="check-all-lbl" disabled>
                            <?php echo esc_html__('Enter Column Name', 'wc-gsheetconnector'); ?>
                        </label>

                    </div>
                </td>
                <td><button type="button" id="btn_extra_order_item_row"
                    class="btn_extra_order_item_row tooltip11 free-add-btn-table">
                    <?php echo esc_html(__('Add', 'wc-gsheetconnector')); ?>
                    <span class="tooltiptext11"><?php echo esc_html(__(' Upgrade To Pro', 'wc-gsheetconnector')); ?></span>
                </button>
            </td>
            <td>
                <span
                class="loading-btn-extra-order-item-row"></span>
            </td>
        </tr>


        <tr>
            <td><label class="check-all-lbl"><?php echo esc_html(__('Additional Custom Columns', 'wc-gsheetconnector')); ?></label>
            </td>
            <td>
                <?php
                $wcgsc_adding_custom_static_headers = array('ip_address' => 'IP Address', 'site_name' => 'Site Name', 'site_url' => 'Site URL', 'site_admin_email' => 'Site Admin Email', 'site_description' => 'Site Description', 'user_agent' => 'User Agent', 'user_name' => 'User Name', 'user_login' => 'User Login', 'user_email' => 'User Email');

                ?>
                <select class="adding_custom_static_headers adding_extra_css"
                id="adding_custom_static_headers">
                <option value=""><?php echo esc_html('Select field', 'wc-gsheetconnector'); ?></option>
                <?php if (!empty($wcgsc_adding_custom_static_headers)) {
                    foreach ($wcgsc_adding_custom_static_headers as $wcgsc_key => $wcgsc_value) {
                        ?>
                        <option value="<?php echo esc_attr($wcgsc_value); ?>" disabled>
                            <?php echo esc_html($wcgsc_value); ?>
                        </option>
                        <?php
                    }
                }
                ?>
            </select>
        </td>

        <td>
            <div class="gs-floating-field">

                <input
                type="text"
                name="ext_row_custom_static_headers"
                id="ext_row_custom_static_headers"
                class="gs-input ext_row_custom_static_headers"
                placeholder="" disabled />

                <label class="check-all-lbl" disabled>
                    <?php echo esc_html__('Enter Column Name', 'wc-gsheetconnector'); ?>
                </label>

            </div>
        </td>
        <td><button type="button" id="btn_custom_static_headers"
            class="btn_custom_static_headers tooltip11 free-add-btn-table">
            <?php echo esc_html(__('Add', 'wc-gsheetconnector')); ?>
            <span class="tooltiptext11"><?php echo esc_html(__(' Upgrade To Pro', 'wc-gsheetconnector')); ?></span>
        </button>
    </td>
    <td>
        <span
        class="loading-btn-custom-static-headers"></span>
    </td>
</tr>

<tr>
    <td><label class="check-all-lbl"><?php echo esc_html(__('Additional Empty Columns', 'wc-gsheetconnector')); ?></label>
    </td>
    <td>
        <?php
        $wcgsc_adding_custom_static_blank_headers = array('blank1' => 'Blank1', 'blank2' => 'Blank2', 'blank3' => 'Blank3', 'blank4' => 'Blank4', 'blank5' => 'Blank5', 'blank6' => 'Blank6', 'blank7' => 'Blank7', 'blank8' => 'Blank8', 'blank9' => 'Blank9', 'blank10' => 'Blank10');
        ?>
        <select class="adding_custom_static_blank_headers adding_extra_css"
        id="adding_custom_static_blank_headers">
        <option value=""><?php echo esc_html('Select field', 'wc-gsheetconnector'); ?></option>
        <?php if (!empty($wcgsc_adding_custom_static_blank_headers)) {
            foreach ($wcgsc_adding_custom_static_blank_headers as $wcgsc_key => $wcgsc_value) {
                ?>
                <option value="<?php echo esc_attr($wcgsc_value); ?>" disabled>
                    <?php echo esc_html($wcgsc_value); ?>
                </option>

                <?php
            }
        }
        ?>
    </select>
</td>

<td>
    <div class="gs-floating-field">
        <input
        type="text"
        name="ext_row_custom_blank_headers"
        id="ext_row_custom_blank_headers"
        class="gs-input ext_row_custom_blank_headers"
        placeholder="" disabled />

        <label class="check-all-lbl" disabled>
            <?php echo esc_html__('Enter Column Name', 'wc-gsheetconnector'); ?>
        </label>

    </div>
</td>
<td><button type="button" id="btn_custom_blank_headers"
    class="btn_custom_blank_headers tooltip11 free-add-btn-table">
    <?php echo esc_html(__('Add', 'wc-gsheetconnector')); ?>
    <span class="tooltiptext11"><?php echo esc_html(__('Upgrade To Pro', 'wc-gsheetconnector')); ?></span>
</button>
</td>
<td>
    <span
    class="loading-btn-custom-blank-headers"></span>
</td>
</tr>

</tbody>
</table>
</div>
<div class="checked-all-div master-option d-flex align-center fw-500 mb-15 width-content-checked">
    <label class="check-all-lbl"><?php echo esc_html(__('Enable All Columns', 'wc-gsheetconnector')); ?> </label>
    <span class="tooltip11"><span class="tooltiptext11"><?php echo esc_html__('Upgrade To Pro', 'wc-gsheetconnector'); ?></span>
    <!-- Toggle button -->
    <label class="button-wcgsc-toggle1 sheet_headers-order button-tog-inactive"
    id="button-wcgsc-toggle1-click-<?php echo esc_attr($wcgsc_header); ?>" data-id="sheet_headers-"
    style="float: left;margin-top: 5px;"></label>
</div>
</div>
<ul class="wcgsc-header">
    <ul class="responsive-grid-add">
        <?php
        $wcgsc_header_list = $wcgsc_service->sheet_headers;
        foreach ($wcgsc_header_list as $wcgsc_header => $wcgsc_data) { ?>
            <li class="li-wcgsc-header1 li-woo-header">
                <i class="fa fa-sort sort-icon1"></i>
                <div class="switch-label1">
                    <label>
                        <span class='label1'>
                            <div class='label_text1'>
                                <?php echo esc_html($wcgsc_header); ?></div>
                                <div class="edit_col_name1">
                                    <span class="tooltip11">
                                        <span class="tooltiptext11">
                                            <?php echo esc_html__('Upgrade To Pro', 'wc-gsheetconnector'); ?>
                                        </span>
                                        <i class="fa fa-pencil"></i>
                                    </span>
                                </div>
                            </span>
                        </label>
                    </div>

                    <div class="toggle-buttom-pos">
                        <span class="tooltip11">
                            <span class="tooltiptext11"><?php echo esc_html__('Upgrade To Pro', 'wc-gsheetconnector'); ?></span>
                            <label for="<?php echo esc_attr($wcgsc_header) . '-one'; ?>"
                                class="button-wcgsc-toggle1 button-tog-active product_headers-lbl"
                                id="button-wcgsc-toggle1-click-<?php echo esc_attr($wcgsc_header); ?>"></label>
                            </span>
                        </div>
                    </li>
                <?php } 
                $wcgsc_header_list_pro = $wcgsc_service->sheet_headers_pro;
                foreach ($wcgsc_header_list_pro as $wcgsc_header) { ?>
                    <li class="li-wcgsc-header1 li-woo-header">
                        <i class="fa fa-sort sort-icon1"></i>
                        <div class="switch-label1">
                            <label>
                                <span class='label1'>
                                    <div class='label_text1'>
                                        <?php echo esc_html($wcgsc_header); ?></div>
                                        <div class="edit_col_name1">
                                            <span class="tooltip11">
                                                <span class="tooltiptext11"><?php esc_html_e('Upgrade To Pro', 'wc-gsheetconnector'); ?></span>
                                                <i class="fa fa-pencil"></i>
                                            </span>
                                        </div>
                                    </label>
                                </div>

                                <div class="toggle-buttom-pos">
                                    <span class="tooltip11">
                                        <span class="tooltiptext11"><?php esc_html_e('Upgrade To Pro', 'wc-gsheetconnector'); ?></span>
                                        <label for="<?php echo esc_attr($wcgsc_header) . '-one'; ?>"
                                            class="button-wcgsc-toggle1 button-tog-inactive product_headers-lbl"
                                            id="button-wcgsc-toggle1-click-<?php echo esc_attr($wcgsc_header); ?>"></label>
                                        </span>
                                    </div>

                                </span>

                            </li>
                        <?php } ?>
                        <!-- Toggle button -->
                    </ul>
                </div>
                <!-- 32 product headers -->
                <div class="wcgsc-header-wrapper wcgsc-list-set32">
                    <div class="checkallmaindiv">
                        <div class="extra-all-main">
                            <table class="table table-light adding_extra_table">
                                <tbody>
                                    <tr>
                                        <td><label class="check-all-lbl"><?php echo esc_html(__('Additional Product Columns', 'wc-gsheetconnector')); ?></label></td>
                                        <td>
                                            <select class="adding_extra_order_row adding_extra_css"
                                            id="adding_extra_order_row">
                                            <option value=""><?php echo esc_html('Select field', 'wc-gsheetconnector'); ?></option>
                                            <?php if (!empty($wcgsc_adding_extra_product_row)) {
                                                foreach ($wcgsc_adding_extra_product_row as $wcgsc_key => $wcgsc_value) {
                                                    ?>
                                                    <option value="<?php echo esc_attr($wcgsc_value); ?>" disabled>
                                                        <?php echo esc_html($wcgsc_value); ?>
                                                    </option>

                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                    </td>

                                    <td>
                                        <div class="gs-floating-field">

                                            <input
                                            type="text"
                                            name="ext_row_label_order"
                                            id="ext_row_label_order"
                                            class="gs-input ext_row_label_order"
                                            placeholder="" disabled />

                                            <label class="check-all-lbl" disabled>
                                                <?php echo esc_html__('Enter Column Name', 'wc-gsheetconnector'); ?>
                                            </label>

                                        </div>
                                    </td>
                                    <td><button type="button" id="btn_extra_order_row"
                                        class="btn_extra_order_row tooltip11 free-add-btn-table">
                                        <?php echo esc_html(__('Add', 'wc-gsheetconnector')); ?>
                                        <span class="tooltiptext11"><?php echo esc_html(__(' Upgrade To Pro', 'wc-gsheetconnector')); ?></span>
                                    </button>
                                </td>
                                <td>
                                    <span
                                    class="loading-btn-extra-order-row"></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="checked-all-div master-option d-flex align-center fw-500 mb-15 width-content-checked">
                    <label class="check-all-lbl"><?php echo esc_html(__('Enable All Columns', 'wc-gsheetconnector')); ?></label>
                    <input type="radio" id="product_headers-one" name="switch-one" class="radio-btn-hide"
                    value="yes" checked="">
                    <input type="radio" id="product_headers-two" name="switch-one" class="radio-btn-hide"
                    value="no">

                    <!-- Toggle button -->
                    <label class="button-wcgsc-toggle1 product_headers-order button-tog-inactive"
                    id="button-wcgsc-toggle1-click-<?php echo esc_attr($wcgsc_header); ?>" data-id="product_headers-"
                    style="float: left;margin-top: 5px;"></label>
                    <!-- Toggle button -->
                </div>

            </div>
            <?php
            $wcgsc_header_list_pro2 = $wcgsc_service->product_headers_pro;
            foreach ($wcgsc_header_list_pro2 as $wcgsc_header) { ?>
                <li class="li-wcgsc-header1 li-woo-header">
                    <i class="fa fa-sort sort-icon1"></i>
                    <div class="switch-label1">
                        <label>
                            <span class='label1'>
                                <div class='label_text1'><?php echo esc_html($wcgsc_header); ?></div>
                                <div class="edit_col_name1">
                                    <span class="tooltip11">
                                        <span class="tooltiptext11"><?php esc_html_e('Upgrade To Pro', 'wc-gsheetconnector'); ?></span>
                                        <i class="fa fa-pencil"></i>
                                    </span>
                                </div>
                            </label>
                        </div>

                        <div class="toggle-buttom-pos">
                            <span class="tooltip11">
                                <span class="tooltiptext11"><?php esc_html_e('Upgrade To Pro', 'wc-gsheetconnector'); ?></span>
                                <label for="<?php echo esc_attr($wcgsc_header) . '-one'; ?>"
                                    class="button-wcgsc-toggle1 button-tog-inactive product_headers-lbl"
                                    id="button-wcgsc-toggle1-click-<?php echo esc_attr($wcgsc_header); ?>"></label>
                                </span>
                            </div>
                        </span>
                    </li>
                <?php } ?>

                <!-- Toggle button -->
                <div class="toggle-buttom-pos">
                    <label class="button-wcgsc-toggle1 button-tog-inactive product_headers-lbl"
                    id="button-wcgsc-toggle1-click-<?php echo esc_attr($wcgsc_header); ?>" data-id="prod_external_link-"></label>
                </div>
                <!-- Toggle button -->


                <input type="radio" id="prod_external_link-one" name="product_headers[prod_external_link]" value="1"
                class="header_name_1 product_headers-one radio-btn-hide">

                <input type="radio" id="prod_external_link-two" name="product_headers[prod_external_link]" value="0"
                checked="" class="header_name_0 product_headers-two radio-btn-hide">

            </li>
        </ul>
    </div>
    <!-- 33 ahmed -->
    <div class="wcgsc-header-wrapper wcgsc-list-set33">
        <div class="checkallmaindiv">
            <div class="checked-all-div master-option d-flex align-center fw-500 mb-15 width-content-checked">
                <label class="check-all-lbl"><?php echo esc_html(__('Enable All Columns', 'wc-gsheetconnector')); ?></label>
                <input type="radio" id="customer_headers-one" name="switch-one" class="radio-btn-hide"
                value="yes" checked="">
                <input type="radio" id="customer_headers-two" name="switch-one" class="radio-btn-hide"
                value="no">

                <!-- Toggle button -->
                <label class="button-wcgsc-toggle1 customer_headers-order button-tog-inactive"
                id="button-wcgsc-toggle1-click-<?php echo esc_attr($wcgsc_header); ?>" data-id="customer_headers-"
                style="float: left;margin-top: 5px;"></label>
                <!-- Toggle button -->
            </div>

        </div>
        <ul class="wcgsc-header ui-sortable">
            <?php
            $wcgsc_header_list_pro3 = $wcgsc_service->customer_headers_pro;
            foreach ($wcgsc_header_list_pro3 as $wcgsc_header) { ?>


                <li class="li-wcgsc-header1 li-woo-header">
                    <i class="fa fa-sort sort-icon1"></i>
                    <div class="switch-label1">
                        <label>
                            <span class='label1'>
                                <div class='label_text1'><?php echo esc_html($wcgsc_header); ?>
                            </div>
                            <div class="edit_col_name1">
                                <span class="tooltip11">
                                    <span class="tooltiptext11"><?php esc_html_e('Upgrade To Pro', 'wc-gsheetconnector'); ?></span>
                                    <i class="fa fa-pencil"></i>
                                </span>
                            </div>
                            <label>

                            </div>

                            <div class="toggle-buttom-pos">
                                <span class="tooltip11">
                                    <span class="tooltiptext11"><?php esc_html_e('Upgrade To Pro', 'wc-gsheetconnector'); ?></span>
                                    <label for="<?php echo esc_attr($wcgsc_header) . '-one'; ?>"
                                        class="button-wcgsc-toggle1 button-tog-inactive product_headers-lbl"
                                        id="button-wcgsc-toggle1-click-<?php echo esc_attr($wcgsc_header); ?>"></label>
                                    </span>
                                </div>

                            </span>

                        </li>
                    <?php } ?>
                </ul>
            </div>
            <!-- 34 ahmed -->
            <div class="wcgsc-header-wrapper wcgsc-list-set34">
                <div class="checkallmaindiv">
                    <table class="table table-light adding_extra_table">
                        <tbody>
                            <tr>
                                <td><label class="check-all-lbl"><?php echo esc_html(__('Additional Product Variation Columns', 'wc-gsheetconnector')); ?></label></td>
                                <td>
                                    <select class="adding_extra_order_row adding_extra_css"
                                    id="adding_extra_order_row">
                                    <option value=""><?php echo esc_html('Select field', 'wc-gsheetconnector'); ?></option>
                                    <?php if (!empty($wcgsc_adding_extra_product_row)) {
                                        foreach ($wcgsc_adding_extra_product_row as $wcgsc_key => $wcgsc_value) {
                                            ?>
                                            <option value="<?php echo esc_attr($wcgsc_value); ?>" disabled>
                                                <?php echo esc_html($wcgsc_value); ?>
                                            </option>

                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </td>

                            <td>
                                <div class="gs-floating-field">

                                    <input
                                    type="text"
                                    name="ext_row_label_order"
                                    id="ext_row_label_order"
                                    class="gs-input ext_row_label_order"
                                    placeholder="" disabled />

                                    <label class="check-all-lbl" disabled>
                                        <?php echo esc_html__('Enter Column Name', 'wc-gsheetconnector'); ?>
                                    </label>

                                </div>
                            </td>
                            <td><button type="button" id="btn_extra_order_row"
                                class="btn_extra_order_row tooltip11 free-add-btn-table">
                                <?php echo esc_html(__('Add', 'wc-gsheetconnector')); ?>
                                <span class="tooltiptext11"><?php echo esc_html(__(' Upgrade To Pro', 'wc-gsheetconnector')); ?></span>
                            </button>
                        </td>
                        <td>
                            <span
                            class="loading-btn-extra-order-row"></span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="checked-all-div master-option d-flex align-center fw-500 mb-15 width-content-checked">
                <label class="check-all-lbl"><?php echo esc_html(__('Enable All Columns', 'wc-gsheetconnector')); ?></label>
                <input type="radio" id="customer_headers-one" name="switch-one" class="radio-btn-hide"
                value="yes" checked="">
                <input type="radio" id="customer_headers-two" name="switch-one" class="radio-btn-hide"
                value="no">

                <!-- Toggle button -->
                <label class="button-wcgsc-toggle1 customer_headers-order button-tog-inactive"
                id="button-wcgsc-toggle1-click-<?php echo esc_attr($wcgsc_header); ?>" data-id="customer_headers-"
                style="float: left;margin-top: 5px;"></label>
                <!-- Toggle button -->
            </div>

        </div>
        <ul class="wcgsc-header ui-sortable">
            <?php
            $wcgsc_header_list_pro4 = $wcgsc_service->product_variations_headers_pro;
            foreach ($wcgsc_header_list_pro4 as $wcgsc_header) { ?>


                <li class="li-wcgsc-header1 li-woo-header">
                    <i class="fa fa-sort sort-icon1"></i>
                    <div class="switch-label1">
                        <label>
                            <span class='label1'>
                                <div class='label_text1'><?php echo esc_html($wcgsc_header); ?>
                            </div>
                            <div class="edit_col_name1">
                                <span class="tooltip11">
                                    <span class="tooltiptext11"><?php esc_html_e('Upgrade To Pro', 'wc-gsheetconnector'); ?></span>
                                    <i class="fa fa-pencil"></i>
                                </span>
                            </div>
                            <label>

                            </div>

                            <div class="toggle-buttom-pos">
                                <span class="tooltip11">
                                    <span class="tooltiptext11"><?php esc_html_e('Upgrade To Pro', 'wc-gsheetconnector'); ?></span>
                                    <label for="<?php echo esc_attr($wcgsc_header) . '-one'; ?>"
                                        class="button-wcgsc-toggle1 button-tog-inactive product_headers-lbl"
                                        id="button-wcgsc-toggle1-click-<?php echo esc_attr($wcgsc_header); ?>"></label>
                                    </span>
                                </div>

                            </span>

                        </li>
                    <?php } ?>
                </ul>
            </div>

            <!-- coupons header -->

            <div class="wcgsc-header-wrapper wcgsc-list-set35">
                <div class="checkallmaindiv">
                    <div class="checked-all-div master-option d-flex align-center fw-500 mb-15 width-content-checked">
                        <label class="check-all-lbl"><?php echo esc_html(__('Enable All Columns', 'wc-gsheetconnector')); ?></label>
                        <input type="radio" id="coupon_headers-one" name="switch-one" class="radio-btn-hide"
                        value="yes" checked="">
                        <input type="radio" id="coupon_headers-two" name="switch-one" class="radio-btn-hide"
                        value="no">

                        <!-- Toggle button -->
                        <label class="button-wcgsc-toggle1 coupon_headers-order button-tog-inactive"
                        id="button-wcgsc-toggle1-click-<?php echo esc_attr($wcgsc_header); ?>" data-id="coupon_headers-"
                        style="float: left;margin-top: 5px;"></label>
                        <!-- Toggle button -->
                    </div>

                </div>
                <ul class="wcgsc-header ui-sortable">
                    <?php
                    $wcgsc_header_list_pro5 = $wcgsc_service->coupons_headers_pro;
                    foreach ($wcgsc_header_list_pro5 as $wcgsc_header) { ?>
                        <li class="li-wcgsc-header1 li-woo-header">
                            <i class="fa fa-sort sort-icon1"></i>
                            <div class="switch-label1">
                                <label>
                                    <span class='label1'>
                                        <div class='label_text1'><?php echo esc_html($wcgsc_header); ?>
                                    </div>
                                    <div class="edit_col_name1">
                                        <span class="tooltip11">
                                            <span class="tooltiptext11"><?php esc_html_e('Upgrade To Pro', 'wc-gsheetconnector'); ?></span>
                                            <i class="fa fa-pencil"></i>
                                        </span>
                                    </div>
                                    <label>
                                    </div>
                                    <div class="toggle-buttom-pos">
                                        <span class="tooltip11">
                                            <span class="tooltiptext11"><?php esc_html_e('Upgrade To Pro', 'wc-gsheetconnector'); ?></span>
                                            <label for="<?php echo esc_attr($wcgsc_header) . '-one'; ?>"
                                                class="button-wcgsc-toggle1 button-tog-inactive coupon_headers-lbl"
                                                id="button-wcgsc-toggle1-click-<?php echo esc_attr($wcgsc_header); ?>"></label>
                                            </span>
                                        </div>

                                    </span>

                                </li>
                            <?php } ?>
                        </ul>
                    </div>

                    <!-- subscriptions header -->
                    <?php if (is_plugin_active('woocommerce-subscriptions/woocommerce-subscriptions.php')) { ?>
                        <div class="wcgsc-header-wrapper wcgsc-list-set36">
                            <div class="checkallmaindiv">
                                <div class="checked-all-div master-option d-flex align-center fw-500 mb-15 width-content-checked">
                                    <label class="check-all-lbl"><?php echo esc_html(__('Enable All Columns', 'wc-gsheetconnector')); ?></label>
                                    <input type="radio" id="subscription_headers-one" name="switch-one" class="radio-btn-hide"
                                    value="yes" checked="">
                                    <input type="radio" id="subscription_headers-two" name="switch-one" class="radio-btn-hide"
                                    value="no">

                                    <!-- Toggle button -->
                                    <label class="button-wcgsc-toggle1 subscription_headers-order button-tog-inactive"
                                    id="button-wcgsc-toggle1-click-<?php echo esc_attr($wcgsc_header); ?>" data-id="subscription_headers-"
                                    style="float: left;margin-top: 5px;"></label>
                                    <!-- Toggle button -->
                                </div>

                            </div>
                            <ul class="wcgsc-header ui-sortable">
                                <?php
                                $wcgsc_header_list_pro6 = $wcgsc_service->subscriptions_headers_pro;
                                foreach ($wcgsc_header_list_pro6 as $wcgsc_header) { ?>
                                    <li class="li-wcgsc-header1 li-woo-header">
                                        <i class="fa fa-sort sort-icon1"></i>
                                        <div class="switch-label1">
                                            <label>
                                                <span class='label1'>
                                                    <div class='label_text1'><?php echo esc_html($wcgsc_header); ?>
                                                </div>
                                                <div class="edit_col_name1">
                                                    <span class="tooltip11">
                                                        <span class="tooltiptext11"><?php esc_html_e('Upgrade To Pro', 'wc-gsheetconnector'); ?></span>
                                                        <i class="fa fa-pencil"></i>
                                                    </span>
                                                </div>
                                            </label>

                                        </div>
                                        <div class="toggle-buttom-pos">
                                            <span class="tooltip11">
                                                <span class="tooltiptext11">
                                                    <?php esc_html_e('Upgrade To Pro', 'wc-gsheetconnector'); ?>
                                                </span>
                                                <label for="<?php echo esc_attr($wcgsc_header); ?>-one"
                                                    class="button-wcgsc-toggle1 button-tog-inactive subscription_headers-lbl"
                                                    id="button-wcgsc-toggle1-click-<?php echo esc_attr($wcgsc_header); ?>"></label>
                                                </span>
                                            </div>

                                        </span>

                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <!--Start Cron Setting-->
            <div class="wcgsc-google-set feed-informtion-inner shadow-box mt-40 p-30">
                <a class="wcgsc-list-set text-decoration-none" data-id="9" href="#0">
                    <p class="maxi_mize maxi_mize9"><i class="fa fa-plus" aria-hidden="true"></i></p>
                    <p class="mini_mize mini_mize9"><i class="fa fa-minus" aria-hidden="true"></i></p>
                    <div class="wc-heading mt-0"><?php echo esc_html(__('Cron Settings', 'wc-gsheetconnector')); ?>
                    <span class="pro-ver"><?php echo esc_html(__("PRO", 'wc-gsheetconnector')); ?></span>
                </div>
            </a>
            <div class="wcgsc-list-set9">
                <div class="sheet_formatting disable">
                    <div class="elemgsc-sheet_formatting_row elemgsc-sheet_formatting_row">
                        <div class="toggle-button sheet_formatting-row-toggle gs-main-toggle">
                            <div>
                                <strong class="fw-700"><?php echo esc_html(__('Enable Cron Sync', 'wc-gsheetconnector')); ?></strong>
                                <p class="mt-5 mb-0"><?php echo esc_html(__('Turn on automatic scheduling', 'wc-gsheetconnector')); ?></p>
                            </div>
                            <span class="toggle">
                                <input type="checkbox" id="wcgsc_sheet_cron_formatting-row-checkbox" class="check-toggle d-none" name="wcgsc_cron_setting" value="1" checked="">
                                <span class="button-woo-toggle button-toggle" for="wcgsc_sheet_cron_formatting-row-checkbox"></span>
                            </span>
                        </div>
                    </div>


                    <div class="wcgsc-cron-settings-row" id="wcgsc-cron-settings-row" style="display: block;">
                        <div class="gsc-sheet-card-status">
                            <div class="wcgsc-radio-group gs-radio-group mb-15">
                                <label class="cron-radio-button">
                                    <input type="radio" name="wcgsc_schedule_type" value="auto" checked="checked">
                                    <?php echo esc_html(__('Set Schedule', 'wc-gsheetconnector')); ?></label>
                                    <label class="cron-radio-button">
                                        <input type="radio" name="wcgsc_schedule_type" value="none" disabled>
                                        <?php echo esc_html(__('No Schedule', 'wc-gsheetconnector')); ?></label>
                                    </div>

                                    <!-- AUTOMATIC SCHEDULING INNER OPTIONS -->
                                    <div id="wcgsc-automatic-options" class="wcgsc-sub-section" style="">
                                        <div class="wcgsc-sub-radio-group mb-20 gap-20">
                                            <label class="automation-sub-radio">
                                                <input type="radio" name="wcgsc_auto_option" value="recurrence" disabled>
                                                <?php echo esc_html(__('Choose Schedule Type', 'wc-gsheetconnector')); ?></label>
                                                <label class="automation-sub-radio">
                                                    <input type="radio" name="wcgsc_auto_option" value="weekly" disabled>
                                                    <?php echo esc_html(__('Repeat Weekly', 'wc-gsheetconnector')); ?></label>
                                                    <label class="automation-sub-radio">
                                                        <input type="radio" name="wcgsc_auto_option" value="once" checked="checked">
                                                        <?php echo esc_html(__('Run Once', 'wc-gsheetconnector')); ?></label>
                                                    </div>

                                                    <!-- Recurrence Select -->
                                                    <div id="wcgsc-recurrence-options" style="display:none;">
                                                        <div class="form-group w-25">
                                                            <label>
                                                                <?php echo esc_html(__('How Often', 'wc-gsheetconnector')); ?></label>
                                                                <select name="wcgsc_recurrence_frequency">
                                                                    <option value="10min" selected="selected"><?php echo esc_html('Every Ten Minutes', 'wc-gsheetconnector'); ?></option>
                                                                    <option value="30min"><?php echo esc_html('Every Thirty Minutes', 'wc-gsheetconnector'); ?></option>
                                                                    <option value="daily"><?php echo esc_html('Once Daily', 'wc-gsheetconnector'); ?></option>
                                                                    <option value="twicedaily"><?php echo esc_html('Twice Daily', 'wc-gsheetconnector'); ?></option>
                                                                    <option value="15days"><?php echo esc_html('Every Fifteen Days', 'wc-gsheetconnector'); ?></option>
                                                                    <option value="monthly"><?php echo esc_html('Monthly', 'wc-gsheetconnector'); ?></option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <!-- Weekly Selection -->
                                                        <div id="wcgsc-weekly-options" style="display:none;">
                                                            <div class="form-group w-25">
                                                                <label>
                                                                    <?php echo esc_html(__('Select Day', 'wc-gsheetconnector')); ?></label>
                                                                    <select name="wcgsc_weekly_day">
                                                                        <option value="sun" selected="selected"><?php echo esc_html('Sunday', 'wc-gsheetconnector'); ?></option>
                                                                        <option value="mon"><?php echo esc_html('Monday', 'wc-gsheetconnector'); ?></option>
                                                                        <option value="tue"><?php echo esc_html('Tuesday', 'wc-gsheetconnector'); ?></option>
                                                                        <option value="wed"><?php echo esc_html('Wednesday', 'wc-gsheetconnector'); ?></option>
                                                                        <option value="thu"><?php echo esc_html('Thursday', 'wc-gsheetconnector'); ?></option>
                                                                        <option value="fri"><?php echo esc_html('Friday', 'wc-gsheetconnector'); ?></option>
                                                                        <option value="sat"><?php echo esc_html('Saturday', 'wc-gsheetconnector'); ?></option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <!-- One Time Date & Time -->
                                                            <div id="wcgsc-once-options" style="">
                                                                <div class="d-flex gap-20 align-center">
                                                                    <div class="form-group w-25">
                                                                        <label>
                                                                            <?php echo esc_html(__('Date', 'wc-gsheetconnector')); ?> </label> <input type="date" class="w-100" id="wcgsc_one_time_date" name="wcgsc_one_date" value="" disabled>
                                                                        </div>

                                                                        <div class="form-group w-25">
                                                                            <label>
                                                                                <?php echo esc_html(__('Time', 'wc-gsheetconnector')); ?></label>
                                                                                <input type="time" class="w-100" name="wcgsc_one_time" value="00:00" disabled>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="wcgsc-real-time-sync mt-20 gs-main-toggle">
                                                                <div>
                                                                    <label for="wcgsc_real_time_sync">
                                                                        <strong class="fw-700"><?php echo esc_html(__('Real-Time Sync', 'wc-gsheetconnector')); ?></strong>
                                                                        <p class="mt-5 mb-0"><?php echo esc_html(__('Sync instantly when new orders are created', 'wc-gsheetconnector')); ?></p>
                                                                    </label>
                                                                </div>

                                                                <!-- Hidden field ensures a value is always submitted -->
                                                                <input type="hidden" name="wcgsc_real_time_sync" value="0">

                                                                <!-- Checkbox (checked by default or based on saved value) -->
                                                                0                              <span class="toggle">
                                                                    <input type="checkbox" id="wcgsc_sheet_cron_formatting-row-checkbox" class="check-toggle d-none" name="wcgsc_cron_setting" value="1" checked="">
                                                                    <span class="button-woo-toggle button-toggle" for="wcgsc_sheet_cron_formatting-row-checkbox"></span>
                                                                </span>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--End Cron Setting-->

                                            <!-- color -->
                                            <div class="wcgsc-google-set shadow-box mt-40 p-30">
                                                <a class="wcgsc-list-set" data-id="4" href="#0">
                                                    <p class="maxi_mize maxi_mize4"><i class="fa fa-plus" aria-hidden="true"></i></p>
                                                    <p class="mini_mize mini_mize4"><i class="fa fa-minus" aria-hidden="true"></i></p>
                                                    <div class="wc-heading"> <?php echo esc_html(__("Header Settings", 'wc-gsheetconnector')); ?>
                                                    <span class="pro-ver"><?php echo esc_html(__("PRO", 'wc-gsheetconnector')); ?></span>
                                                </div>
                                                <p class="mt-10 mb-30"><?php
                                                // translators: Explains advanced Google Sheet settings like freezing headers and background colors.
                                                echo esc_html__('Customize the appearance and behavior of your sheet headers and rows for better readability and organization.', 'wc-gsheetconnector');
                                                ?>
                                            </p>
                                        </a>

                                        <div class="wcgsc-list-set4">
                                            <div class="freez_order_sort form-fields-list">
                                                <div>
                                                    <!-- SECTION 1: Header Behavior -->
                                                    <div class="header-styling-sheet d-flex gap-20">
                                                        <div class="settings-card mb-20 w-100 bg-white">
                                                            <div class="mt-0 header-settings-ineer-size fw-600 mb-20"><?php echo esc_html(__('Header Behavior', 'wc-gsheetconnector')); ?></div>
                                                            <div class="setting-row">
                                                                <div class="toggle-button freeze-header-toggle d-flex align-center justify-between mb-15">
                                                                    <span class="label-text fw-400"><?php echo esc_html(__('Freeze Header', 'wc-gsheetconnector')); ?></span>
                                                                    <label class="switch">
                                                                        <input type="checkbox" id="" name="" value="true" checked="" disabled>
                                                                        <span class="slider round button-toggle"></span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                           
                                                                <div class="mt-30 header-settings-ineer-size fw-600 mb-20"><?php echo esc_html__('Header Style', 'wc-gsheetconnector'); ?></div>
                                                                <div class="sheet_formatting setting-row">
                                                                    <div class="wcgsc-sheet_formatting wcgsc-sheet_formatting">
                                                                        <div class="toggle-button sheet_formatting-header-toggle d-flex align-center justify-between mb-15">
                                                                            <span class="label-texts fw-400"><?php echo esc_html(__('Header Appearance', 'wc-gsheetconnector')); ?></span>
                                                                            <label class="switch" for="sheet_formatting-header-checkbox">
                                                                                <input type="checkbox" id="sheet_formatting-header-checkbox" name="wcgsc[sheet_formatting_header]" value="1" checked="" disabled>
                                                                                <span class="slider round button-toggle"></span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="font-styling-settings" id="font-styling-settings" style="display: block;">
                                                                        <div class="settings-grid">
                                                                            <div class="font-style row-format form-group">
                                                                                <label for="font-size"><?php echo esc_html__('Font Style', 'wc-gsheetconnector'); ?></label>
                                                                                <div class="d-flex gap-5">
                                                                                    <label class="style-btn active"><input type="checkbox" name="font_styles[]" class="toggle-input active" value="normal" disabled>
                                                                                        <?php echo esc_html__('Normal', 'wc-gsheetconnector'); ?></label>
                                                                                        <label class="style-btn"><input type="checkbox" name="font_styles[]" value="italic" disabled>
                                                                                            <?php echo esc_html__('Italic', 'wc-gsheetconnector'); ?></label>
                                                                                            <label class="style-btn"><input type="checkbox" name="font_styles[]" value="bold" disabled><?php echo esc_html__('Bold', 'wc-gsheetconnector'); ?></label>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="font-size row-format form-group">
                                                                                        <label for="font-size"><?php echo esc_html__('Font Size', 'wc-gsheetconnector'); ?></label>
                                                                                        <div class="gs-font-size">
                                                                                            <div class="gs-select-box">
                                                                                                <select disabled>
                                                                                                    <option value="" selected><?php echo esc_html__('Select size', 'wc-gsheetconnector'); ?></option>
                                                                                                    <option><?php echo esc_html__('11', 'wc-gsheetconnector'); ?></option>
                                                                                                    <option><?php echo esc_html__('12', 'wc-gsheetconnector'); ?></option>
                                                                                                    <option><?php echo esc_html__('13', 'wc-gsheetconnector'); ?></option>
                                                                                                    <option><?php echo esc_html__('14', 'wc-gsheetconnector'); ?></option>
                                                                                                    <option><?php echo esc_html__('15', 'wc-gsheetconnector'); ?></option>
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>
                                                                                        <!--<input type="number" id="font-size" name="wcgsc[font_size]" value=""> px-->
                                                                                    </div>
                                                                                    <div class="font-color row-format form-group">
                                                                                        <label for="font-color"><?php echo esc_html__('Font Color', 'wc-gsheetconnector'); ?></label>
                                                                                        <input type="color" id="font-color" name="wcgsc[font_color]" class="bg-color-set-input" value="" disabled>
                                                                                    </div>
                                                                                    <div class="wcgsc-cards-sbg wcgsc-cards-sbg form-group">

                                                                                        <label for="wcgsc-header-color" class="button-wcgsc-toggle-color"></label>
                                                                                        <label><?php echo esc_html__('Header Background', 'wc-gsheetconnector'); ?></label>
                                                                                        <input type="color" id="header-color" name="wcgsc[header-color]" class="bg-color-set-input" value="&lt; ?php echo esc_attr($header_color); ?&gt;" disabled>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                    </div>
                                                                    <!-- SECTION 2: Header Appearance -->
                                                                    <div class="settings-card  mb-20 w-100 bg-white">

                                                                        <!-- SECTION 3: Row Appearance -->
                                                                        <div class="sheet_formatting">
                                                                            <div class="mt-0 header-settings-ineer-size fw-600 mb-20"><?php echo esc_html__('Row Style', 'wc-gsheetconnector'); ?></div>
                                                                            <div class="wcgsc-sheet_formatting_row wcgsc-sheet_formatting_row">
                                                                                <div class="toggle-button sheet_formatting-row-toggle d-flex align-center justify-between">
                                                                                    <span class="label-texts  fw-400"><?php echo esc_html__('Row Appearance', 'wc-gsheetconnector'); ?></span>
                                                                                    <label class="switch" for="sheet_formatting-row-checkbox">
                                                                                        <input type="checkbox" id="sheet_formatting-row-checkbox" name="wcgsc[sheet_formatting_row]" value="1" checked="" disabled>
                                                                                        <span class="slider round button-toggle"></span>
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="font-styling-settings-row" id="font-styling-settings-row">
                                                                                <div class="settings-grid">
                                                                                    <div class="font-style row-format form-group">
                                                                                        <label><?php echo esc_html__('Font Style', 'wc-gsheetconnector'); ?></label>
                                                                                        <div class="d-flex gap-5">
                                                                                            <label class="style-btn active"><input type="checkbox" name="font_styles[]" class="toggle-input active" value="normal" disabled>
                                                                                                <?php echo esc_html__('Normal', 'wc-gsheetconnector'); ?></label>
                                                                                                <label class="style-btn"><input type="checkbox" name="font_styles[]" value="italic" disabled>
                                                                                                    <?php echo esc_html__('Italic', 'wc-gsheetconnector'); ?></label>
                                                                                                    <label class="style-btn"><input type="checkbox" name="font_styles[]" value="bold" disabled><?php echo esc_html__('Bold', 'wc-gsheetconnector'); ?></label>
                                                                                                </div>
                                                                                            </div>

                                                                                            <div class="font-size row-format form-group">
                                                                                                <label for="font-size-row"><?php echo esc_html__('Font Size', 'wc-gsheetconnector'); ?></label>
                                                                                                <div class="gs-font-size">
                                                                                                    <div class="gs-select-box">
                                                                                                        <select disabled>
                                                                                                            <option value="" selected><?php echo esc_html__('Select size', 'wc-gsheetconnector'); ?></option>
                                                                                                            <option><?php echo esc_html__('11', 'wc-gsheetconnector'); ?></option>
                                                                                                            <option><?php echo esc_html__('12', 'wc-gsheetconnector'); ?></option>
                                                                                                            <option><?php echo esc_html__('13', 'wc-gsheetconnector'); ?></option>
                                                                                                            <option><?php echo esc_html__('14', 'wc-gsheetconnector'); ?></option>
                                                                                                            <option><?php echo esc_html__('15', 'wc-gsheetconnector'); ?></option>
                                                                                                        </select>
                                                                                                    </div>
                                                                                                </div>
                                                                                             
                                                                                            </div>
                                                                                            <div class="font-color row-format form-group">
                                                                                                <label for="font-color-row"><?php echo esc_html__('Font Color', 'wc-gsheetconnector'); ?></label>
                                                                                                <input type="color" id="font-color-row" name="wcgsc[font_color_row]" class="bg-color-set-input" value="" disabled>
                                                                                            </div>
                                                                                            <div class="wcgsc-cards-sbg wcgsc-cards-sbg form-group">

                                                                                                <label for="wcgsc-odd-color" class="button-wcgsc-toggle-color"></label>
                                                                                                <label>
                                                                                                    <?php echo esc_html__('Odd Row Color', 'wc-gsheetconnector'); ?> </label>
                                                                                                    <input type="color" id="odd-color" name="wcgsc[odd-color]" class="bg-color-set-input" value="&lt; ?php echo esc_attr($odd_color); ?&gt;" disabled>
                                                                                                </div>
                                                                                                <div class="wcgsc-cards-sbg wcgsc-cards-sbg form-group">
                                                                                                    <label for="wcgsc-even-color" class="button-wcgsc-toggle-color"></label>
                                                                                                    <label>
                                                                                                        <?php echo esc_html__('Even Row Color', 'wc-gsheetconnector'); ?> </label>
                                                                                                        <input type="color" id="even-color" name="wcgsc[even-color]" class="bg-color-set-input" value="&lt; ?php echo esc_attr($even_color); ?&gt;" disabled>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>

                                                                                        <!-- SECTION 4: Spreadsheet Download -->
                                                                                        <div id="spreadsheet-download-sync">
                                                                                            <div class="settings-card mt-30 w-100 bg-white">
                                                                                                <div class="toggle-button sheet-sorting-toggle d-flex align-center justify-between" id="downloads-toggle-checkbox">
                                                                                                    <span class="label-text  fw-400"><?php echo esc_html__('Spreadsheet Download', 'wc-gsheetconnector'); ?></span>
                                                                                                    <label class="switch" for="download-toggle-checkbox">
                                                                                                        <input type="checkbox" id="download-toggle-checkbox" name="wcgsc[download_spreadsheet]" value="1" checked="" disabled>
                                                                                                        <span class="slider round button-toggle"></span>
                                                                                                    </label>
                                                                                                </div>
                                                                                                <div id="download-button-wrapper" class="mt-15">
                                                                                                    <!-- style="display:none;"&gt; -->
                                                                                                    <a class="sheet-url-fluentform common-sheet-url text-dark fw-700 download-spreadsheet">
                                                                                                        <i class="fa-regular fa-file-zipper text-dark fw-500 mr-5"></i><?php echo esc_html__('Download', 'wc-gsheetconnector'); ?></a>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>


                                                                    <input type="hidden" name="wcgsc-nonce" id="wcgsc-nonce"
                                                                    value="<?php echo esc_attr(wp_create_nonce('wcgsc-nonce')); ?>" />

                                                                    <input type="submit" value="<?php echo esc_attr__('Save Settings', 'wc-gsheetconnector'); ?>"
                                                                    id="wcgsc-save-btn" class="wcgsc-save-btn btn btn-primary mt-40" name="wcgsc-save-btn">


                                                                    <div class="wcgsc-google-syc-set1 shadow-box mt-40 p-30">
                                                                        <a class="wcgsc-list-set" data-id="5" href="#0">
                                                                            <p class="maxi_mize maxi_mize5"><i class="fa fa-plus" aria-hidden="true"></i></p>
                                                                            <p class="mini_mize mini_mize5"><i class="fa fa-minus" aria-hidden="true"></i></p>
                                                                            <div class="wc-heading"> <?php echo esc_html(__("Google Sheets Data Sync", 'wc-gsheetconnector')); ?>
                                                                            <span class="pro-ver"><?php echo esc_html(__("PRO", 'wc-gsheetconnector')); ?></span>
                                                                        </div>
                                                                        <p class="mt-10 mb-30"><?php
                                                                       // translators: Explains advanced Google Sheet settings like freezing headers and background colors.
                                                                        echo esc_html__('Effortlessly sync your WooCommerce data with Google Sheets. Available options include Orders, Products, Product Variations, Customers, Coupons, and Subscriptions. Select only the sync types that best suit your needs. This ensures seamless integration and real-time updates for efficient data management.', 'wc-gsheetconnector');
                                                                        ?>
                                                                    </p>
                                                                </a>

                                                                <div class="wcgsc-list-set5" class="popup-click">
                                                                    <div class="sync-card">
                                                                        <div class="wcgsc-syn-btn manually-method-add h-100">
                                                                            <div class="wc-heading mt-0 mb-10"><?php echo esc_html(__("Sync Orders", 'wc-gsheetconnector')); ?></div>
                                                                            <p class="mb-20"><?php echo esc_html(__("Initiate the synchronization of your WooCommerce orders seamlessly with a single click. This ensures that your Google Sheet stays up-to-date with the latest order information, based on your configuration done above, providing you with real-time insights and streamlined data management. Optimize your workflow with the convenience of synchronized orders.", 'wc-gsheetconnector')); ?></p>
                                                                            <div class="d-flex align-center w-100 gap-20 mb-20">
                                                                                <div class="form-group w-100">
                                                                                    <label class="design-syn-ele"><?php echo esc_html(__("From Date", 'wc-gsheetconnector')); ?></label>
                                                                                    <input type="date" name="sync_all_fromdate" id="sync_all_fromdate" class="design-syn-ele" disabled>
                                                                                </div>
                                                                                <div class="form-group w-100">
                                                                                    <label class="design-syn-ele"><?php echo esc_html(__("To Date", 'wc-gsheetconnector')); ?></label>
                                                                                    <input type="date" name="sync_all_todate" id="sync_all_todate" class="design-syn-ele" disabled>
                                                                                </div>
                                                                            </div>

                                                                            <div class="d-flex align-center w-100 gap-20">
                                                                                <div class="form-group w-100">
                                                                                    <label class="design-syn-ele"><?php echo esc_html(__("Orders", 'wc-gsheetconnector')); ?> </label>
                                                                                    <select name="asc_desc_order" id="asc_desc_order" class="design-syn-ele" disabled>
                                                                                        <option value="ASC">
                                                                                            <?php echo esc_html(__("Ascending", 'wc-gsheetconnector')); ?></option>
                                                                                            <option value="DESC">
                                                                                                <?php echo esc_html(__("Descending", 'wc-gsheetconnector')); ?> </option>
                                                                                            </select>
                                                                                        </div>
                                                                                        <div class="form-group w-100">
                                                                                            <label class="design-syn-ele"><?php echo esc_html(__("Order Status", 'wc-gsheetconnector')); ?> </label>
                                                                                            <select name="asc_desc_order" id="asc_desc_order" class="design-syn-ele" disabled>
                                                                                                <option value="ASC">
                                                                                                    <?php echo esc_html(__("All", 'wc-gsheetconnector')); ?></option>
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>
                                                                                        <button type="button" class="button button_primary sync-orders sync-btn design-syn-ele mt-20"
                                                                                        data-type="all">
                                                                                        <?php echo esc_html(__("Sync Orders", 'wc-gsheetconnector')); ?>

                                                                                    </button>
                                                                                    <span id="synctext"></span>
                                                                                    <span class="sync-message-orders sync-message"></span>
                                                                                </div>

                                                                                <div class="wcgsc-syn-btn manually-method-add h-100">
                                                                                    <div class="wc-heading mt-0 mb-10"><?php echo esc_html(__("Sync Products", 'wc-gsheetconnector')); ?></div>
                                                                                    <p class="mb-20"><?php echo esc_html(__("Initiate the synchronization of your WooCommerce products seamlessly with a single click. This ensures that your Google Sheet stays up-to-date with the latest product information, based on your configuration done above, providing you with real-time insights and streamlined data management. Before starting the sync, ensure that the products tab toggle is enabled to optimize your workflow with the convenience of synchronized product data.", 'wc-gsheetconnector')); ?></p>
                                                                                    <div class="d-flex align-center w-100 gap-20 mb-20">
                                                                                        <div class="form-group w-100">
                                                                                            <label class="design-syn-ele"><?php echo esc_html(__("From Date", 'wc-gsheetconnector')); ?></label>
                                                                                            <input type="date" name="sync_all_fromdate_pro" id="sync_all_fromdate_pro"
                                                                                            class="design-syn-ele" disabled>
                                                                                        </div>
                                                                                        <div class="form-group w-100">
                                                                                            <label class="design-syn-ele"><?php echo esc_html(__("To Date", 'wc-gsheetconnector')); ?></label>
                                                                                            <input type="date" name="sync_all_todate_pro" id="sync_all_todate_pro"
                                                                                            class="design-syn-ele" disabled>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="form-group w-100">
                                                                                        <label><?php echo esc_html__('Orders', 'wc-gsheetconnector'); ?>
                                                                                    </label>
                                                                                    <select name="asc_desc_pro" id="asc_desc_pro" class="design-syn-ele" disabled>
                                                                                        <option value="ASC" selected=""><?php echo esc_html(__("Ascending", 'wc-gsheetconnector')); ?></option>
                                                                                        <option value="DESC"><?php echo esc_html(__("Descending", 'wc-gsheetconnector')); ?></option>
                                                                                    </select>
                                                                                </div>

                                                                                <button type="button" class="sync-products sync-btn design-syn-ele mt-20"
                                                                                data-type="wc-products">
                                                                                <?php echo esc_html(__("Sync Products", 'wc-gsheetconnector')); ?>

                                                                            </button>
                                                                            <span id="synctext-product"></span>
                                                                            <span class="sync-message-products sync-message"></span>
                                                                        </div>
                                                                        <div class="wcgsc-syn-btn manually-method-add h-100">
                                                                            <div class="wc-heading mt-0 mb-10"><?php echo esc_html(__("Sync Products Variation", 'wc-gsheetconnector')); ?></div>
                                                                            <p class="mb-20"><?php echo esc_html(__("Initiate the synchronization of your WooCommerce products variations seamlessly with a single click. This ensures that your Google Sheet stays up-to-date with the latest product information, based on your configuration done above. If you are using variable products, then only data will be shown in the sheet. Before starting the sync, ensure that the products variation tab toggle is enabled to optimize your workflow with the convenience of synchronized product data.", 'wc-gsheetconnector')); ?></p>
                                                                            <div class="d-flex align-center w-100 gap-20 mb-20">
                                                                                <div class="form-group w-100">
                                                                                    <label class="design-syn-ele"><?php echo esc_html(__("From Date", 'wc-gsheetconnector')); ?></label>
                                                                                    <input type="date" name="sync_all_fromdate_cus" id="sync_all_fromdate_cus"
                                                                                    class="design-syn-ele" disabled>
                                                                                </div>
                                                                                <div class="form-group w-100">
                                                                                    <label class="design-syn-ele"><?php echo esc_html(__("To Date", 'wc-gsheetconnector')); ?></label>
                                                                                    <input type="date" name="sync_all_todate_cus" id="sync_all_todate_cus"
                                                                                    class="design-syn-ele" disabled>
                                                                                </div>
                                                                            </div>

                                                                            <div class="form-group w-100">
                                                                                <label><?php echo esc_html__('Orders', 'wc-gsheetconnector'); ?></label>
                                                                                <select name="asc_desc_cus" id="asc_desc_cus" class="design-syn-ele" disabled>
                                                                                    <option value="ASC" selected=""><?php echo esc_html(__("Ascending", 'wc-gsheetconnector')); ?></option>
                                                                                    <option value="DESC"><?php echo esc_html(__("Descending", 'wc-gsheetconnector')); ?></option>
                                                                                </select>
                                                                            </div>
                                                                            <button type="button" class="sync-customers sync-btn design-syn-ele mt-20"
                                                                            data-type="wc-customers">
                                                                            <?php echo esc_html(__("Sync Products Variation", 'wc-gsheetconnector')); ?>
                                                                        </button>
                                                                        <span class="sync-message-customers sync-message"></span>
                                                                    </div>

                                                                    <div class="wcgsc-syn-btn manually-method-add h-100">
                                                                        <div class="wc-heading mt-0 mb-10"><?php echo esc_html(__("Sync Customers", 'wc-gsheetconnector')); ?></div>
                                                                        <p class="mb-20"><?php echo esc_html(__("Initiate the synchronization of your WooCommerce Customers seamlessly with a single click. This ensures that your Google Sheet stays up-to-date with the latest order information, based on your configuration done above, providing you with real-time insights and streamlined data management. Optimize your workflow with the convenience of synchronized orders.", 'wc-gsheetconnector')); ?></p>
                                                                        <div class="d-flex align-center w-100 gap-20 mb-20">
                                                                            <div class="form-group w-100">
                                                                                <label class="design-syn-ele"><?php echo esc_html(__("From Date", 'wc-gsheetconnector')); ?></label>
                                                                                <input type="date" name="sync_all_fromdate_cus" id="sync_all_fromdate_cus"
                                                                                class="design-syn-ele" disabled>
                                                                            </div>
                                                                            <div class="form-group w-100">
                                                                                <label class="design-syn-ele"><?php echo esc_html(__("To Date", 'wc-gsheetconnector')); ?></label>
                                                                                <input type="date" name="sync_all_todate_cus" id="sync_all_todate_cus"
                                                                                class="design-syn-ele" disabled>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group w-100">
                                                                            <label><?php echo esc_html__('Orders', 'wc-gsheetconnector'); ?>
                                                                        </label>
                                                                        <select name="asc_desc_cus" id="asc_desc_cus" class="design-syn-ele" disabled>
                                                                            <option value="ASC" selected=""><?php echo esc_html(__("Ascending", 'wc-gsheetconnector')); ?></option>
                                                                            <option value="DESC"><?php echo esc_html(__("Descending", 'wc-gsheetconnector')); ?></option>
                                                                        </select>
                                                                    </div>
                                                                    <button type="button" class="sync-customers sync-btn design-syn-ele mt-20"
                                                                    data-type="wc-customers">
                                                                    <?php echo esc_html(__('Sync Customers', 'wc-gsheetconnector')); ?>
                                                                </button>
                                                                <span class="sync-message-customers sync-message"></span>
                                                            </div>
                                                            <div class="wcgsc-syn-btn manually-method-add h-100">
                                                                <div class="wc-heading mt-0 mb-10"><?php echo esc_html(__("Sync Coupons", 'wc-gsheetconnector')); ?></div>
                                                                <p class="mb-20"><?php echo esc_html(__("Initiate the synchronization of your WooCommerce coupons seamlessly with a single click. This process ensures that your Google Sheet reflects the latest coupon information, following the configuration set above. Benefit from real-time insights and streamlined data management. Before starting the sync, confirm that the coupons tab toggle is enabled enhancing your workflow with the convenience of synchronized coupon data.", 'wc-gsheetconnector')); ?></p>
                                                                <div class="d-flex align-center w-100 gap-20 mb-20">
                                                                    <div class="form-group w-100">
                                                                        <label class="design-syn-ele"><?php echo esc_html(__("From Date", 'wc-gsheetconnector')); ?></label>
                                                                        <input type="date" name="sync_all_fromdate_coupons" id="sync_all_fromdate_coupons"
                                                                        class="design-syn-ele" disabled>
                                                                    </div>
                                                                    <div class="form-group w-100">
                                                                        <label class="design-syn-ele"><?php echo esc_html(__("To Date", 'wc-gsheetconnector')); ?></label>
                                                                        <input type="date" name="sync_all_todate_coupons" id="sync_all_todate_coupons"
                                                                        class="design-syn-ele" disabled>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group w-100">
                                                                    <select name="asc_desc_coupons" id="asc_desc_coupons" class="design-syn-ele" disabled>
                                                                        <option value="ASC" selected=""><?php echo esc_html(__("Ascending", 'wc-gsheetconnector')); ?></option>
                                                                        <option value="DESC"><?php echo esc_html(__("Descending", 'wc-gsheetconnector')); ?></option>
                                                                    </select>
                                                                </div>
                                                                <button type="button" class="sync-coupons sync-btn design-syn-ele mt-20"
                                                                data-type="wc-coupons">
                                                                <?php echo esc_html(__('Sync Coupons', 'wc-gsheetconnector')); ?>
                                                            </button>
                                                            <span class="sync-message-coupons sync-message"></span>
                                                        </div>

                                                        <?php if (is_plugin_active('woocommerce-subscriptions/woocommerce-subscriptions.php')) { ?>
                                                            <div class="wcgsc-syn-btn manually-method-add h-100">
                                                                <div class="wc-heading mt-0 mb-20"> <?php echo esc_html(__('Sync Subscriptions', 'wc-gsheetconnector')); ?> </div>

                                                                <div class="d-flex align-center w-100 gap-20 mb-20">
                                                                    <div class="form-group w-100">
                                                                        <label class="design-syn-ele"><?php echo esc_html(__("From Date", 'wc-gsheetconnector')); ?></label>
                                                                        <input type="date" name="sync_all_fromdate_subscription" id="sync_all_fromdate_subscription"
                                                                        class="design-syn-ele" disabled>
                                                                    </div>
                                                                    <div class="form-group w-100">
                                                                        <label class="design-syn-ele"><?php echo esc_html(__("To Date", 'wc-gsheetconnector')); ?></label>
                                                                        <input type="date" name="sync_all_todate_subscription" id="sync_all_todate_subscription"
                                                                        class="design-syn-ele" disabled>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group w-100">
                                                                    <label class="design-syn-ele"><?php echo esc_html(__("Orders", 'wc-gsheetconnector')); ?></label>
                                                                    <select name="asc_desc_subscription" id="asc_desc_subscription" class="design-syn-ele" disabled>
                                                                        <option value="ASC" selected=""><?php echo esc_html(__("Ascending", 'wc-gsheetconnector')); ?></option>
                                                                        <option value="DESC"><?php echo esc_html(__("Descending", 'wc-gsheetconnector')); ?></option>
                                                                    </select>
                                                                </div>

                                                                <button type="button" class="sync-subscription sync-btn design-syn-ele mt-20"
                                                                data-type="wc-subscription">
                                                                <?php echo esc_html(__('Sync Subscriptions', 'wc-gsheetconnector')); ?>
                                                            </button>
                                                            <span class="sync-message-subscriptions sync-message"></span>
                                                        </div>
                                                    <?php } ?>

                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                </div>

                            </form>
                            <?php
                        }
                        ;
                        ?>