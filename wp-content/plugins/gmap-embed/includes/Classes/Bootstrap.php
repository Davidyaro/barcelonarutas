<?php

namespace WGMSRM\Classes;

use WGMSRM\Traits\ActionLinks;
use WGMSRM\Traits\ActivationHooks;
use WGMSRM\Traits\AdminInitActions;
use WGMSRM\Traits\AssetHandler;
use WGMSRM\Traits\CommonFunctions;
use WGMSRM\Traits\Filters;
use WGMSRM\Traits\InitActions;
use WGMSRM\Traits\MapCRUD;
use WGMSRM\Traits\MarkerCRUD;
use WGMSRM\Traits\MediaButtons;
use WGMSRM\Traits\Menu;
use WGMSRM\Traits\Notice;
use WGMSRM\Traits\PluginsLoadedActions;
use WGMSRM\Traits\Settings;
use WGMSRM\Traits\SetupWizard;

if (!defined('ABSPATH')) {
	exit;
}

class Bootstrap
{

	use Settings, MapCRUD, Notice, Menu, AssetHandler, CommonFunctions, ActionLinks, PluginsLoadedActions, ActivationHooks, InitActions, SetupWizard, Filters, MarkerCRUD, AdminInitActions, MediaButtons;

	private static $instance = null;
	private $plugin_name = 'WP Google Map';
	private $plugin_slug = 'gmap-embed';
	public $wpgmap_api_key = 'AIzaSyD79uz_fsapIldhWBl0NqYHHGBWkxlabro';
	private $capability = 'manage_options';

	public function __construct()
	{
		// Sanitize and validate capability option
		$cap = get_option('_wgm_minimum_role_for_map_edit', 'manage_options');
		$cap = is_string($cap) ? sanitize_text_field($cap) : 'manage_options';
		$this->capability = $cap;

		// Sanitize API key
		$api_key = get_option('wpgmap_api_key');
		$this->wpgmap_api_key = is_string($api_key) ? sanitize_text_field($api_key) : '';

		$this->register_hooks();
		$this->load_dependencies();
	}

	/**
	 * Generating instance
	 *
	 * @return Bootstrap|null
	 */
	public static function instance()
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register all hooks
	 */
	private function register_hooks()
	{
		add_action('init', array($this, 'do_init_actions'));
		add_action('plugins_loaded', array($this, 'wpgmap_do_after_plugins_loaded'));
		add_action('widgets_init', array($this, 'register_widget'));
		add_action('activated_plugin', array($this, 'wpgmap_do_after_activation'), 10, 2);
		add_action('wp_enqueue_scripts', array($this, 'gmap_front_enqueue_scripts'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_gmap_scripts'));
		add_action('admin_menu', array($this, 'gmap_create_menu'));
		add_action('admin_init', array($this, 'do_admin_init_actions'));
		add_action('admin_init', array($this, 'gmapsrm_settings'));
		add_action('admin_notices', array($this, 'gmap_embed_notice_generate'));
		add_filter('plugin_action_links_gmap-embed/srm_gmap_embed.php', array($this, 'gmap_srm_settings_link'), 10, 4);
		add_action('media_buttons', array($this, 'add_wp_google_map_media_button'));
		add_action('admin_footer', array($this, 'wp_google_map_media_button_content'));
		$this->ajax_hooks();

		/** To prevent others plugin loading Google Map API(with checking user consent) */
		if (get_option('_wgm_prevent_other_plugin_theme_api_load') === 'Y') {
			add_filter('script_loader_tag', array($this, 'do_prevent_others_google_maps_tag'), 10000000, 3);
		}

		// Validate user capability before allowing admin actions
		// add_action('admin_init', function () {
		// 	if (!current_user_can($this->capability)) {
		// 		// Escape output for XSS protection
		// 		wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'gmap-embed'));
		// 	}
		// });
	}

	private function ajax_hooks()
	{
		// All AJAX handlers should validate nonce and user capability
		$ajax_actions = [
			'wpgmapembed_save_map_data' => 'save_wpgmapembed_data',
			'wpgmapembed_load_map_data' => 'load_wpgmapembed_list',
			'wpgmapembed_popup_load_map_data' => 'load_popup_wpgmapembed_list',
			'wpgmapembed_get_wpgmap_data' => 'get_wpgmapembed_data',
			'wpgmapembed_remove_wpgmap' => 'remove_wpgmapembed_data',
			'wpgmapembed_save_setup_wizard' => 'wpgmap_save_setup_wizard',
			'wgm_get_all_maps' => 'wgm_get_all_maps',
			'wpgmapembed_save_map_markers' => 'save_map_marker',
			'wpgmapembed_update_map_markers' => 'update_map_marker',
			'wpgmapembed_get_marker_icons' => 'get_marker_icons',
			'wpgmapembed_save_marker_icon' => 'save_marker_icon',
			'wpgmapembed_get_markers_by_map_id' => 'get_markers_by_map_id',
			'wpgmapembed_p_get_markers_by_map_id' => 'p_get_markers_by_map_id',
			'wgm_get_markers_by_map_id' => 'wgm_get_markers_by_map_id_for_dt',
			'wpgmapembed_delete_marker' => 'delete_marker',
			'wpgmapembed_get_marker_data_by_marker_id' => 'get_marker_data_by_marker_id',
		];

		foreach ($ajax_actions as $action => $method) {
			add_action("wp_ajax_{$action}", function () use ($method) {
				if (!current_user_can($this->capability)) {
					wp_send_json_error(['message' => esc_html__('Unauthorized', 'gmap-embed')]);
				}
				$this->$method();
			});
		}

		// For nopriv actions, only allow safe ones
		add_action('wp_ajax_nopriv_wpgmapembed_p_get_markers_by_map_id', function () {
			$this->p_get_markers_by_map_id();
		});
	}

	public function load_dependencies()
	{
		// Define Shortcode.
		$shortcode_file = WGM_PLUGIN_PATH . '/public/includes/shortcodes.php';
		if (file_exists($shortcode_file)) {
			require_once $shortcode_file;
		}
	}

	public function register_widget()
	{
		// Defensive: Only register if class exists
		if (class_exists('WGMSRM\\Classes\\srmgmap_widget')) {
			register_widget('WGMSRM\\Classes\\srmgmap_widget');
		}
	}
}
