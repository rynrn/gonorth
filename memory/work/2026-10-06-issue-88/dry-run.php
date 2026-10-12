<?php
$post = get_post(464);
$content = apply_filters('the_content', $post->post_content);
$image_id = get_post_thumbnail_id(464);
$pattern = '~<figure\b[^>]*>(?:(?!</figure>).)*?<img\b[^>]*\bwp-image-' . (int) $image_id . '\b.*?</figure>~is';
$after = preg_replace($pattern, '', $content, 1, $removed);
$text = html_entity_decode(wp_strip_all_tags(strip_shortcodes($post->post_content)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
$words = preg_match_all('/[\p{L}\p{N}]+/u', $text);
echo wp_json_encode(['id'=>464,'featured'=>$image_id,'figure_removed'=>$removed,'images_before'=>substr_count($content,'<img'),'images_after'=>substr_count($after,'<img'),'h2_count'=>preg_match_all('~<h2\b~i',$after),'hebrew_word_count'=>$words,'minutes'=>max(1,(int)ceil($words/200))],JSON_UNESCAPED_UNICODE);
