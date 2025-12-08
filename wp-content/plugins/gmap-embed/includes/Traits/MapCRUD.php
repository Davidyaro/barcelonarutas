<?php

namespace WGMSRM\Traits;

use WP_Query;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Trait MapCRUD: Map CRUD operation doing here
 */
trait MapCRUD
{

	/**
	 * Get all maps for datatable ajax request
	 *
	 * @since 1.7.5
	 */
	public function wgm_get_all_maps()
	{
		if (!current_user_can($this->capability)) {
			echo wp_json_encode(
				array(
					'responseCode' => 0,
					'message' => esc_html__('Unauthorized access tried.', 'gmap-embed'),
				)
			);
			wp_die();
		}

		$args = array(
			'post_type' => 'wpgmapembed',
			'posts_per_page' => -1,
			'post_status' => 'draft',
		);

		$return_json = array();
		$maps_list = new WP_Query($args);
		while ($maps_list->have_posts()) {
			$maps_list->the_post();
			$title = esc_html(get_post_meta(get_the_ID(), 'wpgmap_title', true));
			$type = esc_html(get_post_meta(get_the_ID(), 'wpgmap_map_type', true));
			$width = esc_html(get_post_meta(get_the_ID(), 'wpgmap_map_width', true));
			$height = esc_html(get_post_meta(get_the_ID(), 'wpgmap_map_height', true));
			$shortcode = '<input class="wpgmap-shortcode regular-text" style="width:100%!important;" type="text" value="' . esc_attr('[gmap-embed id=&quot;' . get_the_ID() . '&quot;]') . '"
                                                       onclick="this.select()"/>';
			$action = '<button class="button media-button button-primary button-small wpgmap-copy-to-clipboard" data-id="' . esc_attr(get_the_ID()) . '" style="margin-right: 5px;"><i class="fas fa-copy"></i></button>'
				. '<a href="?page=wpgmapembed&tag=edit&id=' . esc_attr(get_the_ID()) . '&wgm_map_create_nonce=' . wp_create_nonce('wgm_create_map') . '" class="button media-button button-primary button-small wpgmap-edit" data-id="' . esc_attr(get_the_ID()) . '"><i class="fas fa-edit"></i>
                                                ' . esc_html__('Edit', 'gmap-embed') . '
                                            </a>&nbsp;<span type="button"
                                                    class="button media-button button-small  wgm_wpgmap_delete" data-id="' . esc_attr(get_the_ID()) . '" style="background-color: #aa2828;color: white;opacity:0.7;"><i class="fas fa-trash"></i> ' . esc_html__('Delete', 'gmap-embed') . '
                                            </span>';
			$row = array(
				'id' => get_the_ID(),
				'title' => $title,
				'map_type' => $type,
				'width' => $width,
				'height' => $height,
				'shortcode' => $shortcode,
				'action' => $action,
			);
			$return_json[] = $row;
		}

		echo wp_json_encode(array('data' => $return_json));
		wp_die();
	}

