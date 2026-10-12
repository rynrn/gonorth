$ErrorActionPreference='Stop'
$before=Get-Content memory/data/2026-09-17-batch-02-before.json -Raw|ConvertFrom-Json
$guide=Get-Content memory/data/2026-09-17-batch-02-guide-raw-before.json -Raw|ConvertFrom-Json
$tel=$before|Where-Object id -eq 235
$chateau=$before|Where-Object id -eq 237
$g=$guide.content
if(-not $g.Contains('<p>יקב תל שיפון נמצא') -or -not $g.Contains(', שאטו גולן,')){throw 'Expected existing mentions missing'}
$g=$g.Replace('<p>יקב תל שיפון נמצא','<p><a href="'+$tel.url+'">יקב תל שיפון</a> נמצא')
$g=$g.Replace(', שאטו גולן,',', <a href="'+$chateau.url+'">שאטו גולן</a>,')
$plan=@([pscustomobject]@{id=268;content=$g;old_content=$guide.content})
foreach($p in @($tel,$chateau)){
 $link='<p>לתכנון עצירות נוספות, עיינו ב<a href="'+$guide.url+'">מדריך היקבים בצפון</a>.</p>'
 $plan+=[pscustomobject]@{id=$p.id;content=($p.content+"`n`n"+$link);old_content=$p.content}
}
$plan|ConvertTo-Json -Depth 5|Set-Content memory/data/2026-09-17-batch-02-link-payloads.json -Encoding utf8
$m=[regex]::Match((Get-Content AGENTS.md -Raw),'Username:\s*(\S+)\r?\nPassword:\s*([^\r\n]+)')
function X([string]$s){[System.Security.SecurityElement]::Escape($s)}
$auth='<param><value><int>1</int></value></param><param><value><string>'+(X $m.Groups[1].Value)+'</string></value></param><param><value><string>'+(X $m.Groups[2].Value.Trim())+'</string></value></param>'
function CallWp($method,$id,$extra=''){
 $xmlBody='<?xml version="1.0" encoding="UTF-8"?><methodCall><methodName>'+$method+'</methodName><params>'+$auth+'<param><value><int>'+$id+'</int></value></param>'+$extra+'</params></methodCall>'
 try{[xml]$r=(Invoke-WebRequest 'https://gonorth.co.il/xmlrpc.php' -Method Post -ContentType 'text/xml; charset=utf-8' -Body ([Text.Encoding]::UTF8.GetBytes($xmlBody)) -TimeoutSec 25).Content}catch{throw "Request failed for $id; inspect before retry"}
 if($r.methodResponse.fault){throw "WordPress fault for $id"}
 return $r
}
$receipts=@()
foreach($p in $plan){
 $r=CallWp 'wp.getPost' $p.id
 $live=($r.methodResponse.params.param.value.struct.member|Where-Object name -eq 'post_content').value.InnerText
 if($live -cne $p.old_content){throw "Concurrent change $($p.id)"}
 $extra='<param><value><struct><member><name>post_content</name><value><string>'+(X $p.content)+'</string></value></member></struct></value></param>'
 $r=CallWp 'wp.editPost' $p.id $extra
 if($r.methodResponse.params.param.value.boolean -ne '1'){throw 'Write response mismatch'}
 $r=CallWp 'wp.getPost' $p.id
 $saved=($r.methodResponse.params.param.value.struct.member|Where-Object name -eq 'post_content').value.InnerText
 if($saved -cne $p.content){throw 'Post-write content mismatch'}
 $receipts+=[pscustomobject]@{id=$p.id;verified=$true;applied_at=(Get-Date).ToUniversalTime().ToString('o')}
 $receipts|ConvertTo-Json -Depth 4|Set-Content memory/data/2026-09-17-batch-02-links-applied.json -Encoding utf8
 Write-Output "Links saved and verified $($p.id)"
}
