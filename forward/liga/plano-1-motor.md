# Liga Free Step - Plano 1: Motor de Pontuacao

> **Para quem executar:** use superpowers:subagent-driven-development (recomendado) ou superpowers:executing-plans. Os passos usam caixa de marcar (`- [ ]`).

**Objetivo:** construir `pontuacao.php`, o motor puro da Liga (fases, peso, bonificacao, arredondamento, validacao de chave, ranking, tiers, premiacao), com testes que provadamente falham quando o codigo e sabotado.

**Arquitetura:** um unico arquivo de funcoes puras, sem banco, sem HTTP, sem sessao. Entra dado simples, sai dado simples. Testes em PHP puro no estilo da Gincana (`tests/*.test.php`, funcao `ok()`, saida diferente de zero se algo falhar). Toda aritmetica e inteira: peso em percentual inteiro e dinheiro em centavos, para o arredondamento nunca depender de ponto flutuante.

**Stack:** PHP 8.4 CLI (ja instalado), bash, sed. Sem Composer, sem framework.

**Este e o plano 1 de 4.** A spec (`spec-sistema.md`) cobre quatro subsistemas independentes, cada um entrega software testavel sozinho:

1. **Motor de pontuacao** (este plano)
2. Banco, `comum.php` e `api.php` (tabelas, papeis, escrita versionada)
3. Telas (ranking publico, inscricao, painel admin, area do parceiro)
4. Instalacao e exposicao (vhost, `instalar.sh`, dominio)

Os planos 2 a 4 sao escritos depois que este estiver verde, porque dependem das assinaturas reais do motor.

## Restricoes globais

Copiadas da spec e do regulamento, valem para todas as tarefas.

- Fases validas, em pontos base: **5, 15, 21, 27, 35, 45, 55**. Fase fora disso e erro, nunca zero.
- Peso: oficial **100%**; parceiro com **32 ou mais** inscritos 100%; **16 a 31** 75%; **menos de 16** 50%. Bordas exatas: 15, 16, 31, 32.
- Bonificacao da seletiva: **5 pontos** para a maior nota; empate: todos os empatados recebem; o bonus entra **antes** do peso.
- Arredondamento: inteiro mais proximo, **meio para cima**, **uma unica vez** sobre `(fase + bonus) x peso`. 45 x 75% = 33,75 vira 34; 55 x 50% = 27,5 vira 28.
- Tiers por posicao: 1 a 5 S, 6 a 10 A, 11 a 20 B, 21 em diante C.
- Desempate do ranking: maior numero de participacoes em evento **oficial**.
- So pontua dançarino `confirmado` em evento `aprovado`.
- Premiacao: 40 / 25 / 15 / 10 / 10 por cento, em **centavos inteiros**, e a soma tem de fechar exatamente o total.
- Metodo de trabalho do Matheus: teste primeiro, ver falhar, implementar, depois **sabotar a implementacao e confirmar que o teste falha**. Teste que passa com o codigo quebrado nao testa nada. Asercao de valor absoluto sempre; comparar duas implementacoes so prova que concordam.
- Sem em-dash e sem emoji em nenhum texto.
- A pasta `~/projetos/forward` **nao e repositorio git**. Os passos de commit foram trocados por "rodar a suite inteira". Se o Matheus quiser versionar, e decisao dele (`git init`) e vale antes da tarefa 1.

## Estrutura de arquivos

Tudo em `~/projetos/forward/liga/`.

| Arquivo | Responsabilidade |
|---|---|
| `pontuacao.php` | O motor. Funcoes puras. Nada de `require` de outro arquivo do projeto |
| `tests/ajuda.php` | `ok()`, `igual()`, `lanca()`, `fim()`. Compartilhado pelos testes |
| `tests/rodar.sh` | Roda todos os `*.test.php`, mostra o resumo, sai com 1 se algum falhar |
| `tests/mutar.sh` | Sabota o motor com uma expressao sed, roda um teste, exige que ele falhe, e restaura o motor |
| `tests/fases.test.php` | `pontos_da_fase`, `fase_valida_na_chave`, `validar_distribuicao_de_fases` |
| `tests/peso.test.php` | `peso_do_evento` |
| `tests/pontos.test.php` | `bonus_da_seletiva`, `calcular_pontos` |
| `tests/ranking.test.php` | `tier_da_posicao`, `montar_ranking` |
| `tests/premiacao.test.php` | `distribuir_premiacao` |

