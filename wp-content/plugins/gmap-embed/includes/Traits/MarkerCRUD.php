<?php

namespace WGMSRM\Traits;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Trait MarkerCRUD: Map CRUD operation doing here
 */
trait MarkerCRUD
{


	/**
	 * Get Marker default values
	 *
	 * @return array
	 */
	public function get_marker_default_values()
	{
		return array(
			'map_id' => 0,
			'marker_name' => null,
			'marker_desc' => null,
			'icon' => null,
			'address' => null,
			'lat_lng' => null,
			'have_marker_link' => 0,
			'marker_link' => null,
			'marker_link_new_tab' => 0,
			'show_desc_by_default' => 0,
			'created_at' => current_time('mysql'),
			'created_by' => get_current_user_id(),
			'updated_at' => current_time('mysql'),
			'updated_by' => get_current_user_id(),
		);
	}

	/**
	 * To save new map marker
	 */
	public function save_map_marker()
	{
		// Nonce verification
		if (
			!isset($_POST['wgm_marker_nonce']) ||
			!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wgm_marker_nonce'])), 'wgm_create_marker')
		) {
			$return_array = array(
				'responseCode' => 0,
				'message' => esc_html__('Invalid request. Please reload and try again.', 'gmap-embed'),
			);
			echo wp_json_encode($return_array);
			wp_die();
		}

		if (!current_user_can($this->capability)) {
			$return_array = array(
				'responseCode' => 0,
				'message' => esc_html__('Unauthorized access tried.', 'gmap-embed'),
			);
			echo wp_json_encode($return_array);
			wp_die();
		}



		global $wpdb;

		$data = $_POST['map_markers_data'];
		// if (isset($_POST['map_markers_data']) && is_array($_POST['map_markers_data'])) {
		// 	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		// 	$raw_data = $_POST['map_markers_data'];
		// 	$unslashed_data = is_array($raw_data) ? wp_unslash($raw_data) : array();
		// 	foreach ($unslashed_data as $key => $value) {
		// 		$data[$key] = is_array($value)
		// 			? array_map('sanitize_text_field', $value)
		// 			: sanitize_text_field($value);
		// 	}
		// }

		$map_id = isset($data['wpgmap_map_id']) ? intval(sanitize_text_field(wp_unslash($data['wpgmap_map_id']))) : 0;
		$error = '';
		$map_marker_data = array(
			'map_id' => $map_id,
			'marker_name' => isset($data['wpgmap_marker_name']) && strlen(sanitize_text_field(wp_unslash($data['wpgmap_marker_name']))) === 0 ? null : (isset($data['wpgmap_marker_name']) ? sanitize_text_field(wp_unslash($data['wpgmap_marker_name'])) : null),
			'marker_desc' => isset($data['wpgmap_marker_desc']) ? wp_kses_post(wp_unslash($data['wpgmap_marker_desc'])) : '',
			'icon' => isset($data['wpgmap_marker_icon']) ? esc_url_raw(wp_unslash($data['wpgmap_marker_icon'])) : '',
			'address' => isset($data['wpgmap_marker_address']) ? sanitize_text_field(wp_unslash($data['wpgmap_marker_address'])) : '',
			'lat_lng' => isset($data['wpgmap_marker_lat_lng']) ? sanitize_text_field(wp_unslash($data['wpgmap_marker_lat_lng'])) : '',
			'have_marker_link' => isset($data['wpgmap_have_marker_link']) ? intval($data['wpgmap_have_marker_link']) : 0,
			'marker_link' => isset($data['wpgmap_marker_link']) ? esc_url_raw(wp_unslash($data['wpgmap_marker_link'])) : '',
			'marker_link_new_tab' => isset($data['wpgmap_marker_link_new_tab']) ? intval($data['wpgmap_marker_link_new_tab']) : 0,
			'show_desc_by_default' => isset($data['wpgmap_marker_infowindow_show']) ? intval($data['wpgmap_marker_infowindow_show']) : 0,
		);
		if (empty($map_marker_data['lat_lng'])) {
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

		if (!_wgm_is_premium()) {
			$no_of_marker_already_have = $this->get_no_of_markers_by_map_id(intval($map_id));
			if ($no_of_marker_already_have > 0) {
				echo wp_json_encode(
					array(
						'responseCode' => 0,
						'message' => esc_html__('Please upgrade to premium version to create unlimited markers', 'gmap-embed'),
					)
				);
				wp_die();
			}
		}

		$defaults = $this->get_marker_default_values();
		$wp_gmap_marker_data = wp_parse_args($map_marker_data, $defaults);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->insert(
			$wpdb->prefix . 'wgm_markers',
			$wp_gmap_marker_data,
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%d',
				'%d',
				'%s',
				'%d',
				'%s',
				'%d',
			)
		); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

		$return_array = array(
			'responseCode' => 1,
			'marker_id' => intval($wpdb->insert_id),
		);
		$return_array['message'] = esc_html__('Marker Saved Successfully.', 'gmap-embed');
		echo wp_json_encode($return_array);
		wp_die();
	}

