$ErrorActionPreference='Stop'
$m=[regex]::Match((Get-Content AGENTS.md -Raw),'Username:\s*(\S+)\r?\nPassword:\s*([^\r\n]+)')
$h=@{Authorization=('Basic '+[Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($m.Groups[1].Value+':'+$m.Groups[2].Value.Trim())))}
$expected=Get-Content memory/data/2026-09-22-sukkot-draft-after.json -Raw|ConvertFrom-Json
$p=Invoke-RestMethod 'https://gonorth.co.il/wp-json/wp/v2/pages/441?context=edit' -Headers $h
if($p.content.raw -cne $expected.content.raw -or $p.status -ne 'draft'){throw 'Concurrent change'}
$p|ConvertTo-Json -Depth 20|Set-Content memory/data/2026-09-22-sukkot-prepublish.json -Encoding utf8
$r=Invoke-RestMethod 'https://gonorth.co.il/wp-json/wp/v2/pages/441' -Headers $h -Method Post -ContentType 'application/json' -Body '{"status":"publish"}'
$v=Invoke-RestMethod 'https://gonorth.co.il/wp-json/wp/v2/pages/441?context=edit' -Headers $h
if($v.status -ne 'publish' -or $v.content.raw -cne $expected.content.raw){throw 'Publication check failed'}
$v|ConvertTo-Json -Depth 20|Set-Content memory/data/2026-09-22-sukkot-published.json -Encoding utf8
$page=Invoke-WebRequest $v.link
$qa=[pscustomobject]@{url=$v.link;status=[int]$page.StatusCode;h1=([regex]::Matches($page.Content,'<h1\b')).Count;title=([regex]::Match($page.Content,'<title>(.*?)</title>')).Groups[1].Value;description=([regex]::Match($page.Content,'<meta name="description" content="([^"]+)"')).Groups[1].Value;noindex=$page.Content -match '<meta[^>]+name=["'']robots["''][^>]+noindex';intro=$page.Content.Contains('27 בספטמבר עד 1 באוקטובר 2026')}
$qa|ConvertTo-Json|Set-Content memory/data/2026-09-22-sukkot-published-qa.json -Encoding utf8
$qa|ConvertTo-Json
$settings=Invoke-RestMethod 'https://gonorth.co.il/wp-json/wp/v2/settings' -Headers $h
Write-Output ('Front page ID: '+$settings.page_on_front)
$pages=Invoke-RestMethod 'https://gonorth.co.il/wp-json/wp/v2/pages?per_page=100&context=edit&_fields=id,slug,title,link' -Headers $h
$pages|Where-Object {$_.slug -match 'home|galil|front'}|ConvertTo-Json -Depth 4
$r=Invoke-WebRequest 'https://gonorth.co.il/region/galil/'
[regex]::Match($r.Content,'<body[^>]*>').Value
