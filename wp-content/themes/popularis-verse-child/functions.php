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

    if (!is_page_template($map_templates) && !is_post_type_archive('br_story')) {
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
        'pinUrl' => get_stylesheet_directory_uri() . '/map-pin-red.svg',
        'enableToggle' => false,
        'enableGeoFilters' => false,
        'restUrl' => rest_url('br/v1/map/stories'),
    );

    if (is_page_template('page-historias.php') || is_post_type_archive('br_story')) {
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

function br_register_story_content_types() {
    register_post_type('br_story', array(
        'labels' => array(
            'name' => __('Historias', 'popularis-verse-child'),
            'singular_name' => __('Historia', 'popularis-verse-child'),
            'add_new_item' => __('Añadir historia', 'popularis-verse-child'),
            'edit_item' => __('Editar historia', 'popularis-verse-child'),
            'new_item' => __('Nueva historia', 'popularis-verse-child'),
            'view_item' => __('Ver historia', 'popularis-verse-child'),
            'search_items' => __('Buscar historias', 'popularis-verse-child'),
        ),
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-location',
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail'),
        'show_in_rest' => true,
    ));

    $taxonomies = array(
        'br_district' => array(
            'label' => __('Distritos', 'popularis-verse-child'),
            'singular_label' => __('Distrito', 'popularis-verse-child'),
            'hierarchical' => true,
        ),
        'br_neighborhood' => array(
            'label' => __('Barrios', 'popularis-verse-child'),
            'singular_label' => __('Barrio', 'popularis-verse-child'),
            'hierarchical' => true,
        ),
        'br_period' => array(
            'label' => __('Épocas', 'popularis-verse-child'),
            'singular_label' => __('Época', 'popularis-verse-child'),
            'hierarchical' => false,
        ),
        'br_theme' => array(
            'label' => __('Temas', 'popularis-verse-child'),
            'singular_label' => __('Tema', 'popularis-verse-child'),
            'hierarchical' => false,
        ),
        'br_type' => array(
            'label' => __('Tipos', 'popularis-verse-child'),
            'singular_label' => __('Tipo', 'popularis-verse-child'),
            'hierarchical' => false,
        ),
    );

    foreach ($taxonomies as $taxonomy => $data) {
        register_taxonomy($taxonomy, array('br_story'), array(
            'labels' => array(
                'name' => $data['label'],
                'singular_name' => $data['singular_label'],
            ),
            'hierarchical' => $data['hierarchical'],
            'show_in_rest' => true,
            'show_ui' => true,
            'show_admin_column' => true,
        ));
    }
}

add_action('init', 'br_register_story_content_types');

function br_register_story_meta() {
    $meta_fields = array(
        'br_story_lat' => array('type' => 'number', 'single' => true, 'sanitize' => 'floatval'),
        'br_story_lng' => array('type' => 'number', 'single' => true, 'sanitize' => 'floatval'),
        'br_story_subtitle' => array('type' => 'string', 'single' => true, 'sanitize' => 'sanitize_text_field'),
        'br_story_status' => array('type' => 'string', 'single' => true, 'sanitize' => 'sanitize_text_field'),
        'br_show_on_map' => array('type' => 'boolean', 'single' => true, 'sanitize' => 'rest_sanitize_boolean'),
    );

    foreach ($meta_fields as $meta_key => $args) {
        register_post_meta('br_story', $meta_key, array(
            'type' => $args['type'],
            'single' => $args['single'],
            'show_in_rest' => true,
            'sanitize_callback' => $args['sanitize'],
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            },
        ));
    }
}

add_action('init', 'br_register_story_meta');

