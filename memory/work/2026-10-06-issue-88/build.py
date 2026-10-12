from pathlib import Path
base=Path('memory/work/2026-10-06-issue-88')
functions=Path('memory/deploy/issue-90/functions.php').read_text(encoding='utf-8')
old="""function gonorth_reading_time() {
    $content    = get_post_field( 'post_content', get_the_ID() );
    $word_count = str_word_count( strip_tags( $content ) );
    $minutes    = (int) ceil( $word_count / 200 );
    return $minutes . ' דקות קריאה';
}"""
new="""function gonorth_reading_time() {
    $content = html_entity_decode( wp_strip_all_tags( strip_shortcodes( get_post_field( 'post_content', get_the_ID() ) ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
    $word_count = preg_match_all( '/[\\p{L}\\p{N}]+/u', $content );
    $minutes = max( 1, (int) ceil( $word_count / 200 ) );
    return $minutes . ( 1 === $minutes ? ' דקת קריאה' : ' דקות קריאה' );
}"""
assert functions.count(old)==1
functions=functions.replace(old,new).replace("'2.9.9'","'2.9.10'")
(base/'functions.candidate.php').write_text(functions,encoding='utf-8')
post=(base/'single.before.php').read_text(encoding='utf-8')
needle="$image_id = get_post_thumbnail_id( get_the_ID() );"
extra="""$image_id = get_post_thumbnail_id( get_the_ID() );
$editorial_excerpt = trim( (string) get_post_field( 'post_excerpt', get_the_ID() ) );
$rendered_content = apply_filters( 'the_content', get_the_content() );
// The hero already displays the featured image; suppress a matching figure near the article start.
if ( $image_id && false !== strpos( substr( $rendered_content, 0, 2500 ), 'wp-image-' . $image_id ) ) {
  $rendered_content = preg_replace( '~<figure\\b[^>]*>[^<]*(?:<(?!/figure>)[^>]*>[^<]*)*<img\\b[^>]*\\bwp-image-' . (int) $image_id . '\\b.*?</figure>~is', '', $rendered_content, 1 ) ?: $rendered_content;
}
$sections = [];
$rendered_content = preg_replace_callback( '~<h2([^>]*)>(.*?)</h2>~is', function( $match ) use ( &$sections ) {
  $label = trim( wp_strip_all_tags( $match[2] ) );
  if ( '' === $label ) return $match[0];
  $id = 'gn-section-' . ( count( $sections ) + 1 );
  $sections[] = [ 'id' => $id, 'label' => $label ];
  if ( preg_match( '~\\bid\\s*=~i', $match[1] ) ) return $match[0];
  return '<h2' . $match[1] . ' id="' . esc_attr( $id ) . '">' . $match[2] . '</h2>';
}, $rendered_content );
$show_guide_nav = count( $sections ) >= 3 && preg_match_all( '/[\\p{L}\\p{N}]+/u', wp_strip_all_tags( $rendered_content ) ) >= 500;"""
assert post.count(needle)==1
post=post.replace(needle,extra)
post=post.replace('<span><?php the_author(); ?></span>','<span><a href="<?php echo esc_url( home_url( "/about/" ) ); ?>">צוות כיוון צפון</a></span>',1)
post=post.replace('''        <div class="gn-post-content">
          <?php the_content(); ?>
        </div>''','''        <?php if ( $show_guide_nav ) : ?>
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
        </div>''')
post=post.replace('''          <div class="gn-author-card__avatar">
            <?php echo get_avatar(get_the_author_meta('ID'), 64, '', '', ['class' => 'gn-author-avatar']); ?>
          </div>
          <div class="gn-author-card__info">
            <strong><?php the_author(); ?></strong>
            <p><?php echo esc_html(get_the_author_meta('description') ?: 'כותב בכיוון צפון'); ?></p>
          </div>''','''          <div class="gn-author-card__info">
            <strong>צוות כיוון צפון</strong>
            <p>מדריכים למטיילים בצפון, עם קישורים למקורות ולמקומות באתר.</p>
            <a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">על האתר והעריכה</a>
          </div>''')
(base/'single.candidate.php').write_text(post,encoding='utf-8')
place=(base/'single-gd_place.before.php').read_text(encoding='utf-8')
place=place.replace('''        <!-- Description -->''','''        <nav class="gn-place-shortcuts" aria-label="קיצורי דרך בדף המקום">
          <a href="#gn-place-details">פרטים מעשיים</a>
          <a href="#gn-place-actions">יצירת קשר וניווט</a>
        </nav>

        <!-- Description -->''',1)
