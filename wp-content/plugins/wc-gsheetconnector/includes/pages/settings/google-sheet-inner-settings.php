<?php

/*
 * Google Sheet configuration and settings page
 * @since 1.0
 */
if (!defined('ABSPATH')) {
    exit;
}
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- safe, only reading tab value
$wcgsc_active_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'integration';

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- safe, only reading tab value
$wcgsc_sub_tab = isset($_GET['sub_tab']) ? sanitize_key(wp_unslash($_GET['sub_tab'])) : 'general_settings';

switch ($wcgsc_active_tab) {
    case 'wc_settings':
        // Render sub-navigation
        echo '<div class="gsc-settings-tabs d-flex gap-15 pl-15 pr-15">';
        $wcgsc_sub_tabs = array(
            'general_settings' => esc_html__('General Settings', 'wc-gsheetconnector'),
            'role_settings' => esc_html__('Role Settings', 'wc-gsheetconnector'),
            'beta_version'  => esc_html__('Version Control', 'wc-gsheetconnector'),
        );

        foreach ($wcgsc_sub_tabs as $wcgsc_sub => $wcgsc_label) {
            $wcgsc_class = ($wcgsc_sub === $wcgsc_sub_tab) ? 'gsc-tab active' : 'gsc-tab';
            echo '<a class="' . esc_attr($wcgsc_class) . '" href="' . esc_url(admin_url('admin.php?page=wc-gsheetconnector-config&tab=wc_settings&sub_tab=' . urlencode($wcgsc_sub))) . '">' . esc_html($wcgsc_label) . '</a>';
        }
        echo '</div> ';

        // Load correct sub-tab content
        switch ($wcgsc_sub_tab) {
            case 'general_settings':
                //echo'<div class="wrap w-100 m-0"><div class="inner-wrap w-100 bg-white p-40">';
                include_once WC_GSHEETCONNECTOR_PATH . 'includes/pages/settings/wc-general-settings.php';
                //echo '</div></div>';
                break;
            case 'role_settings':
                //echo'<div class="wrap w-100 m-0"><div class="inner-wrap w-100 bg-white p-40">';
                $wcgsc_role_settings = new wc_gsheetconnector_role_settings_free();
                $wcgsc_role_settings->add_role_setting_page_free();
                //echo '</div></div>';
                break;
            case 'beta_version':
                include_once WC_GSHEETCONNECTOR_PATH . 'includes/pages/settings/wc-beta-version.php';
                break;
           
        }
        break;
}
?>

<!---Start Support  ticket--->

<div class="gsc-support-card mt-30 welcome-wrapper ml-15">

    <!-- LEFT SIDE -->
    <div class="gsc-support-left">

        <div class="gsc-support-icon d-flex justify-center align-center">
            <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M19.8335 14V3.50004C19.8335 3.19062 19.7106 2.89388 19.4918 2.67508C19.273 2.45629 18.9762 2.33337 18.6668 2.33337H3.50016C3.19074 2.33337 2.894 2.45629 2.6752 2.67508C2.45641 2.89388 2.3335 3.19062 2.3335 3.50004V19.8334L7.00016 15.1667H18.6668C18.9762 15.1667 19.273 15.0438 19.4918 14.825C19.7106 14.6062 19.8335 14.3095 19.8335 14ZM24.5002 7.00004H22.1668V17.5H7.00016V19.8334C7.00016 20.1428 7.12308 20.4395 7.34187 20.6583C7.56066 20.8771 7.85741 21 8.16683 21H21.0002L25.6668 25.6667V8.16671C25.6668 7.85729 25.5439 7.56054 25.3251 7.34175C25.1063 7.12296 24.8096 7.00004 24.5002 7.00004Z" fill="#141B38"></path>
            </svg>
        </div>

        <div class="gsc-content">
            <div class="support-headings"><?php echo esc_html__('Need more support? We\'re here to help.', 'wc-gsheetconnector'); ?></div>

            <a href="https://wordpress.org/support/plugin/wc-gsheetconnector" target="_blank" class="btn btn-primary mt-10 link-hover-white">
                <?php echo esc_html__('Submit a Support Ticket', 'wc-gsheetconnector'); ?>
                <svg width="10" height="10" viewBox="0 0 6 8" fill="#fff" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1.66681 0L0.726807 0.94L3.78014 4L0.726807 7.06L1.66681 8L5.66681 4L1.66681 0Z"></path>
                </svg>
            </a>
        </div>

    </div>

    <!-- RIGHT SIDE -->
    <div class="gsc-support-right">

        <div class="gsc-avatars justify-center">
            <img src="<?php echo esc_url(WC_GSHEETCONNECTOR_URL); ?>/assets/img/avatar-2.jfif" alt="">
            <img src="<?php echo esc_url(WC_GSHEETCONNECTOR_URL); ?>/assets/img/avatar-3.png" alt="">
            <img src="<?php echo esc_url(WC_GSHEETCONNECTOR_URL); ?>/assets/img/avatar-5.jfif" alt="">
            <img src=" <?php echo esc_url(WC_GSHEETCONNECTOR_URL); ?>/assets/img/avatar-4.png" alt="">
            <img src="<?php echo esc_url(WC_GSHEETCONNECTOR_URL); ?>/assets/img/avatar.jpeg" alt="">
        </div>

        <p class="text-center"><?php echo esc_html__('Our fast and friendly support team is always happy to help!', 'wc-gsheetconnector'); ?></p>

    </div>

</div>

<!---End Support  ticket--->