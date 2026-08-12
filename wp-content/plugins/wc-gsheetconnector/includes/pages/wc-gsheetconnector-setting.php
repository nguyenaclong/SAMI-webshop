<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! function_exists( 'is_plugin_active' ) ) {
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
}
?>
<?php
// Get selected Sheet
$wcgsc_selected_sheet_key = get_option( 'wcgsc_settings' );
// Get all sheet details of the connected account
$wcgsc_sheet_data = get_option( 'wcgsc_sheet_feeds' );
// Get order states/ Tab names
$wcgsc_selected_order_states = get_option( 'wcgsc_order_states' );
$wcgsc_service = new wc_gsheetconnector_Service();

$wcgsc_adding_extra_order_row = $wcgsc_service->get_adding_extra_order_row();
$wcgsc_adding_extra_product_item_row = $wcgsc_service->get_adding_extra_product_item_row();
$wcgsc_adding_extra_product_row = $wcgsc_service->get_adding_extra_product_row();


// Check if the user is authenticated
$wcgsc_authenticated = get_option('wcgsc_token');

$wcgsc_per = get_option('wcgsc_verify');
   // check user is authenticated when save existing api method
$wcgsc_show_setting = 0;

if ((!empty($wcgsc_authenticated) && $wcgsc_per == "valid") ) {
    $wcgsc_show_setting = 1;
}
else{
 ?>
 <p class="wcgsc-display-note">
    <?php 
    echo wp_kses_post( __(
        '<strong>Authentication Required:</strong>
        You must have to <a href="admin.php?page=wc-gsheetconnector-config&tab=integration" target="_blank">Authenticate using your Google Account</a> along with Google Drive and Google Sheets Permissions in order to enable the settings for configuration.',
        'wc-gsheetconnector'
    ) );
    ?> 
</p>
<?php 
}

