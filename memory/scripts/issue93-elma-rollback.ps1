param([switch]$Apply)
$ErrorActionPreference='Stop'
$baseline=Get-Content -LiteralPath 'memory/data/2026-10-06-issue-93-elma-after-post.json' -Raw|ConvertFrom-Json
$currentRaw=ssh -o BatchMode=yes gonorth 'wp post get 548 --format=json --path=/var/www/gonorth --allow-root'
if($LASTEXITCODE -ne 0){throw 'Pending listing missing; stop rollback'}
$current=$currentRaw|ConvertFrom-Json
if($current.ID -ne 548 -or $current.post_status -ne 'pending' -or $current.post_title -ne 'מלון אלמא — זכרון יעקב' -or $current.post_modified_gmt -ne $baseline.post_modified_gmt -or $current.post_content -ne $baseline.post_content){throw 'Listing changed since snapshot; stop rollback'}
$thumb=ssh -o BatchMode=yes gonorth 'wp post meta get 548 _thumbnail_id --path=/var/www/gonorth --allow-root'
if($LASTEXITCODE -ne 0 -or $thumb.Trim() -ne '549'){throw 'Featured image changed; stop rollback'}
$mediaRaw=ssh -o BatchMode=yes gonorth 'wp post get 549 --format=json --path=/var/www/gonorth --allow-root'
if($LASTEXITCODE -ne 0){throw 'Media missing; stop rollback'}
$media=$mediaRaw|ConvertFrom-Json
$mediaBaseline=Get-Content -LiteralPath 'memory/data/2026-10-06-issue-93-elma-after-media.json' -Raw|ConvertFrom-Json
if($media.ID -ne 549 -or $media.post_parent -ne 548 -or $media.post_mime_type -ne 'image/jpeg' -or $media.post_excerpt -ne $mediaBaseline.post_excerpt){throw 'Media changed; stop rollback'}
$cfg=Get-Content -LiteralPath '.claude/.mcp.json' -Raw|ConvertFrom-Json
$e=$cfg.mcpServers.'wordpress-mcp'.env
$headers=@{Authorization='Basic '+[Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($e.WP_API_USERNAME+':'+$e.WP_API_PASSWORD))}
$geo=Invoke-RestMethod 'https://gonorth.co.il/wp-json/geodir/v2/places/548?context=edit' -Headers $headers -TimeoutSec 30
if($geo.status -ne 'pending' -or $geo.latitude -ne '32.5777742' -or $geo.longitude -ne '34.9449457' -or $geo.city -ne 'זכרון יעקב' -or $geo.street){throw 'Location changed; stop rollback'}
$hashRaw=ssh -o BatchMode=yes gonorth 'sha256sum /var/www/gonorth/wp-content/uploads/issue93-licensed/elma-elgaard-2016.jpg'
if($LASTEXITCODE -ne 0 -or -not $hashRaw.ToLowerInvariant().StartsWith('991c84150e142969425f201d311b022f7238cd3954d0af56aab8498733a5b642')){throw 'Photo changed; stop rollback'}
if(-not $Apply){Write-Output 'Guard passed. No changes made; use -Apply to remove this exact unpublished listing and its attachment.';exit 0}
ssh -o BatchMode=yes gonorth 'wp post delete 548 --force --path=/var/www/gonorth --allow-root'
if($LASTEXITCODE -ne 0){throw 'Listing deletion failed; inspect before retry'}
ssh -o BatchMode=yes gonorth 'wp media delete 549 --force --path=/var/www/gonorth --allow-root'
if($LASTEXITCODE -ne 0){throw 'Attachment deletion failed; inspect orphaned media'}
Write-Output 'Exact pending listing and attachment removed. Inspect issue93-licensed/elma-elgaard-2016* for any leftover generated files.'





