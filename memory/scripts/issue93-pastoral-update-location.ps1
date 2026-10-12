$ErrorActionPreference='Stop'
$id=(Get-Content -LiteralPath 'memory/data/2026-10-06-issue-93-pastoral-id.txt' -Raw).Trim()
if($id -ne '542'){throw 'Unexpected listing ID'}
$cfg=Get-Content -LiteralPath '.claude/.mcp.json' -Raw | ConvertFrom-Json
$e=$cfg.mcpServers.'wordpress-mcp'.env
$headers=@{Authorization='Basic '+[Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($e.WP_API_USERNAME+':'+$e.WP_API_PASSWORD))}
$uri="https://gonorth.co.il/wp-json/geodir/v2/places/$id"
$old=Invoke-RestMethod ($uri+'?context=edit') -Headers $headers -TimeoutSec 30
$old | ConvertTo-Json -Depth 35 | Set-Content -LiteralPath 'memory/data/2026-10-06-issue-93-pastoral-before-location.json' -Encoding utf8NoBOM
if($old.id -ne $id -or $old.status -ne 'pending' -or $old.title.raw -ne 'מלון פסטורל כפר בלום' -or $old.latitude -or $old.longitude){throw 'Unexpected initial GeoDirectory state; no write'}
$payload=@{latitude='33.17495239309772';longitude='35.610987919498726';country='ישראל';region='צפון';city='כפר בלום';street=''} | ConvertTo-Json -Compress
try {
  $null=Invoke-RestMethod $uri -Headers $headers -Method Post -ContentType 'application/json; charset=utf-8' -Body ([Text.Encoding]::UTF8.GetBytes($payload)) -TimeoutSec 30
} catch {
  throw 'GeoDirectory write result uncertain; inspect canonical record before retry'
}
$after=Invoke-RestMethod ($uri+'?context=edit') -Headers $headers -TimeoutSec 30
$after | ConvertTo-Json -Depth 35 | Set-Content -LiteralPath 'memory/data/2026-10-06-issue-93-pastoral-after-location.json' -Encoding utf8NoBOM
if($after.id -ne $id -or $after.status -ne 'pending' -or $after.latitude -ne '33.17495239309772' -or $after.longitude -ne '35.610987919498726' -or $after.country -ne 'ישראל' -or $after.region -ne 'צפון' -or $after.city -ne 'כפר בלום' -or $after.street){throw 'Canonical location mismatch; inspect and use rollback'}
[pscustomobject]@{id=$after.id;status=$after.status;latitude=$after.latitude;longitude=$after.longitude;country=$after.country;region=$after.region;city=$after.city;street=$after.street} | ConvertTo-Json -Compress
