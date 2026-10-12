$ErrorActionPreference='Stop'
$helper=Get-Content memory/scripts/apply-batch-02-links.ps1 -Raw;$start=$helper.IndexOf('$m=[regex]');$end=$helper.IndexOf('$receipts=@()');Invoke-Expression $helper.Substring($start,$end-$start)
$items=@(
@{id=212;guide='https://gonorth.co.il/weekend-trip-north-israel/';content=@'
<p>קפה חמה היא עגלת קפה בכניסה לקיבוץ מבוא חמה, בנקודת נוף גבוהה בדרום רמת הגולן שמשקיפה אל הכנרת. הישיבה בחוץ והנוף הפתוח הופכים אותה לעצירה מתאימה בדרך לחמת גדר, למצפה שלום ולאתרי דרום הגולן.</p>
<h2>מה מחכה בעגלה?</h2>
<p>לצד קפה ומשקאות מוגשים מאפים ונשנושים, ובמקורות העסק מופיעים גם מוצ׳י, בירה ומנות מתחלפות. מכיוון שעגלות קפה משנות תפריט לפי עונה ומלאי, כדאי לבדוק במקום אילו אפשרויות קיימות, במיוחד במקרה של רגישויות או העדפה טבעונית.</p>
<h2>מתי כדאי להגיע?</h2>
<p>העגלה מזוהה במיוחד עם נוף ושקיעה, אך מזג האוויר בגולן יכול להשפיע על הישיבה ועל הפעילות. מומלץ להביא שכבה חמה בשעות הערב, ולהתקשר לפני נסיעה ארוכה. החניה והנגישות בשטח פתוח עשויות להשתנות ויש לברר אותן מראש.</p>
<h2>שעות ותכנון</h2>
<p>מקור שעות עדכני מציג פעילות בשישי ובשבת 09:00–17:00 וסגירה בשאר השבוע. מאחר שמדובר בעגלה עונתית, יש לאמת בפייסבוק או בטלפון לפני ההגעה, ובפרט בחגים ובמזג אוויר חריג. לעוד תחנות באזור ראו את <a href="https://gonorth.co.il/weekend-trip-north-israel/">מדריך סוף השבוע בצפון</a>.</p>
'@},
@{id=209;guide='https://gonorth.co.il/weekend-trip-north-israel/';content=@'
<p>דה פייפ הוא פודטראק וקרפרי ברחוב ירדן במושב רמות, במדרונות הגולן מעל הכנרת. המקום מתאים לעצירת בוקר, ארוחה קלה או בילוי ערב, עם ישיבה באווירה כפרית בדרך לחופי מזרח הכנרת ולאתרי דרום הגולן.</p>
<h2>מה בתפריט?</h2>
<p>ההתמחות היא בקרפים מתוקים ומלוחים, ולצדם כריכים, טוסטים, ארוחות בוקר, מאפים ופיצה. מוגשים גם קפה ומשקאות קרים. מקורות עסקיים מציגים כשרות, אך מי שתלוי בסוג ההשגחה צריך לבקש לראות תעודה עדכנית במקום.</p>
<h2>למי זה מתאים?</h2>
<p>זהו מקום נוח לזוגות ולמשפחות, ואפשר לבחור ארוחה מלאה או עצירה קצרה. התפריט והשירות עשויים להשתנות בין שעות הבוקר לערב, ולכן מומלץ לבדוק מראש אם מגיעים בשביל מנה מסוימת או עם צורך תזונתי.</p>
<h2>שעות ותכנון</h2>
<p>עמוד עסק מנוהל מציג א׳–ה׳ 09:00–13:30 ו־18:00–21:30, שישי 09:00–13:30 ושבת 09:00–14:00. מקור נוסף מציג שעות שונות ושבת סגורה, ולכן חובה לאמת בטלפון לפני נסיעה. לעוד רעיונות ראו את <a href="https://gonorth.co.il/weekend-trip-north-israel/">מדריך סוף השבוע בצפון</a>.</p>
'@},
@{id=419;guide='https://gonorth.co.il/10-%d7%90%d7%98%d7%a8%d7%a7%d7%a6%d7%99%d7%95%d7%aa-%d7%9e%d7%95%d7%9e%d7%9c%d7%a6%d7%95%d7%aa-%d7%91%d7%92%d7%9c%d7%99%d7%9c-%d7%9c%d7%97%d7%95%d7%a4%d7%a9%d7%aa-%d7%9e%d7%a9%d7%a4%d7%97%d7%94/';content=@'
<p>טריפונל הוא מתחם באולינג ובילוי במג׳דל שמס, למרגלות החרמון. זהו מקום מקורה שמתאים למשפחות, לחברים ולאירועים, במיוחד כשמחפשים פעילות שאינה תלויה במזג האוויר.</p>
<h2>מה יש במתחם?</h2>
<p>במקום חמישה מסלולי באולינג, שכל אחד מהם מיועד לעד שמונה משתתפים לפי מקור תיירותי עדכני. לצד הבאולינג מופיעים גם סנוקר, הוקי אוויר ושאפלבורד. בר האוכל מציע מנות כמו המבורגר, שניצלונים, פיצות, סלטים וקינוחים, אך התפריט עשוי להשתנות.</p>
<h2>ימי הולדת וקבוצות</h2>
<p>אפשר לתאם ימי הולדת ומפגשים קבוצתיים ולברר על חבילות הכוללות משחק ואוכל. לקבוצה גדולה מומלץ להזמין כמה מסלולים מראש ולוודא את משך הסבב, מספר המשתתפים בכל מסלול והתאמות לילדים.</p>
<h2>לפני שמגיעים</h2>
<p>לא נמצאו שעות רשמיות עקביות במקורות שנבדקו, ולכן מומלץ להתקשר לפני הנסיעה, בפרט בחופשות ובסופי שבוע. שאלו גם על מחיר למשחק, זמינות נעליים וחניה. לעוד בילויים למשפחה ראו את <a href="https://gonorth.co.il/10-%d7%90%d7%98%d7%a8%d7%a7%d7%a6%d7%99%d7%95%d7%aa-%d7%9e%d7%95%d7%9e%d7%9c%d7%a6%d7%95%d7%aa-%d7%91%d7%92%d7%9c%d7%99%d7%9c-%d7%9c%d7%97%d7%95%d7%a4%d7%a9%d7%aa-%d7%9e%d7%a9%d7%a4%d7%97%d7%94/">מדריך האטרקציות למשפחות</a>.</p>
'@},
@{id=213;guide='https://gonorth.co.il/weekend-trip-north-israel/';content=@'
<p>דורא היא עגלת קפה כשרה בתוך משתלת הגולן במושב נוב שבדרום רמת הגולן. השילוב בין קפה, צמחייה וסביבה כפרית יוצר עצירה נעימה למטיילים באזור, עם חניה בסמוך ואפשרות לשבת בין הצמחים.</p>
<h2>מה אוכלים?</h2>
<p>בתפריט מופיעים קפה, כריכים, סלטים, מאפים, מיצים ופיצות, ולעיתים אפשר להזמין סלי פיקניק לקבוצות. התפריט משתנה ולכן יש לברר מראש במקרה של רגישויות, טבעונות או צורך בכשרות מסוימת ולבדוק תעודה עדכנית במקום.</p>
<h2>למי זה מתאים?</h2>
<p>העגלה מתאימה לזוגות, למשפחות ולקבוצות קטנות שמחפשות הפסקה בדרך. המיקום בתוך משתלה מוסיף עניין לילדים ולחובבי צמחים, אך חשוב להשגיח על ילדים ולכבד את אזורי העבודה והמכירה.</p>
<h2>שעות ותיאום</h2>
<p>מקורות תיירות מקומיים מאמתים את מיקום העגלה ואת מספר הטלפון, אך לא מספקים לוח שעות עדכני ועקבי. לכן אין להסתמך על השעות הישנות שהופיעו בדף ויש להתקשר לפני הגעה. אפשר לשלב את העצירה בעזרת <a href="https://gonorth.co.il/weekend-trip-north-israel/">מדריך סוף השבוע בצפון</a>.</p>
'@}
)
$plan=@();foreach($i in $items){$r=CallWp 'wp.getPost' $i.id;$live=($r.methodResponse.params.param.value.struct.member|? name -eq 'post_content').value.InnerText;$plan+=[pscustomobject]@{id=$i.id;before=$live;after=$i.content.Trim();guide=$i.guide}}
$backup='memory/data/2026-09-23-batch-13-publication-backup.json';if(Test-Path $backup){throw 'Backup exists'};$plan|ConvertTo-Json -Depth 5|Set-Content $backup -Encoding utf8
$q=@();foreach($p in $plan){$r=CallWp 'wp.getPost' $p.id;$live=($r.methodResponse.params.param.value.struct.member|? name -eq 'post_content').value.InnerText;if($live -cne $p.before){throw 'Concurrent'};$x='<param><value><struct><member><name>post_content</name><value><string>'+(X $p.after)+'</string></value></member></struct></value></param>';$r=CallWp 'wp.editPost' $p.id $x;if($r.methodResponse.params.param.value.boolean -ne '1'){throw 'Write failed'};$r=CallWp 'wp.getPost' $p.id;$saved=($r.methodResponse.params.param.value.struct.member|? name -eq 'post_content').value.InnerText;if($saved -cne $p.after){throw 'Mismatch'};$pub=Invoke-RestMethod ('https://gonorth.co.il/wp-json/geodir/v2/places/'+$p.id);$page=Invoke-WebRequest $pub.link;$qa=[pscustomobject]@{id=$p.id;url=$pub.link;http=[int]$page.StatusCode;before=$p.before.Length;after=$p.after.Length;exact=$true;guide=$page.Content.Contains($p.guide)};$q+=$qa;$q|ConvertTo-Json|Set-Content memory/data/2026-09-23-batch-13-publication-qa.json -Encoding utf8;$qa|ConvertTo-Json -Compress}