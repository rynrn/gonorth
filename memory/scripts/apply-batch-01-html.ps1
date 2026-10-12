$ErrorActionPreference='Stop'
$backup=(Get-Content memory/data/2026-09-17-batch-01-before.json -Raw|ConvertFrom-Json).records
$plan=Get-Content memory/data/2026-09-17-batch-01-payloads.json -Raw|ConvertFrom-Json
$m=[regex]::Match((Get-Content AGENTS.md -Raw),'Username:\s*(\S+)\r?\nPassword:\s*([^\r\n]+)')
if(-not $m.Success){throw 'Credential missing'}
function EscapeXml([string]$s){return [System.Security.SecurityElement]::Escape($s)}
$auth='<param><value><int>1</int></value></param><param><value><string>'+(EscapeXml $m.Groups[1].Value)+'</string></value></param><param><value><string>'+(EscapeXml $m.Groups[2].Value.Trim())+'</string></value></param>'
function CallWp([string]$method,[int]$id,[string]$extra=''){
 $body='<?xml version="1.0" encoding="UTF-8"?><methodCall><methodName>'+$method+'</methodName><params>'+$auth+'<param><value><int>'+$id+'</int></value></param>'+$extra+'</params></methodCall>'
 try{$response=Invoke-WebRequest 'https://gonorth.co.il/xmlrpc.php' -Method Post -ContentType 'text/xml; charset=utf-8' -Body ([Text.Encoding]::UTF8.GetBytes($body)) -TimeoutSec 30}catch{throw "WordPress request uncertain for $id; HTTP $([int]$_.Exception.Response.StatusCode). Inspect before retry."}
 [xml]$xml=$response.Content
 if($xml.methodResponse.fault){$code=($xml.methodResponse.fault.value.struct.member|Where-Object name -eq 'faultCode').value.InnerText;throw "WordPress fault $code at $id"}
 return $xml
}
function GetWp([int]$id){
 $xml=CallWp 'wp.getPost' $id
 $o=@{}
 foreach($item in $xml.methodResponse.params.param.value.struct.member){if($item.name -in 'post_id','post_content','post_status','post_modified_gmt'){$o[$item.name]=$item.value.InnerText}}
 return $o
}
$initial=@{}
foreach($item in $plan){
 $live=GetWp $item.id
 $old=$backup|Where-Object id -eq $item.id
 $expected=$old.content
 if($item.id -eq 244){$expected=$item.content -replace '<[^>]+>',''}
 if($live.post_content -cne $expected -or $live.post_status -ne 'publish'){throw "Unexpected current content $($item.id)"}
 $initial[[string]$item.id]=$live
}
$recovery='memory/data/2026-09-17-batch-01-before-html-recovery.json'
if(Test-Path $recovery){throw 'Recovery snapshot already exists'}
$initial|ConvertTo-Json -Depth 5|Set-Content $recovery -Encoding utf8
$receipts=@()
foreach($item in $plan){
 $live=GetWp $item.id
 if($live.post_content -cne $initial[[string]$item.id].post_content -or $live.post_modified_gmt -ne $initial[[string]$item.id].post_modified_gmt){throw "Concurrent edit at $($item.id)"}
 $extra='<param><value><struct><member><name>post_content</name><value><string>'+(EscapeXml $item.content)+'</string></value></member></struct></value></param>'
 $response=CallWp 'wp.editPost' $item.id $extra
 if($response.methodResponse.params.param.value.boolean -ne '1'){throw "Unexpected write response $($item.id)"}
 $receipts+=[pscustomobject]@{id=$item.id;applied_at=(Get-Date).ToUniversalTime().ToString('o');method='wp.editPost; post_content only'}
 $receipts|ConvertTo-Json -Depth 4|Set-Content memory/data/2026-09-17-batch-01-html-applied.json -Encoding utf8
 $after=GetWp $item.id
 if($after.post_content -cne $item.content -or $after.post_status -ne 'publish'){throw "Verification mismatch $($item.id)"}
 Write-Output "HTML saved and verified $($item.id)"
}
