<?php
declare(strict_types=1);

$GLOBALS['falhas'] = 0;

function ok(string $oque, bool $cond): void {
    if (!$cond) { $GLOBALS['falhas']++; echo "  FALHOU: $oque\n"; }
    else { echo "  ok $oque\n"; }
}

// Valor ABSOLUTO esperado. Nunca compare duas implementacoes entre si.
function igual(string $oque, mixed $esperado, mixed $obtido): void {
    if ($esperado === $obtido) { echo "  ok $oque\n"; return; }
    $GLOBALS['falhas']++;
    echo "  FALHOU: $oque\n    esperado: " . var_export($esperado, true)
       . "\n    veio:     " . var_export($obtido, true) . "\n";
}

function lanca(string $oque, callable $f): void {
    try { $f(); }
    catch (InvalidArgumentException $e) { echo "  ok $oque\n"; return; }
    $GLOBALS['falhas']++;
    echo "  FALHOU: $oque (nao lancou InvalidArgumentException)\n";
}

function fim(): void {
    $n = $GLOBALS['falhas'];
    echo $n === 0 ? "\nTUDO VERDE\n" : "\n$n FALHA(S)\n";
    exit($n === 0 ? 0 : 1);
}
