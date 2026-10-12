<?php
require_once '/var/www/gonorth/wp-load.php';
$key = get_option('gonorth_google_server_api_key');
if (!$key) { fwrite(STDERR, "Missing server Places API key.\n"); exit(2); }
$targets = [
  202 => ['center'=>[33.192,35.7518], 'queries'=>['ג׳חנון ברמה אודם',"ג'חנון ברמה אודם",'Jachnun Barama Odem','Jachnun Odem','חלהוולאוו עגלת קפה אודם','רעות עגלת קפה אודם']],
  228 => ['center'=>[32.783225,35.636237], 'queries'=>['קופיש עין גב','Coffish Ein Gev','Coffish נמל עין גב','קופי פיש עין גב','עגלת קפה עין גב 0544939011']],
  357 => ['center'=>[32.854068,35.752574], 'queries'=>['F Ranch נטור','אף ראנץ נטור','חוות F נטור','חוות סוסים נטור 0584559312','רכיבה על סוסים נטור']],
  409 => ['center'=>[32.824808,35.765777], 'queries'=>['מרכז מורשת גוש קטיף אבני איתן','גוש קטיף בגולן ע״ר','גוש קטיף בגולן אבני איתן','Gush Katif Heritage Center Avnei Eitan','04-6763535']]
];
function norm($p) { return [
  'id'=>$p['id']??null,'name'=>$p['displayName']['text']??null,'address'=>$p['formattedAddress']??null,
  'latitude'=>$p['location']['latitude']??null,'longitude'=>$p['location']['longitude']??null,
  'rating'=>$p['rating']??null,'review_count'=>$p['userRatingCount']??null,'business_status'=>$p['businessStatus']??null,
  'website'=>$p['websiteUri']??null,'phone'=>$p['nationalPhoneNumber']??null,'types'=>$p['types']??[]
]; }
$out=[];
foreach($targets as $post_id=>$target){
  [$lat,$lng]=$target['center']; $row=['post_id'=>$post_id,'queries'=>[]];
  foreach($target['queries'] as $query){
    $r=wp_remote_post('https://places.googleapis.com/v1/places:searchText',[
      'timeout'=>20,
      'headers'=>['Content-Type'=>'application/json','X-Goog-Api-Key'=>$key,'X-Goog-FieldMask'=>'places.id,places.displayName,places.formattedAddress,places.location,places.rating,places.userRatingCount,places.businessStatus,places.websiteUri,places.nationalPhoneNumber,places.types'],
      'body'=>wp_json_encode(['textQuery'=>$query,'languageCode'=>'he','regionCode'=>'IL','pageSize'=>10,'locationBias'=>['circle'=>['center'=>['latitude'=>$lat,'longitude'=>$lng],'radius'=>10000.0]]])
    ]);
    $e=['query'=>$query];
    if(is_wp_error($r)){$e['error']=$r->get_error_message();} else {
      $e['http_status']=wp_remote_retrieve_response_code($r); $b=json_decode(wp_remote_retrieve_body($r),true);
      $e['candidates']=array_map('norm',$b['places']??[]); if(isset($b['error']))$e['api_error']=$b['error'];
    }
    $row['queries'][]=$e; usleep(120000);
  }
  $out[]=$row;
}
echo wp_json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),"\n";
