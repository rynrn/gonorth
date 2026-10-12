<?php
wp_set_current_user(1);
$post_id = 518;
$_POST['gn_correction_queue_nonce'] = wp_create_nonce('gn_correction_queue_' . $post_id);
$_POST['gn_correction_state'] = 'reviewing';
$_POST['gn_correction_owner'] = '1';
GoNorth_Corrections::save_queue_meta($post_id);
echo wp_json_encode(array('state'=>get_post_meta($post_id,'_gn_workflow_status',true),'first_review_recorded'=>(bool)get_post_meta($post_id,'_gn_first_triaged_at',true)), JSON_UNESCAPED_UNICODE) . PHP_EOL;