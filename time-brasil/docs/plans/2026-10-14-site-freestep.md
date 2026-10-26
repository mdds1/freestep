# Site Free Step Brazil Team -- Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Construir site institucional WordPress do Free Step Brazil Team com vitrine visual, perfis de membros e formulario de contato para jobs.

**Architecture:** WordPress instalado na VM 301 na porta 8081. Tema filho customizado sobre o tema base GeneratePress (leve e sem bloatware). Tipos de conteudo customizados (CPT) para Membros, Shows, Imprensa e Parceiros registrados via plugin simples. Painel admin nativo do WordPress, sem plugin de page builder -- HTML/CSS direto no tema filho para controle total do layout.

**Tech Stack:** WordPress 6.x, PHP 8.x, GeneratePress (tema base gratuito), ACF Free (campos customizados), WPForms Lite (formulario), Envira Gallery Lite (galeria), Apache na VM 301.

## Global Constraints

- Porta: 8081 na VM 301
- Raiz do site: `/var/www/freestep/`
- Pasta do projeto na bancada: `~/projetos/freestep/`
- Paleta: fundo `#ffffff`, verde `#1a6b35`, dourado `#f5c400`, texto `#1a1a1a`, fundo alternado `#f5f5f5`
- Tipografia: Barlow Condensed Bold (titulos), Inter (corpo) -- Google Fonts
- Logo principal: verde+dourado (variacao 1 das imagens entregues)
- Idioma: portugues brasileiro
- Email de contato: timefreestepbrasil@gmail.com
- Nenhum plugin pago obrigatorio -- tudo com versao gratuita ou codigo proprio
- O site nao pode interferir no FloripaOT (porta 7171) nem no site MyAAC

---

## Estrutura de arquivos

```
/var/www/freestep/                          -- raiz do WordPress
wp-content/
  themes/
    generatepress/                          -- tema base (nao editar)
    freestep-child/                         -- tema filho (nosso codigo)
      style.css                             -- declaracao do tema filho + variaveis CSS
      functions.php                         -- enqueue scripts, register CPTs, ACF
      front-page.php                        -- template da home (one-page)
      single-membro.php                     -- template de pagina individual de membro
      header.php                            -- menu fixo com logo e botao Contratar
      footer.php                            -- rodape com redes sociais
      assets/
        css/
          main.css                          -- estilos globais e secoes
          sections/
            hero.css
            sobre.css
            time.css
            galeria.css
            agenda.css
            imprensa.css
            parceiros.css
            contato.css
        js/
          main.js                           -- scroll suave, menu mobile
        img/
          logo-verde-dourado.png            -- logo principal
          logo-branco.png                   -- logo para rodape
  plugins/
    freestep-cpt/                           -- plugin de tipos customizados
      freestep-cpt.php                      -- registra CPTs: membro, show, imprensa, parceiro
```

---

## Task 1: Ambiente WordPress na VM 301

**Files:**
- Create: `/var/www/freestep/` (instalacao WordPress)
- Modify: `/etc/apache2/sites-available/freestep.conf` (virtual host porta 8081)

**Interfaces:**
- Produces: WordPress acessivel em `http://IP_VM301:8081`

- [ ] **Step 1: Verificar se porta 8081 esta livre**

```bash
ss -tlnp | grep 8081
```
Esperado: nenhuma saida (porta livre).

- [ ] **Step 2: Criar diretorio da aplicacao**

```bash
sudo mkdir -p /var/www/freestep
sudo chown www-data:www-data /var/www/freestep
```

- [ ] **Step 3: Baixar e extrair WordPress**

```bash
cd /tmp
wget https://wordpress.org/latest.tar.gz
tar xzf latest.tar.gz
sudo cp -r wordpress/. /var/www/freestep/
sudo chown -R www-data:www-data /var/www/freestep
```

- [ ] **Step 4: Criar banco de dados MySQL**

```sql
CREATE DATABASE freestep_wp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'freestep'@'localhost' IDENTIFIED BY 'SENHA_FORTE_AQUI';
GRANT ALL PRIVILEGES ON freestep_wp.* TO 'freestep'@'localhost';
FLUSH PRIVILEGES;
```

Rodar via: `sudo mysql -u root`

- [ ] **Step 5: Criar virtual host Apache**

Criar `/etc/apache2/sites-available/freestep.conf`:

```apache
Listen 8081

<VirtualHost *:8081>
    ServerName freestep.local
    DocumentRoot /var/www/freestep
    DirectoryIndex index.php

    <Directory /var/www/freestep>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/freestep_error.log
    CustomLog ${APACHE_LOG_DIR}/freestep_access.log combined
</VirtualHost>
```

- [ ] **Step 6: Ativar site e recarregar Apache**

```bash
sudo a2ensite freestep.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
```

- [ ] **Step 7: Instalar WordPress pelo navegador**

Acessar `http://IP_VM301:8081` e preencher:
- Nome do banco: `freestep_wp`
- Usuario: `freestep`
- Senha: a definida no Step 4
- Host: `localhost`
- Prefixo: `fs_`

Completar instalacao com:
- Titulo do site: `Free Step Brazil Team`
- Usuario admin: definido por voce
- Email: `timefreestepbrasil@gmail.com`

- [ ] **Step 8: Verificar acesso ao painel**

