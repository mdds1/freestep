<?php
declare(strict_types=1);
require __DIR__ . '/ajuda.php';
require __DIR__ . '/../pontuacao.php';

echo "bonus_da_seletiva\n";
igual('sem nota lancada, ninguem recebe', [], bonus_da_seletiva([]));
igual('um maior recebe', [2], bonus_da_seletiva([1 => 7.5, 2 => 9.0, 3 => 8.25]));
igual('empate na maior: todos recebem', [1, 3], bonus_da_seletiva([1 => 9, 2 => 8, 3 => 9]));
igual('nota inteira e decimal empatam', [1, 2], bonus_da_seletiva([1 => 9, 2 => 9.0]));
igual('um unico participante recebe', [5], bonus_da_seletiva([5 => 0]));
igual('ids fora de ordem saem ordenados', [3, 7], bonus_da_seletiva([9 => 8, 3 => 9, 7 => 9]));
igual('vencedores com ids decrescentes saem ordenados', [3, 7, 9], bonus_da_seletiva([9 => 9, 7 => 9, 3 => 9, 5 => 1]));
lanca('nota que nao e numero e erro', fn() => bonus_da_seletiva([1 => 'alta']));
lanca('NAN primeiro e erro', fn() => bonus_da_seletiva([1 => NAN, 2 => 9.0]));
lanca('NAN depois e erro', fn() => bonus_da_seletiva([2 => 9.0, 1 => NAN]));
lanca('INF primeiro e erro', fn() => bonus_da_seletiva([1 => INF, 2 => 9.0]));
lanca('INF depois e erro', fn() => bonus_da_seletiva([2 => 9.0, 1 => INF]));
lanca('-INF e erro', fn() => bonus_da_seletiva([1 => -INF, 2 => 9.0]));

echo "calcular_pontos: peso 100\n";
igual('campeao oficial', 55, calcular_pontos(55, 100, false));
igual('quartas oficial', 27, calcular_pontos(27, 100, false));
igual('inscricao oficial', 5, calcular_pontos(5, 100, false));

echo "calcular_pontos: arredondamento meio para cima (valores absolutos)\n";
igual('45 x 75% = 33,75 -> 34', 34, calcular_pontos(45, 75, false));
igual('55 x 50% = 27,5 -> 28',  28, calcular_pontos(55, 50, false));
igual('55 x 75% = 41,25 -> 41', 41, calcular_pontos(55, 75, false));
igual('35 x 75% = 26,25 -> 26', 26, calcular_pontos(35, 75, false));
igual('27 x 75% = 20,25 -> 20', 20, calcular_pontos(27, 75, false));
igual('21 x 75% = 15,75 -> 16', 16, calcular_pontos(21, 75, false));
igual('15 x 75% = 11,25 -> 11', 11, calcular_pontos(15, 75, false));
igual('5 x 75% = 3,75 -> 4',     4, calcular_pontos(5, 75, false));
igual('45 x 50% = 22,5 -> 23',  23, calcular_pontos(45, 50, false));
igual('35 x 50% = 17,5 -> 18',  18, calcular_pontos(35, 50, false));
igual('27 x 50% = 13,5 -> 14',  14, calcular_pontos(27, 50, false));
igual('21 x 50% = 10,5 -> 11',  11, calcular_pontos(21, 50, false));
igual('15 x 50% = 7,5 -> 8',     8, calcular_pontos(15, 50, false));
igual('5 x 50% = 2,5 -> 3',      3, calcular_pontos(5, 50, false));

echo "calcular_pontos: bonificacao\n";
igual('o bonus soma 5 em peso 100', 32, calcular_pontos(27, 100, true));
// calibragem do regulamento: lider da seletiva que cai nas oitavas soma 26
igual('lider da seletiva nas oitavas soma 26', 26, calcular_pontos(21, 100, true));
igual('26 continua atras dos 27 de quem chegou as quartas',
    true, calcular_pontos(21, 100, true) < calcular_pontos(27, 100, false));
// o bonus entra ANTES do peso: (21+5) x 75% = 19,5 -> 20
// (depois do peso seria 15,75 -> 16, mais 5 = 21)
igual('o bonus e multiplicado pelo peso, nao somado depois', 20, calcular_pontos(21, 75, true));
igual('bonus em peso 50: (55+5) x 50% = 30', 30, calcular_pontos(55, 50, true));

lanca('fase invalida e erro', fn() => calcular_pontos(10, 100, false));
lanca('peso fora de 100/75/50 e erro', fn() => calcular_pontos(55, 80, false));

fim();
