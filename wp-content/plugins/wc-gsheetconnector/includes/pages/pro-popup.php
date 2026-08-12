<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>
<div id="popup" class="popup">
    <div class="popup-outer" id="popup-outer"></div>

    <div class="popup-container">
        <button id="closeButton" class="popup-close" type="button">
            <i class="fa fa-times" aria-hidden="true"></i>
        </button>

        <div class="popup-content align-center">
            <i class="fa fa-lock popup-icon" aria-hidden="true"></i>

            <h2><?php esc_html_e( 'GSheetConnector for WooCommerce PRO Features', 'wc-gsheetconnector' ); ?></h2>

            <p><?php esc_html_e( 'This feature is available in the PRO version. Upgrade to unlock all features.', 'wc-gsheetconnector' ); ?></p>

            <a href="<?php echo esc_url( 'https://www.gsheetconnector.com/woocommerce-google-sheet-connector-pro' ); ?>" 
               target="_blank" 
               rel="noopener noreferrer" 
               class="popup-btn-normal">
               <?php esc_html_e( 'Upgrade To PRO', 'wc-gsheetconnector' ); ?>
           </a>
       </div>

       <p class="note">
        <?php esc_html_e( 'Bonus: Lite users get special discounts automatically at checkout.', 'wc-gsheetconnector' ); ?>
    </p>
</div>
</div>