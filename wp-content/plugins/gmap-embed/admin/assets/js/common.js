(function ($) {
  $(document).ready(function () {
    "use strict";

    /** Constraints */

    /** Common Functions */
    function isGuttenbergActive() {
      return document.body.classList.contains("block-editor-page");
    }

    /** Classic Editor Google Map Select Popup box */
    if (!isGuttenbergActive()) {
      // To load Maps List
      function loadSrmGmapsList() {
        $("#wgm_all_maps").find(".spinner").addClass("is-active");
        $("#wpgmapembed_list").html("");
        var data = {
          action: "wpgmapembed_popup_load_map_data",
          wgm_ajax_nonce: wgm_l.wgm_ajax_nonce,
        };
        jQuery.post(ajaxurl, data, function (response) {
          $("#wgm_all_maps").find(".spinner").removeClass("is-active");
          $("#wpgmapembed_list").html(response);
        });
      }

      // Loading Map/List on window load
      $(window).on("load", loadSrmGmapsList);

      // Removing Popup Box
      function removeSrmGmapPopup() {
        self.parent.tb_remove();
      }

      // Inserting ShortCode From List on click insert button
      $(document.body).on("click", ".wpgmap-insert-shortcode", function () {
        var shortcode = $(this)
          .parent()
          .parent()
          .find(".wpgmap-shortcode")
          .val();
        if (!tinyMCE.activeEditor || tinyMCE.activeEditor.isHidden()) {
          $("textarea#content").val(shortcode);
        } else {
          tinyMCE.execCommand("mceInsertContent", false, shortcode);
        }
        removeSrmGmapPopup();
      });

      // On Click fire removing Popup Box(removeSrmGmapPopup)
      $(document.body).on("click", ".wp_gmap_close_btn", removeSrmGmapPopup);

      // On Escape fire removing Popup Box(removeSrmGmapPopup)
      $(document).keyup(function (e) {
        if (e.which === 27) {
          removeSrmGmapPopup();
        }
      });
    }

    /** Map related functions/event listeners */

    // ******* Remove Single Map *******
    $(document.body).on("click", ".wgm_wpgmap_delete", function () {
      if (!confirm("Are you sure to Delete")) {
        return false;
      }
      $("#wp-gmap-nav").find(".spinner").addClass("is-active");
      var btn_class = $(this);
      btn_class.prop("disabled", true);
      var post_id = $(this).data("id");
      // Validate post_id is numeric
      if (isNaN(post_id) || parseInt(post_id) <= 0) {
        alert("Invalid map ID.");
        btn_class.prop("disabled", false);
        return false;
      }
      var data = {
        action: "wpgmapembed_remove_wpgmap",
        post_id: post_id,
        wgm_ajax_nonce: wgm_l.wgm_ajax_nonce,
      };

      jQuery.post(ajaxurl, data, function (response) {
        try {
          response = JSON.parse(response);
        } catch (e) {
          alert("Unexpected server response.");
          btn_class.prop("disabled", false);
          return;
        }
        $("#wp-gmap-nav").find(".spinner").removeClass("is-active");
        if (response.responseCode === 1) {
          btn_class.prop("disabled", false);
          window.location.reload();
        } else {
          alert("Something went wrong, could not delete, please try again.");
        }
      });
    });

    // ************** Save, Update and Insert Button
    $(document.body).on(
      "click",
      "#wp-gmap-embed-save,#wp-gmap-embed-update",
      function () {
        $("body .wpgmap_msg_error").html("");
        $(this).prop("disabled", true);
        var btn_id,
          parent,
          wpgmap_show_heading = 0,
          wpgmap_show_infowindow = 0,
          wpgmap_disable_zoom_scroll = 0,
          wpgmap_enable_direction = 0;
        $(this).parent().find(".spinner").addClass("is-active");

        btn_id = $(this).attr("id");
        if (btn_id === "wp-gmap-embed-save") {
          parent = $("body #wp-gmap-new");
        } else if (btn_id === "wp-gmap-embed-update") {
          parent = $("body #wp-gmap-edit");
        }

        // getting checkbox values
        if (parent.find("#wpgmap_show_heading").is(":checked") === true) {
          wpgmap_show_heading = 1;
        }

        if (parent.find("#wpgmap_show_infowindow").is(":checked") === true) {
          wpgmap_show_infowindow = 1;
        }
        if (
          parent.find("#wpgmap_disable_zoom_scroll").is(":checked") === true
        ) {
          wpgmap_disable_zoom_scroll = 1;
        }

        if (parent.find("#wpgmap_enable_direction").is(":checked") === true) {
          wpgmap_enable_direction = 1;
        }

        var wpgmap_title = parent.find("#wpgmap_title").val();
        var wpgmap_heading_class = parent.find("#wpgmap_heading_class").val();
        var wpgmap_latlng = parent.find("#wpgmap_latlng").val();
        var wpgmap_map_zoom = parent.find("#wpgmap_map_zoom").val();
        var wpgmap_map_width = parent.find("#wpgmap_map_width").val();
        var wpgmap_map_height = parent.find("#wpgmap_map_height").val();
        var wpgmap_map_type = parent.find("#wpgmap_map_type").val();
        var wpgmap_center_lat_lng = parent.find("#wpgmap_center_lat_lng").val();
        var wgm_theme_json = parent.find("#wgm_theme_json").val();

        // Basic client-side validation
        if (
          !wpgmap_latlng ||
          typeof wpgmap_latlng !== "string" ||
          wpgmap_latlng.trim() === ""
        ) {
          parent
            .find(".wpgmap_msg_error")
            .html(
              '<div class="error bellow-h2 notice notice-error is-dismissible"><p>Please input Latitude and Longitude</p></div>'
            );
          $(this).prop("disabled", false);
          $(this).parent().find(".spinner").removeClass("is-active");
          return false;
        }

        var map_data = {
          wpgmap_title: wpgmap_title,
          wpgmap_heading_class: wpgmap_heading_class,
          wpgmap_show_heading: wpgmap_show_heading,
          wpgmap_latlng: wpgmap_latlng,
          wpgmap_map_zoom: wpgmap_map_zoom,
          wpgmap_disable_zoom_scroll: wpgmap_disable_zoom_scroll,
          wpgmap_map_width: wpgmap_map_width,
          wpgmap_map_height: wpgmap_map_height,
          wpgmap_map_type: wpgmap_map_type,
          wpgmap_show_infowindow: wpgmap_show_infowindow,
          wpgmap_enable_direction: wpgmap_enable_direction,
          wpgmap_center_lat_lng: wpgmap_center_lat_lng,
          wgm_theme_json: wgm_theme_json,
        };

        if (btn_id === "wp-gmap-embed-save") {
          map_data.action_type = "save";
        } else if (btn_id === "wp-gmap-embed-update") {
          map_data.action_type = "update";
          map_data.post_id = parent.find("#wpgmap_map_id").val();
        }
        var data = {
          action: "wpgmapembed_save_map_data",
          map_data: map_data,
          wgm_map_create_nonce: wgm_l.wgm_map_create_nonce,
        };

        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(ajaxurl, data, function (response) {
          $("#" + btn_id)
            .parent()
            .find(".spinner")
            .removeClass("is-active");
          try {
            response = JSON.parse(response);
          } catch (e) {
            parent
              .find(".wpgmap_msg_error")
              .html(
                '<div class="error bellow-h2 notice notice-error is-dismissible"><p>Unexpected server response.</p></div>'
              );
            $("#" + btn_id).prop("disabled", false);
            return;
          }

          // In case of any exception
          if (response.responseCode === 0) {
            $("#" + btn_id).prop("disabled", false);
            parent
              .find(".wpgmap_msg_error")
              .html(
                '<div class="error bellow-h2 notice notice-error is-dismissible"><p>' +
                  $("<div>").text(response.message).html() +
                  "</p></div>"
              );
          } else {
            // In case of successful state
            if (
              btn_id === "wp-gmap-embed-save" ||
              btn_id === "wp-gmap-embed-update"
            ) {
              $("#" + btn_id).prop("disabled", false);
              if (btn_id === "wp-gmap-embed-save") {
                window.location.href =
                  "?page=wpgmapembed&tag=edit&id=" +
                  encodeURIComponent(response.post_id) +
                  "&message=1&wgm_map_create_nonce=" +
                  encodeURIComponent(wgm_l.wgm_map_create_nonce);
              } else {
                $("body .wpgmap_msg_error").html(
                  '<div class="success bellow-h2 notice notice-success is-dismissible"><p>' +
                    $("<div>").text(response.message).html() +
                    "</p></div>"
                );
              }
            }
          }
        });
      }
    );

    /**
     * To view premium notice
     *
     * @since 1.7.5
     */
    $(document.body)
      .find(".wgm_enable_premium")
      .on("click", function () {
        var wgm_notice_text = $(this).attr("data-notice");
        Swal.fire({
          icon: "info",
          showCloseButton: true,
          title: wgm_l.locales.sweet_alert.oops,
          html:
            wgm_notice_text +
            '<br><br><span style="font-size:25px;font-weight:bold;">Starting from $19 only</span><br><br><a target="_blank" href="' +
            wgm_l.get_p_v_url +
            '">Upgrade to Pro</a>',
          confirmButtonText: "Close",
        });
      });

    /**
     * Settings tab active/inactive and rendaring
     *
     * @since 1.7.5
     */
    $(document.body)
      .find(".wgm-settings-menu li")
      .on("click", function (e) {
        e.preventDefault();
        $(this).siblings().removeClass("active");
        $(this).addClass("active");
        var wgm_tab_id = $(this).attr("data-tab");
        $(".wgm_settings_tabs").hide();
        $("#" + wgm_tab_id).show();
      });

    /**
     * Popup window for create Google Map API key
     *
     * @since 1.9.0
     */
    $(document.body)
      .find(".wgm_api_create_gplatform")
      .on("click", function (e) {
        e.preventDefault();
        const wgmDimensions = {
          width: 570,
          height: 700,
        };

        var wgmPopupLeft = (screen.width - wgmDimensions.width) / 2;
        var wgmPopupTop = (screen.height - wgmDimensions.height) / 2;

        window.open(
          "https://console.cloud.google.com/google/maps-hosted",
          "WP Google Map - Create your API key",
          "resizable=yes,width=" +
            wgmDimensions.width +
            ",height=" +
            wgmDimensions.height +
            ",left=" +
            wgmPopupLeft +
            ",top=" +
            wgmPopupTop
        );
        return false;
      });
  });
})(jQuery);
