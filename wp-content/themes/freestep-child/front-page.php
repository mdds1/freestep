<?php get_header(); ?>

<main>

<!-- CAPA -->
<section id="capa" class="fs-hero">
  <div class="fs-hero__overlay"></div>
  <div class="fs-hero__content">
    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/logo-verde-dourado.png"
         alt="Time Free Step Brasil" class="fs-hero__logo">
    <p class="fs-hero__tagline">Levando a cultura do Free Step brasileiro para o mundo</p>
    <a href="#contato" class="btn-contratar">Contratar o Time</a>
  </div>
</section>

<!-- SOBRE -->
<section id="sobre" class="fs-section">
  <div class="fs-container">
    <h2 class="fs-section__title">Sobre o Time</h2>
    <div class="section-divider"></div>
    <div class="fs-sobre__grid">
      <div class="fs-sobre__texto">
        <p>O Time Free Step Brasil e referencia nacional na danca Free Step, levando a cultura brasileira para palcos no Brasil e no mundo. Com apresentacoes em paises como Espanha e Peru, e aparicoes em programas de televisao como o SBT, o time une tecnica, criatividade e orgulho nacional.</p>
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
      <?php // article, e nao <a> envolvendo tudo: os botoes de rede sao
            // links, e link dentro de link e HTML invalido. ?>
      <article class="fs-membro-card">
        <a class="fs-membro-card__foto" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
          <?php if (has_post_thumbnail()): ?>
            <?php the_post_thumbnail('freestep-membro', ['alt' => '']); ?>
          <?php else: ?>
            <span class="fs-membro-card__inicial"><?php echo esc_html(mb_substr(get_the_title(), 0, 1)); ?></span>
          <?php endif; ?>
        </a>
        <div class="fs-membro-card__info">
          <h3 class="fs-membro-card__nome">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
          </h3>
          <?php if ($funcao): ?>
            <p class="fs-membro-card__funcao"><?php echo esc_html($funcao); ?></p>
          <?php endif; ?>
          <?php freestep_redes_do_membro(get_the_ID(), 'fs-social fs-membro-card__social'); ?>
        </div>
      </article>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
  </div>
</section>

<!-- GALERIA -->
<section id="galeria" class="fs-section">
  <div class="fs-container">
    <h2 class="fs-section__title">Galeria</h2>
    <div class="section-divider"></div>
    <?php
    // Substituir pelo shortcode real apos criar galeria no painel:
    // echo do_shortcode('[envira-gallery id="SEU_ID"]');
    ?>
    <div class="fs-vazio">
      <p class="fs-vazio__titulo">Galeria em producao</p>
      <p>Estamos selecionando as melhores fotos e videos das apresentacoes. Em breve esta secao vai estar cheia.</p>
    </div>
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
        <div class="fs-vazio">
          <p class="fs-vazio__titulo">Sem datas abertas no momento</p>
          <p>A agenda de apresentacoes e divulgada aqui assim que confirmada. Para contratar o time para uma data, fale com a gente.</p>
          <p style="margin-top:1.5rem;"><a href="#contato" class="btn-contratar">Contratar o Time</a></p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- IMPRENSA -->
<section id="imprensa" class="fs-section">
  <div class="fs-container">
    <h2 class="fs-section__title">Imprensa</h2>
    <div class="section-divider"></div>
    <?php $aparicoes = new WP_Query(['post_type' => 'fs_imprensa', 'posts_per_page' => 12]); ?>
    <?php if (!$aparicoes->have_posts()) : ?>
      <div class="fs-vazio">
        <p class="fs-vazio__titulo">Materias a caminho</p>
        <p>O time ja passou por televisao e veiculos de imprensa no Brasil e fora. Estamos reunindo os links.</p>
      </div>
    <?php else : ?>
    <div class="fs-imprensa__grid">
      <?php
      while ($aparicoes->have_posts()) : $aparicoes->the_post();
        $veiculo = get_field('imprensa_veiculo');
        $link    = get_field('imprensa_link');
        $logo    = get_field('imprensa_logo');
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
    <?php endif; ?>
  </div>
</section>

<!-- PARCEIROS -->
<section id="parceiros" class="fs-section fs-section--alt">
  <div class="fs-container">
    <h2 class="fs-section__title">Parceiros</h2>
    <div class="section-divider"></div>
    <?php $parceiros = new WP_Query(['post_type' => 'fs_parceiro', 'posts_per_page' => -1]); ?>
    <?php if (!$parceiros->have_posts()) : ?>
      <div class="fs-vazio">
        <p class="fs-vazio__titulo">Espaco para parceiros</p>
        <p>Marcas interessadas em patrocinar ou fazer permuta com o time podem falar com a gente.</p>
        <p style="margin-top:1.5rem;"><a href="#contato" class="btn-contratar">Quero ser parceiro</a></p>
      </div>
    <?php else : ?>
    <div class="fs-parceiros__grid">
      <?php
      while ($parceiros->have_posts()) : $parceiros->the_post();
        $link = get_field('parceiro_link');
      ?>
      <a href="<?php echo esc_url($link); ?>" class="fs-parceiro" target="_blank" rel="noopener">
        <?php the_post_thumbnail('medium', ['alt' => get_the_title()]); ?>
      </a>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <?php endif; ?>
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
    <?php
    // Substituir pelo shortcode real apos criar formulario no WPForms:
    // echo do_shortcode('[wpforms id="SEU_ID"]');
    ?>
    <div class="fs-vazio">
      <p class="fs-vazio__titulo">Fale com a gente</p>
      <p>Escreva para <a href="mailto:timefreestepbrasil@gmail.com">timefreestepbrasil@gmail.com</a> com a data, o local e o tipo de evento.</p>
    </div>
  </div>
</section>

</main>

<?php get_footer(); ?>
