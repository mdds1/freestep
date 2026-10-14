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
           alt="Time Free Step Brasil" height="56">
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
