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

<!-- GALERIA -->
<section id="galeria" class="fs-section">
  <div class="fs-container">
    <h2 class="fs-section__title">Galeria</h2>
    <div class="section-divider"></div>
    <?php
    // Substituir pelo shortcode real apos criar galeria no painel:
    // echo do_shortcode('[envira-gallery id="SEU_ID"]');
    ?>
    <p style="text-align:center; opacity:0.5;">Galeria em breve.</p>
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
    <?php
    // Substituir pelo shortcode real apos criar formulario no WPForms:
    // echo do_shortcode('[wpforms id="SEU_ID"]');
    ?>
    <p style="text-align:center; opacity:0.5;">Formulario em breve.</p>
  </div>
</section>

</main>

<?php get_footer(); ?>
