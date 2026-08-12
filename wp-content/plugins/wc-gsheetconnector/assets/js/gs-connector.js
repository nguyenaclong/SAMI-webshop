jQuery(document).ready(function () {
  /**
   * verify the api code
   * @since 1.0
   */
 jQuery(document).on("click", "#wcgsc-save-code", function (event) {
  event.preventDefault();
  jQuery(".loading-sign").addClass("loading");
  var data = {
    action: "wcgsc_verify_integration",
    code: jQuery("#wcgsc-code").val(),
    security: jQuery("#wcgsc-ajax-nonce").val(),
  };
  jQuery.post(ajaxurl, data, function (response) {
    var clear_msg = response.data;

    if (!response.success) {
      jQuery(".loading-sign").removeClass("loading");
      jQuery("#gs-woo-validation-message").empty();
      jQuery(
        "<span class='gsc-msg gsc-error fw-400 text-dark text-center pt-10 pb-10 manual-margin'>Access code Can't be blank.</span>",
        ).appendTo("#gs-woo-validation-message");
    } else {
      jQuery(".loading-sign").removeClass("loading");
      jQuery("#gs-woo-validation-message").empty();
      jQuery(
        "<div class='gsc-msg gsc-success fw-400 text-dark text-center pt-10 pb-10 manual-margin'>Google account connected successfully. You can now sync your WooCommerce data with Google Sheets.</div>",
        ).appendTo("#gs-woo-validation-message");

      setTimeout(function () {
        window.location.href = jQuery("#redirect_auth").val();
      }, 1000);
    }
  });
});

  /**
   * deactivate the api code
   * @since 1.0
   */
  /**
   * Handle deactivation of Google Sheet integration via AJAX.
   *
   * - Asks for user confirmation
   * - Sends deactivation request to server
   * - Displays message and reloads page on success
   * - Handles loading indicator and nonce validation
   */

  // Open popup (NO confirm alert)
 jQuery(document).on("click", "#wcgsc-deactivate-log", function (e) {
  e.preventDefault();
  e.stopPropagation();
  jQuery("#wcgsc-confirm-popup").removeClass("d-none");
});

  // Cancel popup
 jQuery(document).on("click", "#wcgsc-popup-cancel", function (e) {
  e.preventDefault();
  e.stopPropagation();
  jQuery("#wcgsc-confirm-popup").addClass("d-none");
});

  // Confirm deactivate (AJAX same as before)
 jQuery(document).on("click", "#wcgsc-popup-confirm", function (e) {
  e.preventDefault();
  e.stopPropagation();
  jQuery("#wcgsc-confirm-popup").addClass("d-none");
  jQuery(".loading-sign-deactive").addClass("loading");

  var data = {
    action: "wcgsc_deactivate_integration",
    security: jQuery("#wcgsc-ajax-nonce").val(),
  };

  jQuery.post(ajaxurl, data, function (response) {
    jQuery(".loading-sign-deactive").removeClass("loading");
    jQuery("#deactivate-msg").empty();

    if (response && response.success) {
      jQuery(
        "<div class='gsc-msg gsc-success fw-400 text-dark text-center mt-10 pt-10 pb-10 manual-margin'>Google account disconnected. WooCommerce data will not sync with Google Sheets until it is reconnected.</div>",
        ).appendTo("#deactivate-msg");

      setTimeout(function () {
        location.reload();
      }, 1000);
    } else {
      jQuery(
        "<div class='gsc-msg gsc-error fw-400 text-dark text-center mt-10 pt-10 pb-10 manual-margin'>Error while deactivation. Please try again.</div>",
        ).appendTo("#deactivate-msg");
    }
  });
});

  /** hide notice  */
 jQuery(document).on("click", "#wcgsc-pro-dismiss-header-notice", function () {
  var nonce = jQuery("#wcgsc-ajax-nonce").val();

  jQuery("#pro-notice-bar").hide();

  jQuery.post(ajaxurl, {
    action: "wcgsc_dismiss_pro_notice",
    nonce: nonce,
  });
});

 function html_decode(input) {
  var doc = new DOMParser().parseFromString(input, "text/html");
  return doc.documentElement.textContent;
}

jQuery(document).on("click", "#wcgsc-sync", function () {
  jQuery(this).parent().children(".loading-sign").addClass("loading");
  var integration = jQuery(this).data("init");
  var data = {
    action: "wcgsc_sync_google_account",
    isajax: "yes",
    isinit: integration,
    security: jQuery("#wcgsc-ajax-nonce").val(),
  };

  jQuery.post(ajaxurl, data, function (response) {
    if (response == -1) {
        return false; // Invalid nonce
      }

      if (response.data.success == "yes") {
        jQuery(".loading-sign").removeClass("loading");
        jQuery("#wcgsc-validation-message").empty();
        jQuery(
          "<span class='wcgsc-valid-message'>Fetched latest sheet names.</span>",
          ).appendTo("#wcgsc-validation-message");
        setTimeout(function () {
          location.reload();
        }, 1000);
      } else {
        jQuery(this).parent().children(".loading-sign").removeClass("loading");
        location.reload(); // simply reload the page
      }
    });
});

  /**
   * Clear debug
   */
jQuery(document).on("click", ".debug-clear", function () {
  jQuery(".clear-loading-sign").addClass("loading");
  var data = {
    action: "wcgsc_clear_log",
    security: jQuery("#wcgsc-ajax-nonce").val(),
  };
  jQuery.post(ajaxurl, data, function (response) {
    var clear_msg = response.data;
    if (response.success) {
      jQuery(".clear-loading-sign").removeClass("loading");
      jQuery("#wcgsc-validation-message").empty();
      jQuery(
        "<span class='wcgsc-valid-message'>" + clear_msg + "</span>",
        ).appendTo("#wcgsc-validation-message");
      setTimeout(function () {
        location.reload();
      }, 1000);
    }
  });
});

jQuery(document).on("submit", "#gsSettingFormFree", function (event) {
  console.log("prevent the subitting the form");
  jQuery("#error_spread").html("");
  jQuery("#error_gsTabName").html("");

  var submit = true;
  var spreadsheetsName = jQuery("#wcgsc-sheet-id").val();
  var gsTabName = jQuery("input.wcgsc_order_state:checked").length;

  if (spreadsheetsName == "") {
    jQuery("#error_spread").html("* Please Select Spreadsheet Name !");
    submit = false;
  }
  if (gsTabName <= 0) {
    jQuery("#error_gsTabName").html("* Please select atleast one Tabs !");
    submit = false;
  }

  if (submit == false) {
    event.preventDefault();
    window.scrollTo({ top: 0, behavior: "smooth" });
  }
});

jQuery(".wcgsc-list-set32").hide();
jQuery(".wcgsc-list-set33").hide();
jQuery(".wcgsc-list-set34").hide();
jQuery(".wcgsc-list-set35").hide();
jQuery(".wcgsc-list-set36").hide();
jQuery(document).on("click", ".wcgsc-list-set", function (event) {
  var $this = jQuery(this);
  var $id = $this.attr("data-id");

  if (
    $id == "31" ||
    $id == "32" ||
    $id == "33" ||
    $id == "34" ||
    $id == "35" ||
    $id == "36"
    ) {
    if (jQuery(".wcgsc-list-set" + $id).css("display") == "none") {
      jQuery(".wcgsc-list-set31").hide();
      jQuery(".wcgsc-list-set32").hide();
      jQuery(".wcgsc-list-set33").hide();
      jQuery(".wcgsc-list-set34").hide();
      jQuery(".wcgsc-list-set").removeClass("active-t-gs");
      jQuery(this).addClass("active-t-gs");
      jQuery(".wcgsc-list-set" + $id).show();
    } else {
      if (!$this.hasClass("active-t-gs")) {
        jQuery(".wcgsc-list-set" + $id).hide();
      }
    }
  } else {
    if (jQuery(".wcgsc-list-set" + $id).css("display") == "none") {
        //jQuery(".gs-woo-list-set"+$id).css("display", "block");
      jQuery(".wcgsc-list-set" + $id).show("slow");
      jQuery(".mini_mize" + $id).show();
      jQuery(".maxi_mize" + $id).hide();
    } else {
        //jQuery(".gs-woo-list-set"+$id).css("display", "none");
      jQuery(".wcgsc-list-set" + $id).hide("slow");
      jQuery(".mini_mize" + $id).hide();
      jQuery(".maxi_mize" + $id).show();
    }
  }
});
});