Acessar `http://IP_VM301:8081/wp-admin`. Deve abrir o painel WordPress.

---

## Task 2: Instalar GeneratePress e criar tema filho

**Files:**
- Create: `/var/www/freestep/wp-content/themes/freestep-child/style.css`
- Create: `/var/www/freestep/wp-content/themes/freestep-child/functions.php`

**Interfaces:**
- Consumes: WordPress instalado (Task 1)
- Produces: Tema filho ativo, variaveis CSS da paleta definidas, fontes carregadas

- [ ] **Step 1: Instalar GeneratePress**

No painel WordPress: Aparencia > Temas > Adicionar Novo > buscar "GeneratePress" > Instalar > Ativar.

- [ ] **Step 2: Criar pasta do tema filho**

```bash
sudo mkdir -p /var/www/freestep/wp-content/themes/freestep-child/assets/{css/sections,js,img}
sudo chown -R www-data:www-data /var/www/freestep/wp-content/themes/freestep-child
```

- [ ] **Step 3: Criar style.css do tema filho**

Criar `/var/www/freestep/wp-content/themes/freestep-child/style.css`:

```css
/*
Theme Name: Free Step Child
Template: generatepress
Version: 1.0.0
*/

:root {
  --color-bg: #ffffff;
  --color-bg-alt: #f5f5f5;
  --color-verde: #1a6b35;
  --color-dourado: #f5c400;
  --color-texto: #1a1a1a;
  --color-branco: #ffffff;
  --font-titulo: 'Barlow Condensed', sans-serif;
  --font-corpo: 'Inter', sans-serif;
}

body {
  font-family: var(--font-corpo);
  color: var(--color-texto);
  background: var(--color-bg);
  margin: 0;
}

h1, h2, h3, h4 {
  font-family: var(--font-titulo);
  text-transform: uppercase;
  letter-spacing: 0.02em;
}

.btn-contratar {
  background: var(--color-verde);
  color: var(--color-branco);
  font-family: var(--font-titulo);
  text-transform: uppercase;
  font-weight: 700;
  font-size: 1rem;
  padding: 0.75rem 1.75rem;
  border: none;
  cursor: pointer;
  text-decoration: none;
  display: inline-block;
  transition: background 0.2s;
}

.btn-contratar:hover {
  background: var(--color-dourado);
  color: var(--color-texto);
}

.section-divider {
  width: 60px;
  height: 3px;
  background: var(--color-dourado);
  margin: 0.5rem auto 2rem;
}
```

- [ ] **Step 4: Criar functions.php**

Criar `/var/www/freestep/wp-content/themes/freestep-child/functions.php`:

```php
<?php
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_uri(), ['parent-style'], '1.0.0');
    wp_enqueue_style('freestep-main', get_stylesheet_directory_uri() . '/assets/css/main.css', [], '1.0.0');
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700&family=Inter:wght@400;600&display=swap', [], null);
    wp_enqueue_script('freestep-main', get_stylesheet_directory_uri() . '/assets/js/main.js', [], '1.0.0', true);
});
```

- [ ] **Step 5: Ativar tema filho**

No painel: Aparencia > Temas > Free Step Child > Ativar.

- [ ] **Step 6: Verificar fontes no navegador**

Acessar `http://IP_VM301:8081`. Abrir DevTools > Network. Confirmar que `fonts.googleapis.com` aparece nas requisicoes.

---

## Task 3: Plugin de tipos de conteudo customizados (CPT)

**Files:**
- Create: `/var/www/freestep/wp-content/plugins/freestep-cpt/freestep-cpt.php`

**Interfaces:**
- Produces: CPTs registrados: `fs_membro`, `fs_show`, `fs_imprensa`, `fs_parceiro`; campos ACF associados a cada um

- [ ] **Step 1: Instalar ACF Free**

No painel: Plugins > Adicionar Novo > buscar "Advanced Custom Fields" (por Elliot Condon / WP Engine) > Instalar > Ativar.

- [ ] **Step 2: Criar plugin freestep-cpt**

Criar `/var/www/freestep/wp-content/plugins/freestep-cpt/freestep-cpt.php`:

```php
<?php
/**
 * Plugin Name: Free Step CPT
 * Description: Tipos de conteudo customizados do Free Step Brazil Team
 * Version: 1.0.0
 */

add_action('init', function () {
    // Membros
    register_post_type('fs_membro', [
        'labels' => [
            'name'          => 'Membros',
            'singular_name' => 'Membro',
            'add_new_item'  => 'Adicionar Membro',
            'edit_item'     => 'Editar Membro',
        ],
        'public'       => true,
        'has_archive'  => false,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-groups',
        'supports'     => ['title', 'thumbnail'],
        'rewrite'      => ['slug' => 'membro'],
    ]);

    // Shows / Agenda
    register_post_type('fs_show', [
        'labels' => [
            'name'          => 'Agenda',
            'singular_name' => 'Show',
            'add_new_item'  => 'Adicionar Show',
            'edit_item'     => 'Editar Show',
        ],
        'public'       => true,
        'has_archive'  => false,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-calendar-alt',
        'supports'     => ['title'],
        'rewrite'      => ['slug' => 'show'],
    ]);

    // Imprensa
    register_post_type('fs_imprensa', [
        'labels' => [
            'name'          => 'Imprensa',
            'singular_name' => 'Aparicao',
            'add_new_item'  => 'Adicionar Aparicao',
            'edit_item'     => 'Editar Aparicao',
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-media-video',
        'supports'     => ['title'],
    ]);

    // Parceiros
    register_post_type('fs_parceiro', [
        'labels' => [
            'name'          => 'Parceiros',
            'singular_name' => 'Parceiro',
            'add_new_item'  => 'Adicionar Parceiro',
            'edit_item'     => 'Editar Parceiro',
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-star-filled',
        'supports'     => ['title', 'thumbnail'],
    ]);
});
```