## Interface final do motor

Todas as tarefas devem usar exatamente estes nomes e tipos.

```php
const FASES = [5, 15, 21, 27, 35, 45, 55];
const BONUS_SELETIVA = 5;
const TAMANHOS_DE_CHAVE = [4, 8, 16, 32];
const TAMANHO_MINIMO_DA_FASE = [21 => 16, 27 => 8];
const LIMITE_POR_FASE = [55 => 1, 45 => 1, 35 => 2, 27 => 4, 21 => 8];
const PREMIACAO = [40, 25, 15, 10, 10];

pontos_da_fase(int $fase): int
fase_valida_na_chave(int $fase, int $tamanho): bool
validar_distribuicao_de_fases(array $fases, int $tamanho): array   // [participante_id => fase] -> lista de mensagens, vazia = ok
peso_do_evento(string $tipo, ?int $inscritos): int                  // percentual inteiro: 100, 75 ou 50
bonus_da_seletiva(array $notas): array                              // [participante_id => nota] -> ids empatados na maior nota
calcular_pontos(int $fase, int $peso, bool $bonus): int
tier_da_posicao(int $posicao): string
montar_ranking(array $linhas): array
distribuir_premiacao(int $centavos): array                          // 5 inteiros, soma == $centavos
```

Linha de entrada de `montar_ranking`:
`['dancarino_id'=>int, 'nome'=>string, 'crew'=>?string, 'pontos'=>int, 'tipo_evento'=>'oficial'|'parceiro', 'confirmado'=>bool, 'evento_aprovado'=>bool]`

Linha de saida: `['posicao'=>int, 'tier'=>string, 'dancarino_id'=>int, 'nome'=>string, 'crew'=>?string, 'pontos'=>int, 'participacoes_oficiais'=>int]`

Empate completo (mesmos pontos e mesmas participacoes oficiais) divide a posicao (1, 1, 3). Dentro do empate, ordem por nome.

---

### Tarefa 1: Infraestrutura de teste e de mutacao

**Arquivos:**
- Criar: `~/projetos/forward/liga/pontuacao.php`
- Criar: `~/projetos/forward/liga/tests/ajuda.php`
- Criar: `~/projetos/forward/liga/tests/rodar.sh`
- Criar: `~/projetos/forward/liga/tests/mutar.sh`

**Interfaces:**
- Produz: `ok(string,bool)`, `igual(string,mixed,mixed)`, `lanca(string,callable)`, `fim()` em `tests/ajuda.php`; `tests/mutar.sh '<sed -E>' <arquivo-de-teste> [n-linhas]` com saida 0 quando o mutante morre, 1 quando sobrevive, 2 quando a sabotagem e invalida.

- [ ] **Passo 1: criar o motor vazio**

`pontuacao.php`:
```php
<?php
// Motor de pontuacao da Liga Free Step. Funcoes puras: sem banco, sem HTTP.
// Regras em regulamento.md; decisoes em spec-sistema.md.
declare(strict_types=1);
```

- [ ] **Passo 2: criar `tests/ajuda.php`**

```php
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
```

- [ ] **Passo 3: criar `tests/rodar.sh`**

```bash
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
```

- [ ] **Passo 4: criar `tests/mutar.sh`**

```bash
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
```

- [ ] **Passo 5: tornar executaveis e provar que o `mutar.sh` se defende**

Run:
```bash
cd ~/projetos/forward/liga && chmod +x tests/rodar.sh tests/mutar.sh
tests/mutar.sh 's/ZZZ_NAO_EXISTE/x/' tests/ajuda.php; echo "saida=$?"
```
Expected: `ABORTADO: a sabotagem alterou 0 linha(s), esperava 1` e `saida=2`.

