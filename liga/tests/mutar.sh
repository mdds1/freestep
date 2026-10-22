#!/usr/bin/env bash
# Sabota pontuacao.php com uma expressao sed e exige que o teste falhe.
# uso: tests/mutar.sh '<expressao sed -E>' tests/arquivo.test.php [linhas-alteradas-esperadas]
# saida: 0 mutante morto (bom) | 1 mutante sobreviveu (teste fraco) | 2 sabotagem invalida
set -u
cd "$(dirname "$0")/.." || exit 2
alvo=pontuacao.php
copia=$(mktemp)
cp "$alvo" "$copia"
trap 'cp "$copia" "$alvo"; rm -f "$copia"' EXIT

sed -i -E "$1" "$alvo"

esperadas="${3:-1}"
mudou=$(diff "$copia" "$alvo" | grep -c '^>')
if [ "$mudou" -ne "$esperadas" ]; then
    echo "ABORTADO: a sabotagem alterou $mudou linha(s), esperava $esperadas"
    exit 2
fi
if ! php -l "$alvo" >/dev/null 2>&1; then
    echo "ABORTADO: a sabotagem quebrou a sintaxe; um erro de parse mataria qualquer teste por motivo errado"
    exit 2
fi
if php "$2" >/dev/null 2>&1; then
    echo "MUTANTE SOBREVIVEU: o teste passa com o codigo quebrado"
    exit 1
fi
echo "mutante morto: o teste falhou com o codigo quebrado"
exit 0