jQuery(document).ready(function () {
  jQuery(".wcgsc-addons-list").each(function () {
    if (jQuery(this).html().trim().length === 0) {
      jQuery(this).addClass("blank_div");
      jQuery(this).prev("h2").hide();
    }
  });
});

/**
 * Clear debug for system status tab
 */
jQuery(document).on("click", ".wcgsc-clear-content-logs", function () {
  jQuery(".wcgsc-clear-loading-sign-logs").addClass("loading");
  var data = {
    action: "wcgsc_log_systeminfo",
    security: jQuery("#wcgsc-ajax-nonce").val(),
  };

  jQuery.post(ajaxurl, data, function (response) {
    if (response == -1) {
      return false; // Invalid nonce
    }

    if (response.success) {
      jQuery(".wcgsc-clear-loading-sign-logs").removeClass("loading");
      jQuery(".wcgsc-clear-content-logs-msg").html("Logs are cleared.");
      setTimeout(function () {
        location.reload();
      }, 1000);
    }
  });
});

jQuery(document).ready(function ($) {
  $("#wcgsc_dro_option").on("change", function () {
    var selectedValue = $(this).val();

    if (selectedValue === "wcgsc_manual") {
      $("#wcgsc-confirm-manual-popup-pro").removeClass("d-none");

      // reset dropdown
      $(this).val("wcgsc_existing");
    }

    if (selectedValue === "wcgsc_service") {
      $("#wcgsc-confirm-service-popup-pro").removeClass("d-none");

      // reset dropdown
      $(this).val("wcgsc_existing");
    }

    $(".wcgsc-popup-close-pro, .wcgsc-popup-service-close-pro").on(
      "click",
      function () {
        $(".wcgsc-popup-overlay").addClass("d-none");
      },
      );

    $(".wcgsc-popup-overlay").on("click", function (e) {
      if ($(e.target).is(".wcgsc-popup-overlay")) {
        $(this).addClass("d-none");
      }
    });
  });

  // notification slider
  let totalSlides = $(
    ".notification-gsc-slider-track .notification-gsc-slide",
    ).length;

  if (totalSlides <= 1) {
    $(".notification-gsc-slider-arrows").hide();
  }
  function showNextSlide(currentSlide) {
    var track = currentSlide.closest(".notification-gsc-slider-track");
    var slides = track.find(".notification-gsc-slide");
    var currentIndex = slides.index(currentSlide);
    var nextIndex = currentIndex + 1;

    currentSlide.remove();

    slides = track.find(".notification-gsc-slide");

    if (slides.length > 0) {
      if (nextIndex >= slides.length) {
        nextIndex = 0;
      }
      slides.hide().eq(nextIndex).show();
    }
    location.reload();
  }

  $(document).on(
    "click",
    ".wcgsc-upgrade-close, .wcgsc-dismiss-btn",
    function () {
      var key = $(this).data("key");
      var currentSlide = $(this).closest(".notification-gsc-slide");
      $.post(
        ajaxurl,
        {
          action: "wcgsc_dismiss_notice",
          key: key,
          security: $("#wcgsc-ajax-nonce").val(),
        },
        function () {
          showNextSlide(currentSlide);
        },
        );
    },
    );

  $(document).on("click", ".wcgsc-btn-later", function () {
    var key = $(this).data("key");
    var currentSlide = $(this).closest(".notification-gsc-slide");
    $.post(
      ajaxurl,
      {
        action: "wcgsc_snooze_notice",
        key: key,
        security: $("#wcgsc-ajax-nonce").val(),
      },
      function () {
        showNextSlide(currentSlide);
        // loader.removeClass("loading");
      },
      );
  });




});

