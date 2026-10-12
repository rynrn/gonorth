param([switch]$Apply)
$ErrorActionPreference='Stop'
$baseline=Get-Content -LiteralPath 'memory/data/2026-10-06-issue-93-knights-after-post.json' -Raw|ConvertFrom-Json
$currentRaw=ssh -o BatchMode=yes gonorth 'wp post get 544 --format=json --path=/var/www/gonorth --allow-root'
if($LASTEXITCODE -ne 0){throw 'Pending listing missing; stop rollback'}
$current=$currentRaw|ConvertFrom-Json
if($current.ID -ne 544 -or $current.post_status -ne 'pending' -or $current.post_title -ne 'אולמות האבירים — המצודה ההוספיטלרית בעכו' -or $current.post_modified_gmt -ne $baseline.post_modified_gmt -or $current.post_content -ne $baseline.post_content){throw 'Listing changed since snapshot; stop rollback'}
$thumb=ssh -o BatchMode=yes gonorth 'wp post meta get 544 _thumbnail_id --path=/var/www/gonorth --allow-root'
if($LASTEXITCODE -ne 0 -or $thumb.Trim() -ne '545'){throw 'Featured image changed; stop rollback'}
$mediaRaw=ssh -o BatchMode=yes gonorth 'wp post get 545 --format=json --path=/var/www/gonorth --allow-root'
if($LASTEXITCODE -ne 0){throw 'Media missing; stop rollback'}
$media=$mediaRaw|ConvertFrom-Json
$mediaBaseline=Get-Content -LiteralPath 'memory/data/2026-10-06-issue-93-knights-after-media.json' -Raw|ConvertFrom-Json
if($media.ID -ne 545 -or $media.post_parent -ne 544 -or $media.post_mime_type -ne 'image/jpeg' -or $media.post_excerpt -ne $mediaBaseline.post_excerpt){throw 'Media changed; stop rollback'}
$cfg=Get-Content -LiteralPath '.claude/.mcp.json' -Raw|ConvertFrom-Json
$e=$cfg.mcpServers.'wordpress-mcp'.env
$headers=@{Authorization='Basic '+[Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($e.WP_API_USERNAME+':'+$e.WP_API_PASSWORD))}
$geo=Invoke-RestMethod 'https://gonorth.co.il/wp-json/geodir/v2/places/544?context=edit' -Headers $headers -TimeoutSec 30
if($geo.status -ne 'pending' -or $geo.latitude -ne '32.923404693603516' -or $geo.longitude -ne '35.07143020629883' -or $geo.city -ne 'עכו' -or $geo.street){throw 'Location changed; stop rollback'}
$hashRaw=ssh -o BatchMode=yes gonorth 'sha256sum /var/www/gonorth/wp-content/uploads/issue93-licensed/knights-zapi81-2013.jpg'
if($LASTEXITCODE -ne 0 -or -not $hashRaw.ToLowerInvariant().StartsWith('19ee32a2cd2152201a1c2d91cc0ad26b2b79298ee78cc65f2c622a1bba18fb77')){throw 'Photo changed; stop rollback'}
if(-not $Apply){Write-Output 'Guard passed. No changes made; use -Apply to remove this exact unpublished listing and its attachment.';exit 0}
ssh -o BatchMode=yes gonorth 'wp post delete 544 --force --path=/var/www/gonorth --allow-root'
if($LASTEXITCODE -ne 0){throw 'Listing deletion failed; inspect before retry'}
ssh -o BatchMode=yes gonorth 'wp media delete 545 --force --path=/var/www/gonorth --allow-root'
if($LASTEXITCODE -ne 0){throw 'Attachment deletion failed; inspect orphaned media'}
Write-Output 'Exact pending listing and attachment removed. Inspect issue93-licensed/knights-zapi81-2013* for any leftover generated files.'


