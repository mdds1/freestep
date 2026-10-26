# Perfil padronizado do membro — plano de implementacao

> **Para agentes:** SUB-SKILL OBRIGATORIA: use superpowers:subagent-driven-development
> (recomendado) ou superpowers:executing-plans para executar tarefa a tarefa.
> Os passos usam caixa de marcacao (`- [ ]`).

**Objetivo:** fazer os sete perfis de membro exibirem os mesmos topicos, na
mesma ordem, a partir de um esquema unico, sem gerar texto novo.

**Arquitetura:** um array em `inc/perfil-schema.php` define os topicos do
perfil. Esse array alimenta tres consumidores e nenhum outro: o registro dos
campos no ACF, o template da pagina do membro e os rotulos do painel. Como a
ordem e lida de um unico lugar, nao existe caminho no codigo onde um perfil
divirja do outro.

**Pilha:** WordPress 7.1.3, tema filho sobre GeneratePress 3.6.1, ACF Free,
PHP 8, WP-CLI via `wp-cli.phar`.

**Spec:** `docs/specs/2026-10-14-perfil-padronizado-design.md`

## Restricoes globais

- Site em `/var/www/freestep/`, porta 8081, servido em `http://192.168.15.6:8081/`
- Fonte de verdade do codigo: `~/projetos/freestep/wp-content/`. Deploy e
  `rsync -a --delete` para o docroot mais `chown www-data:www-data`
- Sem plugin pago. ACF Free **nao tem campo repetidor**: lista e textarea
  com um item por linha
- Texto publicado e fiel ao documento do membro, com acentuacao correta.
  Nao reescrever, nao resumir, nao tirar acento
- So entra o que e da danca. Formacao academica fora da area, emprego fora
  da area e qualquer dado pessoal (endereco, telefone, email particular,
  data de nascimento, estado civil) ficam de fora
- Conquista obtida com outro grupo (Los Cocas, 7Minutoz) fica no perfil
  individual, nunca na secao de imprensa do time
- `docs/fontes/` nunca vai para o docroot: tem dado pessoal
- Commits em Conventional Commit, em portugues, sem em-dash e sem emoji
- Nunca empurrar para `origin/main`

## Estrutura de arquivos

| Arquivo | Responsabilidade |
|---|---|
| `wp-content/themes/freestep-child/inc/perfil-schema.php` | **Criar.** Define os topicos do perfil. Unica fonte de estrutura |
| `wp-content/themes/freestep-child/inc/listas.php` | Ja existe. Le os tipos de lista. Sem mudanca |
| `wp-content/themes/freestep-child/inc/acf-fields.php` | **Modificar.** Grupo do membro passa a ser gerado do esquema |
| `wp-content/themes/freestep-child/single-fs_membro.php` | **Modificar.** Percorre o esquema em vez de ter um bloco por topico |
| `wp-content/themes/freestep-child/front-page.php` | **Modificar.** Card passa a ler `membro_papel` |
| `wp-content/themes/freestep-child/functions.php` | **Modificar.** `require` do esquema |
| `wp-content/themes/freestep-child/assets/css/sections/midiakit.css` | **Modificar.** Estilo do aviso de pendencia |
| `ferramentas/verificar-perfis.sh` | **Criar.** As tres checagens |
| `ferramentas/portfolio.php` | **Modificar.** Preenche os 14 topicos |
| `ferramentas/biografias.php` | **Remover.** Absorvido pelo `portfolio.php` |
| `docs/fontes/` | Ja existe. Texto dos documentos, para conferencia |

---

### Task 1: Verificador das tres checagens

Primeiro o teste, antes da implementacao. Rodando agora ele **tem que
falhar**, porque hoje os perfis nao sao padronizados. Se passar de primeira,
o verificador esta errado e nao serve.

**Arquivos:**
- Criar: `ferramentas/verificar-perfis.sh`

**Interfaces:**
- Produz: script executavel que sai com codigo 0 se as tres checagens
  passam, 1 caso contrario. Imprime o que falhou.

- [ ] **Passo 1: escrever o verificador**