document.addEventListener("DOMContentLoaded", function () {
  const slides = document.querySelectorAll(".notification-gsc-slide");
  const prevBtn = document.querySelector(".notification-gsc-slider-btn.prev");
  const nextBtn = document.querySelector(".notification-gsc-slider-btn.next");

  let index = 0;

  function wcgscupdateSlider() {
    if (!slides.length) return;

    slides.forEach((slide) => slide.classList.remove("active"));

    if (slides[index]) {
      slides[index].classList.add("active");
    }
  }

  // SAFE CHECK
  if (nextBtn) {
    nextBtn.addEventListener("click", function () {
      index++;
      if (index >= slides.length) {
        index = 0;
      }
      wcgscupdateSlider();
    });
  }

  // SAFE CHECK
  if (prevBtn) {
    prevBtn.addEventListener("click", function () {
      index--;
      if (index < 0) {
        index = slides.length - 1;
      }
      wcgscupdateSlider();
    });
  }

  wcgscupdateSlider();

  // ======================
  // ADD BADGE (only if value exists)
  // ======================
  var el = document.querySelector(".wcgsc-free-selected-method");

  if (el && el.dataset.value && el.dataset.value.trim() !== "") {
    var badgeText = el.dataset.value.trim();

    var getvalue = el.getAttribute("data-value");

    document

    .querySelectorAll(".nav-tab-wrapper .nav-tab")

    .forEach(function (tab) {
      var href = tab.getAttribute("href") || "";

      if (href.indexOf("tab=integration") !== -1) {
        tab.style.position = "relative";

        if (
          tab.querySelector(".wcgsc-free-selected-badge") ||
          tab.querySelector(".wcgsc-free-selected-authrequired-badge")
          ) {
          return;
      }

      var badge = document.createElement("div");

      if (getvalue == "Auth Required") {
        badge.className = "wcgsc-free-selected-authrequired-badge";
      } else {
        badge.className = "wcgsc-free-selected-badge";
      }

      badge.textContent = badgeText;

      tab.appendChild(badge);
    }
  });
  }

  // copy logs in integration tab
  const copyBtn = document.getElementById("wcgsc-copy-logs");
  const msgDiv = document.querySelector(".wcgsc-copy-msg");
  if (copyBtn && msgDiv) {

    copyBtn.addEventListener("click", function (e) {
      e.preventDefault();

      const tbody = document.querySelector(".error-log-table tbody");
      if (!tbody) return;

      const rows = tbody.querySelectorAll("tr");

      if (!rows.length) {
        showMessage("No logs found to copy.", "error");
        return;
      }

      let output = "";

      rows.forEach((tr) => {
        const cols = tr.querySelectorAll("td");
        if (cols.length < 5) return;

        const date = (cols[0].innerText || "").trim();
        const errorId = (cols[1].innerText || "").trim();
        const code = (cols[2].innerText || "").trim();
        const message = (cols[3].innerText || "").trim();

      // 🔥 Proper details extraction
        const detailsCell = cols[4];
        const details = detailsCell.querySelector("pre")
        ? detailsCell.querySelector("pre").innerText.trim()
        : (detailsCell.innerText || "").trim();

        output += `Date: ${date}\n`;
        output += `Error ID: ${errorId}\n`;
        output += `Code: ${code}\n`;
        output += `Message: ${message}\n`;
        output += `Details: ${details}\n`;
        output += `\n==========================\n\n`;
      });

    // Modern Clipboard API
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard
        .writeText(output)
        .then(function () {
          wcgscshowMessage("Copied successfully.", "success");
        })
        .catch(function () {
          wcgscfallbackCopy(output);
        });
      } else {
        wcgscfallbackCopy(output);
      }

    // Fallback Method
      function wcgscfallbackCopy(text) {
        const ta = document.createElement("textarea");
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        try {
          document.execCommand("copy");
          wcgscshowMessage("Copied successfully.", "success");
        } catch (err) {
          wcgscshowMessage("Copy failed. Please copy manually.", "error");
        }
        document.body.removeChild(ta);
      }

      function wcgscshowMessage(text, type) {
        msgDiv.innerText = text;
        msgDiv.classList.remove("d-none");

        setTimeout(function () {
          msgDiv.classList.add("d-none");
        }, 2000);
      }
    });
  }
});

  /* Select box JS  */
