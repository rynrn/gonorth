$ErrorActionPreference='Stop'
$cfg=Get-Content -LiteralPath .claude/.mcp.json -Raw | ConvertFrom-Json
$e=$cfg.mcpServers.'wordpress-mcp'.env
$pair=$e.WP_API_USERNAME+':'+$e.WP_API_PASSWORD
$headers=@{Authorization='Basic '+[Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($pair))}
$uri='https://gonorth.co.il/wp-json/geodir/v2/places/235'
$old=Get-Content -LiteralPath memory/data/2026-10-06-issue-103-before-edit.json -Raw | ConvertFrom-Json
$current=Invoke-RestMethod ($uri+'?context=edit') -Headers $headers -TimeoutSec 30
if($current.id -ne 235 -or $current.latitude -ne $old.latitude -or $current.longitude -ne $old.longitude -or $current.modified -ne $old.modified -or $current.status -ne 'publish'){throw 'Concurrent canonical change; no write'}
$payload=@{latitude='33.084579429';longitude='35.761299609'} | ConvertTo-Json -Compress
try {
  $null=Invoke-RestMethod $uri -Headers $headers -Method Post -ContentType 'application/json; charset=utf-8' -Body ([Text.Encoding]::UTF8.GetBytes($payload)) -TimeoutSec 30
} catch {
  throw 'GeoDirectory write result uncertain; inspect canonical record before retry'
}
$after=Invoke-RestMethod ($uri+'?context=edit') -Headers $headers -TimeoutSec 30
if($after.latitude -ne '33.084579429' -or $after.longitude -ne '35.761299609' -or $after.region -ne $old.region -or $after.city -ne $old.city -or $after.status -ne 'publish'){throw 'Post-write canonical mismatch; use rollback script'}
$after | ConvertTo-Json -Depth 35 | Set-Content -LiteralPath memory/data/2026-10-06-issue-103-after-edit.json -Encoding utf8NoBOM
[pscustomobject]@{id=$after.id;latitude=$after.latitude;longitude=$after.longitude;region=$after.region;city=$after.city;status=$after.status} | ConvertTo-Json -Compress
