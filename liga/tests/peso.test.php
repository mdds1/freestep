<?php
declare(strict_types=1);
require __DIR__ . '/ajuda.php';
require __DIR__ . '/../pontuacao.php';

echo "peso_do_evento\n";
igual('oficial vale 100', 100, peso_do_evento('oficial', null));
igual('oficial ignora o numero de inscritos', 100, peso_do_evento('oficial', 3));

// as bordas exatas do regulamento
igual('parceiro com 0 inscritos: 50',  50,  peso_do_evento('parceiro', 0));
igual('parceiro com 15 inscritos: 50', 50,  peso_do_evento('parceiro', 15));
igual('parceiro com 16 inscritos: 75', 75,  peso_do_evento('parceiro', 16));
igual('parceiro com 31 inscritos: 75', 75,  peso_do_evento('parceiro', 31));
igual('parceiro com 32 inscritos: 100', 100, peso_do_evento('parceiro', 32));
igual('parceiro com 200 inscritos: 100', 100, peso_do_evento('parceiro', 200));

lanca('parceiro sem numero de inscritos e erro', fn() => peso_do_evento('parceiro', null));
lanca('inscritos negativo e erro', fn() => peso_do_evento('parceiro', -1));
lanca('tipo desconhecido e erro', fn() => peso_do_evento('torneio', 40));

fim();
