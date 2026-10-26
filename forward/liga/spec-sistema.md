# Sistema da Liga Free Step - Especificacao de Design

Data: 19/10/2026
Fonte das regras: `regulamento.md`, nesta mesma pasta.

Este documento descreve o que vai ser construido e por que. Ele e a referencia
para a implementacao; se o codigo divergir daqui, um dos dois esta errado.

---

## 1. Objetivo

Sistema proprio para operar a Liga Free Step Series 1: ranking publico da
temporada, lancamento de resultados, controle financeiro das inscricoes,
homologacao de eventos parceiros e inscricao aberta de dançarinos.

## 2. Decisoes fechadas

Decididas com o Matheus em 19/10/2026.

| Assunto | Decisao |
|---|---|
| Onde roda | Infraestrutura propria, no padrao da Gincana |
| Banco | MySQL, banco proprio `liga_freestep` |
| Papeis | Administracao da Liga, organizador parceiro, publico sem conta |
| Conta de dançarino | Nao existe. Inscricao por formulario aberto, confirmacao manual |
| Exposicao | Preparado para expor, sem dominio fixo no codigo. Dominio novo a comprar |
| Temporadas | Somente a Series 1 |
| Escopo | Completo: motor, financeiro, parceiros e inscricao publica |
| Entrada de resultado | Parceiro **seleciona** da lista de inscritos. Nunca digita nome |

### Decisoes tomadas em 19/10/2026 (antes pendentes)

| Assunto | Decisao |
|---|---|
| Arredondamento | Inteiro mais proximo, meio para cima, uma unica vez sobre o total do evento. Ja no regulamento |
| Forma da chave | **Opcao A**, selecao com fase validada. A opcao B (chave clicavel, tabela `batalhas`) fica para depois e nao exige retrabalho |
| Inscricao retroativa | Nao existe. As inscricoes fecham antes do primeiro evento; os eventos correm durante o ano e a premiacao sai no fim do ciclo |
| Bonificacao da seletiva | Confirmada: 5 pontos para a maior nota, em oficial e parceiro, empate recebe integral, multiplicada pelo peso |

---

## 3. Arquitetura

Padrao da Gincana, com uma diferenca deliberada: o motor de pontuacao e isolado.

```
~/projetos/forward/liga/
  liga.conf              vhost apache, versionado
  instalar.sh            copia para o docroot
  config.exemplo.php     modelo versionado
  config.php             credenciais e dominio, FORA do git

  comum.php              conexao, sessao, papeis, helpers
  pontuacao.php          O MOTOR. Funcoes puras, sem banco e sem HTTP
  api.php                toda escrita passa por aqui
  login.php / sair.php
  usuarios.php           CLI de gestao de contas

  publico.php            portaria publica
  ranking.html           ranking da temporada
  inscricao.html         formulario aberto de inscricao
  evento.html            detalhe de um evento, somente leitura

  admin.php / admin.html        painel da administracao
  parceiro.php / parceiro.html  area do organizador parceiro

  tests/*.test.php
```

No docroot, os `.html` ficam legiveis por todos e os `.php` somente pelo
`www-data`, como ja e feito na Gincana.

### Por que o motor e isolado

As regras do regulamento sao a parte que mais muda: a tabela de pesos ja foi
corrigida uma vez durante o desenho, e a bonificacao da seletiva apareceu
depois. Com `pontuacao.php` sem dependencia de banco nem de HTTP, cada regra e
testavel sozinha e pode ser quebrada de proposito para provar que o teste pega.

### Dominio

Nenhum hostname aparece no codigo. Tudo vem de `config.php`. Enquanto nao
houver dominio comprado, o sistema roda interno; a exposicao e ligada depois
sem alterar codigo.

---

## 4. Modelo de dados

Banco `liga_freestep`, MySQL, utf8mb4.