Run:
```bash
cmp pontuacao.php <(printf '<?php\n// Motor de pontuacao da Liga Free Step. Funcoes puras: sem banco, sem HTTP.\n// Regras em regulamento.md; decisoes em spec-sistema.md.\ndeclare(strict_types=1);\n') && echo "motor intacto"
```
Expected: `motor intacto` (o `mutar.sh` restaurou o arquivo mesmo abortando).

- [ ] **Passo 6: suite sem testes ainda**

Run: `tests/rodar.sh`
Expected: `SUITE VERDE` (nao ha `*.test.php`; o `[ -e ]` impede o laco de tropecar no glob literal).

---

### Tarefa 2: Fases, validade na chave e distribuicao

**Arquivos:**
- Criar: `tests/fases.test.php`
- Modificar: `pontuacao.php`

**Interfaces:**
- Consome: `ok`, `igual`, `lanca`, `fim` da tarefa 1.
- Produz: `FASES`, `TAMANHOS_DE_CHAVE`, `TAMANHO_MINIMO_DA_FASE`, `LIMITE_POR_FASE`, `pontos_da_fase`, `fase_valida_na_chave`, `validar_distribuicao_de_fases`. A tarefa 4 usa `pontos_da_fase`.

Regra de negocio: oitavas (21) so existe em chave de 16 ou mais; quartas (27) em chave de 8 ou mais. Fases 5, 15, 35, 45, 55 valem em qualquer tamanho (15 cobre "rodada extra" ou grupos, regulamento 2.3). Numa chave so cabe 1 campeao, 1 vice, 2 semifinalistas, 4 em quartas, 8 em oitavas.

- [ ] **Passo 1: escrever o teste que vai falhar**

`tests/fases.test.php`:
```php
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
$msg = validar_distribuicao_de_fases([1 => 55, 2 => 55], 8)[0];
ok('a mensagem diz qual fase estourou', str_contains($msg, '55'));

fim();
```

- [ ] **Passo 2: rodar e ver falhar**

Run: `php tests/fases.test.php`
Expected: `PHP Fatal error: Uncaught Error: Call to undefined function pontos_da_fase()`, saida diferente de zero.

- [ ] **Passo 3: implementar o minimo**

Acrescentar ao fim de `pontuacao.php`:
```php

const FASES = [5, 15, 21, 27, 35, 45, 55];
const TAMANHOS_DE_CHAVE = [4, 8, 16, 32];
// Menor chave em que a fase existe. Fase ausente aqui vale em qualquer tamanho.
const TAMANHO_MINIMO_DA_FASE = [21 => 16, 27 => 8];
// Quantos participantes podem terminar em cada fase.
const LIMITE_POR_FASE = [55 => 1, 45 => 1, 35 => 2, 27 => 4, 21 => 8];

function pontos_da_fase(int $fase): int {
    if (!in_array($fase, FASES, true)) {
        throw new InvalidArgumentException("fase invalida: $fase");
    }
    return $fase;
}

function fase_valida_na_chave(int $fase, int $tamanho): bool {
    if (!in_array($tamanho, TAMANHOS_DE_CHAVE, true)) {
        throw new InvalidArgumentException("tamanho de chave invalido: $tamanho");
    }
    pontos_da_fase($fase);
    return !isset(TAMANHO_MINIMO_DA_FASE[$fase]) || $tamanho >= TAMANHO_MINIMO_DA_FASE[$fase];
}

function validar_distribuicao_de_fases(array $fases, int $tamanho): array {
    $erros = [];
    $contagem = [];
    foreach ($fases as $participante => $fase) {
        try {
            if (!fase_valida_na_chave($fase, $tamanho)) {
                $erros[] = "participante $participante: fase $fase nao existe em chave de $tamanho";
                continue;
            }
        } catch (InvalidArgumentException $e) {
            $erros[] = "participante $participante: " . $e->getMessage();
            continue;
        }
        $contagem[$fase] = ($contagem[$fase] ?? 0) + 1;
    }
    foreach (LIMITE_POR_FASE as $fase => $limite) {
        if (($contagem[$fase] ?? 0) > $limite) {
            $erros[] = "fase $fase tem {$contagem[$fase]} participantes, o maximo e $limite";
        }
    }
    return $erros;
}
```

