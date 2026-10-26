<?php
declare(strict_types=1);
require __DIR__ . '/ajuda.php';
require __DIR__ . '/../pontuacao.php';

function linha(int $id, string $nome, int $pontos, string $tipo = 'oficial',
               bool $conf = true, bool $aprov = true): array {
    return ['dancarino_id' => $id, 'nome' => $nome, 'crew' => null, 'pontos' => $pontos,
            'tipo_evento' => $tipo, 'confirmado' => $conf, 'evento_aprovado' => $aprov];
}

echo "tier_da_posicao: cada corte, nas bordas\n";
foreach ([1 => 'S', 5 => 'S', 6 => 'A', 10 => 'A', 11 => 'B', 20 => 'B', 21 => 'C', 80 => 'C'] as $pos => $t) {
    igual("posicao $pos e tier $t", $t, tier_da_posicao($pos));
}
lanca('posicao 0 e erro', fn() => tier_da_posicao(0));

echo "montar_ranking: soma\n";
$r = montar_ranking([
    linha(1, 'Ana', 55), linha(1, 'Ana', 27, 'parceiro'), linha(2, 'Beto', 45),
]);
igual('Ana soma 82 e lidera', 82, $r[0]['pontos']);
igual('o primeiro e a Ana', 1, $r[0]['dancarino_id']);
igual('o segundo e o Beto com 45', 45, $r[1]['pontos']);
igual('posicao 1 e tier S', ['S', 1], [$r[0]['tier'], $r[0]['posicao']]);
igual('posicao 2', 2, $r[1]['posicao']);
igual('Ana tem 1 participacao oficial (a de parceiro nao conta)', 1, $r[0]['participacoes_oficiais']);

echo "montar_ranking: elegibilidade\n";
$r = montar_ranking([
    linha(1, 'Ana', 55),
    linha(2, 'Pendente', 99, 'oficial', false, true),
    linha(3, 'EventoNegado', 99, 'oficial', true, false),
]);
igual('so a Ana aparece', 1, count($r));
igual('e e a Ana', 1, $r[0]['dancarino_id']);
$r = montar_ranking([
    linha(1, 'Ana', 10),
    linha(1, 'Ana', 40, 'parceiro', true, false),
]);
igual('resultado de evento nao aprovado nao soma', 10, $r[0]['pontos']);

echo "montar_ranking: desempate por participacao OFICIAL\n";
// Ceu entra primeiro e tem MENOS participacoes oficiais; precisa perder.
$r = montar_ranking([
    linha(1, 'Ceu',  30, 'parceiro'), linha(1, 'Ceu', 20, 'parceiro'),
    linha(2, 'Dora', 25, 'oficial'),  linha(2, 'Dora', 25, 'oficial'),
]);
igual('Dora (2 oficiais) fica a frente da Ceu (0) com 50 pontos iguais', 2, $r[0]['dancarino_id']);
igual('Dora tem 2 participacoes oficiais', 2, $r[0]['participacoes_oficiais']);
igual('Ceu tem 0', 0, $r[1]['participacoes_oficiais']);
igual('nao empatam: posicoes 1 e 2', [1, 2], [$r[0]['posicao'], $r[1]['posicao']]);

echo "montar_ranking: empate completo divide a posicao\n";
$r = montar_ranking([linha(7, 'Zeca', 30), linha(8, 'Ana', 30), linha(9, 'Caio', 10)]);
igual('empatados na posicao 1', [1, 1], [$r[0]['posicao'], $r[1]['posicao']]);
igual('o seguinte e o terceiro', 3, $r[2]['posicao']);
igual('dentro do empate, ordem por nome', ['Ana', 'Zeca'], [$r[0]['nome'], $r[1]['nome']]);

echo "montar_ranking: tier segue a posicao\n";
$linhas = [];
for ($i = 1; $i <= 21; $i++) { $linhas[] = linha($i, sprintf('D%02d', $i), 1000 - $i); }
$r = montar_ranking($linhas);
igual('posicao 5 e S', 'S', $r[4]['tier']);
igual('posicao 6 e A', 'A', $r[5]['tier']);
igual('posicao 11 e B', 'B', $r[10]['tier']);
igual('posicao 21 e C', 'C', $r[20]['tier']);

igual('sem linhas, ranking vazio', [], montar_ranking([]));

echo "montar_ranking: validacao da entrada\n";
$base = linha(1, 'Ana', 10);
foreach (array_keys($base) as $chave) {
    $l = $base; unset($l[$chave]);
    lanca("faltando a chave $chave e erro", fn() => montar_ranking([$l]));
}
$c = $base; $c['crew'] = null;
igual('crew null com a chave presente e aceito', 10, montar_ranking([$c])[0]['pontos']);
foreach (['confirmado', 'evento_aprovado'] as $k) {
    foreach (['f', 't', '0', 1, 0, null] as $v) {
        $l = $base; $l[$k] = $v;
        lanca("$k = " . var_export($v, true) . " nao e bool", fn() => montar_ranking([$l]));
    }
}
foreach (['Oficial', 'outro', '', null] as $v) {
    $l = $base; $l['tipo_evento'] = $v;
    lanca('tipo_evento ' . var_export($v, true) . ' e erro', fn() => montar_ranking([$l]));
}
foreach ([-1, '5', 5.0, null] as $v) {
    $l = $base; $l['pontos'] = $v;
    lanca('pontos ' . var_export($v, true) . ' e erro', fn() => montar_ranking([$l]));
}
$l = $base; $l['pontos'] = 0;
igual('pontos zero e aceito', 0, montar_ranking([$l])[0]['pontos']);
$l = linha(1, 'Ana', 10, 'oficial', false, true); $l['pontos'] = -3;
lanca('pontos negativos em linha nao elegivel tambem e erro', fn() => montar_ranking([$l]));

fim();
