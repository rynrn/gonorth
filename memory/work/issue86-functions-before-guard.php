<?php
/**
 * GoNorth Child Theme — functions.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/inc/category-hubs.php';
require_once __DIR__ . '/inc/archive-itemlist-schema.php';

// ─── Fix GeoDirectory SQL errors: intercept bad column queries ───
// When get_post_meta('geodir_post_city') is called, GeoDirectory strips 'geodir_'
// and queries 'post_city' column which doesn't exist. We redirect to wp_postmeta instead.
add_filter( 'geodir_pre_get_post_meta', function( $value, $post_id, $meta_key, $single ) {
    $map = [
        'post_city'      => 'geodir_post_city',
        'post_rating'    => 'geodir_post_rating',
        'post_phone'     => 'geodir_post_phone',
        'post_website'   => 'geodir_post_website',
        'post_latitude'  => 'geodir_post_latitude',
        'post_longitude' => 'geodir_post_longitude',
    ];
    if ( isset( $map[ $meta_key ] ) ) {
        global $wpdb;
        $val = $wpdb->get_var( $wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s LIMIT 1",
            $post_id, $map[ $meta_key ]
        ) );
        return $val !== null ? $val : false;
    }
    return $value;
}, 10, 4 );

// ─── Enqueue styles ───
add_action( 'wp_enqueue_scripts', 'gonorth_enqueue_styles' );
function gonorth_enqueue_styles() {
    $main_dependencies = [];

    // The custom homepage template is fully styled by gonorth.css. Loading the
    // parent stylesheet there adds a render-blocking request without affecting
    // the page. Inner pages keep the Astra dependency.
    if ( ! is_front_page() ) {
        wp_enqueue_style( 'astra-parent', get_template_directory_uri() . '/style.css' );
        $main_dependencies[] = 'astra-parent';
    }

    wp_enqueue_style(
        'gonorth-fonts',
        get_stylesheet_directory_uri() . '/assets/fonts/fonts.css',
        [],
        '1.0.0'
    );
    wp_enqueue_style( 'gonorth-main',
        get_stylesheet_directory_uri() . '/assets/css/gonorth.css',
        $main_dependencies, '2.9.12'
    );
}

// The homepage is a custom PHP template and does not render Gutenberg,
// WooCommerce, GeoDirectory widgets, Font Awesome, or Astra components.
// Remove those front-end payloads only on that template; the server-side place
// queries in front-page.php continue to work normally.
add_action( 'wp_enqueue_scripts', 'gonorth_optimize_homepage_assets', PHP_INT_MAX );
function gonorth_optimize_homepage_assets() {
    if ( ! is_front_page() ) {
        return;
    }

    $styles = [
        'astra-theme-css',
        'astra-google-fonts',
        'astra-parent',
        'wp-block-library',
        'wp-block-library-theme',
        'global-styles',
        'classic-theme-styles',
        'woocommerce-layout',
        'woocommerce-smallscreen',
        'woocommerce-general',
        'wc-blocks-style',
        'ayecode-ui',
        'font-awesome',
        'gonorth-fonts',
        'gonorth-main',
        'gonorth-place-data',
    ];

    foreach ( $styles as $handle ) {
        wp_dequeue_style( $handle );
    }

    $scripts = [
        'bootstrap-js-bundle',
        'geodir-js',
        'geodir',
        'sourcebuster-js',
        'wc-order-attribution',
        'astra-theme-js',
    ];

    foreach ( $scripts as $handle ) {
        wp_dequeue_script( $handle );
    }
}

// Inline the complete homepage styles so the first render does not wait for
// separate CSS requests. The cached HTML keeps the server-side file reads off
// the normal request path while preserving the exact existing design.
add_action( 'wp_head', 'gonorth_inline_homepage_styles', 2 );
function gonorth_inline_homepage_styles() {
    if ( ! is_front_page() ) {
        return;
    }

    $files = [
        get_stylesheet_directory() . '/assets/fonts/fonts.css',
        get_stylesheet_directory() . '/assets/css/gonorth.css',
        WP_PLUGIN_DIR . '/gonorth-place-data/assets/place-data.css',
    ];

    $css = '';
    foreach ( $files as $file ) {
        if ( ! is_readable( $file ) ) {
            continue;
        }

        $file_css = file_get_contents( $file );
        if ( 'fonts.css' === basename( $file ) ) {
            $font_base          = trailingslashit( get_stylesheet_directory_uri() . '/assets/fonts' );
            $critical_font_path = get_stylesheet_directory() . '/assets/fonts/heebo-hebrew.woff2';

            if ( is_readable( $critical_font_path ) ) {
                $critical_font_data = 'data:font/woff2;base64,' . base64_encode( file_get_contents( $critical_font_path ) );
                $file_css = str_replace(
                    "url('./heebo-hebrew.woff2')",
                    "url('" . $critical_font_data . "')",
                    $file_css
                );
            }

            $file_css = str_replace( "url('./", "url('" . $font_base, $file_css );
        }
        $css .= $file_css . "\n";
    }

    if ( '' !== $css ) {
        echo '<style id="gonorth-home-inline-css" data-noptimize="1">' . str_replace( '</style', '<\/style', $css ) . '</style>' . "\n";
    }
}
// Some plugins enqueue assets again while rendering the footer. Remove those
// late additions before WordPress prints footer styles and scripts.
add_action( 'wp_footer', 'gonorth_optimize_homepage_late_assets', 19 );
function gonorth_optimize_homepage_late_assets() {
    if ( ! is_front_page() ) {
        return;
    }

    wp_dequeue_style( 'wc-blocks-style' );

    foreach ( [
        'bootstrap-js-bundle',
        'geodir-js',
        'geodir',
        'wc-add-to-cart',
        'wc-jquery-blockui',
        'wc-js-cookie',
        'woocommerce',
        'sourcebuster-js',
        'wc-order-attribution',
        'astra-theme-js',
    ] as $handle ) {
        wp_dequeue_script( $handle );
    }
}
// Prevent late dependency resolution from printing scripts that the custom
// homepage never uses. OneTap, jQuery, and GoNorth analytics remain untouched.
add_filter( 'script_loader_tag', 'gonorth_suppress_unused_homepage_scripts', 10, 3 );
function gonorth_suppress_unused_homepage_scripts( $tag, $handle, $src ) {
    if ( ! is_front_page() ) {
        return $tag;
    }

    $unused = [
        'bootstrap-js-bundle',
        'geodir-js',
        'geodir',
        'wc-add-to-cart',
        'wc-jquery-blockui',
        'wc-js-cookie',
        'woocommerce',
        'sourcebuster-js',
        'wc-order-attribution',
        'astra-theme-js',
    ];

    return in_array( $handle, $unused, true ) ? '' : $tag;
}
// OneTap remains available on the homepage, but its large stylesheet does not
// need to block the first paint. The browser applies it as soon as it finishes.
add_filter( 'style_loader_tag', 'gonorth_async_homepage_styles', 10, 4 );
function gonorth_async_homepage_styles( $html, $handle, $href, $media ) {
    if ( ! is_front_page() || ! in_array( $handle, [ 'accessibility-onetap', 'accessibility-onetap-fonts-readable' ], true ) ) {
        return $html;
    }

    return sprintf(
        "<link rel='stylesheet' id='%s-css' href='%s' media='print' onload=\"this.media='%s'\">\n<noscript><link rel='stylesheet' href='%s' media='%s'></noscript>\n",
        esc_attr( $handle ),
        esc_url( $href ),
        esc_attr( $media ?: 'all' ),
        esc_url( $href ),
        esc_attr( $media ?: 'all' )
    );
}

// #86: keep commerce assets on all unverified and future editorial posts.
add_action( 'wp_enqueue_scripts', 'gonorth_optimize_verified_editorial_assets', PHP_INT_MAX );
add_action( 'wp_footer', 'gonorth_optimize_verified_editorial_assets', 19 );
function gonorth_verified_editorial_without_commerce() {
    if ( is_page( 436 ) ) {
        return true;
    }
    if ( ! is_singular( 'post' ) ) {
        return false;
    }

    $post = get_queried_object();
    if ( ! $post instanceof WP_Post || ! in_array( (int) $post->ID, [ 517, 516, 515, 469, 464, 448, 447, 443, 433, 432, 431, 430, 429, 428, 427, 270, 269, 268, 267, 262, 98, 97, 96, 95, 94 ], true ) ) {
        return false;
    }
    if ( get_post_meta( $post->ID, 'gn_keep_woocommerce_assets', true ) ) {
        return false;
    }

    // A later editorial update with commerce markup keeps the plugin assets.
    $content = strtolower( (string) $post->post_content );
    foreach ( [ 'woocommerce', 'wc-block', 'add_to_cart', 'product_page', '[products', '[product', '<form', '<iframe', '<script' ] as $marker ) {
        if ( false !== strpos( $content, $marker ) ) {
            return false;
        }
    }
    return true;
}
function gonorth_optimize_verified_editorial_assets() {
    if ( ! gonorth_verified_editorial_without_commerce() ) {
        return;
    }

    foreach ( [
        'woocommerce-layout',
        'woocommerce-smallscreen',
        'woocommerce-general',
        'wc-blocks-style',
    ] as $handle ) {
        wp_dequeue_style( $handle );
    }

    foreach ( [
        'wc-jquery-blockui',
        'wc-add-to-cart',
        'wc-js-cookie',
        'woocommerce',
        'sourcebuster-js',
        'wc-order-attribution',
    ] as $handle ) {
        wp_dequeue_script( $handle );
    }
}

// ─── Force RTL / Hebrew ───
add_filter( 'locale', function() { return 'he_IL'; } );

// ─── Body classes ───
add_filter( 'body_class', function( $classes ) {
    $classes[] = 'gonorth-site';
    $classes[] = 'rtl-site';
    return $classes;
} );

// ─── Excerpt ───
add_filter( 'excerpt_length', function() { return 22; } );
add_filter( 'excerpt_more', function() { return '...'; } );

// ─── Reading time helper ───
function gonorth_reading_time() {
    $content = html_entity_decode( wp_strip_all_tags( strip_shortcodes( get_post_field( 'post_content', get_the_ID() ) ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
    $word_count = preg_match_all( '/[\p{L}\p{N}]+/u', $content );
    $minutes = max( 1, (int) ceil( $word_count / 200 ) );
    return $minutes . ( 1 === $minutes ? ' דקת קריאה' : ' דקות קריאה' );
}

// ─── GeoDirectory defaults: map centered on northern Israel ───
add_filter( 'geodir_default_map_lat',  function() { return '32.9'; } );
add_filter( 'geodir_default_map_lng',  function() { return '35.5'; } );
add_filter( 'geodir_default_map_zoom', function() { return 9; } );

// ─── Theme supports ───
add_action( 'after_setup_theme', function() {
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'gallery' ] );
    add_image_size( 'gonorth-card',  640, 400, true );
    add_image_size( 'gonorth-hero', 1440, 600, true );
    add_image_size( 'gonorth-thumb', 400, 300, true );
} );

/**
 * Render an attachment as a responsive, semantic content image.
 *
 * The attachment ALT is preferred when editors supplied one. Contextual fallback
 * text keeps card and hero images meaningful without mutating media metadata.
 */
function gonorth_content_image( $attachment_id, $size, $fallback_alt, $class, $sizes, $priority = false ) {
    $attachment_id = (int) $attachment_id;
    if ( ! $attachment_id || ! wp_attachment_is_image( $attachment_id ) ) {
        return '';
    }

    $stored_alt = trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );
    $attributes = [
        'class'    => sanitize_html_class( $class ),
        'alt'      => $stored_alt !== '' ? $stored_alt : wp_strip_all_tags( (string) $fallback_alt ),
        'sizes'    => (string) $sizes,
        'decoding' => 'async',
        'loading'  => $priority ? 'eager' : 'lazy',
    ];

    if ( $priority ) {
        $attributes['fetchpriority'] = 'high';
    }

    return wp_get_attachment_image( $attachment_id, $size, false, $attributes );
}

