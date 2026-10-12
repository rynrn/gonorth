param(
    [string]$InputPath = 'memory/data/2026-09-26-internal-link-content-export.json',
    [string]$OutputPath = 'memory/data/2026-09-26-internal-link-editorial-graph.json'
)
$ErrorActionPreference = 'Stop'
$data = Get-Content -Raw -LiteralPath $InputPath | ConvertFrom-Json
function Normalize-InternalUrl([string]$url) {
    if ([string]::IsNullOrWhiteSpace($url)) { return $null }
    try {
        $uri = [Uri]::new($url, [UriKind]::Absolute)
    } catch { return $null }
    if ($uri.Host -notin @('gonorth.co.il','www.gonorth.co.il')) { return $null }
    $path = [Uri]::UnescapeDataString($uri.AbsolutePath).TrimEnd('/')
    if ([string]::IsNullOrEmpty($path)) { $path = '/' }
    return $path.ToLowerInvariant()
}
$byPath = @{}
foreach ($row in $data.rows) {
    $path = Normalize-InternalUrl $row.url
    if ($path) { $byPath[$path] = $row }
}
$incoming = @{}
foreach ($row in $data.rows) { $incoming[[string]$row.id] = [System.Collections.Generic.HashSet[int]]::new() }
$graphRows = @()
foreach ($row in $data.rows) {
    $links = @()
    $matches = [regex]::Matches([string]$row.content, '(?is)href\s*=\s*["''](?<url>[^"''#]+)')
    foreach ($match in $matches) {
        $raw = [System.Net.WebUtility]::HtmlDecode($match.Groups['url'].Value)
        $path = Normalize-InternalUrl $raw
        if (-not $path) { continue }
        $targetId = $null
        if ($byPath.ContainsKey($path)) {
            $targetId = [int]$byPath[$path].id
            [void]$incoming[[string]$targetId].Add([int]$row.id)
        }
        $links += [pscustomobject]@{ url=$raw; path=$path; target_id=$targetId }
    }
    $known = @($links | Where-Object target_id | ForEach-Object { [int]$_.target_id })
    $graphRows += [pscustomobject]@{
        id=[int]$row.id
        type=[string]$row.type
        title=[string]$row.title
        url=[string]$row.url
        categories=@($row.categories)
        content_chars=([string]$row.content).Length
        outbound_internal_links=$links.Count
        outbound_known_ids=@($known | Sort-Object -Unique)
        outbound_unknown_paths=@($links | Where-Object { -not $_.target_id } | ForEach-Object path | Sort-Object -Unique)
        duplicate_known_targets=@($known | Group-Object | Where-Object Count -gt 1 | ForEach-Object { [pscustomobject]@{target_id=[int]$_.Name;count=$_.Count} })
    }
}
foreach ($item in $graphRows) {
    $item | Add-Member -NotePropertyName inbound_known_ids -NotePropertyValue @($incoming[[string]$item.id] | Sort-Object)
}
$summary = [ordered]@{
    exported_count=[int]$data.count
    graph_count=$graphRows.Count
    editorial_edges=(@($graphRows | ForEach-Object outbound_known_ids)).Count
    rows_with_no_editorial_inbound=(@($graphRows | Where-Object {$_.inbound_known_ids.Count -eq 0})).Count
    places_with_no_editorial_inbound=(@($graphRows | Where-Object {$_.type -eq 'gd_place' -and $_.inbound_known_ids.Count -eq 0})).Count
    rows_with_no_editorial_outbound=(@($graphRows | Where-Object {$_.outbound_known_ids.Count -eq 0})).Count
    places_with_no_editorial_outbound=(@($graphRows | Where-Object {$_.type -eq 'gd_place' -and $_.outbound_known_ids.Count -eq 0})).Count
    duplicate_target_rows=(@($graphRows | Where-Object {$_.duplicate_known_targets.Count -gt 0})).Count
}
$result=[ordered]@{generated_at_utc=(Get-Date).ToUniversalTime().ToString('o');summary=$summary;rows=$graphRows}
$result | ConvertTo-Json -Depth 8 | Set-Content -LiteralPath $OutputPath -Encoding utf8
$summary
