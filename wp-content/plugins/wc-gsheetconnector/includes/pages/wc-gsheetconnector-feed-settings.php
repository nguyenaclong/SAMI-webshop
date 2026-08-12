<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}
?>
<div class="wcgsc-card">

  <h2>
    <?php esc_html_e( 'Feed Settings', 'wc-gsheetconnector' ); ?>
    <span class="pro-ver"><?php esc_html_e( 'PRO', 'wc-gsheetconnector' ); ?></span>
  </h2>

  <button class="woogsc-feed-btn" id="woogsc-add-feed">
    <?php echo esc_html__( 'Add Feeds', 'wc-gsheetconnector' ); ?>
  </button>

  <div class="woogsc-add-feed">
    <form method="post" id="feedForm">

      <label for="feed_name">
        <?php echo esc_html__( 'Feed Name', 'wc-gsheetconnector' ); ?>
      </label>

      <input type="text" id="feed_name" class="feedName" name="feed_name" required />

      <select name="location" class="location" id="location">
        <option value=""><?php echo esc_html__( 'Select Location', 'wc-gsheetconnector' ); ?></option>
        <option value="Orders"><?php echo esc_html__( 'Orders', 'wc-gsheetconnector' ); ?></option>
        <option value="Products"><?php echo esc_html__( 'Products', 'wc-gsheetconnector' ); ?></option>
        <option value="Products Variation"><?php echo esc_html__( 'Products Variation', 'wc-gsheetconnector' ); ?></option>
        <option value="Customers"><?php echo esc_html__( 'Customers', 'wc-gsheetconnector' ); ?></option>
        <option value="Coupons"><?php echo esc_html__( 'Coupons', 'wc-gsheetconnector' ); ?></option>
        <option value="Subscriptions"><?php echo esc_html__( 'Subscriptions', 'wc-gsheetconnector' ); ?></option>
        <option value="All"><?php echo esc_html__( 'All', 'wc-gsheetconnector' ); ?></option>
      </select>

      <?php
      // Generate nonce securely
      $wcgsc_nonce = wp_create_nonce( 'woogsc-feed-ajax-nonce' );
      ?>

      <input type="hidden"
      name="woogsc-feed-ajax-nonce"
      id="woogsc-feed-ajax-nonce"
      value="<?php echo esc_attr( $wcgsc_nonce ); ?>" />

      <input type="submit"
      name="execute-submit-feed-woogsc"
      class="woogsc-feed-sub-btn wcgsc-disabled-btn"
      value="<?php echo esc_attr__( 'Submit', 'wc-gsheetconnector' ); ?>" />

      <span class="woogsc-feed-fetch-load">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>

    </form>
  </div>
</div>