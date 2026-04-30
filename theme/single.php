<?php
/**
 * Single blog post template.
 * GoNorth child theme — Hebrew RTL.
 *
 * Deploy to: /var/www/gonorth/wp-content/themes/gonorth-child/single.php
 */

get_header();
?>

<div class="gn-single-post" dir="rtl">

  <?php while ( have_posts() ) : the_post(); ?>

    <!-- ───── Post Header ───── -->
    <header class="gn-post-header">

      <?php if ( has_post_thumbnail() ) : ?>
        <div class="gn-post-header__hero">
          <?php the_post_thumbnail( 'full', [ 'class' => 'gn-post-header__hero-img', 'loading' => 'eager' ] ); ?>
          <div class="gn-post-header__hero-overlay"></div>
        </div>
      <?php endif; ?>

      <div class="gn-post-header__content <?php echo has_post_thumbnail() ? 'gn-post-header__content--over-image' : ''; ?>">
        <div class="gn-container">

          <?php
          $cats = get_the_category();
          if ( $cats ) :
          ?>
            <div class="gn-post-header__cats">
              <?php foreach ( $cats as $cat ) : ?>
                <a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>"
                   class="gn-post-header__cat">
                  <?php echo esc_html( $cat->name ); ?>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <h1 class="gn-post-header__title"><?php the_title(); ?></h1>

          <div class="gn-post-header__meta">
            <time class="gn-post-header__date" datetime="<?php echo get_the_date( 'Y-m-d' ); ?>">
              <?php echo get_the_date( 'j בF Y' ); ?>
            </time>
            <span class="gn-post-header__sep" aria-hidden="true">·</span>
            <span class="gn-post-header__author">מאת <?php the_author(); ?></span>
            <span class="gn-post-header__sep" aria-hidden="true">·</span>
            <span class="gn-post-header__read-time">
              <?php
              $word_count  = str_word_count( wp_strip_all_tags( get_the_content() ) );
              $read_time   = max( 1, round( $word_count / 200 ) );
              echo $read_time . ' דקות קריאה';
              ?>
            </span>
          </div>

        </div><!-- .gn-container -->
      </div><!-- .gn-post-header__content -->

    </header><!-- .gn-post-header -->

    <!-- ───── Article Body ───── -->
    <div class="gn-container">
      <div class="gn-post-layout">

        <article class="gn-post-content entry-content">
          <?php the_content(); ?>
        </article>

        <!-- ───── Footer Meta ───── -->
        <footer class="gn-post-footer">

          <?php
          $tags = get_the_tags();
          if ( $tags ) :
          ?>
            <div class="gn-post-tags">
              <span class="gn-post-tags__label">תגיות:</span>
              <?php foreach ( $tags as $tag ) : ?>
                <a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>"
                   class="gn-post-tag">
                  <?php echo esc_html( $tag->name ); ?>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

        </footer>

        <!-- ───── Post Navigation ───── -->
        <nav class="gn-post-nav" aria-label="ניווט בין כתבות">
          <?php
          $prev = get_previous_post();
          $next = get_next_post();
          ?>
          <?php if ( $prev ) : ?>
            <a href="<?php echo esc_url( get_permalink( $prev->ID ) ); ?>" class="gn-post-nav__link gn-post-nav__link--prev">
              <span class="gn-post-nav__arrow">→</span>
              <span class="gn-post-nav__info">
                <span class="gn-post-nav__label">הכתבה הקודמת</span>
                <span class="gn-post-nav__title"><?php echo esc_html( get_the_title( $prev->ID ) ); ?></span>
              </span>
            </a>
          <?php endif; ?>
          <?php if ( $next ) : ?>
            <a href="<?php echo esc_url( get_permalink( $next->ID ) ); ?>" class="gn-post-nav__link gn-post-nav__link--next">
              <span class="gn-post-nav__info">
                <span class="gn-post-nav__label">הכתבה הבאה</span>
                <span class="gn-post-nav__title"><?php echo esc_html( get_the_title( $next->ID ) ); ?></span>
              </span>
              <span class="gn-post-nav__arrow">←</span>
            </a>
          <?php endif; ?>
        </nav>

        <!-- ───── Back to Blog ───── -->
        <div class="gn-post-back">
          <a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>" class="gn-btn gn-btn--outline">
            ← חזרה לבלוג
          </a>
        </div>

      </div><!-- .gn-post-layout -->
    </div><!-- .gn-container -->

  <?php endwhile; ?>

</div><!-- .gn-single-post -->

<?php get_footer(); ?>
