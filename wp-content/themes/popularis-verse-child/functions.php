<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;


if (!function_exists('popularis_verse_parent_css')):

    /**
     * Enqueue CSS.
     */
    function popularis_verse_parent_css() {
        $parent_style = 'popularis-stylesheet';

        $dep = array('bootstrap');
        if (class_exists('WooCommerce')) {
            $dep = array('bootstrap', 'popularis-woocommerce');
        }

        wp_enqueue_style(
            'popularis-verse-child',
            get_stylesheet_directory_uri() . '/style.css',
            array($parent_style),
            wp_get_theme()->get('Version')
        );
    }

endif;

add_action('wp_enqueue_scripts', 'popularis_verse_parent_css');

if (!function_exists('popularis_verse_setup')) :

    /**
     * Global functions.
     */
    function popularis_verse_setup() {

        // Register extra menu for homepage. Loaded only on homepage - definded in template-part-header.php
        register_nav_menus(
                array(
                    'main_menu_home' => esc_html__('Homepage main menu', 'popularis-verse'),
                )
        );

        // Child theme language
load_child_theme_textdomain('popularis-verse-child', get_stylesheet_directory() . '/languages');
    }

endif;

add_action('after_setup_theme', 'popularis_verse_setup');

if (function_exists('popularis_customizer')) {
    add_action('init', 'popularis_customizer');
}

function br_enqueue_map_assets() {
    $map_templates = array(
        'page-principal.php',
        'page-historias.php',
        'page-rutas.php',
        'page-mapa-pantalla.php',
    );

    if (!is_page_template($map_templates)) {
        return;
    }

    wp_enqueue_script(
        'br-map',
        get_stylesheet_directory_uri() . '/assets/br-map.js',
        array(),
        wp_get_theme()->get('Version'),
        true
    );

    $config = array(
        'center' => array(41.3851, 2.1734),
        'zoom' => 13,
        'bounds' => array(
            'latMin' => 41.35,
            'latMax' => 41.42,
            'lngMin' => 2.11,
            'lngMax' => 2.19,
        ),
        'markersCount' => 7,
        'pinUrl' => get_stylesheet_directory_uri() . '/map-pin-red.svg',
        'enableShuffle' => true,
        'enableToggle' => false,
        'enableGeoFilters' => false,
    );

    if (is_page_template('page-historias.php')) {
        $config['enableToggle'] = true;
        $config['enableGeoFilters'] = true;
    }

    wp_localize_script('br-map', 'brMapConfig', $config);
}

add_action('wp_enqueue_scripts', 'br_enqueue_map_assets');

if (!function_exists('popularis_verse_excerpt_length')) :

    /**
     * Limit the excerpt.
     */
    function popularis_verse_excerpt_length($length) {
        if (is_home() || is_archive()) { // Make sure to not limit pagebuilders
            return '24';
        } else {
            return $length;
        }
    }

    add_filter('excerpt_length', 'popularis_verse_excerpt_length', 999);

endif;

/**
 * Move sidebar left.
 */
function popularis_main_content_width_columns() {

    $columns = '12';

    if (is_active_sidebar('sidebar-1')) {
        $columns = '9 col-md-push-3';
    }

    echo esc_attr($columns);
}
