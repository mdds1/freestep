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

/**
 * Funcao de todo o time hoje. Quando alguem tiver funcao propria (diretor,
 * produtor), vira uma chave 'funcao' por membro na lista abaixo.
 *
 * Com acento de proposito: e texto publicado, lido por imprensa e
 * contratante. O arquivo e UTF-8 e o banco e utf8mb4.
 */
const FREESTEP_FUNCAO_PADRAO = 'Dançarino / Coreógrafo';

/**
 * O time, na ordem do grid.
 *
 * 'handle' e o mesmo no Instagram e no TikTok para todos. Se algum dia um
 * membro divergir, o campo pode ser ajustado pelo painel: o helper em
 * inc/social.php omite a rede vazia em vez de renderizar um botao morto.
 */
$membros = [
    ['nome' => 'Alves',      'handle' => 'alvesofc'],
    ['nome' => 'Gu Henrique', 'handle' => 'guhenrique'],
    ['nome' => 'Jorg',       'handle' => 'jorg._'],
    ['nome' => 'Takeshi',    'handle' => 'eutakeshi'],
    ['nome' => 'Jux',        'handle' => 'juxcst'],
    ['nome' => 'Ederson',    'handle' => 'edersonsouza'],
    ['nome' => 'Jeff',        'handle' => 'jeff.tj'],
];

/**
 * Titulos de rodadas anteriores deste script, quando os membros ainda
 * entravam com o handle no lugar do nome. Some quando ninguem mais tiver
 * um site criado antes desta versao.
 */
$legados = [
    'Membro Exemplo 1', 'Membro Exemplo 2', 'Membro Exemplo 3',
    'Membro Exemplo 4', 'Membro Exemplo 5', 'Membro Exemplo 6',
    'alvesofc', 'edersonsouza', 'juxcst', 'takeshi', 'guhenrique', 'jorg._',
];

// get_page_by_title compara sem diferenciar maiuscula, entao o legado
// 'takeshi' casava com o membro real 'Takeshi' e o apagava a cada rodada,
// levando junto a foto destacada e qualquer edicao feita pelo painel.
$nomes_atuais = array_map(
    static fn ($m) => mb_strtolower($m['nome']),
    $membros
);

foreach ($legados as $titulo) {
    if (in_array(mb_strtolower($titulo), $nomes_atuais, true)) {
        continue;
    }

    $antigo = get_page_by_title($titulo, OBJECT, 'fs_membro');
    if ($antigo) {
        wp_delete_post($antigo->ID, true);
        echo "removido legado: {$titulo}", PHP_EOL;
    }
}

$ordem = 0;

foreach ($membros as $membro) {
    $ordem++;

    $existente = get_page_by_title($membro['nome'], OBJECT, 'fs_membro');

    if ($existente) {
        $id = $existente->ID;
        wp_update_post(['ID' => $id, 'menu_order' => $ordem]);
        echo "atualizado: {$membro['nome']} (ID {$id})", PHP_EOL;
    } else {
        $id = wp_insert_post([
            'post_type'   => 'fs_membro',
            'post_title'  => $membro['nome'],
            'post_status' => 'publish',
            'menu_order'  => $ordem,
        ]);

        if (is_wp_error($id)) {
            echo "ERRO em {$membro['nome']}: ", $id->get_error_message(), PHP_EOL;
            continue;
        }

        echo "criado: {$membro['nome']} (ID {$id})", PHP_EOL;
    }

    $handle = $membro['handle'];

    // rawurlencode nao serve aqui: ponto e underline (jorg._, jeff.tj) sao
    // validos em handle e escapar quebraria o link.
    update_field('membro_funcao', FREESTEP_FUNCAO_PADRAO, $id);
    update_field('membro_instagram', "https://instagram.com/{$handle}", $id);
    update_field('membro_tiktok', "https://tiktok.com/@{$handle}", $id);
}
