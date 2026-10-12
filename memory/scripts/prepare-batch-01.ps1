$ErrorActionPreference='Stop'
$match=[regex]::Match((Get-Content AGENTS.md -Raw),'Username:\s*(\S+)\r?\nPassword:\s*([^\r\n]+)')
if (-not $match.Success) { throw 'Credential missing' }
$headers=@{Authorization=('Basic '+[Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($match.Groups[1].Value+':'+$match.Groups[2].Value.Trim())))}
$backup='memory/data/2026-09-17-batch-01-before.json'
if (Test-Path $backup) { throw 'Backup already exists' }
$records=@()
foreach($id in @(244,246,248,268,270,95)) {
 $kind=if($id -in 244,246,248){'geodir/v2/places'}else{'wp/v2/posts'}
 try { $p=Invoke-RestMethod ("https://gonorth.co.il/wp-json/$kind/${id}?context=edit") -Headers $headers -TimeoutSec 25 } catch { throw "Read failed for $id; HTTP $([int]$_.Exception.Response.StatusCode)" }
 if ($p.status -ne 'publish' -or $null -eq $p.content.raw) { throw "Unexpected state $id" }
 if ($id -in 244,246,248 -and $p.content.raw.Trim().Length -gt 0) { throw "Place $id changed since baseline" }
 $records += [pscustomobject]@{id=$id;endpoint="https://gonorth.co.il/wp-json/$kind/$id";url=$p.link;title=$p.title.rendered;status=$p.status;modified=$p.modified;content=$p.content.raw;categories=$p.post_category;featured_media=$p.featured_media;practical_checked_at=$p.practical_checked_at}
}
[ordered]@{captured_at=(Get-Date).ToUniversalTime().ToString('o');records=$records}|ConvertTo-Json -Depth 8|Set-Content $backup -Encoding utf8
$records | Where-Object id -in 268,270,95 | ForEach-Object { [pscustomobject]@{id=$_.id;tail=$_.content.Substring([Math]::Max(0,$_.content.Length-1200))} } | ConvertTo-Json