document.addEventListener("DOMContentLoaded", function () {
    // ONLY select with class "gsc-select"
  document.querySelectorAll("select.gsc-select").forEach((select) => {
      // skip already processed
    if (select.classList.contains("auto-processed")) return;
    select.classList.add("auto-processed");

      // hide original select
    select.style.display = "none";

    const wrapper = document.createElement("div");
    wrapper.className = "auto-select";

    const display = document.createElement("div");
    display.className = "auto-select-display";
    display.innerText =
    select.options[select.selectedIndex]?.text || "Select";

    const optionsBox = document.createElement("div");
    optionsBox.className = "auto-select-options";

    [...select.options].forEach((opt, index) => {
      const item = document.createElement("div");
      item.className = "auto-select-option";
      item.innerText = opt.text;

      item.addEventListener("click", () => {
        select.selectedIndex = index;
        display.innerText = opt.text;
        optionsBox.style.display = "none";

          // trigger change event (WordPress + plugins compatible)
        select.dispatchEvent(new Event("change", { bubbles: true }));
      });

      optionsBox.appendChild(item);
    });

    display.addEventListener("click", (e) => {
      e.stopPropagation();
      optionsBox.style.display =
      optionsBox.style.display === "block" ? "none" : "block";
    });

    wrapper.appendChild(display);
    wrapper.appendChild(optionsBox);

      // insert before select & move select inside
    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(select);
  });

    // close dropdown on outside click
  document.addEventListener("click", function (e) {
    document.querySelectorAll(".auto-select-options").forEach((box) => {
      if (!box.parentElement.contains(e.target)) {
        box.style.display = "none";
      }
    });
  });

});







