$ErrorActionPreference='Stop'
$helper=Get-Content memory/scripts/apply-batch-02-links.ps1 -Raw
$start=$helper.IndexOf('$m=[regex]')
$end=$helper.IndexOf('$receipts=@()')
Invoke-Expression $helper.Substring($start,$end-$start)
$adir=Get-Content memory/data/2026-09-17-adir-practical-update.json -Raw|ConvertFrom-Json
$links=Get-Content memory/data/2026-09-17-batch-02-link-payloads.json -Raw|ConvertFrom-Json
$plan=@()
foreach($item in @(@{id=242;file='242-adir.md';old=$adir.after_content},@{id=235;file='235-tel-shifon.md';old=($links|Where-Object id -eq 235).content})){
 $md=Get-Content ('memory/drafts/'+$item.file) -Raw
 $body=([regex]::Match($md,'(?s)## תוכן מוצע\s+(.*?)\s+## מקורות')).Groups[1].Value
 if(-not $body){throw 'Draft body missing'}
 $parts=@()
 foreach($block in ($body -split '\r?\n\s*\r?\n')){
  if($block.StartsWith('### ')){$parts+='<h2>'+[System.Net.WebUtility]::HtmlEncode($block.Substring(4).Trim())+'</h2>'}else{
   $html=[System.Net.WebUtility]::HtmlEncode($block.Trim())
   $html=[regex]::Replace($html,'\[([^\]]+)\]\((https://[^\s)]+)\)','<a href="$2">$1</a>')
   $parts+='<p>'+$html+'</p>'
  }
 }
 $content=$parts -join "`n`n"
 $r=CallWp 'wp.getPost' $item.id
 $live=($r.methodResponse.params.param.value.struct.member|Where-Object name -eq 'post_content').value.InnerText
 if($live -cne $item.old){throw "Concurrent change $($item.id)"}
 $plan+=[pscustomobject]@{id=$item.id;before=$live;after=$content}
}
$backup='memory/data/2026-09-22-batch-02-publication-backup.json'
if(Test-Path $backup){throw 'Backup exists; inspect before retry'}
$plan|ConvertTo-Json -Depth 5|Set-Content $backup -Encoding utf8
$receipts=@()
foreach($p in $plan){
 $r=CallWp 'wp.getPost' $p.id
 $live=($r.methodResponse.params.param.value.struct.member|Where-Object name -eq 'post_content').value.InnerText
 if($live -cne $p.before){throw 'Concurrent change before write'}
 $extra='<param><value><struct><member><name>post_content</name><value><string>'+(X $p.after)+'</string></value></member></struct></value></param>'
 $r=CallWp 'wp.editPost' $p.id $extra
 if($r.methodResponse.params.param.value.boolean -ne '1'){throw 'Write failed'}
 $r=CallWp 'wp.getPost' $p.id
 $saved=($r.methodResponse.params.param.value.struct.member|Where-Object name -eq 'post_content').value.InnerText
 if($saved -cne $p.after){throw 'Readback mismatch'}
 $public=Invoke-RestMethod ('https://gonorth.co.il/wp-json/geodir/v2/places/'+$p.id) -TimeoutSec 40
 $page=Invoke-WebRequest $public.link -TimeoutSec 40
 $plain=[System.Net.WebUtility]::HtmlDecode(($p.after -replace '<[^>]+>',' ')) -replace '\s+',' '
 $qa=[pscustomobject]@{id=$p.id;url=$public.link;status=$public.status;http=[int]$page.StatusCode;exact_readback=$true;h2=([regex]::Matches($page.Content,'<h2')).Count;intro_present=$page.Content.Contains($plain.Trim().Substring(0,35));guide_link=$page.Content.Contains('https://gonorth.co.il/yekavim-batzafon/');at=(Get-Date).ToUniversalTime().ToString('o')}
 $receipts+=$qa
 $receipts|ConvertTo-Json -Depth 5|Set-Content memory/data/2026-09-22-batch-02-publication-qa.json -Encoding utf8
 $qa|ConvertTo-Json -Compress
}
