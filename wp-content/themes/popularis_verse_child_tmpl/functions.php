<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('popularis_verse_parent_css')) :

    /**
     * Enqueue CSS.
     */
    function popularis_verse_parent_css() {
        $parent_style = 'popularis-stylesheet';

        $dep = array('bootstrap');
        if (class_exists('WooCommerce')) {
            $dep = array('bootstrap', 'popularis-woocommerce');
        }

        // Parent
        wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css', $dep);

        // Child
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

        // Register extra menu for homepage. Loaded only on homepage - defined in template-part-header.php
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

// OJO: engancha el customizer solo si existe la función (en el parent o en un plugin).
if (function_exists('popularis_customizer')) {
    add_action('init', 'popularis_customizer');
}

if (!function_exists('popularis_verse_excerpt_length')) :

    /**
     * Limit the excerpt.
     */
    function popularis_verse_excerpt_length($length) {
        if (is_home() || is_archive()) { // Make sure to not limit pagebuilders
            return 24;
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

function br_enqueue_map_assets() {
    if (!is_page_template('page-historias.php')) {
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
        'enableToggle' => true,
        'enableGeoFilters' => true,
        'restUrl' => rest_url('br/v1/map/stories'),
        'restDetailUrl' => rest_url('br/v1/stories'),
    );

    wp_localize_script('br-map', 'brMapConfig', $config);
}

add_action('wp_enqueue_scripts', 'br_enqueue_map_assets');

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

    ?>
    <p>
        <label for="br-story-subtitle"><?php esc_html_e('Subtítulo', 'popularis-verse-child'); ?></label>
        <input type="text" name="br_story_subtitle" id="br-story-subtitle" class="widefat" value="<?php echo esc_attr($subtitle); ?>" />
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
    );

    foreach ($fields as $field => $sanitize) {
        if (isset($_POST[$field])) {
            $value = call_user_func($sanitize, wp_unslash($_POST[$field]));
            update_post_meta($post_id, $field, $value);
        }
    }

    $show_on_map = isset($_POST['br_show_on_map']) ? '1' : '0';
    update_post_meta($post_id, 'br_show_on_map', $show_on_map);
}

add_action('save_post_br_story', 'br_save_story_metaboxes');

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

function br_register_story_detail_endpoint() {
    register_rest_route('br/v1', '/stories/(?P<story>[\\w-]+)', array(
        'methods' => 'GET',
        'callback' => 'br_get_story_detail_data',
        'permission_callback' => '__return_true',
        'args' => array(
            'story' => array(
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ),
        ),
    ));
}

add_action('rest_api_init', 'br_register_story_map_endpoint');
add_action('rest_api_init', 'br_register_story_detail_endpoint');

function br_format_story_terms($post_id) {
    $taxonomy_map = array(
        'br_district' => 'district',
        'br_neighborhood' => 'neighborhood',
        'br_period' => 'period',
        'br_theme' => 'theme',
        'br_type' => 'type',
    );

    $formatted = array();

    foreach ($taxonomy_map as $taxonomy => $key) {
        $terms = wp_get_object_terms($post_id, $taxonomy);
        if (is_wp_error($terms)) {
            $formatted[$key] = array();
            continue;
        }

        $formatted[$key] = array_map(function ($term) {
            return array(
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
            );
        }, $terms);
    }

    return $formatted;
}

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
            'field' => 'slug',
            'terms' => array($district),
        );
    }

    if (!empty($period)) {
        $tax_query[] = array(
            'taxonomy' => 'br_period',
            'field' => 'slug',
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
            'slug' => $post->post_name,
            'title' => get_the_title($post),
            'subtitle' => get_post_meta($post->ID, 'br_story_subtitle', true),
            'lat' => (float) $lat,
            'lng' => (float) $lng,
            'tags' => $tags,
        );
    }

    return rest_ensure_response($items);
}

function br_get_story_detail_data(WP_REST_Request $request) {
    $story_id = $request->get_param('story');

    $query_args = array(
        'post_type' => 'br_story',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'key' => 'br_story_status',
                'value' => 'approved',
            ),
        ),
    );

    if (is_numeric($story_id)) {
        $query_args['p'] = (int) $story_id;
    } else {
        $query_args['name'] = sanitize_title($story_id);
    }

    $query = new WP_Query($query_args);

    if (!$query->have_posts()) {
        return new WP_Error('br_story_not_found', __('Historia no encontrada', 'popularis-verse-child'), array('status' => 404));
    }

    $post = $query->posts[0];

    $response = array(
        'id' => $post->ID,
        'slug' => $post->post_name,
        'title' => get_the_title($post),
        'subtitle' => get_post_meta($post->ID, 'br_story_subtitle', true),
        'content' => apply_filters('the_content', $post->post_content),
        'excerpt' => apply_filters('the_excerpt', get_the_excerpt($post)),
        'permalink' => get_permalink($post),
        'author' => get_the_author_meta('display_name', $post->post_author),
        'years' => get_post_meta($post->ID, 'br_story_years', true),
        'source' => get_post_meta($post->ID, 'br_story_source', true),
        'lat' => (float) get_post_meta($post->ID, 'br_story_lat', true),
        'lng' => (float) get_post_meta($post->ID, 'br_story_lng', true),
        'terms' => br_format_story_terms($post->ID),
    );

    return rest_ensure_response($response);
}
