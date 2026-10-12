<?php
if (count($args) !== 3) { fwrite(STDERR, "Usage: expected_sha256 content_file thumbnail_id\n"); exit(2); }
$id = 437;
$expected = strtolower($args[0]);
$file = $args[1];
$thumbnail_id = (int) $args[2];
$post = get_post($id);
if (!$post || 'draft' !== $post->post_status || 'page' !== $post->post_type) { fwrite(STDERR, "Unexpected post state\n"); exit(3); }
if (hash('sha256', $post->post_content) !== $expected) { fwrite(STDERR, "Concurrent content change\n"); exit(4); }
$content = file_get_contents($file);
if (false === $content) { fwrite(STDERR, "Cannot read content\n"); exit(5); }
$result = wp_update_post(array('ID'=>$id,'post_title'=>'עגלות קפה ובתי קפה בצפון','post_name'=>'coffee-carts-cafes-north-israel','post_content'=>$content,'post_status'=>'publish'), true);
if (is_wp_error($result)) { fwrite(STDERR, $result->get_error_message()."\n"); exit(6); }
update_post_meta($id, '_yoast_wpseo_title', 'עגלות קפה ובתי קפה בצפון | כיוון צפון');
update_post_meta($id, '_yoast_wpseo_metadesc', 'מדריך עגלות קפה ובתי קפה בצפון לפי אזור ואופי העצירה, עם השוואה שימושית, מקומות פעילים וטיפים לבדיקת שעות, צל ונגישות.');
update_post_meta($id, '_yoast_wpseo_focuskw', 'עגלות קפה בצפון');
set_post_thumbnail($id, $thumbnail_id);
clean_post_cache($id);
$saved = get_post($id);
$ok = $saved && 'publish' === $saved->post_status && 'coffee-carts-cafes-north-israel' === $saved->post_name && hash('sha256', $saved->post_content) === hash('sha256', $content) && get_post_thumbnail_id($id) === $thumbnail_id;
if (!$ok) { fwrite(STDERR, "Post-write verification failed\n"); exit(7); }
echo wp_json_encode(array('id'=>$id,'status'=>$saved->post_status,'slug'=>$saved->post_name,'url'=>get_permalink($id),'sha256'=>hash('sha256',$saved->post_content),'thumbnail_id'=>get_post_thumbnail_id($id),'verified'=>true));