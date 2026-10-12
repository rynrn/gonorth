$ErrorActionPreference='Stop'
$id=(Get-Content -LiteralPath 'memory/data/2026-10-06-issue-93-madatech-id.txt' -Raw).Trim()
if($id -notmatch '^\d+$'){throw 'Unexpected listing ID'}
$cfg=Get-Content -LiteralPath '.claude/.mcp.json' -Raw | ConvertFrom-Json
$e=$cfg.mcpServers.'wordpress-mcp'.env
$headers=@{Authorization='Basic '+[Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($e.WP_API_USERNAME+':'+$e.WP_API_PASSWORD))}
$uri="https://gonorth.co.il/wp-json/geodir/v2/places/$id"
$old=Invoke-RestMethod ($uri+'?context=edit') -Headers $headers -TimeoutSec 30
$old | ConvertTo-Json -Depth 35 | Set-Content -LiteralPath 'memory/data/2026-10-06-issue-93-madatech-before-location.json' -Encoding utf8NoBOM
if($old.id -ne $id -or $old.status -ne 'pending' -or $old.title.raw -ne 'מדעטק — המוזיאון הלאומי למדע, טכנולוגיה וחלל' -or $old.latitude -or $old.longitude){throw 'Unexpected initial GeoDirectory state; no write'}
$payload=@{latitude='32.8099166';longitude='34.9961485';country='ישראל';region='צפון';city='חיפה';street=''} | ConvertTo-Json -Compress
try {
  $null=Invoke-RestMethod $uri -Headers $headers -Method Post -ContentType 'application/json; charset=utf-8' -Body ([Text.Encoding]::UTF8.GetBytes($payload)) -TimeoutSec 30
} catch {
  throw 'GeoDirectory write result uncertain; inspect canonical record before retry'
}
$after=Invoke-RestMethod ($uri+'?context=edit') -Headers $headers -TimeoutSec 30
$after | ConvertTo-Json -Depth 35 | Set-Content -LiteralPath 'memory/data/2026-10-06-issue-93-madatech-after-location.json' -Encoding utf8NoBOM
if($after.id -ne $id -or $after.status -ne 'pending' -or $after.latitude -ne '32.8099166' -or $after.longitude -ne '34.9961485' -or $after.country -ne 'ישראל' -or $after.region -ne 'צפון' -or $after.city -ne 'חיפה' -or $after.street){throw 'Canonical location mismatch; inspect and use rollback'}
[pscustomobject]@{id=$after.id;status=$after.status;latitude=$after.latitude;longitude=$after.longitude;country=$after.country;region=$after.region;city=$after.city;street=$after.street} | ConvertTo-Json -Compress





