param([string]$Stamp = (Get-Date -Format 'yyyy-MM-dd-HHmmss'))
$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
if (Test-Path (Join-Path $root ("data/$Stamp-batch-01-links.json"))) { throw 'Snapshot already exists' }
$places = (Get-Content (Join-Path $root 'data/2026-09-17-baseline-page-audit.json') -Raw | ConvertFrom-Json).rows
$posts = Get-Content (Join-Path $root 'data/2026-09-17-posts.json') -Raw | ConvertFrom-Json
$targets = @(@{place=244;post=268;draft='244-ben-zimra.md';anchor='מדריך היקבים באתר'},@{place=246;post=270;draft='246-indipark.md';anchor='מדריך הפעילויות לילדים בצפון'},@{place=248;post=95;draft='248-meteor.md';anchor='מדריך הטיול ברמת הגולן'})
$result = @()
foreach ($pair in $targets) {
  $p = $places | Where-Object id -eq $pair.place
  $post = $posts | Where-Object id -eq $pair.post
  if (-not $p -or -not $post) { throw 'Missing target' }
  $response = Invoke-WebRequest $post.link -TimeoutSec 30
  $placeResponse = Invoke-WebRequest $p.url -TimeoutSec 30
  $links = @([regex]::Matches($post.content.rendered, 'href=["'']([^"'']+)["'']') | ForEach-Object { $_.Groups[1].Value })
  $targetPath = [uri]::UnescapeDataString(([uri]$p.url).AbsolutePath).TrimEnd('/')
  $linked = @($links | Where-Object {
    try { $u = [uri]::new([uri]$post.link, [System.Net.WebUtility]::HtmlDecode($_)); $u.Host -eq 'gonorth.co.il' -and (([uri]::UnescapeDataString($u.AbsolutePath).TrimEnd('/') -eq $targetPath) -or $u.Query -eq "?p=$($p.id)") } catch { $false }
  }).Count -gt 0
  $result += [pscustomobject]@{place_id=$p.id;place_url=$p.url;guide_id=$post.id;guide_title=$post.title.rendered;guide_url=$post.link;place_status=$placeResponse.StatusCode;guide_status=$response.StatusCode;guide_editorial_link_to_place=$linked;place_editorial_link_to_guide=$false;recommended_anchor=$pair.anchor;scope='Editorial content only, not navigation/footer/dynamic recommendations'}
}
[ordered]@{collected_at=(Get-Date).ToUniversalTime().ToString('o');scope='3 places and 3 guides; editorial links only';rows=$result} | ConvertTo-Json -Depth 5 | Set-Content (Join-Path $root ("data/$Stamp-batch-01-links.json")) -Encoding utf8
$result | Select-Object place_id,guide_id,place_status,guide_status,guide_editorial_link_to_place | ConvertTo-Json