```bash
#!/usr/bin/env bash
# Tres checagens do perfil padronizado. Ver
# docs/specs/2026-10-14-perfil-padronizado-design.md
#
# Uso: ferramentas/verificar-perfis.sh
# Variaveis: FREESTEP_URL, FREESTEP_USER, FREESTEP_PASS

set -uo pipefail

BASE="${FREESTEP_URL:-http://192.168.15.6:8081}"
USUARIO="${FREESTEP_USER:-freestep_admin}"
SENHA="${FREESTEP_PASS:?defina FREESTEP_PASS com a senha do admin}"

MEMBROS=(alves gu-henrique jorg takeshi jux ederson jeff)

# Ordem canonica dos topicos do nucleo. Tem que bater com
# inc/perfil-schema.php; divergir aqui e o verificador mentir.
NUCLEO="release base carreira papel alcance formacao titulos internacional tv marcas ensino"

TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT
FALHAS=0

falhar() { echo "FALHA: $*" >&2; FALHAS=$((FALHAS + 1)); }

# --- login, para as checagens autenticadas
JAR="$TMP/cookies"
curl -s -c "$JAR" -d "log=${USUARIO}&pwd=${SENHA}&wp-submit=Acessar&testcookie=1" \
     "$BASE/wp-login.php" -o /dev/null
if ! grep -q 'wordpress_logged_in' "$JAR"; then
    echo "ERRO: login falhou; as checagens 1 e 2 nao podem rodar" >&2
    exit 1
fi

# Sequencia de topicos emitida numa pagina, na ordem em que aparecem.
topicos() { grep -o 'data-topico="[a-z]*"' "$1" | sed 's/.*"\(.*\)"/\1/' | tr '\n' ' '; }

# --- Checagem 1: autenticado, todo perfil emite o nucleo inteiro na ordem
echo "== 1. padronizacao (autenticado)"
for m in "${MEMBROS[@]}"; do
    curl -s -b "$JAR" "$BASE/membro/$m/" -o "$TMP/auth-$m.html"
    seq_nucleo="$(topicos "$TMP/auth-$m.html" | tr ' ' '\n' \
        | grep -Fx -f <(echo "$NUCLEO" | tr ' ' '\n') | tr '\n' ' ')"
    esperado="$(echo "$NUCLEO" | tr -s ' ') "
    if [ "$seq_nucleo" != "$esperado" ]; then
        falhar "$m emitiu [$seq_nucleo] e nao [$esperado]"
    fi
done

# --- Checagem 2: anonimo nao ve aviso de pendencia; autenticado ve
echo "== 2. aviso de pendencia nao vaza"
VAZOU=0; APARECEU=0
for m in "${MEMBROS[@]}"; do
    curl -s "$BASE/membro/$m/" -o "$TMP/anon-$m.html"
    grep -q 'fs-pendente' "$TMP/anon-$m.html" && { falhar "$m mostra aviso para anonimo"; VAZOU=1; }
    grep -q 'fs-pendente' "$TMP/auth-$m.html" && APARECEU=1
done
# Se o aviso nao aparece nem para quem esta logado, a checagem acima nao
# prova nada: ela passaria com o recurso inteiro ausente.
if [ "$APARECEU" -eq 0 ]; then
    falhar "nenhum perfil mostrou aviso autenticado; a checagem 2 nao esta valendo"
fi

# --- Checagem 3: dado pessoal dos documentos nao aparece no HTML publicado
echo "== 3. dado pessoal nao vazou"
SEGREDOS=(
  "98498-0042" "mralvesofc@gmail.com" "Emilio Mallet" "28/10/1996"
  "94871-1891" "gustavohenrikecreator@gmail.com" "04/08/1995"
  "98890-8765" "eutakeshi@outlook.com"
  "jeffthejokers@gmail.com"
)
for m in "${MEMBROS[@]}"; do
    for s in "${SEGREDOS[@]}"; do
        grep -qF "$s" "$TMP/anon-$m.html" && falhar "$m expos '$s'"
    done
done

echo
if [ "$FALHAS" -eq 0 ]; then
    echo "OK: as tres checagens passaram"
    exit 0
fi
echo "$FALHAS falha(s)"
exit 1
```

- [ ] **Passo 2: tornar executavel e rodar, esperando falha**

```bash
chmod +x ferramentas/verificar-perfis.sh
FREESTEP_PASS='C!hShG%d&7vOH#%bz5' ferramentas/verificar-perfis.sh
```

Esperado: **FALHA** na checagem 1, porque hoje o template nao emite
`data-topico` nenhum, e na checagem 2, porque o aviso de pendencia ainda nao
existe. Se passar, o verificador esta quebrado: conferir que o login
funcionou e que `topicos()` casa com o HTML real.

- [ ] **Passo 3: commit**

```bash
git add ferramentas/verificar-perfis.sh
git commit -m "test(perfil): verificador de padronizacao, vazamento e dado pessoal

Escrito antes da implementacao e conferido falhando: hoje o template nao
emite data-topico e o aviso de pendencia nao existe.

A checagem 2 verifica tambem que o aviso aparece autenticado. Sem isso ela
passaria com o recurso inteiro ausente, que e o modo classico de um teste
nao testar nada.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 2: Esquema unico do perfil

**Arquivos:**
- Criar: `wp-content/themes/freestep-child/inc/perfil-schema.php`
- Modificar: `wp-content/themes/freestep-child/functions.php`

**Interfaces:**
- Produz: `freestep_perfil_schema(): array` — lista ordenada de topicos.
  Cada item tem `chave` (string), `rotulo` (string), `tipo` (um de `linha`,
  `texto`, `lista`, `itens`, `pares`), `nucleo` (bool), `instrucoes`
  (string) e `exemplo` (string).
- Produz: `freestep_perfil_campo(string $chave): string` — devolve
  `membro_<chave>`, o nome do campo no ACF.

- [ ] **Passo 1: escrever o esquema**

```php
<?php
/**
 * Esquema do perfil do membro: a unica fonte da estrutura.
 *
 * Tres consumidores leem daqui e nenhum outro lugar decide estrutura de
 * perfil: o registro dos campos em inc/acf-fields.php, o template
 * single-fs_membro.php e os rotulos do painel. Como a ordem sai de um
 * unico array, nao existe caminho no codigo onde um perfil divirja do
 * outro, que e o problema que este esquema existe para resolver.
 *
 * tipo: linha  -> campo text, uma linha
 *       texto  -> textarea com wpautop, paragrafos
 *       lista  -> textarea, um item por linha
 *       itens  -> textarea, separados por virgula
 *       pares  -> textarea, "rotulo | valor" por linha
 *
 * nucleo: true  -> todo membro deveria ter; vazio vira pendencia
 *         false -> so alguns tem; vazio nao cobra nada
 */

