<?php
if (!defined('ABSPATH')) {
    exit;
}
// ************* WP Google Map Shortcode ***************
if (!function_exists('srm_gmap_embed_shortcode')) {

    /**
     * Generate map based on shortcode input
     *
     * @param $atts
     * @param $content
     *
     * @return string
     * @since 1.0.0
     */
    function srm_gmap_embed_shortcode($atts, $content)
    {
        static $count;
        if (!$count) {
            $count = 0;
        }
        $count++;
        // Sanitize and validate shortcode attributes
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts, 'gmap-embed');
        $wgm_map_id = intval($atts['id']);
        if ($wgm_map_id <= 0) {
            if (is_user_logged_in() && current_user_can('administrator')) {
                return "<span style='color:darkred;'>Shortcode attribute 'id' is missing or invalid. Please check WP Google Map plugin in WordPress admin panel(sidebar). This message only visible to Administrator</span>";
            }
            return '';
        }
        $wpgmap_title = esc_html(get_post_meta($wgm_map_id, 'wpgmap_title', true));
        $wpgmap_show_heading = esc_html(get_post_meta($wgm_map_id, 'wpgmap_show_heading', true));
        $wpgmap_heading_class = esc_attr(get_post_meta($wgm_map_id, 'wpgmap_heading_class', true));
        $wpgmap_map_zoom = intval(get_post_meta($wgm_map_id, 'wpgmap_map_zoom', true));
        $wpgmap_map_width = esc_attr(get_post_meta($wgm_map_id, 'wpgmap_map_width', true));
        $wpgmap_map_height = esc_attr(get_post_meta($wgm_map_id, 'wpgmap_map_height', true));
        $wpgmap_map_type = esc_html(get_post_meta($wgm_map_id, 'wpgmap_map_type', true));
        $wpgmap_enable_direction = esc_html(get_post_meta($wgm_map_id, 'wpgmap_enable_direction', true));
        $wpgmap_center_lat_lng = esc_html(get_center_lat_lng_by_map_id($wgm_map_id));
        $wgm_theme_json = get_post_meta($wgm_map_id, 'wgm_theme_json', true);
        ob_start();
        if ('' !== $wpgmap_center_lat_lng) {
            if ('1' === $wpgmap_show_heading) {
                echo "<h1 class='srm_gmap_heading_" . esc_attr($count) . " " . esc_attr($wpgmap_heading_class) . "'>" . esc_html(wp_strip_all_tags($wpgmap_title)) . '</h1>';
            }
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    var wgm_map = new google.maps.Map(document.getElementById("srm_gmp_embed_<?php echo esc_attr($count); ?>"), {
                        center: new google.maps.LatLng(<?php echo esc_js($wpgmap_center_lat_lng); ?>),
                        zoom: <?php echo intval($wpgmap_map_zoom); ?>,
                        mapTypeId: google.maps.MapTypeId.<?php echo esc_js($wpgmap_map_type); ?>,
                        scrollwheel: <?php echo esc_js(get_option('_wgm_disable_mouse_wheel_zoom')) === 'Y' ? 'false' : 'true'; ?>,
                        zoomControl: <?php echo esc_js(get_option('_wgm_disable_zoom_control')) === 'Y' ? 'false' : 'true'; ?>,
                        mapTypeControl: <?php echo esc_js(get_option('_wgm_disable_map_type_control')) === 'Y' ? 'false' : 'true'; ?>,
                        streetViewControl: <?php echo esc_js(get_option('_wgm_disable_street_view')) === 'Y' ? 'false' : 'true'; ?>,
                        fullscreenControl: <?php echo esc_js(get_option('_wgm_disable_full_screen_control')) === 'Y' ? 'false' : 'true'; ?>,
                        draggable: <?php echo esc_js(get_option('_wgm_disable_mouse_dragging')) === 'Y' ? 'false' : 'true'; ?>,
                        disableDoubleClickZoom: <?php echo esc_js(get_option('_wgm_disable_mouse_double_click_zooming')) === 'Y' ? 'true' : 'false'; ?>,
                        panControl: <?php echo esc_js(get_option('_wgm_disable_pan_control')) === 'Y' ? 'false' : 'true'; ?>
                    });
                    var wgm_theme_json = <?php echo !empty($wgm_theme_json) ? json_encode($wgm_theme_json) : 'null'; ?>;
                    if (wgm_theme_json && wgm_theme_json.length > 0) {
                        try {
                            wgm_map.setOptions({ styles: JSON.parse(wgm_theme_json) });
                        } catch (e) {
                            console.error('Invalid map theme JSON:', e);
                        }
                    }
                    // To view directions form and data
                    <?php if ($wpgmap_enable_direction && _wgm_is_premium()) { ?>
                        var wgm_directionsDisplay_<?php echo esc_attr($count); ?> = new google.maps.DirectionsRenderer();
                        wgm_directionsDisplay_<?php echo esc_attr($count); ?>.setMap(wgm_map);
                        wgm_directionsDisplay_<?php echo esc_attr($count); ?>.setPanel(document.getElementById("wp_gmap_directions_<?php echo esc_attr($count); ?>"));

                        var wgm_get_direction_btn_<?php echo esc_attr($count); ?> = document.getElementById('wp_gmap_submit_<?php echo esc_attr($count); ?>');
                        wgm_get_direction_btn_<?php echo esc_attr($count); ?>.addEventListener('click', function () {
                            var wgm_selectedMode_<?php echo esc_attr($count); ?> = document.getElementById("srm_gmap_mode_<?php echo esc_attr($count); ?>").value,
                                wgm_dirction_start_<?php echo esc_attr($count); ?> = document.getElementById("srm_gmap_from_<?php echo esc_attr($count); ?>").value,
                                wgm_direction_end_<?php echo esc_attr($count); ?> = document.getElementById("srm_gmap_to_<?php echo esc_attr($count); ?>").value;

                            if (wgm_dirction_start_<?php echo esc_attr($count); ?> === '' || wgm_direction_end_<?php echo esc_attr($count); ?> === '') {
                                // cannot calculate route
                                document.getElementById("wp_gmap_results_<?php echo esc_attr($count); ?>").style.display = 'none';
                                return false;
                            } else {

                                document.getElementById('wp_gmap_loading_<?php echo esc_attr($count); ?>').style.display = 'block';

                                var wgm_direction_request_<?php echo esc_attr($count); ?> = {
                                    origin: wgm_dirction_start_<?php echo esc_attr($count); ?>,
                                    destination: wgm_direction_end_<?php echo esc_attr($count); ?>,
                                    travelMode: google.maps.DirectionsTravelMode[wgm_selectedMode_<?php echo esc_attr($count); ?>],
                                    unitSystem: <?php echo esc_js(get_option('_wgm_distance_unit', 'km')) == 'km' ? 'google.maps.UnitSystem.METRIC' : 'google.maps.UnitSystem.IMPERIAL' ?>
                                };
                                var wgm_directionsService_<?php echo esc_attr($count); ?> = new google.maps.DirectionsService();
                                wgm_directionsService_<?php echo esc_attr($count); ?>.route(wgm_direction_request_<?php echo esc_attr($count); ?>, function (response, status) {
                                    document.getElementById('wp_gmap_loading_<?php echo esc_attr($count); ?>').style.display = 'none';
                                    if (status === google.maps.DirectionsStatus.OK) {
                                        wgm_directionsDisplay_<?php echo esc_attr($count); ?>.setDirections(response);
                                        document.getElementById("wp_gmap_results_<?php echo esc_attr($count); ?>").style.display = 'block';
                                    } else {
                                        document.getElementById("wp_gmap_results_<?php echo esc_attr($count); ?>").style.display = 'none';
                                    }
                                });

                            }
                        });
                    <?php } ?>
                    var wgm_data_<?php echo esc_attr($count); ?> = {
                        'action': 'wpgmapembed_p_get_markers_by_map_id',
                        _wgm_p_nonce: '<?php echo esc_js(wp_create_nonce('wgm_marker_rander')); ?>',
                        'data': {
                            map_id: '<?php echo intval($wgm_map_id); ?>'
                        }
                    };
                    var wgm_ajaxurl_<?php echo esc_attr($count); ?> = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>'
                    jQuery.post(wgm_ajaxurl_<?php echo esc_attr($count); ?>, wgm_data_<?php echo esc_attr($count); ?>, function (response) {
                        response = JSON.parse(response);
                        if (response.markers.length === 1) {
                            var wgm_marker_to_<?php echo esc_attr($count); ?> = response.markers[0].marker_desc;
                            jQuery('#srm_gmap_to_<?php echo esc_attr($count); ?>').val(wgm_marker_to_<?php echo esc_attr($count); ?>.replace(/(<([^>]+)>)/gi, ""));
                        }
                        var wgm_default_marker_icon_<?php echo esc_attr($count); ?> = 'https://maps.gstatic.com/mapfiles/api-3/images/spotlight-poi2.png';
                        if (response.markers.length > 0) {
                            var custom_markers = [];
                            var custom_marker_infowindows = [];
                            response.markers.forEach(function (wgm_marker, i) {
                                var wgm_marker_lat_lng_<?php echo esc_attr($count); ?> = wgm_marker.lat_lng.split(',');
                                wgm_custom_marker_<?php echo esc_attr($count); ?> = new google.maps.Marker({
                                    position: new google.maps.LatLng(wgm_marker_lat_lng_<?php echo esc_attr($count); ?>[0], wgm_marker_lat_lng_<?php echo esc_attr($count); ?>[1]),
                                    title: wgm_marker.marker_name,
                                    animation: google.maps.Animation.DROP,
                                    icon: (wgm_marker.icon === '') ? wgm_default_marker_icon_<?php echo esc_attr($count); ?> : wgm_marker.icon
                                });
                                custom_markers[i] = wgm_custom_marker_<?php echo esc_attr($count); ?>;
                                wgm_custom_marker_<?php echo esc_attr($count); ?>.setMap(wgm_map);
                                var wgm_marker_name_<?php echo esc_attr($count); ?> = (wgm_marker.marker_name !== null) ? ('<span class="info_content_title" style="font-size:18px;font-weight: bold;font-family: Arial;">'
                                    + wgm_marker.marker_name +
                                    '</span><br/>') : '';
                                custom_marker_infowindow = new google.maps.InfoWindow({
                                    content: wgm_marker_name_<?php echo esc_attr($count); ?> + wgm_marker.marker_desc
                                });
                                custom_marker_infowindows[i] = custom_marker_infowindow;
                                if (wgm_marker.show_desc_by_default === '1') {
                                    custom_marker_infowindow.open({
                                        anchor: wgm_custom_marker_<?php echo esc_attr($count); ?>,
                                        shouldFocus: false
                                    });
                                } else {
                                    google.maps.event.addListener(wgm_custom_marker_<?php echo esc_attr($count); ?>, 'click', function () {
                                        custom_marker_infowindows[i].open({
                                            anchor: custom_markers[i],
                                            shouldFocus: false
                                        });
                                    });
                                }
                                if (wgm_marker.have_marker_link === '1') {
                                    google.maps.event.addListener(wgm_custom_marker_<?php echo esc_attr($count); ?>, 'click', function () {
                                        var wgm_target = '_self';
                                        if (wgm_marker.marker_link_new_tab === '1') {
                                            wgm_target = '_blank';
                                        }
                                        window.open(wgm_marker.marker_link, wgm_target);
                                    });
                                }
                            });
                        }
                    });
                });

            </script>

            <div id="srm_gmp_embed_<?php echo esc_attr($count); ?>"
                style="width:<?php echo esc_attr($wpgmap_map_width) . ' !important'; ?>;height:<?php echo esc_attr($wpgmap_map_height); ?>  !important; ">
            </div>
            <?php

            if ($wpgmap_enable_direction === '1' and _wgm_is_premium()) {
                ?>
                <style type="text/css">
                    .wp_gmap_direction_box {
                        width: 100%;
                        height: auto;
                    }

                    .fieldcontain {
                        margin: 8px 0;
                    }

                    #wp_gmap_submit {
                        background-color: #333;
                        border: 0;
                        color: #fff;
                        cursor: pointer;
                        font-family: "Noto Sans", sans-serif;
                        font-size: 12px;
                        font-weight: 700;
                        padding: 13px 24px;
                        text-transform: uppercase;
                    }

                    #wp_gmap_directions {
                        border: 1px #ddd solid;
                    }
                </style>
                <div class="wp_gmap_direction_box">
                    <div class="ui-bar-c ui-corner-all ui-shadow">
                        <div data-role="fieldcontain" class="fieldcontain">
                            <label
                                for="srm_gmap_from_<?php echo esc_attr($count); ?>"><?php esc_html_e('From', 'gmap-embed'); ?></label>
                            <input type="text" id="srm_gmap_from_<?php echo esc_attr($count); ?>" value="" style="width: 100%;" />
                        </div>
                        <div data-role="fieldcontain" class="fieldcontain">
                            <label for="srm_gmap_to_<?php echo esc_attr($count); ?>"><?php esc_html_e('To', 'gmap-embed'); ?></label>
                            <input type="text" id="srm_gmap_to_<?php echo esc_attr($count); ?>" value="" style="width: 100%" />
                        </div>
                        <div data-role="fieldcontain" class="fieldcontain">
                            <label for="srm_gmap_mode_<?php echo esc_attr($count); ?>"
                                class="select"><?php esc_html_e('Transportation method', 'gmap-embed'); ?>:</label>
                            <select name="select_choice_<?php echo esc_attr($count); ?>"
                                id="srm_gmap_mode_<?php echo esc_attr($count); ?>" style="padding: 5px;width: 100%;">
                                <option value="DRIVING"><?php esc_html_e('Driving', 'gmap-embed'); ?></option>
                                <option value="WALKING"><?php esc_html_e('Walking', 'gmap-embed'); ?></option>
                                <option value="BICYCLING"><?php esc_html_e('Bicycling', 'gmap-embed'); ?></option>
                                <option value="TRANSIT"><?php esc_html_e('Transit', 'gmap-embed'); ?></option>
                            </select>
                        </div>
                        <button type="button" data-icon="search" data-role="button" href="#" style="padding:8px;"
                            id="wp_gmap_submit_<?php echo esc_attr($count); ?>"><?php esc_html_e('Get Directions', 'gmap-embed'); ?>
                        </button>
                        <span id="wp_gmap_loading_<?php echo esc_attr($count); ?>"
                            style="display: none;"><?php esc_html_e('Loading', 'gmap-embed'); ?>...</span>
                    </div>

                    <!-- Directions will be listed here-->
                    <div id="wp_gmap_results_<?php echo esc_attr($count); ?>"
                        style="display:none;max-height: 300px;overflow-y: scroll;">
                        <div id="wp_gmap_directions_<?php echo esc_attr($count); ?>"></div>
                    </div>

                </div>

                <?php
                if (esc_html(get_option('_wgm_enable_direction_form_auto_complete')) == 'Y') { ?>
                    <script type="text/javascript">
                        jQuery(document).ready(function ($) {
                            new google.maps.places.Autocomplete(
                                (document.getElementById("srm_gmap_from_<?php echo esc_attr($count); ?>"))
                            );
                            new google.maps.places.Autocomplete(
                                (document.getElementById("srm_gmap_to_<?php echo esc_attr($count); ?>"))
                            );
                        });
                    </script>
                    <?php
                }
            }
        } else {
            if (is_user_logged_in() && current_user_can('administrator')) {
                echo "<span style='color:darkred;'>Shortcode not defined, please check WP Google Map plugin in WordPress admin panel(sidebar). This message only visible to Administrator</span>";
            }
        }
        ?>
        <?php
        return ob_get_clean();
    }
}

// ******* Defining Shortcode for WP Google Map
add_shortcode('gmap-embed', 'srm_gmap_embed_shortcode');
