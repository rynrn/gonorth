param([switch]$Apply)
$ErrorActionPreference='Stop'
$base='memory/data/2026-10-08-issue-93-nahal-publish'
$beforeP=Get-Content ($base+'-554-before.json') -Raw -Encoding UTF8|ConvertFrom-Json
$beforeG=Get-Content ($base+'-270-before.json') -Raw -Encoding UTF8|ConvertFrom-Json
$afterGPath=$base+'-270-after.json'
if(-not (Test-Path $afterGPath)){throw 'Guide after-snapshot missing'}
$afterG=Get-Content $afterGPath -Raw -Encoding UTF8|ConvertFrom-Json
$p=ssh -o BatchMode=yes gonorth 'wp post get 554 --format=json --path=/var/www/gonorth --allow-root'|ConvertFrom-Json
$cfg=Get-Content '.claude/.mcp.json' -Raw -Encoding UTF8|ConvertFrom-Json
$e=$cfg.mcpServers.'wordpress-mcp'.env
$h=@{Authorization='Basic '+[Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($e.WP_API_USERNAME+':'+$e.WP_API_PASSWORD))}
$guideUri='https://gonorth.co.il/wp-json/wp/v2/posts/270'
$g=Invoke-RestMethod ($guideUri+'?context=edit') -Headers $h -TimeoutSec 30
$geo=Invoke-RestMethod 'https://gonorth.co.il/wp-json/geodir/v2/places/554?context=edit' -Headers $h -TimeoutSec 30
$thumb=(ssh -o BatchMode=yes gonorth 'wp post meta get 554 _thumbnail_id --path=/var/www/gonorth --allow-root').Trim()
if($p.ID -ne 554 -or $p.post_status -ne 'publish' -or $p.post_content -cne $beforeP.post_content -or $thumb -ne '555' -or $geo.status -ne 'publish' -or $geo.city -ne '' -or $geo.latitude -ne '32.6712039' -or $geo.longitude -ne '34.9652355'){throw 'Listing changed; stop'}
$navFlag=(ssh -o BatchMode=yes gonorth 'wp post meta get 554 _gonorth_hide_driving_navigation --path=/var/www/gonorth --allow-root').Trim()
if($navFlag -ne '1'){throw 'Navigation flag changed; stop'}
if($g.id -ne 270 -or $g.status -ne 'publish' -or $g.content.raw -cne $afterG.content.raw){throw 'Guide changed; stop'}
if(-not $Apply){Write-Output 'Rollback guard passed; no changes made';exit 0}
$body=@{content=$beforeG.content.raw}|ConvertTo-Json -Compress -Depth 5
$null=Invoke-RestMethod $guideUri -Method Post -Headers $h -ContentType 'application/json; charset=utf-8' -Body ([Text.Encoding]::UTF8.GetBytes($body)) -TimeoutSec 30
$out=ssh -o BatchMode=yes gonorth 'wp post update 554 --post_status=pending --path=/var/www/gonorth --allow-root'
if($LASTEXITCODE -ne 0){throw "Listing rollback failed: $out"}
$p=ssh -o BatchMode=yes gonorth 'wp post get 554 --format=json --path=/var/www/gonorth --allow-root'|ConvertFrom-Json
$g=Invoke-RestMethod ($guideUri+'?context=edit') -Headers $h -TimeoutSec 30
if($p.post_status -ne 'pending' -or $p.post_content -cne $beforeP.post_content -or $g.content.raw -cne $beforeG.content.raw){throw 'Rollback verification failed'}
Write-Output 'Restored listing #554 to pending and guide #270 to original content'


