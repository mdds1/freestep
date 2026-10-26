<?php
/**
 * Preenche a biografia dos membros a partir do material do Drive.
 *
 * Fonte: midia kits e curriculos enviados pelo time (maio de 2026).
 *
 * TEXTO FIEL AO ORIGINAL. O time pediu que o que eles escreveram fosse
 * usado como escreveram, entao o texto abaixo segue o documento de cada um,
 * com a acentuacao correta. Nao reescrever com palavras proprias nem tirar
 * acento: e texto de autoria deles, publicado em nome deles.
 *
 * Unica coisa retirada: dado pessoal. Os curriculos trazem endereco
 * residencial, telefone, email particular e data de nascimento, e isso nao
 * vai para site publico.
 *
 * Conquistas obtidas com outros grupos (Los Cocas, 7Minutoz) ficam na
 * biografia individual, nunca na secao de imprensa do time: atribuir ao
 * Free Step Brazil Team o que foi de outro grupo seria falso, e contratante
 * confere.
 *
 * Rodar com:
 *   sudo php wp-cli.phar --allow-root --path=/var/www/freestep \
 *     eval-file ferramentas/biografias.php
 *
 * Idempotente: sobrescreve a biografia, nao acumula.
 */

$biografias = [
    'Alves' => <<<'TXT'
Gabriel Alves, conhecido por Mr Alves, reside em São Paulo, tem 29 anos, é professor de Danças Eletrônicas e Criador de Conteúdo.

Iniciou sua carreira na dança em 2009 e como criador de conteúdo em 2016.

Hoje é o influenciador com maior número de seguidores nas redes sociais da América Latina em seu nicho, somando mais de 7 Milhões e mais de 2 Bilhões em visualizações.

Ministra aulas, faz apresentações, é membro da atual Seleção Brasileira de Free Step Dance (2º e 3º lugar no Mundial, categoria grupos, março/25).

Único brasileiro a vencer um torneio fora do país, sendo campeão em Lima, Peru, em 2025, na categoria 1vs1.
TXT,

    'Gu Henrique' => <<<'TXT'
Co-idealizador da criação do grupo e na atuação ativa da coordenação e desenvolvimento artístico do Free Step Brazil Team, contribuindo para a criação de coreografias originais, performances de alto impacto e projetos que fortalecem a cena do Freestep nacional e internacional.

Produção de conteúdos para internet, workshops e participação em competições dentro e fora do Brasil, sempre representando o estilo com técnica, criatividade e autenticidade.

Iniciou na dança em 2010, participando de diversos campeonatos de Freestep por todo o Brasil, incluindo os estados de Maranhão, Pernambuco, Paraíba, Minas Gerais, Rio de Janeiro, São Paulo, Ceará e Tocantins (Palmas). Ao longo da carreira, atuou como competidor, jurado, atração convidada e representante do cenário brasileiro de Freestep em eventos nacionais e internacionais. Também participou de competições e projetos na Espanha, ampliando sua presença no cenário mundial da dança eletrônica.

Títulos de destaque: Bicampeão Nacional com o grupo Los Cocas (2016 e 2017), Campeão Individual no Meet Up Suzano, Campeão Individual no Meet Up Fábricas de Cultura Itaim, Campeão de Dupla no Torneio Oldschool em Santo André e 3º Lugar no Campeonato Mundial de Danças Eletrônicas, Shuffle Showcase, categoria 3 vs 3, em Barcelona, Espanha.

Participações em TV com o grupo Los Cocas: Dez ou Mil (SBT), Let's Dance (MTV) e Circo do Tiru (SBT).
TXT,

    'Jeff' => <<<'TXT'
Do Tocantins pro Mundo.

O dançarino brasileiro que transcendeu as barreiras da Arte e da cultura para se tornar um ícone de resiliência e inspiração para jovens pelo Mundo. Com 17 anos de carreira na Dança e pós-graduação em Danças Urbanas, atua como dançarino, formador e influenciador digital. Líder do Free Step Brazil Team, seleção brasileira de Danças eletrônicas, destaca-se por levar a dança brasileira para palcos, projetos e experiências internacionais, além de produzir conteúdo digital voltado para o FLASHBACK que alcança milhões de visualizações inspirando jovens, adultos e idosos na prática da dança.

O dançarino, enquanto pessoa com deficiência (PCD), torna-se uma poderosa fonte de inspiração para milhares de jovens que enfrentam diariamente o preconceito e a exclusão social. Sua trajetória demonstra, com sensibilidade e força, que as limitações impostas pela sociedade podem ser superadas por meio da arte, da dedicação e da persistência, incentivando outros a acreditarem em seu potencial e a ocuparem seus espaços com orgulho e determinação.

Presente em mais de 15 países: Áustria, Eslovênia, Dinamarca, Portugal, Espanha, Peru, Itália, Alemanha, Eslováquia, Suíça, Hungria, Holanda, Croácia, França, República Tcheca e Brasil. Ao longo de sua carreira, participou de competições, realizou performances como jurado, conquistou títulos, ministrou workshops e aulas, além de marcar presença em diversos festivais de dança.

Olimpíadas Rio 2016, Condutor: em reconhecimento ao seu relevante trabalho em prol da cultura e da dança brasileira, bem como por sua atuação inspiradora junto a jovens PCD, foi convidado a integrar o seleto grupo de condutores da Tocha Olímpica nos Jogos Olímpicos Rio 2016. Durante o percurso que atravessou todas as capitais do país, teve a honra de conduzir a tocha em sua passagem por Palmas, representando com orgulho a cidade e reafirmando seu compromisso com a inclusão, a arte e a transformação social.

Imprensa: já participou de diversos programas de TV, tanto locais quanto nacionais, entre eles o Encontro com Fátima Bernardes (Globo Nacional), onde apresentou sua inspiradora trajetória de superação por meio da dança.
TXT,

    'Takeshi' => <<<'TXT'
Dançarino, coreógrafo e criador de conteúdo com 15 anos de atuação na cena das danças eletrônicas e urbanas. Profissional com carreira sólida em palcos e presença digital, unindo performance, criatividade e produção de conteúdo autoral.

Reconhecimento: campeão em mais de 20 competições nacionais, incluindo o título de Melhor Dançarino de Free Step do Brasil (2017).

Experiência internacional: membro do Free Step Brazil Team, ministrou Masterclasses Internacionais de Free Step na Espanha (The Showcase League 2025) e no Peru (Elektronia 2025). 2º lugar no Trio do Campeonato Mundial TSL (Espanha, 2025) e 3º lugar no Solo de Shuffle Dance Elektronia (Peru, 2025).

Atuação profissional: possui vasta experiência como instrutor, jurado, modelo, performer em shows, espetáculos, videoclipes, programas de TV e em criação de campanhas digitais.

Parcerias de destaque: trabalhou com grandes marcas e artistas como Rock In Rio, The Town, Amazon Music, Riachuelo, Converse, CONMEBOL, TikTok, AMBEV, TV Globo, SBT, Alok, Glória Groove e Haikaiss, entre outros. Foi coreógrafo e dançarino do grupo 7Minutoz no Rock In Rio 2024 e dançarino na abertura da Copa América (CONMEBOL, 2019).

Fundador da comunidade Passos Livres (2025) e do projeto TKSH Class, aulas online de dança (2020).
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
