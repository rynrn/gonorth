$ErrorActionPreference='Stop'
$helper=Get-Content memory/scripts/apply-batch-02-links.ps1 -Raw;$start=$helper.IndexOf('$m=[regex]');$end=$helper.IndexOf('$receipts=@()');Invoke-Expression $helper.Substring($start,$end-$start)
$items=@(
@{id=167;guide='https://gonorth.co.il/10-%d7%90%d7%98%d7%a8%d7%a7%d7%a6%d7%99%d7%95%d7%aa-%d7%91%d7%92%d7%9c%d7%99%d7%9c-%d7%9c%d7%97%d7%95%d7%a4%d7%a9%d7%aa-%d7%9e%d7%a9%d7%a4%d7%97%d7%94/';content=@'
<p>קידס לנד הוא מתחם משחקים מקורה במג׳ד אל־כרום, סמוך לכביש 85 ולכרמיאל. המקום מרכז כמה סוגי פעילות תחת קורת גג אחת ומתאים למשפחות שמחפשות בילוי לילדים שאינו תלוי בחום או בגשם.</p>
<h2>מה יש במתחם?</h2>
<p>במקורות המתארים את המקום מופיעים מתקני טיפוס ונינג׳ה, ג׳ימבורי, טרמפולינות, מכוניות ממונעות, קרוסלות, משחקי וידאו וחדר לימי הולדת. המתקנים מיועדים לגילים ולרמות שונות, ולכן ההורה צריך לוודא במקום לאיזה מתקן הילד רשאי להיכנס ולהישמע לצוות.</p>
<h2>אירועים ופעילויות</h2>
<p>אפשר לתאם ימי הולדת, פעילויות חברתיות, סדנאות וימי כיף. לקבוצה כדאי לברר מראש מה כוללת החבילה, כמה מלווים נדרשים, האם אוכל כלול ומהם כללי הבטיחות והגרביים במתחמי הקפיצה.</p>
<h2>לפני שמגיעים</h2>
<p>המידע הציבורי על המחיר והשעות אינו עקבי ומבוסס בחלקו על פרסומים ישנים, ולכן אין להסתמך על 11:00–23:00 או על מחיר הכניסה שהופיעו בדף הקודם. יש להתקשר ולוודא שהמתחם פעיל, את גילי הכניסה והשעות. לעוד רעיונות ראו את <a href="https://gonorth.co.il/10-%d7%90%d7%98%d7%a8%d7%a7%d7%a6%d7%99%d7%95%d7%aa-%d7%91%d7%92%d7%9c%d7%99%d7%9c-%d7%9c%d7%97%d7%95%d7%a4%d7%a9%d7%aa-%d7%9e%d7%a9%d7%a4%d7%97%d7%94/">מדריך האטרקציות למשפחות</a>.</p>
'@},
@{id=217;guide='https://gonorth.co.il/weekend-trip-north-israel/';content=@'
<p>קפה התן הוא עגלת קפה בכניסה הצפונית למושב בית הלל, בין מטעים אורגניים בגליל העליון. המקום פועל במתכונת מצומצמת ומשלב קפה ואוכל שמוכן במקום עם אווירת מטע ופעילויות מתחלפות.</p>
<h2>מה בתפריט?</h2>
<p>בימי הפעילות מוגשים קפה של קלייה מקומית, משקאות עונתיים כמו גזוז פירות ותה היביסקוס קר, בירה, כריכים מלחם מחמצת, סלטים ומאפים. התפריט משתנה לפי העונה והמלאי וכולל אפשרויות טבעוניות בחלק מהמנות.</p>
<h2>אירועי ערב</h2>
<p>בפרסומים קודמים הופיע “תן לילה” בימי שלישי עם קוקטיילים, אוכל רחוב, די־ג׳יי והופעות, וכן מכירות יד שנייה ואמנות מקומית בחלק מימי שישי. אלו אירועים משתנים ואין להניח שהם מתקיימים בכל שבוע; יש לבדוק ברשתות החברתיות.</p>
<h2>שעות והגעה</h2>
<p>מקור מקומי ו־Waze מציגים פעילות ביום שישי 08:00–14:00. מאחר שהמתכונת עשויה להשתנות, מומלץ להתקשר לפני נסיעה ולבדוק אירועי ערב, מזג אוויר וחניה. לניווט חפשו “התן – עגלת קפה, בית הלל”. ראו גם <a href="https://gonorth.co.il/weekend-trip-north-israel/">מדריך סוף השבוע בצפון</a>.</p>
'@},
@{id=157;guide='https://gonorth.co.il/10-%d7%90%d7%98%d7%a8%d7%a7%d7%a6%d7%99%d7%95%d7%aa-%d7%91%d7%92%d7%9c%d7%99%d7%9c-%d7%9c%d7%97%d7%95%d7%a4%d7%a9%d7%aa-%d7%9e%d7%a9%d7%a4%d7%97%d7%94/';content=@'
<p>כפר החיות בכפר גלעדי הוא פינת חי משפחתית באצבע הגליל, סמוך לקריית שמונה ולמטולה. המקום משלב מפגש עם בעלי חיים עם גישה חינוכית וטיפולית, ומתאים במיוחד למשפחות עם ילדים צעירים ולקבוצות קטנות.</p>
<h2>מה עושים בביקור?</h2>
<p>המבקרים פוגשים בעלי חיים מקרוב ויכולים להשתתף בהאכלה ובליטוף לפי הנחיית הצוות. הדגש הוא על יחס מכבד לבעלי החיים, היכרות עם הצרכים שלהם וחוויה רגועה. סוגי בעלי החיים והפעילויות עשויים להשתנות.</p>
<h2>קבוצות ואירועים</h2>
<p>אפשר לתאם ימי הולדת, ימי כיף וביקורי קבוצות. עמוד עסק מנוהל מציג התאמה לילדים, חניה חינם ושירותי נגישות, אך יש לתאר מראש כל צורך ולוודא שהמסלול והפעילות מתאימים למשתתפים.</p>
<h2>שעות ותיאום</h2>
<p>בימי חול הביקור בתיאום מראש בלבד. מקור תיירותי מציג שישי ושבת 10:00–13:00, אך עמוד העסק המנוהל אינו מפרסם שעות קבועות ולכן מומלץ לתאם גם בסוף השבוע. אין להגיע בלי לוודא שהמקום פתוח. לעוד פעילויות ראו את <a href="https://gonorth.co.il/10-%d7%90%d7%98%d7%a8%d7%a7%d7%a6%d7%99%d7%95%d7%aa-%d7%91%d7%92%d7%9c%d7%99%d7%9c-%d7%9c%d7%97%d7%95%d7%a4%d7%a9%d7%aa-%d7%9e%d7%a9%d7%a4%d7%97%d7%94/">מדריך האטרקציות למשפחות</a>.</p>
'@}
)
$closures=@(219,221)
$plan=@();foreach($i in $items){$r=CallWp 'wp.getPost' $i.id;$m=$r.methodResponse.params.param.value.struct.member;$live=($m|? name -eq 'post_content').value.InnerText;$status=($m|? name -eq 'post_status').value.InnerText;$plan+=[pscustomobject]@{id=$i.id;action='content';before=$live;before_status=$status;after=$i.content.Trim();guide=$i.guide}}
foreach($id in $closures){$r=CallWp 'wp.getPost' $id;$m=$r.methodResponse.params.param.value.struct.member;$plan+=[pscustomobject]@{id=$id;action='draft';before=($m|? name -eq 'post_content').value.InnerText;before_status=($m|? name -eq 'post_status').value.InnerText;after=$null;guide=$null}}
$backup='memory/data/2026-09-23-batch-16-publication-backup.json';if(Test-Path $backup){throw 'Backup exists'};$plan|ConvertTo-Json -Depth 5|Set-Content $backup -Encoding utf8
$q=@();foreach($p in $plan){$r=CallWp 'wp.getPost' $p.id;$m=$r.methodResponse.params.param.value.struct.member;$live=($m|? name -eq 'post_content').value.InnerText;$status=($m|? name -eq 'post_status').value.InnerText;if($live -cne $p.before -or $status -ne $p.before_status){throw "Concurrent $($p.id)"}
 if($p.action -eq 'content'){$x='<param><value><struct><member><name>post_content</name><value><string>'+(X $p.after)+'</string></value></member></struct></value></param>'}else{$x='<param><value><struct><member><name>post_status</name><value><string>draft</string></value></member></struct></value></param>'}
 $r=CallWp 'wp.editPost' $p.id $x;if($r.methodResponse.params.param.value.boolean -ne '1'){throw 'Write failed'}
 $r=CallWp 'wp.getPost' $p.id;$m=$r.methodResponse.params.param.value.struct.member;$saved=($m|? name -eq 'post_content').value.InnerText;$newstatus=($m|? name -eq 'post_status').value.InnerText
 if($p.action -eq 'content'){if($saved -cne $p.after){throw 'Mismatch'};$pub=Invoke-RestMethod ('https://gonorth.co.il/wp-json/geodir/v2/places/'+$p.id);$page=Invoke-WebRequest $pub.link;$qa=[pscustomobject]@{id=$p.id;action='published';url=$pub.link;http=[int]$page.StatusCode;before=$p.before.Length;after=$p.after.Length;exact=$true;guide=$page.Content.Contains($p.guide)}}else{if($newstatus -ne 'draft'){throw 'Draft failed'};$qa=[pscustomobject]@{id=$p.id;action='unpublished';before_status=$status;after_status=$newstatus;exact=$true}}
 $q+=$qa;$q|ConvertTo-Json -Depth 4|Set-Content memory/data/2026-09-23-batch-16-publication-qa.json -Encoding utf8;$qa|ConvertTo-Json -Compress
}