### usuarios
| Coluna | Tipo | Nota |
|---|---|---|
| id | INT PK AI | |
| nome | VARCHAR(120) | |
| email | VARCHAR(190) UNIQUE | login |
| senha_hash | VARCHAR(255) | `password_hash` |
| papel | ENUM('admin','parceiro') | |
| ativo | TINYINT(1) DEFAULT 1 | desativar sem apagar |
| criado_em | DATETIME | |

### dancarinos
| Coluna | Tipo | Nota |
|---|---|---|
| id | INT PK AI | |
| nome | VARCHAR(120) | como o dançarino escreveu |
| nome_normalizado | VARCHAR(120) INDEX | minuscula, sem acento |
| crew | VARCHAR(120) NULL | |
| contato | VARCHAR(190) NULL | **nunca sai para parceiro** |
| situacao | ENUM('pendente','confirmado','recusado') | |
| confirmado_em | DATETIME NULL | para a regra de retroatividade |
| criado_em | DATETIME | |

**Somente `confirmado` pontua.** E assim que a regra de elegibilidade da secao
2.1 do regulamento vira estrutura, e nao verificacao espalhada pelo codigo.

`nome_normalizado` continua existindo, mas agora para **avisar de duplicata na
inscricao** ("ja existe um Joao Silva, e voce?"), nao para casar lista de
parceiro. Essa segunda funcao morreu com a selecao da lista.

### participantes_evento
Quem competiu num evento, inscrito na Liga ou nao.

| Coluna | Tipo | Nota |
|---|---|---|
| id | INT PK AI | |
| evento_id | INT FK | |
| dancarino_id | INT NULL FK | nulo quando e de fora da Liga |
| nome_externo | VARCHAR(120) NULL | so quando `dancarino_id` e nulo |
| criado_em | DATETIME | |

Existe por uma razao concreta: a chave do parceiro precisa ficar honesta. Se
oito pessoas competiram e tres nao sao da Liga, a chave mostra oito. Mas linha
com `dancarino_id` nulo **nunca gera resultado** e nunca entra no ranking, por
construcao, nao por verificacao.

`UNIQUE KEY (evento_id, dancarino_id)` com nulo permitido.

### pagamentos
| Coluna | Tipo | Nota |
|---|---|---|
| id | INT PK AI | |
| dancarino_id | INT FK | |
| valor | DECIMAL(10,2) | |
| data_pagamento | DATE | |
| registrado_por | INT FK usuarios | quem confirmou |
| observacao | VARCHAR(255) NULL | |

### eventos
| Coluna | Tipo | Nota |
|---|---|---|
| id | INT PK AI | |
| nome | VARCHAR(160) | |
| tipo | ENUM('oficial','parceiro') | |
| organizador_id | INT NULL FK usuarios | nulo quando oficial |
| data_evento | DATE NULL | |
| inscritos_freestep | INT NULL | so para parceiro, define o peso |
| peso | DECIMAL(4,3) NULL | **congelado** na homologacao |
| tamanho_chave | SMALLINT NULL | 4, 8, 16 ou 32. Valida a fase maxima |
| tem_seletiva | TINYINT(1) DEFAULT 0 | liga a aba de notas |
| situacao | ENUM('rascunho','pendente','aprovado','negado') | |
| enviado_em | DATETIME NULL | para medir o prazo de 7 dias |
| homologado_em | DATETIME NULL | |
| homologado_por | INT NULL FK usuarios | |

Evento oficial nasce `aprovado` com `peso` = 1.000.

### seletiva
Nota da pre-seletiva, uma linha por participante. So existe quando
`eventos.tem_seletiva = 1`.

| Coluna | Tipo | Nota |
|---|---|---|
| id | INT PK AI | |
| participante_id | INT FK participantes_evento | |
| nota | DECIMAL(6,2) | escala livre, definida pelo evento |
| criado_em | DATETIME | |

`UNIQUE KEY (participante_id)`.

A nota e **do evento, nao da Liga**: a Liga nao normaliza escala, nao compara
nota entre eventos e nao pontua por nota. Ela so olha quem foi o maior do
proprio evento. Isso evita a armadilha de um evento que da nota de 0 a 10 e
outro de 0 a 100 injetarem pontos diferentes no mesmo ranking.