place=place.replace('''        <div class="gn-single-section">
          <h2 class="gn-single-section__title">פרטים ומידע</h2>''','''        <div class="gn-single-section" id="gn-place-details">
          <h2 class="gn-single-section__title">פרטים ומידע</h2>''',1)
place=place.replace('''        <div class="gn-booking-card">''','''        <div class="gn-booking-card" id="gn-place-actions">''',1)
old_actions='''          <div class="gn-booking-card__actions">
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
            <a href="https://www.waze.com/ul?ll=<?php echo esc_attr($lat); ?>%2C<?php echo esc_attr($lng); ?>&navigate=yes"
               target="_blank" rel="noopener" class="gn-btn gn-btn--outline gn-btn--full">
              נווט ב-Waze
            </a>
          </div>'''
new_actions='''          <div class="gn-booking-card__actions">
            <?php if ($phone) : ?>
            <a href="tel:<?php echo esc_attr($phone); ?>" class="gn-btn gn-btn--green gn-btn--full">
              התקשר למקום
            </a>
            <?php endif; ?>
            <?php if ( is_numeric( $lat ) && is_numeric( $lng ) ) : ?>
            <a href="https://www.waze.com/ul?ll=<?php echo esc_attr($lat); ?>%2C<?php echo esc_attr($lng); ?>&navigate=yes"
               target="_blank" rel="noopener" class="gn-btn gn-btn--outline gn-btn--full">
              נווט ב-Waze
            </a>
            <?php endif; ?>
            <?php if ($website) : ?>
            <a href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener"
               class="gn-booking-card__secondary">
              לאתר הרשמי
            </a>
            <?php endif; ?>
            <?php if ( ! $phone && ! ( is_numeric( $lat ) && is_numeric( $lng ) ) && ! $website ) : ?>
            <p class="gn-booking-card__unavailable">אין פרטי קשר או ניווט מאומתים כרגע. בדקו לפני היציאה.</p>
            <?php endif; ?>
          </div>'''
assert place.count(old_actions)==1
place=place.replace(old_actions,new_actions)
(base/'single-gd_place.candidate.php').write_text(place,encoding='utf-8')
css=(base/'gonorth.before.css').read_text(encoding='utf-8')
css+='''\n/* Issue 88: article navigation and place action hierarchy. */
.gn-guide-summary, .gn-place-shortcuts { direction: rtl; text-align: right; }
.gn-guide-summary { background: var(--gn-bg); border: 1px solid var(--gn-border); border-radius: 12px; padding: 18px 22px; margin: 0 0 28px; }
.gn-guide-summary p { margin: 0 0 12px; line-height: 1.65; }
.gn-guide-toc strong { display: block; margin-bottom: 8px; }
.gn-guide-toc ol { margin: 0; padding-inline-start: 22px; columns: 2; column-gap: 24px; }
.gn-guide-toc li { break-inside: avoid; margin: 0 0 6px; }
.gn-guide-toc a, .gn-place-shortcuts a, .gn-booking-card__secondary, .gn-author-card__info a { color: var(--gn-green); text-decoration: underline; text-underline-offset: 3px; overflow-wrap: anywhere; }
.gn-post-hero__meta a { color: #fff; text-decoration: underline; text-underline-offset: 3px; }
.gn-post-content h2[id], #gn-place-details, #gn-place-actions { scroll-margin-top: 100px; }
.gn-place-shortcuts { display: flex; flex-wrap: wrap; gap: 12px 20px; padding: 12px 0 20px; }
.gn-booking-card__secondary { display: inline-block; align-self: flex-start; padding: 8px 0; }
.gn-booking-card__unavailable { color: var(--gn-muted); margin: 0; line-height: 1.5; }
.gn-guide-toc a:focus-visible, .gn-place-shortcuts a:focus-visible, .gn-booking-card a:focus-visible, .gn-author-card__info a:focus-visible, .gn-post-hero__meta a:focus-visible { outline: 3px solid var(--gn-yellow); outline-offset: 3px; border-radius: 3px; }
@media (max-width: 600px) { .gn-guide-toc ol { columns: 1; } .gn-guide-summary { padding: 16px; } }
'''
(base/'gonorth.candidate.css').write_text(css,encoding='utf-8')
print('built',*[str(base/name) for name in ['functions.candidate.php','single.candidate.php','single-gd_place.candidate.php','gonorth.candidate.css']])
