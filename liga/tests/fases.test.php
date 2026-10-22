<?php
declare(strict_types=1);
require __DIR__ . '/ajuda.php';
require __DIR__ . '/../pontuacao.php';

echo "pontos_da_fase\n";
foreach ([5, 15, 21, 27, 35, 45, 55] as $f) {
    igual("fase $f vale $f", $f, pontos_da_fase($f));
}
foreach ([0, 1, 10, 56, -5] as $f) {
    lanca("fase invalida $f e erro, nunca zero", fn() => pontos_da_fase($f));
}

echo "fase_valida_na_chave\n";
igual('oitavas existem em chave de 16', true,  fase_valida_na_chave(21, 16));
igual('oitavas existem em chave de 32', true,  fase_valida_na_chave(21, 32));
igual('oitavas NAO existem em chave de 8', false, fase_valida_na_chave(21, 8));
igual('oitavas NAO existem em chave de 4', false, fase_valida_na_chave(21, 4));
igual('quartas existem em chave de 8',  true,  fase_valida_na_chave(27, 8));
igual('quartas NAO existem em chave de 4', false, fase_valida_na_chave(27, 4));
foreach ([4, 8, 16, 32] as $t) {
    foreach ([5, 15, 35, 45, 55] as $f) {
        igual("fase $f vale em chave de $t", true, fase_valida_na_chave($f, $t));
    }
}
lanca('tamanho de chave 6 nao existe', fn() => fase_valida_na_chave(35, 6));
lanca('fase invalida na chave e erro', fn() => fase_valida_na_chave(10, 8));

echo "validar_distribuicao_de_fases\n";
igual('chave de 8 completa e sem erro', [],
    validar_distribuicao_de_fases(
        [1 => 55, 2 => 45, 3 => 35, 4 => 35, 5 => 27, 6 => 27, 7 => 27, 8 => 27], 8));
igual('evento em andamento (so o campeao lancado) e aceito', [],
    validar_distribuicao_de_fases([1 => 55], 8));
igual('dois campeoes e erro', 1,
    count(validar_distribuicao_de_fases([1 => 55, 2 => 55], 8)));
igual('dois vices e erro', 1,
    count(validar_distribuicao_de_fases([1 => 45, 2 => 45], 8)));
igual('tres semifinalistas e erro', 1,
    count(validar_distribuicao_de_fases([1 => 35, 2 => 35, 3 => 35], 8)));
igual('cinco nas quartas e erro', 1,
    count(validar_distribuicao_de_fases([1 => 27, 2 => 27, 3 => 27, 4 => 27, 5 => 27], 8)));
igual('nove nas oitavas e erro', 1,
    count(validar_distribuicao_de_fases(array_fill(1, 9, 21), 16)));
igual('oitavas em chave de 8 e erro por participante', 1,
    count(validar_distribuicao_de_fases([1 => 21], 8)));
igual('fase fora da tabela vira mensagem, nao excecao', 1,
    count(validar_distribuicao_de_fases([1 => 10], 8)));
foreach (['55', null, 5.5, true] as $v) {
    $r = validar_distribuicao_de_fases([1 => $v], 8);
    igual('fase ' . var_export($v, true) . ' vira uma mensagem, sem TypeError', 1, count($r));
}
$msg = validar_distribuicao_de_fases([1 => 55, 2 => 55], 8)[0];
ok('a mensagem diz qual fase estourou', str_contains($msg, '55'));

fim();
