<?php
if (!defined('WP_CLI') || !WP_CLI) exit(1);
$id=490; $exterior=532; $interior=533; $old_thumb=492;
$before=json_decode(file_get_contents('/home/claude/issue91-490-before.json'),true);
$post=get_post($id); $mode=$args[0]??'check';
if (!$before || !$post || $post->post_type!=='gd_place' || (int)$before['ID']!==$id) WP_CLI::error('Invalid snapshot');
if ($mode==='rollback') {
 $after=json_decode(@file_get_contents('/home/claude/issue91-490-after.json'),true);
 if (!$after || hash('sha256',$post->post_content)!==$after['content_sha256'] || (int)get_post_thumbnail_id($id)!==$exterior) WP_CLI::error('Rollback guard failed');
 $r=wp_update_post(['ID'=>$id,'post_content'=>$before['post_content']],true);
 if(is_wp_error($r)) WP_CLI::error($r->get_error_message());
 set_post_thumbnail($id,$old_thumb); WP_CLI::success('Original content and thumbnail restored'); return;
}
if ($mode==='check') { WP_CLI::log(wp_json_encode(['sha256'=>hash('sha256',$post->post_content),'thumbnail'=>(int)get_post_thumbnail_id($id),'exterior_url'=>wp_get_attachment_url($exterior),'interior_url'=>wp_get_attachment_url($interior)],JSON_UNESCAPED_SLASHES)); return; }
if ($mode!=='apply') WP_CLI::error('Invalid mode');
if (hash('sha256',$post->post_content)!==hash('sha256',$before['post_content']) || (int)get_post_thumbnail_id($id)!==$old_thumb) WP_CLI::error('Pre-change guard failed');
foreach ([$exterior,$interior] as $a) if(get_post_type($a)!=='attachment' || get_post_mime_type($a)!=='image/webp' || !wp_get_attachment_url($a)) WP_CLI::error('Imported media missing');
$photos=[
 $exterior=>['alt'=>'מבנה חיצוני של מלון גליליון עם מרפסות ועצים','label'=>'מבנה אירוח במלון גליליון — צילום משנת 2021 לפי נתוני המקור; אין בו תיעוד חדרים או מצב נוכחי','author'=>'ישראל פרקר (Israel Preker); ייחוס: Heritage Conservation Outside The City / PikiWiki Israel','source'=>'https://commons.wikimedia.org/wiki/File:PikiWiki_Israel_78859_galilion_hotel.jpg','license'=>'CC BY 2.5','license_url'=>'https://creativecommons.org/licenses/by/2.5/'],
 $interior=>['alt'=>'מבנה מלון גליליון לצד מדשאה והרים ברקע','label'=>'מבנה מלון גליליון והשטח הירוק סביבו — צילום משנת 2021 לפי נתוני המקור; אין בו תיעוד חדרים או מצב נוכחי','author'=>'ישראל פרקר (Israel Preker); ייחוס: Heritage Conservation Outside The City / PikiWiki Israel','source'=>'https://commons.wikimedia.org/wiki/File:PikiWiki_Israel_78866_galilion_hotel.jpg','license'=>'CC BY 2.5','license_url'=>'https://creativecommons.org/licenses/by/2.5/']
];
$figures='';
foreach($photos as $a=>$d){
 update_post_meta($a,'_wp_attachment_image_alt',$d['alt']);
 $credit=sprintf('%s. צילום: %s · <a href="%s">מקור התמונה</a> · רישיון: <a href="%s">%s</a> · עיבוד: הוקטן והומר ל־WebP.',esc_html($d['label']),esc_html($d['author']),esc_url($d['source']),esc_url($d['license_url']),esc_html($d['license']));
 wp_update_post(['ID'=>$a,'post_excerpt'=>wp_strip_all_tags($credit),'post_content'=>$credit]);
 $img=wp_get_attachment_image($a,'large',false,['alt'=>$d['alt'],'loading'=>'lazy','decoding'=>'async','sizes'=>'(max-width: 700px) 100vw, 50vw']);
 $figures.='<figure style="margin:0;min-width:0">'.$img.'<figcaption style="font-size:.85em;line-height:1.5;margin-block-start:.5em">'.$credit.'</figcaption></figure>';
}
$gallery='<section class="gn-place-gallery" aria-labelledby="gn-gallery-490" dir="rtl" style="margin-block:2em"><h2 id="gn-gallery-490">תמונות חוץ של מלון גליליון</h2><p>התמונות מתעדות את מבני המלון וסביבתם בשנת 2021 לפי נתוני המקור. הן אינן מציגות חדרים, ואינן ראיה למראה המלון או לשירותיו כיום.</p><div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,280px),1fr));gap:1.5em">'.$figures.'</div></section>';
$r=wp_update_post(['ID'=>$id,'post_content'=>rtrim($before['post_content'])."\n".$gallery],true);
if(is_wp_error($r)) WP_CLI::error($r->get_error_message());
if(!set_post_thumbnail($id,$exterior)) WP_CLI::error('Could not set thumbnail; run rollback');
file_put_contents('/home/claude/issue91-490-after.json',wp_json_encode(['content_sha256'=>hash('sha256',get_post($id)->post_content),'thumbnail_id'=>$exterior,'interior_id'=>$interior,'applied_at_utc'=>gmdate('c')],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
WP_CLI::success('Galilion gallery applied');


