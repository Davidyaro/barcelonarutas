<?php
defined('ABSPATH') || exit;

function brc_register_rest_routes(): void {

    register_rest_route('br/v1', '/map/stories', [
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => 'brc_rest_map_stories',
        'args' => [
            'bbox' => ['required' => false],
            'zoom' => ['required' => false],
            'q' => ['required' => false],
            'district' => ['required' => false],
            'neighborhood' => ['required' => false],
            'period' => ['required' => false],
            'theme' => ['required' => false],
            'type' => ['required' => false],
            'tag' => ['required' => false],
            'year_from' => ['required' => false],
            'year_to' => ['required' => false],
            'limit' => ['required' => false],
        ],
    ]);
}

function brc_rest_map_stories(WP_REST_Request $request): WP_REST_Response {
    $filters = [
        'bbox' => $request->get_param('bbox'),
        'zoom' => $request->get_param('zoom'),
        'q' => $request->get_param('q'),
        'district' => $request->get_param('district'),
        'neighborhood' => $request->get_param('neighborhood'),
        'period' => $request->get_param('period'),
        'theme' => $request->get_param('theme'),
        'type' => $request->get_param('type'),
        'tag' => $request->get_param('tag'),
        'year_from' => $request->get_param('year_from'),
        'year_to' => $request->get_param('year_to'),
        'limit' => $request->get_param('limit'),
    ];

    $meta_query = [
        [
            'key' => 'br_status',
            'value' => 'approved',
        ],
        [
            'key' => 'br_show_on_map',
            'value' => true,
        ],
        [
            'key' => 'br_lat',
            'compare' => 'EXISTS',
        ],
        [
            'key' => 'br_lng',
            'compare' => 'EXISTS',
        ],
    ];

    if (!empty($filters['bbox'])) {
        $bbox = brc_sanitize_bbox((string) $filters['bbox']);
        if ($bbox) {
            $meta_query[] = [
                'key' => 'br_lng',
                'value' => [$bbox['min_lng'], $bbox['max_lng']],
                'compare' => 'BETWEEN',
                'type' => 'NUMERIC',
            ];
            $meta_query[] = [
                'key' => 'br_lat',
                'value' => [$bbox['min_lat'], $bbox['max_lat']],
                'compare' => 'BETWEEN',
                'type' => 'NUMERIC',
            ];
        }
    }

    $year_from = $filters['year_from'] !== null ? (int) $filters['year_from'] : null;
    $year_to = $filters['year_to'] !== null ? (int) $filters['year_to'] : null;

    if ($year_from) {
        $meta_query[] = [
            'relation' => 'OR',
            [
                'key' => 'br_year_start',
                'value' => $year_from,
                'compare' => '>=',
                'type' => 'NUMERIC',
            ],
            [
                'key' => 'br_year_end',
                'value' => $year_from,
                'compare' => '>=',
                'type' => 'NUMERIC',
            ],
        ];
    }

    if ($year_to) {
        $meta_query[] = [
            'relation' => 'OR',
            [
                'key' => 'br_year_start',
                'value' => $year_to,
                'compare' => '<=',
                'type' => 'NUMERIC',
            ],
            [
                'key' => 'br_year_end',
                'value' => $year_to,
                'compare' => '<=',
                'type' => 'NUMERIC',
            ],
        ];
    }

    $tax_query = ['relation' => 'AND'];
    $taxonomy_filters = [
        'district' => 'br_district',
        'neighborhood' => 'br_neighborhood',
        'period' => 'br_period',
        'theme' => 'br_theme',
        'type' => 'br_story_type',
    ];

    foreach ($taxonomy_filters as $param => $taxonomy) {
        if (empty($filters[$param])) {
            continue;
        }

        $tax_query[] = [
            'taxonomy' => $taxonomy,
            'field' => 'name',
            'terms' => [(string) $filters[$param]],
        ];
    }

    if (!empty($filters['tag'])) {
        $story_taxonomies = get_object_taxonomies('br_story');
        if (in_array('post_tag', $story_taxonomies, true)) {
            $tax_query[] = [
                'taxonomy' => 'post_tag',
                'field' => 'name',
                'terms' => [(string) $filters['tag']],
            ];
        }
    }

    $limit = absint($filters['limit']);
    if ($limit < 1) {
        $limit = 200;
    }

    $query_args = [
        'post_type' => 'br_story',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'meta_query' => $meta_query,
    ];

    if (!empty($filters['q'])) {
        $query_args['s'] = (string) $filters['q'];
    }

    if (count($tax_query) > 1) {
        $query_args['tax_query'] = $tax_query;
    }

    $query = new WP_Query($query_args);
    $items = [];

    foreach ($query->posts as $post) {
        $lat = get_post_meta($post->ID, 'br_lat', true);
        $lng = get_post_meta($post->ID, 'br_lng', true);
        if ($lat === '' || $lng === '') {
            continue;
        }

        $tags = [];
        $terms = wp_get_object_terms($post->ID, array_values($taxonomy_filters));
        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $tags[] = $term->name;
            }
        }

        $items[] = [
            'id' => $post->ID,
            'slug' => $post->post_name,
            'title' => get_the_title($post),
            'subtitle' => get_post_meta($post->ID, 'br_subtitle', true),
            'lat' => (float) $lat,
            'lng' => (float) $lng,
            'tags' => $tags,
        ];
    }

    return rest_ensure_response($items);
}
