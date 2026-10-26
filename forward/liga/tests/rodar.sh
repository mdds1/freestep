#!/usr/bin/env bash
# Roda todos os testes da Liga. Sai com 1 se qualquer um falhar.
cd "$(dirname "$0")" || exit 2
ruim=0
for t in *.test.php; do
    [ -e "$t" ] || continue
    echo "== $t"
    php "$t" || ruim=1
done
[ "$ruim" -eq 0 ] && echo "SUITE VERDE" || echo "SUITE COM FALHA"
exit "$ruim"
