# Site Free Step Brazil Team -- Design

**Data:** 2026-10-14  
**Responsavel:** Matheus  
**Status:** aguardando aprovacao

---

## Objetivo

Site institucional do Free Step Brazil Team com dois focos:

1. **Vitrine/portfolio** -- apresentar o time para contratantes, imprensa e fas com impacto visual forte
2. **Contato profissional** -- capturar propostas de shows, publicidade e jobs

---

## Plataforma

**WordPress** hospedado na VM 301 durante desenvolvimento (porta 8081), migrado para hosting externo (Hostinger ou similar, ~R$25/mes) apos aprovacao final.

---

## Estrutura de paginas

```
/ (home, one-page com scroll e ancoras)
  ├── #capa        -- primeira tela com foto/video de impacto
  ├── #sobre       -- historia, missao, conquistas
  ├── #time        -- grid de cards dos membros, clicaveis
  ├── #galeria     -- fotos e videos de apresentacoes
  ├── #agenda      -- proximos shows
  ├── #imprensa    -- aparicoes na midia (SBT, etc.)
  ├── #parceiros   -- logos de patrocinadores e parceiros
  └── #contato     -- formulario para jobs e publicidade

/membro/[nome]     -- pagina individual de cada dancarino
```

**Navegacao:** menu fixo no topo com logo, links em maiusculo, botao "Contratar" em verde ancorado em #contato.

**Rodape:** redes sociais, email de contato, logo branco sobre fundo verde escuro.

---

## Identidade visual

### Paleta
| Token | Cor | Uso |
|---|---|---|
| Fundo principal | `#ffffff` | Pagina geral |
| Verde escuro | `#1a6b35` | Botoes, rodape, destaques |
| Dourado | `#f5c400` | Titulos, icones, detalhes |
| Texto corrido | `#1a1a1a` | Paragrafos |
| Fundo alternado | `#f5f5f5` | Quebra entre secoes |

### Tipografia
- Titulos: Barlow Condensed Bold (maiusculo) -- Google Fonts, gratuita
- Texto corrido: Inter ou Open Sans -- Google Fonts, gratuita

### Elementos visuais
- Botoes: fundo verde, texto branco maiusculo; hover em dourado
- Separadores: linha fina dourada ou estrela do logo como divisor
- Cards de membros: foto quadrada, nome em maiusculo, funcao, icones de redes sociais

### Logo
Usar variacao verde+dourado como principal. Variacao branca no rodape e no menu sobre fundos escuros.

---

## Secoes detalhadas

### Capa (hero)
- Foto ou video de fundo de apresentacao em palco
- Logo sobreposto centralizado
- Frase: "Levando a cultura do Free Step brasileiro para o mundo"
- Botao: "Contratar o Time" (ancora #contato)

### Sobre o time
- Historia do time
- Missao
- Numeros de destaque (ex: paises visitados, anos de existencia, seguidores)

### Time
- Grid de cards clicaveis
- Cada card: foto, nome, funcao
- Pagina individual `/membro/[nome]`: foto grande, bio, redes sociais

### Galeria
- Fotos de apresentacoes
- Videos embed do YouTube/Instagram
- Plugin: Envira Gallery ou similar

### Agenda
- Lista de proximos shows em ordem cronologica
- Campos: data, local, cidade, descricao, link para ingresso (opcional)
- Shows passados vao para arquivo automaticamente

### Imprensa
- Grid de aparicoes na midia
- Campos: veiculo (ex: SBT), data, logo do veiculo, link

### Parceiros
- Grid de logos com link

### Contato
- Formulario: nome, email, telefone, tipo de contato (show / publicidade / outro), mensagem
- Email de destino: timefreestepbrasil@gmail.com
- Plugin: WPForms Lite (gratuito)

---

## Painel administrativo (WordPress)

A pessoa do time acessa o painel WordPress e consegue, sem tocar em codigo:

| Acao | Como |
|---|---|
| Publicar noticia | Post padrao do WordPress |
| Adicionar membro | Tipo customizado "Membro" com campos: nome, foto, funcao, bio, redes sociais |
| Adicionar show na agenda | Tipo customizado "Show" com campos: data, local, cidade, link |
| Adicionar foto/video na galeria | Upload via plugin de galeria |
| Adicionar aparicao na imprensa | Tipo customizado "Imprensa" com campos: veiculo, data, logo, link |
| Adicionar parceiro | Tipo customizado "Parceiro" com campos: logo, link |

---

## Infraestrutura de desenvolvimento

- **Servidor:** VM 301 (`/opt/canary-1511-clean` e afins), porta **8081**
- **WordPress:** instalado em `/var/www/freestep` ou equivalente
- **Pasta do projeto na bancada:** `~/projetos/freestep/`
- **Apos aprovacao:** migracao para hosting externo (Hostinger Business ~R$25/mes), dominio sugerido: `timefreestepbrasil.com.br` ou `freestepbrazil.com`

---

## Fora do escopo

- Loja virtual
- Area de membros com login
- Blog com comentarios
- Sistema de inscricao em aulas
