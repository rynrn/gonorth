$ErrorActionPreference='Stop'
$helper=Get-Content memory/scripts/apply-batch-02-links.ps1 -Raw
$start=$helper.IndexOf('$m=[regex]')
$end=$helper.IndexOf('$receipts=@()')
Invoke-Expression $helper.Substring($start,$end-$start)
$guide='https://gonorth.co.il/%d7%9e%d7%a1%d7%9c%d7%95%d7%9c-%d7%98%d7%99%d7%95%d7%9c-%d7%91%d7%a8%d7%9e%d7%aa-%d7%94%d7%92%d7%95%d7%9c%d7%9f-%d7%9e%d7%94%d7%91%d7%a0%d7%99%d7%90%d7%a1-%d7%95%d7%a2%d7%93-%d7%aa%d7%9c-%d7%a4/'
$items=@(
 [pscustomobject]@{id=413;content=@'
<p>בית הבד של הגולן – Olea Essence הוא מרכז מבקרים בקצרין שמחבר בין מסורת גידול הזית לבין ייצור מודרני של שמן זית ומוצרי טיפוח. הביקור מתאים למי שרוצה להוסיף לטיול בגולן עצירה קצרה, מקורה ומלמדת, בכתובת דרך השמן 2.</p>
<h2>מה כולל הביקור?</h2>
<p>הסיור עוסק בהיסטוריה של הזית ובשימושיו, כולל הדגמה הקשורה לסבון טבעי, הצצה לאזורי הייצור והמעבדות והיכרות עם תהליך הפקת השמן. בעונת המסיק אפשר לעיתים לראות את העבודה בבית הבד עצמו. הביקור מסתיים בחנות המפעל, עם היכרות וטעימה של שמני זית ומוצרים נוספים.</p>
<h2>למי זה מתאים?</h2>
<p>זוהי עצירה נוחה למשפחות, זוגות וקבוצות שמטיילים באזור קצרין ורוצים פעילות שאינה תלויה לחלוטין במזג האוויר. מומלץ לתאם את הסיור מראש, במיוחד לקבוצה או בתקופות עמוסות, ולבדוק בעת ההזמנה את משך הפעילות ואת המחיר העדכני.</p>
<h2>שעות ותכנון ההגעה</h2>
<p>לפי האתר הרשמי, מרכז המבקרים פתוח בימים א׳–ה׳ 10:00–16:00 וביום ו׳ 10:00–15:00. כדאי לאמת את השעות לפני היציאה, ובפרט בערבי חג. אפשר לשלב את העצירה עם רעיונות נוספים מתוך <a href="GUIDE">מסלול הטיול ברמת הגולן</a>.</p>
'@.Replace('GUIDE',$guide)},
 [pscustomobject]@{id=187;content=@'
<p>שמורת טבע נחל המשושים מציגה את נופי הבזלת של הגולן: ערוצי נחל, מפלים, צמחיית בתה ובריכת המשושים המפורסמת, שקירותיה בנויים מעמודי בזלת בעלי צורה גאומטרית. השמורה היא חלק משמורת יער יהודיה, וההגעה אל החניון המוסדר היא מכביש 888, מצפון לחד נס.</p>
<h2>איזה מסלול לבחור?</h2>
<p>למבקרים שמעדיפים הליכה קלה יש שביל תצפית נגיש וקצר, באורך כ־300 מטר ובמשך כ־10–15 דקות. אפשר להוסיף את שביל הדולמן או לבחור בירידה לבריכת המשושים: הירידה אורכת בקירוב 20 דקות והעלייה כ־30 דקות. מסלול הנחל אל הבריכה ארוך ומאתגר יותר, והמסלול המלא מנחל המשושים לזוויתן מיועד למטיילים מנוסים ודורש יותר משש שעות.</p>
<h2>רחצה ובטיחות</h2>
<p>הרחצה בבריכה מותרת בהתאם להודעות האתר ולתוצאות בדיקות המים, אך אין לקפוץ למים. בימי חשש לשיטפונות אין להיכנס לערוץ. הכניסה למסלול הבריכה מותרת רק עד שעתיים וחצי לפני סגירת האתר, ולכן חשוב להגיע מוקדם ולהתאים את המסלול לשעות האור, למזג האוויר וליכולת המטיילים.</p>
<h2>לפני שמגיעים</h2>
<p>מומלץ להירשם מראש באתר רשות הטבע והגנים כדי להבטיח מקום. שעות הפתיחה משתנות בין קיץ, חורף וערבי חג, והכניסה לשמורה נסגרת שעה לפני שעת הסגירה המפורסמת. בחניון יש אזור פיקניק, שירותים, מי שתייה ושירותי נגישות. אפשר לשלב את הביקור עם תחנות נוספות מתוך <a href="GUIDE">מסלול הטיול ברמת הגולן</a>.</p>
'@.Replace('GUIDE',$guide)}
)
$plan=@()
foreach($item in $items){$r=CallWp 'wp.getPost' $item.id;$live=($r.methodResponse.params.param.value.struct.member|Where-Object name -eq 'post_content').value.InnerText;$plan+=[pscustomobject]@{id=$item.id;before=$live;after=$item.content.Trim()}}
$backup='memory/data/2026-09-23-batch-06-publication-backup.json'
if(Test-Path $backup){throw 'Backup exists; inspect before retry'}
$plan|ConvertTo-Json -Depth 5|Set-Content $backup -Encoding utf8
$receipts=@()
foreach($p in $plan){
 $r=CallWp 'wp.getPost' $p.id;$live=($r.methodResponse.params.param.value.struct.member|Where-Object name -eq 'post_content').value.InnerText
 if($live -cne $p.before){throw "Concurrent change $($p.id)"}
 $extra='<param><value><struct><member><name>post_content</name><value><string>'+(X $p.after)+'</string></value></member></struct></value></param>'
 $r=CallWp 'wp.editPost' $p.id $extra;if($r.methodResponse.params.param.value.boolean -ne '1'){throw "Write failed $($p.id)"}
 $r=CallWp 'wp.getPost' $p.id;$saved=($r.methodResponse.params.param.value.struct.member|Where-Object name -eq 'post_content').value.InnerText;if($saved -cne $p.after){throw "Readback mismatch $($p.id)"}
 $public=Invoke-RestMethod ('https://gonorth.co.il/wp-json/geodir/v2/places/'+$p.id) -TimeoutSec 40;$page=Invoke-WebRequest $public.link -TimeoutSec 40
 $qa=[pscustomobject]@{id=$p.id;url=$public.link;status=$public.status;http=[int]$page.StatusCode;before_chars=$p.before.Length;after_chars=$p.after.Length;exact_readback=$true;h2=([regex]::Matches($p.after,'<h2>')).Count;guide_link=$page.Content.Contains($guide);at=(Get-Date).ToUniversalTime().ToString('o')}
 $receipts+=$qa;$receipts|ConvertTo-Json -Depth 5|Set-Content memory/data/2026-09-23-batch-06-publication-qa.json -Encoding utf8;$qa|ConvertTo-Json -Compress
}