from pathlib import Path
base=Path('memory/backups/2026-09-26-issue-43-plugin')
out=Path('memory/deploy/issue-43')
out.mkdir(parents=True, exist_ok=True)
s=(base/'class-gonorth-internal-links.php').read_text(encoding='utf-8').replace('\r\n','\n')
needle="\tprivate static array $rendered = array();"
assert needle in s
s=s.replace(needle, needle+"\n\n\t/** @var array<string,array<string,string>> Cached published attribute landing links. */\n\tprivate static array $attribute_landings = array();",1)
old="""\t\tforeach ( array_merge( $categories, $regions ) as $term ) {
\t\t\t$url = get_term_link( $term );
\t\t\tif ( ! is_wp_error( $url ) ) {
\t\t\t\t$hub_links[] = array(
\t\t\t\t\t'url'   => $url,
\t\t\t\t\t'label' => 'gn_region' === $term->taxonomy ? 'עוד מקומות ' . self::region_phrase( $term->slug ) : 'עוד ב' . $term->name,
\t\t\t\t);
\t\t\t}
\t\t}
\t\treturn self::singular_markup"""
new="""\t\tforeach ( array_merge( $categories, $regions ) as $term ) {
\t\t\t$url = get_term_link( $term );
\t\t\tif ( ! is_wp_error( $url ) ) {
\t\t\t\t$hub_links[] = array(
\t\t\t\t\t'url'   => $url,
\t\t\t\t\t'label' => 'gn_region' === $term->taxonomy ? 'עוד מקומות ' . self::region_phrase( $term->slug ) : 'עוד ב' . $term->name,
\t\t\t\t);
\t\t\t}
\t\t}
\t\tif ( 'yes' === GoNorth_Place_Attributes::get_state( $post_id, 'open_saturday' ) ) {
\t\t\t$saturday_landing = self::attribute_landing_link( 'open_saturday' );
\t\t\tif ( $saturday_landing ) {
\t\t\t\t$hub_links[] = $saturday_landing;
\t\t\t}
\t\t}
\t\treturn self::singular_markup"""
assert old in s
s=s.replace(old,new,1)
helper="""

\t/** Return one published feature landing link from its code-owned profile. */
\tprivate static function attribute_landing_link( string $attribute ): array {
\t\tif ( isset( self::$attribute_landings[ $attribute ] ) ) {
\t\t\treturn self::$attribute_landings[ $attribute ];
\t\t}
\t\t$pages = get_posts(
\t\t\tarray(
\t\t\t\t'post_type'      => 'page',
\t\t\t\t'post_status'    => 'publish',
\t\t\t\t'posts_per_page' => 1,
\t\t\t\t'meta_key'       => GoNorth_Attribute_Landing_Pages::META_KEY,
\t\t\t\t'meta_value'     => $attribute,
\t\t\t)
\t\t);
\t\tself::$attribute_landings[ $attribute ] = $pages ? array(
\t\t\t'url'   => (string) get_permalink( $pages[0] ),
\t\t\t'label' => 'מקומות פתוחים בשבת בצפון',
\t\t) : array();
\t\treturn self::$attribute_landings[ $attribute ];
\t}
"""
marker="\n\t/** Render links from a guide to its hubs and a small set of matching places. */"
assert marker in s
s=s.replace(marker,helper+marker,1)
(out/'class-gonorth-internal-links.php').write_text(s,encoding='utf-8',newline='\n')
m=(base/'gonorth-place-data.php').read_text(encoding='utf-8').replace('\r\n','\n')
m=m.replace('Version: 1.17.0','Version: 1.17.1',1).replace("define( 'GONORTH_PLACE_DATA_VERSION', '1.17.0' );","define( 'GONORTH_PLACE_DATA_VERSION', '1.17.1' );",1)
(out/'gonorth-place-data.php').write_text(m,encoding='utf-8',newline='\n')
print('prepared')