- [ ] **Passo 4: rodar e ver passar**

Run: `php tests/fases.test.php`
Expected: `TUDO VERDE`, saida 0.

- [ ] **Passo 5: sabotar e confirmar que o teste falha**

Cada comando deve imprimir `mutante morto` e sair com 0.
```bash
cd ~/projetos/forward/liga
# 1. a validacao da fase some
tests/mutar.sh 's/!in_array\(\$fase, FASES, true\)/false/' tests/fases.test.php
# 2. oitavas passam a existir em chave de 8
tests/mutar.sh 's/21 => 16/21 => 8/' tests/fases.test.php
# 3. quartas passam a existir em chave de 4
tests/mutar.sh 's/27 => 8\]/27 => 4]/' tests/fases.test.php
# 4. dois campeoes passam a ser aceitos
tests/mutar.sh 's/55 => 1,/55 => 2,/' tests/fases.test.php
# 5. a checagem de limite deixa de olhar o limite
tests/mutar.sh 's/0\) > \$limite\)/0) > 99)/' tests/fases.test.php
```
Se algum disser `MUTANTE SOBREVIVEU`, o teste esta fraco: corrija o teste, nao o motor.

- [ ] **Passo 6: suite inteira**

Run: `tests/rodar.sh`
Expected: `SUITE VERDE`.

---

### Tarefa 3: Peso do evento

**Arquivos:**
- Criar: `tests/peso.test.php`
- Modificar: `pontuacao.php`

**Interfaces:**
- Produz: `peso_do_evento(string $tipo, ?int $inscritos): int` devolvendo 100, 75 ou 50 (percentual inteiro). A tarefa 4 consome.

- [ ] **Passo 1: escrever o teste**

`tests/peso.test.php`:
```php
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
```

- [ ] **Passo 2: rodar e ver falhar**

Run: `php tests/peso.test.php`
Expected: `Call to undefined function peso_do_evento()`, saida diferente de zero.

- [ ] **Passo 3: implementar**

Acrescentar a `pontuacao.php`:
```php

// Percentual inteiro (100, 75, 50). Inteiro de proposito: o arredondamento
// de calcular_pontos nao pode depender de ponto flutuante.
function peso_do_evento(string $tipo, ?int $inscritos): int {
    if ($tipo === 'oficial') return 100;
    if ($tipo !== 'parceiro') {
        throw new InvalidArgumentException("tipo de evento invalido: $tipo");
    }
    if ($inscritos === null || $inscritos < 0) {
        throw new InvalidArgumentException('evento parceiro exige numero de inscritos');
    }
    if ($inscritos >= 32) return 100;
    if ($inscritos >= 16) return 75;
    return 50;
}
```

- [ ] **Passo 4: rodar e ver passar**

Run: `php tests/peso.test.php`
Expected: `TUDO VERDE`.

- [ ] **Passo 5: sabotar**

Cada um deve imprimir `mutante morto`.
```bash
cd ~/projetos/forward/liga
tests/mutar.sh 's/>= 32\)/> 32)/' tests/peso.test.php
tests/mutar.sh 's/>= 16\)/> 16)/' tests/peso.test.php
tests/mutar.sh "s/if \(\\\$tipo === 'oficial'\) return 100;/if (\$tipo === 'oficial') return 75;/" tests/peso.test.php
tests/mutar.sh 's/\$inscritos === null \|\| //' tests/peso.test.php
```

- [ ] **Passo 6: suite inteira**

Run: `tests/rodar.sh`
Expected: `SUITE VERDE`.

---

### Tarefa 4: Bonificacao da seletiva e calculo de pontos

**Arquivos:**
- Criar: `tests/pontos.test.php`
- Modificar: `pontuacao.php`

**Interfaces:**
- Consome: `pontos_da_fase` (tarefa 2).
- Produz: `BONUS_SELETIVA`, `bonus_da_seletiva(array $notas): array`, `calcular_pontos(int $fase, int $peso, bool $bonus): int`.

