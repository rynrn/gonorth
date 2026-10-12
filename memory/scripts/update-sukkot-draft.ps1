$ErrorActionPreference='Stop'
$p=Get-Content memory/data/2026-09-22-sukkot-draft-before.json -Raw|ConvertFrom-Json
$c=$p.content.raw.Replace('2026-09-09','2026-09-22').Replace('9 בספטמבר 2026','22 בספטמבר 2026')
$c=[regex]::Replace($c,'התאריכים נבדקו מול .*?</p>','התאריכים נבדקו מול <a href="https://www.hebcal.com/holidays/2026?i=on">לוח החגים לישראל של Hebcal</a>.</p>')
$section=@"
  <h2>פעילות לסוכות 2026: הפנינג באינדי פארק</h2>
  <p>ב<a href="https://gonorth.co.il/places/אינדי-פארק/">אינדי פארק</a>, באזור יסוד המעלה, פורסם הפנינג משפחתי לימים ראשון–חמישי, 27 בספטמבר עד 1 באוקטובר 2026, בשעות 10:00–17:00. התוכנית כוללת סדנאות יצירה מחומרי טבע, הכנת חלילי במבוק ופעילויות בפארק לצד שייט. לפי המארגן, במקום קיימת סוכה. <a href="https://www.indipark.co.il/הפנינגסוכות">פרטי ההפנינג וסוגי הכרטיסים באתר הרשמי</a>.</p>
  <h3>שעות חג וכניסה לאינדי פארק</h3>
  <ul>
    <li>25 בספטמבר, ערב החג: הפארק פתוח 10:00–16:00.</li>
    <li>26 בספטמבר, חג ראשון: הפארק סגור; הקמפינג פתוח לפי הודעת האתר.</li>
    <li>27 בספטמבר–1 באוקטובר: הפארק וההפנינג פועלים 10:00–17:00.</li>
    <li>2 באוקטובר: בלוח המודעות התאריכי מופיעות שעות 10:00–17:00, אך בתחתית האתר מופיעות שעות כלליות קצרות יותר לערבי חג. יש לברר את שעת הסיום ישירות לפני הגעה ביום זה.</li>
    <li>3 באוקטובר, שמחת תורה: הפארק סגור; הקמפינג פתוח לפי הודעת האתר.</li>
  </ul>
  <p>שעת סגירת הפארק אינה שעת סיום כל הפעילויות: באתר מצוינות כניסה אחרונה ב־14:30, סיום שייט ב־15:00 וסגירת תחנות ב־15:00 או 16:00. לפני הנסיעה בדקו את <a href="https://www.indipark.co.il/">לוח המודעות הרשמי</a> ואת שעת הפעילות המבוקשת. מחיר וזמינות קמפינג יש לבדוק בעת ההזמנה.</p>

  <h2>עצירה קולינרית בגליל: יקב אדיר</h2>
  <p>למי שמתכנן יום באזור דלתון, <a href="https://gonorth.co.il/places/יקב-אדיר/">מרכז המבקרים של יקב אדיר</a> מציג טעימות יין וגבינות, ארוחות וסיורים. זהו רעיון לביקור בתיאום, ולא אירוע חג מאומת: לא אומתו שעות מיוחדות לסוכות 2026, ויש לקבל אישור מהמרכז לתאריך ולפעילות שבחרתם. <a href="https://www.adir-winery.co.il/מרכז-המבקרים/">מידע והזמנות באתר היקב</a>. אפשר להמשיך לתכנן בעזרת <a href="https://gonorth.co.il/yekavim-batzafon/">מדריך היקבים בצפון</a>.</p>

"@
$c=$c.Replace('  <h2>תכנון לפי אזור</h2>',$section+'  <h2>תכנון לפי אזור</h2>')
$c|Set-Content memory/drafts/sukkot-batzafon-2026.html -Encoding utf8
$m=[regex]::Match((Get-Content AGENTS.md -Raw),'Username:\s*(\S+)\r?\nPassword:\s*([^\r\n]+)')
$h=@{Authorization=('Basic '+[Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($m.Groups[1].Value+':'+$m.Groups[2].Value.Trim())))}
$live=Invoke-RestMethod 'https://gonorth.co.il/wp-json/wp/v2/pages/441?context=edit' -Headers $h
if($live.content.raw -cne $p.content.raw -or $live.status -ne 'draft'){throw 'Concurrent draft change'}
$body=@{content=$c}|ConvertTo-Json -Depth 5
$saved=Invoke-RestMethod 'https://gonorth.co.il/wp-json/wp/v2/pages/441' -Headers $h -Method Post -ContentType 'application/json; charset=utf-8' -Body ([Text.Encoding]::UTF8.GetBytes($body))
$verify=Invoke-RestMethod 'https://gonorth.co.il/wp-json/wp/v2/pages/441?context=edit' -Headers $h
if($verify.content.raw -cne $c -or $verify.status -ne 'draft' -or $verify.slug -ne 'sukkot-batzafon'){throw 'Draft validation failed'}
$verify|ConvertTo-Json -Depth 20|Set-Content memory/data/2026-09-22-sukkot-draft-after.json -Encoding utf8
$qa=@()
$urls=[regex]::Matches($c,'href="(https://gonorth.co.il/[^"]+)"')|ForEach-Object {$_.Groups[1].Value}|Sort-Object -Unique
foreach($url in $urls){try{$r=Invoke-WebRequest $url -TimeoutSec 20; $qa+=[pscustomobject]@{url=$url;status=[int]$r.StatusCode}}catch{$qa+=[pscustomobject]@{url=$url;status='failed'}}}
$qa|ConvertTo-Json|Set-Content memory/data/2026-09-22-sukkot-links-qa.json -Encoding utf8
[pscustomobject]@{id=$verify.id;status=$verify.status;slug=$verify.slug;title=$verify.yoast_head_json.title;description=$verify.yoast_head_json.description;links=$qa.Count;failed=@($qa|Where-Object status -ne 200).Count}|ConvertTo-Json