	/**
	 * To update existing marker information
	 */

	public function update_map_marker()
	{
		// Nonce verification
		if (
			!isset($_POST['wgm_marker_nonce']) ||
			!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wgm_marker_nonce'])), 'wgm_create_marker')
		) {
			$return_array = array(
				'responseCode' => 0,
				'message' => esc_html__('Invalid request. Please reload and try again.', 'gmap-embed'),
			);
			echo wp_json_encode($return_array);
			wp_die();
		}

		if (!current_user_can($this->capability)) {
			$return_array = array(
				'responseCode' => 0,
				'message' => esc_html__('Unauthorized access tried.', 'gmap-embed'),
			);
			echo wp_json_encode($return_array);
			wp_die();
		}


		global $wpdb;
		$error = '';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized	
		$data = isset($_POST['map_markers_data']) && is_array($_POST['map_markers_data']) ? wp_unslash($_POST['map_markers_data']) : [];
		$marker_id = isset($data['wpgmap_marker_id']) ? intval(sanitize_text_field(wp_unslash($data['wpgmap_marker_id']))) : 0;
		$map_id = isset($data['wpgmap_map_id']) ? intval(sanitize_text_field(wp_unslash($data['wpgmap_map_id']))) : 0;
		$map_marker_data = array(
			'map_id' => $map_id,
			'marker_name' => isset($data['wpgmap_marker_name']) && strlen(sanitize_text_field(wp_unslash($data['wpgmap_marker_name']))) === 0 ? null : (isset($data['wpgmap_marker_name']) ? sanitize_text_field(wp_unslash($data['wpgmap_marker_name'])) : null),
			'marker_desc' => isset($data['wpgmap_marker_desc']) ? wp_kses_post(wp_unslash($data['wpgmap_marker_desc'])) : '',
			'icon' => isset($data['wpgmap_marker_icon']) ? esc_url_raw(wp_unslash($data['wpgmap_marker_icon'])) : '',
			'address' => isset($data['wpgmap_marker_address']) ? sanitize_text_field(wp_unslash($data['wpgmap_marker_address'])) : '',
			'lat_lng' => isset($data['wpgmap_marker_lat_lng']) ? sanitize_text_field(wp_unslash($data['wpgmap_marker_lat_lng'])) : '',
			'have_marker_link' => isset($data['wpgmap_have_marker_link']) ? intval($data['wpgmap_have_marker_link']) : 0,
			'marker_link' => isset($data['wpgmap_marker_link']) ? esc_url_raw(wp_unslash($data['wpgmap_marker_link'])) : '',
			'marker_link_new_tab' => isset($data['wpgmap_marker_link_new_tab']) ? intval($data['wpgmap_marker_link_new_tab']) : 0,
			'show_desc_by_default' => isset($data['wpgmap_marker_infowindow_show']) ? intval($data['wpgmap_marker_infowindow_show']) : 0,
		);
		if (empty($map_marker_data['lat_lng'])) {
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

		$defaults = $this->get_marker_default_values();
		$wp_gmap_marker_data = wp_parse_args($map_marker_data, $defaults);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
			$wpdb->prefix . 'wgm_markers',
			$wp_gmap_marker_data,
			array('id' => intval($marker_id)),
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%d',
				'%d',
				'%s',
				'%d',
				'%s',
				'%d',
			),
			array('%d')
		); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

