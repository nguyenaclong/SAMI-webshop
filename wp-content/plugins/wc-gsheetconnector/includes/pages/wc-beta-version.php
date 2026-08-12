<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}
?>

<div class="wrap gs-form" id="opener">
  <div class="card" id="googlesheet">
    <div class="wrap gs-form">
      <div class="wcgsc-card">

        <h2>
          <?php esc_html_e( 'Beta Opt-in', 'wc-gsheetconnector' ); ?>
          <span class="pro-ver"><?php esc_html_e( 'PRO', 'wc-gsheetconnector' ); ?></span>
        </h2>

        <div class="wcgsc-disabled-section">
          <p>
            <?php esc_html_e( 'Turn on the Beta Version feature to get notified about new beta releases. The beta version will not install automatically and you always have the option to ignore it.', 'wc-gsheetconnector' ); ?>
          </p>

          <label class="switch">
            <input type="checkbox" name="beta-version-setting" value="" class="beta-version-setting" />
            <span class="slider round"></span>
          </label>

          <label>
            <strong style="font-size: 16px;">
              <?php esc_html_e( 'Enable Beta Version', 'wc-gsheetconnector' ); ?>
            </strong>
          </label>

          <p>
            <?php esc_html_e( 'Get updates for pre-release versions', 'wc-gsheetconnector' ); ?>
          </p>

          <input type="button" class="beta-btn" value="<?php echo esc_attr__( 'Save', 'wc-gsheetconnector' ); ?>" />
        </div>

      </div>
    </div>
  </div>
</div>

<?php
// popup file include
include_once WC_GSHEETCONNECTOR_PATH . 'includes/pages/pro-popup.php';
?>