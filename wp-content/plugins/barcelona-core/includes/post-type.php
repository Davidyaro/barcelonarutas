<?php
defined('ABSPATH') || exit;

function brc_register_post_types(): void {

    // Stories
    register_post_type('br_story', [
        'labels' => [
            'name' => 'Historias',
            'singular_name' => 'Historia',
        ],
        'public' => true,
        'show_in_rest' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-location-alt',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'revisions'],
        'rewrite' => ['slug' => 'historias'],
    ]);

    // Routes
    register_post_type('br_route', [
        'labels' => [
            'name' => 'Rutas',
            'singular_name' => 'Ruta',
        ],
        'public' => true,
        'show_in_rest' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-location',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'revisions'],
        'rewrite' => ['slug' => 'rutas'],
    ]);
}