Formula: `intdiv((fase + bonus) * peso * 2 + 100, 200)`. E o arredondamento meio para cima em inteiros: `floor(x/100 + 0,5)`.

- [ ] **Passo 1: escrever o teste**

`tests/pontos.test.php`:
```php
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
lanca('nota que nao e numero e erro', fn() => bonus_da_seletiva([1 => 'alta']));

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
```

- [ ] **Passo 2: rodar e ver falhar**

Run: `php tests/pontos.test.php`
Expected: `Call to undefined function bonus_da_seletiva()`.

- [ ] **Passo 3: implementar**

Acrescentar a `pontuacao.php`:
```php

const BONUS_SELETIVA = 5;

// Recebe [participante_id => nota]. Devolve os ids que empataram na maior nota.
// A nota e do proprio evento: nunca se compara nota entre eventos.
function bonus_da_seletiva(array $notas): array {
    if ($notas === []) return [];
    foreach ($notas as $id => $nota) {
        if (!is_int($nota) && !is_float($nota)) {
            throw new InvalidArgumentException("nota invalida para o participante $id");
        }
    }
    $maior = max($notas);
    $vencedores = array_keys(array_filter($notas, fn($n) => $n == $maior));
    sort($vencedores);
    return $vencedores;
}

// (fase + bonus) x peso, arredondado UMA vez, meio para cima, em inteiros.
function calcular_pontos(int $fase, int $peso, bool $bonus): int {
    if (!in_array($peso, [100, 75, 50], true)) {
        throw new InvalidArgumentException("peso invalido: $peso");
    }
    $base = pontos_da_fase($fase) + ($bonus ? BONUS_SELETIVA : 0);
    return intdiv($base * $peso * 2 + 100, 200);
}
```

- [ ] **Passo 4: rodar e ver passar**

Run: `php tests/pontos.test.php`
Expected: `TUDO VERDE`.

- [ ] **Passo 5: sabotar**

Cada um deve imprimir `mutante morto`.
```bash
cd ~/projetos/forward/liga
# o meio deixa de subir (27,5 -> 27)
tests/mutar.sh 's/\+ 100, 200\)/+ 99, 200)/' tests/pontos.test.php
# o bonus deixa de existir
tests/mutar.sh 's/BONUS_SELETIVA : 0\)/0 : 0)/' tests/pontos.test.php
# o bonus passa a valer 10
tests/mutar.sh 's/BONUS_SELETIVA = 5;/BONUS_SELETIVA = 10;/' tests/pontos.test.php
# o empate deixa de receber
tests/mutar.sh 's/\$n == \$maior/$n === max(array_keys($notas))/' tests/pontos.test.php
# todo mundo recebe
tests/mutar.sh 's/\$n == \$maior/true/' tests/pontos.test.php
# arredonda para baixo
tests/mutar.sh 's/\* 2 \+ 100, 200\)/* 2, 200)/' tests/pontos.test.php
```
Se o quarto disser `ABORTADO` por sintaxe, troque a expressao por qualquer outra que quebre o criterio do empate e mantenha o PHP valido, por exemplo `s/\$n == \$maior/$n > $maior - 100 \&\& $n < 0/`.

- [ ] **Passo 6: suite inteira**

Run: `tests/rodar.sh`
Expected: `SUITE VERDE`.

---

### Tarefa 5: Tiers e ranking

**Arquivos:**
- Criar: `tests/ranking.test.php`
- Modificar: `pontuacao.php`

**Interfaces:**
- Produz: `tier_da_posicao(int $posicao): string`, `montar_ranking(array $linhas): array` (formato na secao "Interface final do motor").

- [ ] **Passo 1: escrever o teste**

`tests/ranking.test.php`:
```php
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

fim();
```

- [ ] **Passo 2: rodar e ver falhar**

Run: `php tests/ranking.test.php`
Expected: `Call to undefined function tier_da_posicao()`.

- [ ] **Passo 3: implementar**

