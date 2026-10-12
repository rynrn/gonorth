param([switch]$Apply)
$ErrorActionPreference='Stop'
$beforeP=Get-Content -LiteralPath 'memory/data/2026-10-06-issue-93-publish-534-before.json' -Raw|ConvertFrom-Json
$beforeG=Get-Content -LiteralPath 'memory/data/2026-10-06-issue-93-publish-516-before.json' -Raw|ConvertFrom-Json
$afterPFile='memory/data/2026-10-06-issue-93-publish-534-after-geodir.json'
$afterGFile='memory/data/2026-10-06-issue-93-publish-516-after-rest.json'
if(-not (Test-Path -LiteralPath $afterPFile)){throw 'Published listing snapshot absent'}
$afterP=Get-Content -LiteralPath $afterPFile -Raw|ConvertFrom-Json
$afterG=$null
if(Test-Path -LiteralPath $afterGFile){$afterG=Get-Content -LiteralPath $afterGFile -Raw|ConvertFrom-Json}
$cfg=Get-Content -LiteralPath '.claude/.mcp.json' -Raw|ConvertFrom-Json
$e=$cfg.mcpServers.'wordpress-mcp'.env
$h=@{Authorization='Basic '+[Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($e.WP_API_USERNAME+':'+$e.WP_API_PASSWORD))}
$geoUri='https://gonorth.co.il/wp-json/geodir/v2/places/534'
$guideUri='https://gonorth.co.il/wp-json/wp/v2/posts/516'
$geo=Invoke-RestMethod ($geoUri+'?context=edit') -Headers $h -TimeoutSec 30
$guide=Invoke-RestMethod ($guideUri+'?context=edit') -Headers $h -TimeoutSec 30
if($geo.id -ne 534 -or $geo.status -ne 'publish' -or $geo.content.raw -cne $afterP.content.raw -or $geo.city -ne '' -or $geo.latitude -ne '32.7027036' -or $geo.longitude -ne '35.1275862'){throw 'Published listing changed; stop'}
if($guide.id -ne 516 -or $guide.status -ne 'publish'){throw 'Guide changed status; stop'}
$guideIsAfter=$afterG -and ($guide.content.raw -ceq $afterG.content.raw)
$guideIsBefore=$guide.content.raw -ceq $beforeG.post_content
if(-not $guideIsAfter -and -not $guideIsBefore){throw 'Guide content changed; stop'}
$thumb=(ssh -o BatchMode=yes gonorth 'wp post meta get 534 _thumbnail_id --path=/var/www/gonorth --allow-root').Trim()
if($thumb -ne '535'){throw 'Featured media changed; stop'}
if(-not $Apply){Write-Output 'Rollback guard passed; no changes made';exit 0}
if($guideIsAfter){
  $body=@{content=$beforeG.post_content}|ConvertTo-Json -Compress -Depth 5
  $null=Invoke-RestMethod $guideUri -Method Post -Headers $h -ContentType 'application/json; charset=utf-8' -Body ([Text.Encoding]::UTF8.GetBytes($body)) -TimeoutSec 30
}
$body=@{status='pending';content=$beforeP.post_content}|ConvertTo-Json -Compress -Depth 5
$null=Invoke-RestMethod $geoUri -Method Post -Headers $h -ContentType 'application/json; charset=utf-8' -Body ([Text.Encoding]::UTF8.GetBytes($body)) -TimeoutSec 30
$geo=Invoke-RestMethod ($geoUri+'?context=edit') -Headers $h -TimeoutSec 30
$guide=Invoke-RestMethod ($guideUri+'?context=edit') -Headers $h -TimeoutSec 30
if($geo.status -ne 'pending' -or $geo.content.raw -cne $beforeP.post_content -or $guide.content.raw -cne $beforeG.post_content){throw 'Rollback verification failed; inspect canonical state'}
Write-Output 'Restored reviewed listing to pending and guide to previous content'