// ─── Register nav menus ───
add_action( 'after_setup_theme', function() {
    register_nav_menus( [
        'primary' => 'תפריט ראשי',
        'footer'  => 'תפריט פוטר',
    ] );
} );

// ─── Sidebars ───
add_action( 'widgets_init', function() {
    register_sidebar( [
        'name'          => 'סרגל צד — מאמרים',
        'id'            => 'gonorth-blog-sidebar',
        'before_widget' => '<div class="gn-sidebar__card">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ] );
    register_sidebar( [
        'name'          => 'סרגל צד — מקומות',
        'id'            => 'gonorth-listings-sidebar',
        'before_widget' => '<div class="gn-sidebar__card">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ] );
    unregister_widget( 'WP_Widget_Meta' );
    unregister_widget( 'WP_Widget_Calendar' );
}, 11 );

// ─── Custom pagination helper ───
function gonorth_pagination() {
    $pages = paginate_links( [
        'type'      => 'array',
        'prev_text' => '→',
        'next_text' => '←',
    ] );
    if ( ! $pages ) return;
    echo '<div class="gn-pagination">';
    foreach ( $pages as $page ) {
        echo $page;
    }
    echo '</div>';
}

// ─── Yoast SEO: homepage meta defaults ───
function gonorth_homepage_seo_profile() {
    return [
        'title'       => 'טיולים בצפון: אטרקציות, לינה ומסלולים | כיוון צפון',
        'description' => 'מתכננים טיול בצפון ישראל? גלו אטרקציות, מסלולי טבע, צימרים, מסעדות, יקבים וסיורים בגליל, בגולן, בכינרת ובכרמל, עם מידע שימושי לתכנון החופשה הבאה.',
        'answer'      => 'כיוון צפון הוא מדריך תיירות לתכנון טיולים וחופשות בצפון ישראל. באתר תמצאו אטרקציות, מסלולי טבע, מקומות לינה, מסעדות, יקבים, עגלות קפה וסיורים בגליל, ברמת הגולן, בכינרת, בכרמל ובעמק יזרעאל. אפשר לחפש לפי אזור או סוג בילוי, להשוות אפשרויות ולקבל מידע שימושי לתכנון יום טיול, חופשה משפחתית או סוף שבוע זוגי — הכול בעברית ובמקום אחד.',
    ];
}

add_action( 'init', function() {
    if ( ! defined( 'WPSEO_VERSION' ) ) return;
    $profile = gonorth_homepage_seo_profile();
    $opts = get_option( 'wpseo_titles', [] );
    if ( empty( $opts['title-home-wpseo'] ) ) {
        $opts['title-home-wpseo']    = $profile['title'];
        $opts['metadesc-home-wpseo'] = $profile['description'];
        update_option( 'wpseo_titles', $opts );
    }
} );

function gonorth_homepage_seo_title( $title ) {
    if ( ! is_front_page() ) return $title;
    $profile = gonorth_homepage_seo_profile();
    return $profile['title'];
}
add_filter( 'wpseo_title', 'gonorth_homepage_seo_title', 20 );
add_filter( 'wpseo_opengraph_title', 'gonorth_homepage_seo_title', 20 );
add_filter( 'wpseo_twitter_title', 'gonorth_homepage_seo_title', 20 );

function gonorth_homepage_seo_description( $description ) {
    if ( ! is_front_page() ) return $description;
    $profile = gonorth_homepage_seo_profile();
    return $profile['description'];
}
add_filter( 'wpseo_metadesc', 'gonorth_homepage_seo_description', 20 );
add_filter( 'wpseo_opengraph_desc', 'gonorth_homepage_seo_description', 20 );
add_filter( 'wpseo_twitter_description', 'gonorth_homepage_seo_description', 20 );

// ─── Yoast SEO: places archive metadata ───
function gonorth_places_archive_seo_title( $title ) {
    if ( ! is_post_type_archive( 'gd_place' ) ) return $title;
    $page = max( 1, (int) get_query_var( 'paged' ) );
    if ( $page > 1 ) {
        return sprintf( 'מקומות בצפון ישראל | כיוון צפון — עמוד %d', $page );
    }
    return 'מקומות בצפון ישראל — אטרקציות, לינה ואוכל | כיוון צפון';
}
add_filter( 'wpseo_title', 'gonorth_places_archive_seo_title' );
add_filter( 'wpseo_opengraph_title', 'gonorth_places_archive_seo_title' );

function gonorth_places_archive_seo_description( $description ) {
    if ( ! is_post_type_archive( 'gd_place' ) ) return $description;
    $description = 'גלו מקומות לטיול בצפון ישראל: אטרקציות, מסלולי טבע, לינה, אוכל וסיורים בגליל, בגולן, בכינרת ובכרמל. מידע לתכנון ביקור בצפון.';
    $page        = max( 1, (int) get_query_var( 'paged' ) );
    return $page > 1 ? $description . sprintf( ' עמוד %d.', $page ) : $description;
}
add_filter( 'wpseo_metadesc', 'gonorth_places_archive_seo_description' );
add_filter( 'wpseo_opengraph_desc', 'gonorth_places_archive_seo_description' );
add_filter( 'wpseo_twitter_description', 'gonorth_places_archive_seo_description' );
add_filter( 'wpseo_twitter_title', 'gonorth_places_archive_seo_title' );

function gonorth_places_archive_schema( $data ) {
    if ( ! is_post_type_archive( 'gd_place' ) ) return $data;
    $data['name']        = gonorth_places_archive_seo_title( '' );
    $data['description'] = gonorth_places_archive_seo_description( '' );
    return $data;
}
add_filter( 'wpseo_schema_webpage', 'gonorth_places_archive_schema' );

// Localize breadcrumb schema for the places archive and legacy food categories.
function gonorth_place_breadcrumb_schema_labels( $piece ) {
    $archive = is_post_type_archive( 'gd_place' );
    $term = is_tax( 'gd_placecategory' ) ? get_queried_object() : null;
    $category = $term instanceof WP_Term && in_array( $term->slug, [ 'ochel', 'batei-kafe' ], true );
    if ( ( ! $archive && ! $category ) || empty( $piece['itemListElement'] ) || ! is_array( $piece['itemListElement'] ) ) return $piece;

    foreach ( $piece['itemListElement'] as &$crumb ) {
        if ( ! is_array( $crumb ) || empty( $crumb['name'] ) ) continue;
        if ( $crumb['name'] === 'Home' ) $crumb['name'] = 'דף הבית';
        if ( $archive && $crumb['name'] === 'Places' ) $crumb['name'] = 'מקומות בצפון';
        if ( $category && $term->slug === 'ochel' && $crumb['name'] === 'ochel' ) $crumb['name'] = 'אוכל ושתייה';
    }
    unset( $crumb );
    return $piece;
}
add_filter( 'wpseo_schema_breadcrumb', 'gonorth_place_breadcrumb_schema_labels', 20 );

// ─── Yoast SEO: canonical place-category metadata ───
function gonorth_category_seo_profiles() {
    return [
        'ochel' => [
            'label'       => 'אוכל ושתייה',
            'h1'          => 'אוכל ושתייה בצפון',
            'title'       => 'אוכל ושתייה בצפון ישראל | כיוון צפון',
            'description' => 'גלו אפשרויות אוכל ושתייה בצפון ישראל, עם מידע על מסעדות, בתי קפה ועגלות קפה. בדקו את פרטי המקום לפני הביקור.',
        ],
        'batei-kafe' => [
            'label'       => 'בתי קפה',
            'h1'          => 'בתי קפה בצפון',
            'title'       => 'בתי קפה בצפון ישראל | כיוון צפון',
            'description' => 'מצאו בתי קפה בצפון ישראל ועיינו בפרטי המקומות לפני היציאה. בדקו שעות פתיחה, מיקום וזמינות מול העסק לפני הביקור.',
        ],
        'atraktziot-main' => [
            'label'       => 'אטרקציות',
            'h1'          => 'אטרקציות בצפון',
            'title'       => 'אטרקציות בצפון ישראל | כיוון צפון',
            'description' => 'מחפשים אטרקציות בצפון? גלו פארקים, מרכזי מבקרים, פעילויות מים וחוויות למשפחות, זוגות וקבוצות בגליל, בגולן, בכינרת ובכרמל, לתכנון יום טיול בצפון.',
        ],
        'teva' => [
            'label'       => 'טבע ומסלולים',
            'h1'          => 'טבע ומסלולים בצפון',
            'title'       => 'טבע ומסלולים בצפון ישראל | כיוון צפון',
            'description' => 'מדריך טבע ומסלולים בצפון ישראל: שמורות, נחלים, מפלים ותצפיות בגליל, בגולן, בחרמון ובכינרת, עם רעיונות למסלולים משפחתיים ולטיולי יום לפני היציאה לדרך.',
        ],
        'accommodation' => [
            'label'       => 'לינה',
            'h1'          => 'לינה וצימרים בצפון',
            'title'       => 'צימרים ולינה בצפון ישראל | כיוון צפון',
            'description' => 'מצאו לינה בצפון ישראל: צימרים, מלונות, גלמפינג ואירוח כפרי בגליל, בגולן, בכינרת ובכרמל, עם מידע שימושי לבחירת החופשה המתאימה לזוגות ולמשפחות.',
        ],
        'restaurants-sub' => [
            'label'       => 'מסעדות',
            'h1'          => 'מסעדות בצפון',
            'title'       => 'מסעדות בצפון ישראל | כיוון צפון',
            'description' => 'גלו מסעדות מומלצות בצפון ישראל: מטבח גלילי, מסעדות שף, אוכל מקומי ומקומות למשפחות בגליל, בגולן, בכינרת ובכרמל, כולל פרטים מעשיים לתכנון הביקור.',
        ],
        'yakavim' => [
            'label'       => 'יקבים',
            'h1'          => 'יקבים בצפון',
            'title'       => 'יקבים בצפון ישראל | כיוון צפון',
            'description' => 'גלו יקבים בצפון ישראל: מרכזי מבקרים, טעימות יין וסיורים ביקבי הגליל, רמת הגולן והכרמל, עם מידע שימושי לתכנון ביקור ולבחירת חוויית היין המתאימה.',
        ],
        'agalot-kafe' => [
            'label'       => 'עגלות קפה',
            'h1'          => 'עגלות קפה בצפון',
            'title'       => 'עגלות קפה בצפון ישראל | כיוון צפון',
            'description' => 'מצאו עגלות קפה בצפון ישראל: קפה, מאפים וארוחות בוקר מול נופי הגליל, הגולן, עמק יזרעאל והכרמל, עם מידע שימושי לכל עצירה בדרך לטיול הבא שלכם בצפון.',
        ],
        'tours' => [
            'label'       => 'סיורים ופעילויות',
            'h1'          => 'סיורים ופעילויות בצפון',
            'title'       => 'סיורים ופעילויות בצפון ישראל | כיוון צפון',
            'description' => 'גלו סיורים ופעילויות בצפון ישראל: טיולי ג׳יפים, קיאקים, רכיבה, סדנאות וסיורים מודרכים למשפחות ולקבוצות בגליל ובגולן, כולל מידע שימושי לתכנון והזמנה.',
        ],
    ];
}

function gonorth_current_category_seo_profile() {
    if ( ! is_tax( 'gd_placecategory' ) ) return [];
    $term = get_queried_object();
    if ( ! $term instanceof WP_Term ) return [];
    $profiles = gonorth_category_seo_profiles();
    return $profiles[ $term->slug ] ?? [];
}

function gonorth_category_seo_title( $title ) {
    $profile = gonorth_current_category_seo_profile();
    if ( ! $profile ) return $title;
    $page = max( 1, (int) get_query_var( 'paged' ) );
    return $page > 1 ? $profile['title'] . sprintf( ' — עמוד %d', $page ) : $profile['title'];
}
add_filter( 'wpseo_title', 'gonorth_category_seo_title', 20 );
add_filter( 'wpseo_opengraph_title', 'gonorth_category_seo_title', 20 );
add_filter( 'wpseo_twitter_title', 'gonorth_category_seo_title', 20 );

function gonorth_category_seo_description( $description ) {
    $profile = gonorth_current_category_seo_profile();
    if ( ! $profile ) return $description;
    $page = max( 1, (int) get_query_var( 'paged' ) );
    return $page > 1 ? $profile['description'] . sprintf( ' עמוד %d.', $page ) : $profile['description'];
}
add_filter( 'wpseo_metadesc', 'gonorth_category_seo_description', 20 );
add_filter( 'wpseo_opengraph_desc', 'gonorth_category_seo_description', 20 );
add_filter( 'wpseo_twitter_description', 'gonorth_category_seo_description', 20 );

function gonorth_category_seo_schema( $data ) {
    $profile = gonorth_current_category_seo_profile();
    if ( ! $profile ) return $data;
    $data['name']        = gonorth_category_seo_title( '' );
    $data['description'] = gonorth_category_seo_description( '' );
    return $data;
}
add_filter( 'wpseo_schema_webpage', 'gonorth_category_seo_schema', 20 );

// ─── Yoast SEO: region archive metadata and editorial profiles ───
function gonorth_region_seo_profiles() {
    return [
        'golan' => [
            'h1'          => 'טיולים ומקומות ברמת הגולן',
            'title'       => 'טיולים ומקומות ברמת הגולן | כיוון צפון',
            'description' => 'מתכננים טיול ברמת הגולן? גלו מסלולי טבע, אטרקציות, יקבים, מסעדות, סיורים ומקומות לינה בצפון ובדרום הגולן, עם רעיונות שימושיים לכל עונה ולכל הרכב.',
            'intro_title' => 'מתכננים חופשה ברמת הגולן',
            'paragraphs'  => [
                'רמת הגולן מתאימה ליום טיול, לסוף שבוע או לחופשה ארוכה. בצפון הגולן תמצאו נוף הררי ואוויר קריר, ובמרכז ובדרום אפשר לשלב אתרי טבע, תצפיות, יקבים ועצירות אוכל. בחרו את האזור ואת סוג החוויה לפי הקצב וההרכב שלכם.',
                'האביב מתאים לפריחה ולמסלולים, בקיץ כדאי להעדיף פעילויות מים ואתרים מוצלים, בסתיו נעים לשלב טיול עם טעימות, ובחורף חשוב לבדוק מזג אוויר והנחיות לפני היציאה. היעזרו בקישורים למטה כדי לבנות מסלול שמתאים לכם.',
            ],
            'links'       => [
                'teva'            => 'טבע ומסלולים',
                'atraktziot-main' => 'אטרקציות',
                'yakavim'         => 'יקבים',
                'accommodation'   => 'לינה וצימרים',
                'tours'           => 'סיורים ופעילויות',
            ],
        ],
        'galil' => [
            'h1'          => 'טיולים ומקומות בגליל',
            'title'       => 'טיולים ומקומות בגליל העליון והתחתון | כיוון צפון',
            'description' => 'מתכננים טיול בגליל? גלו מסלולי טבע, אטרקציות, מסעדות, יקבים, סיורים ומקומות לינה בגליל העליון, התחתון והמערבי, עם רעיונות שימושיים לכל עונה.',
            'intro_title' => 'מתכננים טיול בגליל',
            'paragraphs'  => [
                'הגליל מאפשר לשלב בין טבע, יישובים, אוכל מקומי ומקומות לינה. הגליל העליון מתאים למסלולים, תצפיות וחופשה הררית; הגליל התחתון נוח לטיולי משפחות ולשילוב אתרים סביב הכינרת; ובגליל המערבי אפשר לחבר בין החוף, ערים עתיקות ועצירות קולינריות.',
                'באביב האזור מתאים לטיולי פריחה, בקיץ כדאי לשלב מסלולי מים ופעילות מוצלת, ובסתיו ובחורף אפשר לבנות יום של אוכל, יין ואתרים מקורים. התחילו בבחירת סוג הבילוי, ואחר כך התאימו את עומס המסלול ומרחקי הנסיעה להרכב שלכם.',
            ],
            'links'       => [
                'teva'            => 'טבע ומסלולים',
                'atraktziot-main' => 'אטרקציות',
                'restaurants-sub' => 'מסעדות',
                'accommodation'   => 'לינה וצימרים',
                'tours'           => 'סיורים ופעילויות',
            ],
        ],
        'carmel' => [
            'h1'          => 'טיולים בכרמל ובעמק יזרעאל',
            'title'       => 'טיולים בכרמל ובעמק יזרעאל | כיוון צפון',
            'description' => 'מתכננים טיול בכרמל ובעמק יזרעאל? גלו מסלולי טבע, אטרקציות, מסעדות, עגלות קפה, סיורים ומקומות לינה מחוף הכרמל ועד העמק, עם רעיונות לכל עונה בשנה.',
            'intro_title' => 'מתכננים טיול בכרמל ובעמק יזרעאל',
            'paragraphs'  => [
                'אזור הכרמל ועמק יזרעאל מציע מעבר מהיר בין יער, חוף, נוף חקלאי ויישובים. בחוף הכרמל אפשר לשלב יום בים עם אוכל וטיול קצר; בהר הכרמל תמצאו חורש, תצפיות ומסלולים; ובעמק יזרעאל אפשר לתכנן טיול נינוח בין אתרים, מסעדות ועגלות קפה.',
                'האביב מתאים לפריחה בעמק, הקיץ לשילוב בין חוף לפעילות מוצלת, והסתיו והחורף לימי הליכה קרירים ולעצירות אוכל. לפני היציאה בדקו את העומס במסלול, זמני הנסיעה ושעות הפתיחה, ובחרו את הקטגוריות שמתאימות לקצב הטיול.',
            ],
            'links'       => [
                'teva'            => 'טבע ומסלולים',
                'atraktziot-main' => 'אטרקציות',
                'restaurants-sub' => 'מסעדות',
                'agalot-kafe'     => 'עגלות קפה',
                'accommodation'   => 'לינה וצימרים',
            ],
        ],
    ];
}

function gonorth_current_region_seo_profile() {
    if ( ! is_tax( 'gn_region' ) ) return [];
    $term = get_queried_object();
    if ( ! $term instanceof WP_Term ) return [];
    $profiles = gonorth_region_seo_profiles();
    return $profiles[ $term->slug ] ?? [];
}

function gonorth_region_seo_title( $title ) {
    $profile = gonorth_current_region_seo_profile();
    if ( ! $profile ) return $title;
    $page = max( 1, (int) get_query_var( 'paged' ) );
    return $page > 1 ? $profile['title'] . sprintf( ' — עמוד %d', $page ) : $profile['title'];
}
add_filter( 'wpseo_title', 'gonorth_region_seo_title', 20 );
add_filter( 'wpseo_opengraph_title', 'gonorth_region_seo_title', 20 );
add_filter( 'wpseo_twitter_title', 'gonorth_region_seo_title', 20 );

function gonorth_region_seo_description( $description ) {
    $profile = gonorth_current_region_seo_profile();
    if ( ! $profile ) return $description;
    $page = max( 1, (int) get_query_var( 'paged' ) );
    return $page > 1 ? $profile['description'] . sprintf( ' עמוד %d.', $page ) : $profile['description'];
}
add_filter( 'wpseo_metadesc', 'gonorth_region_seo_description', 20 );
add_filter( 'wpseo_opengraph_desc', 'gonorth_region_seo_description', 20 );
add_filter( 'wpseo_twitter_description', 'gonorth_region_seo_description', 20 );

function gonorth_region_seo_schema( $data ) {
    $profile = gonorth_current_region_seo_profile();
    if ( ! $profile ) return $data;
    $data['name']        = gonorth_region_seo_title( '' );
    $data['description'] = gonorth_region_seo_description( '' );
    return $data;
}
add_filter( 'wpseo_schema_webpage', 'gonorth_region_seo_schema', 20 );

// ─── Social sharing images for primary archive pages ────────────────────────
function gonorth_social_image_profile() {
    $filename = '';
    $alt      = '';

    if ( is_front_page() ) {
        $filename = 'gonorth-og-home.jpg';
        $alt      = 'נוף ירוק, שביל ומים בצפון ישראל — כיוון צפון';
    } elseif ( is_post_type_archive( 'gd_place' ) ) {
        $filename = 'gonorth-og-home.jpg';
        $alt      = 'מקומות וטיולים בצפון ישראל — כיוון צפון';
    } elseif ( is_tax( 'gd_placecategory' ) ) {
        $term = get_queried_object();
        $licensed_category_images = [
            'restaurants-sub' => [ 525, 'שולחנות ערוכים במסעדה בטבריה' ],
            'tours'           => [ 526, 'שיט רפטינג בנהר הירדן בצפון' ],
        ];
        if ( $term instanceof WP_Term && isset( $licensed_category_images[ $term->slug ] ) ) {
            [ $attachment_id, $attachment_alt ] = $licensed_category_images[ $term->slug ];
            $attachment = wp_get_attachment_image_src( $attachment_id, 'full' );
            if ( $attachment ) {
                return [
                    'url'    => $attachment[0],
                    'width'  => (int) $attachment[1],
                    'height' => (int) $attachment[2],
                    'type'   => 'image/jpeg',
                    'alt'    => $attachment_alt,
                ];
            }
        }
        $category_images = [
            'atraktziot-main' => [ 'gonorth-og-category-atraktziot-main.jpg', 'אטרקציות בצפון ישראל' ],
            'teva'            => [ 'gonorth-og-category-teva.jpg', 'טבע ומסלולים בצפון ישראל' ],
            'accommodation'   => [ 'gonorth-og-category-accommodation.jpg', 'לינה וצימרים בצפון ישראל' ],
            'restaurants-sub' => [ 'gonorth-og-category-restaurants-sub.jpg', 'מסעדות בצפון ישראל' ],
            'yakavim'         => [ 'gonorth-og-category-yakavim.jpg', 'יקבים בצפון ישראל' ],
            'agalot-kafe'     => [ 'gonorth-og-category-agalot-kafe.jpg', 'עגלות קפה בצפון ישראל' ],
            'tours'           => [ 'gonorth-og-category-tours.jpg', 'סיורים ופעילויות בצפון ישראל' ],
        ];
        if ( $term instanceof WP_Term && isset( $category_images[ $term->slug ] ) ) {
            [ $filename, $alt ] = $category_images[ $term->slug ];
        }
    } elseif ( is_tax( 'gn_region' ) ) {
        $term = get_queried_object();
        $region_images = [
            'galil'  => [ 'gonorth-og-region-galil.jpg', 'טיולים ומקומות בגליל' ],
            'golan'  => [ 'gonorth-og-region-golan.jpg', 'טיולים ומקומות ברמת הגולן' ],
            'carmel' => [ 'gonorth-og-region-carmel.jpg', 'טיולים בכרמל ובעמק יזרעאל' ],
        ];
        if ( $term instanceof WP_Term && isset( $region_images[ $term->slug ] ) ) {
            [ $filename, $alt ] = $region_images[ $term->slug ];
        }
    }

    if ( ! $filename ) return [];
    return [
        'url'    => trailingslashit( get_stylesheet_directory_uri() ) . 'assets/social/' . $filename,
        'width'  => 1200,
        'height' => 630,
        'type'   => 'image/jpeg',
        'alt'    => $alt,
    ];
}

function gonorth_social_image_url( $image ) {
    $profile = gonorth_social_image_profile();
    return $profile ? $profile['url'] : $image;
}
add_filter( 'wpseo_opengraph_image', 'gonorth_social_image_url', 30 );
add_filter( 'wpseo_twitter_image', 'gonorth_social_image_url', 30 );

function gonorth_add_social_opengraph_image( $images ) {
    $profile = gonorth_social_image_profile();
    if ( $profile && is_object( $images ) && method_exists( $images, 'add_image' ) ) {
        $images->add_image( $profile );
    }
    return $images;
}
add_filter( 'wpseo_add_opengraph_images', 'gonorth_add_social_opengraph_image', 5 );

function gonorth_social_image_width( $width ) {
    $profile = gonorth_social_image_profile();
    return $profile ? $profile['width'] : $width;
}
add_filter( 'wpseo_opengraph_image_width', 'gonorth_social_image_width', 30 );

function gonorth_social_image_height( $height ) {
    $profile = gonorth_social_image_profile();
    return $profile ? $profile['height'] : $height;
}
add_filter( 'wpseo_opengraph_image_height', 'gonorth_social_image_height', 30 );

function gonorth_social_image_type( $type ) {
    $profile = gonorth_social_image_profile();
    return $profile ? $profile['type'] : $type;
}
add_filter( 'wpseo_opengraph_image_type', 'gonorth_social_image_type', 30 );

function gonorth_social_twitter_card( $card ) {
    return gonorth_social_image_profile() ? 'summary_large_image' : $card;
}
add_filter( 'wpseo_twitter_card_type', 'gonorth_social_twitter_card', 30 );

// ─── Blog: Set page 25 as posts page ───
add_action( 'init', function() {
    if ( (int) get_option('page_for_posts') !== 25 ) {
        update_option('page_for_posts', 25);
        update_option('show_on_front', 'page');
        update_option('page_on_front', 23);
    }
}, 20);

// ─── Place search: one GET contract for home and archive ───
function gonorth_place_search_scope(): bool {
    return is_post_type_archive( 'gd_place' ) || is_tax( 'gd_placecategory' );
}

function gonorth_place_search_param( string $name ): string {
    if ( ! isset( $_GET[ $name ] ) || ! is_scalar( $_GET[ $name ] ) ) {
        return '';
    }
    return sanitize_text_field( wp_unslash( (string) $_GET[ $name ] ) );
}

// Verified archive filters. Absence means all, including unknown states.
function gonorth_place_filter_region(): string {
    $region = sanitize_key( gonorth_place_search_param( 'region' ) );
    return in_array( $region, [ 'galil', 'golan', 'carmel' ], true ) ? $region : '';
}

function gonorth_place_filter_saturday(): string {
    $state = sanitize_key( gonorth_place_search_param( 'saturday' ) );
    return in_array( $state, [ 'yes', 'no', 'unknown' ], true ) ? $state : '';
}

function gonorth_place_filters_active(): bool {
    return '' !== gonorth_place_filter_region() || '' !== gonorth_place_filter_saturday();
}

// Even invalid filter values must not create indexable duplicate archive URLs.
function gonorth_place_filter_query_present(): bool {
    return isset( $_GET['region'] ) || isset( $_GET['saturday'] );
}

add_action( 'pre_get_posts', 'gonorth_place_archive_filter_query', 90 );
function gonorth_place_archive_filter_query( WP_Query $query ): void {
    if ( is_admin() || ! $query->is_main_query() ||
        ( ! $query->is_post_type_archive( 'gd_place' ) && ! $query->is_tax( 'gd_placecategory' ) ) ) {
        return;
    }
    $region = gonorth_place_filter_region();
    $saturday = gonorth_place_filter_saturday();
    if ( '' === $region && '' === $saturday ) return;

    $tax = [];
    if ( '' !== $region ) {
        $tax[] = [ 'taxonomy' => 'gn_region', 'field' => 'slug', 'terms' => [ $region ] ];
    }
    if ( 'yes' === $saturday || 'unknown' === $saturday ) {
        $tax[] = [
            'taxonomy' => GoNorth_Place_Attributes::TAXONOMY,
            'field' => 'slug',
            'terms' => [ 'open_saturday' ],
            'operator' => 'yes' === $saturday ? 'IN' : 'NOT IN',
        ];
    }
    if ( $tax ) {
        $existing = $query->get( 'tax_query' );
        $query->set( 'tax_query', array_merge( [ 'relation' => 'AND' ], $existing ? [ (array) $existing ] : [], $tax ) );
    }

    if ( 'no' === $saturday || 'unknown' === $saturday ) {
        $negative = [
            'key' => GoNorth_Place_Attributes::FALSE_META,
            'value' => '"open_saturday"',
            'compare' => 'no' === $saturday ? 'LIKE' : 'NOT LIKE',
        ];
        if ( 'unknown' === $saturday ) {
            $negative = [
                'relation' => 'OR',
                [ 'key' => GoNorth_Place_Attributes::FALSE_META, 'compare' => 'NOT EXISTS' ],
                $negative,
            ];
        }
        $existing = $query->get( 'meta_query' );
        $query->set( 'meta_query', array_merge( [ 'relation' => 'AND' ], $existing ? [ (array) $existing ] : [], [ $negative ] ) );
    }
}

add_filter( 'wpseo_robots_array', 'gonorth_place_filter_robots', 30 );
function gonorth_place_filter_robots( array $robots ): array {
    if ( gonorth_place_search_scope() && gonorth_place_filter_query_present() ) {
        $robots['index'] = 'noindex';
        $robots['follow'] = 'follow';
    }
    return $robots;
}

add_filter( 'wpseo_canonical', 'gonorth_place_filter_canonical', 30 );
function gonorth_place_filter_canonical( $canonical ) {
    if ( ! gonorth_place_search_scope() || ! gonorth_place_filter_query_present() ) return $canonical;
    if ( is_tax( 'gd_placecategory' ) ) {
        $term = get_queried_object();
        $clean = $term instanceof WP_Term ? get_term_link( $term ) : false;
    } else {
        $clean = get_post_type_archive_link( 'gd_place' );
    }
    return $clean && ! is_wp_error( $clean ) ? $clean : $canonical;
}

// Yoast omits the canonical tag on noindex archives, so emit the clean URL here.
add_action( 'wp_head', 'gonorth_place_filter_canonical_tag', 11 );
function gonorth_place_filter_canonical_tag(): void {
    if ( ! gonorth_place_search_scope() || ! gonorth_place_filter_query_present() ) return;
    $canonical = gonorth_place_filter_canonical( '' );
    if ( $canonical ) echo '<link rel="canonical" href="' . esc_url( $canonical ) . '" />' . "
";
}

add_action( 'template_redirect', 'gonorth_place_search_route', 1 );
function gonorth_place_search_route(): void {
    if ( is_admin() || ! gonorth_place_search_scope() ) {
        return;
    }

    // Both forms submit to /places/. Turn the selected category into its
    // canonical taxonomy path; keep only the search term in the query string.
    if ( isset( $_GET['category'] ) ) {
        $slug = sanitize_title( gonorth_place_search_param( 'category' ) );
        $term = $slug ? get_term_by( 'slug', $slug, 'gd_placecategory' ) : false;
        $target = $term ? get_term_link( $term ) : get_post_type_archive_link( 'gd_place' );
        if ( is_wp_error( $target ) || ! $target ) {
            $target = home_url( '/places/' );
        }
        $search = gonorth_place_search_param( 's' );
        if ( '' !== $search ) $target = add_query_arg( 's', $search, $target );
        $region = gonorth_place_filter_region();
        $saturday = gonorth_place_filter_saturday();
        if ( '' !== $region ) $target = add_query_arg( 'region', $region, $target );
        if ( '' !== $saturday ) $target = add_query_arg( 'saturday', $saturday, $target );
        wp_safe_redirect( $target, 302, 'GoNorth Search' );
        exit;
    }

    // An empty GeoDirectory search currently renders the Astra "no post"
    // template repeatedly until PHP fails. Keep the search URL and Yoast's
    // noindex/follow policy, but render one useful empty state instead.
    if ( is_search() || gonorth_place_filters_active() ) {
        global $wp_query;
        if ( $wp_query instanceof WP_Query && 0 === (int) $wp_query->found_posts ) {
            status_header( 200 );
            get_header();
            echo '<main class="gn-search-empty gn-container" id="gn-search-results" dir="rtl">';
            echo '<h2>לא נמצאו מקומות לחיפוש הזה</h2>';
            echo '<p>נסו לשנות אזור או מצב שבת, לחפש מילה קצרה יותר, או עיינו בכל המקומות.</p>';
            echo '<a href="' . esc_url( home_url( '/places/' ) ) . '">הצג את כל המקומות</a>';
            echo '</main>';
            get_footer();
            exit;
        }
    }
}

// ─── GeoDirectory archive: inject GoNorth hero ───
add_action( 'astra_content_before', 'gonorth_geodir_hero_inject' );
function gonorth_geodir_hero_inject() {
    if ( ! gonorth_place_search_scope() ) return;

    $profiles = gonorth_category_seo_profiles();
    $cat_labels = [];
    foreach ( $profiles as $slug => $profile ) {
        if ( in_array( $slug, [ 'ochel', 'batei-kafe' ], true ) ) continue;
        $cat_labels[ $slug ] = $profile['label'];
    }
    $current_slug = '';
    $cat_name = 'כל המקומות בצפון';
    if ( is_tax( 'gd_placecategory' ) ) {
        $term = get_queried_object();
        if ( $term instanceof WP_Term ) {
            $current_slug = $term->slug;
            $cat_name = $profiles[ $current_slug ]['h1'] ?? $term->name;
            if ( ! isset( $cat_labels[ $current_slug ] ) ) {
                $cat_labels[ $current_slug ] = $term->name;
            }
        }
    }
    $search = get_search_query( false );
    $region = gonorth_place_filter_region();
    $saturday = gonorth_place_filter_saturday();
    echo '<section class="gn-listings-hero">';
    echo '<div class="gn-container">';
    echo '<h1>' . esc_html( $cat_name ) . '</h1>';
    echo '<p>מקומות לטיול ולבילוי בגליל, בגולן, בחרמון ובעמק יזרעאל</p>';
    if ( 'tours' === $current_slug ) {
        echo '<p class="gn-cat__credits">צילום השיט בירדן: <a href="https://commons.wikimedia.org/wiki/File:RAFTING_ON_THE_JORDAN_RIVER.jpg">איתמר גרינברג / Israel Tourism</a>, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC BY-SA 2.0</a>. התמונה הוקטנה ונחתכת בתצוגות השיתוף.</p>';
    }
    echo '<form class="gn-filter-bar" id="gn-archive-search" action="' . esc_url( home_url( '/places/' ) ) . '" method="get" role="search">';
    echo '<label class="gn-visually-hidden" for="gn-cat-select">קטגוריה</label>';
    echo '<select name="category" class="gn-filter-select" id="gn-cat-select">';
    echo '<option value="">כל הקטגוריות</option>';
    foreach ( $cat_labels as $slug => $label ) {
        $sel = selected( $slug, $current_slug, false );
        echo '<option value="' . esc_attr( $slug ) . '"' . $sel . '>' . esc_html( $label ) . '</option>';
    }
    echo '</select>';
    echo '<label class="gn-visually-hidden" for="gn-region-select">אזור</label>';
    echo '<select name="region" class="gn-filter-select" id="gn-region-select">';
    echo '<option value="">כל האזורים</option>';
    foreach ( [ 'galil' => 'גליל', 'golan' => 'גולן', 'carmel' => 'כרמל ועמק יזרעאל' ] as $slug => $label ) {
        echo '<option value="' . esc_attr( $slug ) . '"' . selected( $slug, $region, false ) . '>' . esc_html( $label ) . '</option>';
    }
    echo '</select>';
    echo '<label class="gn-visually-hidden" for="gn-saturday-select">מצב פעילות בשבת</label>';
    echo '<select name="saturday" class="gn-filter-select" id="gn-saturday-select" aria-describedby="gn-filter-help">';
    foreach ( [ '' => 'שבת: כל המצבים', 'yes' => 'פתוח בשבת — מאומת', 'no' => 'לא פתוח בשבת — מאומת', 'unknown' => 'שבת: אין מידע מאומת' ] as $state => $label ) {
        echo '<option value="' . esc_attr( $state ) . '"' . selected( $state, $saturday, false ) . '>' . esc_html( $label ) . '</option>';
    }
    echo '</select>';
    echo '<label class="gn-visually-hidden" for="gn-search-input">חיפוש מקום או עיר</label>';
    echo '<input type="search" name="s" id="gn-search-input" value="' . esc_attr( $search ) . '" placeholder="חיפוש מקום, עיר..." class="gn-filter-input">';
    echo '<button type="submit" class="gn-filter-btn">חפש</button>';
    echo '</form>';
    echo '<p id="gn-filter-help" class="gn-filter-help">הסינון לפי שבת מבדיל בין מידע מאומת לבין מקום שעדיין לא נבדק.</p>';
    global $wp_query;
    $count = $wp_query instanceof WP_Query ? (int) $wp_query->found_posts : 0;
    $count_label = 0 === $count ? 'לא נמצאו מקומות (0)' : ( 1 === $count ? 'נמצא מקום אחד' : 'נמצאו ' . number_format_i18n( $count ) . ' מקומות' );
    $parts = [];
    if ( '' !== $search ) $parts[] = 'חיפוש “' . $search . '”';
    if ( '' !== $current_slug ) $parts[] = 'קטגוריית ' . $cat_labels[ $current_slug ];
    if ( '' !== $region ) $parts[] = 'אזור ' . [ 'galil' => 'גליל', 'golan' => 'גולן', 'carmel' => 'כרמל ועמק יזרעאל' ][ $region ];
    if ( '' !== $saturday ) $parts[] = [ 'yes' => 'פתוח בשבת — מאומת', 'no' => 'לא פתוח בשבת — מאומת', 'unknown' => 'שבת: אין מידע מאומת' ][ $saturday ];
    $summary = $count_label . ( $parts ? ' — ' . implode( ' · ', $parts ) : '' );
    echo '<div class="gn-filter-state"><p class="gn-filter-summary" role="status">' . esc_html( $summary ) . '</p>';
    if ( $parts ) echo '<a class="gn-filter-reset" href="' . esc_url( home_url( '/places/' ) ) . '">נקה סינון</a>';
    echo '</div></div></section>';
    echo '<script>(function(){var cat=document.getElementById("gn-cat-select"),region=document.getElementById("gn-region-select"),sat=document.getElementById("gn-saturday-select"),query=document.getElementById("gn-search-input");function sync(){var match=location.pathname.match(/\/places\/category\/([^\/]+)\//),slug=match?decodeURIComponent(match[1]):"",params=new URLSearchParams(location.search);cat.value=slug;region.value=params.get("region")||"";sat.value=params.get("saturday")||"";query.value=params.get("s")||"";}cat.addEventListener("change",function(){if(this.form){this.form.requestSubmit();}});window.addEventListener("pageshow",sync);window.addEventListener("popstate",sync);})();</script>';
}

// ─── SEO intro block renderer — region pages (page 1 only) ───
function gonorth_region_seo_intro() {
    if ( ! is_tax( 'gn_region' ) || get_query_var( 'paged' ) > 1 ) return;
    $profile = gonorth_current_region_seo_profile();
    if ( ! $profile ) return;

    echo '<section class="gn-seo-intro gn-container" aria-labelledby="gn-region-intro-title">';
    echo '<div class="gn-seo-intro__inner">';
    echo '<h2 class="gn-seo-intro__title" id="gn-region-intro-title">' . esc_html( $profile['intro_title'] ) . '</h2>';
    foreach ( $profile['paragraphs'] as $paragraph ) {
        echo '<p class="gn-seo-intro__text">' . esc_html( $paragraph ) . '</p>';
    }
    echo '<nav class="gn-region-links" aria-label="קטגוריות לטיול באזור">';
    echo '<strong>חפשו לפי סוג הבילוי: </strong>';
    $links = [];
    foreach ( $profile['links'] as $slug => $label ) {
        $links[] = '<a href="' . esc_url( home_url( '/places/category/' . $slug . '/' ) ) . '">' . esc_html( $label ) . '</a>';
    }
    echo wp_kses_post( implode( ' <span aria-hidden="true">·</span> ', $links ) );
    echo '</nav></div></section>';
}

// ─── SEO intro block — taxonomy category pages (page 1 only) ───
add_action( 'astra_content_before', 'gonorth_category_seo_intro', 15 );
function gonorth_category_seo_intro() {
    if ( ! is_tax( 'gd_placecategory' ) ) return;
    if ( get_query_var( 'paged' ) > 1 ) return;

    $term = get_queried_object();
    if ( ! $term ) return;

    $hubs = gonorth_category_hub_profiles();
    if ( empty( $hubs[ $term->slug ] ) ) return;
    $hub = $hubs[ $term->slug ];

    echo '<section class="gn-category-hub gn-container" dir="rtl" aria-labelledby="gn-category-hub-title">';
    echo '<div class="gn-category-hub__inner">';
    echo '<h2 class="gn-category-hub__title" id="gn-category-hub-title">' . esc_html( $hub['title'] ) . '</h2>';
    echo '<div class="gn-category-hub__intro">';
    foreach ( $hub['intro'] as $paragraph ) {
        echo '<p>' . esc_html( $paragraph ) . '</p>';
    }
    echo '</div>';

    echo '<div class="gn-category-hub__columns">';
    echo '<section class="gn-category-hub__panel"><h3>טיפים לתכנון</h3><ul>';
    foreach ( $hub['tips'] as $tip ) {
        echo '<li>' . esc_html( $tip ) . '</li>';
    }
    echo '</ul></section>';

    echo '<section class="gn-category-hub__panel"><h3>המשיכו לתכנן</h3>';
    echo '<div class="gn-category-hub__links">';
    foreach ( $hub['regions'] as $slug => $label ) {
        $region = get_term_by( 'slug', $slug, 'gn_region' );
        if ( ! $region ) continue;
        $url = get_term_link( $region );
        if ( is_wp_error( $url ) ) continue;
        echo '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
    }
    foreach ( $hub['guides'] as $post_id => $label ) {
        if ( 'publish' !== get_post_status( $post_id ) ) continue;
        echo '<a href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html( $label ) . '</a>';
    }
    echo '</div></section></div>';

    $places = new WP_Query( [
        'post_type'              => 'gd_place',
        'post_status'            => 'publish',
        'posts_per_page'         => 3,
        'orderby'                => 'date',
        'order'                  => 'DESC',
        'no_found_rows'          => true,
        'ignore_sticky_posts'    => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'tax_query'              => [ [
            'taxonomy' => 'gd_placecategory',
            'field'    => 'slug',
            'terms'    => $term->slug,
        ] ],
    ] );
    if ( $places->have_posts() ) {
        echo '<section class="gn-category-hub__places"><h3>מקומות שכדאי להכיר</h3><div class="gn-category-hub__links">';
        while ( $places->have_posts() ) {
            $places->the_post();
            echo '<a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a>';
        }
        echo '</div></section>';
        wp_reset_postdata();
    }

    echo '<section class="gn-category-hub__faq" aria-labelledby="gn-category-faq-title">';
    echo '<h2 id="gn-category-faq-title">שאלות נפוצות</h2><div class="gn-category-hub__faq-grid">';
    foreach ( $hub['faqs'] as $faq ) {
        echo '<article class="gn-category-hub__faq-item">';
        echo '<h3>' . esc_html( $faq[0] ) . '</h3>';
        echo '<p>' . esc_html( $faq[1] ) . '</p>';
        echo '</article>';
    }
    echo '</div></section></div></section>';
}

// ─── GeoDirectory single listing hierarchy ───
add_filter( 'astra_the_title_enabled', function( $enabled ) {
    return is_singular( 'gd_place' ) ? false : $enabled;
} );
add_action( 'astra_blog_archive_title_before', function() {
    if ( is_post_type_archive( 'gd_place' ) || is_tax( 'gd_placecategory' ) || is_tax( 'gn_region' ) ) {
        add_filter( 'astra_the_post_title_enabled', '__return_false', 99 );
    }
} );
add_action( 'astra_blog_archive_title_after', function() {
    if ( is_post_type_archive( 'gd_place' ) || is_tax( 'gd_placecategory' ) || is_tax( 'gn_region' ) ) {
        remove_filter( 'astra_the_post_title_enabled', '__return_false', 99 );
    }
} );
add_filter( 'astra_single_post_meta', function( $markup ) {
    return is_singular( 'gd_place' ) ? '' : $markup;
} );
add_filter( 'gonorth_place_attributes_in_content', function( $show, $post_id ) {
    return is_singular( 'gd_place' ) && (int) $post_id === get_queried_object_id() ? false : $show;
}, 10, 2 );
add_filter( 'gonorth_nearby_places_in_content', function( $show, $post_id ) {
    return is_singular( 'gd_place' ) && (int) $post_id === get_queried_object_id() ? false : $show;
}, 10, 2 );

add_action( 'astra_content_before', 'gonorth_gd_single_hero_inject' );
function gonorth_gd_single_hero_inject() {
    if ( ! is_singular( 'gd_place' ) ) return;

    $post_id  = get_queried_object_id();
    $post_obj = get_queried_object();
    if ( ! $post_obj ) return;

    $data       = function_exists( 'gonorth_get_place_data' ) ? gonorth_get_place_data( $post_id ) : [];
    $terms      = get_the_terms( $post_id, 'gd_placecategory' );
    $cat_labels = [
        'teva'            => 'טבע ומסלולים',
        'atraktziot-main' => 'אטרקציות',
        'accommodation'   => 'לינה',
        'restaurants-sub' => 'מסעדות',
        'yakavim'         => 'יקבים',
        'tours'           => 'סיורים ופעילויות',
        'agalot-kafe'     => 'עגלות קפה',
    ];
    $cat_name     = ( $terms && ! is_wp_error( $terms ) ) ? ( $cat_labels[ $terms[0]->slug ] ?? $terms[0]->name ) : '';
    $cat_url      = ( $terms && ! is_wp_error( $terms ) ) ? get_term_link( $terms[0] ) : home_url( '/places/' );
    $city         = $data['city'] ?? '';
    $phone        = $data['phone'] ?? '';
    $website      = $data['website'] ?? '';
    $lat          = $data['latitude'] ?? '';
    $lng          = $data['longitude'] ?? '';
    $has_valid_location = is_numeric( $lat ) && is_numeric( $lng ) && (float) $lat > 29 && (float) $lat < 34 && (float) $lng > 34 && (float) $lng < 36;
    $business_status = (string) get_post_meta( $post_id, '_gonorth_business_status', true );
    $is_closed = in_array( $business_status, [ 'permanently_closed', 'temporarily_closed', 'closed_permanently', 'closed_temporarily' ], true );
    $rating       = $data['google']['rating'] ?? '';
    $review_count = $data['google']['review_count'] ?? '';
    $featured     = get_post_meta( $post_id, 'geodir_featured', true );
    $image_id     = get_post_thumbnail_id( $post_id );
    $hero_image   = gonorth_content_image(
        $image_id,
        'full',
        'תמונה ראשית של ' . $post_obj->post_title,
        'gn-single-hero__media',
        '100vw',
        true
    );
    $chips        = class_exists( 'GoNorth_Place_Attributes' ) ? GoNorth_Place_Attributes::render_chips( $post_id ) : '';

    echo '<section class="gn-single-hero' . ( ! $hero_image ? ' gn-single-hero--no-img' : '' ) . '">';
    echo $hero_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo '<div class="gn-single-hero__overlay"></div><div class="gn-container"><div class="gn-single-hero__content">';
    echo '<nav class="gn-breadcrumb gn-breadcrumb--light" aria-label="פירורי לחם">';
    echo '<a href="' . esc_url( home_url( '/' ) ) . '">בית</a><span>›</span>';
    echo '<a href="' . esc_url( home_url( '/places/' ) ) . '">מקומות</a>';
    if ( $cat_name ) echo '<span>›</span><a href="' . esc_url( $cat_url ) . '">' . esc_html( $cat_name ) . '</a>';
    echo '<span>›</span><span>' . esc_html( $post_obj->post_title ) . '</span></nav>';
    if ( $cat_name ) echo '<span class="gn-single-hero__badge">' . esc_html( $cat_name ) . '</span>';
    if ( $featured ) echo '<span class="gn-single-hero__badge gn-single-hero__badge--featured">מומלץ</span>';
    echo '<h1 class="gn-single-hero__title">' . esc_html( $post_obj->post_title ) . '</h1>';
    if ( $city ) echo '<div class="gn-single-hero__location">' . esc_html( $city ) . '</div>';
    if ( $rating ) echo '<div class="gn-single-hero__rating">' . gonorth_google_rating_html( $rating, $review_count ) . '</div>';
    echo '</div></div></section>';

    echo '<section class="gn-place-overview"><div class="gn-container">';
    if ( $chips ) {
        echo '<div class="gn-place-overview__signals">';
        echo $chips; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '</div>';
    }
    echo '<nav class="gn-place-overview__actions" aria-label="פעולות למקום">';
    if ( ! $is_closed && ( $has_valid_location || $phone ) ) {
        echo '<div class="gn-place-overview__primary">';
        if ( $has_valid_location ) echo '<a href="https://waze.com/ul?ll=' . esc_attr( $lat ) . ',' . esc_attr( $lng ) . '&navigate=yes" target="_blank" rel="noopener" class="gn-single-action gn-single-action--primary">ניווט ב-Waze</a>';
        if ( $phone ) echo '<a href="tel:' . esc_attr( $phone ) . '" class="gn-single-action gn-single-action--primary">התקשרו</a>';
        echo '</div>';
    }
    echo '<div class="gn-place-overview__secondary">';
    if ( ! $is_closed && $website ) echo '<a href="' . esc_url( $website ) . '" target="_blank" rel="noopener" class="gn-single-action">לאתר הרשמי</a>';
    if ( ! $is_closed && $has_valid_location ) echo '<a href="https://www.google.com/maps?q=' . esc_attr( $lat ) . ',' . esc_attr( $lng ) . '" target="_blank" rel="noopener" class="gn-single-action">Google Maps</a>';
    echo '<a href="https://wa.me/?text=' . rawurlencode( $post_obj->post_title . ' — ' . get_permalink( $post_id ) ) . '" target="_blank" rel="noopener" class="gn-single-action">שיתוף</a>';
    echo '</div></nav>';
    if ( $is_closed ) {
        echo '<p class="gn-place-overview__limit">המקום מסומן כסגור. בדקו את הודעת המצב והחלופות בעמוד.</p>';
    } else {
        echo '<nav class="gn-place-overview__shortcuts" aria-label="קיצורי דרך בדף המקום"><a href="#post_content">על המקום</a>';
        if ( $has_valid_location ) echo '<a href="#gn-place-location-title-' . (int) $post_id . '">מיקום ומפה</a>';
        echo '</nav>';
    }
    echo '</div></section>';
}

// ─── GeoDirectory single listing: inject Google photo + rating block once ───
// Use geodir_single_tab_content_after so it fires only once (after post_content tab),
// not the_content filter which fires for every tab and causes duplication.
add_action( 'geodir_single_tab_content_after', function( $tab ) {
    if ( empty( $tab->tab_key ) || $tab->tab_key !== 'post_content' ) return;
    if ( ! is_singular( 'gd_place' ) ) return;
    echo gonorth_gd_google_block_html( get_queried_object_id() );
}, 10 );
function gonorth_gd_google_block_html( $post_id ) {
    $google_place_id  = get_post_meta( $post_id, 'google_place_id', true );
    if ( ! $google_place_id ) return '';
    $gplace           = gonorth_get_google_place_data( $google_place_id );
    if ( ! $gplace )  return '';
    $google_rating    = $gplace['rating'] ?? null;
    $google_total     = $gplace['total'] ?? null;
    $google_photo_url = get_post_meta( $post_id, 'google_photo_url', true );
    $maps_url = 'https://www.google.com/maps/place/?q=place_id:' . esc_attr( $google_place_id );

    // Don't repeat the photo if it's already used as the hero background
    $has_manual_thumb = (bool) get_the_post_thumbnail_url( $post_id, 'gonorth-hero' );
    $show_photo       = $google_photo_url && $has_manual_thumb;

    $out = '<div class="gn-google-block">';
    if ( $show_photo ) {
        $out .= '<img src="' . esc_url($google_photo_url) . '" alt="' . esc_attr( get_the_title($post_id) ) . '" class="gn-google-block__photo" loading="lazy" />';
    }
    $out .= '<div class="gn-google-block__footer">';
    if ( $google_rating ) {
        $out .= gonorth_google_rating_html( $google_rating, $google_total );
    }
    $out .= '<a href="' . esc_url($maps_url) . '" target="_blank" rel="noopener" class="gn-google-block__link">כל הביקורות בגוגל</a>';
    $out .= '</div></div>';
    return $out;
}

// ─── GeoDirectory single listing: location, map and nearby discovery ───
add_action( 'geodir_single_tab_content_after', 'gonorth_gd_discovery_sections', 20 );
function gonorth_gd_discovery_sections( $tab ) {
    if ( empty( $tab->tab_key ) || 'post_content' !== $tab->tab_key || ! is_singular( 'gd_place' ) ) return;

    $post_id = get_queried_object_id();
    $data    = function_exists( 'gonorth_get_place_data' ) ? gonorth_get_place_data( $post_id ) : [];
    $address = $data['address'] ?? '';
    $city    = $data['city'] ?? '';
    $lat     = $data['latitude'] ?? '';
    $lng     = $data['longitude'] ?? '';

    if ( $address || $city || ( $lat && $lng ) ) {
        $location = class_exists( 'GoNorth_Place_Data' )
            ? GoNorth_Place_Data::format_location( $address, $city )
            : implode( ', ', array_filter( [ $address, $city ] ) );
        echo '<section class="gn-place-location" aria-labelledby="gn-place-location-title-' . esc_attr( $post_id ) . '">';
        echo '<div class="gn-place-section-heading"><p>מגיעים בקלות</p><h2 id="gn-place-location-title-' . esc_attr( $post_id ) . '">מיקום וניווט</h2></div>';
        if ( $location ) echo '<p class="gn-place-location__address">' . esc_html( $location ) . '</p>';
        if ( $lat && $lng ) {
            $lat_float = (float) $lat;
            $lng_float = (float) $lng;
            $bbox = implode( ',', [ $lng_float - 0.02, $lat_float - 0.015, $lng_float + 0.02, $lat_float + 0.015 ] );
            $map_url = add_query_arg( [
                'bbox'   => $bbox,
                'layer'  => 'mapnik',
                'marker' => $lat_float . ',' . $lng_float,
            ], 'https://www.openstreetmap.org/export/embed.html' );
            echo '<div class="gn-place-location__map">';
            echo '<iframe src="' . esc_url( $map_url ) . '" title="מפת המיקום של ' . esc_attr( get_the_title( $post_id ) ) . '" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>';
            echo '</div>';
        }
        echo '</section>';
    }

    if ( class_exists( 'GoNorth_Nearby_Places' ) ) {
        echo GoNorth_Nearby_Places::render_nearby_places( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}

// ─── GeoDirectory: translate English strings to Hebrew ───
add_filter( 'gettext', 'gonorth_gd_translate', 20, 3 );
function gonorth_gd_translate( $translated, $text, $domain ) {
    $map = [
        'Profile'                                    => 'פרופיל',
        'Reviews'                                    => 'ביקורות',
        'Leave a Review'                             => 'כתוב ביקורת',
        'Leave a Comment'                            => 'כתוב תגובה',
        'Favorite'                                   => 'מועדפים',
        'New'                                        => 'חדש',
        'Near'                                       => 'קרוב ל',
        'All Places'                                 => 'כל המקומות',
        'View: Grid 1'                               => 'רשת',
        'View: Grid 2'                               => 'רשת רחבה',
        'View: Grid 3'                               => 'רשת צפופה',
        'View: Grid 4'                               => 'כרטיסים',
        'View: Grid 5'                               => 'תצוגה חלופית',
        'View: List'                                 => 'רשימה',
        'Read more'                                  => 'קרא עוד',
        'Your email address will not be published.'  => 'הכתובת שלך לא תפורסם.',
        'Enter your review comments here (required)...' => 'כתוב את הביקורת שלך כאן...',
        'Submit'                                     => 'שלח ביקורת',
        'Search for'                                 => 'חיפוש',
        'Search'                                     => 'חיפוש',
        'You searched for'                           => 'חיפשש7�',
        'Loading...'                                 => 'טוען...',
        'Loading…'                                   => 'טוען...',
        'No Records Found'                           => 'לא נǞצאו תוצאות',
        'Sorry, no records were found. Please adjust your search criteria and try again.' => 'לא נמצאו תוצאות. נסו לעדכן את תנאי החיפוש.',
        'Maps failed to load'                        => 'המפה לא נטענה',
        'Sorry, unable to load the Maps API.'        => 'לא ניתן לטעון את המפה כרגע.',
        'View all'                                   => 'לכל המקומות',
        'No listings were found matching your selection.' => 'לא נמצאו מקומות התואמים לסינון שבחרתם.',
        'Something missing? Why not add a listing?.' => 'חסר מקום? אפשר לשלוח לנו פרטים להוספה.',
        'Previous' => '› הקודם',
        'Next'     => 'הבא ‹',
    ];
    return $map[ $text ] ?? $translated;
}

add_filter( 'geodir_no_listings_found_message', 'gonorth_no_listings_message', 10, 2 );
function gonorth_no_listings_message( $message, $post_type ) {
    $add_url = home_url( '/add-listing-2/' );
    return '😕 לא מצאנו מקומות התואמים לחיפוש שלך.<br><small>נסו מילה אחרת, או <a href="' . esc_url( home_url('/places/') ) . '">עיינו בכל המקומות</a>.</small>';
}

// ─── Redirect internal GeoDirectory system pages ───
add_action( 'init', 'gonorth_redirect_system_pages', 1 );
function gonorth_redirect_system_pages() {
    if ( is_admin() || wp_doing_ajax() ) return;
    $path = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ), '/' );
    if ( in_array( $path, [ 'search', 'location', 'gd-archive', 'gd-archive-item', 'gd-details' ], true ) ) {
        wp_safe_redirect( home_url( '/places/' ), 301 );
        exit;
    }
}

// ─── SEO: redirect the legacy static sitemap to Yoast's canonical index ───
add_action( 'template_redirect', 'gonorth_redirect_legacy_sitemap', 0 );
function gonorth_redirect_legacy_sitemap() {
    $path = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ), '/' );
    if ( 'sitemap.xml' !== $path ) return;

    wp_safe_redirect( home_url( '/sitemap_index.xml' ), 301, 'GoNorth' );
    exit;
}

// ─── Replace leftover frontend strings from plugins ───
add_action( 'template_redirect', 'gonorth_frontend_string_cleanup', 0 );
function gonorth_frontend_string_cleanup() {
    if ( is_admin() || wp_doing_ajax() || is_feed() ) return;
    ob_start( function( $html ) {
        return strtr( $html, [
            'All Places' => 'כל המקומות',
            'View: Grid 1' => 'רשת',
            'View: Grid 2' => 'רשת רחבה',
            'View: Grid 3' => 'רשת צפופה',
            'View: Grid 4' => 'כרטיסים',
            'View: Grid 5' => 'תצוגה חלופית',
            'View: List' => 'רשימה',
            'Loading…' => 'טוען...',
            'Loading...' => 'טוען...',
            'No Records Found' => 'לא נמצאו תוצאות',
            'Sorry, no records were found. Please adjust your search criteria and try again.' => 'לא נמצאו תוצאות. נסו לעדכן את תנאי החיפוש.',
            'Maps failed to load' => 'המפה לא נטענה',
            'Sorry, unable to load the Maps API.' => 'לא ניתן לטעון את המפה כרגע.',
            'View all' => 'לכל המקומות',
            'You searched for' => 'חיפשתם',
            'Search {' => 'חיפוש {',
        ] );
    } );
}

// ─── Blog home: inject GoNorth hero ───
add_action( 'astra_content_before', 'gonorth_blog_hero_inject' );
function gonorth_blog_hero_inject() {
    if ( ! is_home() ) return;
    echo '<section class="gn-blog-hero">';
    echo '<div class="gn-container">';
    echo '<h1>הבלוג של GoNorth</h1>';
    echo '<p>מדריכים, כתבות וטיפים לטיול בצפון ישראל</p>';
    echo '</div></section>';
}

// ─── Optional measurement consent ─────────────────────────────────────────
add_action( 'wp_footer', 'gonorth_cookie_banner' );
function gonorth_cookie_banner() {
    $privacy_url = esc_url( home_url( '/mediniyut-pratiyut/' ) );
    echo <<<HTML
<button type="button" id="gn-cookie-settings" class="gn-cookie-settings" aria-controls="gn-cookie-banner" aria-expanded="false">העדפות פרטיות</button>
<div id="gn-cookie-banner" class="gn-cookie-banner" role="dialog" aria-modal="false" aria-labelledby="gn-cookie-title" dir="rtl">
  <div class="gn-cookie-copy">
    <h2 id="gn-cookie-title" tabindex="-1">בחירת מדידה באתר</h2>
    <p>קובצי עוגיות חיוניים מאפשרים את פעולת האתר. רק אם תאשרו נטען Google Analytics למדידת שימוש ואת רכיב Google Preferred Sources. אפשר לסרב ולשנות בחירה בכל עת. <a href="{$privacy_url}">מדיניות פרטיות</a></p>
  </div>
  <div class="gn-cookie-actions">
    <button type="button" id="gn-cookie-accept" class="gn-cookie-accept">מאשר/ת מדידה</button>
    <button type="button" id="gn-cookie-reject" class="gn-cookie-reject">ללא מדידה</button>
  </div>
</div>
<script data-noptimize="1">
(function(){
  var b=document.getElementById('gn-cookie-banner');
  var settings=document.getElementById('gn-cookie-settings');
  if(!b||!settings)return;
  function choice(){try{return localStorage.getItem('gn_analytics_consent_v2');}catch(e){return null;}}
  function show(){b.style.display='flex';settings.setAttribute('aria-expanded','true');document.getElementById('gn-cookie-title').focus();}
  function hide(){b.style.display='none';settings.setAttribute('aria-expanded','false');settings.focus();}
  function clearAnalyticsCookies(){
    document.cookie.split(';').forEach(function(part){
      var name=part.split('=')[0].trim();
      if(!/^(_ga(?:_|$)|_gid$|_gat(?:_|$))/.test(name))return;
      document.cookie=name+'=; Max-Age=0; path=/';
      document.cookie=name+'=; Max-Age=0; path=/; domain=.gonorth.co.il';
    });
  }
  if(choice()!=='1'&&choice()!=='0')show();
  settings.addEventListener('click',show);
  document.getElementById('gn-cookie-accept').addEventListener('click',function(){
    try{localStorage.setItem('gn_analytics_consent_v2','1');localStorage.removeItem('gn_cookie_ok');}catch(e){return;}
    hide();window.dispatchEvent(new Event('gn-consent-granted'));
  });
  document.getElementById('gn-cookie-reject').addEventListener('click',function(){
    try{localStorage.setItem('gn_analytics_consent_v2','0');localStorage.removeItem('gn_cookie_ok');}catch(e){return;}
    clearAnalyticsCookies();hide();window.location.reload();
  });
})();
</script>
HTML;
}

// ─── Google Places API Integration ───────────────────────────────────────────

// Force IPv4 for Google API calls (server has IPv6 issues with google)
add_action( 'http_api_curl', function( $handle, $parsed_args, $url ) {
    if ( strpos( $url, 'maps.googleapis.com' ) !== false ) {
        curl_setopt( $handle, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
    }
}, 10, 3 );

/**
 * Fetch Google Place data (rating, reviews count, first photo reference)
 * Cached for 24 hours via WP transients.
 */
function gonorth_get_google_place_data( $place_id ) {
    if ( ! $place_id ) return null;

    $cache_key = 'gn_gplace_' . md5( $place_id );
    $cached    = get_transient( $cache_key );
    if ( $cached !== false ) return $cached;

    $api_key = get_option( 'gonorth_google_api_key' );
    if ( ! $api_key ) return null;

    $url = 'https://maps.googleapis.com/maps/api/place/details/json?' . http_build_query( [
        'place_id' => $place_id,
        'fields'   => 'rating,user_ratings_total,photos',
        'language' => 'he',
        'key'      => $api_key,
    ] );

    $response = wp_remote_get( $url, [ 'timeout' => 8 ] );
    if ( is_wp_error( $response ) ) return null;

    $body = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( empty( $body['result'] ) ) return null;

    $data = [
        'rating'    => $body['result']['rating'] ?? null,
        'total'     => $body['result']['user_ratings_total'] ?? null,
        'photo_ref' => $body['result']['photos'][0]['photo_reference'] ?? null,
    ];

    set_transient( $cache_key, $data, DAY_IN_SECONDS );
    return $data;
}

/**
 * Build a Google Places photo URL.
 */
function gonorth_google_photo_url( $photo_reference, $maxwidth = 900 ) {
    $api_key = get_option( 'gonorth_google_api_key' );
    return 'https://maps.googleapis.com/maps/api/place/photo?' . http_build_query( [
        'maxwidth'        => $maxwidth,
        'photo_reference' => $photo_reference,
        'key'             => $api_key,
    ] );
}

/**
 * Render a Google rating badge HTML.
 */
function gonorth_google_rating_html( $rating, $total ) {
    if ( ! $rating ) return '';
    $stars = round( $rating * 2 ) / 2;
    $full  = floor( $stars );
    $half  = ( $stars - $full >= 0.5 ) ? 1 : 0;
    $empty = 5 - $full - $half;

    $stars_html  = str_repeat( '<span class="gn-star gn-star--full">★</span>', $full );
    $stars_html .= $half ? '<span class="gn-star gn-star--half">★</span>' : '';
    $stars_html .= str_repeat( '<span class="gn-star gn-star--empty">☆</span>', $empty );

    $total_fmt = $total ? number_format( $total ) : '';

    return '<div class="gn-google-rating">
        <span class="gn-google-rating__logo">G</span>
        <div class="gn-google-rating__stars">' . $stars_html . '</div>
        <span class="gn-google-rating__score">' . esc_html( $rating ) . '</span>
        ' . ( $total_fmt ? '<span class="gn-google-rating__total">(' . esc_html( $total_fmt ) . ' ביקורות)</span>' : '' ) . '
    </div>';
}

// ─── Remove Astra's English post navigation on GeoDirectory listing pages ───
add_action( 'template_redirect', function() {
    if ( is_singular( 'gd_place' ) ) {
        remove_action( 'astra_entry_after', 'astra_single_post_navigation_markup' );
    }
} );

// ─── GeoDirectory: תרגום שדות טופס הגשת מקום לעברית ───
add_filter( 'gettext', 'gonorth_geodir_translate', 20, 3 );
add_filter( 'gettext_with_context', 'gonorth_geodir_translate_ctx', 20, 4 );

function gonorth_geodir_translate( $translated, $original, $domain ) {
    $map = [
        'Place Title'        => 'שם המקום',
        'Place Description'  => 'תיאור המקום',
        'Category'           => 'קטגוריה',
        'Default Category'   => 'קטגוריה',
        'Tags'               => 'תגיות',
        'Address'            => 'כתובת',
        'City'               => 'עיר',
        'Region'             => 'אזור',
        'Country'            => 'מדינה',
        'Zip/Post Code'      => 'מיקוד',
        'Address Latitude'   => 'קו רוחב',
        'Address Longitude'  => 'קו אורך',
        'Select Map View'    => 'בחירת מיקום במפה',
        'Images'             => 'תמונות',
        'Name'               => 'שם',
        'Email'              => 'אימייל',
        'Submit Listing'     => 'שלח מקום',
        'Add Listing'        => 'הוסף מקום',
        'Add Place'          => 'הוסף מקום',
        'Preview'            => 'תצוגה מקדימה',
        'Save'               => 'שמור',
        'Required'           => 'שדה חובה',
        'Login'              => 'התחברות',
        'Register'           => 'הרשמה',
        'Search address'     => 'חפש כתובת',
        'Set Address On Map' => 'סמן מיקום במפה',
        'Select a category'  => 'בחר קטגוריה',
        'Add tags'           => 'הוסף תגיות',
        'Submit'             => 'שלח',
    ];
    return $map[ $original ] ?? $translated;
}

function gonorth_geodir_translate_ctx( $translated, $original, $context, $domain ) {
    return gonorth_geodir_translate( $translated, $original, $domain );
}


// ─── GeoDirectory: תרגום כותרות סעיפים בטופס הגשה ───
add_filter( 'geodir_add_listing_tab_title', 'gonorth_geodir_tab_title', 10, 1 );
function gonorth_geodir_tab_title( $title ) {
    $map = [
        'Your Details'          => 'הפרטים שלך',
        'Enter Listing Details' => 'פרטי המקום',
        'Set Image Texts'       => 'תיאורי תמונות',
        'Images'                => 'תמונות',
        'Map'                   => 'מפה',
        'Location'              => 'מיקום',
    ];
    return $map[ $title ] ?? $title;
}

// ─── GeoDirectory: תרגום נוסף של מחרוזות ───
add_filter( 'gettext', 'gonorth_geodir_translate_extra', 25, 3 );
function gonorth_geodir_translate_extra( $translated, $original, $domain ) {
    $map = [
        'Your Details'            => 'הפרטים שלך',
        'Enter Listing Details'   => 'פרטי המקום',
        'Set Image Texts'         => 'תיאורי תמונות',
        'Location'                => 'מיקום',
        'Map'                     => 'מפה',
        'Next'                    => 'הבא',
        'Back'                    => 'חזור',
        'Finish'                  => 'סיים',
        'Save Changes'            => 'שמור שינויים',
        'Continue'                => 'המשך',
        'Select country'          => 'בחר מדינה',
        'Type to search'          => 'הקלד לחיפוש',
        'No results found'        => 'לא נמצאו תוצאות',
        'Enter your address'      => 'הזן כתובת',
        'Enter address'           => 'הזן כתובת',
        'Set Address On Map'      => 'סמן מיקום במפה',
        'Latitude'                => 'קו רוחב',
        'Longitude'               => 'קו אורך',
        'Upload Image'            => 'העלה תמונה',
        'Add Image'               => 'הוסף תמונה',
        'Remove Image'            => 'הסר תמונה',
        'Caption'                 => 'כיתוב',
        'This field is required.' => 'שדה חובה.',
        'Please enter a valid email address.' => 'יש להזין כתובת אימייל תקינה.',
        'Submit Listing'          => 'שלח מקום',
        'Drop files here'         => 'גרור קבצים לכאן',
        'Select Files'            => 'בחר קבצים',
        'Preview'                 => 'תצוגה מקדימה',
        'Submit'                  => 'שלח',
    ];
    return $map[ $original ] ?? $translated;
}

// ─── GeoDirectory: ישראל כברירת מחדל + RTL fixes בדף הגשת מקום ───
add_action( 'wp_head', 'gonorth_add_listing_fixes' );
function gonorth_add_listing_fixes() {
    if ( ! is_page( 15 ) && strpos( $_SERVER['REQUEST_URI'] ?? '', 'add-listing' ) === false ) return;
    echo '<style>
    .gd-add-listing .col-sm-2.col-form-label { text-align: right !important; }
    .gd-add-listing .col-sm-10 { text-align: right !important; }
    .gd-add-listing .form-group, .gd-add-listing .geodir-field { direction: rtl; }
    .gd-add-listing .geodir-progress-bar { direction: ltr; }
    .gd-add-listing .nav-tabs { direction: rtl; }
    select[name="address_country"] + span { display: none !important; }
    </style>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var sel = document.querySelector("select[name=\'address_country\']");
        if (sel) {
            sel.value = "IL";
            sel.dispatchEvent(new Event("change"));
            // hide the select2 widget if present
            var parent = sel.closest(".geodir-field");
            if (parent) {
                var s2 = parent.querySelector(".select2-container");
                if (s2) s2.style.display = "none";
                var lbl = parent.querySelector(".col-sm-10");
                if (lbl) {
                    var fixed = document.createElement("div");
                    fixed.className = "form-control";
                    fixed.style.cssText = "background:#f8f9fa;color:#6c757d;cursor:default;";
                    fixed.textContent = "ישראל";
                    lbl.insertBefore(fixed, lbl.firstChild);
                }
            }
        }
    });
    </script>';
}

// ─── GeoDirectory: תרגום JS-rendered strings בדף הגשת מקום ───
add_action( 'wp_footer', 'gonorth_add_listing_js_translations' );
function gonorth_add_listing_js_translations() {
    if ( strpos( $_SERVER['REQUEST_URI'] ?? '', 'add-listing' ) === false ) return;
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var translations = {
            'Enter Listing Details': 'פרטי המקום',
            'Your Details':          'הפרטים שלך',
            'Set Image Texts':       'תיאורי תמונות',
            'Preview Listing':       'תצוגה מקדימה',
            'Submit Listing':        'שלח מקום',
            'Save Listing':          'שמור מקום',
            'Next':                  'הבא',
            'Back':                  'חזור',
            'Finish':                'סיים',
            'Continue':              'המשך',
            'Select country':        'בחר מדינה',
            'Add Listing':           'הוסף מקום',
            'Images':                'תמונות',
            'Location':              'מיקום',
            'Map':                   'מפה',
        };

        function translateNode(node) {
            if (node.nodeType === Node.TEXT_NODE) {
                var text = node.textContent.trim();
                if (translations[text]) {
                    node.textContent = node.textContent.replace(text, translations[text]);
                }
            }
        }

        function translateAll() {
            // Translate tab labels
            document.querySelectorAll('.nav-link, .tab-title, .wizard-title, .step-title, .geodir-tab-title').forEach(function(el) {
                var text = el.textContent.trim();
                if (translations[text]) el.textContent = translations[text];
            });

            // Translate buttons
            document.querySelectorAll('button, input[type="submit"], a.btn, .btn').forEach(function(el) {
                var text = el.textContent.trim();
                if (translations[text]) {
                    if (el.tagName === 'INPUT') el.value = translations[text];
                    else el.childNodes.forEach(function(n) {
                        if (n.nodeType === Node.TEXT_NODE && translations[n.textContent.trim()]) {
                            n.textContent = translations[n.textContent.trim()];
                        }
                    });
                }
            });

            // Translate headings in form
            document.querySelectorAll('h1,h2,h3,h4,h5,legend,label').forEach(function(el) {
                var text = el.textContent.trim();
                if (translations[text]) el.textContent = translations[text];
            });

            // Set Israel as default country
            var sel = document.querySelector("select[name='address_country']");
            if (sel && sel.value !== 'IL') {
                sel.value = 'IL';
                sel.dispatchEvent(new Event('change', {bubbles: true}));
            }
        }

        // Run immediately and observe for dynamic DOM changes
        translateAll();
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(m) {
                if (m.addedNodes.length) translateAll();
            });
        });
        observer.observe(document.body, { childList: true, subtree: true });
    });
    </script>
    <?php
}

