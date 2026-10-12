param([switch]$Apply)
$ErrorActionPreference='Stop'
$baseline=Get-Content -LiteralPath 'memory/data/2026-10-06-issue-93-efendi-after-post.json' -Raw|ConvertFrom-Json
$currentRaw=ssh -o BatchMode=yes gonorth 'wp post get 546 --format=json --path=/var/www/gonorth --allow-root'
if($LASTEXITCODE -ne 0){throw 'Pending listing missing; stop rollback'}
$current=$currentRaw|ConvertFrom-Json
if($current.ID -ne 546 -or $current.post_status -ne 'pending' -or $current.post_title -ne 'מלון האפנדי — עכו' -or $current.post_modified_gmt -ne $baseline.post_modified_gmt -or $current.post_content -ne $baseline.post_content){throw 'Listing changed since snapshot; stop rollback'}
$thumb=ssh -o BatchMode=yes gonorth 'wp post meta get 546 _thumbnail_id --path=/var/www/gonorth --allow-root'
if($LASTEXITCODE -ne 0 -or $thumb.Trim() -ne '547'){throw 'Featured image changed; stop rollback'}
$mediaRaw=ssh -o BatchMode=yes gonorth 'wp post get 547 --format=json --path=/var/www/gonorth --allow-root'
if($LASTEXITCODE -ne 0){throw 'Media missing; stop rollback'}
$media=$mediaRaw|ConvertFrom-Json
$mediaBaseline=Get-Content -LiteralPath 'memory/data/2026-10-06-issue-93-efendi-after-media.json' -Raw|ConvertFrom-Json
if($media.ID -ne 547 -or $media.post_parent -ne 546 -or $media.post_mime_type -ne 'image/jpeg' -or $media.post_excerpt -ne $mediaBaseline.post_excerpt){throw 'Media changed; stop rollback'}
$cfg=Get-Content -LiteralPath '.claude/.mcp.json' -Raw|ConvertFrom-Json
$e=$cfg.mcpServers.'wordpress-mcp'.env
$headers=@{Authorization='Basic '+[Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($e.WP_API_USERNAME+':'+$e.WP_API_PASSWORD))}
$geo=Invoke-RestMethod 'https://gonorth.co.il/wp-json/geodir/v2/places/546?context=edit' -Headers $headers -TimeoutSec 30
if($geo.status -ne 'pending' -or $geo.latitude -ne '32.92259730' -or $geo.longitude -ne '35.06819240' -or $geo.city -ne 'עכו' -or $geo.street){throw 'Location changed; stop rollback'}
$hashRaw=ssh -o BatchMode=yes gonorth 'sha256sum /var/www/gonorth/wp-content/uploads/issue93-licensed/efendi-yuvaly-2012.jpg'
if($LASTEXITCODE -ne 0 -or -not $hashRaw.ToLowerInvariant().StartsWith('2cf9a9aa5d903a515460708c82b080dd3472177526324fd99afdcf6af2036c5d')){throw 'Photo changed; stop rollback'}
if(-not $Apply){Write-Output 'Guard passed. No changes made; use -Apply to remove this exact unpublished listing and its attachment.';exit 0}
ssh -o BatchMode=yes gonorth 'wp post delete 546 --force --path=/var/www/gonorth --allow-root'
if($LASTEXITCODE -ne 0){throw 'Listing deletion failed; inspect before retry'}
ssh -o BatchMode=yes gonorth 'wp media delete 547 --force --path=/var/www/gonorth --allow-root'
if($LASTEXITCODE -ne 0){throw 'Attachment deletion failed; inspect orphaned media'}
Write-Output 'Exact pending listing and attachment removed. Inspect issue93-licensed/efendi-yuvaly-2012* for any leftover generated files.'