function br_render_story_details_metabox($post) {
    wp_nonce_field('br_story_details_nonce', 'br_story_details_nonce');
    $lat = get_post_meta($post->ID, 'br_story_lat', true);
    $lng = get_post_meta($post->ID, 'br_story_lng', true);
    $subtitle = get_post_meta($post->ID, 'br_story_subtitle', true);
    $status = get_post_meta($post->ID, 'br_story_status', true);
    $show_on_map = get_post_meta($post->ID, 'br_show_on_map', true);
    $id_internal = get_post_meta($post->ID, 'br_id_internal', true);
    $full_address = get_post_meta($post->ID, 'br_full_address_approx', true);
    $year_start = get_post_meta($post->ID, 'br_year_start', true);
    $year_end = get_post_meta($post->ID, 'br_year_end', true);
    $reading_time = get_post_meta($post->ID, 'br_reading_time_min', true);
    $source = get_post_meta($post->ID, 'br_source', true);
    $author_text = get_post_meta($post->ID, 'br_author_text', true);
    $internal_notes = get_post_meta($post->ID, 'br_internal_notes', true);
    $image_id = get_post_meta($post->ID, 'br_img', true);
    $location = get_post_meta($post->ID, 'br_location', true);
    if (is_string($location)) {
        $maybe_location = maybe_unserialize($location);
        if (is_array($maybe_location)) {
            $location = $maybe_location;
        }
    }
    $location_lat = is_array($location) ? ($location['lat'] ?? '') : '';
    $location_lng = is_array($location) ? ($location['lng'] ?? '') : '';

    ?>
    <p>
        <label for="br-story-subtitle"><?php esc_html_e('Subtítulo', 'popularis-verse-child'); ?></label>
        <input type="text" name="br_story_subtitle" id="br-story-subtitle" class="widefat" value="<?php echo esc_attr($subtitle); ?>" />
    </p>
    <p>
        <label for="br-id-internal"><?php esc_html_e('ID interno', 'popularis-verse-child'); ?></label>
        <input type="text" name="br_id_internal" id="br-id-internal" class="widefat" value="<?php echo esc_attr($id_internal); ?>" />
    </p>
    <p>
        <label for="br-full-address-approx"><?php esc_html_e('Dirección', 'popularis-verse-child'); ?></label>
        <input type="text" name="br_full_address_approx" id="br-full-address-approx" class="widefat" value="<?php echo esc_attr($full_address); ?>" />
    </p>
    <p>
        <label for="br-story-lat"><?php esc_html_e('Latitud', 'popularis-verse-child'); ?></label>
        <input type="number" step="0.000001" name="br_story_lat" id="br-story-lat" class="widefat" value="<?php echo esc_attr($lat); ?>" />
    </p>
    <p>
        <label for="br-story-lng"><?php esc_html_e('Longitud', 'popularis-verse-child'); ?></label>
        <input type="number" step="0.000001" name="br_story_lng" id="br-story-lng" class="widefat" value="<?php echo esc_attr($lng); ?>" />
    </p>
    <p>
        <label for="br-location-lat"><?php esc_html_e('Ubicación en el mapa (lat)', 'popularis-verse-child'); ?></label>
        <input type="number" step="0.000001" name="br_location_lat" id="br-location-lat" class="widefat" value="<?php echo esc_attr($location_lat); ?>" />
    </p>
    <p>
        <label for="br-location-lng"><?php esc_html_e('Ubicación en el mapa (lng)', 'popularis-verse-child'); ?></label>
        <input type="number" step="0.000001" name="br_location_lng" id="br-location-lng" class="widefat" value="<?php echo esc_attr($location_lng); ?>" />
    </p>
    <p>
        <label for="br-year-start"><?php esc_html_e('Año inicio', 'popularis-verse-child'); ?></label>
        <input type="number" name="br_year_start" id="br-year-start" class="widefat" value="<?php echo esc_attr($year_start); ?>" />
    </p>
    <p>
        <label for="br-year-end"><?php esc_html_e('Año fin', 'popularis-verse-child'); ?></label>
        <input type="number" name="br_year_end" id="br-year-end" class="widefat" value="<?php echo esc_attr($year_end); ?>" />
    </p>
    <p>
        <label for="br-reading-time-min"><?php esc_html_e('Tiempo de lectura (min)', 'popularis-verse-child'); ?></label>
        <input type="number" name="br_reading_time_min" id="br-reading-time-min" class="widefat" value="<?php echo esc_attr($reading_time); ?>" />
    </p>
    <p>
        <label for="br-story-status"><?php esc_html_e('Estado', 'popularis-verse-child'); ?></label>
        <select name="br_story_status" id="br-story-status" class="widefat">
            <?php
            $options = array(
                '' => __('Selecciona...', 'popularis-verse-child'),
                'draft' => __('Borrador', 'popularis-verse-child'),
                'review' => __('En revisión', 'popularis-verse-child'),
                'approved' => __('Aprobada', 'popularis-verse-child'),
            );
            foreach ($options as $value => $label) {
                printf(
                    '<option value="%s"%s>%s</option>',
                    esc_attr($value),
                    selected($status, $value, false),
                    esc_html($label)
                );
            }
            ?>
        </select>
    </p>
    <p>
        <label>
            <input type="checkbox" name="br_show_on_map" value="1" <?php checked($show_on_map, '1'); ?> />
            <?php esc_html_e('Mostrar en mapa', 'popularis-verse-child'); ?>
        </label>
    </p>
    <p>
        <label for="br-source"><?php esc_html_e('Fuente', 'popularis-verse-child'); ?></label>
        <input type="url" name="br_source" id="br-source" class="widefat" value="<?php echo esc_attr($source); ?>" />
    </p>
    <p>
        <label for="br-author-text"><?php esc_html_e('Autor del texto', 'popularis-verse-child'); ?></label>
        <input type="text" name="br_author_text" id="br-author-text" class="widefat" value="<?php echo esc_attr($author_text); ?>" />
    </p>
    <p>
        <label for="br-internal-notes"><?php esc_html_e('Notas internas', 'popularis-verse-child'); ?></label>
        <textarea name="br_internal_notes" id="br-internal-notes" class="widefat" rows="4"><?php echo esc_textarea($internal_notes); ?></textarea>
    </p>
    <p>
        <label for="br-img"><?php esc_html_e('Imagen (ID adjunto)', 'popularis-verse-child'); ?></label>
        <input type="number" name="br_img" id="br-img" class="widefat" value="<?php echo esc_attr($image_id); ?>" />
    </p>
    <?php
}