### resultados
| Coluna | Tipo | Nota |
|---|---|---|
| id | INT PK AI | |
| evento_id | INT FK | |
| dancarino_id | INT FK | NOT NULL. Nunca um nome em texto |
| fase | SMALLINT | 5, 15, 21, 27, 35, 45 ou 55 |
| bonus_seletiva | TINYINT(1) DEFAULT 0 | tirou a maior nota do evento |
| pontos | INT | **congelado** no lancamento, ja com peso e bonus |
| criado_em | DATETIME | |

`UNIQUE KEY (evento_id, dancarino_id)`. Reenvio corrige, nunca duplica.

`bonus_seletiva` e derivado da tabela `seletiva` no momento do lancamento e
depois congelado junto com os pontos, pelo mesmo motivo.

### batalhas (somente na opcao B)
Nao existe na opcao A. Ver secao 6.3.

| Coluna | Tipo | Nota |
|---|---|---|
| id | INT PK AI | |
| evento_id | INT FK | |
| rodada | SMALLINT | 1 = primeira rodada da chave |
| posicao | SMALLINT | posicao da batalha dentro da rodada |
| participante_a | INT NULL FK participantes_evento | |
| participante_b | INT NULL FK participantes_evento | nulo = bye |
| vencedor | INT NULL FK participantes_evento | |

`UNIQUE KEY (evento_id, rodada, posicao)`.

### Por que peso e pontos sao congelados

O regulamento apura o numero de inscritos no fechamento das inscricoes. Se um
parceiro corrigir esse numero meses depois, ponto ja distribuido nao pode mudar
sozinho nas costas de ninguem. Pelo mesmo motivo, `resultados.pontos` guarda o
valor calculado no lancamento: consertar uma regra em marco nao reescreve
janeiro.

Recalcular existe, como acao explicita no painel da administracao, registrada e
decidida pelo Matheus. Nunca como efeito colateral de leitura.

---

## 5. O motor (`pontuacao.php`)

Funcoes puras, sem estado.

```
pontos_da_fase(int $fase): int
    Valida que a fase e uma das sete. Fase invalida e erro, nao zero.

fase_valida_na_chave(int $fase, int $tamanho): bool
    Oitavas (21) so existem em chave de 16 ou mais; quartas (27) em chave de
    8 ou mais. 5, 15, 35, 45 e 55 valem em qualquer tamanho (15 cobre rodada
    extra ou grupos, regulamento 2.3). Tamanhos validos: 4, 8, 16, 32.

validar_distribuicao_de_fases(array $fases, int $tamanho): array
    Lista de mensagens de erro, vazia quando esta tudo certo. Limites por
    chave: 1 campeao, 1 vice, 2 semifinalistas, 4 nas quartas, 8 nas oitavas.
    Evento em andamento (so parte lancada) e aceito.

peso_do_evento(string $tipo, ?int $inscritos): int
    Percentual inteiro, nunca float.
    oficial -> 100 | parceiro 32+ -> 100 | 16 a 31 -> 75 | menos de 16 -> 50

bonus_da_seletiva(array $notas): array
    Recebe participante_id => nota. Devolve os ids empatados na maior nota.
    Lista vazia quando nao ha nota lancada.
    Nao arredonda nota e nao compara nota entre eventos.

calcular_pontos(int $fase, int $peso, bool $bonus): int
    (fase + (bonus ? 5 : 0)) x peso%, arredondado UMA vez, meio para cima,
    em aritmetica inteira: intdiv(base * peso * 2 + 100, 200).
    O bonus entra ANTES do peso, conforme o regulamento.

montar_ranking(array $linhas): array
    Filtra (so confirmado em evento aprovado), soma por dançarino, ordena por
    pontos desc e, no empate, por participacoes em evento OFICIAL desc.
    Empate completo divide a posicao (1, 1, 3), e dentro dele ordena por nome.

tier_da_posicao(int $posicao): string
    1 a 5 -> S | 6 a 10 -> A | 11 a 20 -> B | 21 em diante -> C

distribuir_premiacao(int $centavos): array
    40 / 25 / 15 / 10 / 10 por cento, em centavos inteiros. A sobra do
    arredondamento vai para o primeiro lugar e a soma fecha o bolo.

fase_do_chaveamento(array $batalhas, int $participante): int   (opcao B, fora da v1)
    Deduz a fase a partir da ultima rodada em que o participante venceu.
```

