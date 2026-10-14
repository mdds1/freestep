# Perfil padronizado do membro

Data: 14/10/2026
Projeto: site do Time Free Step Brasil

## Problema

Cada membro enviou um documento com forma propria: dois midia kits (Alves,
Jeff) e dois curriculos (Gu Henrique, Takeshi). A primeira versao da pagina
do membro copiou essa diversidade: o perfil do Jeff tem paises e imprensa, o
do Takeshi tem marcas, o do Alves tem numeros de rede, e nenhum tem a mesma
forma do outro. O resultado nao parece um time, parece quatro documentos
diferentes publicados lado a lado.

O objetivo e padronizar **como** a informacao aparece, sem gerar texto novo:
o conteudo continua sendo o que cada um escreveu no proprio documento.

## Levantamento dos documentos

Cruzamento do que os quatro documentos trazem:

| Informacao | Alves | Gu Henrique | Jeff | Takeshi |
|---|:--:|:--:|:--:|:--:|
| Release de apresentacao | sim | sim | sim | sim |
| Cidade de origem | sim | sim | sim | sim |
| Tempo de carreira | sim | sim | sim | sim |
| Papel no time | sim | sim | sim | sim |
| Formacao | sim | sim | sim | sim |
| Titulos e premiacoes | sim | sim | sim | sim |
| Programas de TV | sim | sim | sim | sim |
| Marcas e publicidade | sim | sim | sim | sim |
| Atuacao internacional | sim | sim | sim | sim |
| Workshops e ensino | sim | sim | sim | sim |
| Numeros por rede | sim | sim | sim | so em imagem |
| Visualizacoes totais | sim | nao | sim | nao |
| Perfil de publico | nao | nao | sim | nao |
| Links de portfolio | nao | nao | sim | sim |

Dez informacoes aparecem nos quatro. Os numeros por rede aparecem em tres em
formato de texto, e no quarto apenas como captura de tela, parcialmente
legivel.

## Decisoes tomadas

**So entra o que e da danca.** Os curriculos trazem formacao e experiencia
profissional fora da area: Tecnologo em Analise e Desenvolvimento de
Sistemas, auxiliar de informatica, operador de telemarketing, analista de
negocios. Nada disso entra. Da formacao entram apenas cursos e formacoes de
danca; da experiencia, apenas criacao de conteudo e ensino de danca.

**Nenhum dado pessoal entra.** Os documentos trazem endereco residencial,
telefone, email particular, data de nascimento e estado civil. Nada disso
vai para o site.

**Conquista obtida com outro grupo fica no perfil individual.** Los Cocas,
7Minutoz e participacoes anteriores aparecem na pagina do membro, nunca na
secao de imprensa do time. Atribuir ao Time Free Step o que foi de outro
grupo seria falso, e contratante confere.

**Alcance nas redes entra no nucleo.** E o dado que mais pesa para quem
contrata, e e informacao da carreira de danca deles. Quem ainda nao tem fica
com a lacuna aberta, sinalizada para a equipe, ate trazer os numeros.

**Texto fiel ao documento.** O conteudo e transcrito do que cada um
escreveu, com a acentuacao correta. Nao se reescreve com palavras proprias,
nao se resume e nao se corta acento.

## Topicos do perfil

### Nucleo: 11 topicos, na mesma ordem em todo perfil

| # | Chave | Rotulo | Tipo |
|---|---|---|---|
| 1 | `release` | Release | texto corrido |
| 2 | `base` | Base | linha |
| 3 | `carreira` | Carreira | linha |
| 4 | `papel` | Papel no time | linha |
| 5 | `alcance` | Alcance nas redes | pares (Rede \| Seguidores) |
| 6 | `formacao` | Formacao em danca | lista |
| 7 | `titulos` | Titulos e premiacoes | lista |
| 8 | `internacional` | Atuacao internacional | itens |
| 9 | `tv` | Programas de TV | pares (Programa \| Emissora) |
| 10 | `marcas` | Marcas e publicidade | itens |
| 11 | `ensino` | Workshops e ensino | lista |

### Extras: um topico para cada informacao que so alguns tem

| Chave | Rotulo | Tipo | Quem tem |
|---|---|---|---|
| `visualizacoes` | Visualizacoes | linha | Alves, Jeff |
| `publico` | Perfil de publico | pares (Metrica \| Valor) | Jeff |
| `links` | Links de portfolio | pares (Rotulo \| URL) | Takeshi, Jeff |

