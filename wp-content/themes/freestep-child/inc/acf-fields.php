<?php
/**
 * Grupos de campos ACF registrados em codigo.
 *
 * Em codigo, e nao pelo painel, por tres motivos: valem assim que o tema
 * esta ativo, nao dependem de importacao manual de JSON, e versionam no git
 * junto com os templates que leem os campos. O painel continua sendo onde a
 * equipe preenche os VALORES; so a definicao dos campos vive aqui.
 */

add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key'      => 'group_fs_membro',
        'title'    => 'Dados do Membro',
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'fs_membro']]],
        'fields'   => [
            [
                'key'          => 'field_membro_funcao',
                'label'        => 'Funcao no time',
                'name'         => 'membro_funcao',
                'type'         => 'text',
                'instructions' => 'Ex: Dancarino, Coreografo, Diretor.',
            ],
            [
                'key'       => 'field_membro_bio',
                'label'     => 'Biografia',
                'name'      => 'membro_bio',
                'type'      => 'textarea',
                'rows'      => 6,
                'new_lines' => 'wpautop',
            ],
            [
                'key'          => 'field_membro_instagram',
                'label'        => 'Instagram',
                'name'         => 'membro_instagram',
                'type'         => 'url',
                'instructions' => 'Link completo, com https://',
            ],
            [
                'key'   => 'field_membro_tiktok',
                'label' => 'TikTok',
                'name'  => 'membro_tiktok',
                'type'  => 'url',
            ],
            [
                'key'   => 'field_membro_youtube',
                'label' => 'YouTube',
                'name'  => 'membro_youtube',
                'type'  => 'url',
            ],
        ],
    ]);

    acf_add_local_field_group([
        'key'      => 'group_fs_show',
        'title'    => 'Dados do Show',
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'fs_show']]],
        'fields'   => [
            [
                // Ymd porque front-page.php compara com date('Ymd') na meta_query.
                'key'           => 'field_show_data',
                'label'         => 'Data do show',
                'name'          => 'show_data',
                'type'          => 'date_picker',
                'required'      => 1,
                'display_format' => 'd/m/Y',
                'return_format'  => 'Ymd',
                'first_day'      => 0,
            ],
            [
                'key'          => 'field_show_local',
                'label'        => 'Local',
                'name'         => 'show_local',
                'type'         => 'text',
                'instructions' => 'Nome da casa, teatro ou evento.',
            ],
            [
                'key'          => 'field_show_cidade',
                'label'        => 'Cidade',
                'name'         => 'show_cidade',
                'type'         => 'text',
                'instructions' => 'Ex: Florianopolis, SC',
            ],
            [
                'key'          => 'field_show_link_ingresso',
                'label'        => 'Link dos ingressos',
                'name'         => 'show_link_ingresso',
                'type'         => 'url',
                'instructions' => 'Deixe vazio se nao houver venda de ingresso.',
            ],
        ],
    ]);

    acf_add_local_field_group([
        'key'      => 'group_fs_imprensa',
        'title'    => 'Dados da Imprensa',
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'fs_imprensa']]],
        'fields'   => [
            [
                'key'          => 'field_imprensa_veiculo',
                'label'        => 'Veiculo',
                'name'         => 'imprensa_veiculo',
                'type'         => 'text',
                'required'     => 1,
                'instructions' => 'Ex: SBT, Globo, Jornal NH.',
            ],
            [
                'key'            => 'field_imprensa_data',
                'label'          => 'Data da materia',
                'name'           => 'imprensa_data',
                'type'           => 'date_picker',
                'display_format' => 'd/m/Y',
                'return_format'  => 'Ymd',
                'first_day'      => 0,
            ],
            [
                'key'   => 'field_imprensa_link',
                'label' => 'Link da materia',
                'name'  => 'imprensa_link',
                'type'  => 'url',
            ],
            [
                // front-page.php le $logo['url'], portanto return_format array.
                'key'           => 'field_imprensa_logo',
                'label'         => 'Logo do veiculo',
                'name'          => 'imprensa_logo',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
                'instructions'  => 'Opcional. Sem logo, o site mostra o nome do veiculo.',
            ],
        ],
    ]);

    acf_add_local_field_group([
        'key'      => 'group_fs_parceiro',
        'title'    => 'Dados do Parceiro',
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'fs_parceiro']]],
        'fields'   => [
            [
                'key'          => 'field_parceiro_link',
                'label'        => 'Site do parceiro',
                'name'         => 'parceiro_link',
                'type'         => 'url',
                'instructions' => 'A logo do parceiro vem da Imagem destacada.',
            ],
        ],
    ]);
});
