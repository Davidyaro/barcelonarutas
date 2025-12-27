<?php
defined('ABSPATH') || exit;

function brc_register_acf_fields(): void {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_br_story_fields',
        'title' => 'Historia',
        'fields' => [
            [
                'key' => 'field_br_id_internal',
                'label' => 'ID interno',
                'name' => 'br_id_internal',
                'type' => 'text',
            ],
            [
                'key' => 'field_br_subtitle',
                'label' => 'Subtitulo',
                'name' => 'br_subtitle',
                'type' => 'text',
            ],
            [
                'key' => 'field_br_full_address_approx',
                'label' => 'Dirección',
                'name' => 'br_full_address_approx',
                'type' => 'text',
            ],
            [
                'key' => 'field_br_lat',
                'label' => 'Latitud',
                'name' => 'br_lat',
                'type' => 'number',
                'required' => 1,
                'step' => '0.000001',
            ],
            [
                'key' => 'field_br_lng',
                'label' => 'Longitud',
                'name' => 'br_lng',
                'type' => 'number',
                'required' => 1,
                'step' => '0.000001',
            ],
            [
                'key' => 'field_br_year_start',
                'label' => 'Año inicio',
                'name' => 'br_year_start',
                'type' => 'number',
            ],
            [
                'key' => 'field_br_year_end',
                'label' => 'Año fin',
                'name' => 'br_year_end',
                'type' => 'number',
            ],
            [
                'key' => 'field_br_reading_time_min',
                'label' => 'Tiempo de lectura',
                'name' => 'br_reading_time_min',
                'type' => 'number',
            ],
            [
                'key' => 'field_br_status',
                'label' => 'Estado',
                'name' => 'br_status',
                'type' => 'select',
                'choices' => [
                    'pending' => 'Pendiente',
                    'reviewed' => 'Revisada',
                    'approved' => 'Aprobada',
                ],
                'default_value' => 'pending',
                'ui' => 1,
            ],
            [
                'key' => 'field_br_show_on_map',
                'label' => 'Mostrar en el mapa',
                'name' => 'br_show_on_map',
                'type' => 'true_false',
                'ui' => 1,
                'default_value' => 1,
            ],
            [
                'key' => 'field_br_source',
                'label' => 'Fuente',
                'name' => 'br_source',
                'type' => 'url',
            ],
            [
                'key' => 'field_br_author_text',
                'label' => 'Autor del texto',
                'name' => 'br_author_text',
                'type' => 'text',
            ],
            [
                'key' => 'field_br_internal_notes',
                'label' => 'Notas internas',
                'name' => 'br_internal_notes',
                'type' => 'textarea',
            ],
            [
                'key' => 'field_br_img',
                'label' => 'Imagen',
                'name' => 'br_img',
                'type' => 'image',
                'return_format' => 'id',
                'preview_size' => 'medium',
                'library' => 'all',
            ],
            [
                'key' => 'field_br_location',
                'label' => 'Ubicación en el mapa',
                'name' => 'br_location',
                'type' => 'google_map',
                'center_lat' => 41.3874,
                'center_lng' => 2.1686,
                'zoom' => 13,
                'height' => 400,
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'br_story',
                ],
            ],
        ],
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'active' => true,
    ]);
}

add_action('acf/init', 'brc_register_acf_fields');
