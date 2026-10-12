param([switch]$Apply)
$ErrorActionPreference='Stop'
$idFile='memory/data/2026-10-06-issue-93-madatech-id.txt'
if(-not (Test-Path -LiteralPath $idFile)){throw 'No created listing ID; nothing to roll back'}
$id=(Get-Content -LiteralPath $idFile -Raw).Trim()
if($id -notmatch '^\d+$'){throw 'Invalid listing ID'}
$postRaw=ssh -o BatchMode=yes gonorth "wp post get $id --format=json --path=/var/www/gonorth --allow-root"
if($LASTEXITCODE -ne 0){throw 'Created listing missing; stop'}
$post=$postRaw|ConvertFrom-Json
if($post.ID -ne [int]$id -or $post.post_type -ne 'gd_place' -or $post.post_status -ne 'pending' -or $post.post_title -ne 'מדעטק — המוזיאון הלאומי למדע, טכנולוגיה וחלל'){throw 'Unexpected listing; stop'}
$afterPost='memory/data/2026-10-06-issue-93-madatech-after-post.json'
if(Test-Path -LiteralPath $afterPost){$snapshot=Get-Content -LiteralPath $afterPost -Raw|ConvertFrom-Json; if($post.post_modified_gmt -ne $snapshot.post_modified_gmt -or $post.post_content -ne $snapshot.post_content){throw 'Listing changed since snapshot; stop'}}
$mediaId=$null
$mediaFile='memory/data/2026-10-06-issue-93-madatech-media-id.txt'
if(Test-Path -LiteralPath $mediaFile){
  $mediaId=(Get-Content -LiteralPath $mediaFile -Raw).Trim()
  if($mediaId -notmatch '^\d+$'){throw 'Invalid media ID'}
  $mediaRaw=ssh -o BatchMode=yes gonorth "wp post get $mediaId --format=json --path=/var/www/gonorth --allow-root"
  if($LASTEXITCODE -ne 0){throw 'Created attachment missing; stop'}
  $media=$mediaRaw|ConvertFrom-Json
  if($media.ID -ne [int]$mediaId -or $media.post_parent -ne [int]$id -or $media.post_mime_type -ne 'image/jpeg'){throw 'Unexpected attachment; stop'}
  $thumb=(ssh -o BatchMode=yes gonorth "wp post meta get $id _thumbnail_id --path=/var/www/gonorth --allow-root").Trim()
  if($thumb -ne $mediaId){throw 'Featured image changed; stop'}
}
$flag=(ssh -o BatchMode=yes gonorth "wp post meta get $id _gonorth_hide_driving_navigation --path=/var/www/gonorth --allow-root").Trim()
if($flag -ne '1'){throw 'Navigation flag changed; stop'}
$cat=(ssh -o BatchMode=yes gonorth "wp post term list $id gd_placecategory --field=slug --path=/var/www/gonorth --allow-root").Trim()
$regionTerm=(ssh -o BatchMode=yes gonorth "wp post term list $id gn_region --field=slug --path=/var/www/gonorth --allow-root").Trim()
if($cat -ne 'atraktziot-main' -or $regionTerm -ne 'carmel'){throw 'Listing terms changed; stop'}
$cfg=Get-Content -LiteralPath '.claude/.mcp.json' -Raw|ConvertFrom-Json
$e=$cfg.mcpServers.'wordpress-mcp'.env
$headers=@{Authorization='Basic '+[Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($e.WP_API_USERNAME+':'+$e.WP_API_PASSWORD))}
$geo=Invoke-RestMethod "https://gonorth.co.il/wp-json/geodir/v2/places/$($id)?context=edit" -Headers $headers -TimeoutSec 30
if($geo.status -ne 'pending' -or $geo.latitude -ne '32.8099166' -or $geo.longitude -ne '34.9961485' -or $geo.city -ne 'חיפה' -or $geo.street){throw 'Location changed; stop'}
if($mediaId){
  $mediaBaseline=Get-Content -LiteralPath 'memory/data/2026-10-06-issue-93-madatech-after-media.json' -Raw|ConvertFrom-Json
  if($media.post_excerpt -ne $mediaBaseline.post_excerpt){throw 'Media credit changed; stop'}
  $file=(ssh -o BatchMode=yes gonorth "wp post meta get $mediaId _wp_attached_file --path=/var/www/gonorth --allow-root").Trim()
  if($file -ne 'issue93-licensed/madatech-oshri-2026-scaled.jpg'){throw 'Media path changed; stop'}
  $hash=(ssh -o BatchMode=yes gonorth 'sha256sum /var/www/gonorth/wp-content/uploads/issue93-licensed/madatech-oshri-2026-scaled.jpg').Split(' ')[0]
  $expectedHash=(Get-Content -LiteralPath 'memory/data/2026-10-06-issue-93-madatech-after-media-hash.txt' -Raw).Trim(); if($hash -ne $expectedHash){throw 'Photo bytes changed; stop'}
}if(-not $Apply){Write-Output "Guard passed for pending $id; no changes made.";exit 0}
ssh -o BatchMode=yes gonorth "wp post delete $id --force --path=/var/www/gonorth --allow-root"
if($LASTEXITCODE -ne 0){throw 'Listing deletion failed'}
if($mediaId){ssh -o BatchMode=yes gonorth "wp media delete $mediaId --force --path=/var/www/gonorth --allow-root";if($LASTEXITCODE -ne 0){throw 'Attachment deletion failed'}}
Write-Output "Removed exact pending listing $id and its attachment. Inspect issue93-licensed/madatech-oshri-2026* for orphan files."


