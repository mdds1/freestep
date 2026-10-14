<?php
/**
 * Cria os membros de exemplo do grid do time.
 *
 * Idempotente: roda de novo sem duplicar, porque procura pelo titulo antes
 * de criar. Os nomes sao marcados como exemplo de proposito, para a equipe
 * saber exatamente o que substituir.
 */

$exemplos = [
    [
        'nome'   => 'Membro Exemplo 1',
        'funcao' => 'Diretor e Coreografo',
        'bio'    => 'Texto de exemplo. Substitua pela biografia real do membro: trajetoria no Free Step, principais apresentacoes e o que ele traz para o time.',
        'redes'  => [
            'instagram' => 'https://instagram.com/timefreestepbrasil',
            'tiktok'    => 'https://tiktok.com/@timefreestepbrasil',
            'facebook'  => 'https://facebook.com/timefreestepbrasil',
        ],
    ],
    [
        'nome'   => 'Membro Exemplo 2',
        'funcao' => 'Dancarino',
        'bio'    => 'Texto de exemplo. Substitua pela biografia real do membro.',
        'redes'  => [
            'instagram' => 'https://instagram.com/timefreestepbrasil',
            'tiktok'    => 'https://tiktok.com/@timefreestepbrasil',
        ],
    ],
    [
        'nome'   => 'Membro Exemplo 3',
        'funcao' => 'Dancarina',
        'bio'    => 'Texto de exemplo. Substitua pela biografia real do membro.',
        'redes'  => [
            'instagram' => 'https://instagram.com/timefreestepbrasil',
            'facebook'  => 'https://facebook.com/timefreestepbrasil',
        ],
    ],
    [
        'nome'   => 'Membro Exemplo 4',
        'funcao' => 'Dancarino',
        'bio'    => 'Texto de exemplo. Substitua pela biografia real do membro.',
        'redes'  => [
            'instagram' => 'https://instagram.com/timefreestepbrasil',
            'tiktok'    => 'https://tiktok.com/@timefreestepbrasil',
            'facebook'  => 'https://facebook.com/timefreestepbrasil',
        ],
    ],
    [
        'nome'   => 'Membro Exemplo 5',
        'funcao' => 'Dancarina',
        'bio'    => 'Texto de exemplo. Substitua pela biografia real do membro.',
        'redes'  => [
            'instagram' => 'https://instagram.com/timefreestepbrasil',
            'tiktok'    => 'https://tiktok.com/@timefreestepbrasil',
        ],
    ],
    [
        'nome'   => 'Membro Exemplo 6',
        'funcao' => 'Dancarino',
        'bio'    => 'Texto de exemplo. Substitua pela biografia real do membro.',
        'redes'  => [
            'instagram' => 'https://instagram.com/timefreestepbrasil',
            'facebook'  => 'https://facebook.com/timefreestepbrasil',
        ],
    ],
];

$ordem = 0;

foreach ($exemplos as $e) {
    $ordem++;

    $existente = get_page_by_title($e['nome'], OBJECT, 'fs_membro');
    if ($existente) {
        echo "ja existe: {$e['nome']} (ID {$existente->ID})", PHP_EOL;
        continue;
    }

    $id = wp_insert_post([
        'post_type'   => 'fs_membro',
        'post_title'  => $e['nome'],
        'post_status' => 'publish',
        'menu_order'  => $ordem,
    ]);

    if (is_wp_error($id)) {
        echo "ERRO em {$e['nome']}: ", $id->get_error_message(), PHP_EOL;
        continue;
    }

    update_field('membro_funcao', $e['funcao'], $id);
    update_field('membro_bio', $e['bio'], $id);

    foreach ($e['redes'] as $rede => $url) {
        update_field("membro_{$rede}", $url, $id);
    }

    echo "criado: {$e['nome']} (ID {$id})", PHP_EOL;
}