		$return_array = array(
			'responseCode' => 1,
			'marker_id' => intval($marker_id),
		);
		$return_array['message'] = esc_html__('Updated Successfully.', 'gmap-embed');
		echo wp_json_encode($return_array);
		wp_die();
	}

	/**
	 * Get all marker icons/pins
	 */
	public function get_marker_icons()
	{
		// Nonce verification
		if (
			!isset($_GET['wgm_marker_nonce']) ||
			!wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['wgm_marker_nonce'])), 'wgm_create_marker')
		) {
			$return_array = array(
				'responseCode' => 0,
				'message' => esc_html__('Invalid request. Please reload and try again.', 'gmap-embed'),
			);
			echo wp_json_encode($return_array);
			wp_die();
		}

		if (!current_user_can($this->capability)) {
			$return_array = array(
				'responseCode' => 0,
				'message' => esc_html__('Unauthorized access tried.', 'gmap-embed'),
			);
			echo wp_json_encode($return_array);
			wp_die();
		}

		ob_start();
		require_once WGM_PLUGIN_PATH . 'admin/includes/markers-icons.php';
		$output = ob_get_clean();
		// Allow onclick attribute for wpgmapChangeCurrentMarkerIcon in <img> tags
		$allowed_html = array(
			'img' => array(
				'src' => array(),
				'alt' => array(),
				'class' => array(),
				'style' => array(),
				'onclick' => array(),
				'width' => array(),
				'height' => array(),
				'id' => array(),
				'data-*' => array(),
			),
			'div' => array(
				'class' => array(),
				'id' => array(),
				'style' => array(),
			),
			'span' => array(
				'class' => array(),
				'id' => array(),
				'style' => array(),
			),
			'a' => array(
				'href' => array(),
				'class' => array(),
				'id' => array(),
				'style' => array(),
				'target' => array(),
				'rel' => array(),
			),
			// Add more tags/attributes as needed
		);
		echo wp_kses($output, $allowed_html);
		wp_die();
	}

	/**
	 * Save Marker Icon
	 */
	public function save_marker_icon()
	{
		// Nonce verification
		if (
			!isset($_POST['wgm_marker_nonce']) ||
			!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wgm_marker_nonce'])), 'wgm_create_marker')
		) {
			$return_array = array(
				'responseCode' => 0,
				'message' => esc_html__('Invalid request. Please reload and try again.', 'gmap-embed'),
			);
			echo wp_json_encode($return_array);
			wp_die();
		}

		if (!current_user_can($this->capability)) {
			$return_array = array(
				'responseCode' => 0,
				'message' => esc_html__('Unauthorized access tried.', 'gmap-embed'),
			);
			echo wp_json_encode($return_array);
			wp_die();
		}


		global $wpdb;
		$error = '';
		$icon_url = isset($_POST['data']['icon_url']) ? esc_url_raw(wp_unslash($_POST['data']['icon_url'])) : '';
		$map_icon_data = array(
			'type' => 'uploaded_marker_icon',
			'title' => '',
			'desc' => '',
			'file_name' => $icon_url,
		);

		$is_marker_icon_already_exist = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}wgm_icons WHERE file_name=%s", $icon_url)); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		if ($is_marker_icon_already_exist == 0) {
			$defaults = array(
				'file_name' => '',
			);
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wp_gmap_marker_icon = wp_parse_args($map_icon_data, $defaults);
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->insert(
				$wpdb->prefix . 'wgm_icons',
				$wp_gmap_marker_icon,
				array(
					'%s',
					'%s',
					'%s',
					'%s',
				)
			); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		}

		$return_array = array(
			'responseCode' => 1,
			'icon_url' => esc_url($icon_url),
		);
		$return_array['message'] = esc_html__('Updated Successfully.', 'gmap-embed');
		echo wp_json_encode($return_array);
		wp_die();
	}

	/**
	 * Get no of markers by map id
	 *
	 * @param $map_id int
	 *
	 * @retun int
	 */
	public function get_no_of_markers_by_map_id($map_id = 0)
	{
		global $wpdb;
		$map_id = intval($map_id);
		return $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}wgm_markers WHERE map_id=%d", $map_id)); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Get all markers by map id
	 */
	public function get_markers_by_map_id()
	{
		// Nonce verification
		if (
			!isset($_POST['wgm_marker_nonce']) ||
			!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wgm_marker_nonce'])), 'wgm_create_marker')
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


		global $wpdb;
		$map_id = isset($_POST['data']['map_id']) ? intval(sanitize_text_field(wp_unslash($_POST['data']['map_id']))) : 0;
		$filtered_map_markers = array();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct query is required for custom table.
		$map_markers = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wgm_markers WHERE map_id=%d", $map_id));
		if (count($map_markers) > 0) {
			foreach ($map_markers as $key => $map_marker) {
				$map_marker->marker_desc = wp_unslash($map_marker->marker_desc);
				$filtered_map_markers[$key] = $map_marker;
			}
		}
		$return_array = array(
			'responseCode' => 1,
			'markers' => $filtered_map_markers,
		);
		$return_array['message'] = esc_html__('Markers fetched successfully.', 'gmap-embed');
		echo wp_json_encode($return_array);
		wp_die();
	}

	/**
	 * Public Get all markers by map id
	 */
	public function p_get_markers_by_map_id()
	{

		global $wpdb;
		// Nonce verification
		if (
			!isset($_POST['_wgm_p_nonce']) ||
			!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wgm_p_nonce'])), 'wgm_marker_rander')
		) {
			$return_array = array(
				'responseCode' => 0,
				'message' => esc_html__('Invalid request. Please reload and try again.', 'gmap-embed'),
			);
			echo wp_json_encode($return_array);
			wp_die();
		}

		$map_id = isset($_POST['data']['map_id']) ? intval(sanitize_text_field(wp_unslash($_POST['data']['map_id']))) : 0;
		$filtered_map_markers = array();
		$map_markers = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wgm_markers WHERE map_id=%d", $map_id)); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		if (count($map_markers) > 0) {
			foreach ($map_markers as $key => $map_marker) {
				$map_marker->marker_desc = wp_kses_post(wp_unslash($map_marker->marker_desc));
				$filtered_map_markers[$key] = $map_marker;
			}
		}
		$return_array = array(
			'responseCode' => 1,
			'markers' => $filtered_map_markers,
		);
		$return_array['message'] = esc_html__('Markers fetched successfully.', 'gmap-embed');
		echo wp_json_encode($return_array);
		wp_die();
	}

	/**
	 * Get markers by map id for datatable
	 */
	public function wgm_get_markers_by_map_id_for_dt()
	{

		// Nonce verification
		if (
			!isset($_GET['wgm_marker_nonce']) ||
			!wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['wgm_marker_nonce'])), 'wgm_create_marker')
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

		$map_id = isset($_GET['map_id']) ? intval(sanitize_text_field(wp_unslash($_GET['map_id']))) : 0;

		$return_json = array();
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct query is required for custom table.
		$wpgmap_markers = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wgm_markers WHERE map_id=%d", $map_id)); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		if (count($wpgmap_markers) > 0) {
			foreach ($wpgmap_markers as $marker_key => $wpgmap_marker) {
				$action = '<a href="" class="wpgmap_marker_edit button button-small"
                           map_marker_id="' . esc_attr($wpgmap_marker->id) . '"><i class="fas fa-edit"></i></a>
                        <a href="" class="wpgmap_marker_view button button-small"
                           map_marker_id="' . esc_attr($wpgmap_marker->id) . '"><i class="fas fa-eye"></i></a>
                        <a href="" class="wpgmap_marker_trash button button-small"
                           map_marker_id="' . esc_attr($wpgmap_marker->id) . '"><i class="fas fa-trash"></i></a>';
				$row = array(
					'id' => intval($wpgmap_marker->id),
					'marker_name' => esc_html($wpgmap_marker->marker_name),
					//phpscs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
					'icon' => '<img src="' . esc_url($wpgmap_marker->icon) . '" width="20">',
					'action' => $action,
				);
				$return_json[] = $row;
			}
		}
		echo wp_json_encode(array('data' => $return_json));
		wp_die();
	}

	/**
	 * Delete single marker
	 */
	public function delete_marker()
	{
		// Nonce verification
		if (
			!isset($_POST['wgm_marker_nonce']) ||
			!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wgm_marker_nonce'])), 'wgm_create_marker')
		) {
			$return_array = array(
				'responseCode' => 0,
				'message' => esc_html__('Invalid request. Please reload and try again.', 'gmap-embed'),
			);
			echo wp_json_encode($return_array);
			wp_die();
		}

		if (!current_user_can($this->capability)) {
			$return_array = array(
				'responseCode' => 0,
				'message' => esc_html__('Unauthorized access tried.', 'gmap-embed'),
			);
			echo wp_json_encode($return_array);
			wp_die();
		}


		$marker_id = isset($_POST['data']['marker_id']) ? intval(sanitize_text_field(wp_unslash($_POST['data']['marker_id']))) : 0;
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->delete(
			$wpdb->prefix . 'wgm_markers',
			array(
				'id' => $marker_id,
			),
			array(
				'%d',
			)
		); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Get marker single data by marker ID
	 */
	public function get_marker_data_by_marker_id()
	{
		// Nonce verification
		if (
			!isset($_POST['wgm_marker_nonce']) ||
			!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wgm_marker_nonce'])), 'wgm_create_marker')
		) {
			$return_array = array(
				'responseCode' => 0,
				'message' => esc_html__('Invalid request. Please reload and try again.', 'gmap-embed'),
			);
			echo wp_json_encode($return_array);
			wp_die();
		}

		if (!current_user_can($this->capability)) {
			$return_array = array(
				'responseCode' => 0,
				'message' => esc_html__('Unauthorized access tried.', 'gmap-embed'),
			);
			echo wp_json_encode($return_array);
			wp_die();
		}

		global $wpdb;
		$marker_id = 0;
		if (isset($_POST['data']['marker_id'])) {
			$marker_id = intval(sanitize_text_field(wp_unslash($_POST['data']['marker_id'])));
		}
		$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wgm_markers WHERE id=%d", intval($marker_id)), OBJECT); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		if ($result) {
			$result->marker_desc = wp_kses_post(wp_unslash($result->marker_desc));
		}
		echo wp_json_encode($result);
		wp_die();
	}
}
