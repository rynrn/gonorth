$ErrorActionPreference='Stop'
$helper=Get-Content memory/scripts/apply-batch-02-links.ps1 -Raw;$start=$helper.IndexOf('$m=[regex]');$end=$helper.IndexOf('$receipts=@()');Invoke-Expression $helper.Substring($start,$end-$start)
$items=@(
@{id=391;guide='https://gonorth.co.il/10-%d7%90%d7%98%d7%a8%d7%a7%d7%a6%d7%99%d7%95%d7%aa-%d7%9e%d7%95%d7%9e%d7%9c%d7%a6%d7%95%d7%aa-%d7%91%d7%92%d7%9c%d7%99%d7%9c-%d7%9c%d7%97%d7%95%d7%a4%d7%a9%d7%aa-%d7%9e%d7%a9%d7%a4%d7%97%d7%94/';content=@'
<p>War Zone Paintball הוא מתחם פיינטבול בעין קנייא שבצפון הגולן, סמוך לאזור החרמון. המגרש משתרע על כ־4,000 מ״ר וכולל מתקנים, מכשולים ומקומות מסתור למשחק קבוצתי שמשלב תנועה, אסטרטגיה וירי בכדורי צבע.</p>
<h2>איך מתנהל המשחק?</h2>
<p>לפני הכניסה מקבלים תדריך, ציוד מגן וציוד משחק, ולאחר תרגול קצר מתחלקים לקבוצות ויוצאים לתרחיש במגרש. מקורות התיירות מציגים גם פעילות מותאמת לילדים צעירים, אך סוג הציוד והמשחק משתנה לפי גיל, ולכן חובה לברר מראש איזו פעילות מתאימה לכל משתתף.</p>
<h2>למי זה מתאים?</h2>
<p>המתחם מתאים למשפחות, חברים, ימי הולדת וימי גיבוש. כדאי להגיע בבגדים נוחים ובנעליים סגורות שאפשר ללכלך, ולהביא מים. מי שיש לו מגבלה רפואית או צורך בנגישות צריך להעלות זאת בשיחת התיאום ולקבל אישור להתאמה.</p>
<h2>תיאום ושעות</h2>
<p>הפעילות מתקיימת בתיאום מראש. מקורות עדכניים מציגים פעילות לאורך כל השבוע ובשעות ערב, אך יש ביניהם הבדלים קלים ולכן יש לאשר בטלפון את השעה, המחיר, משך המשחק וכמות הכדורים. לעוד רעיונות ראו את <a href="https://gonorth.co.il/10-%d7%90%d7%98%d7%a8%d7%a7%d7%a6%d7%99%d7%95%d7%aa-%d7%9e%d7%95%d7%9e%d7%9c%d7%a6%d7%95%d7%aa-%d7%91%d7%92%d7%9c%d7%99%d7%9c-%d7%9c%d7%97%d7%95%d7%a4%d7%a9%d7%aa-%d7%9e%d7%a9%d7%a4%d7%97%d7%94/">מדריך האטרקציות למשפחות</a>.</p>
'@},
@{id=411;guide='https://gonorth.co.il/weekend-trip-north-israel/';content=@'
<p>מרכבות הבשן היא חוות סוסים משפחתית במושב אבני איתן בדרום רמת הגולן. הפעילות מתמקדת במפגש עם הסוסים ובעלי החיים, סיורים בחווה ונסיעה בכרכרה, ומתאימה למשפחות, לזוגות ולקבוצות שמחפשות חוויה כפרית בקצב רגוע.</p>
<h2>אילו פעילויות יש?</h2>
<p>אפשר לתאם סיור בחווה שכולל היכרות עם סוסי פוני ולימוד בסיסי על שפת הסוס, או סיור זוגי בכרכרה ברחבי המושב. לקבוצות מוצעות הפעלות כמו רעיית צאן, האכלת כבשים, מפגש עם סוסים ומשימות גיבוש. סוג הפעילות ומשך הביקור נקבעים מראש.</p>
<h2>אירועים וקבוצות</h2>
<p>בתיאום ניתן לקיים ימי הולדת, ימי גיבוש וחגיגות אישיות. לפני ההזמנה כדאי למסור את גיל המשתתפים, גודל הקבוצה וכל צורך בנגישות, ולברר אילו בעלי חיים ופעילויות זמינים באותו יום. מומלץ להגיע בנעליים סגורות ובבגדים שמתאימים לחווה.</p>
<h2>שעות ותיאום</h2>
<p>מקור תיירותי מציג פעילות א׳–ה׳ 09:00–18:00, שישי 09:00–13:00 ושבת סגור, אך גם מציין שהאטרקציה אינה מפרסמת בו כעת. לכן יש להתייחס לשעות כמידע לבירור ולהגיע רק לאחר תיאום טלפוני. אפשר לשלב את הביקור בעזרת <a href="https://gonorth.co.il/weekend-trip-north-israel/">מדריך סוף השבוע בצפון</a>.</p>
'@}
)
$plan=@();foreach($i in $items){$r=CallWp 'wp.getPost' $i.id;$live=($r.methodResponse.params.param.value.struct.member|? name -eq 'post_content').value.InnerText;$plan+=[pscustomobject]@{id=$i.id;before=$live;after=$i.content.Trim();guide=$i.guide}}
$backup='memory/data/2026-09-23-batch-12-publication-backup.json';if(Test-Path $backup){throw 'Backup exists'};$plan|ConvertTo-Json -Depth 5|Set-Content $backup -Encoding utf8
$q=@();foreach($p in $plan){$r=CallWp 'wp.getPost' $p.id;$live=($r.methodResponse.params.param.value.struct.member|? name -eq 'post_content').value.InnerText;if($live -cne $p.before){throw 'Concurrent'};$x='<param><value><struct><member><name>post_content</name><value><string>'+(X $p.after)+'</string></value></member></struct></value></param>';$r=CallWp 'wp.editPost' $p.id $x;if($r.methodResponse.params.param.value.boolean -ne '1'){throw 'Write failed'};$r=CallWp 'wp.getPost' $p.id;$saved=($r.methodResponse.params.param.value.struct.member|? name -eq 'post_content').value.InnerText;if($saved -cne $p.after){throw 'Mismatch'};$pub=Invoke-RestMethod ('https://gonorth.co.il/wp-json/geodir/v2/places/'+$p.id);$page=Invoke-WebRequest $pub.link;$qa=[pscustomobject]@{id=$p.id;url=$pub.link;http=[int]$page.StatusCode;before_chars=$p.before.Length;after_chars=$p.after.Length;exact=$true;guide=$page.Content.Contains($p.guide)};$q+=$qa;$q|ConvertTo-Json|Set-Content memory/data/2026-09-23-batch-12-publication-qa.json -Encoding utf8;$qa|ConvertTo-Json -Compress}