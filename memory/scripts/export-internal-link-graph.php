<?php
/**
 * Read-only WP-CLI export for the GoNorth internal-link audit.
 * Run with: wp eval-file /tmp/export-internal-link-graph.php --path=/var/www/gonorth --allow-root
 */
$query = new WP_Query(array(
    'post_type' => array('post', 'page', 'gd_place'),
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'ID',
    'order' => 'ASC',
    'no_found_rows' => true,
    'update_post_meta_cache' => false,
    'update_post_term_cache' => true,
));
$rows = array();
foreach ($query->posts as $post) {
    $terms = array();
    if ('gd_place' === $post->post_type) {
        $term_names = wp_get_post_terms($post->ID, 'gd_placecategory', array('fields' => 'names'));
        if (!is_wp_error($term_names)) {
            $terms = array_values($term_names);
        }
    }
    $rows[] = array(
        'id' => (int) $post->ID,
        'type' => $post->post_type,
        'title' => html_entity_decode(get_the_title($post), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        'slug' => $post->post_name,
        'url' => get_permalink($post),
        'content' => $post->post_content,
        'categories' => $terms,
        'modified_gmt' => $post->post_modified_gmt,
    );
}
echo wp_json_encode(array(
    'generated_at_utc' => gmdate('c'),
    'count' => count($rows),
    'rows' => $rows,
), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
