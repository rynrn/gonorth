<?php
/**
 * GoNorth Homepage Template
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<!-- ══════════════════════════════════
     HERO SECTION
══════════════════════════════════ -->
<section class="gn-hero">
  <div class="gn-hero__bg"></div>
  <div class="gn-hero__mountains"></div>
  <div class="gn-hero__content">
    <div class="gn-hero__badge">המדריך המוביל לצפון ישראל</div>
    <h1 class="gn-hero__title">גלה את <span>הצפון</span><br>כמו מקומי</h1>
    <p class="gn-hero__subtitle">מאות אטרקציות, מסעדות, לינה וסיורים בגליל, גולן וחרמון — הכל במקום אחד.</p>

    <form class="gn-hero__search" action="<?php echo esc_url( home_url('/places/') ); ?>" method="GET">
      <label class="gn-visually-hidden" for="gn-home-category">קטגוריה</label>
      <select id="gn-home-category" name="category" class="gn-search__cat">
        <option value="">כל הקטגוריות</option>
        <option value="teva">טבע ומסלולים</option>
        <option value="atraktziot-main">אטרקציות</option>
        <option value="accommodation">לינה</option>
        <option value="restaurants-sub">מסעדות</option>
        <option value="yakavim">יקבים</option>
        <option value="tours">סיורים ופעילויות</option>
        <option value="agalot-kafe">עגלות קפה</option>
      </select>
      <label class="gn-visually-hidden" for="gn-home-search">חיפוש מקום, עיר או פעילות</label>
      <input id="gn-home-search" type="search" name="s" placeholder="חפש מקום, עיר, פעילות..." class="gn-search__input" />
      <button type="submit" class="gn-search__btn">חפש</button>
    </form>

    <div class="gn-hero__stats">
      <?php
      $count = wp_count_posts('gd_place')->publish ?? 0;
      ?>
      <div class="gn-hero__stat"><strong><?php echo $count > 0 ? $count . '+' : '200+'; ?></strong>מקומות רשומים</div>
      <div class="gn-hero__stat"><strong>4</strong>אזורים</div>
      <div class="gn-hero__stat"><strong>חינם</strong>לחלוטין</div>
    </div>
  </div>
</section>

<!-- Direct answer for visitors and search engines -->
<section class="gn-seo-intro gn-container" aria-labelledby="gn-home-intro-title">
  <div class="gn-seo-intro__inner">
    <h2 class="gn-seo-intro__title" id="gn-home-intro-title">מה תמצאו בכיוון צפון?</h2>
    <p class="gn-seo-intro__text"><?php $homepage_seo = gonorth_homepage_seo_profile(); echo esc_html( $homepage_seo['answer'] ); ?></p>
  </div>
</section>

<!-- ══════════════════════════════════
     CATEGORIES
══════════════════════════════════ -->
<section class="gn-section gn-categories">
  <div class="gn-container">
    <div class="gn-section__header">
      <h2 class="gn-section__title">גלה לפי <span>קטגוריה</span></h2>
      <a href="<?php echo home_url('/places/'); ?>" class="gn-section__link">כל הקטגוריות →</a>
    </div>
    <div class="gn-cats__grid">
      <?php
      $cats = [
        ['slug' => 'teva',           'name' => 'טבע ומסלולים',     'image_id' => 59],
        ['slug' => 'atraktziot-main','name' => 'אטרקציות',          'image_id' => 55],
        ['slug' => 'accommodation',  'name' => 'לינה',              'image_id' => 64],
        ['slug' => 'restaurants-sub','name' => 'מסעדות',            'image_id' => 525],
        ['slug' => 'yakavim',        'name' => 'יקבים',             'image_id' => 49],
        ['slug' => 'tours',          'name' => 'סיורים ופעילויות', 'image_id' => 526],
        ['slug' => 'agalot-kafe',    'name' => 'עגלות קפה',         'image_id' => 461],
      ];
      foreach ( $cats as $cat ) :
        $term  = get_term_by( 'slug', $cat['slug'], 'gd_placecategory' );
        $count = $term ? $term->count : 0;
        $url   = get_term_link( $cat['slug'], 'gd_placecategory' );
        if ( is_wp_error( $url ) ) {
          $url = home_url( '/places/' );
        }
      ?>
      <a href="<?php echo esc_url($url); ?>" class="gn-cat__card gn-cat__card--<?php echo esc_attr($cat['slug']); ?>">
        <div class="gn-cat__bg">
          <?php
          echo gonorth_content_image(
            $cat['image_id'],
            'medium_large',
            'תמונה המייצגת ' . $cat['name'] . ' בצפון',
            'gn-cat__media',
            '(max-width: 767px) 100vw, (max-width: 991px) 50vw, 33vw'
          ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
          ?>
        </div>
        <div class="gn-cat__overlay">
          <h3><?php echo esc_html($cat['name']); ?></h3>
          <span><?php echo $count > 0 ? $count . ' מקומות' : 'גלה עכשיו'; ?></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <p class="gn-cat__credits">צילום השיט בירדן: <a href="https://commons.wikimedia.org/wiki/File:RAFTING_ON_THE_JORDAN_RIVER.jpg">איתמר גרינברג / Israel Tourism</a>, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC BY-SA 2.0</a>. התמונה הוקטנה ונחתכת בכרטיס.</p>
  </div>
</section>

<!-- ══════════════════════════════════
     FEATURED LISTINGS
══════════════════════════════════ -->
<section class="gn-section gn-section--bg gn-listings">
  <div class="gn-container">
    <div class="gn-section__header">
      <h2 class="gn-section__title">מקומות <span>מומלצים</span></h2>
      <a href="<?php echo home_url('/places/'); ?>" class="gn-section__link">כל המקומות →</a>
    </div>

    <div class="gn-cards__grid">
      <?php
      $featured = new WP_Query([
        'post_type'      => 'gd_place',
        'post_status'    => 'publish',
        'posts_per_page' => 6,
        'meta_query'     => [[
          'key'     => 'geodir_featured',
          'value'   => '1',
          'compare' => '=',
        ]],
      ]);

      // Fallback: just get latest listings
      if ( ! $featured->have_posts() ) {
        $featured = new WP_Query([
          'post_type'      => 'gd_place',
          'post_status'    => 'publish',
          'posts_per_page' => 6,
        ]);
      }

      if ( $featured->have_posts() ) :
        global $wpdb;
        while ( $featured->have_posts() ) : $featured->the_post();
          $terms    = get_the_terms( get_the_ID(), 'gd_placecategory' );
          $cat_name = ( $terms && ! is_wp_error($terms) ) ? $terms[0]->name : '';
          $gd       = $wpdb->get_row( $wpdb->prepare(
            "SELECT city, overall_rating, featured FROM wp_geodir_gd_place_detail WHERE post_id = %d",
            get_the_ID()
          ) );
          $city          = $gd ? $gd->city : '';
          $rating        = $gd ? $gd->overall_rating : '';
          $featured_flag = $gd ? $gd->featured : 0;
          $image_id      = get_post_thumbnail_id( get_the_ID() );
      ?>
      <article class="gn-card">
        <div class="gn-card__img">
          <?php
          echo gonorth_content_image(
            $image_id,
            'medium_large',
            'תמונה ראשית של ' . get_the_title(),
            'gn-card__media',
            '(max-width: 767px) 100vw, (max-width: 991px) 50vw, 33vw'
          ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
          ?>
          <?php if (!$image_id) echo '<span class="gn-card__placeholder"></span>'; ?>
          <?php if ($cat_name) : ?><span class="gn-card__badge"><?php echo esc_html($cat_name); ?></span><?php endif; ?>
          <?php if ($featured_flag) : ?><span class="gn-card__badge gn-card__badge--featured">מומלץ</span><?php endif; ?>
        </div>
        <div class="gn-card__body">
          <h3 class="gn-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
          <?php if ($city) : ?><div class="gn-card__location"><?php echo esc_html($city); ?></div><?php endif; ?>
          <div class="gn-card__desc"><?php echo wp_trim_words( get_the_excerpt(), 18 ); ?></div>
          <div class="gn-card__footer">
            <?php if ($rating) : ?>
            <div class="gn-card__rating"><span class="gn-stars">★★★★★</span> <?php echo esc_html($rating); ?></div>
            <?php else : ?>
            <div class="gn-card__rating"></div>
            <?php endif; ?>
            <a href="<?php the_permalink(); ?>" class="gn-card__link">פרטים נוספים →</a>
          </div>
        </div>
      </article>
      <?php
        endwhile;
        wp_reset_postdata();
      else :
      ?>
      <div class="gn-empty-state">
        <p>עדיין אין מקומות רשומים. <a href="<?php echo home_url('/add-listing-2/'); ?>">הוסף את הראשון!</a></p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════
     REGIONS
══════════════════════════════════ -->
<section class="gn-section gn-section--bg gn-regions">
  <div class="gn-container">
    <div class="gn-section__header">
      <h2 class="gn-section__title">גלה לפי <span>אזור</span></h2>
      <a href="<?php echo home_url('/places/'); ?>" class="gn-section__link">כל המקומות →</a>
    </div>
    <div class="gn-regions__grid">

      <?php
      $galil_url  = get_term_link( 'galil',  'gn_region' );
      $golan_url  = get_term_link( 'golan',  'gn_region' );
      $carmel_url = get_term_link( 'carmel', 'gn_region' );
      $galil_url  = is_wp_error($galil_url)  ? home_url('/places/') : $galil_url;
      $golan_url  = is_wp_error($golan_url)  ? home_url('/places/') : $golan_url;
      $carmel_url = is_wp_error($carmel_url) ? home_url('/places/') : $carmel_url;
      ?>
      <a href="<?php echo esc_url($galil_url); ?>" class="gn-region__card gn-region__card--featured">
        <?php echo gonorth_content_image( 59, 'large', 'נוף בגליל העליון והתחתון', 'gn-region__media', '(max-width: 768px) 100vw, 50vw' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <div class="gn-region__overlay">
          <div class="gn-region__tag">הגליל</div>
          <h3>גליל עליון ותחתון</h3>
          <p>נחלים, יערות, כפרים ונופי הרים ירוקים</p>
          <span class="gn-region__cta">גלה עכשיו →</span>
        </div>
      </a>

      <div class="gn-regions__col">

        <a href="<?php echo esc_url($golan_url); ?>" class="gn-region__card">
          <?php echo gonorth_content_image( 49, 'medium_large', 'נוף ויקבים ברמת הגולן', 'gn-region__media', '(max-width: 768px) 100vw, 50vw' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
          <div class="gn-region__overlay">
            <div class="gn-region__tag">הגולן</div>
            <h3>רמת הגולן</h3>
            <p>יקבים, מצפים וטבע פתוח</p>
            <span class="gn-region__cta">גלה עכשיו →</span>
          </div>
        </a>

        <a href="<?php echo esc_url($carmel_url); ?>" class="gn-region__card">
          <?php echo gonorth_content_image( 57, 'medium_large', 'נוף הכרמל ועמק יזרעאל', 'gn-region__media', '(max-width: 768px) 100vw, 50vw' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
          <div class="gn-region__overlay">
            <div class="gn-region__tag">הכרמל</div>
            <h3>חוף הכרמל ועמק יזרעאל</h3>
            <p>ים, הרים, היסטוריה ומרחבים</p>
            <span class="gn-region__cta">גלה עכשיו →</span>
          </div>
        </a>

      </div>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════
     BLOG / ARTICLES
══════════════════════════════════ -->
<section class="gn-section gn-blog">
  <div class="gn-container">
    <div class="gn-section__header">
      <h2 class="gn-section__title">מדריכים ו<span>כתבות</span></h2>
      <a href="<?php echo home_url('/blog/'); ?>" class="gn-section__link">כל הכתבות →</a>
    </div>

    <div class="gn-blog__grid">
      <?php
      $posts = new WP_Query([
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 3,
      ]);
      $i = 0;
      if ( $posts->have_posts() ) :
        while ( $posts->have_posts() ) : $posts->the_post();
          $i++;
          $cats   = get_the_category();
          $cat_name = $cats ? $cats[0]->name : '';
          $image_id = get_post_thumbnail_id( get_the_ID() );
      ?>
      <article class="gn-blog__card <?php echo $i === 1 ? 'gn-blog__card--featured' : ''; ?>">
        <a href="<?php the_permalink(); ?>" class="gn-blog__img <?php echo $image_id ? '' : 'gn-blog__img--placeholder'; ?>">
          <?php
          echo gonorth_content_image(
            $image_id,
            $i === 1 ? 'large' : 'medium_large',
            'תמונה ראשית של ' . get_the_title(),
            'gn-blog__media',
            $i === 1 ? '(max-width: 767px) 100vw, 50vw' : '(max-width: 767px) 100vw, 25vw'
          ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
          ?>
          <?php if (!$image_id) : ?>
            <span style="font-size:<?php echo $i===1?'5':'3'; ?>rem"><?php echo $i===1?'':($i===2?'':''); ?></span>
          <?php endif; ?>
        </a>
        <div class="gn-blog__body">
          <?php if ($cat_name) : ?><div class="gn-blog__cat"><?php echo esc_html($cat_name); ?></div><?php endif; ?>
          <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
          <div class="gn-blog__meta"><?php echo get_the_date(); ?><?php if ($i===1) echo ' · ' . gonorth_reading_time(); ?></div>
        </div>
      </article>
      <?php
        endwhile;
        wp_reset_postdata();
      else :
      ?>
      <div class="gn-empty-state" style="grid-column:1/-1">
        <p>עמוד הבלוג מתעדכן בקרוב עם מדריכים וכתבות חדשות.</p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php get_footer(); ?>