- [ ] **Step 3: Ativar plugin**

No painel: Plugins > Free Step CPT > Ativar.

- [ ] **Step 4: Verificar CPTs no menu lateral**

No painel, o menu lateral deve exibir: Membros, Agenda, Imprensa, Parceiros.

- [ ] **Step 5: Registrar campos ACF para cada CPT**

No painel: ACF > Grupos de campos > Adicionar Novo.

**Grupo: Dados do Membro** (exibir em: `fs_membro`)
- `membro_funcao` -- Texto -- "Funcao no time (ex: Lider de Coreografia)"
- `membro_bio` -- Textarea -- "Bio curta"
- `membro_instagram` -- URL -- "Instagram"
- `membro_tiktok` -- URL -- "TikTok"
- `membro_youtube` -- URL -- "YouTube"

**Grupo: Dados do Show** (exibir em: `fs_show`)
- `show_data` -- Date Picker -- "Data do show"
- `show_local` -- Texto -- "Local / Venue"
- `show_cidade` -- Texto -- "Cidade e Estado"
- `show_link_ingresso` -- URL -- "Link para ingresso (opcional)"

**Grupo: Dados da Imprensa** (exibir em: `fs_imprensa`)
- `imprensa_veiculo` -- Texto -- "Veiculo (ex: SBT)"
- `imprensa_data` -- Date Picker -- "Data"
- `imprensa_link` -- URL -- "Link para a materia"
- `imprensa_logo` -- Image -- "Logo do veiculo"

**Grupo: Dados do Parceiro** (exibir em: `fs_parceiro`)
- `parceiro_link` -- URL -- "Site do parceiro"

- [ ] **Step 6: Testar criacao de um membro**

No painel: Membros > Adicionar Membro. Preencher titulo (nome), foto destacada, e os campos ACF. Salvar. Confirmar que salva sem erro.

---

## Task 4: Header e footer

**Files:**
- Create: `freestep-child/header.php`
- Create: `freestep-child/footer.php`
- Create: `freestep-child/assets/css/sections/header.css`
- Create: `freestep-child/assets/css/sections/footer.css`

**Interfaces:**
- Consumes: Logo PNG em `assets/img/logo-verde-dourado.png` e `assets/img/logo-branco.png`
- Produces: Menu fixo no topo + rodape com redes sociais, incluidos em todos os templates

- [ ] **Step 1: Copiar logos para o tema filho**

```bash
# Copiar os arquivos de logo enviados pelo usuario para:
cp /caminho/logo-verde-dourado.png /var/www/freestep/wp-content/themes/freestep-child/assets/img/logo-verde-dourado.png
cp /caminho/logo-branco.png /var/www/freestep/wp-content/themes/freestep-child/assets/img/logo-branco.png
sudo chown www-data:www-data /var/www/freestep/wp-content/themes/freestep-child/assets/img/*
```

- [ ] **Step 2: Criar header.php**

```php
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="fs-header">
  <div class="fs-header__inner">
    <a href="<?php echo home_url('/'); ?>" class="fs-header__logo">
      <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/logo-verde-dourado.png"
           alt="Free Step Brazil Team" height="56">
    </a>
    <nav class="fs-header__nav">
      <a href="#sobre">Sobre</a>
      <a href="#time">Time</a>
      <a href="#galeria">Galeria</a>
      <a href="#agenda">Agenda</a>
      <a href="#imprensa">Imprensa</a>
      <a href="#parceiros">Parceiros</a>
      <a href="#contato" class="btn-contratar">Contratar</a>
    </nav>
    <button class="fs-header__hamburger" aria-label="Menu">&#9776;</button>
  </div>
</header>
```

- [ ] **Step 3: Criar footer.php**

```php
<footer class="fs-footer">
  <div class="fs-footer__inner">
    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/logo-branco.png"
         alt="Free Step Brazil Team" height="64">
    <div class="fs-footer__social">
      <a href="https://www.instagram.com/timefreestepbrazil/" target="_blank" rel="noopener">Instagram</a>
    </div>
    <p class="fs-footer__email">timefreestepbrasil@gmail.com</p>
    <p class="fs-footer__copy">&copy; <?php echo date('Y'); ?> Free Step Brazil Team. Todos os direitos reservados.</p>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
```

- [ ] **Step 4: Criar assets/css/sections/header.css**

