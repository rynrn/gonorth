$ErrorActionPreference='Stop'
$source=Get-Content -Raw memory/data/2026-09-26-internal-link-content-export.json|ConvertFrom-Json
function Row([int]$id){$r=$source.rows|Where-Object id -eq $id;if(-not $r){throw "Missing $id"};return $r}
$w48=Row 48;$w197=Row 197;$c229=Row 229;$r335=Row 335;$g268=Row 268;$g267=Row 267;$g95=Row 95
$updates=@()
function AddUpdate([object]$row,[string]$new,[string]$reason){
 if($new -ceq [string]$row.content){throw "No change for $($row.id)"}
 $script:updates += [pscustomobject]@{id=[int]$row.id;title=[string]$row.title;url=[string]$row.url;reason=$reason;old_content=[string]$row.content;new_content=$new;old_sha256=([Convert]::ToHexString([Security.Cryptography.SHA256]::HashData([Text.Encoding]::UTF8.GetBytes([string]$row.content))).ToLowerInvariant());new_sha256=([Convert]::ToHexString([Security.Cryptography.SHA256]::HashData([Text.Encoding]::UTF8.GetBytes($new))).ToLowerInvariant())}
}
$n268=[string]$g268.content
$n268=$n268.Replace('יקב אסף, יקב הר אודם, יקב מיקה','יקב אסף, <a href="'+$w48.url+'">יקב הר אודם</a>, יקב מיקה')
$n268=$n268.Replace('דלתון, כרם בן זמרה, רמות נפתלי, ספסופה','דלתון, כרם בן זמרה, <a href="'+$w197.url+'">יקב רמות נפתלי</a>, ספסופה')
AddUpdate $g268 $n268 'קישורים הקשריים לשני יקבים שמוזכרים במדריך'
$n48=[string]$w48.content
$n48 += "`n<p>לתכנון עצירות יין נוספות באזור, עיינו ב<a href=`"$($g268.url)`">מדריך היקבים בצפון</a> ובדקו מראש תיאום ושעות פעילות.</p>"
AddUpdate $w48 $n48 'קישור חזרה למדריך היקבים'
$n267=[string]$g267.content
$needle='<p><strong>טיפ:</strong> בעונות עמוסות, מסעדה קרובה מדי לחוף יכולה להיות צפופה. לפעמים עדיף להתרחק עשר דקות ולקבל חוויה רגועה יותר.</p>'
$insert=$needle+"`n<p>בדרך לכנרת דרך הגליל התחתון אפשר לבדוק גם את <a href=`"$($c229.url)`">שישו קפה במושב הזורעים</a>, ולוודא באתר העסק את שעות הפעילות העדכניות.</p>"
if(-not $n267.Contains($needle)){throw 'Restaurant insertion point missing'}
$n267=$n267.Replace($needle,$insert)
AddUpdate $g267 $n267 'קישור הקשרי לבית קפה בגליל התחתון'
$n229=[string]$c229.content
$n229 += "`n<p>לעוד אפשרויות אוכל לפי אזור וסגנון, עברו אל <a href=`"$($g267.url)`">מדריך המסעדות המומלצות בצפון</a>.</p>"
AddUpdate $c229 $n229 'קישור חזרה למדריך המסעדות'
$n95=[string]$g95.content
$needle95='כפר אל-רום הסמוך מציע נקודות תצפית מצוינות, ובסביבה תמצאו גם פעילויות אתגריות כמו אופני הרים ורכיבה על סוסים.'
$replace95=$needle95+" למי שמחפש רכיבה בתיאום מראש, אפשר לבדוק את <a href=`"$($r335.url)`">גיל גולן — מרכז רכיבה באלוני הבשן</a>."
if(-not $n95.Contains($needle95)){throw 'Golan insertion point missing'}
$n95=$n95.Replace($needle95,$replace95)
AddUpdate $g95 $n95 'קישור הקשרי למרכז רכיבה בגולן'
$n335=[string]$r335.content
if($n335 -notmatch [regex]::Escape([string]$g95.url)){throw 'Existing backlink from 335 to 95 is missing'}
$updates|ConvertTo-Json -Depth 6|Set-Content -LiteralPath memory/data/2026-09-26-issue-45-batch-03-backup-and-payload.json -Encoding utf8
$utf8=[Text.UTF8Encoding]::new($false)
foreach($u in $updates){[IO.File]::WriteAllText((Join-Path (Resolve-Path memory/data).Path ("issue-45-$($u.id)-new.html")),[string]$u.new_content,$utf8)}
$updates|Select-Object id,title,reason,@{n='old_chars';e={$_.old_content.Length}},@{n='new_chars';e={$_.new_content.Length}}|Format-Table -AutoSize -Wrap