Acrescentar a `pontuacao.php`:
```php

function tier_da_posicao(int $posicao): string {
    if ($posicao < 1) {
        throw new InvalidArgumentException("posicao invalida: $posicao");
    }
    return $posicao <= 5 ? 'S' : ($posicao <= 10 ? 'A' : ($posicao <= 20 ? 'B' : 'C'));
}

// Elegibilidade mora AQUI, nao espalhada pelo codigo: so conta linha de
// dançarino confirmado em evento aprovado (regulamento 2.1 e secao 4).
function montar_ranking(array $linhas): array {
    $por = [];
    foreach ($linhas as $l) {
        if (!$l['confirmado'] || !$l['evento_aprovado']) continue;
        $id = $l['dancarino_id'];
        $por[$id] ??= ['dancarino_id' => $id, 'nome' => $l['nome'], 'crew' => $l['crew'],
                       'pontos' => 0, 'participacoes_oficiais' => 0];
        $por[$id]['pontos'] += $l['pontos'];
        if ($l['tipo_evento'] === 'oficial') $por[$id]['participacoes_oficiais']++;
    }
    $lista = array_values($por);
    usort($lista, fn($a, $b) =>
        [$b['pontos'], $b['participacoes_oficiais'], $a['nome']]
        <=> [$a['pontos'], $a['participacoes_oficiais'], $b['nome']]);

    $posicao = 0;
    $anterior = null;
    foreach ($lista as $i => $r) {
        if ($anterior === null
            || $r['pontos'] !== $anterior['pontos']
            || $r['participacoes_oficiais'] !== $anterior['participacoes_oficiais']) {
            $posicao = $i + 1;
        }
        $lista[$i] = ['posicao' => $posicao, 'tier' => tier_da_posicao($posicao)] + $r;
        $anterior = $r;
    }
    return $lista;
}
```

- [ ] **Passo 4: rodar e ver passar**

Run: `php tests/ranking.test.php`
Expected: `TUDO VERDE`.

- [ ] **Passo 5: sabotar**

Cada um deve imprimir `mutante morto`.
```bash
cd ~/projetos/forward/liga
# cortes de tier deslocados, um por vez
tests/mutar.sh 's/\$posicao <= 5 /$posicao <= 4 /' tests/ranking.test.php
tests/mutar.sh 's/\$posicao <= 10 /$posicao <= 9 /' tests/ranking.test.php
tests/mutar.sh 's/\$posicao <= 20 /$posicao <= 19 /' tests/ranking.test.php
# pendente passa a pontuar
tests/mutar.sh "s/!\\\$l\['confirmado'\] \|\| //" tests/ranking.test.php
# evento nao aprovado passa a pontuar
tests/mutar.sh "s/ \|\| !\\\$l\['evento_aprovado'\]//" tests/ranking.test.php
# o desempate por participacao oficial some
tests/mutar.sh "s/\\\$[ab]\['participacoes_oficiais'\],/0,/g" tests/ranking.test.php 2
# evento de parceiro passa a contar como oficial
tests/mutar.sh "s/=== 'oficial'\) \\\$por/!== 'oficial') \$por/" tests/ranking.test.php
# empate deixa de dividir a posicao
tests/mutar.sh 's/\|\| \$r\[.pontos.\] !== \$anterior\[.pontos.\]/|| true/' tests/ranking.test.php
```
Se algum disser `ABORTADO`, o sed nao casou: leia o `pontuacao.php` real e ajuste a expressao, sem enfraquecer o teste. Se disser `MUTANTE SOBREVIVEU`, falta uma asercao no teste.

- [ ] **Passo 6: suite inteira**

Run: `tests/rodar.sh`
Expected: `SUITE VERDE`.

---

### Tarefa 6: Premiacao

**Arquivos:**
- Criar: `tests/premiacao.test.php`
- Modificar: `pontuacao.php`

**Interfaces:**
- Produz: `PREMIACAO`, `distribuir_premiacao(int $centavos): array` com 5 inteiros cuja soma e exatamente `$centavos`. A sobra de arredondamento vai para o primeiro lugar.

- [ ] **Passo 1: escrever o teste**

`tests/premiacao.test.php`:
```php
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
```

- [ ] **Passo 2: rodar e ver falhar**

Run: `php tests/premiacao.test.php`
Expected: `Call to undefined function distribuir_premiacao()`.

