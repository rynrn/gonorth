param(
    [string]$InputPath = 'memory/data/2026-09-26-internal-link-content-export.json',
    [string]$OutputPath = 'memory/data/2026-09-26-internal-link-public-crawl.json'
)
$ErrorActionPreference='Stop'
$data=Get-Content -Raw -LiteralPath $InputPath|ConvertFrom-Json
$sourceRows=@($data.rows|Select-Object id,type,title,url)
$results=$sourceRows|ForEach-Object -Parallel {
    $row=$_
    try {
        $response=Invoke-WebRequest -Uri $row.url -MaximumRedirection 5 -TimeoutSec 40 -Headers @{'User-Agent'='GoNorth internal-link audit/1.0'}
        $html=[string]$response.Content
        $paths=@()
        foreach($m in [regex]::Matches($html,'(?is)href\s*=\s*["''](?<url>[^"''#]+)')) {
            $raw=[System.Net.WebUtility]::HtmlDecode($m.Groups['url'].Value)
            try { $uri=[Uri]::new($raw,[UriKind]::Absolute) } catch { continue }
            if($uri.Host -notin @('gonorth.co.il','www.gonorth.co.il')) { continue }
            $path=[Uri]::UnescapeDataString($uri.AbsolutePath).TrimEnd('/').ToLowerInvariant()
            if([string]::IsNullOrEmpty($path)){$path='/'}
            $paths+=$path
        }
        $canon=$null
        $cm=[regex]::Match($html,'(?is)<link[^>]+rel=["''][^"'']*canonical[^"'']*["''][^>]+href=["''](?<url>[^"'']+)')
        if(-not $cm.Success){$cm=[regex]::Match($html,'(?is)<link[^>]+href=["''](?<url>[^"'']+)["''][^>]+rel=["''][^"'']*canonical')}
        if($cm.Success){$canon=$cm.Groups['url'].Value}
        [pscustomobject]@{id=[int]$row.id;type=[string]$row.type;title=[string]$row.title;url=[string]$row.url;status=[int]$response.StatusCode;canonical=$canon;h1_count=([regex]::Matches($html,'(?is)<h1\b')).Count;internal_paths=@($paths|Sort-Object -Unique);internal_link_occurrences=$paths.Count;error=$null}
    } catch {
        [pscustomobject]@{id=[int]$row.id;type=[string]$row.type;title=[string]$row.title;url=[string]$row.url;status=$null;canonical=$null;h1_count=$null;internal_paths=@();internal_link_occurrences=0;error=$_.Exception.Message}
    }
} -ThrottleLimit 8
$pathToId=@{}
foreach($row in $data.rows){
    $u=[Uri]$row.url
    $path=[Uri]::UnescapeDataString($u.AbsolutePath).TrimEnd('/').ToLowerInvariant()
    if([string]::IsNullOrEmpty($path)){$path='/'}
    $pathToId[$path]=[int]$row.id
}
$incoming=@{}
foreach($row in $data.rows){$incoming[[string]$row.id]=[System.Collections.Generic.HashSet[int]]::new()}
foreach($result in $results){
    $targets=@()
    foreach($path in $result.internal_paths){
        if($pathToId.ContainsKey($path)){
            $target=[int]$pathToId[$path]
            if($target -ne [int]$result.id){
                $targets+=$target
                [void]$incoming[[string]$target].Add([int]$result.id)
            }
        }
    }
    $result|Add-Member -NotePropertyName known_target_ids -NotePropertyValue @($targets|Sort-Object -Unique)
}
foreach($result in $results){$result|Add-Member -NotePropertyName inbound_source_ids -NotePropertyValue @($incoming[[string]$result.id]|Sort-Object)}
$summary=[ordered]@{
    crawled=$results.Count
    http_200=(@($results|Where-Object status -eq 200)).Count
    errors=(@($results|Where-Object error)).Count
    zero_public_inbound=(@($results|Where-Object {$_.inbound_source_ids.Count -eq 0})).Count
    places_zero_public_inbound=(@($results|Where-Object {$_.type -eq 'gd_place' -and $_.inbound_source_ids.Count -eq 0})).Count
    zero_public_outbound=(@($results|Where-Object {$_.known_target_ids.Count -eq 0})).Count
    bad_h1_count=(@($results|Where-Object {$_.status -eq 200 -and $_.h1_count -ne 1})).Count
}
[ordered]@{generated_at_utc=(Get-Date).ToUniversalTime().ToString('o');summary=$summary;rows=@($results|Sort-Object id)}|ConvertTo-Json -Depth 8|Set-Content -LiteralPath $OutputPath -Encoding utf8
$summary
