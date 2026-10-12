<?php
if (!defined('ABSPATH')) { exit; }
$id = 191;
$post = get_post($id);
$thumbnail = get_post_thumbnail_id($id);
$record = [
    'ID' => $id,
    'title' => $post ? $post->post_title : null,
    'status' => get_post_status($id),
    'excerpt' => $post ? $post->post_excerpt : null,
    'content' => $post ? $post->post_content : null,
    'permalink' => get_permalink($id),
    'thumbnail_id' => $thumbnail,
    'thumbnail_url' => $thumbnail ? wp_get_attachment_url($thumbnail) : null,
    'categories' => wp_get_post_terms($id, 'gd_placecategory', ['fields' => 'slugs']),
    'regions' => wp_get_post_terms($id, 'gn_region', ['fields' => 'slugs']),
    'source_url' => get_post_meta($id, '_gonorth_verification_source_url', true),
    'source_type' => get_post_meta($id, '_gonorth_verification_source_type', true),
    'source_checked_at' => get_post_meta($id, '_gonorth_verification_checked_at', true),
    'data' => GoNorth_Place_Data::get($id),
];
echo wp_json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;