```css
.fs-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  background: var(--color-bg);
  border-bottom: 3px solid var(--color-dourado);
  padding: 0.75rem 0;
}

.fs-header__inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.fs-header__nav a {
  font-family: var(--font-titulo);
  text-transform: uppercase;
  font-weight: 700;
  font-size: 0.95rem;
  color: var(--color-texto);
  text-decoration: none;
  margin-left: 1.5rem;
  letter-spacing: 0.05em;
  transition: color 0.2s;
}

.fs-header__nav a:hover {
  color: var(--color-verde);
}

.fs-header__hamburger {
  display: none;
  background: none;
  border: none;
  font-size: 1.75rem;
  cursor: pointer;
}

@media (max-width: 768px) {
  .fs-header__nav {
    display: none;
    flex-direction: column;
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--color-bg);
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--color-bg-alt);
  }

  .fs-header__nav.open {
    display: flex;
  }

  .fs-header__nav a {
    margin: 0.5rem 0;
  }

  .fs-header__hamburger {
    display: block;
  }
}
```

- [ ] **Step 5: Criar assets/css/sections/footer.css**

```css
.fs-footer {
  background: var(--color-verde);
  color: var(--color-branco);
  text-align: center;
  padding: 3rem 1.5rem;
}

.fs-footer__inner {
  max-width: 800px;
  margin: 0 auto;
}

.fs-footer__social {
  margin: 1.5rem 0 1rem;
}

.fs-footer__social a {
  color: var(--color-dourado);
  font-family: var(--font-titulo);
  text-transform: uppercase;
  font-weight: 700;
  text-decoration: none;
  margin: 0 0.75rem;
}

.fs-footer__social a:hover {
  color: var(--color-branco);
}

.fs-footer__email {
  color: var(--color-branco);
  margin: 0.5rem 0;
}

.fs-footer__copy {
  font-size: 0.8rem;
  opacity: 0.7;
  margin-top: 1.5rem;
}
```

- [ ] **Step 6: Adicionar imports no main.css**

Criar/atualizar `assets/css/main.css`:

```css
@import url('sections/header.css');
@import url('sections/footer.css');

* {
  box-sizing: border-box;
}

body {
  padding-top: 80px; /* compensa o header fixo */
}

.fs-section {
  padding: 5rem 1.5rem;
}

.fs-section--alt {
  background: var(--color-bg-alt);
}

.fs-section__title {
  text-align: center;
  font-size: 2.5rem;
  color: var(--color-verde);
  margin-bottom: 0.25rem;
}

.fs-container {
  max-width: 1200px;
  margin: 0 auto;
}
```

- [ ] **Step 7: Adicionar JS do hamburger em assets/js/main.js**

```js
document.addEventListener('DOMContentLoaded', function () {
  const hamburger = document.querySelector('.fs-header__hamburger');
  const nav = document.querySelector('.fs-header__nav');

  if (hamburger && nav) {
    hamburger.addEventListener('click', function () {
      nav.classList.toggle('open');
    });
  }

  // Scroll suave para ancoras
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth' });
        if (nav.classList.contains('open')) nav.classList.remove('open');
      }
    });
  });
});
```

- [ ] **Step 8: Verificar header e footer no navegador**

Acessar `http://IP_VM301:8081`. Menu deve aparecer fixo no topo com logo e links. Rodape verde no final da pagina.

---

## Task 5: Capa (hero section)

**Files:**
- Create: `freestep-child/front-page.php` (inicio)
- Create: `freestep-child/assets/css/sections/hero.css`

**Interfaces:**
- Consumes: header.php e footer.php (Task 4)
- Produces: Primeira secao visual do site com foto de fundo, logo e botao

- [ ] **Step 1: Criar front-page.php com estrutura basica**

```php
<?php get_header(); ?>

<main>

<!-- CAPA -->
<section id="capa" class="fs-hero">
  <div class="fs-hero__overlay"></div>
  <div class="fs-hero__content">
    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/logo-verde-dourado.png"
         alt="Free Step Brazil Team" class="fs-hero__logo">
    <p class="fs-hero__tagline">Levando a cultura do Free Step brasileiro para o mundo</p>
    <a href="#contato" class="btn-contratar">Contratar o Time</a>
  </div>
</section>

<!-- demais secoes virao nas tarefas seguintes -->

</main>

<?php get_footer(); ?>
```

- [ ] **Step 2: Fazer upload de foto de fundo**

No painel: Midia > Adicionar Nova. Enviar uma foto de apresentacao em palco (formato landscape, minimo 1920x1080px). Copiar a URL da imagem apos upload.

- [ ] **Step 3: Criar assets/css/sections/hero.css**

Substituir `URL_DA_FOTO` pela URL copiada no step anterior:

```css
.fs-hero {
  position: relative;
  min-height: 100vh;
  background-image: url('URL_DA_FOTO');
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
}

.fs-hero__overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
}

.fs-hero__content {
  position: relative;
  z-index: 1;
  text-align: center;
  padding: 2rem;
}

.fs-hero__logo {
  width: 220px;
  max-width: 60vw;
  margin-bottom: 1.5rem;
  filter: drop-shadow(0 4px 16px rgba(0,0,0,0.5));
}

.fs-hero__tagline {
  font-family: var(--font-titulo);
  font-size: 1.5rem;
  color: var(--color-branco);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 2rem;
  max-width: 600px;
}
```

- [ ] **Step 4: Adicionar import em main.css**

Adicionar no final de `assets/css/main.css`:

```css
@import url('sections/hero.css');
```

