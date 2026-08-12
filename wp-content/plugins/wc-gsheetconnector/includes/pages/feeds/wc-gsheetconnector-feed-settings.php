<?php
if (! defined('ABSPATH')) {
  exit;
}
?>
<div class="wcgsc-card">

  <div class="heading mt-0">
    <div class="heading mt-0"><?php echo esc_html__('Sheet Sync Feeds', 'wc-gsheetconnector'); ?>
      <span class="pro-ver"><?php echo esc_html(__("PRO", 'wc-gsheetconnector')); ?></span>
    </div>
    <p><?php echo esc_html__('Create, connect, manage, and monitor how your form submissions are automatically synced to Google Sheets in real time.', 'wc-gsheetconnector'); ?></p>
  </div>

  <button class="woogsc-feed-btn btn btn-primary mt-20" id="woogsc-add-feed">
    <?php echo esc_html__('- Close Feeds', 'wc-gsheetconnector'); ?>
  </button>

  <div class="woogsc-add-feed shadow-box mt-50 p-30 add-feed-form">
    <form method="post" id="feedForm">
      <div class="row">
        <div class="col-4">
          <div class="form-group mr-10">
            <label for="feed_name">
              <?php echo esc_html__('Feed Name', 'wc-gsheetconnector'); ?>
            </label>
            <input type="text" id="feed_name" class="feedName" name="feed_name" disabled />
          </div>
        </div>
        <div class="col-4">
          <div class="form-group mr-10">
            <label for="feed_name">
              <?php echo esc_html__('Feed Type', 'wc-gsheetconnector'); ?>
            </label>
            <select name="location" class="location" id="location">
              <option value="" disabled><?php echo esc_html__('Select Feed Type', 'wc-gsheetconnector'); ?></option>
              <option value="Orders" disabled><?php echo esc_html__('Orders', 'wc-gsheetconnector'); ?></option>
              <option value="Products" disabled><?php echo esc_html__('Products', 'wc-gsheetconnector'); ?></option>
              <option value="Products Variation" disabled><?php echo esc_html__('Products Variation', 'wc-gsheetconnector'); ?></option>
              <option value="Customers" disabled><?php echo esc_html__('Customers', 'wc-gsheetconnector'); ?></option>
              <option value="Coupons" disabled><?php echo esc_html__('Coupons', 'wc-gsheetconnector'); ?></option>
              <option value="Subscriptions" disabled><?php echo esc_html__('Subscriptions', 'wc-gsheetconnector'); ?></option>
              <option value="All" disabled><?php echo esc_html__('All', 'wc-gsheetconnector'); ?></option>
            </select>
          </div>
        </div>
        <div class="col-4 mt-25">

          <input type="submit"
            name="execute-submit-feed-woogsc"
            class="woogsc-feed-sub-btn wcgsc-disabled-btn btn deactivate-btn"
            value="<?php echo esc_attr__('Submit', 'wc-gsheetconnector'); ?>" disabled />

          <span class="woogsc-feed-fetch-load">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
        </div>
      </div>

    </form>
  </div>

  <!---Table Pro-->
  <!-- FILTERS AND SEARCH -->
  <ul class="subsubsub mt-30">
    <li class="all">
      <a href="#" class="text-decoration-none">
        <?php echo esc_html__('All', 'wc-gsheetconnector'); ?>
      </a> |
    </li>
    <li class="inactive">
      <a href="#" class="text-decoration-none">
        <?php echo esc_html__('Inactive', 'wc-gsheetconnector'); ?>
      </a> |
    </li>
    <li class="trash">
      <a href="#" class="text-decoration-none">
        <?php echo esc_html__('Trash', 'wc-gsheetconnector'); ?>
      </a>
    </li>
  </ul>

  <form>
    <p class="search-box mt-30">
      <input type="hidden" name="page" value="wc-gsheetconnector-config">
      <input type="hidden" name="tab" value="feed_settings">
      <input type="hidden" name="view" value="search">

      <?php
      ?>
      <input type="search"
        name="search-feed"
        placeholder="Search Feeds" disabled>
      <?php
      ?>
      <input type="submit" class="action-btn" value="Search" disabled>
    </p>
  </form>
  <div class="form-feed-table-common">
    <table class="wp-list-table striped full-tables">
      <thead>
        <tr>
          <th scope="col" class="manage-column  pt-15 pb-15 pl-20 pr-20 text-left bg-white text-dark fw-600"><?php echo esc_html__('Feed ID', 'wc-gsheetconnector'); ?></th>
          <th scope="col" class="manage-column pt-15 pb-15 pl-20 pr-20 text-left bg-white text-dark fw-600"><?php echo esc_html__('Feed Name', 'wc-gsheetconnector'); ?></th>
          <th scope="col" class="manage-column pt-15 pb-15 pl-20 pr-20 text-left bg-white text-dark fw-600"><?php echo esc_html__('Author', 'wc-gsheetconnector'); ?></th>
          <th scope="col" class="manage-column pt-15 pb-15 pl-20 pr-20 text-left bg-white text-dark fw-600"><?php echo esc_html__('Feed Type', 'wc-gsheetconnector'); ?></th>
          <th scope="col" class="manage-column pt-15 pb-15 pl-20 pr-20 text-left bg-white text-dark fw-600"><?php echo esc_html__('Timestamp', 'wc-gsheetconnector'); ?></th>

          <th scope="col" class="manage-column pt-15 pb-15 pl-20 pr-20 text-left bg-white text-dark fw-600">
            <?php echo esc_html__('Status', 'wc-gsheetconnector'); ?> </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td data-label="ID" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('15', 'wc-gsheetconnector'); ?> </th>
          </td>
          <td data-label="Feed Name" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <div class="feed-rename-col">
              <a class="feed-link text-decoration-none" href="?page=wc-gsheetconnector-config&amp;tab=feed_settings&amp;feed_id=15">
                <?php echo esc_html__('feed', 'wc-gsheetconnector'); ?> </a>
              <div class="feed-rename-box d-flex align-center" id="open-feed" style="display: none">
                <p>
                  <?php echo esc_html__('Rename Feed', 'wc-gsheetconnector'); ?> </p>
                <input type="text" id="edit-feed-name" style="display: none;">
                <button id="save-feed-name-btn" class="button" style="display: none;"> <?php echo esc_html__('Save', 'wc-gsheetconnector'); ?></button>
                <span id="loading-feed-15" class="loading-feed" style="display: none;"></span>
              </div>
            </div>
            <div class="row-actions">
              <span class="edit">
                <a href="#" class="text-decoration-none">
                  <?php echo esc_html__('Edit', 'wc-gsheetconnector'); ?> </a> |
              </span>

              <span class="edit">
                <div id="feed-container">

                  <input type="hidden" name="feed_id" id="data-feed-id" value="15">
                  <input type="hidden" name="feed_name" id="data-feed-name" value="feed">

                  <button id="rename-feed-btn">
                    <?php echo esc_html__('Rename Feed', 'wc-gsheetconnector'); ?> </button>
                  <input type="hidden" name="woogsc-update-feed-name-ajax-nonce" id="woogsc-update-feed-name-ajax-nonce" value="a23482be9b">

                </div>
              </span>

              <span class="trash">
                | <a href="#" class="trash-feed text-decoration-none" data-feed-id="15">
                  <?php echo esc_html__('Trash', 'wc-gsheetconnector'); ?> </a>
                <span id="loading-feed-15" class="loading-feed" style="display: none;"></span>
                <input type="hidden" name="woogsc-trash-feed-ajax-nonce" id="woogsc-trash-feed-ajax-nonce" value="1f604fc974">


              </span>
            </div>
          </td>
          <td data-label="Author" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('admin', 'wc-gsheetconnector'); ?> </td>
          <td data-label="Location" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('Orders', 'wc-gsheetconnector'); ?> </td>
          <td data-label="Created" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('2026-04-27 09:30:53', 'wc-gsheetconnector'); ?> </td>

          <td data-label="Status" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <input type="hidden" name="woogsc-status-feed-ajax-nonce" id="woogsc-status-feed-ajax-nonce" value="8caa405837">

            <span id="loading-status-feed-15" class="loading-feed" style="display: none;"></span>
            <label class="switch">
              <input type="checkbox" class="toggle-status check-toggle" data-feed-id="15" checked="checked" disabled>
              <span class="slider round button-toggle"></span>
            </label>
          </td>
        </tr>
        <tr>
          <td data-label="ID" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('16', 'wc-gsheetconnector'); ?>
          </td>
          <td data-label="Feed Name" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <div class="feed-rename-col">
              <a class="feed-link text-decoration-none" href="?page=wc-gsheetconnector-config&amp;tab=feed_settings&amp;feed_id=16">
                <?php echo esc_html__('feed1', 'wc-gsheetconnector'); ?> </a>
              <div class="feed-rename-box d-flex align-center" id="open-feed" style="display: none">
                <p>
                  <?php echo esc_html__('Rename Feed', 'wc-gsheetconnector'); ?> </p>
                <input type="text" id="edit-feed-name" style="display: none;">
                <button id="save-feed-name-btn" class="button" style="display: none;"><?php echo esc_html__('Save', 'wc-gsheetconnector'); ?></button>
                <span id="loading-feed-16" class="loading-feed" style="display: none;"></span>
              </div>
            </div>



            <div class="row-actions">
              <span class="edit">
                <a href="#" class="text-decoration-none">
                  <?php echo esc_html__('Edit', 'wc-gsheetconnector'); ?> </a> |
              </span>

              <span class="edit">
                <div id="feed-container">

                  <input type="hidden" name="feed_id" id="data-feed-id" value="16">
                  <input type="hidden" name="feed_name" id="data-feed-name" value="feed1">

                  <button id="rename-feed-btn">
                    <?php echo esc_html__('Rename Feed', 'wc-gsheetconnector'); ?> </button>
                  <input type="hidden" name="woogsc-update-feed-name-ajax-nonce" id="woogsc-update-feed-name-ajax-nonce" value="a23482be9b">

                </div>
              </span>

              <span class="trash">
                | <a href="#" class="trash-feed text-decoration-none" data-feed-id="16">
                  <?php echo esc_html__('Trash', 'wc-gsheetconnector'); ?> </a>
                <span id="loading-feed-16" class="loading-feed" style="display: none;"></span>
                <input type="hidden" name="woogsc-trash-feed-ajax-nonce" id="woogsc-trash-feed-ajax-nonce" value="1f604fc974">


              </span>
            </div>
          </td>
          <td data-label="Author" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('admin', 'wc-gsheetconnector'); ?> </a> </td>
          <td data-label="Location" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('Products', 'wc-gsheetconnector'); ?> </td>
          <td data-label="Created" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('2026-04-27 09:31:18', 'wc-gsheetconnector'); ?> </td>

          <td data-label="Status" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <input type="hidden" name="woogsc-status-feed-ajax-nonce" id="woogsc-status-feed-ajax-nonce" value="8caa405837">

            <span id="loading-status-feed-16" class="loading-feed" style="display: none;"></span>
            <label class="switch">
              <input type="checkbox" class="toggle-status check-toggle" data-feed-id="16" checked="checked" disabled>
              <span class="slider round button-toggle"></span>
            </label>
          </td>
        </tr>
        <tr>
          <td data-label="ID" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('17', 'wc-gsheetconnector'); ?>
          </td>
          <td data-label="Feed Name" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <div class="feed-rename-col">
              <a class="feed-link text-decoration-none" href="?page=wc-gsheetconnector-config&amp;tab=feed_settings&amp;feed_id=17">
                <?php echo esc_html__('feed2', 'wc-gsheetconnector'); ?> </a>
              <div class="feed-rename-box d-flex align-center" id="open-feed" style="display: none">
                <p>
                  <?php echo esc_html__('Rename Feed', 'wc-gsheetconnector'); ?> </p>
                <input type="text" id="edit-feed-name" style="display: none;">
                <button id="save-feed-name-btn" class="button" style="display: none;"><?php echo esc_html__('Save', 'wc-gsheetconnector'); ?></button>
                <span id="loading-feed-17" class="loading-feed" style="display: none;"></span>
              </div>
            </div>



            <div class="row-actions">
              <span class="edit">
                <a href="#" class="text-decoration-none">
                  <?php echo esc_html__('Edit', 'wc-gsheetconnector'); ?> </a> |
              </span>

              <span class="edit">
                <div id="feed-container">

                  <input type="hidden" name="feed_id" id="data-feed-id" value="17">
                  <input type="hidden" name="feed_name" id="data-feed-name" value="feed2">

                  <button id="rename-feed-btn">
                    <?php echo esc_html__('Rename Feed', 'wc-gsheetconnector'); ?> </button>
                  <input type="hidden" name="woogsc-update-feed-name-ajax-nonce" id="woogsc-update-feed-name-ajax-nonce" value="a23482be9b">

                </div>
              </span>

              <span class="trash">
                | <a href="#" class="trash-feed text-decoration-none" data-feed-id="17">
                  <?php echo esc_html__('Trash', 'wc-gsheetconnector'); ?> </a>
                <span id="loading-feed-17" class="loading-feed" style="display: none;"></span>
                <input type="hidden" name="woogsc-trash-feed-ajax-nonce" id="woogsc-trash-feed-ajax-nonce" value="1f604fc974">


              </span>
            </div>
          </td>
          <td data-label="Author" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('admin', 'wc-gsheetconnector'); ?> </td>
          <td data-label="Location" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('Products Variation', 'wc-gsheetconnector'); ?> </td>
          <td data-label="Created" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('2026-04-27 09:31:35', 'wc-gsheetconnector'); ?> </td>

          <td data-label="Status" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <input type="hidden" name="woogsc-status-feed-ajax-nonce" id="woogsc-status-feed-ajax-nonce" value="8caa405837">

            <span id="loading-status-feed-17" class="loading-feed" style="display: none;"></span>
            <label class="switch">
              <input type="checkbox" class="toggle-status check-toggle" data-feed-id="17" checked="checked" disabled>
              <span class="slider round button-toggle"></span>
            </label>
          </td>
        </tr>
        <tr>
          <td data-label="ID" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('18', 'wc-gsheetconnector'); ?> </td>
          </td>
          <td data-label="Feed Name" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <div class="feed-rename-col">
              <a class="feed-link text-decoration-none" href="?page=wc-gsheetconnector-config&amp;tab=feed_settings&amp;feed_id=18">
                <?php echo esc_html__('feed3', 'wc-gsheetconnector'); ?> </a>
              <div class="feed-rename-box d-flex align-center" id="open-feed" style="display: none">
                <p>
                  <?php echo esc_html__('Rename Feed', 'wc-gsheetconnector'); ?> </p>
                <input type="text" id="edit-feed-name" style="display: none;">
                <button id="save-feed-name-btn" class="button" style="display: none;"><?php echo esc_html__('Save', 'wc-gsheetconnector'); ?></button>
                <span id="loading-feed-18" class="loading-feed" style="display: none;"></span>
              </div>
            </div>



            <div class="row-actions">
              <span class="edit">
                <a href="#" class="text-decoration-none">
                  <?php echo esc_html__('Edit', 'wc-gsheetconnector'); ?> </a> |
              </span>

              <span class="edit">
                <div id="feed-container">

                  <input type="hidden" name="feed_id" id="data-feed-id" value="18">
                  <input type="hidden" name="feed_name" id="data-feed-name" value="feed3">

                  <button id="rename-feed-btn">
                    <?php echo esc_html__('Rename Feed', 'wc-gsheetconnector'); ?> </button>
                  <input type="hidden" name="woogsc-update-feed-name-ajax-nonce" id="woogsc-update-feed-name-ajax-nonce" value="a23482be9b">

                </div>
              </span>

              <span class="trash">
                | <a href="#" class="trash-feed text-decoration-none" data-feed-id="18">
                  <?php echo esc_html__('Trash', 'wc-gsheetconnector'); ?> </a>
                <span id="loading-feed-18" class="loading-feed" style="display: none;"></span>
                <input type="hidden" name="woogsc-trash-feed-ajax-nonce" id="woogsc-trash-feed-ajax-nonce" value="1f604fc974">


              </span>
            </div>
          </td>
          <td data-label="Author" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('admin', 'wc-gsheetconnector'); ?> </td>
          <td data-label="Location" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('Customers', 'wc-gsheetconnector'); ?> </td>
          <td data-label="Created" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('2026-04-27 09:31:47', 'wc-gsheetconnector'); ?> </td>

          <td data-label="Status" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <input type="hidden" name="woogsc-status-feed-ajax-nonce" id="woogsc-status-feed-ajax-nonce" value="8caa405837">

            <span id="loading-status-feed-18" class="loading-feed" style="display: none;"></span>
            <label class="switch">
              <input type="checkbox" class="toggle-status check-toggle" data-feed-id="18" checked="checked" disabled>
              <span class="slider round button-toggle"></span>
            </label>
          </td>
        </tr>
        <tr>
          <td data-label="ID" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('19', 'wc-gsheetconnector'); ?> </td>
          </td>
          <td data-label="Feed Name" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <div class="feed-rename-col">
              <a class="feed-link text-decoration-none" href="?page=wc-gsheetconnector-config&amp;tab=feed_settings&amp;feed_id=19">
                <?php echo esc_html__('feed4', 'wc-gsheetconnector'); ?> </a>
              <div class="feed-rename-box d-flex align-center" id="open-feed" style="display: none">
                <p>
                  <?php echo esc_html__('Rename Feed', 'wc-gsheetconnector'); ?> </p>
                <input type="text" id="edit-feed-name" style="display: none;">
                <button id="save-feed-name-btn" class="button" style="display: none;"><?php echo esc_html__('Save', 'wc-gsheetconnector'); ?></button>
                <span id="loading-feed-19" class="loading-feed" style="display: none;"></span>
              </div>
            </div>



            <div class="row-actions">
              <span class="edit">
                <a href="#" class="text-decoration-none">
                  <?php echo esc_html__('Edit', 'wc-gsheetconnector'); ?> </a> |
              </span>

              <span class="edit">
                <div id="feed-container">

                  <input type="hidden" name="feed_id" id="data-feed-id" value="19">
                  <input type="hidden" name="feed_name" id="data-feed-name" value="feed4">

                  <button id="rename-feed-btn">
                    <?php echo esc_html__('Rename Feed', 'wc-gsheetconnector'); ?> </button>
                  <input type="hidden" name="woogsc-update-feed-name-ajax-nonce" id="woogsc-update-feed-name-ajax-nonce" value="a23482be9b">

                </div>
              </span>

              <span class="trash">
                | <a href="#" class="trash-feed text-decoration-none" data-feed-id="19">
                  <?php echo esc_html__('Trash', 'wc-gsheetconnector'); ?> </a>
                <span id="loading-feed-19" class="loading-feed" style="display: none;"></span>
                <input type="hidden" name="woogsc-trash-feed-ajax-nonce" id="woogsc-trash-feed-ajax-nonce" value="1f604fc974">


              </span>
            </div>
          </td>
          <td data-label="Author" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('admin', 'wc-gsheetconnector'); ?> </td>
          <td data-label="Location" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('Coupons', 'wc-gsheetconnector'); ?> </td>
          <td data-label="Created" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('2026-04-27 09:32:00', 'wc-gsheetconnector'); ?> </td>

          <td data-label="Status" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <input type="hidden" name="woogsc-status-feed-ajax-nonce" id="woogsc-status-feed-ajax-nonce" value="8caa405837">

            <span id="loading-status-feed-19" class="loading-feed" style="display: none;"></span>
            <label class="switch">
              <input type="checkbox" class="toggle-status check-toggle" data-feed-id="19" checked="checked" disabled>
              <span class="slider round button-toggle"></span>
            </label>
          </td>
        </tr>
        <tr>
          <td data-label="ID" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('20', 'wc-gsheetconnector'); ?>
          </td>
          <td data-label="Feed Name" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <div class="feed-rename-col">
              <a class="feed-link text-decoration-none" href="?page=wc-gsheetconnector-config&amp;tab=feed_settings&amp;feed_id=20">
                <?php echo esc_html__('feed5', 'wc-gsheetconnector'); ?> </a>
              <div class="feed-rename-box d-flex align-center" id="open-feed" style="display: none">
                <p>
                  <?php echo esc_html__('Rename Feed', 'wc-gsheetconnector'); ?> </p>
                <input type="text" id="edit-feed-name" style="display: none;">
                <button id="save-feed-name-btn" class="button" style="display: none;"><?php echo esc_html__('Save', 'wc-gsheetconnector'); ?></button>
                <span id="loading-feed-20" class="loading-feed" style="display: none;"></span>
              </div>
            </div>



            <div class="row-actions">
              <span class="edit">
                <a href="#" class="text-decoration-none">
                  <?php echo esc_html__('Edit', 'wc-gsheetconnector'); ?> </a> |
              </span>

              <span class="edit">
                <div id="feed-container">

                  <input type="hidden" name="feed_id" id="data-feed-id" value="20">
                  <input type="hidden" name="feed_name" id="data-feed-name" value="feed5">

                  <button id="rename-feed-btn">
                    <?php echo esc_html__('Rename Feed', 'wc-gsheetconnector'); ?> </button>
                  <input type="hidden" name="woogsc-update-feed-name-ajax-nonce" id="woogsc-update-feed-name-ajax-nonce" value="a23482be9b">

                </div>
              </span>

              <span class="trash">
                | <a href="#" class="trash-feed text-decoration-none" data-feed-id="20">
                  <?php echo esc_html__('Trash', 'wc-gsheetconnector'); ?> </a>
                <span id="loading-feed-20" class="loading-feed" style="display: none;"></span>
                <input type="hidden" name="woogsc-trash-feed-ajax-nonce" id="woogsc-trash-feed-ajax-nonce" value="1f604fc974">


              </span>
            </div>
          </td>
          <td data-label="Author" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('admin', 'wc-gsheetconnector'); ?> </td>
          <td data-label="Location" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('Subscriptions', 'wc-gsheetconnector'); ?> </td>
          <td data-label="Created" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('2026-04-27 09:32:17', 'wc-gsheetconnector'); ?> </td>

          <td data-label="Status" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <input type="hidden" name="woogsc-status-feed-ajax-nonce" id="woogsc-status-feed-ajax-nonce" value="8caa405837">

            <span id="loading-status-feed-20" class="loading-feed" style="display: none;"></span>
            <label class="switch">
              <input type="checkbox" class="toggle-status check-toggle" data-feed-id="20" checked="checked" disabled>
              <span class="slider round button-toggle"></span>
            </label>
          </td>
        </tr>
        <tr>
          <td data-label="ID" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('21', 'wc-gsheetconnector'); ?>
          </td>
          <td data-label="Feed Name" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <div class="feed-rename-col">
              <a class="feed-link text-decoration-none" href="?page=wc-gsheetconnector-config&amp;tab=feed_settings&amp;feed_id=21">
                <?php echo esc_html__('feed6', 'wc-gsheetconnector'); ?> </a>
              <div class="feed-rename-box d-flex align-center" id="open-feed" style="display: none">
                <p>
                  <?php echo esc_html__('Rename Feed', 'wc-gsheetconnector'); ?> </p>
                <input type="text" id="edit-feed-name" style="display: none;">
                <button id="save-feed-name-btn" class="button" style="display: none;"><?php echo esc_html__('Save', 'wc-gsheetconnector'); ?></button>
                <span id="loading-feed-21" class="loading-feed" style="display: none;"></span>
              </div>
            </div>



            <div class="row-actions">
              <span class="edit">
                <a href="#" class="text-decoration-none">
                  <?php echo esc_html__('Edit', 'wc-gsheetconnector'); ?> </a> |
              </span>

              <span class="edit">
                <div id="feed-container">

                  <input type="hidden" name="feed_id" id="data-feed-id" value="21">
                  <input type="hidden" name="feed_name" id="data-feed-name" value="feed6">

                  <button id="rename-feed-btn">
                    <?php echo esc_html__('Rename Feed', 'wc-gsheetconnector'); ?> </button>
                  <input type="hidden" name="woogsc-update-feed-name-ajax-nonce" id="woogsc-update-feed-name-ajax-nonce" value="a23482be9b">

                </div>
              </span>

              <span class="trash">
                | <a href="#" class="trash-feed text-decoration-none" data-feed-id="21">
                  <?php echo esc_html__('Trash', 'wc-gsheetconnector'); ?> </a>
                <span id="loading-feed-21" class="loading-feed" style="display: none;"></span>
                <input type="hidden" name="woogsc-trash-feed-ajax-nonce" id="woogsc-trash-feed-ajax-nonce" value="1f604fc974">


              </span>
            </div>
          </td>
          <td data-label="Author" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('admin', 'wc-gsheetconnector'); ?> </td>
          <td data-label="Location" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('All', 'wc-gsheetconnector'); ?> </td>
          <td data-label="Created" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <?php echo esc_html__('2026-04-27 09:32:27', 'wc-gsheetconnector'); ?> </td>

          <td data-label="Status" class="pt-15 pb-15 pl-20 pr-20 text-left">
            <input type="hidden" name="woogsc-status-feed-ajax-nonce" id="woogsc-status-feed-ajax-nonce" value="8caa405837">

            <span id="loading-status-feed-21" class="loading-feed" style="display: none;"></span>
            <label class="switch">
              <input type="checkbox" class="toggle-status check-toggle" data-feed-id="21" checked="checked" disabled>
              <span class="slider round button-toggle"></span>
            </label>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

</div>