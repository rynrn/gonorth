$ErrorActionPreference='Stop'
$helper=Get-Content memory/scripts/apply-batch-02-links.ps1 -Raw
$start=$helper.IndexOf('$m=[regex]')
$end=$helper.IndexOf('$receipts=@()')
Invoke-Expression $helper.Substring($start,$end-$start)
$items=@(
 [pscustomobject]@{id=193;guide='https://gonorth.co.il/yekavim-batzafon/';content=@'
<p>יקב רמת הגולן ומרכז המבקרים שלו נמצאים בקצרין ומציעים היכרות עם עולם היין של האזור — מהכרמים הגבוהים והאקלים המקומי ועד תהליך הייצור והטעימה. זהו יעד מתאים לחובבי יין, לזוגות ולקבוצות שרוצים לשלב בטיול בגולן עצירה מקצועית ומסודרת.</p>
<h2>איזו חוויה לבחור?</h2>
<p>היקב מציע כמה חוויות באורכים שונים. הסיור הקלאסי נמשך כשעה ומשלב היכרות עם תהליך היין וטעימות; קיימים גם סיור מעמיק של כ־90 דקות, סיור פרטי של כשעתיים, חוויה מקצועית ארוכה יותר וסיור כרמים בצפון הגולן. ההיצע, המחירים והשפות עשויים להשתנות, ולכן כדאי לבחור ולהזמין מראש באתר היקב.</p>
<h2>אפשר להגיע בלי סיור?</h2>
<p>בשעות הפעילות אפשר להגיע למרכז המבקרים, לטעום יינות ולשבת במקום גם בלי להצטרף לסיור. לסיורים עצמם נדרש תיאום מראש. כל יינות היקב כשרים למהדרין וכשרים לפסח לפי המידע הרשמי של היקב; הטעימות מיועדות לבגירים ויש לתכנן נהג תורן.</p>
<h2>שעות ותכנון הביקור</h2>
<p>מרכז המבקרים פתוח בימים א׳–ה׳ 09:00–17:30. בימי שישי וערבי חג הוא פתוח עד 14:00 בשעון חורף ועד 15:00 בשעון קיץ. המקום סגור בשבתות, בחגי ישראל ובימי זיכרון. מומלץ לבדוק זמינות לפני הנסיעה. לעוד רעיונות באזור ראו את <a href="https://gonorth.co.il/yekavim-batzafon/">מדריך היקבים בצפון</a>.</p>
'@},
 [pscustomobject]@{id=191;guide='https://gonorth.co.il/maslulei-mayim-batzafon/';content=@'
<p>שמורת טבע נחל חרמון, המוכרת כבניאס, משלבת מים זורמים כל השנה, צמחיית נחלים, עתיקות ומסלולי הליכה למרגלות החרמון. בשמורה שני מתחמי ביקור מרכזיים: אזור המעיינות והעתיקות, ואזור מפל הבניאס והשביל התלוי. כדאי לבחור מראש את נקודת ההתחלה ואת אורך המסלול.</p>
<h2>מה רואים בכל מתחם?</h2>
<p>במתחם המעיינות פוגשים את מקורות הנחל ואת שרידי המקדשים והמבנים הקדומים. מתחם המפל מוביל אל מפל הבניאס, המוגדר בידי רשות הטבע והגנים כמפל הגדול בישראל, ואל השביל התלוי שמאפשר ללכת מעל הערוץ השוצף. קיימים גם מסלולי המשך, ולכן חשוב לעיין במפת השמורה לפני היציאה.</p>
<h2>למי הביקור מתאים?</h2>
<p>אפשר להתאים את הביקור למשפחות ולמטיילים בדרגות קושי שונות. באזור המעיינות הונגשו החניה, מרכז השירות, התצוגה הארכיאולוגית, השירותים ושבילים עד למעיינות, לצד פינות פיקניק וברזייה. קטעים אחרים כוללים מדרגות ושבילים שאינם נגישים, ולכן יש לבדוק מראש את המסלול המתאים.</p>
<h2>שעות והכנה מראש</h2>
<p>בשעות הקיץ האתר פתוח א׳–ה׳ ושבת 08:00–17:00, ובשישי ובערבי חג 08:00–16:00. בחורף הסגירה מוקדמת בשעה; בערבי ראש השנה, פסח ויום כיפור השעות מצומצמות יותר. הכניסה נסגרת שעה לפני שעת הסגירה, ומומלץ להירשם מראש. לעוד אפשרויות מים באזור ראו את <a href="https://gonorth.co.il/maslulei-mayim-batzafon/">מדריך מסלולי המים בצפון</a>.</p>
'@}
)
$plan=@()
foreach($item in $items){$r=CallWp 'wp.getPost' $item.id;$live=($r.methodResponse.params.param.value.struct.member|Where-Object name -eq 'post_content').value.InnerText;$plan+=[pscustomobject]@{id=$item.id;before=$live;after=$item.content.Trim();guide=$item.guide}}
$backup='memory/data/2026-09-23-batch-07-publication-backup.json'
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
 $qa=[pscustomobject]@{id=$p.id;url=$public.link;status=$public.status;http=[int]$page.StatusCode;before_chars=$p.before.Length;after_chars=$p.after.Length;exact_readback=$true;h2=([regex]::Matches($p.after,'<h2>')).Count;guide_link=$page.Content.Contains($p.guide);at=(Get-Date).ToUniversalTime().ToString('o')}
 $receipts+=$qa;$receipts|ConvertTo-Json -Depth 5|Set-Content memory/data/2026-09-23-batch-07-publication-qa.json -Encoding utf8;$qa|ConvertTo-Json -Compress
}