Topico extra sem conteudo nao gera aviso de pendencia: por definicao nem
todo membro tem. Topico do nucleo sem conteudo gera.

## Arquitetura

### Esquema unico

`inc/perfil-schema.php` expoe `freestep_perfil_schema()`, que devolve um
array ordenado. Cada entrada traz:

- `chave`: sufixo do campo ACF (`membro_<chave>`)
- `rotulo`: titulo exibido no site e no painel
- `tipo`: `linha`, `texto`, `lista`, `itens` ou `pares`
- `nucleo`: booleano, define se a ausencia vira pendencia
- `instrucoes`: texto de ajuda no painel
- `exemplo`: exemplo de preenchimento, exibido no painel

Nenhum outro arquivo do tema decide estrutura de perfil.

### Tres consumidores do esquema

**Registro dos campos** (`inc/acf-fields.php`): o grupo do membro passa a
ser gerado percorrendo o esquema, em vez de ter os campos escritos a mao.
Mapeamento do tipo do esquema para o campo do ACF:

| Tipo | Campo ACF | Observacao |
|---|---|---|
| `linha` | text | uma linha |
| `texto` | textarea | `new_lines` = `wpautop` |
| `lista` | textarea | um item por linha, sem conversao |
| `itens` | textarea | separados por virgula, sem conversao |
| `pares` | textarea | `rotulo \| valor` por linha, sem conversao |

**Template** (`single-fs_membro.php`): percorre o esquema na ordem e renderiza
cada topico. Nao existe caminho no codigo onde a ordem divirja entre
membros, que e o requisito central.

**Painel**: rotulo, instrucoes e exemplo vem do mesmo esquema, entao o que a
equipe le ao preencher e o que aparece publicado.

### Leitura dos valores

`inc/listas.php` ja existe e cobre os tipos de lista:

- `freestep_linhas()` para `lista`
- `freestep_itens()` para `itens`
- `freestep_pares()` para `pares`

Sao textarea com um item por linha porque o ACF Free nao tem campo
repetidor. O parser ignora linha vazia e espaco sobrando, e aceita par sem a
barra, para a equipe nao travar na sintaxe.

### Pendencia visivel so para a equipe

Topico do nucleo sem conteudo:

- visitante anonimo: o bloco nao e emitido no HTML
- usuario com `edit_posts`: sai um aviso discreto com link para o campo

O contratante nunca ve lacuna; a equipe ve a lista do que falta.

### Preenchimento

`ferramentas/portfolio.php` passa a carregar os 14 topicos a partir do
conteudo dos documentos, substituindo a versao atual. Permanece versionado e
idempotente: rodar de novo sobrescreve, nao duplica. Campo ausente para um
membro e gravado vazio, para o script seguir sendo a fonte de verdade em vez
de deixar sobra de rodada anterior.

## Migracao dos campos atuais

Os campos de hoje passam a seguir o esquema:

| Hoje | Vira |
|---|---|
| `membro_bio` | `membro_release` |
| `membro_funcao` | `membro_papel` |
| `membro_numeros` | `membro_alcance` |
| `membro_conquistas` | `membro_titulos` |
| `membro_paises` | `membro_internacional` |
| `membro_imprensa` | `membro_tv` |
| `membro_marcas` | `membro_marcas` (mantem) |

`membro_funcao` e lido tambem pelo card do grid em `front-page.php`, que
precisa acompanhar a troca. Os campos antigos sao removidos do registro
depois que o script de preenchimento gravar nos novos.

## Verificacao

Tres checagens, em ordem de importancia:

**Padronizacao.** Extrair a sequencia de topicos emitidos no HTML de cada um
dos sete perfis e comparar: todos devem emitir a mesma sequencia de topicos
do nucleo, na mesma ordem. Esta e a verificacao que responde ao problema que
originou o trabalho.

**Vazamento de pendencia.** Requisicao sem sessao a um perfil incompleto nao
pode conter o texto do aviso. Para provar que o teste testa, repetir a mesma
requisicao autenticada e confirmar que ali o aviso aparece: se aparecer nos
dois, ou em nenhum, a checagem nao esta valendo nada.

**Dado pessoal.** Varrer o HTML dos sete perfis procurando os dados que
existem nos documentos e nao podem vazar: os telefones, os emails
particulares, o endereco residencial e as datas de nascimento. Zero
ocorrencias.

## Fora de escopo

- Galeria da home a partir das fotos do Drive
- Secao de imprensa do time na home
- Foto de palco no fundo do hero
- Coleta dos dados que faltam de Jorg, Jux e Ederson, que depende do time