- [ ] **Step 5: Definir front-page.php como pagina inicial**

No painel:
1. Paginas > Adicionar Nova > titulo "Home" > Publicar
2. Configuracoes > Leitura > "Uma pagina estatica" > Pagina inicial: Home

- [ ] **Step 6: Verificar capa no navegador**

Acessar `http://IP_VM301:8081`. Deve mostrar foto de fundo com overlay escuro, logo no centro, tagline e botao verde.

---

## Task 6: Secao Sobre

**Files:**
- Modify: `freestep-child/front-page.php`
- Create: `freestep-child/assets/css/sections/sobre.css`

**Interfaces:**
- Produces: Secao #sobre com historia, missao e numeros do time

- [ ] **Step 1: Adicionar secao no front-page.php**

Inserir apos o fechamento da secao `#capa`:

```php
<!-- SOBRE -->
<section id="sobre" class="fs-section">
  <div class="fs-container">
    <h2 class="fs-section__title">Sobre o Time</h2>
    <div class="section-divider"></div>
    <div class="fs-sobre__grid">
      <div class="fs-sobre__texto">
        <p>O Free Step Brazil Team e referencia nacional na danca Free Step, levando a cultura brasileira para palcos no Brasil e no mundo. Com apresentacoes em paises como Espanha e Peru, e aparicoes em programas de televisao como o SBT, o time une tecnica, criatividade e orgulho nacional.</p>
        <p>Nossa missao e representar o melhor do Free Step brasileiro, inspirando novas geracoes e conectando culturas atraves da danca.</p>
      </div>
      <div class="fs-sobre__numeros">
        <div class="fs-numero">
          <span class="fs-numero__valor">72K+</span>
          <span class="fs-numero__label">Seguidores</span>
        </div>
        <div class="fs-numero">
          <span class="fs-numero__valor">3+</span>
          <span class="fs-numero__label">Paises</span>
        </div>
        <div class="fs-numero">
          <span class="fs-numero__valor">SBT</span>
          <span class="fs-numero__label">Na televisao</span>
        </div>
      </div>
    </div>
  </div>
</section>
```

- [ ] **Step 2: Criar assets/css/sections/sobre.css**

```css
.fs-sobre__grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 3rem;
  align-items: center;
  margin-top: 2rem;
}

.fs-sobre__texto p {
  font-size: 1.1rem;
  line-height: 1.8;
  margin-bottom: 1rem;
}

.fs-sobre__numeros {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.fs-numero {
  text-align: center;
  border-left: 4px solid var(--color-dourado);
  padding-left: 1.5rem;
}

.fs-numero__valor {
  display: block;
  font-family: var(--font-titulo);
  font-size: 3rem;
  color: var(--color-verde);
  line-height: 1;
}

.fs-numero__label {
  font-family: var(--font-titulo);
  text-transform: uppercase;
  font-size: 0.9rem;
  color: var(--color-texto);
  letter-spacing: 0.1em;
}

@media (max-width: 768px) {
  .fs-sobre__grid {
    grid-template-columns: 1fr;
  }

  .fs-sobre__numeros {
    flex-direction: row;
    justify-content: space-around;
  }
}
```

- [ ] **Step 3: Adicionar import em main.css**

```css
@import url('sections/sobre.css');
```

- [ ] **Step 4: Verificar no navegador**

Rolar apos a capa. Deve aparecer a secao "Sobre o Time" com texto e numeros lado a lado.

---

## Task 7: Secao Time (grid de membros)

**Files:**
- Modify: `freestep-child/front-page.php`
- Create: `freestep-child/single-membro.php`
- Create: `freestep-child/assets/css/sections/time.css`

**Interfaces:**
- Consumes: CPT `fs_membro` e campos ACF (Task 3)
- Produces: Grid clicavel de membros na home + pagina individual de cada membro

- [ ] **Step 1: Adicionar secao #time no front-page.php**

```php
<!-- TIME -->
<section id="time" class="fs-section fs-section--alt">
  <div class="fs-container">
    <h2 class="fs-section__title">O Time</h2>
    <div class="section-divider"></div>
    <div class="fs-time__grid">
      <?php
      $membros = new WP_Query([
        'post_type'      => 'fs_membro',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
      ]);
      while ($membros->have_posts()) :
        $membros->the_post();
        $funcao = get_field('membro_funcao');
      ?>
      <a href="<?php the_permalink(); ?>" class="fs-membro-card">
        <?php if (has_post_thumbnail()): ?>
          <div class="fs-membro-card__foto">
            <?php the_post_thumbnail('medium', ['alt' => get_the_title()]); ?>
          </div>
        <?php endif; ?>
        <div class="fs-membro-card__info">
          <h3 class="fs-membro-card__nome"><?php the_title(); ?></h3>
          <?php if ($funcao): ?>
            <p class="fs-membro-card__funcao"><?php echo esc_html($funcao); ?></p>
          <?php endif; ?>
        </div>
      </a>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
  </div>
</section>
```

- [ ] **Step 2: Criar single-membro.php**