/**jQuery for save uninstall settings */
jQuery(document).ready(function ($) {
// uninstall plugin settings
  const $wcgsc_unistall_checkbox = $("#gs_woo_unistall_settings");
  const $wcgsc_unistall_saveBtn = $(".wcgsc-uninstall-settings-save");
  const $wcgsc_unistall_msg = $("#wcgsc-uninstall-msg");
  const $wcgsc_unistall_loader = $(".wcgsc-loading-uninstall");
  const $wcgsc_unistall_popup = $("#wcgsc-uninstall-free");

  // Page load → disable button
  $wcgsc_unistall_saveBtn.prop("disabled", true).addClass("common-disable");

  // Checkbox change
  $wcgsc_unistall_checkbox.on("change", function () {
    // If user tries to enable it → show popup
    if ($(this).is(":checked")) {
      // Uncheck temporarily
      $(this).prop("checked", false);

      // Show popup
      $wcgsc_unistall_popup.removeClass("d-none");

      return;
    }

    // Enable save button normally when unchecked
    $wcgsc_unistall_saveBtn.prop("disabled", false).removeClass("common-disable");
  });

  /* =========================
     POPUP CONFIRM BUTTON
     ========================= */

  $("#wcgsc-confirm-enable-uninstall").on("click", function () {
    $wcgsc_unistall_checkbox.prop("checked", true);

    $wcgsc_unistall_popup.addClass("d-none");

    $wcgsc_unistall_saveBtn.prop("disabled", false).removeClass("common-disable");
  });

  /* =========================
     POPUP CANCEL BUTTON
     ========================= */

  $("#wcgsc-free-uninstall-cancel").on("click", function () {
    $wcgsc_unistall_checkbox.prop("checked", false);

    $wcgsc_unistall_popup.addClass("d-none");
  });

  /* =========================
     SAVE SETTINGS
     ========================= */

  $wcgsc_unistall_saveBtn.on("click", function (e) {
    e.preventDefault();
    var isChecked = $wcgsc_unistall_checkbox.is(":checked");
    $.ajax({
      url: ajaxurl,
      type: "POST",
      dataType: "json",
      data: {
        action: "wcgsc_save_uninstall_settings",
        uninstall_setting: isChecked ? 1 : 0,
        security: $("#wcgsc-uninstall-ajax-nonce").val(),
      },

      beforeSend: function () {
        $wcgsc_unistall_loader.addClass("loading");
        $wcgsc_unistall_saveBtn.prop("disabled", true).addClass("common-disable");
      },

      success: function (response) {
        if (!response.success) return;

        $wcgsc_unistall_msg.removeClass("gsc-success gsc-error d-none");

        $wcgsc_unistall_msg
        .addClass("gsc-success")
        .text("Plugin preferences updated successfully.");

        setTimeout(function () {
          $wcgsc_unistall_msg.addClass("d-none").text("");
        }, 2000);
      },

      error: function () {
        $wcgsc_unistall_msg
        .removeClass("d-none")
        .addClass("gsc-error")
        .text("Something went wrong");

        $wcgsc_unistall_saveBtn.prop("disabled", false).removeClass("common-disable");
      },

      complete: function () {
        $wcgsc_unistall_loader.removeClass("loading");
      },
    });
  });
});
/**
 * Scroll to auth code field after OAuth redirect
 */
