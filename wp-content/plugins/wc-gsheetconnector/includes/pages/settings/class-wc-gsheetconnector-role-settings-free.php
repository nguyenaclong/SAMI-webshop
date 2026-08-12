<?php

/**
 * Settings class for Gogglesheet Role settings
 * @since 1.0
 */
// Exit if accessed directly
if (! defined('ABSPATH')) exit;


/**
 * WPF_Role_Settings Class
 * @since 1.0
 */
class wc_gsheetconnector_role_settings_free
{

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
    public function __construct()
    {
        add_action('admin_init', array($this, 'init_settings'));
    }

    // White list our options using the Settings API
    public function init_settings()
    {
        register_setting('wcgsc-settings', $this->gs_woo_page_roles_setting_option_name, array($this, 'validate_wcgsc_access_roles'));
    }

    /**
     * do validate and sanitize selected participants
     * @param array $selected_roles
     * @return array $roles
     * @since 1.0
     */
    public function validate_wcgsc_access_roles($selected_roles)
    {
        $roles = array();
        $system_roles = wc_gsheetconnector_utility::instance()->get_system_roles();

        if (! empty($selected_roles) && is_array($selected_roles)) {

            foreach ($system_roles as $role => $display_name) {
                if (in_array($role, $selected_roles, true)) {
                    $roles[ ] = $display_name;
                }
            }
        }
        return $roles;
    }

