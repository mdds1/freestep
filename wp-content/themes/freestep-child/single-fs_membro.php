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
      <?php // O campo usa new_lines => wpautop, entao o ACF ja devolve os
            // paragrafos em HTML. esc_html aqui imprimia as tags <p> na
            // tela. wp_kses_post mantem a formatacao e barra tag perigosa. ?>
      <div class="fs-membro-single__bio"><?php echo wp_kses_post(get_field('membro_bio')); ?></div>
      <?php freestep_redes_do_membro(get_the_ID(), 'fs-social fs-social--grande fs-membro-single__social'); ?>
      <a href="<?php echo home_url('/#time'); ?>" class="fs-membro-single__voltar">&#8592; Voltar para o time</a>
    </div>
  </div>
</section>
<?php endwhile; ?>
</main>

<?php get_footer(); ?>
