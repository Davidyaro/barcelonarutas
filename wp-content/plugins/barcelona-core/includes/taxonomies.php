<?php
defined('ABSPATH') || exit;

function brc_register_taxonomies(): void {

    // Stories taxonomies
    $story = ['br_story'];

    register_taxonomy('br_district', $story, [
        'label' => 'Distritos',
        'public' => true,
        'hierarchical' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'distrito'],
    ]);

    register_taxonomy('br_neighborhood', $story, [
        'label' => 'Barrios',
        'public' => true,
        'hierarchical' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'barrio'],
    ]);

    register_taxonomy('br_period', $story, [
        'label' => 'Épocas',
        'public' => true,
        'hierarchical' => false,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'epoca'],
    ]);

    register_taxonomy('br_theme', $story, [
        'label' => 'Temas',
        'public' => true,
        'hierarchical' => false,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'tema'],
    ]);

    register_taxonomy('br_story_type', $story, [
        'label' => 'Tipo de historia',
        'public' => true,
        'hierarchical' => false,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'tipo'],
    ]);

    // Routes can reuse some
    $route = ['br_route'];

    register_taxonomy('br_route_period', $route, [
        'label' => 'Épocas (Ruta)',
        'public' => true,
        'hierarchical' => false,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'ruta-epoca'],
    ]);

    register_taxonomy('br_route_theme', $route, [
        'label' => 'Temas (Ruta)',
        'public' => true,
        'hierarchical' => false,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'ruta-tema'],
    ]);

    register_taxonomy('br_route_district', $route, [
        'label' => 'Distritos (Ruta)',
        'public' => true,
        'hierarchical' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'ruta-distrito'],
    ]);
}
