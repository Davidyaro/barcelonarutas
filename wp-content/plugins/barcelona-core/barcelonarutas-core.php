<?php
/**
 * Plugin Name: Barcelonarutas Core
 * Description: CPTs, taxonomies, meta fields and REST API for BarcelonaRutas.
 * Version: 0.1.0
 * Author: David
 */

defined('ABSPATH') || exit;

define('BRC_VERSION', '0.1.0');
define('BRC_PATH', plugin_dir_path(__FILE__));
define('BRC_URL', plugin_dir_url(__FILE__));

require_once BRC_PATH . 'includes/helpers.php';
require_once BRC_PATH . 'includes/post-types.php';
require_once BRC_PATH . 'includes/taxonomies.php';
require_once BRC_PATH . 'includes/meta.php';
require_once BRC_PATH . 'includes/acf-fields.php';
require_once BRC_PATH . 'includes/rest.php';

add_action('init', 'brc_register_post_types');
add_action('init', 'brc_register_taxonomies');
add_action('init', 'brc_register_meta');

add_action('rest_api_init', 'brc_register_rest_routes');

register_activation_hook(__FILE__, function () {
    brc_register_post_types();
    brc_register_taxonomies();
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function () {
    flush_rewrite_rules();
});
