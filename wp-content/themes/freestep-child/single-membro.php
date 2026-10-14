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
