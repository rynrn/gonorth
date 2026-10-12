$ErrorActionPreference='Stop'
$backup=(Get-Content memory/data/2026-09-17-batch-01-before.json -Raw | ConvertFrom-Json).records
$plan=Get-Content memory/data/2026-09-17-batch-01-payloads.json -Raw | ConvertFrom-Json
$receiptPath='memory/data/2026-09-17-batch-01-applied.json'
if(Test-Path $receiptPath){throw 'An apply receipt exists; inspect before retrying'}
$match=[regex]::Match((Get-Content AGENTS.md -Raw),'Username:\s*(\S+)\r?\nPassword:\s*([^\r\n]+)')
if(-not $match.Success){throw 'Credential missing'}
$headers=@{Authorization=('Basic '+[Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($match.Groups[1].Value+':'+$match.Groups[2].Value.Trim())))}
function ReadRecord($endpoint){
 if($endpoint -notmatch '^https://gonorth\.co\.il/wp-json/(geodir/v2/places|wp/v2/posts)/\d+$'){throw 'Unexpected endpoint'}
 try { return Invoke-RestMethod ($endpoint+'?context=edit') -Headers $headers -TimeoutSec 30 } catch {throw "Read failed; HTTP $([int]$_.Exception.Response.StatusCode)"}
}
foreach($item in $plan){
 $old=$backup | Where-Object id -eq $item.id
 $live=ReadRecord $item.endpoint
 if($live.content.raw -cne $old.content -or $live.modified -ne $old.modified -or $live.status -ne 'publish'){throw "Concurrent change at $($item.id)"}
}
$receipts=@()
foreach($item in $plan){
 $old=$backup | Where-Object id -eq $item.id
 $live=ReadRecord $item.endpoint
 if($live.content.raw -cne $old.content -or $live.modified -ne $old.modified){throw "Concurrent change at $($item.id)"}
 $payload=@{content=$item.content}|ConvertTo-Json -Compress
 try { $result=Invoke-RestMethod $item.endpoint -Method Post -Headers $headers -ContentType 'application/json; charset=utf-8' -Body ([Text.Encoding]::UTF8.GetBytes($payload)) -TimeoutSec 30 } catch {throw "Update uncertain at $($item.id); inspect live content before retry. HTTP $([int]$_.Exception.Response.StatusCode)"}
 $receipts+=[pscustomobject]@{id=$item.id;url=$result.link;applied_at=(Get-Date).ToUniversalTime().ToString('o');status=$result.status}
 $receipts|ConvertTo-Json -Depth 4|Set-Content $receiptPath -Encoding utf8
 $check=ReadRecord $item.endpoint
 if($check.content.raw -cne $item.content -or $check.status -ne 'publish'){throw "Post-write mismatch $($item.id)"}
 Write-Output "Updated and verified $($item.id)"
}
