<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}
?>
<div id="opener">
  <div class="wcgsc-fields">
    <a class="wcgsc-list-set" data-id="1" href="javascript:void(0);">
      <p class="maxi_mize maxi_mize1"><i class="fa fa-plus" aria-hidden="true"></i></p>
      <p class="mini_mize mini_mize1"><i class="fa fa-minus" aria-hidden="true"></i></p>
      <h2> <?php echo esc_html__( 'Google Sheet to WooCommerce Sync Configuration', 'wc-gsheetconnector' ); ?> 
      <span class="pro-ver"><?php echo esc_html__( 'PRO', 'wc-gsheetconnector' ); ?></span>
      <span class="loading-sign-deactive">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
    </h2>
  </a>

  <div class="wcgsc-list-set1">
    <div class="sheet-details">
      <h3 class="systemifo">
        <?php echo esc_html__( 'Connected WooCommerce Google Sheet', 'wc-gsheetconnector' ); ?>
      </h3>

      <div class="row">
        <label class="productsheetlabel"><?php echo esc_html__( 'Google Spreadsheet Name', 'wc-gsheetconnector' ); ?></label>
        <input type="text" value="Plugin Test" disabled class="productsheetname">
      </div>  

      <div class="sheet-url-ps row" id="sheet-url">
        <label class="productsheetlabel"><?php echo esc_html__( 'Google Spreadsheet URL', 'wc-gsheetconnector' ); ?></label>
        <a href="https://docs.google.com/spreadsheets/d/1tHVvTNzNRnn03uZsCQmvrPh55rDx58c0YSXnos-nvy8" target="_blank" rel="noopener noreferrer">
          <input type="button" disabled id="viewsheet" value="<?php echo esc_attr__( 'View Spreadsheet', 'wc-gsheetconnector' ); ?>">
        </a>
      </div>
    </div>
  </div>
</div>

<!-- ================= PRODUCT SYNC ================= -->

<div class="system-debug-logs">
  <a class="wcgsc-list-set" data-id="2" href="javascript:void(0);">
    <p class="maxi_mize maxi_mize2"><i class="fa fa-plus"></i></p>
    <p class="mini_mize maxi_mize2"><i class="fa fa-minus"></i></p>
    <h2>
      <i class="fa fa-refresh"></i>
      <?php echo esc_html__( 'Sync Settings for Two-Way Sync - Insert & Updates', 'wc-gsheetconnector' ); ?>
      <span class="pro-ver"><?php echo esc_html__( 'PRO', 'wc-gsheetconnector' ); ?></span>
    </h2>
  </a>

  <div class="wcgsc-list-set2 wcgsc-list-sync">
    <div class="sheet-details">

      <!-- INSERT -->
      <form method="post" id="product-sheet-to-wc-form-insert">
        <?php wp_nonce_field( 'wcgsc_sync_nonce', 'wcgsc_sync_nonce_field' ); ?>
        <label for="rangeStart"><?php echo esc_html__( 'Enter Rows Range - from', 'wc-gsheetconnector' ); ?></label>
        <input type="number" id="rangeStart" name="range_start" min="2" disabled>

        <label for="rangeEnd"><?php echo esc_html__( 'to', 'wc-gsheetconnector' ); ?></label>
        <input type="number" id="rangeEnd" name="range_end" min="2" disabled>

        <input type="submit" class="button button-primary product-sheet-to-wc-insert"
        value="<?php echo esc_attr__( 'Click to Sync Insert', 'wc-gsheetconnector' ); ?>">
      </form>

      <!-- UPDATE -->
      <form method="post">
        <?php wp_nonce_field( 'wcgsc_sync_nonce', 'wcgsc_sync_nonce_field' ); ?>
        <label for="rangeStartUpdate"><?php echo esc_html__( 'Enter Rows Range - from', 'wc-gsheetconnector' ); ?></label>
        <input type="number" id="rangeStartUpdate" name="range_start_update" min="2" disabled>

        <label for="rangeEndUpdate"><?php echo esc_html__( 'to', 'wc-gsheetconnector' ); ?></label>
        <input type="number" id="rangeEndUpdate" name="range_end_update" min="2" disabled>

        <input type="submit" class="button button-primary product-sheet-to-wc-update"
        value="<?php echo esc_attr__( 'Click to Sync Update', 'wc-gsheetconnector' ); ?>">
      </form>

    </div>
  </div>
</div>

<!-- ================= ORDER STATUS ================= -->

<div class="system-debug-logs">
  <a class="wcgsc-list-set" data-id="3" href="javascript:void(0);">
    <p class="maxi_mize maxi_mize3"><i class="fa fa-plus"></i></p>
    <p class="mini_mize maxi_mize3"><i class="fa fa-minus"></i></p>

    <h2>
      <i class="fa fa-refresh"></i>
      <?php echo esc_html__( 'Sync Settings for Two-Way Sync - Order Status Updates', 'wc-gsheetconnector' ); ?>
    </h2>
  </a>

  <div class="wcgsc-list-set3 wcgsc-list-sync">
    <div class="sheet-details">

      <form method="post" id="order-status-sheet-to-wc-form-update">
        <?php wp_nonce_field( 'wcgsc_sync_nonce', 'wcgsc_sync_nonce_field' ); ?>

        <label for="OrderstatusrangeStart"><?php echo esc_html__( 'Enter Rows Range - from', 'wc-gsheetconnector' ); ?></label>
        <input type="number" id="OrderstatusrangeStart" name="order_status_range_start" min="2" disabled>

        <label for="OrderstatusrangeEnd"><?php echo esc_html__( 'to', 'wc-gsheetconnector' ); ?></label>
        <input type="number" id="OrderstatusrangeEnd" name="order_status_range_end" min="2" disabled>

        <input type="submit" class="button button-primary order-status-sheet-to-wc-update"
        value="<?php echo esc_attr__( 'Click to Order Status Sync Update', 'wc-gsheetconnector' ); ?>">
      </form>

    </div>
  </div>
</div>

<!-- ================= ORDER INSERT ================= -->

<div class="system-debug-logs">
  <a class="wcgsc-list-set" data-id="4" href="javascript:void(0);">
    <p class="maxi_mize maxi_mize4"><i class="fa fa-plus"></i></p>
    <p class="mini_mize maxi_mize4"><i class="fa fa-minus"></i></p>

    <h2>
      <i class="fa fa-refresh"></i>
      <?php echo esc_html__( 'Sync Settings for Two-Way Sync - Insert Orders', 'wc-gsheetconnector' ); ?>
    </h2>
  </a>

  <div class="wcgsc-list-set4 wcgsc-list-sync">
    <div class="sheet-details">

      <form method="post" id="order-sheet-to-wc-form-insert">
        <?php wp_nonce_field( 'wcgsc_sync_nonce', 'wcgsc_sync_nonce_field' ); ?>

        <label for="OrderrangeStart"><?php echo esc_html__( 'Enter Rows Range - from', 'wc-gsheetconnector' ); ?></label>
        <input type="number" id="OrderrangeStart" name="order_range_start" min="2" disabled>

        <label for="OrderrangeEnd"><?php echo esc_html__( 'to', 'wc-gsheetconnector' ); ?></label>
        <input type="number" id="OrderrangeEnd" name="order_range_end" min="2" disabled>

        <input type="submit" class="button button-primary order-sheet-to-wc-insert"
        value="<?php echo esc_attr__( 'Click to Sync Order', 'wc-gsheetconnector' ); ?>">
      </form>

    </div>
  </div>
</div>