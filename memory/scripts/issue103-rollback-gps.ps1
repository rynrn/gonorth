$ErrorActionPreference='Stop'
$cfg=Get-Content -LiteralPath .claude/.mcp.json -Raw | ConvertFrom-Json
$e=$cfg.mcpServers.'wordpress-mcp'.env
$pair=$e.WP_API_USERNAME+':'+$e.WP_API_PASSWORD
$headers=@{Authorization='Basic '+[Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($pair))}
$uri='https://gonorth.co.il/wp-json/geodir/v2/places/235'
$old=Get-Content -LiteralPath memory/data/2026-10-06-issue-103-before-edit.json -Raw | ConvertFrom-Json
$current=Invoke-RestMethod ($uri+'?context=edit') -Headers $headers -TimeoutSec 30
if($current.latitude -ne '33.084579429' -or $current.longitude -ne '35.761299609'){throw 'Current GPS differs from expected correction; no rollback'}
$payload=@{latitude=$old.latitude;longitude=$old.longitude} | ConvertTo-Json -Compress
$null=Invoke-RestMethod $uri -Headers $headers -Method Post -ContentType 'application/json; charset=utf-8' -Body ([Text.Encoding]::UTF8.GetBytes($payload)) -TimeoutSec 30
$check=Invoke-RestMethod ($uri+'?context=edit') -Headers $headers -TimeoutSec 30
if($check.latitude -ne $old.latitude -or $check.longitude -ne $old.longitude){throw 'Rollback mismatch'}
[pscustomobject]@{id=$check.id;latitude=$check.latitude;longitude=$check.longitude} | ConvertTo-Json -Compress