    public function add_role_setting_page_free()
    {
        if (! current_user_can('manage_options')) {
?>
            <span class="per_not_allo"><?php echo esc_html(__('Permission Not Allowed', 'wc-gsheetconnector')); ?></span>
        <?php
            return;
        }
        $gs_woo_page_roles = get_option($this->gs_woo_page_roles_setting_option_name);
        ?>
        <form id="wcgsc_settings_form" method="post" action="options.php">
            <!--Start Pro Setting(Role Permissions)-->
            <div class="gsc-pro-promo ml-15 mr-pro-15">

                <div class="gsc-pro-header">
                    <div class="gsc-pro-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 19c-1 1-2 1-3 1 0-1 0-2 1-3l4-4"></path>
                            <path d="M14 3l7 7"></path>
                            <path d="M9 18l-4 4"></path>
                            <path d="M15 3c2 0 6 4 6 6-2 2-6 6-8 8l-6-6c2-2 6-8 8-8z"></path>
                            <circle cx="15" cy="9" r="1.5"></circle>
                        </svg>
                    </div>

                    <div>
                        <div class="unlock-header"><?php echo esc_html(__('Unlock Role-Based Access Control', 'wc-gsheetconnector')); ?></div>
                        <span class="gsc-pro-badge"><?php echo esc_html(__('Advanced options are available in PRO', 'wc-gsheetconnector')); ?></span>
                    </div>
                </div>

                <!-- Feature Tabs -->
                <div class="gsc-pro-tabs pt-20 pb-20 pl-20 pr-20">

                    <div>
                        <div class="mb-20 fw-600 text-dark pro-roll-sub-header"><?php echo esc_html(__('Role Permissions', 'wc-gsheetconnector')); ?></div>
                        <div class="gsc-pro-grid">
                            <ul>
                                <li><?php esc_html_e('Allow specific WordPress roles', 'wc-gsheetconnector'); ?></li>
                                <li><?php esc_html_e('Enable/disable integration access', 'wc-gsheetconnector'); ?></li>
                                <li><?php esc_html_e('Control WooCommerce sync visibility', 'wc-gsheetconnector'); ?></li>
                                <li><?php esc_html_e('Restrict settings management', 'wc-gsheetconnector'); ?></li>
                            </ul>
                        </div>
                    </div>

                    <div>
                        <div class="mb-20 fw-600 text-dark pro-roll-sub-header"><?php echo esc_html(__('Security Control', 'wc-gsheetconnector')); ?></div>
                        <div class="gsc-pro-grid">
                            <ul>
                                <li><?php esc_html_e('Prevent unauthorized changes', 'wc-gsheetconnector'); ?></li>
                                <li><?php esc_html_e('Secure Google Sheet credentials', 'wc-gsheetconnector'); ?></li>
                                <li><?php esc_html_e('Role-based configuration control', 'wc-gsheetconnector'); ?></li>
                                <li><?php esc_html_e('Protect integration settings', 'wc-gsheetconnector'); ?></li>
                            </ul>
                        </div>
                    </div>

                    <div>
                        <div class="mb-20 fw-600 text-dark pro-roll-sub-header"><?php echo esc_html(__('Management Benefits', 'wc-gsheetconnector')); ?></div>
                        <div class="gsc-pro-grid">
                            <ul>
                                <li><?php esc_html_e('Grant access to trusted editors', 'wc-gsheetconnector'); ?></li>
                                <li><?php esc_html_e('Hide settings from subscribers', 'wc-gsheetconnector'); ?></li>
                                <li><?php esc_html_e('Team-based permission structure', 'wc-gsheetconnector'); ?></li>
                                <li><?php esc_html_e('Improved dashboard security', 'wc-gsheetconnector'); ?></li>
                            </ul>
                        </div>
                    </div>

                    <div>
                        <div class="mb-20 fw-600 text-dark pro-roll-sub-header"><?php echo esc_html(__('Audit & Monitoring', 'wc-gsheetconnector')); ?></div>
                        <div class="gsc-pro-grid">
                            <ul>
                                <li><?php esc_html_e('Track role-based changes', 'wc-gsheetconnector'); ?></li>
                                <li><?php esc_html_e('Monitor integration access', 'wc-gsheetconnector'); ?></li>
                                <li><?php esc_html_e('Review permission updates', 'wc-gsheetconnector'); ?></li>
                                <li><?php esc_html_e('Maintain admin accountability', 'wc-gsheetconnector'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- CTA -->
                <div class="gsc-pro-footer text-center">
                    <a href="https://www.gsheetconnector.com/woocommerce-google-sheet-connector-pro"
                        target="_blank"
                        class="btn btn-primary text-decoration-none link-hover-white">
                        <?php echo esc_html(__('Upgrade to Unlock', 'wc-gsheetconnector')); ?>
                    </a>
                </div>

            </div>
            <!--End Pro Setting-->


            <div class="wrap w-100 m-0">
                <div class="inner-wrap w-100 bg-white p-40 blur-pro-feature">
                    <div class="heading mt-0"><?php echo esc_html__('Access Management', 'wc-gsheetconnector'); ?></div>
                    <p><?php echo esc_html__('Control which user roles can access and manage WooCommerce Google Sheets integration.', 'wc-gsheetconnector'); ?></p>


                    <div class="wcgsc-card">
                        <div class="gsc-access-wrapper mt-30">
                            <div class="gsc-access-box bg-white pt-15 pb-15 pl-15 pr-15">
                                <div class="para-heading fw-600 mb-20"><?php echo esc_html__('Plugin Access Control', 'wc-gsheetconnector'); ?></div>

                                <div class="wcgsc-card">
                                    <?php
                                    wc_gsheetconnector_utility::instance()->wcgsc_checkbox_roles_multi(
                                        $this->gs_woo_page_roles_setting_option_name . '[]',
                                        $gs_woo_page_roles
                                    );
                                    ?>
                                </div>
                            </div>

                            <div class="gsc-access-info">
                                <div class="para-heading fw-600 mb-20"><?php echo esc_html__('Permission Guidelines', 'wc-gsheetconnector'); ?></div>
                                <ul>
                                    <li><?php echo esc_html__('Control which user roles can access the GSheetConnector plugin', 'wc-gsheetconnector'); ?></li>
                                    <li><?php echo esc_html__('Allow selected users to manage Google Sheets integration settings', 'wc-gsheetconnector'); ?></li>
                                    <li><?php echo esc_html__('Restrict access to sensitive features like feeds, logs, and configurations', 'wc-gsheetconnector'); ?></li>
                                    <li><?php echo esc_html__('Only enabled roles will see the plugin menu in the admin panel', 'wc-gsheetconnector'); ?></li>
                                    <li><?php echo esc_html__('Recommended: Allow only Administrators and trusted Editors', 'wc-gsheetconnector'); ?></li>
                                </ul>
                            </div>
                        </div> <!-- gsc-access-wrapper#end  -->


                    </div> <!-- wcgsc-card #end -->

                    <div class="select-info text-right mt-30">
                        <input type="submit"
                            class="btn btn-primary button-large"
                            name="wcgsc_settings"
                            value="<?php echo esc_html__('Save Settings', 'wc-gsheetconnector'); ?>" />
                    </div>

                </div> <!-- Inner-wrap #end -->
            </div> <!-- wrap #end  -->
        </form>
<?php
    }
}

$wcgsc_role_settings = new wc_gsheetconnector_role_settings_free();