function br_register_story_metaboxes() {
    add_meta_box(
        'br_story_details',
        __('Detalles de historia', 'popularis-verse-child'),
        'br_render_story_details_metabox',
        'br_story',
        'normal',
        'high'
    );
}

add_action('add_meta_boxes', 'br_register_story_metaboxes');

function br_save_story_metaboxes($post_id) {
    if (!isset($_POST['br_story_details_nonce']) || !wp_verify_nonce($_POST['br_story_details_nonce'], 'br_story_details_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $fields = array(
        'br_story_lat' => 'floatval',
        'br_story_lng' => 'floatval',
        'br_story_subtitle' => 'sanitize_text_field',
        'br_story_status' => 'sanitize_text_field',
        'br_id_internal' => 'sanitize_text_field',
        'br_full_address_approx' => 'sanitize_text_field',
        'br_year_start' => 'absint',
        'br_year_end' => 'absint',
        'br_reading_time_min' => 'absint',
        'br_source' => 'esc_url_raw',
        'br_author_text' => 'sanitize_text_field',
        'br_internal_notes' => 'sanitize_textarea_field',
        'br_img' => 'absint',
    );

    foreach ($fields as $field => $sanitize) {
        if (isset($_POST[$field])) {
            $value = call_user_func($sanitize, wp_unslash($_POST[$field]));
            update_post_meta($post_id, $field, $value);
        }
    }

    $show_on_map = isset($_POST['br_show_on_map']) ? '1' : '0';
    update_post_meta($post_id, 'br_show_on_map', $show_on_map);

    $location_lat = isset($_POST['br_location_lat']) ? floatval(wp_unslash($_POST['br_location_lat'])) : null;
    $location_lng = isset($_POST['br_location_lng']) ? floatval(wp_unslash($_POST['br_location_lng'])) : null;
    if ($location_lat !== null && $location_lng !== null && $location_lat !== 0.0 && $location_lng !== 0.0) {
        update_post_meta($post_id, 'br_location', array('lat' => $location_lat, 'lng' => $location_lng));
    }
}

add_action('save_post_br_story', 'br_save_story_metaboxes');

function br_register_story_acf_fields() {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    if (function_exists('acf_get_local_field_group')) {
        $existing = acf_get_local_field_group('group_br_story_fields');
        if (!empty($existing)) {
            return;
        }
    }

    acf_add_local_field_group(array(
        'key' => 'group_br_story_fields',
        'title' => 'Historia',
        'fields' => array(
            array(
                'key' => 'field_br_id_internal',
                'label' => 'ID interno',
                'name' => 'br_id_internal',
                'type' => 'text',
            ),
            array(
                'key' => 'field_br_subtitle',
                'label' => 'Subtitulo',
                'name' => 'br_subtitle',
                'type' => 'text',
            ),
            array(
                'key' => 'field_br_full_address_approx',
                'label' => 'Dirección',
                'name' => 'br_full_address_approx',
                'type' => 'text',
            ),
            array(
                'key' => 'field_br_lat',
                'label' => 'Latitud',
                'name' => 'br_lat',
                'type' => 'number',
                'required' => 1,
                'step' => '0.000001',
            ),
            array(
                'key' => 'field_br_lng',
                'label' => 'Longitud',
                'name' => 'br_lng',
                'type' => 'number',
                'required' => 1,
                'step' => '0.000001',
            ),
            array(
                'key' => 'field_br_year_start',
                'label' => 'Año inicio',
                'name' => 'br_year_start',
                'type' => 'number',
            ),
            array(
                'key' => 'field_br_year_end',
                'label' => 'Año fin',
                'name' => 'br_year_end',
                'type' => 'number',
            ),
            array(
                'key' => 'field_br_reading_time_min',
                'label' => 'Tiempo de lectura',
                'name' => 'br_reading_time_min',
                'type' => 'number',
            ),
            array(
                'key' => 'field_br_status',
                'label' => 'Estado',
                'name' => 'br_status',
                'type' => 'select',
                'choices' => array(
                    'pending' => 'Pendiente',
                    'reviewed' => 'Revisada',
                    'approved' => 'Aprobada',
                ),
                'default_value' => 'pending',
                'ui' => 1,
            ),
            array(
                'key' => 'field_br_show_on_map',
                'label' => 'Mostrar en el mapa',
                'name' => 'br_show_on_map',
                'type' => 'true_false',
                'ui' => 1,
                'default_value' => 1,
            ),
            array(
                'key' => 'field_br_source',
                'label' => 'Fuente',
                'name' => 'br_source',
                'type' => 'url',
            ),
            array(
                'key' => 'field_br_author_text',
                'label' => 'Autor del texto',
                'name' => 'br_author_text',
                'type' => 'text',
            ),
            array(
                'key' => 'field_br_internal_notes',
                'label' => 'Notas internas',
                'name' => 'br_internal_notes',
                'type' => 'textarea',
            ),
            array(
                'key' => 'field_br_img',
                'label' => 'Imagen',
                'name' => 'br_img',
                'type' => 'image',
                'return_format' => 'id',
                'preview_size' => 'medium',
                'library' => 'all',
            ),
            array(
                'key' => 'field_br_location',
                'label' => 'Ubicación en el mapa',
                'name' => 'br_location',
                'type' => 'google_map',
                'center_lat' => 41.3874,
                'center_lng' => 2.1686,
                'zoom' => 13,
                'height' => 400,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'br_story',
                ),
            ),
        ),
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'active' => true,
    ));
}

