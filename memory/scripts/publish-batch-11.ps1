$ErrorActionPreference='Stop'
$helper=Get-Content memory/scripts/apply-batch-02-links.ps1 -Raw;$start=$helper.IndexOf('$m=[regex]');$end=$helper.IndexOf('$receipts=@()');Invoke-Expression $helper.Substring($start,$end-$start)
$items=@(
@{id=395;guide='https://gonorth.co.il/weekend-trip-north-israel/';content=@'
<p>דיר עזיז הוא דיר עיזים ומגבנה משפחתית במושב כנף שבדרום רמת הגולן. הביקור מחבר בין מפגש קרוב עם העיזים לבין היכרות עם ייצור גבינות במקום, ומתאים למשפחות ולחובבי קולינריה מקומית שמחפשים פעילות קטנה ואישית.</p>
<h2>מה כולל הביקור?</h2>
<p>הסיור נמשך כשעה ומתחלק לשני חלקים: תחילה מבקרים בדיר, לומדים על גידול העיזים והטיפול בהן וניתן ללטף אותן; אחר כך עוברים למגבנה, מקבלים הסבר על סוגי הגבינות וההבדלים ביניהן וטועמים גבינות טריות ובשלות שמיוצרות במקום.</p>
<h2>תיאום והתאמה</h2>
<p>זהו מרכז מבקרים קטן, ולכן מגיעים רק בתיאום מראש בוואטסאפ או בטלפון. הסיור בתשלום, ומומלץ לברר בזמן התיאום את המחיר, התאמה לילדים, נגישות וזמינות טעימות. אין להגיע ללא תיאום משום שהפעילות בדיר ובמגבנה קודמת לאירוח.</p>
<h2>שעות ותכנון באזור</h2>
<p>הפעילות המפורסמת היא בדרך כלל בין 10:00 ל־16:00, בתיאום מראש בלבד. כדאי לאמת את השעה ביום הביקור וללבוש בגדים ונעליים שמתאימים לסביבה חקלאית. אפשר לשלב את העצירה עם אתרי דרום הגולן בעזרת <a href="https://gonorth.co.il/weekend-trip-north-israel/">מדריך סוף השבוע בצפון</a>.</p>
'@},
@{id=387;guide='https://gonorth.co.il/10-%d7%90%d7%98%d7%a8%d7%a7%d7%a6%d7%99%d7%95%d7%aa-%d7%9e%d7%95%d7%9e%d7%9c%d7%a6%d7%95%d7%aa-%d7%91%d7%92%d7%9c%d7%99%d7%9c-%d7%9c%d7%97%d7%95%d7%a4%d7%a9%d7%aa-%d7%9e%d7%a9%d7%a4%d7%97%d7%94/';content=@'
<p>קטיף עצמי רוני הוא מטע משפחתי בכפר בוקעאתא שבצפון רמת הגולן. בעונת הפרי המבקרים קוטפים ישירות מהעצים ונהנים מאווירת מטע, נוף צפוני ופינות שמתאימות לעצירה משפחתית.</p>
<h2>מה קוטפים?</h2>
<p>הפרי משתנה לפי מועד ההבשלה ויכול לכלול דובדבנים, תפוחים ואגסים. במקורות העסקיים מופיעים אכילה במטע ומאכלים דרוזיים בסופי שבוע, אך ההיצע אינו קבוע. כדאי לברר לפני ההגעה מה בשל לקטיף, מה כלול בכרטיס ואילו שירותים פועלים באותו יום.</p>
<h2>מתי מגיעים?</h2>
<p>עונת הדובדבנים קצרה ותלויה במזג האוויר, ובהמשך הקיץ והסתיו עשויים להבשיל פירות אחרים. מקורות שונים מציגים שעות וטלפונים שאינם זהים, ולכן אין להסתמך על לוח קבוע: יש לתאם מראש עם העסק ולאשר שהמטע פתוח ושנותר פרי.</p>
<h2>הכנה לביקור</h2>
<p>לניווט חפשו “קטיף עצמי רוני, בוקעאתא”. מומלץ להגיע עם כובע, מים ונעליים סגורות שמתאימות לקרקע חקלאית, ולברר לגבי חניה, נגישות, תשלום ואוכל. לעוד פעילויות משפחתיות ראו את <a href="https://gonorth.co.il/10-%d7%90%d7%98%d7%a8%d7%a7%d7%a6%d7%99%d7%95%d7%aa-%d7%9e%d7%95%d7%9e%d7%9c%d7%a6%d7%95%d7%aa-%d7%91%d7%92%d7%9c%d7%99%d7%9c-%d7%9c%d7%97%d7%95%d7%a4%d7%a9%d7%aa-%d7%9e%d7%a9%d7%a4%d7%97%d7%94/">מדריך האטרקציות למשפחות</a>.</p>
'@}
)
$plan=@();foreach($i in $items){$r=CallWp 'wp.getPost' $i.id;$live=($r.methodResponse.params.param.value.struct.member|? name -eq 'post_content').value.InnerText;$plan+=[pscustomobject]@{id=$i.id;before=$live;after=$i.content.Trim();guide=$i.guide}}
$backup='memory/data/2026-09-23-batch-11-publication-backup.json';if(Test-Path $backup){throw 'Backup exists'};$plan|ConvertTo-Json -Depth 5|Set-Content $backup -Encoding utf8
$q=@();foreach($p in $plan){$r=CallWp 'wp.getPost' $p.id;$live=($r.methodResponse.params.param.value.struct.member|? name -eq 'post_content').value.InnerText;if($live -cne $p.before){throw 'Concurrent'};$x='<param><value><struct><member><name>post_content</name><value><string>'+(X $p.after)+'</string></value></member></struct></value></param>';$r=CallWp 'wp.editPost' $p.id $x;if($r.methodResponse.params.param.value.boolean -ne '1'){throw 'Write failed'};$r=CallWp 'wp.getPost' $p.id;$saved=($r.methodResponse.params.param.value.struct.member|? name -eq 'post_content').value.InnerText;if($saved -cne $p.after){throw 'Readback mismatch'};$pub=Invoke-RestMethod ('https://gonorth.co.il/wp-json/geodir/v2/places/'+$p.id);$page=Invoke-WebRequest $pub.link;$qa=[pscustomobject]@{id=$p.id;url=$pub.link;http=[int]$page.StatusCode;before_chars=$p.before.Length;after_chars=$p.after.Length;exact=$true;h2=3;guide=$page.Content.Contains($p.guide)};$q+=$qa;$q|ConvertTo-Json -Depth 4|Set-Content memory/data/2026-09-23-batch-11-publication-qa.json -Encoding utf8;$qa|ConvertTo-Json -Compress}