```php
<?php get_header(); ?>

<main>
<?php while (have_posts()) : the_post(); ?>
<section class="fs-section fs-membro-single">
  <div class="fs-container fs-membro-single__inner">
    <div class="fs-membro-single__foto">
      <?php the_post_thumbnail('large'); ?>
    </div>
    <div class="fs-membro-single__dados">
      <p class="fs-membro-single__funcao"><?php echo esc_html(get_field('membro_funcao')); ?></p>
      <h1 class="fs-membro-single__nome"><?php the_title(); ?></h1>
      <div class="section-divider" style="margin: 0.5rem 0 1.5rem;"></div>
      <p class="fs-membro-single__bio"><?php echo esc_html(get_field('membro_bio')); ?></p>
      <div class="fs-membro-single__social">
        <?php
        $instagram = get_field('membro_instagram');
        $tiktok    = get_field('membro_tiktok');
        $youtube   = get_field('membro_youtube');
        if ($instagram): ?>
          <a href="<?php echo esc_url($instagram); ?>" target="_blank" rel="noopener">Instagram</a>
        <?php endif;
        if ($tiktok): ?>
          <a href="<?php echo esc_url($tiktok); ?>" target="_blank" rel="noopener">TikTok</a>
        <?php endif;
        if ($youtube): ?>
          <a href="<?php echo esc_url($youtube); ?>" target="_blank" rel="noopener">YouTube</a>
        <?php endif; ?>
      </div>
      <a href="<?php echo home_url('/#time'); ?>" class="fs-membro-single__voltar">&#8592; Voltar para o time</a>
    </div>
  </div>
</section>
<?php endwhile; ?>
</main>

<?php get_footer(); ?>
```

- [ ] **Step 3: Criar assets/css/sections/time.css**

```css
.fs-time__grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 2rem;
  margin-top: 2rem;
}

.fs-membro-card {
  text-decoration: none;
  color: inherit;
  background: var(--color-bg);
  border-bottom: 3px solid transparent;
  transition: border-color 0.2s, transform 0.2s;
}

.fs-membro-card:hover {
  border-color: var(--color-dourado);
  transform: translateY(-4px);
}

.fs-membro-card__foto img {
  width: 100%;
  aspect-ratio: 1;
  object-fit: cover;
  display: block;
}

.fs-membro-card__info {
  padding: 1rem 0.5rem;
  text-align: center;
}

.fs-membro-card__nome {
  font-family: var(--font-titulo);
  font-size: 1.1rem;
  color: var(--color-verde);
  margin: 0 0 0.25rem;
}

.fs-membro-card__funcao {
  font-size: 0.85rem;
  color: var(--color-texto);
  margin: 0;
  opacity: 0.75;
}

/* Pagina individual */
.fs-membro-single__inner {
  display: grid;
  grid-template-columns: 1fr 1.5fr;
  gap: 4rem;
  align-items: start;
}

.fs-membro-single__foto img {
  width: 100%;
  display: block;
}

.fs-membro-single__funcao {
  font-family: var(--font-titulo);
  text-transform: uppercase;
  color: var(--color-dourado);
  font-size: 1rem;
  letter-spacing: 0.1em;
  margin: 0;
}

.fs-membro-single__nome {
  font-size: 3rem;
  color: var(--color-verde);
  margin: 0.25rem 0 0;
}

.fs-membro-single__bio {
  font-size: 1.05rem;
  line-height: 1.8;
  margin-bottom: 2rem;
}

.fs-membro-single__social a {
  display: inline-block;
  margin-right: 1rem;
  font-family: var(--font-titulo);
  text-transform: uppercase;
  font-weight: 700;
  color: var(--color-verde);
  text-decoration: none;
  border-bottom: 2px solid var(--color-dourado);
  padding-bottom: 2px;
}

.fs-membro-single__voltar {
  display: inline-block;
  margin-top: 2rem;
  color: var(--color-texto);
  text-decoration: none;
  font-size: 0.9rem;
  opacity: 0.6;
}

@media (max-width: 768px) {
  .fs-membro-single__inner {
    grid-template-columns: 1fr;
    gap: 2rem;
  }
}
```

- [ ] **Step 4: Adicionar import em main.css**

```css
@import url('sections/time.css');
```

- [ ] **Step 5: Cadastrar 2 membros de teste no painel**

Membros > Adicionar Membro. Preencher nome, foto, funcao e bio. Publicar.

- [ ] **Step 6: Verificar grid e pagina individual**

Acessar `#time` na home. Cards devem aparecer no grid. Clicar em um membro deve abrir a pagina individual com foto, bio e redes sociais.

---

## Task 8: Secoes Galeria, Agenda, Imprensa, Parceiros e Contato

**Files:**
- Modify: `freestep-child/front-page.php`
- Create: `freestep-child/assets/css/sections/galeria.css`
- Create: `freestep-child/assets/css/sections/agenda.css`
- Create: `freestep-child/assets/css/sections/imprensa.css`
- Create: `freestep-child/assets/css/sections/parceiros.css`
- Create: `freestep-child/assets/css/sections/contato.css`

**Interfaces:**
- Consumes: CPTs `fs_show`, `fs_imprensa`, `fs_parceiro` (Task 3); WPForms Lite instalado
- Produces: Site completo com todas as secoes funcionais

- [ ] **Step 1: Instalar Envira Gallery Lite**

No painel: Plugins > Adicionar Novo > "Envira Gallery" > Instalar > Ativar.
Criar uma galeria: Envira Gallery > Adicionar Nova > upload de fotos > Publicar. Copiar o shortcode gerado (ex: `[envira-gallery id="123"]`).

