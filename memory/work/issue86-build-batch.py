from pathlib import Path
src=Path(r'C:\dev\gonorth\memory\work\issue86-functions-before-batch.php').read_bytes()
start=src.find(b'// #86 canary:')
end=src.find('// ─── Force RTL / Hebrew ───'.encode('utf-8'))
if start<0 or end<0 or end<=start: raise SystemExit('markers missing')
block='''// #86: keep commerce assets on all unverified and future editorial posts.
add_action( 'wp_enqueue_scripts', 'gonorth_optimize_verified_editorial_assets', PHP_INT_MAX );
add_action( 'wp_footer', 'gonorth_optimize_verified_editorial_assets', 19 );
function gonorth_verified_editorial_without_commerce() {
    if ( is_page( 436 ) ) {
        return true;
    }
    if ( ! is_singular( 'post' ) ) {
        return false;
    }

    $post = get_queried_object();
    if ( ! $post instanceof WP_Post || ! in_array( (int) $post->ID, [ __IDS__ ], true ) ) {
        return false;
    }
    if ( get_post_meta( $post->ID, 'gn_keep_woocommerce_assets', true ) ) {
        return false;
    }

    // A later editorial update with commerce markup keeps the plugin assets.
    $content = strtolower( (string) $post->post_content );
    foreach ( [ 'woocommerce', 'wc-block', 'add_to_cart', 'product_page', '[products', '[product', '<form', '<iframe', '<script' ] as $marker ) {
        if ( false !== strpos( $content, $marker ) ) {
            return false;
        }
    }
    return true;
}
function gonorth_optimize_verified_editorial_assets() {
    if ( ! gonorth_verified_editorial_without_commerce() ) {
        return;
    }

    foreach ( [
        'woocommerce-layout',
        'woocommerce-smallscreen',
        'woocommerce-general',
        'wc-blocks-style',
    ] as $handle ) {
        wp_dequeue_style( $handle );
    }

    foreach ( [
        'wc-jquery-blockui',
        'wc-add-to-cart',
        'wc-js-cookie',
        'woocommerce',
        'sourcebuster-js',
        'wc-order-attribution',
    ] as $handle ) {
        wp_dequeue_script( $handle );
    }
}

'''
for tag,ids in [('stage1','431, 432, 517'),('all25','517, 516, 515, 469, 464, 448, 447, 443, 433, 432, 431, 430, 429, 428, 427, 270, 269, 268, 267, 262, 98, 97, 96, 95, 94')]:
    candidate=src[:start]+block.replace('__IDS__',ids).encode('utf-8')+src[end:]
    dest=Path(r'C:\dev\gonorth\memory\work\issue86-functions-'+tag+'.php')
    dest.write_bytes(candidate)
    print(tag,len(candidate))
