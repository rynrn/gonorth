param([switch]$Apply)
$ErrorActionPreference = 'Stop'
$baseline = Get-Content -LiteralPath 'memory/data/2026-10-06-issue-93-beit-shearim-after-post.json' -Raw | ConvertFrom-Json
$currentRaw = ssh gonorth "wp post get 534 --format=json --path=/var/www/gonorth --allow-root"
if ($LASTEXITCODE -ne 0) { throw 'Pending post missing; stop rollback' }
$current = $currentRaw | ConvertFrom-Json
if ($current.ID -ne 534 -or $current.post_status -ne 'pending' -or $current.post_title -ne 'גן לאומי בית שערים' -or $current.post_modified_gmt -ne $baseline.post_modified_gmt -or $current.post_content -ne $baseline.post_content) { throw 'Post changed since snapshot; stop rollback' }
$thumb = ssh gonorth "wp post meta get 534 _thumbnail_id --path=/var/www/gonorth --allow-root"
if ($LASTEXITCODE -ne 0 -or $thumb.Trim() -ne '535') { throw 'Featured media changed; stop rollback' }
$mediaRaw = ssh gonorth "wp post get 535 --format=json --path=/var/www/gonorth --allow-root"
if ($LASTEXITCODE -ne 0) { throw 'Media missing; stop rollback' }
$media = $mediaRaw | ConvertFrom-Json
if ($media.ID -ne 535 -or $media.post_parent -ne 534 -or $media.post_mime_type -ne 'image/jpeg') { throw 'Media changed; stop rollback' }
$cfg = Get-Content -LiteralPath '.claude/.mcp.json' -Raw | ConvertFrom-Json
$envv = $cfg.mcpServers.'wordpress-mcp'.env
$headers = @{Authorization='Basic '+[Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($envv.WP_API_USERNAME+':'+$envv.WP_API_PASSWORD))}
$geo = Invoke-RestMethod 'https://gonorth.co.il/wp-json/geodir/v2/places/534?context=edit' -Headers $headers -TimeoutSec 30
if ($geo.status -ne 'pending' -or $geo.latitude -ne '32.7027036' -or $geo.longitude -ne '35.1275862' -or $geo.city -or $geo.street) { throw 'Location changed; stop rollback' }
$hashRaw = ssh gonorth "sha256sum /var/www/gonorth/wp-content/uploads/issue93-licensed/beit-shearim-cave14-davidbena-2019.jpg"
if ($LASTEXITCODE -ne 0 -or -not $hashRaw.ToLowerInvariant().StartsWith('a3a8abe729f9a9fee47bdd53d71d2855893a8e497dfb9667390eb80aa2602cf6')) { throw 'Photo changed; stop rollback' }
if (-not $Apply) { Write-Output 'Guard passed. No changes made; use -Apply to remove this exact pending post and its imported attachment.'; exit 0 }
ssh gonorth "wp post delete 534 --force --path=/var/www/gonorth --allow-root"
if ($LASTEXITCODE -ne 0) { throw 'Post deletion failed; inspect before retry' }
ssh gonorth "wp media delete 535 --force --path=/var/www/gonorth --allow-root"
if ($LASTEXITCODE -ne 0) { throw 'Media deletion failed; inspect orphaned attachment' }
ssh gonorth "test ! -e /var/www/gonorth/wp-content/uploads/issue93-licensed/beit-shearim-cave14-davidbena-2019.jpg"
if ($LASTEXITCODE -ne 0) { throw 'Original file remains; inspect before manual removal' }
Write-Output 'Exact pending listing and image removed.'

