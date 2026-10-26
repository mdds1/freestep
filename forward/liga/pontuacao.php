<?php
// Motor de pontuacao da Liga Free Step. Funcoes puras: sem banco, sem HTTP.
// Regras em regulamento.md; decisoes em spec-sistema.md.
declare(strict_types=1);

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
        if (!is_int($fase)) {
            $erros[] = "participante $participante: fase nao inteira";
            continue;
        }
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

const BONUS_SELETIVA = 5;

// Recebe [participante_id => nota]. Devolve os ids que empataram na maior nota.
// A nota e do proprio evento: nunca se compara nota entre eventos.
function bonus_da_seletiva(array $notas): array {
    if ($notas === []) return [];
    foreach ($notas as $id => $nota) {
        if (!is_int($nota) && !is_float($nota)) {
            throw new InvalidArgumentException("nota invalida para o participante $id");
        }
        if (!is_finite((float) $nota)) {
            throw new InvalidArgumentException("nota nao finita para o participante $id");
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

function tier_da_posicao(int $posicao): string {
    if ($posicao < 1) {
        throw new InvalidArgumentException("posicao invalida: $posicao");
    }
    return $posicao <= 5 ? 'S' : ($posicao <= 10 ? 'A' : ($posicao <= 20 ? 'B' : 'C'));
}

function validar_linha_do_ranking(array $l): void {
    foreach (['dancarino_id', 'nome', 'crew', 'pontos', 'tipo_evento', 'confirmado', 'evento_aprovado'] as $chave) {
        if (!array_key_exists($chave, $l)) {
            throw new InvalidArgumentException("linha sem a chave $chave");
        }
    }
    if (!is_bool($l['confirmado']) || !is_bool($l['evento_aprovado'])) {
        throw new InvalidArgumentException('confirmado e evento_aprovado devem ser bool');
    }
    if (!in_array($l['tipo_evento'], ['oficial', 'parceiro'], true)) {
        throw new InvalidArgumentException('tipo_evento deve ser oficial ou parceiro');
    }
    if (!is_int($l['pontos']) || $l['pontos'] < 0) {
        throw new InvalidArgumentException('pontos deve ser inteiro nao negativo');
    }
}

// Elegibilidade mora AQUI, nao espalhada pelo codigo: so conta linha de
// dançarino confirmado em evento aprovado (regulamento 2.1 e secao 4).
function montar_ranking(array $linhas): array {
    foreach ($linhas as $l) {
        validar_linha_do_ranking($l);
    }
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
