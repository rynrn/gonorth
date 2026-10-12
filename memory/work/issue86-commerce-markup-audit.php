<?php
$posts = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => -1 ) );
$matches = array();
foreach ( $posts as $post ) {
    $raw = (string) $post->post_content;
    if ( preg_match( '/\\[(?:woocommerce_|products\\b|product_page\\b|add_to_cart\\b|add_to_cart_url\\b|product_category\\b|product_categories\\b)/i', $raw ) || strpos( $raw, '<!-- wp:woocommerce/' ) !== false ) {
        $matches[] = (int) $post->ID;
    }
}
echo json_encode( array( 'published_posts' => count( $posts ), 'commerce_markup_post_ids' => $matches ) );