if($wcgsc_show_setting == 1){
  ?>
  <form method="post" id="gsSettingFormFree">

    <div class="wcgsc-fields">
        <h2><?php echo esc_html( __( 'WooCommerce Google Sheet Settings', 'wc-gsheetconnector' ) ); ?></h2>


        <div class="wcgsc-in-fields">
            <div class="sheet-details">
                <div class="row">
                    <label><?php echo esc_html__( 'Google Sheet Name', 'wc-gsheetconnector' ); ?></label>
                    <select name="wcgsc-sheet-id" id="wcgsc-sheet-id">
                        <option value=""><?php echo esc_html__( 'Select', 'wc-gsheetconnector' ); ?></option>

                        <?php
                        if ( ! empty( $wcgsc_sheet_data ) ) {
                            foreach ( $wcgsc_sheet_data as $wcgsc_key => $wcgsc_value ) {
                                $wcgsc_selected = '';
                                if ( $wcgsc_selected_sheet_key !== '' && $wcgsc_key == $wcgsc_selected_sheet_key ) {
                                    $wcgsc_selected = 'selected';
                                }
                                ?>
                                <option value="<?php echo esc_attr( $wcgsc_key ); ?>" <?php echo esc_attr( $wcgsc_selected ); ?>>
                                    <?php echo esc_html( $wcgsc_value['sheet_name'] ); ?>
                                </option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                    <span class="error_msg" id="error_spread"></span>
                    <span class="loading-sign">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                    <input type="hidden" name="wcgsc-ajax-nonce" id="wcgsc-ajax-nonce"
                    value="<?php echo esc_attr( wp_create_nonce( 'wcgsc-ajax-nonce' ) ); ?>" />
                </div>

                <div class="sheet-url row" id="sheet-url">
                    <?php
                    $wcgsc_sheet_id = '';
                    if ( ! empty( $wcgsc_selected_sheet_key ) ) {
                        $wcgsc_sheet_id = $wcgsc_selected_sheet_key;
                        ?>
                        <label><?php echo esc_html__( 'Google Sheet URL', 'wc-gsheetconnector' ); ?></label>
                        <a href="https://docs.google.com/spreadsheets/d/<?php echo esc_attr( $wcgsc_sheet_id ); ?>" target="_blank">
                            <input type="button" id="viewsheet" name="viewsheet" value="<?php echo esc_attr__( 'View Spreadsheet', 'wc-gsheetconnector' ); ?>">
                        </a>
                        <?php
                    }
                    ?>
                </div>



                <p class="wcgsc-sync-row">
                  <?php
                  printf(
                            // translators: %s is the HTML <a> link for syncing WooCommerce settings.
                    esc_html__( 'Spreadsheet Name and URL not showing? %s to fetch sheets', 'wc-gsheetconnector' ),
                    '<a id="wcgsc-sync" data-init="yes">&nbsp;' . esc_html__( 'Click here', 'wc-gsheetconnector' ) . '&nbsp;</a>'
                );
                ?> 

                <span class="loading-sign">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
            </p>

            <p id="wcgsc-validation-message"></p>
        </div>
    </div>

</div>


<div class="wcgsc-tabs-set" >
    <h2><?php echo esc_html( __( 'Google Sheets/Tab Name ', 'wc-gsheetconnector' ) ); ?></h2>
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

    foreach ( $wcgsc_order_state_list as $wcgsc_key => $wcgsc_state_name ) {
       $wcgsc_order_state_checked = "";
       if(!empty($wcgsc_selected_order_states)){
        if ( in_array( $wcgsc_key, $wcgsc_selected_order_states ) ) {
         $wcgsc_order_state_checked = "checked";
     }
 }
 ?>
 <div class="wcgsc-cards">
    <span class="wcgsc-pointer">
        <input type="checkbox" class="wcgsc_order_state check-toggle" name="wcgsc_order_state[]"
        value="<?php echo esc_attr( $wcgsc_key ); ?>"
        <?php echo checked( $wcgsc_order_state_checked, 'checked', false ); ?>
        id="<?php echo esc_attr( $wcgsc_key ); ?>"
        style="display: none;">
        <?php echo esc_html( $wcgsc_state_name ); ?>
        <label for="<?php echo esc_attr( $wcgsc_key ); ?>" class="button-wcgsc-toggle"></label>
    </span>
</div>

<?php } ?>

<div class="wcgsc-cards1">
    <span class="wcgsc-pointer">
        <?php echo esc_html__( 'All Orders ', 'wc-gsheetconnector' ); ?>
        <label for="pro" class="button-wcgsc-toggle tooltip11">
            <span class="tooltiptext11"><?php esc_html_e( 'Upgrade To Pro', 'wc-gsheetconnector' ); ?></span>

        </label>
    </span>
</div>



</div>

<div class="wcgsc-header1" hidden>
    <h2><?php echo esc_html( __( 'Headers ', 'wc-gsheetconnector' ) ); ?></h2>

    <ul>
        <?php 
        $wcgsc_header_list = $wcgsc_service->sheet_headers;
        foreach( $wcgsc_header_list as $wcgsc_header => $wcgsc_data ) { ?>
           <li class="li-wcgsc-header1">
            <i class="fa fa-sort sort-icon1"></i>
            <div class="switch-label1">
                <label>
                    <span class='label1'>
                        <div class='label_text1'><?php echo esc_html( $wcgsc_header ); ?></div>
                        <div class="edit_col_name1">
                            <span class="tooltip11">
                                <span class="tooltiptext11"><?php esc_html_e( 'Upgrade To Pro', 'wc-gsheetconnector' ); ?></span>

                                <i class="fa fa-pencil"></i>
                            </span>
                        </div>
                    </span>
                </label>
            </div>

            <div class="toggle-buttom-pos">
                <span class="tooltip11">
                    <span class="tooltiptext11"><?php esc_html_e( 'Upgrade To Pro', 'wc-gsheetconnector' ); ?></span>

                    <label for="<?php echo esc_attr( $wcgsc_header ); ?>-one"
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
    <div class="wcgsc-google-set">
        <a class="wcgsc-list-set" data-id="12" href="#0">
            <p class="maxi_mize maxi_mize12"><i class="fa fa-plus" aria-hidden="true"></i></p>
            <p class="mini_mize mini_mize12"><i class="fa fa-minus" aria-hidden="true"></i></p>
            <h2><?php echo esc_html( __( 'Custom Order Status', 'wc-gsheetconnector' ) ); ?> 
            <span class="pro-ver"><?php echo esc_html__( 'PRO', 'wc-gsheetconnector' ); ?></span>
        </h2>
    </a> 

    <div class="wcgsc-list-set12">
        <?php 
        $wcgsc_corder_statuses = wc_get_order_statuses();
        $wcgsc_custom_order_status = array_diff( $wcgsc_corder_statuses, [
            'wc-pending', 'wc-processing', 'wc-on-hold',
            'wc-completed', 'wc-cancelled', 'wc-refunded',
            'wc-failed', 'wc-draft'
        ] );

        if ( ! empty( $wcgsc_custom_order_status ) ) {
            foreach ( $wcgsc_custom_order_status as $wcgsc_key => $wcgsc_state_name ) {
                ?>
                <span class="wcgsc-cards1">
                    <span class="wcgsc-pointer">
                        <?php echo esc_html( $wcgsc_state_name ); ?>
                        <label for="pro" class="button-wcgsc-toggle tooltip11">
                            <span class="tooltiptext11"><?php esc_html_e( 'Upgrade To Pro', 'wc-gsheetconnector' ); ?></span>

                        </label>
                    </span>
                </span>
                <?php
            }
        } else {
            ?>
            <h4 style="margin-left: 40%;">
                <?php echo esc_html__( 'No Custom Orders found in your WooCommerce Store', 'wc-gsheetconnector' ); ?>
            </h4>
            <?php
        }
        ?>
    </div>
</div>




<div class="wcgsc-google-set" >
    <a class="gs-woo-list-set" data-id="13" href="#0">
        <p class="maxi_mize maxi_mize13"><i class="fa fa-plus" aria-hidden="true"></i></i></p>
        <p class="mini_mize mini_mize13"><i class="fa fa-minus" aria-hidden="true"></i></p>
        <h2> <?php echo esc_html( __( ' Other Sheet Tabs to Enable ', 'wc-gsheetconnector' ) ); ?> 
        <span class="pro-ver"><?php echo esc_html( __( 'PRO', 'wc-gsheetconnector' ) ); ?></span>

    </h2>
</a> 
<!-- Other Sheet Tabs to Enable -->
<div class="wcgsc-list-set13">

    <div class="wcgsc-cards1">
        <span class="wcgsc-pointer">
            <?php echo esc_html( __( 'All Products', 'wc-gsheetconnector' ) ); ?>
            <label for="pro" class="button-wcgsc-toggle tooltip11">
                <span class="tooltiptext11"><?php esc_html_e( 'Upgrade To Pro', 'wc-gsheetconnector' ); ?></span>

            </label>
        </span>
    </div>

    <div class="wcgsc-cards1">
        <span class="wcgsc-pointer">
            <?php echo esc_html( __( 'All Products Variation', 'wc-gsheetconnector' ) ); ?>
            <label for="pro" class="button-wcgsc-toggle tooltip11">
                <span class="tooltiptext11"><?php esc_html_e( 'Upgrade To Pro', 'wc-gsheetconnector' ); ?></span>

            </label>
        </span>
    </div>

    <div class="wcgsc-cards1">
        <span class="wcgsc-pointer">
            <?php echo esc_html( __( 'All Customers', 'wc-gsheetconnector' ) ); ?>
            <label for="pro" class="button-wcgsc-toggle tooltip11">
                <span class="tooltiptext11"><?php esc_html_e( 'Upgrade To Pro', 'wc-gsheetconnector' ); ?></span>

            </label>
        </span>
    </div>

    <div class="wcgsc-cards1">
        <span class="wcgsc-pointer">
            <?php echo esc_html( __( 'All Coupons', 'wc-gsheetconnector' ) ); ?>
            <label for="pro" class="button-wcgsc-toggle tooltip11">
                <span class="tooltiptext11"><?php esc_html_e( 'Upgrade To Pro', 'wc-gsheetconnector' ); ?></span>

            </label>
        </span>
    </div>

    <?php if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) { ?>
        <div class="wcgsc-cards1">
            <span class="wcgsc-pointer">
                <?php echo esc_html( __( 'All Subscriptions', 'wc-gsheetconnector' ) ); ?>
                <label for="pro" class="button-wcgsc-toggle tooltip11">
                    <span class="tooltiptext11"><?php esc_html_e( 'Upgrade To Pro', 'wc-gsheetconnector' ); ?></span>

                </label>
            </span>
        </div>
    <?php } ?>

</div>


</div>


<!-- product category filter start-->
<div  class="wcgsc-google-set">
    <a class="wcgsc-list-set" data-id="7" href="#0">
        <p class="maxi_mize maxi_mize7"><i class="fa fa-plus" aria-hidden="true"></i></i></p>
        <p class="mini_mize mini_mize7"><i class="fa fa-minus" aria-hidden="true"></i></p>
        <h2> <?php echo esc_html( __( 'Product Category Filter', 'wc-gsheetconnector' ) ); ?> 
        <span class="pro-ver">
            <?php esc_html_e( 'PRO', 'wc-gsheetconnector' ); ?>
        </span>
    </h2>
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
        <div class="wcgsc-cards1">
            <span class="wcgsc-pointer">
                <?php echo esc_html( __( 'Select All Category', 'wc-gsheetconnector' ) ); ?>
                <label for="pro" class="button-wcgsc-toggle tooltip11">
                   <span class="tooltiptext11"><?php esc_html_e( 'Upgrade To Pro', 'wc-gsheetconnector' ); ?></span>

               </label>
           </span>
       </div>

       <?php 
       foreach ( $wcgsc_product_categories as $wcgsc_key => $wcgsc_category ) {
          ?>
          <div class="wcgsc-cards1">
            <span class="wcgsc-pointer">
                <?php echo esc_html( $wcgsc_category->name ); ?>
                <label for="pro" class="button-wcgsc-toggle tooltip11">
                    <span class="tooltiptext11"><?php echo esc_html__( 'Upgrade To Pro', 'wc-gsheetconnector' ); ?></span>
                </label>
            </span>


        </div>

    <?php } 

    ?>
</div>
<?php } ?>
</div>
<br class="clear">
<!-- order category filter start-->
<div  class="wcgsc-google-set">
    <a class="wcgsc-list-set" data-id="8" href="#0">
        <p class="maxi_mize maxi_mize8"><i class="fa fa-plus" aria-hidden="true"></i></i></p>
        <p class="mini_mize mini_mize8"><i class="fa fa-minus" aria-hidden="true"></i></p>
        <h2> <?php echo esc_html( __( 'Order Category Filter', 'wc-gsheetconnector' ) ); ?> 
        <span class="pro-ver"><?php echo esc_html( __( 'PRO', 'wc-gsheetconnector' ) ); ?></span>

    </h2>
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
        <div class="wcgsc-cards1">
            <span class="wcgsc-pointer">
               <?php echo esc_html( __( 'Select All Category', 'wc-gsheetconnector' ) ); ?> 
               <label for="pro" class="button-wcgsc-toggle tooltip11">
                   <span class="tooltiptext11"><?php esc_html_e( 'Upgrade To Pro', 'wc-gsheetconnector' ); ?></span>


               </label>
           </span>
       </div>

       <?php 
       foreach ( $wcgsc_order_categories as $wcgsc_key => $wcgsc_category ) {
          ?>
          <div class="wcgsc-cards1">
            <span class="wcgsc-pointer">
                <?php echo esc_html( $wcgsc_category->name ); ?>
                <label for="pro" class="button-wcgsc-toggle tooltip11">
                    <span class="tooltiptext11"><?php echo esc_html__( 'Upgrade To Pro', 'wc-gsheetconnector' ); ?></span>
                </label>
            </span>

        </div>

    <?php } 

    ?>
</div>
<?php } ?>
</div>

<!-- order category filter end-->
<div id="gform_setting_gsheet_field_maps" class="gform-settings-field gform-settings-field__map_form_fields"
titlea="Upgrade to Pro">

<div class="wcgsc-google-set" >

    <a class="wcgsc-list-set" data-id="3" href="#0">
        <p class="maxi_mize maxi_mize3"><i class="fa fa-plus" aria-hidden="true"></i></i></p>
        <p class="mini_mize mini_mize3"><i class="fa fa-minus" aria-hidden="true"></i></p>
        <h2> <?php echo esc_html( __( 'Google Sheet Headers (Column Name) ', 'wc-gsheetconnector' ) ); ?> 
        <span class="pro-ver"><?php echo esc_html( __( 'PRO', 'wc-gsheetconnector' ) ); ?></span>
    </h2>
</a>

<div class="wcgsc-header-wrapper wcgsc-list-set3">

    <div class="tabs-gs-back">
        <div class="tabs-gs">

            <a class="wcgsc-list-set active-t-gs" data-id="31" href="#0"><?php echo esc_html( __( 'Orders Header', 'wc-gsheetconnector' ) ); ?></a>
            <a class="wcgsc-list-set" data-id="32" href="#0"><?php echo esc_html( __( 'Products Header', 'wc-gsheetconnector' ) ); ?></a>
            <a class="wcgsc-list-set" data-id="34" href="#0"><?php echo esc_html( __( 'Product Variation Header', 'wc-gsheetconnector' ) ); ?></a>
            <a class="wcgsc-list-set" data-id="33" href="#0"><?php echo esc_html( __( 'Customers Header', 'wc-gsheetconnector' ) ); ?></a>
            <a class="wcgsc-list-set" data-id="35" href="#0"><?php echo esc_html( __( 'Coupons Header', 'wc-gsheetconnector' ) ); ?></a>
            <?php if (is_plugin_active('woocommerce-subscriptions/woocommerce-subscriptions.php')) { ?>
                <a class="wcgsc-list-set" data-id="36" href="#0"><?php echo esc_html( __( 'Subscriptions Header', 'wc-gsheetconnector' ) ); ?></a>
            <?php } ?>
        </div>
    </div>
    <br class="clear">
    <div class="wcgsc-header-wrapper wcgsc-list-set31" id="extra-field">
        <div class="checkallmaindiv">
            <div class="extra-all-main">
                <table class="table table-light adding_extra_table">
                    <tbody>
                        <tr>
                            <td><label class="check-all-lbl"><?php echo esc_html( __( 'Extra Header Related To Order', 'wc-gsheetconnector' ) ); ?></label></td>
                            <td>
                                <select class="adding_extra_order_row adding_extra_css"
                                id="adding_extra_order_row">
                                <option value=""><?php echo esc_html('--Select--','wc-gsheetconnector'); ?></option>
                                <?php if(!empty($wcgsc_adding_extra_order_row)){
                                    foreach ($wcgsc_adding_extra_order_row as $wcgsc_key => $wcgsc_value) {
                                        ?>
                                        <option value="<?php echo esc_attr( $wcgsc_value ); ?>" disabled>
                                            <?php echo esc_html( $wcgsc_value ); ?>
                                        </option>

                                        <?php
                                    }
                                }
                                ?>
                            </select>
                        </td>
                        <td><label class="check-all-lbl" disabled><?php echo esc_html( __( 'Label', 'wc-gsheetconnector' ) ); ?></label></td>
                        <td>
                            <input type="text" name="ext_row_label_order" id="ext_row_label_order"
                            class="ext_row_label_order" disabled />
                        </td>
                        <td><button type="button" id="btn_extra_order_row"
                            class="btn_extra_order_row tooltip11">
                            <?php echo esc_html( __( ' Add New Extra Fields', 'wc-gsheetconnector' ) ); ?>
                            <span class="tooltiptext11"><?php echo esc_html( __( ' Upgrade To Pro', 'wc-gsheetconnector' ) ); ?></span>
                        </button>
                    </td>
                    <td>
                        <span
                        class="loading-btn-extra-order-row">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                    </td>
                </tr>
                <tr>
                    <td><label class="check-all-lbl"><?php echo esc_html( __( "Extra Header Related To Order's Product", 'wc-gsheetconnector' ) ); ?></label>
                    </td>
                    <td>
                        <select class="adding_extra_product_item_row adding_extra_css"
                        id="adding_extra_product_item_row">
                        <option value=""><?php echo esc_html('--Select--','wc-gsheetconnector'); ?></option>
                        <?php if(!empty($wcgsc_adding_extra_product_item_row)){
                            foreach ($wcgsc_adding_extra_product_item_row as $wcgsc_key => $wcgsc_value) {
                                ?>
                                <option value="<?php echo esc_attr( $wcgsc_value ); ?>" disabled>
                                    <?php echo esc_html( $wcgsc_value ); ?>
                                </option>

                                <?php
                            }
                        }
                        ?>
                    </select>
                </td>
                <td><label class="check-all-lbl" disabled> <?php echo esc_html( __( ' Label', 'wc-gsheetconnector' ) ); ?></label></td>
                <td><input type="text" name="ext_row_label_order_item_row"
                    id="ext_row_label_order_item_row" class="ext_row_label_order_item_row"
                    disabled />
                </td>
                <td><button type="button" id="btn_extra_order_item_row"
                    class="btn_extra_order_item_row tooltip11">
                    <?php echo esc_html( __( ' Add New Extra Fields', 'wc-gsheetconnector' ) ); ?>
                    <span class="tooltiptext11"><?php echo esc_html( __( ' Upgrade To Pro', 'wc-gsheetconnector' ) ); ?></span>
                </button>
            </td>
            <td>
                <span
                class="loading-btn-extra-order-item-row">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
            </td>
        </tr>


        <tr>
            <td><label class="check-all-lbl"><?php echo esc_html( __( ' Custom Static Headers', 'wc-gsheetconnector' ) ); ?></label>
            </td>
            <td>
                <?php 
                $wcgsc_adding_custom_static_headers = array('ip_address' => 'IP Address','site_name'=> 'Site Name','site_url'=> 'Site URL','site_admin_email'=>'Site Admin Email','site_description'=>'Site Description','user_agent'=> 'User Agent','user_name'=> 'User Name','user_login'=>'User Login','user_email'=>'User Email');

                ?>
                <select class="adding_custom_static_headers adding_extra_css"
                id="adding_custom_static_headers">
                <option value=""><?php echo esc_html('--Select--','wc-gsheetconnector'); ?></option>
                <?php if(!empty($wcgsc_adding_custom_static_headers)){
                    foreach ($wcgsc_adding_custom_static_headers as $wcgsc_key => $wcgsc_value) {
                        ?>
                        <option value="<?php echo esc_attr( $wcgsc_value ); ?>" disabled>
                            <?php echo esc_html( $wcgsc_value ); ?>
                        </option>
                        <?php
                    }
                }
                ?>
            </select>
        </td>
        <td><label class="check-all-lbl" disabled><?php echo esc_html( __( ' Label', 'wc-gsheetconnector' ) ); ?></label></td>
        <td><input type="text" name="ext_row_custom_static_headers"
            id="ext_row_custom_static_headers" class="ext_row_custom_static_headers"
            disabled />
        </td>
        <td><button type="button" id="btn_custom_static_headers"
            class="btn_custom_static_headers tooltip11">
            <?php echo esc_html( __( ' Add New Custom Static Headers', 'wc-gsheetconnector' ) ); ?>
            <span class="tooltiptext11"><?php echo esc_html( __( ' Upgrade To Pro', 'wc-gsheetconnector' ) ); ?></span>
        </button>
    </td>
    <td>
        <span
        class="loading-btn-custom-static-headers">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
    </td>
</tr>

<tr>
    <td><label class="check-all-lbl"><?php echo esc_html( __( 'Custom Static Blank Headers', 'wc-gsheetconnector' ) ); ?></label>
    </td>
    <td>
        <?php 
        $wcgsc_adding_custom_static_blank_headers = array('blank1' => 'Blank1','blank2'=> 'Blank2','blank3'=> 'Blank3','blank4'=>'Blank4','blank5'=>'Blank5','blank6'=> 'Blank6','blank7'=> 'Blank7','blank8'=>'Blank8','blank9'=>'Blank9','blank10'=>'Blank10');
        ?>
        <select class="adding_custom_static_blank_headers adding_extra_css"
        id="adding_custom_static_blank_headers">
        <option value=""><?php echo esc_html('--Select--','wc-gsheetconnector'); ?></option>
        <?php if(!empty($wcgsc_adding_custom_static_blank_headers)){
            foreach ($wcgsc_adding_custom_static_blank_headers as $wcgsc_key => $wcgsc_value) {
                ?>
                <option value="<?php echo esc_attr( $wcgsc_value ); ?>" disabled>
                    <?php echo esc_html( $wcgsc_value ); ?>
                </option>

                <?php
            }
        }
        ?>
    </select>
</td>
<td><label class="check-all-lbl" disabled><?php echo esc_html( __( 'Label', 'wc-gsheetconnector' ) ); ?></label></td>
<td><input type="text" name="ext_row_custom_blank_headers"
    id="ext_row_custom_blank_headers" class="ext_row_custom_blank_headers"
    disabled />
</td>
<td><button type="button" id="btn_custom_blank_headers"
    class="btn_custom_blank_headers tooltip11">
    <?php echo esc_html( __( 'Add New Custom Static Blank Headers', 'wc-gsheetconnector' ) ); ?> 
    <span class="tooltiptext11"><?php echo esc_html( __( 'Upgrade To Pro', 'wc-gsheetconnector' ) ); ?></span>
</button>
</td>
<td>
    <span
    class="loading-btn-custom-blank-headers">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
</td>
</tr>

</tbody>
</table>
</div>
<div class="checked-all-div">
    <label class="check-all-lbl"><?php echo esc_html( __( 'Check All', 'wc-gsheetconnector' ) ); ?> </label>
    <span class="tooltip11"><span class="tooltiptext11"><?php echo esc_html__( 'Upgrade To Pro', 'wc-gsheetconnector' ); ?></span>
    <!-- Toggle button -->
    <label class="button-wcgsc-toggle1 sheet_headers-order button-tog-inactive"
    id="button-wcgsc-toggle1-click-<?php echo esc_attr($wcgsc_header); ?>" data-id="sheet_headers-"
    style="float: left;margin-top: 5px;"></label>
</div>
</div>
<ul class="wcgsc-header">
    <ul>
        <?php 
        $wcgsc_header_list = $wcgsc_service->sheet_headers;
        foreach( $wcgsc_header_list as $wcgsc_header => $wcgsc_data ) { ?>
            <li class="li-wcgsc-header1">
                <i class="fa fa-sort sort-icon1"></i>
                <div class="switch-label1">
                    <label>
                        <span class='label1'>
                            <div class='label_text1'>
                                <?php echo esc_html( $wcgsc_header ); ?></div>
                                <div class="edit_col_name1">
                                    <span class="tooltip11">
                                        <span class="tooltiptext11">
                                            <?php echo esc_html__('Upgrade To Pro', 'wc-gsheetconnector'); ?>
                                        </span>
                                        <i class="fa fa-pencil"></i>
                                    </span>
                                </div>
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
                        </span>

                    </li>
                <?php } ?>

                <?php 
                $wcgsc_header_list_pro = $wcgsc_service->sheet_headers_pro;
                foreach( $wcgsc_header_list_pro as $wcgsc_header  ) { ?>
                    <li class="li-wcgsc-header1">
                        <i class="fa fa-sort sort-icon1"></i>
                        <div class="switch-label1">
                            <label>
                                <span class='label1'>
                                    <div class='label_text1'>
                                        <?php echo esc_html( $wcgsc_header ); ?></div>
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
                                    <td><label class="check-all-lbl"><?php echo esc_html( __( 'Additional Headers for Products', 'wc-gsheetconnector' ) ); ?></label></td>
                                    <td>
                                        <select class="adding_extra_order_row adding_extra_css"
                                        id="adding_extra_order_row">
                                        <option value=""><?php echo esc_html('--Select--','wc-gsheetconnector'); ?></option>
                                        <?php if(!empty($wcgsc_adding_extra_product_row)){
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
                                <td><label class="check-all-lbl" disabled><?php echo esc_html( __( 'Label', 'wc-gsheetconnector' ) ); ?></label></td>
                                <td>
                                    <input type="text" name="ext_row_label_order" id="ext_row_label_order"
                                    class="ext_row_label_order" disabled />
                                </td>
                                <td><button type="button" id="btn_extra_order_row"
                                    class="btn_extra_order_row tooltip11">
                                    <?php echo esc_html( __( ' Add New Extra Fields', 'wc-gsheetconnector' ) ); ?>
                                    <span class="tooltiptext11"><?php echo esc_html( __( ' Upgrade To Pro', 'wc-gsheetconnector' ) ); ?></span>
                                </button>
                            </td>
                            <td>
                                <span
                                class="loading-btn-extra-order-row">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="checked-all-div">
                <label class="check-all-lbl"><?php echo esc_html( __( 'Check All', 'wc-gsheetconnector' ) ); ?></label>
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
        foreach( $wcgsc_header_list_pro2 as $wcgsc_header  ) { ?>
           <li class="li-wcgsc-header1">
            <i class="fa fa-sort sort-icon1"></i>
            <div class="switch-label1">
                <label>
                    <span class='label1'>
                        <div class='label_text1'><?php echo esc_html( $wcgsc_header ); ?></div>
                        <div class="edit_col_name1">
                            <span class="tooltip11">
                                <span class="tooltiptext11"><?php esc_html_e( 'Upgrade To Pro', 'wc-gsheetconnector' ); ?></span>
                                <i class="fa fa-pencil"></i>
                            </span>
                        </div>
                    </label>
                </div>

                <div class="toggle-buttom-pos">
                    <span class="tooltip11">
                        <span class="tooltiptext11"><?php esc_html_e( 'Upgrade To Pro', 'wc-gsheetconnector' ); ?></span>
                        <label for="<?php echo esc_attr( $wcgsc_header ) . '-one'; ?>"
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
<div class="wcgsc-header-wrapper wcgsc-list-set33" >
    <div class="checkallmaindiv">
        <div class="checked-all-div">
            <label class="check-all-lbl"><?php echo esc_html( __( 'Check All', 'wc-gsheetconnector' ) ); ?></label>
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
        foreach( $wcgsc_header_list_pro3 as $wcgsc_header  ) { ?>


            <li class="li-wcgsc-header1">
                <i class="fa fa-sort sort-icon1"></i>
                <div class="switch-label1">
                    <label>
                        <span class='label1'>
                            <div class='label_text1'><?php echo esc_html( $wcgsc_header ); ?>
                        </div>
                        <div class="edit_col_name1">
                            <span class="tooltip11">
                                <span class="tooltiptext11"><?php esc_html_e( 'Upgrade To Pro', 'wc-gsheetconnector' ); ?></span>
                                <i class="fa fa-pencil"></i>
                            </span>
                        </div>
                        <label>

                        </div>

                        <div class="toggle-buttom-pos">
                            <span class="tooltip11">
                                <span class="tooltiptext11"><?php esc_html_e( 'Upgrade To Pro', 'wc-gsheetconnector' ); ?></span>
                                <label for="<?php echo esc_attr( $wcgsc_header ) . '-one'; ?>"
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
        <div class="wcgsc-header-wrapper wcgsc-list-set34" >
            <div class="checkallmaindiv">
                <table class="table table-light adding_extra_table">
                    <tbody>
                        <tr>
                            <td><label class="check-all-lbl"><?php echo esc_html( __( 'Additional Headers for Product Variation', 'wc-gsheetconnector' ) ); ?></label></td>
                            <td>
                                <select class="adding_extra_order_row adding_extra_css"
                                id="adding_extra_order_row">
                                <option value=""><?php echo esc_html('--Select--','wc-gsheetconnector'); ?></option>
                                <?php if(!empty($wcgsc_adding_extra_product_row)){
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
                        <td><label class="check-all-lbl" disabled><?php echo esc_html( __( 'Label', 'wc-gsheetconnector' ) ); ?></label></td>
                        <td>
                            <input type="text" name="ext_row_label_order" id="ext_row_label_order"
                            class="ext_row_label_order" disabled />
                        </td>
                        <td><button type="button" id="btn_extra_order_row"
                            class="btn_extra_order_row tooltip11">
                            <?php echo esc_html( __( ' Add New Extra Fields', 'wc-gsheetconnector' ) ); ?>
                            <span class="tooltiptext11"><?php echo esc_html( __( ' Upgrade To Pro', 'wc-gsheetconnector' ) ); ?></span>
                        </button>
                    </td>
                    <td>
                        <span
                        class="loading-btn-extra-order-row">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="checked-all-div">
            <label class="check-all-lbl"><?php echo esc_html( __( 'Check All', 'wc-gsheetconnector' ) ); ?></label>
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
        foreach( $wcgsc_header_list_pro4 as $wcgsc_header  ) { ?>


            <li class="li-wcgsc-header1">
                <i class="fa fa-sort sort-icon1"></i>
                <div class="switch-label1">
                    <label>
                        <span class='label1'>
                            <div class='label_text1'><?php echo esc_html( $wcgsc_header ); ?>
                        </div>
                        <div class="edit_col_name1">
                            <span class="tooltip11">
                                <span class="tooltiptext11"><?php esc_html_e( 'Upgrade To Pro', 'wc-gsheetconnector' ); ?></span>
                                <i class="fa fa-pencil"></i>
                            </span>
                        </div>
                        <label>

                        </div>

                        <div class="toggle-buttom-pos">
                            <span class="tooltip11">
                                <span class="tooltiptext11"><?php esc_html_e( 'Upgrade To Pro', 'wc-gsheetconnector' ); ?></span>
                                <label for="<?php echo esc_attr( $wcgsc_header ) . '-one'; ?>"
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

        <div class="wcgsc-header-wrapper wcgsc-list-set35" >
            <div class="checkallmaindiv">
                <div class="checked-all-div">
                    <label class="check-all-lbl"><?php echo esc_html( __( 'Check All', 'wc-gsheetconnector' ) ); ?></label>
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
                foreach( $wcgsc_header_list_pro5 as $wcgsc_header  ) { ?>
                    <li class="li-wcgsc-header1">
                        <i class="fa fa-sort sort-icon1"></i>
                        <div class="switch-label1">
                            <label>
                                <span class='label1'>
                                    <div class='label_text1'><?php echo esc_html( $wcgsc_header ); ?>
                                </div>
                                <div class="edit_col_name1">
                                    <span class="tooltip11">
                                        <span class="tooltiptext11"><?php esc_html_e( 'Upgrade To Pro', 'wc-gsheetconnector' ); ?></span>
                                        <i class="fa fa-pencil"></i>
                                    </span>
                                </div>
                                <label>
                                </div>
                                <div class="toggle-buttom-pos">
                                    <span class="tooltip11">
                                        <span class="tooltiptext11"><?php esc_html_e( 'Upgrade To Pro', 'wc-gsheetconnector' ); ?></span>
                                        <label for="<?php echo esc_attr( $wcgsc_header ) . '-one'; ?>"
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
                    <div class="wcgsc-header-wrapper wcgsc-list-set36" >
                        <div class="checkallmaindiv">
                            <div class="checked-all-div">
                                <label class="check-all-lbl"><?php echo esc_html( __( 'Check All', 'wc-gsheetconnector' ) ); ?></label>
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
                            foreach( $wcgsc_header_list_pro6 as $wcgsc_header  ) { ?>
                                <li class="li-wcgsc-header1">
                                    <i class="fa fa-sort sort-icon1"></i>
                                    <div class="switch-label1">
                                        <label>
                                            <span class='label1'>
                                                <div class='label_text1'><?php echo esc_html( $wcgsc_header ); ?>
                                            </div>
                                            <div class="edit_col_name1">
                                                <span class="tooltip11">
                                                    <span class="tooltiptext11"><?php esc_html_e( 'Upgrade To Pro', 'wc-gsheetconnector' ); ?></span>
                                                    <i class="fa fa-pencil"></i>
                                                </span>
                                            </div>
                                            <label>

                                            </div>
                                            <div class="toggle-buttom-pos">
                                                <span class="tooltip11">
                                                    <span class="tooltiptext11">
                                                        <?php esc_html_e( 'Upgrade To Pro', 'wc-gsheetconnector' ); ?>
                                                    </span>
                                                    <label for="<?php echo esc_attr( $wcgsc_header ); ?>-one"
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



               <!-- sorting -->
               <div class="wcgsc-header-set">
                <a class="wcgsc-list-set" data-id="6" href="#0">
                    <p class="maxi_mize maxi_mize6"><i class="fa fa-plus" aria-hidden="true"></i></p>
                    <p class="mini_mize mini_mize6"><i class="fa fa-minus" aria-hidden="true"></i></p>
                    <h2> <?php echo esc_html( __( "WooCommerce orders row's management", 'wc-gsheetconnector' ) ); ?> 
                    <span class="pro-ver"><?php echo esc_html( __( "PRO", 'wc-gsheetconnector' ) ); ?></span>
                </h2>
            </a>


            <div class="wcgsc-list-set6">

                <div class="wcgsc-op-wise">
                    <label style="font-weight: bold;"><?php echo esc_html( __( "Manage row's by", 'wc-gsheetconnector' ) ); ?> </label>

                    <span class="wcgsc-pointer">
                        <input type="radio" name="order_wise_product_wise" value="productwise" id="product_wise">
                        <label><?php echo esc_html( __( "Product Wise", 'wc-gsheetconnector' ) ); ?></label>

                        <input type="radio" name="order_wise_product_wise" value="orderwise" id="order_wise" checked="">

                        <label><?php echo esc_html( __( "Order Wise", 'wc-gsheetconnector' ) ); ?></label>

                    </span>
                </div>
                <div class="note_orderwise">
                    <p class="notes"><?php echo esc_html( __( "Notes - ", 'wc-gsheetconnector' ) ); ?></p>
                    <div class="message">
                        <p>
                            <i><?php echo esc_html( __( "Order-Wise - ", 'wc-gsheetconnector' ) ); ?></i>
                            <?php echo esc_html( __( "Single Entry will be saved in Google Sheet!", 'wc-gsheetconnector' ) ); ?>
                        </p>
                        <p>
                            <i><?php echo esc_html( __( "Product Wise - ", 'wc-gsheetconnector' ) ); ?></i>
                            <?php echo esc_html( __( "Each Entry will be shown product wise with same Order ID, if multiple products are there
                                in
                                order", 'wc-gsheetconnector' ) ); ?>
                            </p>
                        </div>
                    </div>
                    <br>
                    <div class="wcgsc-op-wise">
                        <label style="font-weight: bold;"> <?php echo esc_html( __( "Sorting", 'wc-gsheetconnector' ) ); ?></label>

                        <span class="wcgsc-pointer">
                            <input type="radio" name="asc_desc_sorting" value="ASC" id="asc_sorting" checked="">
                            <label><?php echo esc_html( __( "Ascending", 'wc-gsheetconnector' ) ); ?></label>

                            <input type="radio" name="asc_desc_sorting" value="DESC" id="desc_sorting">

                            <label><?php echo esc_html( __( "Descending", 'wc-gsheetconnector' ) ); ?></label>

                        </span>
                    </div>

                </div>
            </div>


            
            
            <!-- color -->
            <div class="wcgsc-google-set" >
                <a class="wcgsc-list-set" data-id="4" href="#0">
                    <p class="maxi_mize maxi_mize4"><i class="fa fa-plus" aria-hidden="true"></i></p>
                    <p class="mini_mize mini_mize4"><i class="fa fa-minus" aria-hidden="true"></i></p>
                    <h2> <?php echo esc_html( __( "Google Sheet Settings", 'wc-gsheetconnector' ) ); ?> 
                    <span class="pro-ver"><?php echo esc_html( __( "PRO", 'wc-gsheetconnector' ) ); ?></span>

                </h2>
            </a>

            <div class="wcgsc-list-set4">
                <div class="freez_order_sort">
                    <div class="">
                        <label style="font-weight: bold;"><?php echo esc_html( __( "Freeze Header", 'wc-gsheetconnector' ) ); ?></label>
                        <span class="wcgsc-pointer">
                            <input type="checkbox" name="freeze_header" value="true" class="check-toggle"
                            id="freeze_header" style="display: none;">

                            <label for="freeze_header" class="button-wcgsc-toggle"></label>
                        </span>

                        <label style="font-weight: bold;"><?php echo esc_html( __( "Background Color", 'wc-gsheetconnector' ) ); ?> </label>

                        <div class="wcgsc-cards">
                            <label><?php echo esc_html( __( "Header Row", 'wc-gsheetconnector' ) ); ?> </label>
                            <span class="wcgsc-pointer">
                                <input type="color" name="wcgsc_header_color" value="#ffffff">
                            </span>
                        </div>
                        <div class="wcgsc-cards">
                            <label><?php echo esc_html( __( "Odd Rows", 'wc-gsheetconnector' ) ); ?></label>
                            <span class="wcgsc-pointer">
                                <input type="color" name="wcgsc_odd_color" value="#ffffff">
                            </span>
                        </div>
                        <div class="wcgsc-cards">
                         <label><?php echo esc_html( __( "Even Rows", 'wc-gsheetconnector' ) ); ?></label>
                         <span class="wcgsc-pointer">
                            <input type="color" name="wcgsc_even_color" value="#ffffff">
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="wcgsc-google-syc-set1">
        <a class="wcgsc-list-set" data-id="5" href="#0">
            <p class="maxi_mize maxi_mize5"><i class="fa fa-plus" aria-hidden="true"></i></p>
            <p class="mini_mize mini_mize5"><i class="fa fa-minus" aria-hidden="true"></i></p>
            <h2> <?php echo esc_html( __( "Google Sheet Sync", 'wc-gsheetconnector' ) ); ?> 
            <span class="pro-ver"><?php echo esc_html( __( "PRO", 'wc-gsheetconnector' ) ); ?></span>
        </h2>
    </a> 

    <div class="wcgsc-list-set5"  class="popup-click"  >
        <div class=" sync-card">
            <div class="wcgsc-syn-btn">
                <span class="wcgsc-pointer">
                    <label class="design-syn-ele"><?php echo esc_html( __( "Sync Orders", 'wc-gsheetconnector' ) ); ?> </label>
                    <select name="asc_desc_order" id="asc_desc_order" class="design-syn-ele">
                        <option value="ASC">
                            <?php echo esc_html( __( "Ascending", 'wc-gsheetconnector' ) ); ?></option>
                            <option value="DESC">
                               <?php echo esc_html( __( "Descending", 'wc-gsheetconnector' ) ); ?> </option>
                           </select>

                           <label class="design-syn-ele"><?php echo esc_html( __( "From Date", 'wc-gsheetconnector' ) ); ?></label>
                           <input type="date" name="sync_all_fromdate" id="sync_all_fromdate" class="design-syn-ele">
                           <label class="design-syn-ele"><?php echo esc_html( __( "To Date", 'wc-gsheetconnector' ) ); ?></label>
                           <input type="date" name="sync_all_todate" id="sync_all_todate" class="design-syn-ele">
                           <label class="design-syn-ele"><?php echo esc_html( __( "Select Order Status", 'wc-gsheetconnector' ) ); ?> </label>
                           <select name="asc_desc_order" id="asc_desc_order" class="design-syn-ele">
                            <option value="ASC">
                                <?php echo esc_html( __( "All", 'wc-gsheetconnector' ) ); ?></option>
                            </select>
                            <button type="button" class="button button_primary sync-orders sync-btn design-syn-ele"
                            data-type="all">
                            <?php echo esc_html( __( "Sync Orders", 'wc-gsheetconnector' ) ); ?>
                            <img class="sync-loader-orders wcgsc-display"
                            src="<?php echo esc_url( WC_GSHEETCONNECTOR_URL . '/assets/img/ajax-loader.gif' ); ?>">
                        </button>
                    </span>
                    <span id="synctext"></span>
                    <span class="sync-message-orders sync-message" style="display:block"></span>
                </div>

                <div class="wcgsc-syn-btn">
                    <span class="wcgsc-pointer">
                        <label class="design-syn-ele"><?php echo esc_html( __( "Sync Products", 'wc-gsheetconnector' ) ); ?></label>
                        <select name="asc_desc_pro" id="asc_desc_pro" class="design-syn-ele">
                            <option value="ASC" selected=""><?php echo esc_html( __( "Ascending", 'wc-gsheetconnector' ) ); ?></option>
                            <option value="DESC"><?php echo esc_html( __( "Descending", 'wc-gsheetconnector' ) ); ?></option>
                        </select>
                        <label class="design-syn-ele"><?php echo esc_html( __( "From Date", 'wc-gsheetconnector' ) ); ?></label>
                        <input type="date" name="sync_all_fromdate_pro" id="sync_all_fromdate_pro"
                        class="design-syn-ele">
                        <label class="design-syn-ele"><?php echo esc_html( __( "To Date", 'wc-gsheetconnector' ) ); ?></label>
                        <input type="date" name="sync_all_todate_pro" id="sync_all_todate_pro"
                        class="design-syn-ele">

                        <button type="button" class="button button_primary sync-products sync-btn design-syn-ele"
                        data-type="wc-products">
                        <?php echo esc_html( __( "Sync Products", 'wc-gsheetconnector' ) ); ?>
                        <img class="sync-loader-products wcgsc-display"
                        src="<?php echo esc_url( WC_GSHEETCONNECTOR_URL . '/assets/img/ajax-loader.gif' ); ?>">
                    </button>

                </span>
                <span id="synctext-product"></span>
                <span class="sync-message-products sync-message" style="display:block"></span>
            </div>
            <div class="wcgsc-syn-btn">
                <span class="wcgsc-pointer">
                    <label class="design-syn-ele"><?php echo esc_html( __( "Sync Products Variation", 'wc-gsheetconnector' ) ); ?></label>
                    <select name="asc_desc_cus" id="asc_desc_cus" class="design-syn-ele">
                        <option value="ASC" selected=""><?php echo esc_html( __( "Ascending", 'wc-gsheetconnector' ) ); ?></option>
                        <option value="DESC"><?php echo esc_html( __( "Descending", 'wc-gsheetconnector' ) ); ?></option>
                    </select>
                    <label class="design-syn-ele"><?php echo esc_html( __( "From Date", 'wc-gsheetconnector' ) ); ?></label>
                    <input type="date" name="sync_all_fromdate_cus" id="sync_all_fromdate_cus"
                    class="design-syn-ele">
                    <label class="design-syn-ele"><?php echo esc_html( __( "To Date", 'wc-gsheetconnector' ) ); ?></label>
                    <input type="date" name="sync_all_todate_cus" id="sync_all_todate_cus"
                    class="design-syn-ele">

                    <button type="button" class="button button_primary sync-customers sync-btn design-syn-ele"
                    data-type="wc-customers">
                    <?php echo esc_html( __( "Sync Products Variation", 'wc-gsheetconnector' ) ); ?>
                    <img class="sync-loader-customers wcgsc-display"
                    src="<?php echo esc_url( WC_GSHEETCONNECTOR_URL . '/assets/img/ajax-loader.gif' ); ?>">
                </button>

            </span>
            <span class="sync-message-customers sync-message" style="display:block"></span>
        </div>

        <div class="wcgsc-syn-btn">
            <span class="wcgsc-pointer">
                <label class="design-syn-ele"><?php echo esc_html( __( "Sync Customers", 'wc-gsheetconnector' ) ); ?></label>
                <select name="asc_desc_cus" id="asc_desc_cus" class="design-syn-ele">
                    <option value="ASC" selected=""><?php echo esc_html( __( "Ascending", 'wc-gsheetconnector' ) ); ?></option>
                    <option value="DESC"><?php echo esc_html( __( "Descending", 'wc-gsheetconnector' ) ); ?></option>
                </select>
                <label class="design-syn-ele"><?php echo esc_html( __( "From Date", 'wc-gsheetconnector' ) ); ?></label>
                <input type="date" name="sync_all_fromdate_cus" id="sync_all_fromdate_cus"
                class="design-syn-ele">
                <label class="design-syn-ele"><?php echo esc_html( __( "To Date", 'wc-gsheetconnector' ) ); ?></label>
                <input type="date" name="sync_all_todate_cus" id="sync_all_todate_cus"
                class="design-syn-ele">

                <button type="button" class="button button_primary sync-customers sync-btn design-syn-ele"
                data-type="wc-customers">
                <?php echo esc_html( __( 'Sync Customers', 'wc-gsheetconnector' ) ); ?>
                <img class="sync-loader-customers wcgsc-display"
                src="<?php echo esc_url( WC_GSHEETCONNECTOR_URL . '/assets/img/ajax-loader.gif' ); ?>">
            </button>

        </span>
        <span class="sync-message-customers sync-message" style="display:block"></span>
    </div>
    <div class="wcgsc-syn-btn">
        <span class="wcgsc-pointer">
            <label class="design-syn-ele"><?php echo esc_html( __( "Sync Coupons", 'wc-gsheetconnector' ) ); ?></label>
            <select name="asc_desc_coupons" id="asc_desc_coupons" class="design-syn-ele">
                <option value="ASC" selected=""><?php echo esc_html( __( "Ascending", 'wc-gsheetconnector' ) ); ?></option>
                <option value="DESC"><?php echo esc_html( __( "Descending", 'wc-gsheetconnector' ) ); ?></option>
            </select>
            <label class="design-syn-ele"><?php echo esc_html( __( "From Date", 'wc-gsheetconnector' ) ); ?></label>
            <input type="date" name="sync_all_fromdate_coupons" id="sync_all_fromdate_coupons"
            class="design-syn-ele">
            <label class="design-syn-ele"><?php echo esc_html( __( "To Date", 'wc-gsheetconnector' ) ); ?></label>
            <input type="date" name="sync_all_todate_coupons" id="sync_all_todate_coupons"
            class="design-syn-ele">

            <button type="button" class="button button_primary sync-coupons sync-btn design-syn-ele"
            data-type="wc-coupons">
            <?php echo esc_html( __( 'Sync Coupons', 'wc-gsheetconnector' ) ); ?>
            <img class="sync-loader-coupons wcgsc-display"
            src="<?php echo esc_url( WC_GSHEETCONNECTOR_URL . '/assets/img/ajax-loader.gif' ); ?>">
        </button>

    </span>
    <span class="sync-message-coupons sync-message" style="display:block"></span>
</div>

<?php if (is_plugin_active('woocommerce-subscriptions/woocommerce-subscriptions.php')) { ?>
    <div class="wcgsc-syn-btn">
        <span class="wcgsc-pointer">
            <label class="design-syn-ele"><?php echo esc_html( __( "Sync Subscriptions", 'wc-gsheetconnector' ) ); ?></label>
            <select name="asc_desc_subscription" id="asc_desc_subscription" class="design-syn-ele">
                <option value="ASC" selected=""><?php echo esc_html( __( "Ascending", 'wc-gsheetconnector' ) ); ?></option>
                <option value="DESC"><?php echo esc_html( __( "Descending", 'wc-gsheetconnector' ) ); ?></option>
            </select>
            <label class="design-syn-ele"><?php echo esc_html( __( "From Date", 'wc-gsheetconnector' ) ); ?></label>
            <input type="date" name="sync_all_fromdate_subscription" id="sync_all_fromdate_subscription"
            class="design-syn-ele">
            <label class="design-syn-ele"><?php echo esc_html( __( "To Date", 'wc-gsheetconnector' ) ); ?></label>
            <input type="date" name="sync_all_todate_subscription" id="sync_all_todate_subscription"
            class="design-syn-ele">

            <button type="button" class="button button_primary sync-subscription sync-btn design-syn-ele"
            data-type="wc-subscription">
            <?php echo esc_html( __( 'Sync Subscriptions', 'wc-gsheetconnector' ) ); ?>
            <img class="sync-loader-subscriptions wcgsc-display"
            src="<?php echo esc_url( WC_GSHEETCONNECTOR_URL . '/assets/img/ajax-loader.gif' ); ?>">
        </button>

    </span>
    <span class="sync-message-subscriptions sync-message" style="display:block"></span>
</div>
<?php } ?>

</div>
<div class="download-card">
    <!-- dropdown select tab name -->
    <div class="wcgsc-download-drop wcgsc-display">
        <select name="wcgsc-download-tab" id="wcgsc-download-tab">

            <option value="all_entire_sheet_tabs" style="font-weight: bold;" selected=""> </option>
        </select>
    </div>
    <!-- dropdown select tab name -->
    <div class="wcgsc-download-btn" style="padding: 5px;">
        <span class="wcgsc-pointer">
            <button type="button" class="button button_primary download-orders download-btn"
            data-type="all" data-url="https://docs.google.com/spreadsheets/d/"
            data-sheet_id=""><?php echo esc_html( __( "Download Spreadsheet", 'wc-gsheetconnector' ) ); ?>
            <img class="download-loader wcgsc-display"
            src="<?php echo esc_url( WC_GSHEETCONNECTOR_URL . '/assets/img/ajax-loader.gif' ); ?>">
        </button>
        <span>
            <?php echo esc_html( __( '(You can download connected Google Spreadsheet )', 'wc-gsheetconnector' ) ); ?>
        </span>

    </span>
</div>
<div class="wcgsc-download-msg">
    <span class="download-message"></span>
</div>
</div>

</div>
</div>
</div>
</h2>

</div>
<input type="hidden" name="wcgsc-nonce" id="wcgsc-nonce"
value="<?php echo esc_attr( wp_create_nonce( 'wcgsc-nonce' ) ); ?>" />

<input type="submit" value="<?php echo esc_attr__( 'Submit Data', 'wc-gsheetconnector' ); ?>"
id="wcgsc-save-btn" class="wcgsc-save-btn" name="wcgsc-save-btn">
</form>
<?php
}
$wcgsc_file = WC_GSHEETCONNECTOR_PATH . 'includes/pages/pro-popup.php';
if ( file_exists( $wcgsc_file ) ) {
    include $wcgsc_file;
};?>
<!-- popup file include here -->