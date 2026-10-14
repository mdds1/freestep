<?php
/**
 * Cadastra os membros do time a partir dos handles de rede social.
 *
 * Substitui os "Membro Exemplo N" criados antes de os handles reais
 * chegarem. Idempotente: procura pelo titulo antes de criar, entao rodar
 * de novo nao duplica, so atualiza os campos.
 *
 * O titulo de exibicao e o proprio handle porque so temos o handle. Quando
 * a equipe passar os nomes e as funcoes reais, editar pelo painel
 * (Membros > editar) ou ajustar 'nome' e 'funcao' aqui e rodar de novo.
 *
 * Rodar com:
 *   sudo php wp-cli.phar --allow-root --path=/var/www/freestep \
 *     eval-file ferramentas/membros.php
 */

/** Handles iguais no Instagram e no TikTok, na ordem do grid. */
$handles = [
    'alvesofc',
    'edersonsouza',
    'juxcst',
    'takeshi',
    'guhenrique',
    'jorg._',
];

// Limpa os exemplos de antes dos handles reais.
for ($i = 1; $i <= 6; $i++) {
    $exemplo = get_page_by_title("Membro Exemplo {$i}", OBJECT, 'fs_membro');
    if ($exemplo) {
        wp_delete_post($exemplo->ID, true);
        echo "removido exemplo: Membro Exemplo {$i}", PHP_EOL;
    }
}

$ordem = 0;

foreach ($handles as $handle) {
    $ordem++;

    $existente = get_page_by_title($handle, OBJECT, 'fs_membro');

    if ($existente) {
        $id = $existente->ID;
        wp_update_post(['ID' => $id, 'menu_order' => $ordem]);
        echo "atualizado: {$handle} (ID {$id})", PHP_EOL;
    } else {
        $id = wp_insert_post([
            'post_type'   => 'fs_membro',
            'post_title'  => $handle,
            'post_status' => 'publish',
            'menu_order'  => $ordem,
        ]);

        if (is_wp_error($id)) {
            echo "ERRO em {$handle}: ", $id->get_error_message(), PHP_EOL;
            continue;
        }

        echo "criado: {$handle} (ID {$id})", PHP_EOL;
    }

    // rawurlencode nao serve aqui: o ponto e o underline de jorg._ sao
    // validos em handle e nao devem ser escapados.
    update_field('membro_instagram', "https://instagram.com/{$handle}", $id);
    update_field('membro_tiktok', "https://tiktok.com/@{$handle}", $id);
}
