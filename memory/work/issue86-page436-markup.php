<?php
$post = get_post( 436 );
$needles = array( 'woocommerce', 'wc-block', 'add_to_cart', 'product_page', '[products', '[product', '<form', '<iframe', '<script' );
$found = array();
if ( $post ) {
    $content = strtolower( (string) $post->post_content );
    foreach ( $needles as $needle ) {
        if ( false !== strpos( $content, $needle ) ) {
            $found[] = $needle;
        }
    }
}
echo json_encode( array( 'exists' => (bool) $post, 'found' => $found ) );
