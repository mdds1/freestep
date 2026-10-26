<?php
declare(strict_types=1);
require __DIR__ . '/ajuda.php';
require __DIR__ . '/../pontuacao.php';

echo "distribuir_premiacao\n";
igual('R$ 1.000,00 divide em 400/250/150/100/100', [40000, 25000, 15000, 10000, 10000],
    distribuir_premiacao(100000));
igual('R$ 30,00 (uma inscricao)', [1200, 750, 450, 300, 300], distribuir_premiacao(3000));

// 1001 centavos: os pisos somam 1000, a sobra de 1 vai para o primeiro lugar
igual('a sobra de arredondamento vai para o primeiro', [401, 250, 150, 100, 100],
    distribuir_premiacao(1001));
igual('R$ 0 distribui zero', [0, 0, 0, 0, 0], distribuir_premiacao(0));

foreach ([1, 7, 99, 1001, 3000, 12345, 99999] as $total) {
    igual("a soma fecha exatamente o bolo de $total centavos", $total,
        array_sum(distribuir_premiacao($total)));
}
lanca('bolo negativo e erro', fn() => distribuir_premiacao(-1));

fim();
