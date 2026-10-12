<?php
/**
 * GoNorth — Single GeoDirectory Listing
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();

$post_id    = get_the_ID();
$terms      = get_the_terms($post_id, 'gd_placecategory');
$cat_name   = ($terms && !is_wp_error($terms)) ? $terms[0]->name : '';
$category_links = [];
if ( $terms && ! is_wp_error( $terms ) ) {
  foreach ( $terms as $term ) {
    $term_url = get_term_link( $term );
    if ( ! is_wp_error( $term_url ) ) {
      $category_links[] = [ 'name' => $term->name, 'url' => $term_url ];
    }
  }
}
$city       = get_post_meta($post_id, 'geodir_post_city', true);
$address    = get_post_meta($post_id, 'geodir_post_address', true);
$phone      = get_post_meta($post_id, 'geodir_post_phone', true);
$website    = get_post_meta($post_id, 'geodir_post_website', true);
$email      = get_post_meta($post_id, 'geodir_post_email', true);
$rating     = get_post_meta($post_id, 'geodir_post_rating', true);
$featured   = get_post_meta($post_id, 'geodir_featured', true);
$lat        = get_post_meta($post_id, 'geodir_post_latitude', true);
$lng        = get_post_meta($post_id, 'geodir_post_longitude', true);
$waze_allowed = get_post_meta($post_id, '_gonorth_hide_driving_navigation', true) !== '1';
$image_id   = get_post_thumbnail_id( $post_id );

// Google Places data
$google_place_id   = get_post_meta($post_id, 'google_place_id', true);
$gplace            = $google_place_id ? gonorth_get_google_place_data($google_place_id) : null;
$google_rating     = $gplace['rating'] ?? null;
$google_total      = $gplace['total'] ?? null;
$google_photo_ref  = $gplace['photo_ref'] ?? null;
$google_photo_url  = $google_photo_ref ? gonorth_google_photo_url($google_photo_ref, 1200) : null;

// Build star rating display
$stars_full  = $rating ? round($rating) : 0;
$stars_html  = str_repeat('★', $stars_full) . str_repeat('☆', 5 - $stars_full);
?>

<!-- ══ SINGLE HERO ══ -->
<section class="gn-single-hero <?php echo !$image_id ? 'gn-single-hero--no-img' : ''; ?>">
  <?php
  echo gonorth_content_image(
    $image_id,
    'full',
    'תמונה ראשית של ' . get_the_title(),
    'gn-single-hero__media',
    '100vw',
    true
  ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
  ?>
  <div class="gn-single-hero__overlay"></div>
  <div class="gn-container">
    <div class="gn-single-hero__content">
      <?php if ($cat_name) : ?>
      <div class="gn-single-hero__badge"><?php echo esc_html($cat_name); ?></div>
      <?php endif; ?>
      <?php if ($featured) : ?>
      <div class="gn-single-hero__badge gn-single-hero__badge--featured">מומלץ</div>
      <?php endif; ?>
      <h1><?php the_title(); ?></h1>
      <?php if ($city) : ?>
      <div class="gn-single-hero__location"><?php echo esc_html($city); ?></div>
      <?php endif; ?>
      <?php if ($google_rating) : ?>
      <div class="gn-single-hero__rating">
        <?php echo gonorth_google_rating_html($google_rating, $google_total); ?>
      </div>
      <?php elseif ($rating) : ?>
      <div class="gn-single-hero__rating">
        <span class="gn-stars"><?php echo $stars_html; ?></span>
        <span><?php echo esc_html($rating); ?> / 5</span>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ══ MAIN CONTENT ══ -->
<div class="gn-single-layout">
  <div class="gn-container">
    <div class="gn-single-wrap">

      <!-- Main column -->
      <div class="gn-single-main">

        <!-- Breadcrumb -->
        <nav class="gn-breadcrumb">
          <a href="<?php echo home_url('/'); ?>">בית</a>
          <span>›</span>
          <a href="<?php echo home_url('/places/'); ?>">מקומות</a>
          <?php foreach ( $category_links as $category_link ) : ?>
            <span>›</span>
            <a href="<?php echo esc_url( $category_link['url'] ); ?>"><?php echo esc_html( $category_link['name'] ); ?></a>
          <?php endforeach; ?>
          <span>›</span>
          <span><?php the_title(); ?></span>
        </nav>

        <!-- Description -->
        <div class="gn-single-section">
          <h2 class="gn-single-section__title">על המקום</h2>
          <div class="gn-single-content">
            <?php the_content(); ?>
          </div>
        </div>

        <!-- Details grid -->
        <div class="gn-single-section">
          <h2 class="gn-single-section__title">פרטים ומידע</h2>
          <div class="gn-details-grid">
            <?php if ($address) : ?>
            <div class="gn-detail-item">
              <span class="gn-detail-icon"></span>
              <div>
                <strong>כתובת</strong>
                <span><?php echo esc_html($address); ?></span>
              </div>
            </div>
            <?php endif; ?>

            <?php if ($phone) : ?>
            <div class="gn-detail-item">
              <span class="gn-detail-icon"></span>
              <div>
                <strong>טלפון</strong>
                <span><a href="tel:<?php echo esc_attr($phone); ?>"><?php echo esc_html($phone); ?></a></span>
              </div>
            </div>
            <?php endif; ?>

            <?php if ($website) : ?>
            <div class="gn-detail-item">
              <span class="gn-detail-icon"></span>
              <div>
                <strong>אתר</strong>
                <span><a href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener">
                  <?php echo esc_html(str_replace(['https://', 'http://'], '', $website)); ?>
                </a></span>
              </div>
            </div>
            <?php endif; ?>

            <?php if ($email) : ?>
            <div class="gn-detail-item">
              <span class="gn-detail-icon"></span>
              <div>
                <strong>אימייל</strong>
                <span><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></span>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Google Photos / Reviews link -->
        <?php if ($google_photo_url) : ?>
        <div class="gn-single-section">
          <h2 class="gn-single-section__title">תמונות</h2>
          <div class="gn-google-photos">
            <img src="<?php echo esc_url($google_photo_url); ?>"
                 alt="<?php echo esc_attr(get_the_title()); ?>"
                 class="gn-google-photo"
                 loading="lazy" />
            <?php if ($google_rating && $google_place_id) : ?>
            <div class="gn-google-photos__credit">
              <a href="https://www.google.com/maps/place/?q=place_id:<?php echo esc_attr($google_place_id); ?>"
                 target="_blank" rel="noopener" class="gn-google-photos__link">כל הביקורות בגוגל</a>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <?php elseif ($google_place_id && $google_rating) : ?>
        <div class="gn-single-section gn-single-section--compact">
          <div class="gn-google-reviews-bar">
            <?php echo gonorth_google_rating_html($google_rating, $google_total); ?>
            <a href="https://www.google.com/maps/place/?q=place_id:<?php echo esc_attr($google_place_id); ?>"
               target="_blank" rel="noopener" class="gn-google-reviews-bar__link">כל הביקורות בגוגל ←</a>
          </div>
        </div>
        <?php endif; ?>

        <!-- Listing navigation -->
        <?php
        $prev_listing = get_previous_post();
        $next_listing = get_next_post();
        if ( $prev_listing || $next_listing ) :
        ?>
        <nav class="gn-listing-nav" aria-label="ניווט בין מקומות">
          <?php if ( $next_listing ) : ?>
          <a href="<?php echo get_permalink($next_listing); ?>" class="gn-listing-nav__item gn-listing-nav__item--prev">
            <span class="gn-listing-nav__arrow">›</span>
            <div class="gn-listing-nav__text">
              <span class="gn-listing-nav__label">הקודם</span>
              <span class="gn-listing-nav__title"><?php echo esc_html(get_the_title($next_listing)); ?></span>
            </div>
          </a>
          <?php endif; ?>
          <?php if ( $prev_listing ) : ?>
          <a href="<?php echo get_permalink($prev_listing); ?>" class="gn-listing-nav__item gn-listing-nav__item--next">
            <div class="gn-listing-nav__text">
              <span class="gn-listing-nav__label">הבא</span>
              <span class="gn-listing-nav__title"><?php echo esc_html(get_the_title($prev_listing)); ?></span>
            </div>
            <span class="gn-listing-nav__arrow">‹</span>
          </a>
          <?php endif; ?>
        </nav>
        <?php endif; ?>

      </div>

      <!-- Sidebar -->
      <aside class="gn-single-sidebar">

        <!-- Booking/CTA card -->
        <div class="gn-booking-card">
          <?php if ($featured) : ?>
          <div class="gn-booking-card__badge">מקום מומלץ</div>
          <?php endif; ?>
          <h3><?php the_title(); ?></h3>
          <?php if ($google_rating) : ?>
          <div class="gn-booking-card__rating">
            <?php echo gonorth_google_rating_html($google_rating, $google_total); ?>
          </div>
          <?php elseif ($rating) : ?>
          <div class="gn-booking-card__rating">
            <span class="gn-stars"><?php echo $stars_html; ?></span>
            <span><?php echo esc_html($rating); ?></span>
          </div>
          <?php endif; ?>

          <div class="gn-booking-card__actions">
            <?php if ($phone) : ?>
            <a href="tel:<?php echo esc_attr($phone); ?>" class="gn-btn gn-btn--green gn-btn--full">
              התקשר עכשיו
            </a>
            <?php endif; ?>
            <?php if ($website) : ?>
            <a href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener"
               class="gn-btn gn-btn--outline gn-btn--full">
              לאתר הרשמי
            </a>
            <?php endif; ?>
            <?php if ($waze_allowed) : ?>
            <a href="https://www.waze.com/ul?ll=<?php echo esc_attr($lat); ?>%2C<?php echo esc_attr($lng); ?>&navigate=yes"
               target="_blank" rel="noopener" class="gn-btn gn-btn--outline gn-btn--full">
              נווט ב-Waze
            </a>
            <?php endif; ?>
          </div>
          <?php if (!$waze_allowed) : ?>
          <p>המפה מסמנת את מיקום המקום בלבד. אין יעד ניווט מאומת לרכב; בדקו את הוראות ההגעה בעמוד לפני הנסיעה.</p>
          <?php endif; ?>

          <div class="gn-booking-card__share">
            <span>שתף:</span>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>"
               target="_blank" rel="noopener" aria-label="Facebook">f</a>
            <a href="https://wa.me/?text=<?php echo urlencode(get_the_title() . ' ' . get_permalink()); ?>"
               target="_blank" rel="noopener" aria-label="WhatsApp"></a>
          </div>
        </div>

        <!-- Related listings -->
        <?php
        $related_args = [
          'post_type'      => 'gd_place',
          'posts_per_page' => 3,
          'post__not_in'   => [$post_id],
          'orderby'        => 'rand',
        ];
        if ($terms && !is_wp_error($terms)) {
          $related_args['tax_query'] = [[
            'taxonomy' => 'gd_placecategory',
            'field'    => 'term_id',
            'terms'    => array_map(function($t){ return $t->term_id; }, $terms),
          ]];
        }
        $related = new WP_Query($related_args);
        if ($related->have_posts()) :
        ?>
        <div class="gn-sidebar__card">
          <h4>מקומות דומים</h4>
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
              <span><?php echo esc_html(get_post_meta(get_the_ID(), 'geodir_post_city', true)); ?></span>
            </div>
          </a>
          <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <?php endif; ?>

      </aside>

    </div>
  </div>
</div>

<?php endwhile; ?>
<?php get_footer(); ?>