O ranking so considera dançarino com `situacao = 'confirmado'` e resultado de
evento com `situacao = 'aprovado'`.

---

## 6. Fluxos por papel

### 6.1 Publico, sem login
- **Ranking**: posicao, nome, crew, tier, pontos, participacoes.
- **Inscricao**: formulario com nome, crew e contato. Cria dançarino
  `pendente` e mostra a instrucao de Pix. Nao expoe dado de terceiro.
- **Evento**: quem participou e ate onde foi, com a chave quando houver.
  Somente leitura. Circuito com dinheiro em jogo precisa ser auditavel por
  quem compete.

### 6.2 Administracao
- Cadastrar evento oficial e lancar resultado, pela mesma tela do parceiro.
- Fila de homologacao: aprovar ou negar evento de parceiro.
- Fila de inscricoes: confirmar pagamento, que promove o dançarino a
  `confirmado`.
- Financeiro: bolo acumulado e projecao da divisao sobre o ranking atual.
- Recalcular, como acao explicita.

Nao existe mais fila de conciliacao. Ela existia para limpar erro de digitacao
de nome, e a selecao da lista acaba com a origem do erro.

### 6.3 Organizador parceiro

Fluxo em quatro passos, na ordem:

1. **Cria o evento**, que nasce `pendente`. Informa nome, data, numero de
   inscritos na categoria Free Step e tamanho da chave.
2. **Monta a lista de participantes.** Tela com os dançarinos **confirmados**
   da Liga, com busca por nome e crew, e ele marca quem competiu. Quem nao e da
   Liga ele acrescenta como nome avulso, que vai para
   `participantes_evento.nome_externo`. **Parceiro nunca cria dançarino da
   Liga.** Sem excecao.
3. **Lanca a seletiva**, se o evento teve. Uma nota por participante, inclusive
   os de fora. O maior do proprio evento recebe a bonificacao, se a regra for
   confirmada.
4. **Lanca o resultado da chave**, de uma das duas formas abaixo.

Ele acompanha a situacao da homologacao, nao ve financeiro, nao ve contato de
ninguem e nao toca em evento que nao e dele. Nome e crew ele ve, porque ja sao
publicos no ranking.

#### Opcao A: selecao com fase validada (base da spec)

Para cada participante marcado, ele escolhe a fase alcancada numa lista
fechada. O sistema recusa o que e impossivel: fase acima do que
`tamanho_chave` permite, mais de um campeao, mais de um vice, mais de dois
semifinalistas, mais de quatro nas quartas.

Pega quase todo erro real por uma fracao do trabalho. O que ela nao pega e
inconsistencia interna plausivel, por exemplo quatro pessoas marcadas nas
oitavas num evento onde so tres cairam ali.

#### Opcao B: chave clicavel completa

Ele monta o chaveamento e clica no vencedor de cada batalha. A fase deixa de
ser digitada e passa a ser **deduzida** de `batalhas`. Fica impossivel ter dois
campeoes ou alguem nas quartas num evento de oito pessoas, e o evento publico
ganha a chave desenhada.

E a tela mais cara do sistema, mais trabalho que todo o resto somado. Ela
troca o passo 4 e acrescenta a tabela `batalhas`; motor, ranking, financeiro e
homologacao ficam identicos. Por isso pode entrar depois, sem retrabalho.

---

## 7. Integridade e tratamento de erro