function freestep_perfil_schema()
{
    return [
        [
            'chave'      => 'release',
            'rotulo'     => 'Release',
            'tipo'       => 'texto',
            'nucleo'     => true,
            'instrucoes' => 'Texto de apresentação, fiel ao que o membro escreveu no próprio material.',
            'exemplo'    => '',
        ],
        [
            'chave'      => 'base',
            'rotulo'     => 'Base',
            'tipo'       => 'linha',
            'nucleo'     => true,
            'instrucoes' => 'Cidade e estado.',
            'exemplo'    => 'São Paulo, SP',
        ],
        [
            'chave'      => 'carreira',
            'rotulo'     => 'Carreira',
            'tipo'       => 'linha',
            'nucleo'     => true,
            'instrucoes' => 'Desde quando dança, ou quantos anos de carreira.',
            'exemplo'    => 'Na dança desde 2009',
        ],
        [
            'chave'      => 'papel',
            'rotulo'     => 'Papel no time',
            'tipo'       => 'linha',
            'nucleo'     => true,
            'instrucoes' => 'Como aparece no grid da home.',
            'exemplo'    => 'Líder do Time',
        ],
        [
            'chave'      => 'alcance',
            'rotulo'     => 'Alcance nas redes',
            'tipo'       => 'pares',
            'nucleo'     => true,
            'instrucoes' => 'Uma rede por linha, no formato Rede | Seguidores.',
            'exemplo'    => "TikTok | 2,3 milhões\nInstagram | 486 mil",
        ],
        [
            'chave'      => 'formacao',
            'rotulo'     => 'Formação em dança',
            'tipo'       => 'lista',
            'nucleo'     => true,
            'instrucoes' => 'Uma por linha. Só formação de dança: curso fora da área não entra.',
            'exemplo'    => 'Pós-graduação em Danças Urbanas',
        ],
        [
            'chave'      => 'titulos',
            'rotulo'     => 'Títulos e premiações',
            'tipo'       => 'lista',
            'nucleo'     => true,
            'instrucoes' => 'Uma por linha, com ano e local quando houver.',
            'exemplo'    => '1º lugar Campeonato Internacional de Free Step, Lima, Peru, 2025',
        ],
        [
            'chave'      => 'internacional',
            'rotulo'     => 'Atuação internacional',
            'tipo'       => 'itens',
            'nucleo'     => true,
            'instrucoes' => 'Países separados por vírgula.',
            'exemplo'    => 'Espanha, Peru',
        ],
        [
            'chave'      => 'tv',
            'rotulo'     => 'Programas de TV',
            'tipo'       => 'pares',
            'nucleo'     => true,
            'instrucoes' => 'Um por linha, no formato Programa | Emissora.',
            'exemplo'    => 'Caldeirão | TV Globo',
        ],
        [
            'chave'      => 'marcas',
            'rotulo'     => 'Marcas e publicidade',
            'tipo'       => 'itens',
            'nucleo'     => true,
            'instrucoes' => 'Separadas por vírgula.',
            'exemplo'    => 'Converse, Amazon Music, Riachuelo',
        ],
        [
            'chave'      => 'ensino',
            'rotulo'     => 'Workshops e ensino',
            'tipo'       => 'lista',
            'nucleo'     => true,
            'instrucoes' => 'Um por linha, com local e ano quando houver.',
            'exemplo'    => 'Masterclass Free Step Elektronia, Peru, 2025',
        ],
        [
            'chave'      => 'visualizacoes',
            'rotulo'     => 'Visualizações',
            'tipo'       => 'linha',
            'nucleo'     => false,
            'instrucoes' => 'Total somado, se o membro divulgar.',
            'exemplo'    => 'Mais de 2 bilhões',
        ],
        [
            'chave'      => 'publico',
            'rotulo'     => 'Perfil de público',
            'tipo'       => 'pares',
            'nucleo'     => false,
            'instrucoes' => 'Uma métrica por linha, no formato Métrica | Valor.',
            'exemplo'    => "Público masculino | 70%\nIdade média | 25-50 anos",
        ],
        [
            'chave'      => 'links',
            'rotulo'     => 'Links de portfólio',
            'tipo'       => 'pares',
            'nucleo'     => false,
            'instrucoes' => 'Um por linha, no formato Rótulo | URL.',
            'exemplo'    => 'Rock In Rio 2024 | https://youtube.com/watch?v=...',
        ],
    ];
}

/**
 * Nome do campo no ACF para uma chave do esquema.
 */
function freestep_perfil_campo($chave)
{
    return 'membro_' . $chave;
}
```

- [ ] **Passo 2: carregar o esquema no tema**

Em `functions.php`, logo apos o `require_once` do `social.php`:

```php
require_once get_stylesheet_directory() . '/inc/perfil-schema.php';
```

- [ ] **Passo 3: conferir que o esquema carrega e esta coerente**

```bash
cd ~/projetos/freestep
php -l wp-content/themes/freestep-child/inc/perfil-schema.php
echo 'h447cyzw' | sudo -S rsync -a --delete wp-content/themes/freestep-child/ /var/www/freestep/wp-content/themes/freestep-child/
echo 'h447cyzw' | sudo -S chown -R www-data:www-data /var/www/freestep/wp-content/themes/freestep-child
cd ~/projetos/freestep && echo 'h447cyzw' | sudo -S php ferramentas/wp-cli.phar --allow-root --path=/var/www/freestep eval '
$s = freestep_perfil_schema();
$tipos_validos = ["linha","texto","lista","itens","pares"];
$nucleo = 0; $chaves = [];
foreach ($s as $t) {
  if (!in_array($t["tipo"], $tipos_validos, true)) { echo "TIPO INVALIDO: ", $t["chave"], PHP_EOL; }
  if (in_array($t["chave"], $chaves, true)) { echo "CHAVE DUPLICADA: ", $t["chave"], PHP_EOL; }
  $chaves[] = $t["chave"];
  if ($t["nucleo"]) { $nucleo++; }
}
echo count($s), " topicos, ", $nucleo, " de nucleo", PHP_EOL;
echo implode(" ", array_column(array_filter($s, fn($t) => $t["nucleo"]), "chave")), PHP_EOL;'
```

Esperado: `14 topicos, 11 de nucleo` e a sequencia
`release base carreira papel alcance formacao titulos internacional tv marcas ensino`,
identica a variavel `NUCLEO` do verificador. Nenhum tipo invalido, nenhuma
chave duplicada.

- [ ] **Passo 4: commit**

```bash
git add wp-content/themes/freestep-child/inc/perfil-schema.php wp-content/themes/freestep-child/functions.php
git commit -m "feat(perfil): esquema unico do perfil do membro

Define os 14 topicos do perfil, 11 de nucleo e 3 extras, num array so.
Ele vai alimentar o registro dos campos, o template e os rotulos do painel,
para que a ordem dos topicos saia de um unico lugar.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 3: Campos do ACF gerados a partir do esquema

**Arquivos:**
- Modificar: `wp-content/themes/freestep-child/inc/acf-fields.php`

**Interfaces:**
- Consome: `freestep_perfil_schema()`, `freestep_perfil_campo()` da Task 2
- Produz: grupo ACF `group_fs_membro` com um campo por topico do esquema,
  mais os campos de rede social, que nao fazem parte do esquema de perfil