// ─── Disable comments on all gd_place listings globally ───
add_filter( 'comments_open', function( $open, $post_id ) {
    if ( get_post_type( $post_id ) === 'gd_place' ) return false;
    return $open;
}, 10, 2 );
add_filter( 'pings_open', function( $open, $post_id ) {
    if ( get_post_type( $post_id ) === 'gd_place' ) return false;
    return $open;
}, 10, 2 );

// ─── Remove GeoDirectory tab navigation on single listing pages ───
add_filter( 'geodir_tab_head_output', '__return_empty_string', 99 );
add_filter( 'geodir_details_tabs_nav', '__return_empty_string', 99 );
add_filter( 'geodir_single_tab_nav_html', '__return_empty_string', 99 );


// ─── GeoDirectory single listing: DOM cleanup via JS ───
add_action( 'wp_footer', 'gonorth_gd_single_dom_cleanup' );
function gonorth_gd_single_dom_cleanup() {
    if ( ! is_singular( 'gd_place' ) ) return;
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Remove פרופיל heading and its HR completely from DOM
        document.querySelectorAll('h2.gd-tab-list-title, .gd-tab-list-title').forEach(function(el) {
            var next = el.nextElementSibling;
            if (next && next.tagName === 'HR') next.remove();
            el.remove();
        });

        // Remove "Place Categories:" text node, keep only badge links
        document.querySelectorAll('.geodir-category').forEach(function(span) {
            span.childNodes.forEach(function(node) {
                if (node.nodeType === Node.TEXT_NODE) node.remove();
            });
        });

        // Style category badges with GoNorth green
        document.querySelectorAll('.geodir_post_taxomomies .badge').forEach(function(badge) {
            badge.style.cssText = 'background:#2D6A4F !important;color:#fff !important;font-size:0.82rem !important;font-family:Heebo,sans-serif !important;font-weight:500 !important;padding:5px 14px !important;border-radius:20px !important;margin:0 0 0 6px !important;text-decoration:none !important;display:inline-block !important;';
            badge.classList.remove('text-bg-dark');
        });

        // Style the categories container
        var taxo = document.querySelector('.geodir_post_taxomomies');
        if (taxo) {
            taxo.style.cssText = 'margin:16px 0 8px 0;direction:rtl;text-align:right;';
        }

        // Move categories block to after the main content
        var cats = document.querySelector('.geodir_post_taxomomies');
        var mainContent = document.querySelector('.geodir-post-meta-container');
        if (cats && mainContent) {
            mainContent.parentNode.insertBefore(cats, mainContent.nextSibling);
        }
    });
    </script>
    <?php
}


