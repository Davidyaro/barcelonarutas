<?php
defined('ABSPATH') || exit;

function brc_register_meta(): void {

    // === br_story meta ===
    $story_meta = [
        'br_id_internal' => ['type' => 'string'],
        'br_subtitle' => ['type' => 'string'],
        'br_full_address_approx' => ['type' => 'string'],
        'br_lat' => ['type' => 'number'],
        'br_lng' => ['type' => 'number'],
        'br_year_start' => ['type' => 'integer'],
        'br_year_end' => ['type' => 'integer'],
        'br_reading_time_min' => ['type' => 'integer'],
        'br_show_on_map' => ['type' => 'boolean'],
        'br_status' => ['type' => 'string'], // pending|reviewed|approved
        'br_source' => ['type' => 'string'],
        'br_author_text' => ['type' => 'string'],
        'br_internal_notes' => ['type' => 'string'],
    ];

    foreach ($story_meta as $key => $conf) {
        register_post_meta('br_story', $key, [
            'single' => true,
            'type' => $conf['type'],
            'show_in_rest' => true,
            'sanitize_callback' => function($value) use ($key) {
                if ($key === 'br_status') {
                    $allowed = ['pending', 'reviewed', 'approved'];
                    return brc_get_enum($allowed, (string)$value, 'pending');
                }
                if ($key === 'br_show_on_map') return brc_bool($value);
                return is_string($value) ? sanitize_text_field($value) : $value;
            },
            // Solo admins pueden editar notas internas
            'auth_callback' => function() use ($key) {
                if ($key === 'br_internal_notes') {
                    return current_user_can('manage_options');
                }
                return current_user_can('edit_posts');
            }
        ]);
    }

    // Defaults (cuando guardes historias, idealmente setearías esto)
    // br_show_on_map=true, br_status=pending

    // === br_route meta ===
    $route_meta = [
        'br_route_stops' => ['type' => 'array'], // array de IDs
        'br_time_estimated_min' => ['type' => 'integer'],
        'br_distance_estimated_km' => ['type' => 'number'],
        'br_route_level' => ['type' => 'string'], // easy|medium|hard
    ];

    foreach ($route_meta as $key => $conf) {
        register_post_meta('br_route', $key, [
            'single' => true,
            'type' => $conf['type'],
            'show_in_rest' => true,
            'sanitize_callback' => function($value) use ($key) {
                if ($key === 'br_route_level') {
                    return brc_get_enum(['easy','medium','hard'], (string)$value, 'easy');
                }
                if ($key === 'br_route_stops') {
                    if (!is_array($value)) return [];
                    return array_values(array_filter(array_map('intval', $value)));
                }
                return $value;
            },
            'auth_callback' => fn() => current_user_can('edit_posts'),
        ]);
    }
}
