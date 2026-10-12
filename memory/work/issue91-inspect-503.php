<?php
$post_id = 503;
$images = function_exists('geodir_get_images') ? geodir_get_images($post_id) : array();
$out = array(
    'post_id' => $post_id,
    'thumbnail_id' => get_post_thumbnail_id($post_id),
    'images' => array_map(function($image) {
        return array(
            'id' => isset($image->ID) ? $image->ID : null,
            'type' => isset($image->type) ? $image->type : null,
            'file' => isset($image->file) ? $image->file : null,
            'title' => isset($image->title) ? $image->title : null,
            'caption' => isset($image->caption) ? $image->caption : null,
            'menu_order' => isset($image->menu_order) ? $image->menu_order : null,
            'featured' => isset($image->featured) ? $image->featured : null,
        );
    }, is_array($images) ? $images : array()),
);
echo wp_json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

