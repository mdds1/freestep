<?php
/**
 * Pagina do membro, no formato do midia kit.
 *
 * A estrutura espelha o documento que o time manda para contratante e
 * imprensa: release, numeros por rede, paises, conquistas, imprensa e
 * marcas. Bloco sem conteudo preenchido simplesmente nao aparece, em vez de
 * deixar titulo vazio na pagina.
 */

get_header(); ?>

<main>
<?php while (have_posts()) : the_post();
  $id         = get_the_ID();
  $funcao     = get_field('membro_funcao');
  $bio        = get_field('membro_bio');
  $numeros    = freestep_pares('membro_numeros', $id);
  $conquistas = freestep_linhas('membro_conquistas', $id);
  $paises     = freestep_itens('membro_paises', $id);
  $imprensa   = freestep_pares('membro_imprensa', $id);
  $marcas     = freestep_itens('membro_marcas', $id);
?>

<section class="fs-section fs-membro-single">
  <div class="fs-container fs-membro-single__inner">
    <div class="fs-membro-single__foto">
      <?php the_post_thumbnail('large'); ?>
    </div>
    <div class="fs-membro-single__dados">
      <?php if ($funcao): ?>
        <p class="fs-membro-single__funcao"><?php echo esc_html($funcao); ?></p>
      <?php endif; ?>
      <h1 class="fs-membro-single__nome"><?php the_title(); ?></h1>
      <div class="section-divider" style="margin: 0.5rem 0 1.5rem;"></div>
      <?php if ($bio): ?>
        <?php // O campo usa new_lines => wpautop, entao o ACF ja devolve os
              // paragrafos em HTML. esc_html aqui imprimia as tags <p> na
              // tela. wp_kses_post mantem a formatacao e barra tag perigosa. ?>
        <div class="fs-membro-single__bio"><?php echo wp_kses_post($bio); ?></div>
      <?php endif; ?>
      <?php freestep_redes_do_membro($id, 'fs-social fs-social--grande fs-membro-single__social'); ?>
    </div>
  </div>
</section>

<?php if ($numeros): ?>
<section class="fs-section fs-section--alt fs-bloco">
  <div class="fs-container">
    <h2 class="fs-section__title">Alcance</h2>
    <div class="section-divider"></div>
    <div class="fs-numeros-rede">
      <?php foreach ($numeros as $n): ?>
        <div class="fs-numero-rede">
          <span class="fs-numero-rede__valor"><?php echo esc_html($n['valor']); ?></span>
          <span class="fs-numero-rede__rede"><?php echo esc_html($n['rotulo']); ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($conquistas): ?>
<section class="fs-section fs-bloco">
  <div class="fs-container" style="max-width: 860px;">
    <h2 class="fs-section__title">Conquistas</h2>
    <div class="section-divider"></div>
    <ul class="fs-lista-marcada">
      <?php foreach ($conquistas as $c): ?>
        <li><?php echo esc_html($c); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php if ($paises): ?>
<section class="fs-section fs-section--alt fs-bloco">
  <div class="fs-container">
    <?php // Titulo sem contagem de proposito: o documento do Jeff diz "mais
          // de 15 paises" e a lista traz 16 com o Brasil. Afirmar um numero
          // aqui brigaria com o que esta escrito no midia kit dele. ?>
    <h2 class="fs-section__title">Países</h2>
    <div class="section-divider"></div>
    <div class="fs-chips">
      <?php foreach ($paises as $p): ?>
        <span class="fs-chip"><?php echo esc_html($p); ?></span>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($imprensa): ?>
<section class="fs-section fs-bloco">
  <div class="fs-container">
    <h2 class="fs-section__title">Imprensa</h2>
    <div class="section-divider"></div>
    <div class="fs-imprensa-lista">
      <?php foreach ($imprensa as $i): ?>
        <div class="fs-imprensa-lista__item">
          <strong><?php echo esc_html($i['rotulo']); ?></strong>
          <?php if ($i['valor']): ?>
            <span><?php echo esc_html($i['valor']); ?></span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($marcas): ?>
<section class="fs-section fs-section--alt fs-bloco">
  <div class="fs-container">
    <h2 class="fs-section__title">Marcas e parcerias</h2>
    <div class="section-divider"></div>
    <div class="fs-chips">
      <?php foreach ($marcas as $m): ?>
        <span class="fs-chip fs-chip--marca"><?php echo esc_html($m); ?></span>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="fs-section fs-membro-single__rodape">
  <div class="fs-container" style="text-align:center;">
    <a href="<?php echo esc_url(home_url('/#contato')); ?>" class="btn-contratar">Contratar o Time</a>
    <p><a href="<?php echo esc_url(home_url('/#time')); ?>" class="fs-membro-single__voltar">&#8592; Voltar para o time</a></p>
  </div>
</section>

<?php endwhile; ?>
</main>

<?php get_footer(); ?>
