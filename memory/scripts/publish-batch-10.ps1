$ErrorActionPreference='Stop'
$helper=Get-Content memory/scripts/apply-batch-02-links.ps1 -Raw;$start=$helper.IndexOf('$m=[regex]');$end=$helper.IndexOf('$receipts=@()');Invoke-Expression $helper.Substring($start,$end-$start)
$items=@(
@{id=189;guide='https://gonorth.co.il/maslulei-mayim-batzafon/';content=@'
<p>שמורת טבע יהודיה משתרעת בלב הגולן וכוללת קניונים בזלתיים, מפלים, בריכות ושטחי יער. זהו מרחב גדול עם כמה מסלולים ברמות קושי שונות, ולכן הביקור דורש בחירה מראש של מסלול ולא רק הגעה לשער השמורה.</p>
<h2>איך בוחרים מסלול?</h2>
<p>באתר רשות הטבע והגנים מופיעים מסלולי נחל יהודיה והזוויתן, אך פתיחת קטעים משתנה בעקבות מזג אוויר, בטיחות ובדיקות מים. מסלול נחל יהודיה מומלץ מגיל 10 ומחייב ידיעת שחייה. יש לבדוק ביום הטיול אילו מסלולים פתוחים ומהי שעת הכניסה האחרונה למסלול שנבחר.</p>
<h2>מה חשוב לבטיחות?</h2>
<p>יש לצאת עם נעלי הליכה מתאימות, מים, כובע ומפת מסלולים, ולהתאים את המסלול לכושר ולשעות האור. אין להסתמך על מידע ישן לגבי ירידות למפלים או חיבורים בין נחלים, משום שקטעים עשויים להיסגר. מומלץ להירשם מראש ולעיין במבזקי רשות הטבע והגנים.</p>
<h2>שעות ותכנון ההגעה</h2>
<p>בקיץ השמורה פתוחה א׳–ה׳ ושבת 08:00–17:00 ובשישי וערבי חג עד 16:00; ביולי–אוגוסט הפתיחה ב־07:00. בחורף הסגירה מוקדמת בשעה. הכניסה האחרונה לשמורה היא עד שעתיים וחצי לפני הסגירה, ולעיתים מוקדם יותר למסלול מסוים. ראו גם <a href="https://gonorth.co.il/maslulei-mayim-batzafon/">מסלולי מים נוספים בצפון</a>.</p>
'@},
@{id=177;guide='https://gonorth.co.il/maslulei-mayim-batzafon/';content=@'
<p>עין תינה הוא מסלול מים קצר בשמורת טבע מורדות הגולן, בין גדות לגונן. הולכים מתוך עמק החולה במעלה פלג זורם, בין צמחיית נחלים ועצי תאנה, עד לנקודה שממנה נראים המים פורצים בסבך ולתצפית חזרה אל העמק.</p>
<h2>אורך המסלול ורמת הקושי</h2>
<p>אורך המסלול כ־1.5 ק״מ, מהם כחצי קילומטר בתוך הפלג, ומשך הטיול כשעה עד שעתיים. רובו קליל, אך העלייה האחרונה כוללת טיפוס מעט מאתגר על סלעי בזלת רטובים. המסלול מתחיל ומסתיים בחניון עין תינה שעל כביש 918, בין סימני קילומטר 23–24.</p>
<h2>ציוד וכללי בטיחות</h2>
<p>חובה להגיע בנעליים המתאימות להליכה במים; מומלץ להביא בגדים להחלפה, מים לשתייה וכובע. אין לשתות ממי המעיין, להבעיר אש, ללון בשמורה או לרדת משבילים מסומנים. אין לחצות גדרות בגולן בגלל חשש לשדות מוקשים, ויש לוותר על הטיול בשרב או בסכנת שיטפונות.</p>
<h2>מתי כדאי להגיע?</h2>
<p>המסלול מתאים במיוחד לימים חמים, אך כמות המים ותנאי הדרך עשויים להשתנות. בעונת הפריחה אפשר לראות אירוס ענף צהוב ונדיר. לפני היציאה יש לבדוק תחזית ומבזקי רשות הטבע והגנים. לרעיונות נוספים ראו את <a href="https://gonorth.co.il/maslulei-mayim-batzafon/">מדריך מסלולי המים בצפון</a>.</p>
'@},
@{id=204;guide='https://gonorth.co.il/weekend-trip-north-israel/';content=@'
<p>קפה מוייז הוא פודטראק במתחם לול ארט במושב רמות, מול נוף הכנרת, הרי הגליל והגולן. הוא מאפשר לשלב קפה וארוחה קלה עם סדנת יצירה, ביקור בגלריה או עצירה בדרך לאתרי דרום הגולן.</p>
<h2>מה מחכה במקום?</h2>
<p>הקפה פועל בחצר המתחם ומציע ישיבה מול הנוף. מכיוון שהתפריט עשוי להשתנות, כדאי לבדוק במקום את המאפים, הכריכים, המשקאות והאפשרויות התזונתיות הזמינות באותו יום. קבוצות יכולות לתאם אירוח אישי גם מעבר לשעות הפעילות הרגילות.</p>
<h2>שילוב עם לול ארט</h2>
<p>לול ארט מציע סדנאות DIY לילדים ולמבוגרים בהזמנה מראש. אפשר לתכנן ביקור שבו חלק מהמשפחה משתתף בסדנה ולאחר מכן יושבים בקפה, אך יש להזמין את הסדנה דרך האתר ולבדוק את כללי הכניסה והאוכל של המתחם.</p>
<h2>שעות ותכנון ההגעה</h2>
<p>לפי אתר המתחם, קפה מוייז פתוח בימים א׳–ה׳ 09:00–14:00 וביום ו׳ 08:30–15:30, וסגור בשבת. שעות עונתיות ואירוח קבוצות עשויים להשתנות, ולכן מומלץ לבדוק לפני נסיעה. לעוד עצירות באזור ראו את <a href="https://gonorth.co.il/weekend-trip-north-israel/">מדריך סוף השבוע בצפון</a>.</p>
'@}
)
$plan=@();foreach($i in $items){$r=CallWp 'wp.getPost' $i.id;$live=($r.methodResponse.params.param.value.struct.member|? name -eq 'post_content').value.InnerText;$plan+=[pscustomobject]@{id=$i.id;before=$live;after=$i.content.Trim();guide=$i.guide}}
$backup='memory/data/2026-09-23-batch-10-publication-backup.json';if(Test-Path $backup){throw 'Backup exists'};$plan|ConvertTo-Json -Depth 5|Set-Content $backup -Encoding utf8
$q=@();foreach($p in $plan){$r=CallWp 'wp.getPost' $p.id;$live=($r.methodResponse.params.param.value.struct.member|? name -eq 'post_content').value.InnerText;if($live -cne $p.before){throw "Concurrent $($p.id)"};$x='<param><value><struct><member><name>post_content</name><value><string>'+(X $p.after)+'</string></value></member></struct></value></param>';$r=CallWp 'wp.editPost' $p.id $x;if($r.methodResponse.params.param.value.boolean -ne '1'){throw 'Write failed'};$r=CallWp 'wp.getPost' $p.id;$saved=($r.methodResponse.params.param.value.struct.member|? name -eq 'post_content').value.InnerText;if($saved -cne $p.after){throw 'Readback mismatch'};$pub=Invoke-RestMethod ('https://gonorth.co.il/wp-json/geodir/v2/places/'+$p.id);$page=Invoke-WebRequest $pub.link;$qa=[pscustomobject]@{id=$p.id;url=$pub.link;http=[int]$page.StatusCode;before_chars=$p.before.Length;after_chars=$p.after.Length;exact_readback=$true;h2=([regex]::Matches($p.after,'<h2>')).Count;guide_link=$page.Content.Contains($p.guide)};$q+=$qa;$q|ConvertTo-Json -Depth 5|Set-Content memory/data/2026-09-23-batch-10-publication-qa.json -Encoding utf8;$qa|ConvertTo-Json -Compress}