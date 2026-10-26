<?php
/**
 * Leitura dos campos do midia kit que sao listas.
 *
 * O ACF Free nao tem campo repetidor, entao esses campos sao textarea com
 * um item por linha (ou separados por virgula). Estas funcoes transformam
 * isso em array, ignorando linha vazia e espaco sobrando, para o membro da
 * equipe poder digitar no painel sem se preocupar com formatacao.
 */

/**
 * Um item por linha.
 *
 * @return string[]
 */
function freestep_linhas($campo, $post_id)
{
    $bruto = (string) get_field($campo, $post_id);

    if (trim($bruto) === '') {
        return [];
    }

    $linhas = preg_split('/\r\n|\r|\n/', $bruto);
    $linhas = array_map('trim', $linhas);

    return array_values(array_filter($linhas, static fn ($l) => $l !== ''));
}

/**
 * Itens separados por virgula.
 *
 * @return string[]
 */
function freestep_itens($campo, $post_id)
{
    $bruto = (string) get_field($campo, $post_id);

    if (trim($bruto) === '') {
        return [];
    }

    $itens = array_map('trim', explode(',', $bruto));

    return array_values(array_filter($itens, static fn ($i) => $i !== ''));
}

/**
 * Linha no formato "rotulo | valor".
 *
 * Linha sem a barra vira item so com rotulo, para o painel nao obrigar a
 * sintaxe quando o item nao tem dois lados.
 *
 * @return array<int, array{rotulo: string, valor: string}>
 */
function freestep_pares($campo, $post_id)
{
    $pares = [];

    foreach (freestep_linhas($campo, $post_id) as $linha) {
        $partes = array_map('trim', explode('|', $linha, 2));

        $pares[] = [
            'rotulo' => $partes[0],
            'valor'  => $partes[1] ?? '',
        ];
    }

    return $pares;
}