- [ ] **Step 2: Instalar WPForms Lite**

No painel: Plugins > Adicionar Novo > "WPForms Lite" > Instalar > Ativar.
WPForms > Adicionar Novo > Formulario Simples de Contato. Campos: Nome, Email, Telefone, Tipo de Contato (dropdown: Show / Publicidade / Outro), Mensagem. Configurar notificacao para `timefreestepbrasil@gmail.com`. Publicar. Copiar shortcode (ex: `[wpforms id="456"]`).

- [ ] **Step 3: Adicionar secoes no front-page.php**

Inserir apos a secao `#time`, substituindo `SHORTCODE_GALERIA` e `SHORTCODE_FORM` pelos shortcodes reais:

```php
<!-- GALERIA -->
<section id="galeria" class="fs-section">
  <div class="fs-container">
    <h2 class="fs-section__title">Galeria</h2>
    <div class="section-divider"></div>
    <?php echo do_shortcode('[SHORTCODE_GALERIA]'); ?>
  </div>
</section>

<!-- AGENDA -->
<section id="agenda" class="fs-section fs-section--alt">
  <div class="fs-container">
    <h2 class="fs-section__title">Agenda</h2>
    <div class="section-divider"></div>
    <div class="fs-agenda__lista">
      <?php
      $hoje = date('Ymd');
      $shows = new WP_Query([
        'post_type'      => 'fs_show',
        'posts_per_page' => 10,
        'meta_key'       => 'show_data',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => [[
          'key'     => 'show_data',
          'value'   => $hoje,
          'compare' => '>=',
          'type'    => 'DATE',
        ]],
      ]);
      if ($shows->have_posts()) :
        while ($shows->have_posts()) : $shows->the_post();
          $data   = get_field('show_data');
          $local  = get_field('show_local');
          $cidade = get_field('show_cidade');
          $link   = get_field('show_link_ingresso');
      ?>
      <div class="fs-agenda__item">
        <div class="fs-agenda__data"><?php echo date('d/m/Y', strtotime($data)); ?></div>
        <div class="fs-agenda__info">
          <strong><?php the_title(); ?></strong>
          <span><?php echo esc_html($local); ?> &mdash; <?php echo esc_html($cidade); ?></span>
        </div>
        <?php if ($link): ?>
          <a href="<?php echo esc_url($link); ?>" class="btn-contratar" target="_blank" rel="noopener">Ingressos</a>
        <?php endif; ?>
      </div>
      <?php endwhile; wp_reset_postdata();
      else : ?>
        <p style="text-align:center; opacity:0.6;">Nenhum show agendado no momento.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- IMPRENSA -->
<section id="imprensa" class="fs-section">
  <div class="fs-container">
    <h2 class="fs-section__title">Imprensa</h2>
    <div class="section-divider"></div>
    <div class="fs-imprensa__grid">
      <?php
      $aparicoes = new WP_Query(['post_type' => 'fs_imprensa', 'posts_per_page' => 12]);
      while ($aparicoes->have_posts()) : $aparicoes->the_post();
        $veiculo = get_field('imprensa_veiculo');
        $link    = get_field('imprensa_link');
        $logo    = get_field('imprensa_logo');
        $data    = get_field('imprensa_data');
      ?>
      <a href="<?php echo esc_url($link); ?>" class="fs-imprensa__item" target="_blank" rel="noopener">
        <?php if ($logo): ?>
          <img src="<?php echo esc_url($logo['url']); ?>" alt="<?php echo esc_attr($veiculo); ?>">
        <?php else: ?>
          <span class="fs-imprensa__nome"><?php echo esc_html($veiculo); ?></span>
        <?php endif; ?>
        <span class="fs-imprensa__titulo"><?php the_title(); ?></span>
      </a>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
  </div>
</section>

<!-- PARCEIROS -->
<section id="parceiros" class="fs-section fs-section--alt">
  <div class="fs-container">
    <h2 class="fs-section__title">Parceiros</h2>
    <div class="section-divider"></div>
    <div class="fs-parceiros__grid">
      <?php
      $parceiros = new WP_Query(['post_type' => 'fs_parceiro', 'posts_per_page' => -1]);
      while ($parceiros->have_posts()) : $parceiros->the_post();
        $link = get_field('parceiro_link');
      ?>
      <a href="<?php echo esc_url($link); ?>" class="fs-parceiro" target="_blank" rel="noopener">
        <?php the_post_thumbnail('medium', ['alt' => get_the_title()]); ?>
      </a>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
  </div>
</section>

<!-- CONTATO -->
<section id="contato" class="fs-section">
  <div class="fs-container" style="max-width: 700px;">
    <h2 class="fs-section__title">Contato</h2>
    <div class="section-divider"></div>
    <p style="text-align:center; margin-bottom: 2rem; font-size: 1.1rem;">
      Jobs, shows e publicidade: entre em contato e nossa equipe responde em breve.
    </p>
    <?php echo do_shortcode('[SHORTCODE_FORM]'); ?>
  </div>
</section>
```

- [ ] **Step 4: Criar CSS das secoes restantes**

