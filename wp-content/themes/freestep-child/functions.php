<?php

require_once get_stylesheet_directory() . '/inc/acf-fields.php';
require_once get_stylesheet_directory() . '/inc/social.php';

/**
 * Versao do asset = data de modificacao do arquivo. Enquanto o site esta em
 * construcao isso evita o cache do navegador servir CSS velho depois de um
 * deploy, sem precisar lembrar de subir um numero a mao.
 */
function freestep_asset_version($relativo)
{
    $caminho = get_stylesheet_directory() . $relativo;

    return file_exists($caminho) ? (string) filemtime($caminho) : '1.0.0';
}

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_uri(), ['parent-style'], freestep_asset_version('/style.css'));
    wp_enqueue_style('freestep-main', get_stylesheet_directory_uri() . '/assets/css/main.css', ['child-style'], freestep_asset_version('/assets/css/main.css'));

    // Cada secao entra como handle proprio, dependente do main. Com @import
    // dentro do main.css elas vinham sem versao e o cache servia CSS velho.
    $secoes = ['header', 'footer', 'hero', 'sobre', 'time', 'social', 'agenda', 'imprensa', 'parceiros', 'contato'];
    foreach ($secoes as $secao) {
        $relativo = "/assets/css/sections/{$secao}.css";
        wp_enqueue_style(
            "freestep-{$secao}",
            get_stylesheet_directory_uri() . $relativo,
            ['freestep-main'],
            freestep_asset_version($relativo)
        );
    }

    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700&family=Inter:wght@400;600&display=swap', [], null);
    wp_enqueue_script('freestep-main', get_stylesheet_directory_uri() . '/assets/js/main.js', [], freestep_asset_version('/assets/js/main.js'), true);
});