// ─── Region Taxonomy (אזור) ───────────────────────────────────────────────────
add_action( 'init', 'gonorth_register_region_taxonomy' );
function gonorth_register_region_taxonomy() {
    register_taxonomy( 'gn_region', 'gd_place', [
        'label'             => 'אזור',
        'labels'            => [
            'name'              => 'אזורים',
            'singular_name'     => 'אזור',
            'menu_name'         => 'אזורים',
            'all_items'         => 'כל האזורים',
            'edit_item'         => 'ערוך אזור',
            'view_item'         => 'צפה באזור',
            'update_item'       => 'עדכן אזור',
            'add_new_item'      => 'הוסף אזור חדש',
            'new_item_name'     => 'שם אזור חדש',
            'search_items'      => 'חפש אזורים',
            'not_found'         => 'לא נמצאו אזורים',
        ],
        'public'            => true,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_admin_column' => true,
        'hierarchical'      => false,
        'rewrite'           => [ 'slug' => 'region', 'with_front' => false ],
        'show_in_rest'      => true,
    ] );
}


// ─── Preconnect + preload critical font ──────────────────────────────────────
add_action( 'wp_head', 'gonorth_preconnect_hints', 1 );
function gonorth_preconnect_hints() {
    if ( is_front_page() ) {
        return;
    }

    echo '<link rel="preconnect" href="https://maps.googleapis.com">' . "\n";
}

