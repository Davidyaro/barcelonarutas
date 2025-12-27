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