add_action('acf/init', 'br_register_story_acf_fields');

function br_register_story_map_endpoint() {
    register_rest_route('br/v1', '/map/stories', array(
        'methods' => 'GET',
        'callback' => 'br_get_story_map_data',
        'permission_callback' => '__return_true',
        'args' => array(
            'bbox' => array(
                'required' => false,
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'district' => array(
                'required' => false,
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'period' => array(
                'required' => false,
                'sanitize_callback' => 'sanitize_text_field',
            ),
        ),
    ));
}

add_action('rest_api_init', 'br_register_story_map_endpoint');

function br_get_story_map_data(WP_REST_Request $request) {
    $bbox = $request->get_param('bbox');
    $district = $request->get_param('district');
    $period = $request->get_param('period');

    $meta_query = array(
        array(
            'key' => 'br_story_status',
            'value' => 'approved',
        ),
        array(
            'key' => 'br_show_on_map',
            'value' => '1',
        ),
        array(
            'key' => 'br_story_lat',
            'compare' => 'EXISTS',
        ),
        array(
            'key' => 'br_story_lng',
            'compare' => 'EXISTS',
        ),
    );

    if (!empty($bbox)) {
        $parts = array_map('floatval', explode(',', $bbox));
        if (count($parts) === 4) {
            $meta_query[] = array(
                'key' => 'br_story_lng',
                'value' => array($parts[0], $parts[2]),
                'compare' => 'BETWEEN',
                'type' => 'NUMERIC',
            );
            $meta_query[] = array(
                'key' => 'br_story_lat',
                'value' => array($parts[1], $parts[3]),
                'compare' => 'BETWEEN',
                'type' => 'NUMERIC',
            );
        }
    }

    $tax_query = array('relation' => 'AND');

    if (!empty($district)) {
        $tax_query[] = array(
            'taxonomy' => 'br_district',
            'field' => 'name',
            'terms' => array($district),
        );
    }

    if (!empty($period)) {
        $tax_query[] = array(
            'taxonomy' => 'br_period',
            'field' => 'name',
            'terms' => array($period),
        );
    }

    $query_args = array(
        'post_type' => 'br_story',
        'post_status' => 'publish',
        'posts_per_page' => 200,
        'meta_query' => $meta_query,
    );

    if (count($tax_query) > 1) {
        $query_args['tax_query'] = $tax_query;
    }

    $query = new WP_Query($query_args);
    $items = array();

    foreach ($query->posts as $post) {
        $lat = get_post_meta($post->ID, 'br_story_lat', true);
        $lng = get_post_meta($post->ID, 'br_story_lng', true);
        if ($lat === '' || $lng === '') {
            continue;
        }

        $terms = wp_get_object_terms($post->ID, array('br_district', 'br_neighborhood', 'br_period', 'br_theme', 'br_type'));
        $tags = array();
        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $tags[] = $term->name;
            }
        }

        $items[] = array(
            'id' => $post->ID,
            'title' => get_the_title($post),
            'subtitle' => get_post_meta($post->ID, 'br_story_subtitle', true),
            'lat' => (float) $lat,
            'lng' => (float) $lng,
            'tags' => $tags,
        );
    }

    return rest_ensure_response($items);
}