// ─── Hebrew UI translations via gettext filter ────────────────────────────────
add_filter( 'gettext', 'gn_translate_strings', 20, 3 );
function gn_translate_strings( $translated, $original, $domain ) {
    $translations = [
        // Navigation / Pagination
        'Previous'        => 'הקודם',
        'Next'            => 'הבא',
        'Older posts'     => 'פוסטים ישנים יותר',
        'Newer posts'     => 'פוסטים חדשים יותר',
        'Page'            => 'עמוד',
        'of'              => 'מתוך',

        // Search
        'Search'          => 'חיפוש',
        'Search for:'     => 'חפש:',
        'No results found' => 'לא נמצאו תוצאות',
        'Nothing Found'   => 'לא נמצא',
        'Sorry, but nothing matched your search terms.' => 'מצטערים, לא נמצאו תוצאות לחיפוש שלך.',

        // Comments / Forms
        'Leave a Reply'   => 'השאר תגובה',
        'Comment'         => 'תגובה',
        'Name'            => 'שם',
        'Email'           => 'אימייל',
        'Website'         => 'אתר אינטרנט',
        'Submit'          => 'שלח',
        'Post Comment'    => 'פרסם תגובה',
        'Your email address will not be published.' => 'כתובת האימייל שלך לא תפורסם.',
        'Required fields are marked' => 'שדות חובה מסומנים',

        // GeoDirectory strings
        'Add Listing'     => 'הוסף מקום',
        'Edit Listing'    => 'ערוך מקום',
        'Filter'          => 'סינון',
        'Sort By'         => 'מיין לפי',
        'Rating'          => 'דירוג',
        'Distance'        => 'מרחק',
        'Newest'          => 'חדשים ביותר',
        'Featured'        => 'מומלצים',
        'No listings found.' => 'לא נמצאו מקומות.',
        'View on Map'     => 'הצג במפה',
        'Get Directions'  => 'נווט',
        'Write a Review'  => 'כתוב ביקורת',
        'Reviews'         => 'ביקורות',
        'Address'         => 'כתובת',
        'Phone'           => 'טלפון',
        'Hours'           => 'שעות פתיחה',

        // Errors / Notices
        'Error'           => 'שגיאה',
        'Required'        => 'שדה חובה',
        'Invalid email'   => 'אימייל לא תקין',
        'Please fill in all required fields.' => 'אנא מלא את כל שדות החובה.',
    ];

    return $translations[ $original ] ?? $translated;
}

