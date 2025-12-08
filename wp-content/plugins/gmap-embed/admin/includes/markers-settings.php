<?php
if (
    isset($_GET['tag']) && $_GET['tag'] === 'edit'
) {
    if (
        !isset($_GET['wgm_map_create_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['wgm_map_create_nonce'])), 'wgm_create_map')
    ) {
        wp_die(esc_html__('Invalid request. Nonce verification failed.', 'gmap-embed'));
    }
}
$map_id = isset($_GET['id']) ? intval(sanitize_text_field(wp_unslash($_GET['id']))) : 0;
?>
<div style="text-align: right;margin-top:10px;" class="add_new_marker_btn_area">
    <button type="button" value="New Marker" class="button button-primary add_new_marker"
        style="margin-bottom: 10px;"><i class="dashicons dashicons-plus" style="margin: 5px 0 0 0;"></i>
        <?php esc_html_e('New Marker', 'gmap-embed'); ?>
    </button>
    <?php
    if (!_wgm_is_premium()) {
        ?>
        <sup class="wgm-pro-label" style="top: -4px; display: none;"><?php esc_html_e('Pro', 'gmap-embed'); ?></sup>
        <?php
    }
    ?>
</div>

<div class="wgm_gmap_marker_list" style="display: block" map_id="<?php echo esc_attr($map_id); ?>">
    <table id="wgm_gmap_marker_list" class="display" style="width:100%">
        <thead>
            <tr>
                <th><?php esc_html_e('ID', 'gmap-embed'); ?></th>
                <th><?php esc_html_e('Marker Name', 'gmap-embed'); ?></th>
                <th><?php esc_html_e('Icon', 'gmap-embed'); ?></th>
                <th><?php esc_html_e('Action', 'gmap-embed'); ?></th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <div class="wgm_marker_create_hints">
        <ul>
            <li><?php // translators: %1$s: New Marker button label, %2$s: Address, Zip code or Latitude,Longitude label.
            echo sprintf(esc_html__('Click on %1$s button and Search your desired location by %2$s.', 'gmap-embed'), '<b>' . esc_html__('New Marker', 'gmap-embed') . '</b>', '<b>' . esc_html__('Address, Zip code or Latitude,Longitude', 'gmap-embed') . '.</b>'); ?>
            </li>
            <li><b><?php esc_html_e('Right Click', 'gmap-embed'); ?></b>
                <?php esc_html_e('on Map to set marker location and set others options.', 'gmap-embed'); ?>
            </li>
            <li>
                <?php
                /*
                 * Translators: %1$s: 'Save Marker' (bolded), %2$s: 'Save Map' (bolded).
                 * Displays a message instructing the user to click on 'Save Marker' and not forget to click on 'Save Map'.
                 */
                echo sprintf(esc_html__('Click on %1$s, Don\'t forget to click on %2$s!', 'gmap-embed'), '<b>' . esc_html__('Save Marker', 'gmap-embed') . '</b>', '<b>' . esc_html__('Save Map', 'gmap-embed') . '</b>');
                ?>
            </li>
        </ul>
    </div>
</div>


<table class="wgm_gmap_properties add_new_marker_form" style="display: none;width:100%">

    <tr>
        <td>
            <label for="wpgmap_marker_name"><b><?php esc_html_e('Marker Title', 'gmap-embed'); ?>
                </b></label><br />
            <input id="wpgmap_marker_name" name="wpgmap_marker_name" type="text" class="regular-text">
        </td>
    </tr>

    <tr>
        <td>
            <label for="wpgmap_marker_desc"><b><?php esc_html_e('Description', 'gmap-embed'); ?></b></label><br />
            <?php
            echo (_wgm_is_premium() === false) ? '<button type="button" class="button wgm_enable_premium" style="opacity: .7;" data-notice="' .
                // translators: %s: Premium version URL.
                esc_html(sprintf(__('You need to upgrade to the <a target="_blank" href="%s">Premium</a> Version to add <b> Images in marker InfoWindow </b>.', 'gmap-embed'), esc_url('https://wpgooglemap.com/pricing?utm_source=admin_markers&utm_medium=admin_link&utm_campaign=marker_add_media'))) .
                '"><span class="dashicons dashicons-admin-media" style="line-height: 1.5;"></span> ' . esc_html__('Add Media', 'gmap-embed') . '</button><sup class="wgm-pro-label" style="top: -45px;display: block;width: 23px;left: 107px;">' . esc_html__('Pro', 'gmap-embed') . '</sup>' : '';
            wp_editor(
                '',
                'wpgmap_marker_desc',
                array(
                    'textarea_name' => 'wpgmap_marker_desc',
                    'textarea_rows' => '3',
                    'media_buttons' => _wgm_is_premium() === true,
                    'quicktags' => _wgm_is_premium() === true,
                )
            );
            ?>
        </td>
    </tr>

    <tr>
        <td style="padding-top: 10px;">
            <span style="float: left;">
                <b><?php esc_html_e('Marker Icon', 'gmap-embed'); ?></b> &nbsp;
            </span>
            <?php //phpscs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
            <img src="<?php echo esc_attr(plugin_dir_url(__FILE__) . '../assets/images/markers/default.png'); ?>"
                id="wpgmap_marker_icon_preview" style="float: left;max-width: 20px;">
            <?php
            $ajax_url = add_query_arg(
                array(
                    'action' => 'wpgmapembed_get_marker_icons',
                    'from' => 'create',
                    'wgm_marker_nonce' => wp_create_nonce('wgm_create_marker'),
                    'type' => 'image',
                    'TB_iframe' => false
                ),
                admin_url('admin-ajax.php')
            );
            ?>
            <button style="float: left;margin: 0 9px;" class="button"
                onclick="tb_show('<?php esc_html_e('Choose marker icon', 'gmap-embed'); ?>', '<?php echo esc_url($ajax_url); ?>')">
                <?php esc_html_e('Choose Icon', 'gmap-embed'); ?>
            </button>
            <button style="float: left" class="button"
                id="wpgmap_upload_marker_icon"><?php esc_html_e('Upload Icon', 'gmap-embed'); ?></button>
            <input type="hidden" name="wpgmap_marker_icon" id="wpgmap_marker_icon"
                value="<?php echo esc_attr(plugin_dir_url(__FILE__) . '../assets/images/markers/default.png'); ?>" />
        </td>
    </tr>

    <tr>
        <td>
            <label for="wpgmap_marker_address"><b><?php esc_html_e('Address', 'gmap-embed'); ?></b></label><br />
            <input id="wpgmap_marker_address" name="wpgmap_marker_address" type="text" class="regular-text">
        </td>
    </tr>

    <tr>
        <td>
            <label for="wpgmap_marker_lat_lng"><b>
                    <?php esc_html_e('Latitude,Longitude', 'gmap-embed'); ?><span
                        class="required-star">*</span></b></label><br />
            <input id="wpgmap_marker_lat_lng" name="wpgmap_marker_lat_lng" type="text" class="regular-text">
        </td>
    </tr>

    <tr>
        <td>
            <label for="wpgmap_marker_link"><?php esc_html_e('Has Marker Link?', 'gmap-embed'); ?></label>&nbsp;
            <select name="wpgmap_have_marker_link" id="wpgmap_have_marker_link">
                <option value="1"><?php esc_html_e('Yes', 'gmap-embed'); ?></option>
                <option value="0" selected="selected"><?php esc_html_e('No', 'gmap-embed'); ?></option>
            </select>
            <br />
            <div id="wpgmap_marker_link_area" style="display: none;">
                <input id="wpgmap_marker_link" name="wpgmap_marker_link"
                    placeholder="<?php esc_attr_e('Enter Marker link here', 'gmap-embed'); ?>" type="text"
                    class="regular-text" style="margin: 5px 0;">
                <br />
                <label>
                    <input type="checkbox" id="wpgmap_marker_link_new_tab" name="wpgmap_marker_link_new_tab"
                        class="alignleft" style="margin: 2px 5px 0px 0px;" />
                    <span class="alignleft"><?php esc_html_e('Open link in new window', 'gmap-embed'); ?></span>
                </label>
            </div>
        </td>
    </tr>
    <tr>
        <td>

            <label>
                <span
                    class="alignleft"><?php esc_html_e('Open marker description by default', 'gmap-embed'); ?></span>&nbsp;
                <select name="wpgmap_marker_infowindow_show" id="wpgmap_marker_infowindow_show">
                    <option value="1"><?php esc_html_e('Yes', 'gmap-embed'); ?></option>
                    <option value="0" selected="selected">
                        <?php esc_html_e('No, Open description on click', 'gmap-embed'); ?>
                    </option>
                </select>
            </label>
        </td>
    </tr>


    <?php $map_id = (isset($_GET['tag']) && sanitize_text_field(wp_unslash($_GET['tag'])) === 'edit') ? intval(sanitize_text_field(wp_unslash($_GET['id']))) : 0; ?>
    <tr>
        <td>
            <button class=" button button-primary button-large wgm_marker_cancel" type="button">
                <i class="dashicons dashicons-no-alt" style="line-height: 1.6;"></i>
                <b><?php esc_html_e('Cancel', 'gmap-embed'); ?></b>
            </button>
            <button class=" button button-primary button-large wpgmap_marker_add" type="button" markerid="0"
                mapid="<?php echo esc_attr($map_id); ?>">
                <i class="dashicons dashicons-location" style="line-height: 1.6;"></i>
                <b><?php esc_html_e('Save Marker', 'gmap-embed'); ?></b>
            </button>
            <span class="spinner alignleft"></span>
        </td>
    </tr>
</table>

<div class="gmap_embed_message_area alignleft wgm-col-full">
    <div id="marker_errors" style="color: red;"></div>
    <div id="marker_success" style="color: green;font-weight: bold;"></div>
</div>