	/**
	 * To save New Map Data
	 */
	public function save_wpgmapembed_data()
	{
		// Nonce verification
		if (
			!isset($_POST['wgm_map_create_nonce']) ||
			!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wgm_map_create_nonce'])), 'wgm_create_map')
		) {
			echo wp_json_encode(
				array(
					'responseCode' => 0,
					'message' => esc_html__('Invalid request. Please reload and try again.', 'gmap-embed'),
				)
			);
			wp_die();
		}

		if (!current_user_can($this->capability)) {
			echo wp_json_encode(
				array(
					'responseCode' => 0,
					'message' => esc_html__('Unauthorized access tried.', 'gmap-embed'),
				)
			);
			wp_die();
		}

		$error = '';
		// Getting ajax fields value
		$meta_data = array(
			'wpgmap_title' => isset($_POST['map_data']['wpgmap_title']) ? sanitize_text_field(wp_strip_all_tags(wp_unslash($_POST['map_data']['wpgmap_title']))) : '',
			'wpgmap_heading_class' => isset($_POST['map_data']['wpgmap_heading_class']) ? sanitize_html_class(wp_unslash($_POST['map_data']['wpgmap_heading_class'])) : '',
			'wpgmap_show_heading' => isset($_POST['map_data']['wpgmap_show_heading']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_show_heading'])) : '',
			'wpgmap_latlng' => isset($_POST['map_data']['wpgmap_latlng']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_latlng'])) : '',
			'wpgmap_map_zoom' => isset($_POST['map_data']['wpgmap_map_zoom']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_map_zoom'])) : '',
			'wpgmap_disable_zoom_scroll' => isset($_POST['map_data']['wpgmap_disable_zoom_scroll']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_disable_zoom_scroll'])) : '',
			'wpgmap_map_width' => isset($_POST['map_data']['wpgmap_map_width']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_map_width'])) : '',
			'wpgmap_map_height' => isset($_POST['map_data']['wpgmap_map_height']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_map_height'])) : '',
			'wpgmap_map_type' => isset($_POST['map_data']['wpgmap_map_type']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_map_type'])) : '',
			'wpgmap_show_infowindow' => isset($_POST['map_data']['wpgmap_show_infowindow']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_show_infowindow'])) : '',
			'wpgmap_enable_direction' => isset($_POST['map_data']['wpgmap_enable_direction']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_enable_direction'])) : '',
			'wpgmap_center_lat_lng' => isset($_POST['map_data']['wpgmap_center_lat_lng']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_center_lat_lng'])) : '',
			'wgm_theme_json' => isset($_POST['map_data']['wgm_theme_json']) ? sanitize_textarea_field(wp_unslash($_POST['map_data']['wgm_theme_json'])) : '',
		);
		$meta_data['wgm_theme_json'] = json_encode(json_decode(sanitize_textarea_field(wp_unslash($meta_data['wgm_theme_json']))));
		$action_type = isset($_POST['map_data']['action_type']) ? sanitize_text_field(wp_unslash($_POST['map_data']['action_type'])) : '';
		if ($meta_data['wpgmap_latlng'] === '') {
			$error = esc_html__('Please input Latitude and Longitude', 'gmap-embed');
		}
		if (strlen($error) > 0) {
			echo wp_json_encode(
				array(
					'responseCode' => 0,
					'message' => $error,
				)
			);
			wp_die();
		}

		$post_id = 0;
		if ($action_type === 'save') {
			// Saving post array
			$post_array = array(
				'post_type' => 'wpgmapembed',
			);
			$post_id = wp_insert_post($post_array);
		} elseif ($action_type === 'update') {
			$post_id = isset($_POST['map_data']['post_id']) ? intval(sanitize_text_field(wp_unslash($_POST['map_data']['post_id']))) : 0;
		}

		// Updating post meta
		foreach ($meta_data as $key => $value) {
			$this->wgm_update_post_meta($post_id, $key, $value);
		}
		$return_array = array(
			'responseCode' => 1,
			'post_id' => intval($post_id),
		);
		if ($action_type === 'save') {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Required for updating custom table
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->prefix}wgm_markers SET map_id = %d WHERE map_id = %d",
					intval($post_id),
					0
				)
			);
			$return_array['message'] = esc_html__('Map created Successfully.', 'gmap-embed');
		} elseif ($action_type === 'update') {
			$return_array['message'] = esc_html__('Map updated Successfully.', 'gmap-embed');
		}
		echo wp_json_encode($return_array);
		wp_die();
	}

	/**
	 * Classic editor: Loading popup content on WP Google Map click
	 */
	public function load_popup_wpgmapembed_list()
	{
		if (!current_user_can($this->capability)) {
			echo wp_json_encode(
				array(
					'responseCode' => 0,
					'message' => esc_html__('Unauthorized access tried.', 'gmap-embed'),
				)
			);
			wp_die();
		}
		$content = '';
		$args = array(
			'post_type' => 'wpgmapembed',
			'posts_per_page' => -1,
			'post_status' => 'draft',
		);
		$maps_list = new WP_Query($args);

		while ($maps_list->have_posts()) {
			$maps_list->the_post();
			$title = get_post_meta(get_the_ID(), 'wpgmap_title', true);
			$content .= '<div class="wp-gmap-single">
                                        <div class="wp-gmap-single-left">
                                            <div class="wp-gmap-single-title">
                                                ' . esc_html($title) . '
                                            </div>
                                            <div class="wp-gmap-single-shortcode">
                                                <input class="wpgmap-shortcode regular-text" type="text" value="[gmap-embed id=&quot;' . esc_attr(get_the_ID()) . '&quot;]"
                                                       onclick="this.select()"/>
                                            </div>
                                        </div>
                                        <div class="wp-gmap-single-action">
                                            <button type="button"
                                                    class="button media-button button-primary button-large wpgmap-insert-shortcode">
                                                ' . esc_html__('Insert', 'gmap-embed') . '
                                            </button>                                            
                                        </div>
                                    </div>';
		}
		$allowed_html = [
			'a' => [],
			'br' => [],
			'em' => [],
			'strong' => [],
			'div' => [
				'class' => []
			],
			'button' => [
				'type' => [],
				'class' => []
			],
			'input' => [
				'class' => [],
				'value' => [],
				'name' => [],
				'onclick' => [],
				'type' => [],
			],
		];
		echo wp_kses(wp_unslash($content), $allowed_html);
		wp_die();
	}

	/**
	 * Get map data by mnap id
	 *
	 * @param string $gmap_id
	 *
	 * @return false|string
	 */
	public function get_wpgmapembed_data($gmap_id = 0)
	{
		if ($gmap_id == 0) {
			$gmap_id = 0;
			if (
				isset($_POST['_wgm_nonce']) &&
				wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wgm_nonce'])), '_wgm_nonce') &&
				isset($_POST['wpgmap_id'])
			) {
				$gmap_id = intval(sanitize_text_field(wp_unslash($_POST['wpgmap_id'])));
			}
		}

		$gmap_data = array(
			'wpgmap_id' => intval($gmap_id),
			'wpgmap_title' => esc_html(get_post_meta($gmap_id, 'wpgmap_title', true)),
			'wpgmap_heading_class' => esc_html(get_post_meta($gmap_id, 'wpgmap_heading_class', true)),
			'wpgmap_show_heading' => esc_html(get_post_meta($gmap_id, 'wpgmap_show_heading', true)),
			'wpgmap_latlng' => esc_html(get_post_meta($gmap_id, 'wpgmap_latlng', true)),
			'wpgmap_map_zoom' => esc_html(get_post_meta($gmap_id, 'wpgmap_map_zoom', true)),
			'wpgmap_disable_zoom_scroll' => esc_html(get_post_meta($gmap_id, 'wpgmap_disable_zoom_scroll', true)),
			'wpgmap_map_width' => esc_html(get_post_meta($gmap_id, 'wpgmap_map_width', true)),
			'wpgmap_map_height' => esc_html(get_post_meta($gmap_id, 'wpgmap_map_height', true)),
			'wpgmap_map_type' => esc_html(get_post_meta($gmap_id, 'wpgmap_map_type', true)),
			'wpgmap_show_infowindow' => esc_html(get_post_meta($gmap_id, 'wpgmap_show_infowindow', true)),
			'wpgmap_enable_direction' => esc_html(get_post_meta($gmap_id, 'wpgmap_enable_direction', true)),
			'wgm_theme_json' => wp_kses_data(get_post_meta($gmap_id, 'wgm_theme_json', true)),
			'wpgmap_center_lat_lng' => esc_html(get_center_lat_lng_by_map_id($gmap_id)),
		);
		$gmap_data['wgm_theme_json'] = strlen($gmap_data['wgm_theme_json']) == 0 ? '[]' : wp_kses_data($gmap_data['wgm_theme_json']);
		return wp_json_encode($gmap_data);
	}

	/**
	 * Remove map including post meta by map id
	 */
	public function remove_wpgmapembed_data()
	{
		if (!current_user_can($this->capability)) {
			$return_array = array(
				'responseCode' => 0,
				'message' => esc_html__('Unauthorized access tried.', 'gmap-embed'),
			);
			echo wp_json_encode($return_array);
			wp_die();
		}

		$meta_data = array(
			'wpgmap_title',
			'wpgmap_heading_class',
			'wpgmap_show_heading',
			'wpgmap_latlng',
			'wpgmap_map_zoom',
			'wpgmap_disable_zoom_scroll',
			'wpgmap_map_width',
			'wpgmap_map_height',
			'wpgmap_map_type',
			'wpgmap_show_infowindow',
			'wpgmap_enable_direction',
		);

		$post_id = isset($_POST['post_id']) ? intval(sanitize_text_field(wp_unslash($_POST['post_id']))) : 0;
		wp_delete_post($post_id);
		foreach ($meta_data as $field_name) {
			delete_post_meta($post_id, $field_name);
		}
		$return_array = array(
			'responseCode' => 1,
			'message' => esc_html__('Deleted Successfully.', 'gmap-embed'),
		);
		echo wp_json_encode($return_array);
		wp_die();
	}
	/**
	 * Create new map with default map and marker data.
	 *
	 * Sanitizes and escapes all data before saving to the database.
	 * Uses wp_insert_post for map creation and $wpdb->insert for marker creation.
	 * All values are sanitized and escaped according to WordPress coding standards.
	 *
	 * @return int $map_id The ID of the newly created map.
	 */
	public function initiate_new_map()
	{
		// Set default meta data for new map
		$meta_data = array(
			'wpgmap_title' => 'New Map',
			'wpgmap_heading_class' => '',
			'wpgmap_show_heading' => 0,
			'wpgmap_map_zoom' => 4,
			'wpgmap_map_width' => '100%',
			'wpgmap_map_height' => '300px',
			'wpgmap_map_type' => 'ROADMAP',
			'wpgmap_show_infowindow' => 0,
			'wpgmap_enable_direction' => 0,
			'wpgmap_center_lat_lng' => '40.779220392557676,-87.3700530411561',
			'wpgmap_latlng' => '40.779220392557676,-87.3700530411561',
			'wgm_theme_json' => '[]',
		);

		// Sanitize and encode theme JSON
		$meta_data['wgm_theme_json'] = wp_json_encode(json_decode(sanitize_textarea_field($meta_data['wgm_theme_json'])));

		// Prepare post array
		$post_array = array(
			'post_type' => 'wpgmapembed',
			'post_status' => 'draft',
			'post_title' => sanitize_text_field($meta_data['wpgmap_title']),
		);

		// Insert new map post
		$map_id = wp_insert_post($post_array);

		// Ensure map_id is valid
		$map_id = intval($map_id);

		// Update post meta with sanitized values
		foreach ($meta_data as $key => $value) {
			$this->wgm_update_post_meta($map_id, sanitize_key($key), sanitize_text_field($value));
		}

		// Prepare demo marker data with sanitization
		$map_marker_data = array(
			'map_id' => $map_id,
			'marker_name' => sanitize_text_field('Chicago'),
			'marker_desc' => wp_kses_post(''),
			'icon' => esc_url_raw('https://maps.gstatic.com/mapfiles/api-3/images/spotlight-poi2.png'),
			'address' => sanitize_text_field(''),
			'lat_lng' => sanitize_text_field('40.779220392557676,-87.3700530411561'),
			'have_marker_link' => 0,
			'marker_link' => esc_url_raw(''),
			'marker_link_new_tab' => 0,
			'show_desc_by_default' => 1,
		);

		// Merge with marker defaults
		$defaults = $this->get_marker_default_values();
		$wp_gmap_marker_data = wp_parse_args($map_marker_data, $defaults);

		// Insert marker into custom table
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'wgm_markers',
			array_map('sanitize_text_field', $wp_gmap_marker_data)
		);

		return $map_id;
	}
}
