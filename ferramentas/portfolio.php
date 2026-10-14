<?php
/**
 * Preenche os blocos do midia kit de cada membro.
 *
 * Fonte: os midia kits e curriculos que o time enviou pelo Drive (maio de
 * 2026). Sao os documentos que eles mandam para contratante e imprensa.
 *
 * REGRA: informacao fiel ao documento. O que o site faz e padronizar a
 * apresentacao, nao reescrever o conteudo. Nao trocar palavra, nao resumir
 * e nao tirar acento.
 *
 * Unica coisa retirada: dado pessoal. Os curriculos trazem endereco
 * residencial, telefone, email particular e data de nascimento, que nao vao
 * para site publico.
 *
 * Conquistas obtidas com outros grupos (Los Cocas, 7Minutoz) ficam aqui, na
 * pagina individual, e nunca na secao de imprensa do time: atribuir ao Time
 * Free Step o que foi de outro grupo seria falso, e contratante confere.
 *
 * Rodar com:
 *   sudo php wp-cli.phar --allow-root --path=/var/www/freestep \
 *     eval-file ferramentas/portfolio.php
 *
 * Idempotente: sobrescreve os campos, nao acumula.
 */

$portfolio = [

    'Alves' => [
        // Midia Kit att Gabriel Alves 08052026.pdf
        'numeros' => [
            'TikTok | 2,3 milhões',
            'YouTube | 1,5 milhão',
            'Facebook | 1,2 milhão',
            'Instagram | 486 mil',
        ],
        'conquistas' => [
            'Membro da atual Seleção Brasileira de Free Step Dance',
            '2º e 3º lugar no Mundial, categoria grupos (março/2025)',
            'Campeão em Lima, Peru, 2025, categoria 1vs1: único brasileiro a vencer um torneio fora do país',
            'Mais de 7 milhões de seguidores somados e mais de 2 bilhões de visualizações',
            'Influenciador com maior número de seguidores da América Latina em seu nicho',
        ],
    ],

    'Gu Henrique' => [
        // Currículo Artístico - Gustavo Henrique.docx
        'conquistas' => [
            'Bicampeão Nacional com o grupo Los Cocas, 2016 e 2017',
            '3º lugar no Campeonato Mundial de Danças Eletrônicas, Shuffle Showcase, categoria 3 vs 3, Barcelona, Espanha',
            'Campeão Individual no Meet Up Suzano',
            'Campeão Individual no Meet Up Fábricas de Cultura Itaim',
            'Campeão de Dupla no Torneio Oldschool em Santo André',
            'Workshop Mega Class, Tocantins',
        ],
        'imprensa' => [
            'Dez ou Mil | SBT, com o grupo Los Cocas',
            "Let's Dance | MTV, reality show de dança organizado pela Samsung",
            'Circo do Tiru | SBT, performance com o grupo Los Cocas',
        ],
    ],

    'Jeff' => [
        // MÍDIA KIT JEFF TJ.pdf
        'paises' => 'Áustria, Eslovênia, Dinamarca, Portugal, Espanha, Peru, Itália, Alemanha, Eslováquia, Suíça, Hungria, Holanda, Croácia, França, República Tcheca, Brasil',
        'conquistas' => [
            'Condutor da Tocha Olímpica nos Jogos Olímpicos Rio 2016, na passagem por Palmas',
            'Líder do Time Free Step Brasil, seleção brasileira de Danças Eletrônicas',
            '17 anos de carreira na dança e pós-graduação em Danças Urbanas',
            'Apresentações, competições, júri e workshops em mais de 15 países',
        ],
        'imprensa' => [
            'Encontro com Fátima Bernardes | Globo Nacional',
            'Jornal Anhanguera 1ª Edição | Globo Tocantins',
            'Jornal Anhanguera 2ª Edição | Globo Tocantins',
            'Globo Esporte TO | Globo Tocantins',
            'Bom dia TO | Globo Tocantins',
            'Jornal do Meio Dia | TVE Tocantins',
            'TVE Esportes | TVE Tocantins',
            'Repórter Tocantins | TVE Tocantins',
            'Programa Rodrigo Blamires | SBT Tocantins',
            'De Papo com Núbia Dourado | SBT Tocantins',
            'Balanço Geral TO | Record Tocantins',
            'Programa Plágio na TV | Record Tocantins',
            'Bom dia 54 | Band Tocantins',
            'Jornal 54 | Band Tocantins',
            'Programa Viver Bem | Band Tocantins',
            'Canal do Povo | Band Tocantins',
            'O Povo na TV | TV Norte Tocantins',
            'Super Manhã | TV Norte Tocantins',
            'Alerta Tocantins | TV Norte Tocantins',
            'Tá na Hora Tocantins | TV Norte Tocantins',
            'Programa A Voz do Povo | Rede TV Tocantins',
            'Programa Fala Comunidade | Rede TV Tocantins',
            'Programa Revolução Cultural | TV Assembleia',
        ],
    ],

    'Takeshi' => [
        // CURRICULO - TAKESHI 2025.pdf
        'conquistas' => [
            'Melhor Dançarino de Free Step do Brasil, 2017',
            'Campeão em mais de 20 competições nacionais',
            '1º lugar no SUPER FREE STEP BRASIL, solo e grupo com os LosCocas, 2017',
            '2º lugar no Trio do Campeonato Mundial TSL, Espanha, 2025',
            '3º lugar no Solo de Shuffle Dance Elektronia, Peru, 2025',
            'Masterclass Free Step OFF-TSL, Espanha, 2025',
            'Masterclass Free Step Elektronia, Peru, 2025',
            'Coreógrafo e dançarino do grupo 7Minutoz no Rock In Rio 2024',
            'Dançarino na abertura da Copa América, CONMEBOL, 2019',
            'Fundador da comunidade Passos Livres, 2025, e do projeto TKSH Class, 2020',
        ],
        'imprensa' => [
            'Caldeirão | TV Globo, com os LosCocas, 2022',
        ],
        'marcas' => 'Rock In Rio, The Town, Amazon Music, Riachuelo, Converse, CONMEBOL, TikTok, AMBEV, TV Globo, SBT, Alok, Glória Groove, Haikaiss, Centauro, 99Food, Sennheiser Brasil, Peugeot',
    ],
];

$campos = [
    'numeros'    => 'membro_numeros',
    'conquistas' => 'membro_conquistas',
    'paises'     => 'membro_paises',
    'imprensa'   => 'membro_imprensa',
    'marcas'     => 'membro_marcas',
];

foreach ($portfolio as $nome => $dados) {
    $membro = get_page_by_title($nome, OBJECT, 'fs_membro');

    if (!$membro) {
        echo "ERRO: membro '{$nome}' nao encontrado", PHP_EOL;
        continue;
    }

    $preenchidos = [];

    foreach ($campos as $chave => $campo) {
        // Campo ausente para este membro e limpo de proposito: assim o
        // script continua sendo a fonte de verdade, em vez de deixar sobra
        // de uma rodada anterior.
        $valor = $dados[$chave] ?? '';

        if (is_array($valor)) {
            $valor = implode("\n", $valor);
        }

        update_field($campo, $valor, $membro->ID);

        if ($valor !== '') {
            $preenchidos[] = $chave;
        }
    }

    printf("%-12s (ID %d): %s%s", $nome, $membro->ID, implode(', ', $preenchidos), PHP_EOL);
}

echo PHP_EOL, "sem material no Drive: Jorg, Jux, Ederson", PHP_EOL;
