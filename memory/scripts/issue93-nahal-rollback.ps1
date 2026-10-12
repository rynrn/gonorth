param([switch]$Apply)
$ErrorActionPreference='Stop'
$idFile='memory/data/2026-10-06-issue-93-nahal-id.txt'
if(-not (Test-Path -LiteralPath $idFile)){throw 'ID file absent'}
$id=(Get-Content -LiteralPath $idFile -Raw).Trim()
if($id -notmatch '^\d+$'){throw 'Invalid ID'}
$raw=ssh -o BatchMode=yes gonorth "wp post get $id --format=json --path=/var/www/gonorth --allow-root"
if($LASTEXITCODE -ne 0){throw 'Listing missing'}
$p=$raw|ConvertFrom-Json
if($p.ID -ne [int]$id -or $p.post_type -ne 'gd_place' -or $p.post_status -ne 'pending' -or $p.post_title -ne 'שמורת טבע נחל המערות'){throw 'Listing guard failed'}
$afterFile='memory/data/2026-10-06-issue-93-nahal-after-post.json'
if(Test-Path -LiteralPath $afterFile){
  $expected=Get-Content -LiteralPath $afterFile -Raw|ConvertFrom-Json
  if($p.post_content -ne $expected.post_content -or $p.post_excerpt -ne $expected.post_excerpt -or $p.post_modified_gmt -ne $expected.post_modified_gmt){throw 'Listing changed since snapshot'}
}elseif($p.post_content -or $p.post_excerpt){throw 'Missing snapshot of changed listing'}
$flag=(ssh -o BatchMode=yes gonorth "wp post meta get $id _gonorth_hide_driving_navigation --path=/var/www/gonorth --allow-root").Trim()
if($flag -ne '1'){throw 'Navigation flag changed'}
$cfg=Get-Content -LiteralPath '.claude/.mcp.json' -Raw|ConvertFrom-Json
$e=$cfg.mcpServers.'wordpress-mcp'.env
$h=@{Authorization='Basic '+[Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($e.WP_API_USERNAME+':'+$e.WP_API_PASSWORD))}
$geo=Invoke-RestMethod "https://gonorth.co.il/wp-json/geodir/v2/places/$($id)?context=edit" -Headers $h -TimeoutSec 30
if($geo.id -ne [int]$id -or $geo.status -ne 'pending' -or $geo.city -ne '' -or $geo.country -ne 'ישראל' -or $geo.region -ne 'צפון' -or $geo.latitude -ne '32.6712039' -or $geo.longitude -ne '34.9652355'){throw 'Location guard failed'}
$mediaId=$null
$mediaFile='memory/data/2026-10-06-issue-93-nahal-media-id.txt'
if(Test-Path -LiteralPath $mediaFile){
  $mediaId=(Get-Content -LiteralPath $mediaFile -Raw).Trim()
  if($mediaId -notmatch '^\d+$'){throw 'Invalid media ID'}
  $mraw=ssh -o BatchMode=yes gonorth "wp post get $mediaId --format=json --path=/var/www/gonorth --allow-root"
  if($LASTEXITCODE -ne 0){throw 'Media missing'}
  $m=$mraw|ConvertFrom-Json
  if($m.ID -ne [int]$mediaId -or $m.post_type -ne 'attachment' -or $m.post_parent -ne [int]$id -or $m.post_mime_type -ne 'image/jpeg'){throw 'Media guard failed'}
  $thumb=(ssh -o BatchMode=yes gonorth "wp post meta get $id _thumbnail_id --path=/var/www/gonorth --allow-root").Trim()
  if($thumb -ne $mediaId){throw 'Thumbnail changed'}
  $attached=(ssh -o BatchMode=yes gonorth "wp post meta get $mediaId _wp_attached_file --path=/var/www/gonorth --allow-root").Trim()
  $saved=(Get-Content -LiteralPath 'memory/data/2026-10-06-issue-93-nahal-media-path.txt' -Raw).Trim()
  if($attached -ne $saved -or $attached -notmatch '^issue93-licensed/'){throw 'Media path changed'}
  $hash=(ssh -o BatchMode=yes gonorth "sha256sum /var/www/gonorth/wp-content/uploads/$attached").Split(' ')[0].ToUpperInvariant()
  $expectedHash=(Get-Content -LiteralPath 'memory/data/2026-10-06-issue-93-nahal-media-hash.txt' -Raw).Trim().ToUpperInvariant()
  if($hash -ne $expectedHash){throw 'Media bytes changed'}
}
if(-not $Apply){Write-Output "Guard passed for pending listing $id; no changes made";exit 0}
ssh -o BatchMode=yes gonorth "wp post delete $id --force --path=/var/www/gonorth --allow-root" | Out-Null
if($LASTEXITCODE -ne 0){throw 'Listing deletion failed'}
if($mediaId){
  ssh -o BatchMode=yes gonorth "wp media delete $mediaId --force --path=/var/www/gonorth --allow-root" | Out-Null
  if($LASTEXITCODE -ne 0){throw 'Media deletion failed'}
}
Write-Output "Deleted exact pending listing $id and its media"

