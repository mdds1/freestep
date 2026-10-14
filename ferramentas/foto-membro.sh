#!/usr/bin/env bash
# Define a foto destacada de um membro.
#
# Uso: ferramentas/foto-membro.sh <nome-do-membro> <arquivo-de-imagem>
#   ferramentas/foto-membro.sh Takeshi ~/fotos/takeshi.jpg
#
# Guarda uma copia em assets/fotos/ antes de importar, porque as imagens
# costumam chegar pelo scratchpad em /tmp, que nao sobrevive a reboot nesta
# VM. A copia no repositorio e a fonte para remontar o site do zero.
#
# Idempotente: rodar de novo troca a foto destacada pela nova.

set -euo pipefail

NOME="${1:-}"
ARQUIVO="${2:-}"

if [[ -z "$NOME" || -z "$ARQUIVO" ]]; then
    echo "uso: $0 <nome-do-membro> <arquivo-de-imagem>" >&2
    exit 1
fi

if [[ ! -f "$ARQUIVO" ]]; then
    echo "erro: arquivo nao encontrado: $ARQUIVO" >&2
    exit 1
fi

REPO="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WP="/var/www/freestep"
CLI="${FREESTEP_WP_CLI:-$REPO/ferramentas/wp-cli.phar}"

if [[ ! -f "$CLI" ]]; then
    echo "erro: wp-cli nao encontrado em $CLI" >&2
    echo "defina FREESTEP_WP_CLI ou baixe para ferramentas/wp-cli.phar" >&2
    exit 1
fi

# Slug a partir do nome: minusculo, sem acento, espaco vira hifen.
SLUG="$(echo "$NOME" \
    | iconv -f utf-8 -t ascii//TRANSLIT \
    | tr '[:upper:]' '[:lower:]' \
    | tr -cs 'a-z0-9' '-' \
    | sed 's/^-//; s/-$//')"

EXT="${ARQUIVO##*.}"
DESTINO="$REPO/assets/fotos/${SLUG}.${EXT,,}"

mkdir -p "$REPO/assets/fotos"
cp "$ARQUIVO" "$DESTINO"
chmod 644 "$DESTINO"
echo "guardado em assets/fotos/${SLUG}.${EXT,,}"

ID="$(sudo php "$CLI" --allow-root --path="$WP" post list \
    --post_type=fs_membro --title="$NOME" --field=ID)"

if [[ -z "$ID" ]]; then
    echo "erro: nenhum membro com o titulo '$NOME'" >&2
    echo "membros cadastrados:" >&2
    sudo php "$CLI" --allow-root --path="$WP" post list \
        --post_type=fs_membro --fields=ID,post_title >&2
    exit 1
fi

sudo php "$CLI" --allow-root --path="$WP" media import "$DESTINO" \
    --post_id="$ID" --featured_image --title="$NOME"

echo "foto destacada definida para $NOME (post $ID)"