- [ ] **Passo 1: substituir os campos escritos a mao pelo gerador**

No `acf-fields.php`, o bloco de campos de `group_fs_membro` passa a ser
montado antes do `acf_add_local_field_group`. Os campos de rede social
(`membro_instagram`, `membro_tiktok`, `membro_facebook`, `membro_youtube`)
continuam escritos a mao: eles alimentam `inc/social.php`, nao o perfil.

```php
    // Campos do perfil, gerados do esquema. Escrever a mao de novo aqui
    // seria recriar a divergencia entre perfis que o esquema resolve.
    $campos = [];

    $tipo_acf = [
        'linha' => ['type' => 'text'],
        'texto' => ['type' => 'textarea', 'rows' => 8, 'new_lines' => 'wpautop'],
        'lista' => ['type' => 'textarea', 'rows' => 6, 'new_lines' => ''],
        'itens' => ['type' => 'textarea', 'rows' => 3, 'new_lines' => ''],
        'pares' => ['type' => 'textarea', 'rows' => 5, 'new_lines' => ''],
    ];

    foreach (freestep_perfil_schema() as $topico) {
        $nome = freestep_perfil_campo($topico['chave']);

        $instrucoes = $topico['instrucoes'];
        if ($topico['exemplo'] !== '') {
            $instrucoes .= '<br>Exemplo:<br><code>'
                . nl2br(esc_html($topico['exemplo'])) . '</code>';
        }
        if (!$topico['nucleo']) {
            $instrucoes .= '<br><em>Opcional: nem todo membro tem.</em>';
        }

        $campos[] = array_merge(
            [
                'key'          => 'field_' . $nome,
                'label'        => $topico['rotulo'],
                'name'         => $nome,
                'instructions' => $instrucoes,
            ],
            $tipo_acf[$topico['tipo']]
        );
    }

    // Redes sociais: fora do esquema de perfil, alimentam inc/social.php.
    foreach (['instagram' => 'Instagram', 'tiktok' => 'TikTok', 'facebook' => 'Facebook', 'youtube' => 'YouTube'] as $chave => $rotulo) {
        $campos[] = [
            'key'   => "field_membro_{$chave}",
            'label' => $rotulo,
            'name'  => "membro_{$chave}",
            'type'  => 'url',
        ];
    }

    acf_add_local_field_group([
        'key'      => 'group_fs_membro',
        'title'    => 'Dados do Membro',
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'fs_membro']]],
        'fields'   => $campos,
    ]);
```

- [ ] **Passo 2: conferir que os 18 campos foram registrados**

```bash
cd ~/projetos/freestep && php -l wp-content/themes/freestep-child/inc/acf-fields.php
echo 'h447cyzw' | sudo -S rsync -a --delete wp-content/themes/freestep-child/ /var/www/freestep/wp-content/themes/freestep-child/
echo 'h447cyzw' | sudo -S chown -R www-data:www-data /var/www/freestep/wp-content/themes/freestep-child
cd ~/projetos/freestep && echo 'h447cyzw' | sudo -S php ferramentas/wp-cli.phar --allow-root --path=/var/www/freestep eval '
$g = acf_get_field_groups(["post_type" => "fs_membro"]);
foreach ($g as $grupo) {
  $n = array_column(acf_get_fields($grupo), "name");
  echo $grupo["title"], ": ", count($n), " campos", PHP_EOL, implode(" ", $n), PHP_EOL;
}'
```

Esperado: 18 campos. Os 14 do esquema na ordem do esquema, depois os 4 de
rede social.

- [ ] **Passo 3: commit**

```bash
git add wp-content/themes/freestep-child/inc/acf-fields.php
git commit -m "feat(perfil): gera os campos do membro a partir do esquema

Os campos do perfil deixam de ser escritos a mao: percorrem o esquema. Os
de rede social continuam a mao porque alimentam inc/social.php, e nao o
perfil.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 4: Template percorrendo o esquema

E aqui que a checagem 1 do verificador passa a valer.

**Arquivos:**
- Modificar: `wp-content/themes/freestep-child/single-fs_membro.php`
- Modificar: `wp-content/themes/freestep-child/assets/css/sections/midiakit.css`

**Interfaces:**
- Consome: `freestep_perfil_schema()`, `freestep_perfil_campo()` da Task 2;
  `freestep_linhas()`, `freestep_itens()`, `freestep_pares()` de
  `inc/listas.php`
- Produz: cada bloco de topico sai com `data-topico="<chave>"` no elemento
  `<section>`, que e o que o verificador le

- [ ] **Passo 1: escrever a funcao que desenha um topico**

Criar `wp-content/themes/freestep-child/inc/perfil-render.php`:

```php
<?php
/**
 * Desenho de um topico do perfil.
 *
 * Separado do template porque o template so precisa percorrer o esquema e
 * chamar isto; toda a decisao de como cada tipo aparece mora aqui.
 */

/**
 * Imprime um topico. Vazio: nada para anonimo, aviso para quem edita.
 *
 * @param array $topico Entrada do esquema.
 * @param int   $id     ID do membro.
 * @param int   $pos    Posicao, para alternar o fundo das secoes.
 */
function freestep_perfil_topico(array $topico, $id, $pos)
{
    $chave = $topico['chave'];
    $campo = freestep_perfil_campo($chave);

    switch ($topico['tipo']) {
        case 'linha':
        case 'texto':
            $valor = trim((string) get_field($campo, $id));
            $vazio = $valor === '';
            break;
        case 'lista':
            $valor = freestep_linhas($campo, $id);
            $vazio = $valor === [];
            break;
        case 'itens':
            $valor = freestep_itens($campo, $id);
            $vazio = $valor === [];
            break;
        case 'pares':
            $valor = freestep_pares($campo, $id);
            $vazio = $valor === [];
            break;
        default:
            return;
    }

    // Topico extra vazio nao cobra nada: por definicao nem todo membro tem.
    if ($vazio && (!$topico['nucleo'] || !current_user_can('edit_posts'))) {
        return;
    }

    $alt = ($pos % 2 === 1) ? ' fs-section--alt' : '';
    printf(
        '<section class="fs-section fs-bloco%s" data-topico="%s">',
        esc_attr($alt),
        esc_attr($chave)
    );
    echo '<div class="fs-container">';
    printf('<h2 class="fs-section__title">%s</h2>', esc_html($topico['rotulo']));
    echo '<div class="section-divider"></div>';

    if ($vazio) {
        printf(
            '<p class="fs-pendente">Falta preencher <strong>%s</strong>. '
                . '<a href="%s">Preencher agora</a></p>',
            esc_html($topico['rotulo']),
            esc_url(get_edit_post_link($id))
        );
    } else {
        freestep_perfil_valor($topico['tipo'], $valor, $chave);
    }

    echo '</div></section>';
}

