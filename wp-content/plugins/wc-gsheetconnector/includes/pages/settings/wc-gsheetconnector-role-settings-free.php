<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Capability check (IMPORTANT for CASA)
if ( ! current_user_can( 'manage_options' ) ) {
    echo '<p>' . esc_html__( 'Permission not allowed.', 'wc-gsheetconnector' ) . '</p>';
    return;
}

// Correct option name + default value
$wcgsc_page_roles = get_option( 'wcgsc_page_roles_setting', array() );
?>

<form id="wcgsc_role_settings_form" method="post" action="options.php">
    <?php
    settings_fields( 'wcgsc-settings' );
    settings_errors();
    ?>

    <div class="wrap gs-form">
        <div class="card" id="googlesheet">
            <div class="wrap gs-form">
                <div class="wcgsc-card">

                    <label>
                        <?php echo esc_html__( 'Roles that can access Google Sheet Page', 'wc-gsheetconnector' ); ?>
                    </label>

                    <?php
                    // FIX: correct option name pass karo
                    wc_gsheetconnector_utility::instance()->wcgsc_checkbox_roles_multi(
                        'wcgsc_page_roles_setting[]',
                        $wcgsc_page_roles
                    );
                    ?>

                </div>
            </div>
        </div>
    </div>

    <div class="select-info">
        <input 
        type="submit" 
        class="button button-primary button-large"
        name="wcgsc_settings"
        value="<?php echo esc_attr__( 'Save', 'wc-gsheetconnector' ); ?>" 
        />
    </div>
</form>