<?php
/**
 * GoNorth Custom Header
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$assets = get_stylesheet_directory_uri() . '/assets';
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- Favicons -->
<link rel="icon" type="image/svg+xml" href="<?php echo esc_url( $assets . '/gonorth-icon.svg' ); ?>">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo esc_url( $assets . '/gonorth-32.png' ); ?>">
<link rel="apple-touch-icon" sizes="192x192" href="<?php echo esc_url( $assets . '/gonorth-192.png' ); ?>">
<meta name="msapplication-TileImage" content="<?php echo esc_url( $assets . '/gonorth-192.png' ); ?>">
<!-- Google Tag Manager -->
<script data-noptimize="1">(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-WM5VRDCR');</script>
<!-- End Google Tag Manager -->
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WM5VRDCR"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<a class="gn-skip-link screen-reader-text" href="#main-content">דלג לתוכן הראשי</a>

<header class="gn-header" id="gn-header">
  <div class="gn-header__inner">

    <!-- Logo -->
    <a href="<?php echo esc_url( home_url('/') ); ?>" class="gn-logo">
      <img
        src="<?php echo esc_url( $assets . '/gonorth-icon.svg' ); ?>"
        alt="כיוון צפון — מדריך הצפון"
        width="48"
        height="48"
        class="gn-logo__img"
      >
      <span class="gn-logo__tagline"><span class="gn-logo__tagline-light">כיוון</span><span class="gn-logo__tagline-bold">צפון</span></span>
    </a>

    <!-- Desktop Nav -->
    <nav class="gn-nav" id="gn-nav" aria-label="ניווט ראשי">
      <?php
      wp_nav_menu([
        'theme_location' => '',
        'menu'           => 26,
        'container'      => false,
        'items_wrap'     => '<ul class="gn-nav__list">%3$s</ul>',
        'fallback_cb'    => function() {
          echo '<ul class="gn-nav__list">
            <li><a href="' . home_url('/places/category/attractions/') . '">אטרקציות</a></li>
            <li><a href="' . home_url('/places/category/accommodation/') . '">לינה</a></li>
            <li><a href="' . home_url('/places/category/restaurants/') . '">אוכל</a></li>
            <li><a href="' . home_url('/places/category/tours/') . '">סיורים</a></li>
            <li><a href="' . home_url('/blog/') . '">בלוג</a></li>
          </ul>';
        },
      ]);
      ?>
    </nav>

    <!-- Mobile hamburger -->
    <button class="gn-hamburger" id="gn-hamburger" aria-label="פתח תפריט" aria-expanded="false" aria-controls="gn-mobile-menu">
      <span></span><span></span><span></span>
    </button>

  </div>

  <!-- Mobile menu -->
  <div class="gn-mobile-menu" id="gn-mobile-menu" aria-hidden="true">
    <ul>
      <li><a href="<?php echo home_url('/places/category/attractions/'); ?>">אטרקציות</a></li>
      <li><a href="<?php echo home_url('/places/category/accommodation/'); ?>">לינה</a></li>
      <li><a href="<?php echo home_url('/places/category/restaurants/'); ?>">אוכל</a></li>
      <li><a href="<?php echo home_url('/places/category/tours/'); ?>">סיורים</a></li>
      <li><a href="<?php echo home_url('/blog/'); ?>">בלוג</a></li>
    </ul>
  </div>
</header>

<div id="main-content" tabindex="-1"></div>

<script>
document.getElementById('gn-hamburger').addEventListener('click', function() {
  var menu = document.getElementById('gn-mobile-menu');
  var isOpen = menu.classList.toggle('open');
  this.classList.toggle('open');
  this.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  this.setAttribute('aria-label', isOpen ? 'סגור תפריט' : 'פתח תפריט');
  menu.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
});
</script>

<?php
// Allow Astra hooks to fire (astra_content_before etc.)
if ( function_exists( 'astra_content_before' ) ) astra_content_before();
?>
