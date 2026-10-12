<?php
/**
 * GoNorth — Single Blog Post
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while (have_posts()) : the_post();

$cats    = get_the_category();
$cat_name = $cats ? $cats[0]->name : '';
$cat_url  = $cats ? get_category_link($cats[0]->term_id) : '';
$image_id = get_post_thumbnail_id( get_the_ID() );
$editorial_excerpt = trim( (string) get_post_field( 'post_excerpt', get_the_ID() ) );
$rendered_content = apply_filters( 'the_content', get_the_content() );
// The hero already displays the featured image; suppress a matching figure near the article start.
if ( $image_id && false !== strpos( substr( $rendered_content, 0, 2500 ), 'wp-image-' . $image_id ) ) {
  $rendered_content = preg_replace( '~<figure\b[^>]*>(?:(?!</figure>).)*?<img\b[^>]*\bwp-image-' . (int) $image_id . '\b.*?</figure>~is', '', $rendered_content, 1 ) ?: $rendered_content;
}
$sections = [];
$existing_heading_ids = [];
if ( preg_match_all( '~<h2[^>]*\bid\s*=\s*["\']([^"\']+)["\']~i', $rendered_content, $found_ids ) ) {
  $existing_heading_ids = $found_ids[1];
}
$rendered_content = preg_replace_callback( '~<h2([^>]*)>(.*?)</h2>~is', function( $match ) use ( &$sections, &$existing_heading_ids ) {
  $label = trim( wp_strip_all_tags( $match[2] ) );
  if ( '' === $label ) return $match[0];
  $id = 'gn-toc-' . get_the_ID() . '-' . ( count( $sections ) + 1 );
  while ( in_array( $id, $existing_heading_ids, true ) ) $id .= '-2';
  if ( preg_match( '~\bid\s*=\s*["\']([^"\']+)["\']~i', $match[1], $existing ) ) {
    $id = $existing[1];
    $sections[] = [ 'id' => $id, 'label' => $label ];
    return $match[0];
  }
  $sections[] = [ 'id' => $id, 'label' => $label ];
  $existing_heading_ids[] = $id;
  return '<h2' . $match[1] . ' id="' . esc_attr( $id ) . '">' . $match[2] . '</h2>';
}, $rendered_content );
$show_guide_nav = count( $sections ) >= 3 && preg_match_all( '/[\p{L}\p{N}]+/u', wp_strip_all_tags( $rendered_content ) ) >= 500;
?>

<!-- ══ POST HERO ══ -->
<section class="gn-post-hero <?php echo !$image_id ? 'gn-post-hero--no-img' : ''; ?>">
  <?php
  echo gonorth_content_image(
    $image_id,
    'full',
    'תמונה ראשית של ' . get_the_title(),
    'gn-post-hero__media',
    '100vw',
    true
  ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
  ?>
  <div class="gn-post-hero__overlay"></div>
  <div class="gn-container">
    <div class="gn-post-hero__content">
      <nav class="gn-breadcrumb gn-breadcrumb--light">
        <a href="<?php echo home_url('/'); ?>">בית</a>
        <span>›</span>
        <a href="<?php echo home_url('/blog/'); ?>">בלוג</a>
        <?php if ($cat_name) : ?>
        <span>›</span>
        <a href="<?php echo esc_url($cat_url); ?>"><?php echo esc_html($cat_name); ?></a>
        <?php endif; ?>
      </nav>
      <?php if ($cat_name) : ?>
      <div class="gn-single-hero__badge"><?php echo esc_html($cat_name); ?></div>
      <?php endif; ?>
      <h1><?php the_title(); ?></h1>
      <div class="gn-post-hero__meta">
        <span><a href="<?php echo esc_url( home_url( "/about/" ) ); ?>">צוות כיוון צפון</a></span>
        <span><?php echo get_the_date(); ?></span>
        <span><?php echo gonorth_reading_time(); ?></span>
      </div>
    </div>
  </div>
</section>

<!-- ══ POST LAYOUT ══ -->
<div class="gn-single-layout">
  <div class="gn-container">
    <div class="gn-single-wrap">

      <!-- Post content -->
      <article class="gn-single-main">
        <?php if ( $show_guide_nav ) : ?>
        <div class="gn-guide-summary" dir="rtl">
          <?php if ( $editorial_excerpt ) : ?><p><strong>בקצרה:</strong> <?php echo esc_html( $editorial_excerpt ); ?></p><?php endif; ?>
          <nav class="gn-guide-toc" aria-label="תוכן עניינים">
            <strong>במדריך הזה</strong>
            <ol>
              <?php foreach ( $sections as $section ) : ?>
              <li><a href="#<?php echo esc_attr( $section['id'] ); ?>"><?php echo esc_html( $section['label'] ); ?></a></li>
              <?php endforeach; ?>
            </ol>
          </nav>
        </div>
        <?php endif; ?>
        <div class="gn-post-content">
          <?php echo $rendered_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>

        <!-- Tags -->
        <?php $tags = get_the_tags(); if ($tags) : ?>
        <div class="gn-post-tags">
          <strong>תגיות: </strong>
          <?php foreach ($tags as $tag) : ?>
          <a href="<?php echo get_tag_link($tag->term_id); ?>" class="gn-tag"><?php echo esc_html($tag->name); ?></a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Share -->
        <div class="gn-post-share">
          <strong>שתף:</strong>
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>"
             target="_blank" rel="noopener" class="gn-share-btn gn-share-btn--fb">Facebook</a>
          <a href="https://wa.me/?text=<?php echo urlencode(get_the_title() . ' - ' . get_permalink()); ?>"
             target="_blank" rel="noopener" class="gn-share-btn gn-share-btn--wa">WhatsApp</a>
        </div>

        <!-- Post nav — RTL: prev (older post) on right with ‹, next (newer post) on left with › -->
        <div class="gn-post-nav">
          <?php
          $prev = get_previous_post();
          $next = get_next_post();
          if ($prev) : ?>
          <a href="<?php echo get_permalink($prev); ?>" class="gn-post-nav__prev">
            ‹ <?php echo esc_html(get_the_title($prev)); ?>
          </a>
          <?php endif; ?>
          <?php if ($next) : ?>
          <a href="<?php echo get_permalink($next); ?>" class="gn-post-nav__next">
            <?php echo esc_html(get_the_title($next)); ?> ›
          </a>
          <?php endif; ?>
        </div>

        <!-- Comments -->
        <?php comments_template(); ?>
      </article>

      <!-- Sidebar -->
      <aside class="gn-blog-sidebar">
        <!-- Author card -->
        <div class="gn-sidebar__card gn-author-card">
          <div class="gn-author-card__info">
            <strong>צוות כיוון צפון</strong>
            <p>מדריכים למטיילים בצפון, עם קישורים למקורות ולמקומות באתר.</p>
            <a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">על האתר והעריכה</a>
          </div>
        </div>

        <!-- Related posts -->
        <?php
        $related = new WP_Query([
          'post_type'      => 'post',
          'posts_per_page' => 4,
          'post__not_in'   => [ get_the_ID(), 267 ], // #267 redirects to the consolidated restaurant guide.
          'orderby'        => 'rand',
          'category__in'   => $cats ? array_map(function($c){ return $c->term_id; }, $cats) : [],
        ]);
        if ($related->have_posts()) :
        ?>
        <div class="gn-sidebar__card">
          <h4>כתבות נוספות</h4>
          <?php while ($related->have_posts()) : $related->the_post();
            $related_image_id = get_post_thumbnail_id( get_the_ID() );
          ?>
          <a href="<?php the_permalink(); ?>" class="gn-related-item">
            <div class="gn-related-item__img">
              <?php echo gonorth_content_image( $related_image_id, 'medium', 'תמונה ראשית של ' . get_the_title(), 'gn-related-item__media', '56px' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
              <?php if (!$related_image_id) echo '<span></span>'; ?>
            </div>
            <div class="gn-related-item__text">
              <strong><?php the_title(); ?></strong>
              <span><?php echo get_the_date(); ?></span>
            </div>
          </a>
          <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <?php endif; ?>

        <!-- Listings CTA -->
        <div class="gn-sidebar__card gn-sidebar__card--cta">
          <div class="gn-sidebar__cta-icon"></div>
          <h4>גלה מקומות בצפון</h4>
          <p>מאות אטרקציות, מסעדות ולינה בגליל וגולן</p>
          <a href="<?php echo home_url('/places/'); ?>" class="gn-btn gn-btn--green">לכל המקומות</a>
        </div>

        <?php if (is_active_sidebar('gonorth-blog-sidebar')) :
          dynamic_sidebar('gonorth-blog-sidebar');
        endif; ?>
      </aside>

    </div>
  </div>
</div>

<?php endwhile; ?>
<?php get_footer(); ?>

