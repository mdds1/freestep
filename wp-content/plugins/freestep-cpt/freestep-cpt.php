<?php
/**
 * Plugin Name: Free Step CPT
 * Description: Tipos de conteudo customizados do Time Free Step Brasil
 * Version: 1.0.0
 */

add_action('init', function () {
    register_post_type('fs_membro', [
        'labels' => [
            'name'          => 'Membros',
            'singular_name' => 'Membro',
            'add_new_item'  => 'Adicionar Membro',
            'edit_item'     => 'Editar Membro',
        ],
        'public'       => true,
        'has_archive'  => false,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-groups',
        'supports'     => ['title', 'thumbnail'],
        'rewrite'      => ['slug' => 'membro'],
    ]);

    register_post_type('fs_show', [
        'labels' => [
            'name'          => 'Agenda',
            'singular_name' => 'Show',
            'add_new_item'  => 'Adicionar Show',
            'edit_item'     => 'Editar Show',
        ],
        'public'       => true,
        'has_archive'  => false,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-calendar-alt',
        'supports'     => ['title'],
        'rewrite'      => ['slug' => 'show'],
    ]);

    register_post_type('fs_imprensa', [
        'labels' => [
            'name'          => 'Imprensa',
            'singular_name' => 'Aparicao',
            'add_new_item'  => 'Adicionar Aparicao',
            'edit_item'     => 'Editar Aparicao',
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-media-video',
        'supports'     => ['title'],
    ]);

    register_post_type('fs_parceiro', [
        'labels' => [
            'name'          => 'Parceiros',
            'singular_name' => 'Parceiro',
            'add_new_item'  => 'Adicionar Parceiro',
            'edit_item'     => 'Editar Parceiro',
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-star-filled',
        'supports'     => ['title', 'thumbnail'],
    ]);
});