// Analytics is loaded once through the GTM container in header.php.

// ─── SEO issue #73: consolidate true duplicate editorial hubs ───
add_action( 'template_redirect', 'gonorth_issue_73_content_redirects', -1 );
function gonorth_issue_73_content_redirects() {
    if ( is_admin() || wp_doing_ajax() || is_feed() ) return;

    $path = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ), '/' );
    $redirects = [
        'north-israel-wineries-guide'   => '/yekavim-batzafon/',
        'north-israel-wineries-guide-2' => '/yekavim-batzafon/',
        'wineries-galilee-golan-guide'  => '/yekavim-batzafon/',
        'misadot-mumlatzot-batzafon'    => '/misadot-mumlatzot-tzafon-israel/',
        'north-israel-family-activities-guide' => '/atrakziot-leyeladim-batzafon/',
        'attractions-for-kids-north-israel'    => '/atrakziot-leyeladim-batzafon/',
        'water-trails-north-israel-summer'     => '/maslulei-mayim-batzafon/',
        'water-trails-north-israel'            => '/maslulei-mayim-batzafon/',
        'מדריך-לינה-ברמת-הגולן-מלונות-צימרים-וק' => '/accommodation-ramat-hagolan-guide/',
        'accommodation-golan-heights'           => '/accommodation-ramat-hagolan-guide/',
        'springs-valley-guide'                  => '/valley-of-springs-guide/',
        'shavuot-2025-north-israel'             => '/shavuot-north-israel/',
    ];

    $decoded_path = rawurldecode( $path );
    $destination = $redirects[ $path ] ?? $redirects[ $decoded_path ] ?? '';
    if ( $destination ) {
        wp_safe_redirect( home_url( $destination ), 301, 'GoNorth' );
        exit;
    }
}

// Keep automatic previous/next navigation away from URLs consolidated in issue #73.
add_filter( 'get_previous_post_where', 'gonorth_issue_73_skip_consolidated_adjacent_posts' );
add_filter( 'get_next_post_where', 'gonorth_issue_73_skip_consolidated_adjacent_posts' );
function gonorth_issue_73_skip_consolidated_adjacent_posts( $where ) {
    global $wpdb;
    $excluded_ids = [ 84, 123, 127, 135, 144, 251, 252, 256, 257, 258, 267 ];
    return $where . ' AND p.ID NOT IN (' . implode( ',', array_map( 'absint', $excluded_ids ) ) . ')';
}