- [ ] **Passo 3: implementar**

Acrescentar a `pontuacao.php`:
```php

const PREMIACAO = [40, 25, 15, 10, 10];

// Em centavos inteiros, nunca em float: dinheiro nao pode perder centavo
// por arredondamento. A sobra vai para o primeiro lugar.
function distribuir_premiacao(int $centavos): array {
    if ($centavos < 0) {
        throw new InvalidArgumentException("bolo negativo: $centavos");
    }
    $fatias = array_map(fn($p) => intdiv($centavos * $p, 100), PREMIACAO);
    $fatias[0] += $centavos - array_sum($fatias);
    return $fatias;
}
```

- [ ] **Passo 4: rodar e ver passar**

Run: `php tests/premiacao.test.php`
Expected: `TUDO VERDE`.

- [ ] **Passo 5: sabotar**

Cada um deve imprimir `mutante morto`.
```bash
cd ~/projetos/forward/liga
# a sobra se perde (a soma deixa de fechar)
tests/mutar.sh 's/^\s*\$fatias\[0\] \+= .*$/        \/\/ sobra descartada/' tests/premiacao.test.php
# a sobra vai para o ultimo, nao para o primeiro
tests/mutar.sh 's/\$fatias\[0\] \+=/$fatias[4] +=/' tests/premiacao.test.php
# percentuais errados
tests/mutar.sh 's/\[40, 25, 15, 10, 10\]/[40, 25, 15, 15, 5]/' tests/premiacao.test.php
```

- [ ] **Passo 6: suite inteira**

Run: `tests/rodar.sh`
Expected: `SUITE VERDE`.

---

### Tarefa 7: Varredura final

**Arquivos:** nenhum novo.

- [ ] **Passo 1: suite inteira, do zero**

Run: `cd ~/projetos/forward/liga && tests/rodar.sh`
Expected: cinco arquivos, todos `TUDO VERDE`, e `SUITE VERDE`.

- [ ] **Passo 2: o motor e mesmo puro**

Run: `grep -nE 'require|include|mysqli|PDO|\$_(GET|POST|SESSION|SERVER)|file_get_contents|date\(|time\(' pontuacao.php`
Expected: nenhuma linha. Qualquer saida e violacao da arquitetura: o motor nao toca em banco, requisicao, sessao nem relogio.

- [ ] **Passo 3: o motor nao ficou sabotado**

Run: `php -l pontuacao.php && grep -c 'BONUS_SELETIVA = 5;' pontuacao.php && grep -c 'intdiv(\$base \* \$peso \* 2 + 100, 200)' pontuacao.php`
Expected: `No syntax errors detected`, `1`, `1`. Prova que o `mutar.sh` restaurou o arquivo em todas as sabotagens.

- [ ] **Passo 4: cobertura contra a spec**

Conferir, item por item da secao 8 da spec, que existe teste: sete fases e fase invalida (T2); faixas de peso nas bordas 15/16/31/32 (T3); arredondamento 33,75 e 27,5 (T4); bonificacao sem nota, com maior e com empate (T4); bonus multiplicado pelo peso (T4); fase impossivel na chave (T2); desempate oficial (T5); cortes de tier nas posicoes 5/6/10/11/20/21 (T5); pendente nao pontua e evento nao aprovado nao pontua (T5); soma da premiacao fecha (T6). Os itens "participante com `dancarino_id` nulo nao gera resultado" e "papel nao acessa o que nao e dele" pertencem ao plano 2, porque dependem de banco e de `api.php`.

---

## Fora deste plano

- Tabelas, `comum.php`, `api.php`, papeis, controle de versao de escrita: plano 2.
- Telas e a area do parceiro: plano 3.
- Instalacao, vhost e dominio: plano 4.
- Opcao B (chave clicavel, `fase_do_chaveamento`, tabela `batalhas`): fora da v1, decidido em 19/10/2026.
- Premiacao com empate nas posicoes 1 a 5 (dois dançarinos na mesma posicao dividem a faixa?): o regulamento nao diz. O motor entrega a posicao compartilhada e deixa a regra de divisao para decisao do Matheus antes do plano 2.
