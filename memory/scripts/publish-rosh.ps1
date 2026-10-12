$ErrorActionPreference='Stop'
$p=Get-Content memory/data/2026-09-22-rosh-before.json -Raw|ConvertFrom-Json
$c=$p.content.raw.Replace('2026-09-09','2026-09-22').Replace('9 בספטמבר 2026','22 בספטמבר 2026')
$c=[regex]::Replace($c,'<p>ראש השנה תשפ״ז.*?</p>','<p>ראש השנה בצפון: המדריך הבא מיועד לתכנון החג בשנת 2027. ראש השנה תשפ״ח מתחיל בשקיעה ביום שישי, 1 באוקטובר 2027, ומסתיים בצאת החג ביום ראשון, 3 באוקטובר. התאריכים אומתו ב<a href="https://www.hebcal.com/holidays/2027?i=on">לוח ישראל של Hebcal</a>. נכון לעדכון זה, לא אומתו אירועים, ארוחות חג או שעות פעילות מיוחדות ל־2027; יש לבדוק אותם מול המארגנים סמוך לחג.</p>')
$c=$c.Replace('מתי ראש השנה 2026?','מתי ראש השנה 2027?').Replace('ערב ראש השנה חל ביום שישי, 11 בספטמבר 2026. ימי החג הם שבת וראשון, 12–13 בספטמבר.','ערב ראש השנה חל ביום שישי, 1 באוקטובר 2027. ימי החג הם שבת וראשון, 2–3 באוקטובר.')
$c|Set-Content memory/drafts/rosh-hashanah-batzafon-2027.html -Encoding utf8
$m=[regex]::Match((Get-Content AGENTS.md -Raw),'Username:\s*(\S+)\r?\nPassword:\s*([^\r\n]+)')
$h=@{Authorization=('Basic '+[Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($m.Groups[1].Value+':'+$m.Groups[2].Value.Trim())))}
$live=Invoke-RestMethod 'https://gonorth.co.il/wp-json/wp/v2/pages/440?context=edit' -Headers $h
if($live.content.raw -cne $p.content.raw -or $live.status -ne 'draft'){throw 'Concurrent change'}
$body=@{content=$c;status='publish'}|ConvertTo-Json
$r=Invoke-RestMethod 'https://gonorth.co.il/wp-json/wp/v2/pages/440' -Headers $h -Method Post -ContentType 'application/json; charset=utf-8' -Body ([Text.Encoding]::UTF8.GetBytes($body))
$v=Invoke-RestMethod 'https://gonorth.co.il/wp-json/wp/v2/pages/440?context=edit' -Headers $h
if($v.content.raw -cne $c -or $v.status -ne 'publish'){throw 'Publication mismatch'}
$v|ConvertTo-Json -Depth 20|Set-Content memory/data/2026-09-22-rosh-published.json -Encoding utf8
$page=Invoke-WebRequest $v.link -TimeoutSec 25
$qa=[pscustomobject]@{url=$v.link;status=[int]$page.StatusCode;h1=([regex]::Matches($page.Content,'<h1\b')).Count;title=$v.yoast_head_json.title;description=$v.yoast_head_json.description;noindex=$page.Content -match '<meta[^>]+name=["'']robots["''][^>]+noindex';updated=$page.Content.Contains('1 באוקטובר 2027')}
$qa|ConvertTo-Json|Set-Content memory/data/2026-09-22-rosh-published-qa.json -Encoding utf8
$qa|ConvertTo-Json
$checks=@()
foreach($url in ([regex]::Matches($c,'href="(https://gonorth.co.il/[^"]+)"')|ForEach-Object {$_.Groups[1].Value}|Sort-Object -Unique)){
 try{$r=Invoke-WebRequest $url -TimeoutSec 20;$checks+=[pscustomobject]@{url=$url;status=[int]$r.StatusCode}}catch{$checks+=[pscustomobject]@{url=$url;status='failed'}}
}
$checks|ConvertTo-Json|Set-Content memory/data/2026-09-22-rosh-links-qa.json -Encoding utf8
Write-Output ('Links checked: '+$checks.Count+'; failures: '+@($checks|Where-Object status -ne 200).Count)
