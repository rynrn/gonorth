from pathlib import Path
src=Path(r'C:\dev\gonorth\memory\work\issue86-functions-before-guard.php').read_bytes()
old=b'''    if ( is_page( 436 ) ) {
        return true;
    }
    if ( ! is_singular( 'post' ) ) {
        return false;
    }

    $post = get_queried_object();
    if ( ! $post instanceof WP_Post || ! in_array( (int) $post->ID, [ 517, 516, 515, 469, 464, 448, 447, 443, 433, 432, 431, 430, 429, 428, 427, 270, 269, 268, 267, 262, 98, 97, 96, 95, 94 ], true ) ) {
        return false;
    }
'''
new=b'''    if ( ! is_page( 436 ) && ! is_singular( 'post' ) ) {
        return false;
    }

    $post = get_queried_object();
    if ( ! $post instanceof WP_Post ) {
        return false;
    }
    if ( ! is_page( 436 ) && ! in_array( (int) $post->ID, [ 517, 516, 515, 469, 464, 448, 447, 443, 433, 432, 431, 430, 429, 428, 427, 270, 269, 268, 267, 262, 98, 97, 96, 95, 94 ], true ) ) {
        return false;
    }
'''
if src.count(old)!=1:raise SystemExit('expected-condition-not-unique')
out=Path(r'C:\dev\gonorth\memory\work\issue86-functions-guard-fix.php')
out.write_bytes(src.replace(old,new))
print('candidate',out.stat().st_size)
