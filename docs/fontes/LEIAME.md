# Fontes dos perfis

Texto extraido dos midia kits e curriculos que o time enviou pelo Drive em
maio de 2026. Ficam aqui para que "fiel ao documento" seja verificavel: da
para conferir qualquer frase publicada contra a origem.

## Atencao: estes arquivos tem dado pessoal

Endereco residencial, telefone, email particular, data de nascimento e
estado civil. **Nada disso pode ir para o site.**

Consequencias praticas:

- esta pasta nunca e copiada para `/var/www/freestep/`
- se um dia este repositorio ganhar um remoto publico, esta pasta sai antes
- `ferramentas/verificar-perfis.php` varre o HTML publicado procurando
  exatamente estes dados, e falha se achar

## Arquivos

| Arquivo | Origem |
|---|---|
| `alves-midiakit.txt` | Mídia Kit att Gabriel Alves 08052026.pdf |
| `alves-curriculo.txt` | CURRICULYM ATT 080526.pdf |
| `gu-henrique-curriculo.txt` | Currículo Artístico - Gustavo Henrique.docx |
| `jeff-midiakit.txt` | MÍDIA KIT JEFF TJ.pdf |
| `takeshi-curriculo.txt` | CURRICULO - TAKESHI 2025 .pdf |

Jorg, Jux e Ederson nao enviaram documento. A pasta do Ederson no Drive veio
vazia; ele entrou no time ha pouco.

O texto dos PDFs saiu com `pdftotext -layout`. O do .docx saiu lendo
`word/document.xml` do zip. As capturas de tela de metricas que existem nos
documentos nao viram texto: o alcance do Takeshi, por exemplo, so esta em
imagem no documento dele e precisa ser confirmado com ele.
