jQuery(document).ready(function (jQuery) {
  /**
   * Hide empty addon sections and mark them with a CSS class on page load.
   */

   jQuery(".gsheetconnector-addons-list").each(function () {
    if (jQuery(this).html().trim().length === 0) {
      jQuery(this).addClass("blank_div");
      jQuery(this).prev("div").hide();
    }
  });

  /**
   * Handle plugin install button click via AJAX.
   *
   * - Shows loading spinner
   * - Sends plugin slug and download URL to server
   * - On success, hides install button and shows activate button
   * - On error or failure, resets button state
   */

   jQuery(".wcgsc-install-plugin-btn").on("click", function () {
    var button = jQuery(this);
    var pluginSlug = button.data("plugin");
    var downloadUrl = button.data("download");
    var loaderSpan = button
    .closest(".button-bar")
    .find(".loading-sign-install");

    loaderSpan.addClass("loading");
    button.prop("disabled", true);

    jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      dataType: "json",
      data: {
        action: "wcgsc_free_extension_install_plugin",
        plugin_slug: pluginSlug,
        download_url: downloadUrl,
        security: jQuery("#wcgsc-ajax-nonce").val(),
      },

      success: function (response) {
        loaderSpan.removeClass("loading");

        if (response.success) {
          // ✅ Install success
          button.hide();

          button
          .closest(".button-bar")
          .find(".wcgsc-activate-plugin-btn")
          .show();
        } else {
          // ❌ Permission or other error → open popup
          jQuery(".popup-actions-active-msg").text(
            response.data.message ||
            "You do not have permission to install this plugin.",
            );

          jQuery("#wcgsc-confirm-active-popup-free").removeClass("d-none");

          button.prop("disabled", false);
        }
      },

      error: function () {
        loaderSpan.removeClass("loading");

        jQuery(".popup-actions-active-msg").text(
          "Something went wrong. Please try again.",
          );

        jQuery("#wcgsc-confirm-active-popup-free").removeClass("d-none");

        button.prop("disabled", false);
      },
    });
  });

  /**
   * Handle plugin activation button click via AJAX.
   *
   * - Shows loading spinner
   * - Sends plugin slug to server for activation
   * - On success, updates button to "Activated" and reloads page
   * - On error or failure, resets button and removes loading state
   */

   jQuery(document).on("click", ".wcgsc-activate-plugin-btn", function () {
    var button = jQuery(this);
    var pluginSlug = button.data("plugin");
    var loaderSpan = button.siblings(".loading-sign-active");

    loaderSpan.addClass("loading");
    button.prop("disabled", true);

    jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      dataType: "json",
      data: {
        action: "wcgsc_free_extension_activate_plugin",
        plugin_slug: pluginSlug,
        security: jQuery("#wcgsc-ajax-nonce").val(),
      },

      success: function (response) {
        loaderSpan.removeClass("loading");

        if (response.success) {
          // ✅ Success → reload only
          location.reload();
        } else {
          // ❌ Permission denied → open popup
          jQuery(".popup-actions-active-msg").text(
            response.data.message ||
            "You do not have permission to activate this plugin.",
            );

          jQuery("#wcgsc-confirm-active-popup-free").removeClass("d-none");

          button.prop("disabled", false);
        }
      },

      error: function () {
        loaderSpan.removeClass("loading");

        jQuery(".popup-actions-active-msg").text(
          "Something went wrong. Please try again.",
          );

        jQuery("#wcgsc-confirm-active-popup-free").removeClass("d-none");

        button.prop("disabled", false);
      },
    });
  });

  /**
   * Handle plugin deactivation button click via AJAX.
   *
   * - Sends plugin slug to server for deactivation
   * - On success, shows alert and reloads the page
   * - On error, shows AJAX error alert
   */

   let gselefproPluginSlug = null;

  // Open confirmation popup
  jQuery(document).on("click", ".wcgsc-deactivate-plugin", function (e) {
    e.preventDefault();

    gselefproPluginSlug = jQuery(this).data("plugin");
    jQuery("#wcgsc-confirm-deactive-popup-free").removeClass("d-none");
  });

  // Cancel popup
  jQuery("#wcgsc-deactive-popup-cancel-free").on("click", function () {
    gselefproPluginSlug = null;
    jQuery("#wcgsc-confirm-deactive-popup-free").addClass("d-none");
  });

  // Confirm deactivate
  jQuery("#wcgsc-deactive-popup-confirm-free").on("click", function () {
    if (!gselefproPluginSlug) return;

    jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      dataType: "json",
      data: {
        action: "wcgsc_free_extension_deactivate_plugin",
        plugin_slug: gselefproPluginSlug,
        security: jQuery("#wcgsc-ajax-nonce").val(),
      },
      success: function (response) {
        if (response.success) {
          jQuery(".success-message")
          .text(response.data || "Integration deactivated successfully!")
          .fadeIn()
          .delay(3000)
          .fadeOut();

          location.reload();
        }
      },
      error: function () {
        console.log("AJAX error while deactivating plugin");
      },
    });

    // Close popup
    jQuery("#wcgsc-confirm-deactive-popup-free").addClass("d-none");
    gselefproPluginSlug = null;
  });

});

document.querySelectorAll(".market-tab").forEach((tab) => {
  tab.addEventListener("click", function () {
    let filter = this.dataset.filter;

    document
    .querySelectorAll(".market-tab")
    .forEach((t) => t.classList.remove("active"));

    this.classList.add("active");

    document.querySelectorAll(".gsc-market-item").forEach((card) => {
      if (filter === "all") {
        card.style.display = "block";
      } else {
        card.style.display = card.classList.contains(filter) ? "block" : "none";
      }
    });
  });
});
