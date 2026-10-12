<?php
$ids = get_posts( array(
    'post_type' => 'gd_place',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'fields' => 'ids',
) );
$rows = array();
foreach ( $ids as $id ) {
    $region_terms = wp_get_post_terms( $id, 'gn_region' );
    $category_terms = wp_get_post_terms( $id, 'gd_placecategory' );
    $rows[] = array(
        'id' => (int) $id,
        'title' => get_the_title( $id ),
        'url' => get_permalink( $id ),
        'regions' => is_wp_error( $region_terms ) ? array() : array_map( static function ( $term ) {
            return array( 'id' => (int) $term->term_id, 'slug' => $term->slug, 'name' => $term->name );
        }, $region_terms ),
        'categories' => is_wp_error( $category_terms ) ? array() : array_map( static function ( $term ) {
            return array( 'id' => (int) $term->term_id, 'slug' => $term->slug, 'name' => $term->name );
        }, $category_terms ),
    );
}
usort( $rows, static function ( $a, $b ) { return $a['id'] <=> $b['id']; } );
echo wp_json_encode( $rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
