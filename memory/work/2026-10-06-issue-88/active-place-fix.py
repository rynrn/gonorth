from pathlib import Path
base=Path('memory/work/2026-10-06-issue-88')
p=base/'functions.candidate.php'
s=p.read_text(encoding='utf-8')
needle="""    $lng          = $data['longitude'] ?? '';
    $rating"""
replacement="""    $lng          = $data['longitude'] ?? '';
    $has_valid_location = is_numeric( $lat ) && is_numeric( $lng ) && (float) $lat > 29 && (float) $lat < 34 && (float) $lng > 34 && (float) $lng < 36;
    $business_status = (string) get_post_meta( $post_id, '_gonorth_business_status', true );
    $is_closed = in_array( $business_status, [ 'permanently_closed', 'temporarily_closed', 'closed_permanently', 'closed_temporarily' ], true );
    $rating"""
assert s.count(needle)==1
s=s.replace(needle,replacement)
old="""    echo '<nav class="gn-place-overview__actions" aria-label="פעולות למקום">';
    if ( $lat && $lng ) echo '<a href="https://waze.com/ul?ll=' . esc_attr( $lat ) . ',' . esc_attr( $lng ) . '&navigate=yes" target="_blank" rel="noopener" class="gn-single-action gn-single-action--primary">ניווט ב-Waze</a>';
    if ( $phone ) echo '<a href="tel:' . esc_attr( $phone ) . '" class="gn-single-action">התקשרו</a>';
    if ( $website ) echo '<a href="' . esc_url( $website ) . '" target="_blank" rel="noopener" class="gn-single-action">לאתר הרשמי</a>';
    if ( $lat && $lng ) echo '<a href="https://www.google.com/maps?q=' . esc_attr( $lat ) . ',' . esc_attr( $lng ) . '" target="_blank" rel="noopener" class="gn-single-action">Google Maps</a>';
    echo '<a href="https://wa.me/?text=' . rawurlencode( $post_obj->post_title . ' — ' . get_permalink( $post_id ) ) . '" target="_blank" rel="noopener" class="gn-single-action">שיתוף</a>';
    echo '</nav></div></section>';"""
new="""    echo '<nav class="gn-place-overview__actions" aria-label="פעולות למקום">';
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
    echo '</div></section>';"""
assert s.count(old)==1
s=s.replace(old,new)
p.write_text(s,encoding='utf-8')
# GeoDirectory overrides this legacy template; restore its pre-change bytes.
(base/'single-gd_place.candidate.php').write_bytes((base/'single-gd_place.before.php').read_bytes())
css=base/'gonorth.candidate.css'
s=css.read_text(encoding='utf-8')
start=s.index('/* Issue 88: article navigation and place action hierarchy. */')
s=s[:start]+'''/* Issue 88: article navigation and active GeoDirectory place actions. */
.gn-guide-summary { direction: rtl; text-align: right; background: var(--gn-bg); border: 1px solid var(--gn-border); border-radius: 12px; padding: 18px 22px; margin: 0 0 28px; }
.gn-guide-summary p { margin: 0 0 12px; line-height: 1.65; }
.gn-guide-toc strong { display: block; margin-bottom: 8px; }
.gn-guide-toc ol { margin: 0; padding-inline-start: 22px; columns: 2; column-gap: 24px; }
.gn-guide-toc li { break-inside: avoid; margin: 0 0 6px; }
.gn-guide-toc a, .gn-author-card__info a, .gn-place-overview__shortcuts a { color: var(--gn-green); text-decoration: underline; text-underline-offset: 3px; overflow-wrap: anywhere; }
.gn-post-hero__meta a { color: #fff; text-decoration: underline; text-underline-offset: 3px; }
.gn-post-content h2[id], .gn-place-location h2[id], #post_content { scroll-margin-top: 100px; }
.gn-place-overview__actions { display: flex; flex-direction: column; align-items: flex-start; gap: 10px; }
.gn-place-overview__primary, .gn-place-overview__secondary { display: flex; flex-wrap: wrap; gap: 8px; }
.gn-place-overview__secondary .gn-single-action { background: var(--gn-bg); }
.gn-place-overview__shortcuts { display: flex; flex-wrap: wrap; gap: 8px 18px; grid-column: 1 / -1; direction: rtl; text-align: right; }
.gn-place-overview__limit { grid-column: 1 / -1; margin: 0; color: var(--gn-text); }
.gn-guide-toc a:focus-visible, .gn-author-card__info a:focus-visible, .gn-post-hero__meta a:focus-visible, .gn-place-overview__shortcuts a:focus-visible, .gn-place-overview .gn-single-action:focus-visible { outline: 3px solid var(--gn-green-dark); outline-offset: 3px; box-shadow: 0 0 0 5px #fff; border-radius: 3px; }
@media (max-width: 640px) { .gn-guide-toc ol { columns: 1; } .gn-guide-summary { padding: 16px; } .gn-place-overview__actions { display: flex; width: 100%; } .gn-place-overview__primary { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); width: 100%; } .gn-place-overview__secondary { width: 100%; } }
@media (max-width: 420px) { .gn-place-overview__primary { grid-template-columns: 1fr 1fr; } }
'''
css.write_text(s,encoding='utf-8')
print('active place candidate updated; legacy template restored')