/**
 * Desenha o valor de um topico conforme o tipo.
 */
function freestep_perfil_valor($tipo, $valor, $chave)
{
    switch ($tipo) {
        case 'texto':
            // O campo usa wpautop, entao o ACF ja devolve HTML. esc_html
            // aqui imprimiria as tags <p> na tela.
            printf('<div class="fs-texto">%s</div>', wp_kses_post($valor));
            break;

        case 'linha':
            printf('<p class="fs-linha">%s</p>', esc_html($valor));
            break;

        case 'lista':
            echo '<ul class="fs-lista-marcada">';
            foreach ($valor as $item) {
                printf('<li>%s</li>', esc_html($item));
            }
            echo '</ul>';
            break;

        case 'itens':
            echo '<div class="fs-chips">';
            foreach ($valor as $item) {
                $classe = ($chave === 'marcas') ? 'fs-chip fs-chip--marca' : 'fs-chip';
                printf('<span class="%s">%s</span>', esc_attr($classe), esc_html($item));
            }
            echo '</div>';
            break;

        case 'pares':
            // "alcance" e numero grande e merece cartao; os demais pares
            // sao lista de duas colunas.
            if ($chave === 'alcance') {
                echo '<div class="fs-numeros-rede">';
                foreach ($valor as $p) {
                    printf(
                        '<div class="fs-numero-rede"><span class="fs-numero-rede__valor">%s</span>'
                            . '<span class="fs-numero-rede__rede">%s</span></div>',
                        esc_html($p['valor']),
                        esc_html($p['rotulo'])
                    );
                }
                echo '</div>';
                break;
            }

            echo '<div class="fs-imprensa-lista">';
            foreach ($valor as $p) {
                $rotulo = esc_html($p['rotulo']);
                // Em "links" o valor e URL e vira o proprio link.
                if ($chave === 'links' && $p['valor'] !== '') {
                    printf(
                        '<div class="fs-imprensa-lista__item"><strong>'
                            . '<a href="%s" target="_blank" rel="noopener">%s</a></strong></div>',
                        esc_url($p['valor']),
                        $rotulo
                    );
                    continue;
                }
                printf('<div class="fs-imprensa-lista__item"><strong>%s</strong>', $rotulo);
                if ($p['valor'] !== '') {
                    printf('<span>%s</span>', esc_html($p['valor']));
                }
                echo '</div>';
            }
            echo '</div>';
            break;
    }
}
```

E carregar em `functions.php`:

```php
require_once get_stylesheet_directory() . '/inc/perfil-render.php';
```

- [ ] **Passo 2: reescrever o template**

`single-fs_membro.php` inteiro:

```php
<?php
/**
 * Pagina do membro.
 *
 * O corpo da pagina e o esquema do perfil percorrido em ordem. Nao ha um
 * bloco escrito por topico de proposito: e assim que os sete perfis saem
 * com a mesma forma, em vez de depender de alguem repetir a estrutura.
 */

get_header(); ?>

<main>
<?php while (have_posts()) : the_post();
  $id = get_the_ID();
?>

<section class="fs-section fs-membro-single">
  <div class="fs-container fs-membro-single__inner">
    <div class="fs-membro-single__foto">
      <?php the_post_thumbnail('large'); ?>
    </div>
    <div class="fs-membro-single__dados">
      <?php $papel = get_field(freestep_perfil_campo('papel'), $id); ?>
      <?php if ($papel): ?>
        <p class="fs-membro-single__funcao"><?php echo esc_html($papel); ?></p>
      <?php endif; ?>
      <h1 class="fs-membro-single__nome"><?php the_title(); ?></h1>
      <div class="section-divider" style="margin: 0.5rem 0 1.5rem;"></div>
      <?php freestep_redes_do_membro($id, 'fs-social fs-social--grande fs-membro-single__social'); ?>
    </div>
  </div>
</section>

<?php
$pos = 0;
foreach (freestep_perfil_schema() as $topico) {
    freestep_perfil_topico($topico, $id, $pos);
    $pos++;
}
?>

<section class="fs-section fs-membro-single__rodape">
  <div class="fs-container" style="text-align:center;">
    <a href="<?php echo esc_url(home_url('/#contato')); ?>" class="btn-contratar">Contratar o Time</a>
    <p><a href="<?php echo esc_url(home_url('/#time')); ?>" class="fs-membro-single__voltar">&#8592; Voltar para o time</a></p>
  </div>
</section>

<?php endwhile; ?>
</main>

<?php get_footer(); ?>
```

O topico `papel` aparece duas vezes: no cabecalho e como bloco. Isso e
proposital e necessario para a padronizacao: o verificador exige os 11
topicos do nucleo emitidos. Se incomodar visualmente, a solucao e esconder
o bloco por CSS, nunca pular o topico no laco.

- [ ] **Passo 3: estilo do aviso de pendencia e dos tipos novos**

Acrescentar em `assets/css/sections/midiakit.css`:

```css
/* Aviso de pendencia. So sai do servidor para quem pode editar, entao
   nunca e visto por contratante. Discreto de proposito: e lembrete de
   trabalho, nao erro. */
.fs-pendente {
  max-width: 560px;
  margin: 2rem auto 0;
  padding: 1rem 1.25rem;
  text-align: center;
  font-size: 0.92rem;
  border: 1px dashed rgba(201, 158, 0, 0.6);
  border-radius: 8px;
  background: var(--lavagem-dourado);
}