### O risco que dominava: nome digitado diferente

"Joao Silva", "João Silva" e "joão silva" viravam tres linhas no ranking, cada
uma com um pedaco dos pontos. Nao dava erro, nao quebrava nada, e produzia um
ranking errado que parecia certo.

A correcao e estrutural e vem da ideia do Matheus de selecionar em vez de
digitar:

1. `resultados.dancarino_id` e NOT NULL. Nome em texto nunca entra ali.
2. O parceiro **seleciona** da lista de confirmados. Nao ha campo de nome livre
   para quem e da Liga.
3. Quem nao e da Liga entra em `participantes_evento.nome_externo`, aparece na
   chave e nao pode gerar resultado.
4. **Parceiro nunca cria dançarino.** Sem excecao.

O erro foi eliminado na origem, nao tratado depois.

### Demais casos

| Situacao | Tratamento |
|---|---|
| Resultado repetido no mesmo evento | Impossivel por `UNIQUE (evento_id, dancarino_id)`. Reenvio atualiza |
| Escrita simultanea | Controle de versao do `api.php`, como na Gincana |
| Parceiro envia fora do prazo de 7 dias | `enviado_em` marca o atraso e sinaliza. Nao recusa sozinho: a decisao e politica, do Matheus |
| Homologacao cancelada | Resultados saem do ranking, porque a aprovacao e o que os deixa entrar |
| Nao inscrito competiu | Fica como `nome_externo`, aparece na chave, nao pontua. Secao 2.1 do regulamento |
| Inscricao apos o inicio dos eventos | Nao existe: as inscricoes fecham antes do primeiro evento |
| Fase impossivel para o tamanho da chave | Erro explicito na tela de lancamento |
| Dois campeoes no mesmo evento | Recusado na opcao A, impossivel na opcao B |
| Seletiva sem nota lancada | Nenhuma bonificacao distribuida. Nao e erro |
| Fase invalida | Erro explicito, nunca zero silencioso |

---

## 8. Testes

Cada regra do motor comeca pelo teste, que roda e falha antes de existir
implementacao. Depois de passar, **a implementacao e sabotada de proposito para
confirmar que o teste falha**. Teste que passa com o codigo quebrado nao testa
nada. Comparar duas implementacoes so prova que elas concordam, entao cada
teste carrega tambem asercao de valor absoluto.

Cobertura minima:

- Cada uma das sete fases, e fase invalida.
- Cada faixa de peso, incluindo as bordas exatas: 15, 16, 31 e 32 inscritos.
- Arredondamento, com os casos quebrados reais (33,75 e 27,5).
- Bonificacao: sem nota, com um maior, com empate na maior, e o caso de
  calibragem do regulamento (lider da seletiva que cai nas oitavas soma 26 e
  continua atras dos 27 de quem chegou as quartas).
- Bonus multiplicado pelo peso, nao somado depois.
- Fase impossivel para o tamanho da chave.
- Participante com `dancarino_id` nulo nao gera resultado.
- Desempate por participacao em evento oficial.
- Cada corte de tier, nas bordas: posicoes 5, 6, 10, 11, 20 e 21.
- Dançarino pendente nao pontua.
- Resultado de evento nao aprovado nao pontua.
- Soma da premiacao fecha exatamente o bolo.
- Parceiro nao le contato nem financeiro, e nao escreve em evento alheio.

---

## 9. Fora de escopo

- **Conta de dançarino.** Decidido contra, pelo custo de suporte.
- **Multiplas temporadas.** Somente a Series 1. Se vier a Series 2, a migracao
  e acrescentar `temporada_id` nas tabelas e preencher tudo com 1. Nenhuma
  logica de temporada e escrita agora.
- **Pagamento automatico.** Pix e confirmado a mao pelo Matheus.
- **Normalizacao de nota de seletiva entre eventos.** A nota nunca sai do
  proprio evento.
- **Campeonato Forward.** Produto separado, nao acumula ponto na Liga.
  Fica para depois.
