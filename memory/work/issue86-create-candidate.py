from pathlib import Path
before=Path(r'C:\dev\gonorth\memory\work\issue86-functions-before.php')
after=Path(r'C:\dev\gonorth\memory\work\issue86-functions-candidate.php')
data=before.read_bytes()
marker=b'// \xe2\x94\x80\xe2\x94\x80\xe2\x94\x80 Force RTL / Hebrew \xe2\x94\x80\xe2\x94\x80\xe2\x94\x80'
block=b'''// #86 canary: this editorial guide has no WooCommerce UI or commerce shortcodes.
 // Keep the change scoped to one page while validating the shared assets.
add_action( 'wp_enqueue_scripts', 'gonorth_optimize_kayaks_editorial_assets', PHP_INT_MAX );
add_action( 'wp_footer', 'gonorth_optimize_kayaks_editorial_assets', 19 );
function gonorth_optimize_kayaks_editorial_assets() {
    if ( ! is_page( 436 ) ) {
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
if data.count(marker)!=1:
    raise SystemExit('marker-not-unique')
after.write_bytes(data.replace(marker,block+marker))
print('candidate-bytes',after.stat().st_size)