`assets/css/sections/agenda.css`:
```css
.fs-agenda__item {
  display: flex;
  align-items: center;
  gap: 2rem;
  padding: 1.25rem 0;
  border-bottom: 1px solid #e0e0e0;
}

.fs-agenda__data {
  font-family: var(--font-titulo);
  font-size: 1.25rem;
  color: var(--color-verde);
  min-width: 110px;
  font-weight: 700;
}

.fs-agenda__info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.fs-agenda__info strong {
  font-family: var(--font-titulo);
  font-size: 1.1rem;
  text-transform: uppercase;
}

.fs-agenda__info span {
  font-size: 0.9rem;
  opacity: 0.7;
}

@media (max-width: 600px) {
  .fs-agenda__item { flex-direction: column; align-items: flex-start; gap: 0.75rem; }
}
```

`assets/css/sections/imprensa.css`:
```css
.fs-imprensa__grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 1.5rem;
  margin-top: 2rem;
}

.fs-imprensa__item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  padding: 1.5rem;
  border: 1px solid #e0e0e0;
  text-decoration: none;
  color: inherit;
  transition: border-color 0.2s;
}

.fs-imprensa__item:hover { border-color: var(--color-dourado); }

.fs-imprensa__item img { max-height: 60px; object-fit: contain; }

.fs-imprensa__titulo {
  font-size: 0.85rem;
  text-align: center;
  opacity: 0.75;
}

.fs-imprensa__nome {
  font-family: var(--font-titulo);
  font-size: 1.2rem;
  color: var(--color-verde);
}
```

`assets/css/sections/parceiros.css`:
```css
.fs-parceiros__grid {
  display: flex;
  flex-wrap: wrap;
  gap: 2rem;
  justify-content: center;
  align-items: center;
  margin-top: 2rem;
}

.fs-parceiro img {
  max-height: 80px;
  max-width: 160px;
  object-fit: contain;
  filter: grayscale(1);
  opacity: 0.6;
  transition: filter 0.2s, opacity 0.2s;
}

.fs-parceiro:hover img {
  filter: grayscale(0);
  opacity: 1;
}
```

`assets/css/sections/contato.css`:
```css
/* WPForms override para seguir a paleta */
.wpforms-submit {
  background: var(--color-verde) !important;
  color: var(--color-branco) !important;
  font-family: var(--font-titulo) !important;
  text-transform: uppercase !important;
  font-size: 1rem !important;
  padding: 0.75rem 2rem !important;
  border: none !important;
  cursor: pointer !important;
  border-radius: 0 !important;
}

.wpforms-submit:hover {
  background: var(--color-dourado) !important;
  color: var(--color-texto) !important;
}

.wpforms-field input,
.wpforms-field textarea,
.wpforms-field select {
  border: 1px solid #ccc !important;
  border-radius: 0 !important;
  font-family: var(--font-corpo) !important;
}
```

- [ ] **Step 5: Adicionar imports em main.css**

```css
@import url('sections/agenda.css');
@import url('sections/imprensa.css');
@import url('sections/parceiros.css');
@import url('sections/contato.css');
```

- [ ] **Step 6: Verificar todas as secoes no navegador**

Rolar a pagina do topo ao rodape. Confirmar que todas as secoes renderizam, o formulario aparece e o menu leva a cada secao via scroll suave.

- [ ] **Step 7: Testar formulario de contato**

Preencher e enviar o formulario. Verificar que o email chega em `timefreestepbrasil@gmail.com`.

---

## Task 9: Ajustes finais e entrega para o time

**Files:**
- Modify: Configuracoes do WordPress

**Interfaces:**
- Produces: Site pronto para uso, com painel documentado para o responsavel do time

- [ ] **Step 1: Configurar permalinks**

Configuracoes > Links Permanentes > "Nome do post" > Salvar.

- [ ] **Step 2: Remover conteudo de exemplo**

Excluir o post "Hello World" e a pagina "Sample Page" criados pelo WordPress na instalacao.

- [ ] **Step 3: Criar usuario editor para o time**

Usuarios > Adicionar Novo:
- Funcao: Editor
- Nome de usuario: `editor-freestep`
- Email: `timefreestepbrasil@gmail.com`
- Senha: forte, a ser entregue para o responsavel pelo time

- [ ] **Step 4: Instalar plugin de backup**

Plugins > Adicionar Novo > "UpdraftPlus" > Instalar > Ativar.
Configurar backup semanal automatico para Google Drive ou Download Local.

- [ ] **Step 5: Verificacao final de responsividade**

Testar no navegador com DevTools em:
- 375px (celular)
- 768px (tablet)
- 1280px (desktop)

Confirmar que menu hamburguer abre no mobile, grid de membros reorganiza, secoes ficam legiveis.

- [ ] **Step 6: Verificar velocidade**

Plugins > Adicionar Novo > "LiteSpeed Cache" ou "W3 Total Cache" > Instalar > Ativar com configuracao padrao. Recarregar o site e confirmar que assets estao sendo cacheados.

- [ ] **Step 7: Documentar painel para o responsavel**

Criar documento simples (pode ser pagina privada no proprio WordPress) com instrucoes:
- Como adicionar membro
- Como adicionar show na agenda
- Como publicar noticia
- Como adicionar aparicao na imprensa
- Como adicionar parceiro
