from pathlib import Path
base=Path('memory/work/2026-10-06-issue-88')
post=base/'single.candidate.php'
s=post.read_text(encoding='utf-8')
s=s.replace("$sections = [];\n$rendered_content", """$sections = [];
$existing_heading_ids = [];
if ( preg_match_all( '~<h2[^>]*\\bid\\s*=\\s*["\\']([^"\\']+)["\\']~i', $rendered_content, $found_ids ) ) {
  $existing_heading_ids = $found_ids[1];
}
$rendered_content""",1)
s=s.replace("function( $match ) use ( &$sections )", "function( $match ) use ( &$sections, &$existing_heading_ids )",1)
s=s.replace("""  $id = 'gn-section-' . ( count( $sections ) + 1 );
  if ( preg_match""", """  $id = 'gn-toc-' . get_the_ID() . '-' . ( count( $sections ) + 1 );
  while ( in_array( $id, $existing_heading_ids, true ) ) $id .= '-2';
  if ( preg_match""",1)
s=s.replace("""  $sections[] = [ 'id' => $id, 'label' => $label ];
  return '<h2'""", """  $sections[] = [ 'id' => $id, 'label' => $label ];
  $existing_heading_ids[] = $id;
  return '<h2'""",1)
post.write_text(s,encoding='utf-8')
place=base/'single-gd_place.candidate.php'
s=place.read_text(encoding='utf-8')
s=s.replace("$image_id   = get_post_thumbnail_id( $post_id );", "$image_id   = get_post_thumbnail_id( $post_id );\n$has_valid_location = is_numeric( $lat ) && is_numeric( $lng ) && (float) $lat > 29 && (float) $lat < 34 && (float) $lng > 34 && (float) $lng < 36;")
s=s.replace("is_numeric( $lat ) && is_numeric( $lng ) )", "$has_valid_location )")
s=s.replace("! ( is_numeric( $lat ) && is_numeric( $lng ) )", "! $has_valid_location")
place.write_text(s,encoding='utf-8')
css=base/'gonorth.candidate.css'
s=css.read_text(encoding='utf-8').replace('outline: 3px solid var(--gn-yellow); outline-offset: 3px; border-radius: 3px;', 'outline: 3px solid var(--gn-green-dark); outline-offset: 3px; box-shadow: 0 0 0 5px #fff; border-radius: 3px;')
css.write_text(s,encoding='utf-8')
print('review fixes applied')
