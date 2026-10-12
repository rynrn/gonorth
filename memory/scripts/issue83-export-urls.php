<?php
$ids = get_posts(array('post_type' => array('gd_place','post','page'), 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids'));
$urls = array();
foreach ($ids as $id) $urls[] = array('id'=>(int)$id,'url'=>get_permalink($id));
echo wp_json_encode($urls, JSON_UNESCAPED_UNICODE) . PHP_EOL;