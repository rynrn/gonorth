from pathlib import Path
p=Path('memory/work/2026-10-06-issue-88/build.py')
s=p.read_text(encoding='utf-8')
s=s.replace(r'~<figure\b[^>]*>[^<]*(?:<(?!/figure>)[^>]*>[^<]*)*<img\b[^>]*\bwp-image-',r'~<figure\b[^>]*>(?:(?!</figure>).)*?<img\b[^>]*\bwp-image-')
old="""  $id = 'gn-section-' . ( count( $sections ) + 1 );
  $sections[] = [ 'id' => $id, 'label' => $label ];
  if ( preg_match( '~\\bid\\s*=~i', $match[1] ) ) return $match[0];
  return '<h2' . $match[1] . ' id="' . esc_attr( $id ) . '">' . $match[2] . '</h2>';"""
new="""  $id = 'gn-section-' . ( count( $sections ) + 1 );
  if ( preg_match( '~\\bid\\s*=\\s*["\\']([^"\\']+)["\\']~i', $match[1], $existing ) ) {
    $id = $existing[1];
    $sections[] = [ 'id' => $id, 'label' => $label ];
    return $match[0];
  }
  $sections[] = [ 'id' => $id, 'label' => $label ];
  return '<h2' . $match[1] . ' id="' . esc_attr( $id ) . '">' . $match[2] . '</h2>';"""
assert old in s
s=s.replace(old,new)
p.write_text(s,encoding='utf-8')