function wcgscScrollToAuthCode() {
  const code = new URLSearchParams(window.location.search).get("code");

  if (!code) {
    return;
  }

  const selectors = ["#wcgsc-code"];

  let target = null;

  for (let sel of selectors) {
    if (document.querySelector(sel)) {
      target = sel;
      break;
    }
  }

  if (target) {
    window.location.hash = target.replace("#", "");

    document.querySelector(target).scrollIntoView({
      behavior: "smooth",
      block: "start",
    });
  }
}
jQuery(document).ready(function () {
  wcgscScrollToAuthCode();
});

/****Extenxion Counter****/
jQuery(document).ready(function ($) {
  $(".wc-free-counter").each(function () {
    let $this = $(this);
    let countTo = parseFloat($this.attr("data-count"));

    $({ countNum: 0 }).animate(
    {
      countNum: countTo,
    },
    {
      duration: 2500,
      easing: "swing",

      step: function () {
        if (countTo % 1 !== 0) {
          $this.text(this.countNum.toFixed(1));
        } else {
          $this.text(Math.floor(this.countNum));
        }
      },

      complete: function () {
        if (countTo % 1 !== 0) {
          $this.text(countTo.toFixed(1));
        } else {
          $this.text(countTo);
        }
      },
    },
    );
  }); 

  function wcgscfreeLoadFeedPage(page) {
    $.post(
      ajaxurl,
      {
        action: "wcgsc_free_paginate_feed_list",
        paged: page,
        security: $("#wcgsc-pagination-nonce").val()
      },
      function (res) {
        if (res.success) {
          let rows = res.data.rows_html;
          let pagination = res.data.pagination_html;

                // Inject table rows
          $("#wcgsc-feed-table-body").html(rows);

                // Inject pagination links
          $("#wcgsc-pagination-wrap").html(pagination);

                // Toggle headers and pagination visibility based on feed existence
          if (!res.data.has_feeds) {
            $("#wcgsc-feed-table thead").hide();
            $("#wcgsc-pagination-wrap").hide();
          } else {
            $("#wcgsc-feed-table thead").show();
            $("#wcgsc-pagination-wrap").show();
          }

          $("#wcgsc-feed-table").attr("data-page", page);
        }
      }
      );
  }

// Initial load
  $(document).ready(function () {
    wcgscfreeLoadFeedPage(1);
  });

// Event delegation for pagination buttons
  $(document).on("click", ".wcgsc-page-link", function (e) {
    e.preventDefault();
    let page = $(this).data("page");
    if (page) {
      wcgscfreeLoadFeedPage(page);
    }
  });

});
  /***new slider for without permission for existing method */
document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".gsc-slider-wrapper").forEach(function (wrapper) {
    const slider = wrapper.querySelector(".gsc-slider");
    const slides = wrapper.querySelectorAll(".gsc-slide");
    const prevBtn = wrapper.querySelector(".gsc-nav.prev");
    const nextBtn = wrapper.querySelector(".gsc-nav.next");

    let current = 0;

    function updateSlider() {
      slider.style.transform = "translateX(" + -current * 100 + "%)";
    }

    nextBtn?.addEventListener("click", function () {
      current = (current + 1) % slides.length;
      updateSlider();
    });

    prevBtn?.addEventListener("click", function () {
      current = (current - 1 + slides.length) % slides.length;
      updateSlider();
    });

    // ✅ attach function
    wrapper.goToSlide = function (index) {
      current = index;
      updateSlider();
    };
  });

  /* =========================
     AUTO MOVE TO STEP 4
     ========================== */

  setTimeout(function () {
    const errorBox = document.querySelector(".wcgsc-free-permission-error");
    const target = document.querySelector(".wc-free-connection-guide-slider");

    if (!errorBox) {
      console.log("No permission error.");
      return;
    }

    if (!target) {
      console.log("Slider not found.");
      return;
    }

    if (target.goToSlide) {
      target.goToSlide(3); // Step-4
      target.scrollIntoView({
        behavior: "smooth",
        block: "center",
      });
      console.log("Auto moved to Step 4.");
    } else {
      console.log("goToSlide not available.");
    }
  }, 800);
});