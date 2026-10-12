<?php
if (!defined('WP_CLI') || !WP_CLI) exit(1);
$id=503; $tank=527; $stream=528;
$before=json_decode(file_get_contents('/home/claude/issue91-503-before.json'),true);
$post=get_post($id); $mode=$args[0]??'check';
if (!$before || !$post || $post->post_type!=='gd_place' || (int)$before['ID']!==$id) WP_CLI::error('Invalid snapshot');
if ($mode==='rollback') {
 $after=json_decode(@file_get_contents('/home/claude/issue91-503-after.json'),true);
 if (!$after || hash('sha256',$post->post_content)!==$after['content_sha256'] || (int)get_post_thumbnail_id($id)!==$tank) WP_CLI::error('Rollback guard failed');
 $r=wp_update_post(['ID'=>$id,'post_content'=>$before['post_content']],true);
 if (is_wp_error($r)) WP_CLI::error($r->get_error_message());
 set_post_thumbnail($id,509); WP_CLI::success('Original content and thumbnail restored'); return;
}
if ($mode==='check') { WP_CLI::log(wp_json_encode(['sha256'=>hash('sha256',$post->post_content),'thumbnail'=>(int)get_post_thumbnail_id($id),'tank_url'=>wp_get_attachment_url($tank),'stream_url'=>wp_get_attachment_url($stream)],JSON_UNESCAPED_SLASHES)); return; }
if ($mode!=='apply') WP_CLI::error('Invalid mode');
if (hash('sha256',$post->post_content)!==hash('sha256',$before['post_content']) || (int)get_post_thumbnail_id($id)!==509) WP_CLI::error('Pre-change guard failed');
foreach ([$tank,$stream] as $a) if(get_post_type($a)!=='attachment' || get_post_mime_type($a)!=='image/webp' || !wp_get_attachment_url($a)) WP_CLI::error('Imported media missing');
$photos=[
 $tank=>['alt'=>'הטנק הסורי ההפוך בערוץ הבניאס','label'=>'הטנק הסורי ההפוך בערוץ הבניאס','author'=>'Shayshal2','source'=>'https://commons.wikimedia.org/wiki/File:SyrianTank_in_Banias_river_3.jpg','license'=>'CC BY-SA 4.0','license_url'=>'https://creativecommons.org/licenses/by-sa/4.0/'],
 $stream=>['alt'=>'ערוץ נחל הבניאס סמוך לטנק הסורי','label'=>'נחל הבניאס סמוך לטנק הסורי','author'=>'יותם נחום (Yotam Nachum)','source'=>'https://commons.wikimedia.org/wiki/File:Syrian_Tank_Stream.jpg','license'=>'CC0 1.0','license_url'=>'https://creativecommons.org/publicdomain/zero/1.0/']
];
$figures='';
foreach($photos as $a=>$d){
 update_post_meta($a,'_wp_attachment_image_alt',$d['alt']);
 $credit=sprintf('%s. צילום: %s · <a href="%s">מקור התמונה</a> · רישיון: <a href="%s">%s</a> · עיבוד: הוקטנה והומרה ל־WebP.',esc_html($d['label']),esc_html($d['author']),esc_url($d['source']),esc_url($d['license_url']),esc_html($d['license']));
 wp_update_post(['ID'=>$a,'post_excerpt'=>wp_strip_all_tags($credit),'post_content'=>$credit]);
 $img=wp_get_attachment_image($a,'large',false,['alt'=>$d['alt'],'loading'=>'lazy','decoding'=>'async','sizes'=>'(max-width: 700px) 100vw, 50vw']);
 $figures.='<figure style="margin:0;min-width:0">'.$img.'<figcaption style="font-size:.85em;line-height:1.5;margin-block-start:.5em">'.$credit.'</figcaption></figure>';
}
$gallery='<section class="gn-place-gallery" aria-labelledby="gn-gallery-503" dir="rtl" style="margin-block:2em"><h2 id="gn-gallery-503">תמונות מהמקום</h2><p>צילום הטנק וצילום הנחל הסמוך ממחישים את המקום. התמונות אינן מציגות כניסה או חניה; <a href="https://www.parks.org.il/new/syrian-tank/">בדקו את מידע המסלול העדכני באתר רשות הטבע והגנים</a> לפני יציאה.</p><div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,280px),1fr));gap:1.5em">'.$figures.'</div></section>';
$r=wp_update_post(['ID'=>$id,'post_content'=>rtrim($before['post_content'])."\n".$gallery],true);
if(is_wp_error($r)) WP_CLI::error($r->get_error_message());
if(!set_post_thumbnail($id,$tank)) WP_CLI::error('Could not set thumbnail; run rollback');
file_put_contents('/home/claude/issue91-503-after.json',wp_json_encode(['content_sha256'=>hash('sha256',get_post($id)->post_content),'thumbnail_id'=>$tank,'stream_id'=>$stream,'applied_at_utc'=>gmdate('c')],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
WP_CLI::success('Gallery applied');

