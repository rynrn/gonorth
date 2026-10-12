$ErrorActionPreference='Stop'
$helper=Get-Content memory/scripts/apply-batch-02-links.ps1 -Raw;$start=$helper.IndexOf('$m=[regex]');$end=$helper.IndexOf('$receipts=@()');Invoke-Expression $helper.Substring($start,$end-$start)
$items=@(
@{id=240;guide='https://gonorth.co.il/yekavim-batzafon/';content=@'
<p>יקב הרי גליל שוכן סמוך לקיבוץ יראון, במבנה שממנו נשקפים כרמי הגליל וההרים. מרכז המבקרים מתאים ליחידים, למשפחות בוגרות, לקבוצות ולארגונים שרוצים להכיר את תהליך יצירת היין ואת החיבור של היקב לסביבה המקומית.</p>
<h2>מה כולל הביקור?</h2>
<p>הסיור המודרך עובר בשלבי הייצור, מגידול הענבים בכרם ועד למזיגה מהבקבוק או מהחבית, ומשלב הסבר על פעילות היקב בתחום הקיימות. אפשר לבחור טעימת יינות מקצועית הכוללת שלושה יינות נבחרים, ולתאם חבילות ופעילויות נוספות בהתאם לקבוצה.</p>
<h2>למי זה מתאים?</h2>
<p>הביקור מתאים לחובבי יין ולמי שמחפש עצירה מסודרת בגליל העליון. טעימות אלכוהול מיועדות לבגירים, ומומלץ לתכנן נהג תורן. סיורים דורשים תיאום מראש; לקבוצה כדאי לברר מראש את משך הפעילות, השפה, הנגישות והכיבוד.</p>
<h2>שעות ותכנון ההגעה</h2>
<p>מרכז המבקרים פתוח בימים א׳–ה׳ 09:00–17:00. ביום שישי הוא פתוח עד 14:00 בחורף ועד 16:00 בקיץ, וסגור בשבתות ובחגים. לפני נסיעה ארוכה כדאי לוודא שהמרכז והסיור הרצוי זמינים. לעוד אפשרויות ראו את <a href="https://gonorth.co.il/yekavim-batzafon/">מדריך היקבים בצפון</a>.</p>
'@},
@{id=198;guide='https://gonorth.co.il/yekavim-batzafon/';content=@'
<p>מרכז המבקרים של יקב דלתון נמצא בפארק התעשייה דלתון שבמרום הגליל ומציע טעימות ואירוח סביב יינות גליליים. הביקור מתאים לזוגות, לחברים ולקבוצות שרוצים לעצור ליד הכרמים והיקבים של הגליל העליון ולשלב יין עם אוכל מקומי.</p>
<h2>מה אפשר להזמין?</h2>
<p>במרכז מוצעות טעימות של ארבעה יינות ממבחר סדרות היקב, לצד מסלול טעימות פרימיום. אפשר להוסיף פלטת גבינות גלילית עם לחמים ומטבלים, פיצה או לחם גלילי ושמן זית. המחירים והתפריט עשויים להשתנות, ולכן יש לבדוק אותם בזמן ההזמנה.</p>
<h2>תיאום והתאמה</h2>
<p>מומלץ להזמין ביקור מראש, במיוחד בסופי שבוע, בחגים או לקבוצה. הטעימות מיועדות לבגירים בלבד ויש לתכנן נהג תורן. בשיחת התיאום אפשר לברר את משך האירוח, אפשרויות מזון, נגישות והתאמות לקבוצה.</p>
<h2>שעות והגעה</h2>
<p>לפי האתר הרשמי, מרכז המבקרים פתוח בימים א׳–ה׳ 09:00–16:30 וביום ו׳ 09:00–14:00. לניווט יש לחפש ב־Waze “יקב דלתון”. לעוד יקבים ותחנות טעימה באזור ראו את <a href="https://gonorth.co.il/yekavim-batzafon/">מדריך היקבים בצפון</a>.</p>
'@},
@{id=229;guide='https://gonorth.co.il/weekend-trip-north-israel/';content=@'
<p>שישו קפה פועל במשק אבנעים במושב הזורעים, מול השדות של הגליל התחתון. בית הקפה נולד מתוך משק משפחתי שמגדל שקדים זה שלושה דורות, והשקדים ותוצרת האזור משתלבים בתפריט ובחוויה הכפרית.</p>
<h2>מה אוכלים ואיך יושבים?</h2>
<p>התפריט חלבי וכולל גם מנות צמחוניות וטבעוניות. אפשר לבחור ישיבה בחצר מוצלת או במתחם מקורה וממוזג. במקום יש שירותים, Wi-Fi וחניה בשפע, כך שהוא מתאים לעצירה רגועה, לפגישה או לארוחה בדרך לכנרת ולגליל התחתון.</p>
<h2>צריך להזמין מקום?</h2>
<p>בשעות היום עובדים על בסיס מקום פנוי ואין צורך להזמין מראש. לערבי רביעי וחמישי, כאשר מתקיימת פעילות ערב, אפשר להזמין שולחן דרך מערכת ההזמנות של העסק. לאירועים יש ליצור קשר ישירות עם המקום.</p>
<h2>שעות פתיחה</h2>
<p>לפי האתר הרשמי, הקפה פתוח בימים א׳–ה׳ 09:00–14:00, וביום חמישי גם 19:00–22:30. ביום שישי הפעילות היא 08:30–13:00. כדאי לבדוק עדכונים בחגים ובאירועים מיוחדים. אפשר לשלב אותו בתכנון מתוך <a href="https://gonorth.co.il/weekend-trip-north-israel/">מדריך סוף השבוע בצפון</a>.</p>
'@},
@{id=159;guide='https://gonorth.co.il/10-%d7%90%d7%98%d7%a8%d7%a7%d7%a6%d7%99%d7%95%d7%aa-%d7%9e%d7%95%d7%9e%d7%9c%d7%a6%d7%95%d7%aa-%d7%91%d7%92%d7%9c%d7%99%d7%9c-%d7%9c%d7%97%d7%95%d7%a4%d7%a9%d7%aa-%d7%9e%d7%a9%d7%a4%d7%97%d7%94/';content=@'
<p>לול ארט הוא מתחם סדנאות DIY במושב רמות, מול נוף הכנרת. לול ישן הוסב לחלל יצירה שבו ילדים ומבוגרים מכינים מוצרים שימושיים מחומרים שונים, לצד קפה מוייז וחנות של ערכות ועבודות יד.</p>
<h2>איך פועלת הסדנה?</h2>
<p>כל משתתף בוחר ערכה אישית מתוך הקטלוג, וכולם יכולים ליצור יחד סביב אותו שולחן גם אם בחרו פריטים שונים. הסדנאות נמשכות בדרך כלל שעה וחצי עד שעתיים ומתאימות לפי האתר מגיל 3. מומלץ להגיע בבגדים נוחים שאפשר ללכלך.</p>
<h2>הזמנה וכללים</h2>
<p>את המקום מזמינים באתר: בוחרים תאריך, שעה וערכות ומשלמים מקדמה לשמירת השולחן. מלווה שאינו מזמין ערכה משלם דמי כניסה מגיל 3. אין להכניס מזון או בעלי חיים למתחם; קבוצות יכולות לתאם אירוח גם מעבר לשעות הרגילות.</p>
<h2>שעות ותכנון הביקור</h2>
<p>המתחם פתוח בימים א׳–ה׳ 09:00–14:00 וביום ו׳ 09:00–15:00, וסגור בשבת. שעות עונתיות עשויות להשתנות ולכן יש להסתמך על מערכת ההזמנות. לעוד רעיונות למשפחות ראו את <a href="https://gonorth.co.il/10-%d7%90%d7%98%d7%a8%d7%a7%d7%a6%d7%99%d7%95%d7%aa-%d7%9e%d7%95%d7%9e%d7%9c%d7%a6%d7%95%d7%aa-%d7%91%d7%92%d7%9c%d7%99%d7%9c-%d7%9c%d7%97%d7%95%d7%a4%d7%a9%d7%aa-%d7%9e%d7%a9%d7%a4%d7%97%d7%94/">מדריך האטרקציות למשפחות</a>.</p>
'@}
)
$plan=@();foreach($i in $items){$r=CallWp 'wp.getPost' $i.id;$live=($r.methodResponse.params.param.value.struct.member|? name -eq 'post_content').value.InnerText;$plan+=[pscustomobject]@{id=$i.id;before=$live;after=$i.content.Trim();guide=$i.guide}}
$backup='memory/data/2026-09-23-batch-09-publication-backup.json';if(Test-Path $backup){throw 'Backup exists'};$plan|ConvertTo-Json -Depth 5|Set-Content $backup -Encoding utf8
$q=@();foreach($p in $plan){$r=CallWp 'wp.getPost' $p.id;$live=($r.methodResponse.params.param.value.struct.member|? name -eq 'post_content').value.InnerText;if($live -cne $p.before){throw "Concurrent $($p.id)"};$x='<param><value><struct><member><name>post_content</name><value><string>'+(X $p.after)+'</string></value></member></struct></value></param>';$r=CallWp 'wp.editPost' $p.id $x;if($r.methodResponse.params.param.value.boolean -ne '1'){throw 'Write failed'};$r=CallWp 'wp.getPost' $p.id;$saved=($r.methodResponse.params.param.value.struct.member|? name -eq 'post_content').value.InnerText;if($saved -cne $p.after){throw 'Readback mismatch'};$pub=Invoke-RestMethod ('https://gonorth.co.il/wp-json/geodir/v2/places/'+$p.id);$page=Invoke-WebRequest $pub.link;$qa=[pscustomobject]@{id=$p.id;url=$pub.link;http=[int]$page.StatusCode;before_chars=$p.before.Length;after_chars=$p.after.Length;exact_readback=$true;h2=([regex]::Matches($p.after,'<h2>')).Count;guide_link=$page.Content.Contains($p.guide)};$q+=$qa;$q|ConvertTo-Json -Depth 5|Set-Content memory/data/2026-09-23-batch-09-publication-qa.json -Encoding utf8;$qa|ConvertTo-Json -Compress}