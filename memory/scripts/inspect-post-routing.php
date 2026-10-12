<?php
$url = 'https://gonorth.co.il/agamon-hahula-guide/';
echo wp_json_encode(array(
  'url_to_postid' => url_to_postid($url),
  'get_permalink' => get_permalink(432),
  'status' => get_post_status(432),
  'type' => get_post_type(432),
  'is_public' => is_post_publicly_viewable(432),
  'same_slug' => get_posts(array('name' => 'agamon-hahula-guide', 'post_type' => 'any', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids')),
));
