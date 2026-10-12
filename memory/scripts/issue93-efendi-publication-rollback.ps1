param([switch]$Apply)
$ErrorActionPreference='Stop'
$base='memory/data/2026-10-08-issue-93-efendi-publish'
$before=Get-Content ($base+'-546-before.json') -Raw -Encoding UTF8|ConvertFrom-Json
$p=ssh -o BatchMode=yes gonorth 'wp post get 546 --format=json --path=/var/www/gonorth --allow-root'|ConvertFrom-Json
$cfg=Get-Content '.claude/.mcp.json' -Raw -Encoding UTF8|ConvertFrom-Json
$e=$cfg.mcpServers.'wordpress-mcp'.env
$h=@{Authorization='Basic '+[Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($e.WP_API_USERNAME+':'+$e.WP_API_PASSWORD))}
$geo=Invoke-RestMethod 'https://gonorth.co.il/wp-json/geodir/v2/places/546?context=edit' -Headers $h -TimeoutSec 30
$m=Invoke-RestMethod 'https://gonorth.co.il/wp-json/wp/v2/media/547?context=edit' -Headers $h -TimeoutSec 30
$thumb=(ssh -o BatchMode=yes gonorth 'wp post meta get 546 _thumbnail_id --path=/var/www/gonorth --allow-root').Trim()
if($p.ID -ne 546 -or $p.post_status -ne 'publish' -or $p.post_content -cne $before.post_content -or $geo.status -ne 'publish' -or $geo.city -ne 'עכו' -or $geo.latitude -ne '32.92259730' -or $geo.longitude -ne '35.06819240' -or $thumb -ne '547' -or $m.post -ne 546){throw 'Published listing changed; stop'}
if(-not $Apply){Write-Output 'Rollback guard passed; no changes made';exit 0}
$out=ssh -o BatchMode=yes gonorth 'wp post update 546 --post_status=pending --path=/var/www/gonorth --allow-root'
if($LASTEXITCODE -ne 0){throw "Listing rollback failed: $out"}
$p=ssh -o BatchMode=yes gonorth 'wp post get 546 --format=json --path=/var/www/gonorth --allow-root'|ConvertFrom-Json
if($p.post_status -ne 'pending' -or $p.post_content -cne $before.post_content){throw 'Rollback verification failed'}
Write-Output 'Restored listing #546 to pending'
