param([string]$Stamp = (Get-Date -Format 'yyyy-MM-dd-HHmmss'))
$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
$rows = @()
$page = 1
$expected = $null
while ($true) {
  $uri = "https://gonorth.co.il/wp-json/geodir/v2/places?per_page=100&page=$page&status=publish"
  $response = Invoke-WebRequest $uri -TimeoutSec 45
  $items = @($response.Content | ConvertFrom-Json)
  if ($page -eq 1 -and $response.Headers['X-WP-Total']) { $expected = [int]($response.Headers['X-WP-Total'] | Select-Object -First 1) }
  foreach ($p in $items) {
    $html = if ($null -ne $p.content.raw) { [string]$p.content.raw } else { [string]$p.content.rendered }
    $plain = [System.Net.WebUtility]::HtmlDecode(($html -replace '<[^>]+>', ' ')) -replace '\s+', ' '
    $plain = $plain.Trim()
    $rows += [pscustomobject][ordered]@{id=[int]$p.id;title=$p.title.rendered;url=$p.link;content=$html;content_chars=$plain.Length;word_count=(@($plain -split '\s+' | Where-Object {$_})).Count;categories=$p.post_category;street=$p.street;city=$p.city;phone=$p.phone;website=$p.website;business_hours=$p.business_hours;price_type=$p.price_type;practical_checked_at=$p.practical_checked_at;google_place_id=$p.google_place_id;google_rating=$p.google_rating;google_review_count=$p.google_review_count;google_checked_at=$p.google_checked_at;seo_title=$p.yoast_head_json.title;meta_description=$p.yoast_head_json.description;canonical=$p.yoast_head_json.canonical}
  }
  if ($items.Count -lt 100) { break }
  if ($expected -and $rows.Count -ge $expected) { break }
  $page++
  if ($page -gt 20) { throw 'Unexpected pagination: incomplete audit' }
}
if ($expected -and $expected -ne $rows.Count) { throw 'Pagination count mismatch' }
if (@($rows.id | Sort-Object -Unique).Count -ne $rows.Count) { throw 'Duplicate IDs' }
$result = [ordered]@{collected_at=(Get-Date).ToUniversalTime().ToString('o');source='https://gonorth.co.il/wp-json/geodir/v2/places';method='Public API, published places, pagination; raw editorial HTML stripped, entities decoded, whitespace normalized; canonical fields exposed by GeoDirectory, no legacy metadata';expected_total=$expected;count=$rows.Count;rows=$rows}
$target = Join-Path $root "data/$Stamp-page-audit.json"
if (Test-Path $target) { throw 'Snapshot already exists; use a new timestamp' }
$result | ConvertTo-Json -Depth 15 | Set-Content $target -Encoding utf8
[ordered]@{count=$rows.Count;empty=@($rows | Where-Object {$_.content_chars -eq 0}).Count;under_500_chars=@($rows | Where-Object {$_.content_chars -lt 500}).Count;under_60_words=@($rows | Where-Object {$_.word_count -lt 60}).Count;phone=@($rows | Where-Object {$_.phone}).Count;website=@($rows | Where-Object {$_.website}).Count;hours=@($rows | Where-Object {$_.business_hours}).Count;practical_checked=@($rows | Where-Object {$_.practical_checked_at}).Count;google_count=@($rows | Where-Object {$_.google_review_count}).Count;google_checked=@($rows | Where-Object {$_.google_checked_at}).Count;path=$target} | ConvertTo-Json
$rows | Sort-Object content_chars | Select-Object -First 8 id,title,content_chars,website | ConvertTo-Json