.fs-pendente a {
  color: var(--color-verde);
}

.fs-linha {
  text-align: center;
  font-family: var(--font-titulo);
  font-size: 1.4rem;
  margin: 2rem 0 0;
}

.fs-texto {
  max-width: 760px;
  margin: 2rem auto 0;
  font-size: 1.05rem;
  line-height: 1.8;
}

.fs-texto p {
  margin: 0 0 1.1rem;
}

.fs-texto p:last-child {
  margin-bottom: 0;
}
```

- [ ] **Passo 4: publicar e rodar o verificador**

```bash
cd ~/projetos/freestep
php -l wp-content/themes/freestep-child/inc/perfil-render.php
php -l wp-content/themes/freestep-child/single-fs_membro.php
echo 'h447cyzw' | sudo -S rsync -a --delete wp-content/themes/freestep-child/ /var/www/freestep/wp-content/themes/freestep-child/
echo 'h447cyzw' | sudo -S chown -R www-data:www-data /var/www/freestep/wp-content/themes/freestep-child
FREESTEP_PASS='C!hShG%d&7vOH#%bz5' ferramentas/verificar-perfis.sh
```

Esperado: checagem 1 **passa** (os sete perfis emitem os 11 topicos do
nucleo na mesma ordem) e checagem 2 **passa** (o aviso aparece autenticado e
nao aparece anonimo). A checagem 3 ja passava.

- [ ] **Passo 5: mutar a implementacao e confirmar que o verificador acusa**

Teste que passa com o codigo sabotado nao testa nada. Duas mutacoes:

```bash
# Mutacao A: inverter a ordem do esquema. A checagem 1 tem que falhar.
sed -i 's/foreach (freestep_perfil_schema() as $topico) {/foreach (array_reverse(freestep_perfil_schema()) as $topico) {/' \
  wp-content/themes/freestep-child/single-fs_membro.php
echo 'h447cyzw' | sudo -S rsync -a --delete wp-content/themes/freestep-child/ /var/www/freestep/wp-content/themes/freestep-child/
FREESTEP_PASS='C!hShG%d&7vOH#%bz5' ferramentas/verificar-perfis.sh   # esperado: FALHA na checagem 1
git checkout wp-content/themes/freestep-child/single-fs_membro.php

# Mutacao B: mostrar o aviso para todo mundo. A checagem 2 tem que falhar.
sed -i 's/!current_user_can(.edit_posts.)/false/' \
  wp-content/themes/freestep-child/inc/perfil-render.php
echo 'h447cyzw' | sudo -S rsync -a --delete wp-content/themes/freestep-child/ /var/www/freestep/wp-content/themes/freestep-child/
FREESTEP_PASS='C!hShG%d&7vOH#%bz5' ferramentas/verificar-perfis.sh   # esperado: FALHA na checagem 2
git checkout wp-content/themes/freestep-child/inc/perfil-render.php

# Restaurar o estado bom
echo 'h447cyzw' | sudo -S rsync -a --delete wp-content/themes/freestep-child/ /var/www/freestep/wp-content/themes/freestep-child/
echo 'h447cyzw' | sudo -S chown -R www-data:www-data /var/www/freestep/wp-content/themes/freestep-child
FREESTEP_PASS='C!hShG%d&7vOH#%bz5' ferramentas/verificar-perfis.sh   # esperado: OK
```

Se alguma mutacao **nao** fizer o verificador falhar, a checagem
correspondente nao esta testando nada: corrigir o verificador antes de
seguir.

- [ ] **Passo 6: commit**

```bash
git add wp-content/themes/freestep-child/inc/perfil-render.php \
        wp-content/themes/freestep-child/single-fs_membro.php \
        wp-content/themes/freestep-child/functions.php \
        wp-content/themes/freestep-child/assets/css/sections/midiakit.css
git commit -m "feat(perfil): template percorre o esquema em vez de bloco por topico

Os sete perfis passam a emitir os mesmos 11 topicos de nucleo na mesma
ordem, porque a ordem sai do esquema e nao de blocos repetidos a mao.

Topico de nucleo vazio nao e emitido para visitante anonimo e vira aviso
com link de edicao para quem tem edit_posts: contratante nao ve lacuna e a
equipe ve o que falta.

Verificado com mutacao: inverter a ordem do laco derruba a checagem 1, e
mostrar o aviso para todos derruba a checagem 2.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 5: Migracao dos dados e preenchimento dos 14 topicos

**Arquivos:**
- Modificar: `ferramentas/portfolio.php`
- Remover: `ferramentas/biografias.php`
- Modificar: `wp-content/themes/freestep-child/front-page.php`
- Modificar: `wp-content/themes/freestep-child/inc/acf-fields.php` (remover campos antigos)

**Interfaces:**
- Consome: `freestep_perfil_campo()` da Task 2
- Produz: os sete membros com os campos novos preenchidos; campos antigos
  (`membro_bio`, `membro_funcao`, `membro_numeros`, `membro_conquistas`,
  `membro_paises`, `membro_imprensa`) apagados do banco

- [ ] **Passo 1: o card da home passa a ler `membro_papel`**

Em `front-page.php`, trocar a leitura da funcao:

```php
$funcao = get_field('membro_papel');
```

O resto do card nao muda.

- [ ] **Passo 2: reescrever `ferramentas/portfolio.php`**

O arquivo passa a carregar os 14 topicos. O conteudo sai dos documentos em
`docs/fontes/`, fiel ao original e sem dado pessoal. Estrutura:

A **"Regra de extracao por membro"**, no fim deste plano, e a especificacao
do conteudo: ela diz, topico a topico e membro a membro, de qual trecho de
qual arquivo de `docs/fontes/` o texto sai. Nao ha liberdade de redacao, so
transcricao. O esqueleto do arquivo e este:

```php
<?php
/**
 * Preenche o perfil dos membros a partir dos documentos deles.
 *
 * Fonte: docs/fontes/, texto extraido dos midia kits e curriculos enviados
 * pelo time em maio de 2026.
 *
 * REGRA: informacao fiel ao documento. O site padroniza a apresentacao, nao
 * reescreve o conteudo. Nao trocar palavra, nao resumir, nao tirar acento.
 *
 * Fora, sempre: dado pessoal (endereco, telefone, email particular, data de
 * nascimento, estado civil) e tudo que nao e da danca (formacao academica
 * fora da area, emprego fora da area).
 *
 * Conquista obtida com outro grupo fica aqui, no perfil individual, e nunca
 * na secao de imprensa do time.
 *
 * Rodar com:
 *   sudo php wp-cli.phar --allow-root --path=/var/www/freestep \
 *     eval-file ferramentas/portfolio.php
 *
 * Idempotente: sobrescreve, nao acumula. Topico ausente para um membro e
 * gravado vazio, para o script seguir sendo a fonte de verdade em vez de
 * deixar sobra de rodada anterior.
 */

// Chave do array = titulo do membro no WordPress.
// Chave interna = chave do topico no esquema.
// Valor: string para linha e texto; array de strings para lista e pares;
// string com virgulas para itens.
$perfis = [
    'Alves' => [
        'base'     => 'São Paulo, SP',
        'carreira' => 'Na dança desde 2009',
        'papel'    => 'Professor de Danças Eletrônicas e Criador de Conteúdo',
        'alcance'  => [
            'TikTok | 2,4 milhões',
            'Kwai | 2 milhões',
            'YouTube | 1,5 milhão',
            'Facebook | 1,2 milhão',
            'Instagram | 486 mil',
        ],
        'internacional' => 'Espanha, Peru',
        'tv' => [
            'Caldeirão do Mion | TV Globo, 2º lugar, 2022',
            'Circo do Tiru | SBT, 2º lugar, 2024',
        ],
        'visualizacoes' => 'Mais de 2 bilhões',
        // release, titulos, marcas e ensino: transcrever dos trechos
        // indicados na regra de extracao. formacao fica vazio.
    ],

    'Gu Henrique' => [ /* idem, pela regra de extracao */ ],
    'Jeff'        => [ /* idem */ ],
    'Takeshi'     => [ /* idem */ ],

    // Sem documento no Drive: entram sem nenhum topico, de proposito. Os 11
    // de nucleo viram pendencia para a equipe logada.
    'Jorg'    => [],
    'Jux'     => [],
    'Ederson' => [],
];

// Grava todos os topicos do esquema. Topico ausente no array acima e
// gravado vazio: assim este arquivo e a fonte de verdade, em vez de deixar
// sobra de uma rodada anterior no banco.
foreach ($perfis as $nome => $dados) {
    $membro = get_page_by_title($nome, OBJECT, 'fs_membro');

    if (!$membro) {
        echo "ERRO: membro '{$nome}' nao encontrado", PHP_EOL;
        continue;
    }

    $preenchidos = [];

    foreach (freestep_perfil_schema() as $topico) {
        $valor = $dados[$topico['chave']] ?? '';

        if (is_array($valor)) {
            $valor = implode("\n", $valor);
        }

        update_field(freestep_perfil_campo($topico['chave']), $valor, $membro->ID);

        if ($valor !== '') {
            $preenchidos[] = $topico['chave'];
        }
    }

    printf("%-12s (ID %d): %s%s", $nome, $membro->ID, implode(' ', $preenchidos) ?: '(vazio)', PHP_EOL);
}
```

Os quatro membros com documento sao transcritos integralmente segundo a
**Regra de extracao por membro**, no fim deste plano. O trecho acima mostra
o formato com o Alves parcialmente preenchido; os topicos comentados e os
outros tres membros seguem a mesma forma, com o texto vindo de
`docs/fontes/`.

- [ ] **Passo 3: rodar e conferir**

```bash
cd ~/projetos/freestep
echo 'h447cyzw' | sudo -S php ferramentas/wp-cli.phar --allow-root --path=/var/www/freestep eval-file ~/projetos/freestep/ferramentas/portfolio.php
```

Esperado: os sete membros processados, com a lista de topicos preenchidos
por membro.

- [ ] **Passo 4: apagar os campos antigos do banco**

```bash
echo 'h447cyzw' | sudo -S php ferramentas/wp-cli.phar --allow-root --path=/var/www/freestep eval '
$antigos = ["membro_bio","membro_funcao","membro_numeros","membro_conquistas","membro_paises","membro_imprensa"];
$q = new WP_Query(["post_type" => "fs_membro", "posts_per_page" => -1]);
foreach ($q->posts as $p) {
  foreach ($antigos as $c) {
    delete_post_meta($p->ID, $c);
    delete_post_meta($p->ID, "_" . $c);
  }
}
echo "limpos ", count($q->posts), " membros", PHP_EOL;'
```

- [ ] **Passo 5: remover `biografias.php` e rodar o verificador**

```bash
cd ~/projetos/freestep
git rm ferramentas/biografias.php
echo 'h447cyzw' | sudo -S rsync -a --delete wp-content/themes/freestep-child/ /var/www/freestep/wp-content/themes/freestep-child/
echo 'h447cyzw' | sudo -S chown -R www-data:www-data /var/www/freestep/wp-content/themes/freestep-child
FREESTEP_PASS='C!hShG%d&7vOH#%bz5' ferramentas/verificar-perfis.sh
```

Esperado: as tres checagens passam. A checagem 3 agora tem conteudo de
verdade para varrer, entao e aqui que ela de fato prova alguma coisa.

- [ ] **Passo 6: conferir o grid da home**

```bash
curl -s http://192.168.15.6:8081/ | grep -o 'fs-membro-card__funcao">[^<]*' | sed 's/.*">//'
```

Esperado: os sete papeis, sem nenhum vazio.

- [ ] **Passo 7: commit**

