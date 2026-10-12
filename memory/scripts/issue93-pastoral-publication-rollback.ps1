param([switch]$Apply)
$ErrorActionPreference='Stop'
$base='memory/data/2026-10-08-issue-93-pastoral-publish'
$before=Get-Content ($base+'-542-before.json') -Raw -Encoding UTF8|ConvertFrom-Json
$p=ssh -o BatchMode=yes gonorth 'wp post get 542 --format=json --path=/var/www/gonorth --allow-root'|ConvertFrom-Json
$cfg=Get-Content '.claude/.mcp.json' -Raw -Encoding UTF8|ConvertFrom-Json
$e=$cfg.mcpServers.'wordpress-mcp'.env
$h=@{Authorization='Basic '+[Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($e.WP_API_USERNAME+':'+$e.WP_API_PASSWORD))}
$geo=Invoke-RestMethod 'https://gonorth.co.il/wp-json/geodir/v2/places/542?context=edit' -Headers $h -TimeoutSec 30
$m=Invoke-RestMethod 'https://gonorth.co.il/wp-json/wp/v2/media/543?context=edit' -Headers $h -TimeoutSec 30
$thumb=(ssh -o BatchMode=yes gonorth 'wp post meta get 542 _thumbnail_id --path=/var/www/gonorth --allow-root').Trim()
if($p.ID -ne 542 -or $p.post_status -ne 'publish' -or $p.post_content -cne $before.post_content -or $geo.status -ne 'publish' -or $geo.city -ne 'כפר בלום' -or $geo.latitude -ne '33.17495239309772' -or $geo.longitude -ne '35.610987919498726' -or $thumb -ne '543' -or $m.post -ne 542){throw 'Published listing changed; stop'}
if(-not $Apply){Write-Output 'Rollback guard passed; no changes made';exit 0}
$out=ssh -o BatchMode=yes gonorth 'wp post update 542 --post_status=pending --path=/var/www/gonorth --allow-root'
if($LASTEXITCODE -ne 0){throw "Listing rollback failed: $out"}
$p=ssh -o BatchMode=yes gonorth 'wp post get 542 --format=json --path=/var/www/gonorth --allow-root'|ConvertFrom-Json
if($p.post_status -ne 'pending' -or $p.post_content -cne $before.post_content){throw 'Rollback verification failed'}
Write-Output 'Restored listing #542 to pending'


