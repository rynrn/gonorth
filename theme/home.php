<?php
/**
 * Blog posts index template (used when "Posts page" is set to /blog/).
 * GoNorth child theme — Hebrew RTL.
 *
 * Deploy to: /var/www/gonorth/wp-content/themes/gonorth-child/home.php
 */

get_header();
?>

<div class="gn-blog-page" dir="rtl">

  <!-- ───── Hero ───── -->
  <section class="gn-blog-hero">
    <div class="gn-blog-hero__inner">
      <p class="gn-blog-hero__eyebrow">גלו את הצפון</p>
      <h1 class="gn-blog-hero__title">הבלוג שלנו</h1>
      <p class="gn-blog-hero__subtitle">מדריכים, טיפים ויעדים לטיול בצפון ישראל</p>
    </div>
  </section>

  <!-- ───── Posts Grid ───── -->
  <section class="gn-blog-grid-section">
    <div class="gn-container">

      <?php if ( have_posts() ) : ?>

        <div class="gn-blog-grid">
          <?php while ( have_posts() ) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class( 'gn-blog-card' ); ?>>

              <?php if ( has_post_thumbnail() ) : ?>
                <a href="<?php the_permalink(); ?>" class="gn-blog-card__img-wrap" tabindex="-1" aria-hidden="true">
                  <?php the_post_thumbnail( 'medium_large', [ 'class' => 'gn-blog-card__img', 'loading' => 'lazy' ] ); ?>
                </a>
              <?php else : ?>
                <div class="gn-blog-card__img-wrap gn-blog-card__img-wrap--placeholder">
                  <svg viewBox="0 0 400 240" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <rect width="400" height="240" fill="#e8f5e9"/>
                    <path d="M160 80 Q200 40 240 80 L280 140 H120 Z" fill="#2D6A4F" opacity=".35"/>
                    <circle cx="120" cy="90" r="22" fill="#48CAE4" opacity=".5"/>
                  </svg>
                </div>
              <?php endif; ?>

              <div class="gn-blog-card__body">

                <?php
                $cats = get_the_category();
                if ( $cats ) :
                ?>
                  <div class="gn-blog-card__cats">
                    <?php foreach ( $cats as $cat ) : ?>
                      <a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>"
                         class="gn-blog-card__cat">
                        <?php echo esc_html( $cat->name ); ?>
                      </a>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>

                <h2 class="gn-blog-card__title">
                  <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h2>

                <div class="gn-blog-card__excerpt">
                  <?php the_excerpt(); ?>
                </div>

                <div class="gn-blog-card__footer">
                  <time class="gn-blog-card__date" datetime="<?php echo get_the_date( 'Y-m-d' ); ?>">
                    <?php echo get_the_date( 'j בF Y' ); ?>
                  </time>
                  <a href="<?php the_permalink(); ?>" class="gn-blog-card__read-more" aria-label="קרא עוד על <?php the_title_attribute(); ?>">
                    קרא עוד ←
                  </a>
                </div>

              </div><!-- .gn-blog-card__body -->

            </article>

          <?php endwhile; ?>
        </div><!-- .gn-blog-grid -->

        <!-- Pagination -->
        <nav class="gn-blog-pagination" aria-label="ניווט בין עמודים">
          <?php
          the_posts_pagination( [
            'mid_size'           => 2,
            'prev_text'          => '→ הקודם',
            'next_text'          => 'הבא ←',
            'screen_reader_text' => 'ניווט עמודים',
          ] );
          ?>
        </nav>

      <?php else : ?>

        <div class="gn-blog-empty">
          <p>אין כתבות להצגה כרגע — חזרו בקרוב!</p>
          <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="gn-btn gn-btn--primary">
            חזרה לדף הבית
          </a>
        </div>

      <?php endif; ?>

    </div><!-- .gn-container -->
  </section>

</div><!-- .gn-blog-page -->

<?php get_footer(); ?>