```bash
git add -A ferramentas wp-content docs
git commit -m "feat(perfil): preenche os 14 topicos e remove os campos antigos

Conteudo tirado de docs/fontes/, fiel ao documento de cada membro. Fora:
dado pessoal e tudo que nao e da danca.

Campos antigos apagados do banco depois da gravacao nos novos. O card da
home passa a ler membro_papel. ferramentas/biografias.php sai, absorvido
pelo portfolio.php.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 6: Documentacao do painel para a equipe

**Arquivos:**
- Criar: `docs/manual-do-painel.md`

**Interfaces:**
- Consome: o esquema da Task 2, para listar os topicos e o formato de cada um

- [ ] **Passo 1: escrever o manual**

Escrito para o membro da equipe que vai manter o site e nao mexe em codigo.
Precisa cobrir: como entrar no painel, onde ficam os membros, o que e cada
um dos 14 topicos, o formato de lista (um por linha) e de par (`rotulo |
valor`), como subir a foto destacada, e o que significa o aviso amarelo de
pendencia que so ele enxerga.

- [ ] **Passo 2: commit**

```bash
git add docs/manual-do-painel.md
git commit -m "docs: manual do painel para a equipe

Escrito para quem mantem o site sem mexer em codigo: os 14 topicos do
perfil, o formato de cada campo e o que e o aviso de pendencia.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Regra de extracao por membro

De onde sai o conteudo de cada topico, para a Task 5. Tudo fiel ao
documento; o que nao estiver listado aqui fica vazio e vira pendencia.

### Alves (`docs/fontes/alves-midiakit.txt`, `alves-curriculo.txt`)

| Topico | Origem |
|---|---|
| release | Bloco "QUEM É MR ALVES" do midia kit, sem a idade |
| base | "reside em São Paulo" -> `São Paulo, SP` |
| carreira | "iniciou sua carreira na dança em 2009" |
| papel | `Professor de Danças Eletrônicas e Criador de Conteúdo` |
| alcance | Secao REDES SOCIAIS do curriculo, as cinco redes |
| formacao | Vazio: a formacao dele no curriculo e de informatica, fora da regra |
| titulos | Secao EXPERIENCIAS ARTISTICAS E PREMIACOES, so os lugares conquistados e as placas |
| internacional | Barcelona (Espanha), Lima (Peru) |
| tv | "2º Lugar Programa Caldeirão do Mion - TV Globo - 2022", "2º Lugar Programa Circo do Tiru - SBT - 2024" |
| marcas | Lista de publicidade do curriculo |
| ensino | Os "Workshop de Free Step" da lista, com cidade e ano |
| visualizacoes | "mais de 2 bilhões de visualizações" |

**Nunca:** endereco, telefone, email, nascimento, estado civil, formacao em
informatica, e os empregos de auxiliar de informatica, telemarketing e
analista de negocios.

### Gu Henrique (`docs/fontes/gu-henrique-curriculo.txt`)

| Topico | Origem |
|---|---|
| release | Bloco "Free Step Brazil Team — Membro Oficial" |
| base | "Ferraz de Vasconcelos - São Paulo" |
| carreira | "Iniciou na dança em 2010" |
| papel | `Co-idealizador e Coordenação Artística` |
| alcance | Secao REDES SOCIAIS E SEGUIDORES, as cinco redes |
| formacao | Vazio: o curriculo so diz "Ensino Superior Completo", sem area |
| titulos | Bloco "Títulos de destaque" |
| internacional | Espanha |
| tv | "Participações em TV": Dez ou Mil (SBT), Let's Dance (MTV), Circo do Tiru (SBT) |
| marcas | Bloco "Parcerias e Publicidades" |
| ensino | "WORKSHOP MEGA CLASS - TOCANTINS" e a idealizacao de eventos |

### Jeff (`docs/fontes/jeff-midiakit.txt`)

| Topico | Origem |
|---|---|
| release | Bloco "Do Tocantins pro Mundo" inteiro |
| base | Tocantins |
| carreira | "17 anos de carreira na Dança" |
| papel | `Líder do Time` |
| alcance | Bloco "Redes Sociais": cinco contas com seguidores |
| formacao | "pós-graduação em Danças Urbanas" |
| titulos | Condutor da Tocha Olimpica Rio 2016, e a lideranca da selecao |
| internacional | Os 16 paises listados |
| tv | Os 23 programas do bloco Imprensa, com a emissora de cada um |
| marcas | Vazio: o documento tem o titulo "Marcas que já contrataram" sem lista legivel |
| ensino | "ministrou workshops e aulas" em eventos internacionais |
| visualizacoes | Soma declarada por rede no bloco Redes Sociais |
| publico | "70% público masculino", "25-50 anos idade média", "75% moram em capitais", "10% de seguidores estrangeiros" |
| links | O site do portfolio citado no fim do documento |

**Nunca:** o email do fim do documento.

### Takeshi (`docs/fontes/takeshi-curriculo.txt`)

| Topico | Origem |
|---|---|
| release | "Resumo Profissional / Release" |
| base | "Mogi das Cruzes - SP" |
| carreira | "15 anos de atuação" |
| papel | `Dançarino, Coreógrafo e Criador de Conteúdo` |
| alcance | **Vazio.** Os numeros dele so existem como captura de tela no documento, ilegiveis na extracao. Vira pendencia e precisa ser pedido a ele |
| formacao | Bloco "Formação/Cursos", que e todo de danca |
| titulos | Tabela "Destaques de Carreira e Premiações" |
| internacional | Espanha, Peru |
| tv | "Caldeirão (TV Globo) com LosCocas (2022)" |
| marcas | "Parcerias de Destaque" mais o bloco "Campanhas Publicitárias" |
| ensino | Bloco "Workshops & Ensino" |
| links | Bloco "Links de Portfólio (Relevantes)" |

**Nunca:** idade, email, telefone.

### Jorg, Jux, Ederson

Sem documento no Drive. Todos os topicos vazios: os 11 do nucleo aparecem
como pendencia para a equipe logada, e nada aparece para o visitante. E o
comportamento desejado, nao uma falha, e serve de lista do que pedir a eles.

## Fora de escopo

Galeria da home a partir das fotos do Drive, secao de imprensa do time na
home, foto de palco no fundo do hero, e a coleta dos dados que faltam de
Jorg, Jux e Ederson.

