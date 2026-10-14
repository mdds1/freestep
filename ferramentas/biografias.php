<?php
/**
 * Preenche a biografia dos membros a partir do material do Drive.
 *
 * Fonte: midia kits e curriculos enviados pelo time (maio de 2026).
 *
 * Regra aplicada ao extrair: NADA de dado pessoal. Os curriculos trazem
 * endereco residencial, telefone, email particular e data de nascimento, e
 * isso nao vai para um site publico. Entra so carreira, titulos, numeros de
 * audiencia e participacoes.
 *
 * Conquistas obtidas com outros grupos (Los Cocas, 7Minutoz) ficam na
 * biografia individual do membro, nunca na secao de imprensa do time:
 * atribuir ao Time Free Step o que foi de outro grupo seria falso, e
 * contratante confere.
 *
 * Rodar com:
 *   sudo php wp-cli.phar --allow-root --path=/var/www/freestep \
 *     eval-file ferramentas/biografias.php
 *
 * Idempotente: sobrescreve a biografia, nao acumula.
 */

$biografias = [
    'Alves' => <<<'TXT'
Gabriel Alves, conhecido como Mr Alves, e de Sao Paulo e atua como professor de dancas eletronicas e criador de conteudo. Comecou na danca em 2009 e passou a produzir conteudo em 2016.

Hoje e o influenciador com maior numero de seguidores do seu nicho na America Latina, somando mais de 7 milhoes de seguidores e mais de 2 bilhoes de visualizacoes. Em maio de 2026 eram 2,3 milhoes no TikTok, 1,5 milhao no YouTube, 1,2 milhao no Facebook e 486 mil no Instagram.

Integra a Selecao Brasileira de Free Step Dance, que ficou em segundo e terceiro lugar no Mundial de marco de 2025 na categoria grupos. E o unico brasileiro a vencer um torneio fora do pais, campeao em Lima, no Peru, em 2025, na categoria 1 contra 1. Ministra aulas e se apresenta no Brasil e no exterior.
TXT,

    'Gu Henrique' => <<<'TXT'
Gustavo Henrique, o Gu Henrique, e de Ferraz de Vasconcelos, em Sao Paulo, e co-idealizador do Time Free Step Brasil. Atua na coordenacao e no desenvolvimento artistico do time, criando coreografias originais e projetos que fortalecem a cena do Free Step nacional e internacional.

Comecou na danca em 2010 e competiu em campeonatos por todo o Brasil, passando por Maranhao, Pernambuco, Paraiba, Minas Gerais, Rio de Janeiro, Sao Paulo, Ceara e Tocantins. Ao longo da carreira atuou como competidor, jurado, atracao convidada e representante do cenario brasileiro em eventos nacionais e internacionais, incluindo competicoes e projetos na Espanha.

Entre os titulos estao o bicampeonato nacional com o grupo Los Cocas, em 2016 e 2017, e o terceiro lugar no Campeonato Mundial de Dancas Eletronicas, categoria Shuffle Showcase 3 contra 3, em Barcelona. Na televisao, participou do Dez ou Mil e do Circo do Tiru, no SBT, e do reality Let's Dance, na MTV, todos com o Los Cocas.
TXT,

    'Jeff' => <<<'TXT'
Jeff TJ e do Tocantins e lidera o Time Free Step Brasil. Sao 17 anos de carreira na danca e pos-graduacao em Dancas Urbanas, atuando como dancarino, formador e influenciador digital.

Ja se apresentou em mais de 15 paises, entre eles Austria, Eslovenia, Dinamarca, Portugal, Espanha, Peru, Italia, Alemanha, Eslovaquia, Suica, Hungria, Holanda, Croacia, Franca e Republica Tcheca, participando de competicoes, atuando como jurado e ministrando workshops.

Em 2016 foi convidado a integrar o grupo de condutores da Tocha Olimpica dos Jogos do Rio, conduzindo a tocha na passagem por Palmas. Na televisao, participou do Encontro com Fatima Bernardes, na Globo, alem de mais de vinte programas regionais no Tocantins, em emissoras como Globo, SBT, Record, Band e TVE.

Pessoa com deficiencia, usa a propria trajetoria para mostrar que as limitacoes impostas pela sociedade podem ser superadas pela arte, pela dedicacao e pela persistencia.
TXT,

    'Takeshi' => <<<'TXT'
Guilherme Takeshi Araki, o Takeshi, e de Mogi das Cruzes, em Sao Paulo, e soma 15 anos de atuacao na cena das dancas eletronicas e urbanas, como dancarino, coreografo e criador de conteudo.

Foi campeao em mais de 20 competicoes nacionais e levou o titulo de Melhor Dancarino de Free Step do Brasil em 2017. Em 2025 ficou em segundo lugar na categoria trio do Campeonato Mundial TSL, na Espanha, e em terceiro no solo de Shuffle Dance do Elektronia, no Peru, alem de ministrar masterclasses internacionais nos dois paises.

Foi coreografo e dancarino do grupo 7Minutoz no Rock In Rio 2024 e dancou na abertura da Copa America de 2019, pela CONMEBOL. Participou de videoclipes de artistas como Justin Timberlake, Tate McRae, Gloria Groove e Haikaiss, e de campanhas para marcas como Amazon Music, Converse, Riachuelo e Centauro. E fundador da comunidade Passos Livres.
TXT,
];

foreach ($biografias as $nome => $bio) {
    $membro = get_page_by_title($nome, OBJECT, 'fs_membro');

    if (!$membro) {
        echo "ERRO: membro '{$nome}' nao encontrado", PHP_EOL;
        continue;
    }

    update_field('membro_bio', trim($bio), $membro->ID);
    echo "biografia gravada: {$nome} (ID {$membro->ID})", PHP_EOL;
}

// Jorg, Jux e Ederson ficam de fora: nao ha material deles no Drive. O
// Ederson entrou ha pouco; a pasta dele veio vazia.
echo PHP_EOL, "sem biografia (falta material): Jorg, Jux, Ederson", PHP_EOL;
