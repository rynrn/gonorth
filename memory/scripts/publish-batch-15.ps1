$ErrorActionPreference='Stop'
$helper=Get-Content memory/scripts/apply-batch-02-links.ps1 -Raw;$start=$helper.IndexOf('$m=[regex]');$end=$helper.IndexOf('$receipts=@()');Invoke-Expression $helper.Substring($start,$end-$start)
$items=@(
@{id=185;guide='https://gonorth.co.il/maslulei-mayim-batzafon/';content=@'
<p>גן מורד הירדן הוא שם למרחב הטבע המתוכנן לאורך הירדן הדרומי, מסכר דגניה לכיוון מנחמיה ונהריים. האזור מחבר בין גדות הנהר, שטחים פתוחים, חקלאות ואתרי מורשת, אך חלקים ממנו נמצאים בשלבי פיתוח והסדרה שונים.</p>
<h2>מה אפשר לעשות באזור?</h2>
<p>לאורך המרחב קיימים מקטעים שמתאימים להליכה, לרכיבה ולעצירות נוף, אך אין להתייחס לכל התוואי כאל פארק מוסדר ורציף. נקודות הגישה, מצב השבילים והחניה משתנים בין מקטעים, ולכן כדאי לבחור מראש יעד נקודתי ולהסתמך על שילוט בשטח.</p>
<h2>מים ובטיחות</h2>
<p>הירדן הדרומי מושפע מסכרים, הזרמות ותשתיות מים. אין להיכנס למים רק משום שהם נראים רדודים, ואין להבטיח רחצה או נגישות ללא שילוט מקומי שמאשר זאת. יש להתרחק ממתקנים הידרולוגיים, לא לעבור גדרות ולהשגיח על ילדים ליד הגדה.</p>
<h2>תכנון הביקור</h2>
<p>זהו שטח פתוח ולא אתר עם שעות קבלה קבועות. מומלץ להגיע באור יום, לבדוק מזג אוויר ומצב דרכים ולהצטייד במים ובהגנה מהשמש. מאחר שהמרחב ממשיך להתפתח, יש לאמת את נקודת הכניסה והמסלול סמוך לביקור. למסלולי מים מוסדרים יותר ראו את <a href="https://gonorth.co.il/maslulei-mayim-batzafon/">מדריך מסלולי המים בצפון</a>.</p>
'@},
@{id=223;guide='https://gonorth.co.il/weekend-trip-north-israel/';content=@'
<p>מיקה היא בית קפה, קונדיטוריה ומאפייה בקיבוץ אילון שבגליל המערבי. המקום פועל במבנה אבן ששימש בעבר כמאפייה של הקיבוץ, עם חצר כפרית ותנור האפייה המקורי ששומר כחלק מהחלל.</p>
<h2>מה אוכלים?</h2>
<p>התפריט הכשר כולל ארוחות בוקר, שקשוקה, סלטים, כריכים, קישים, פריטטה ובורקסים, לצד אפשרויות טבעוניות. בוויטרינה מוצגים מאפים וקינוחים בעבודת יד, ובהם בריוש, טארטים, עוגות ועוגיות. המבחר משתנה לפי האפייה והעונה.</p>
<h2>למי זה מתאים?</h2>
<p>זהו מקום מתאים לארוחת בוקר, לקפה אחרי טיול בנחל בצת או לעצירה בדרך לאזור ראש הנקרה. אפשר להזמין גם קייטרינג חלבי, עוגות ופעילויות לפי הפרסומים הקיימים, אך שירותים אלה דורשים תיאום ישיר.</p>
<h2>שעות ותכנון</h2>
<p>הדף הישן הציג שעות קבועות, אך לא נמצא עמוד עסק רשמי עדכני שמאשר אותן. לכן מומלץ להתקשר לפני הנסיעה ולברר שעות, כשרות, הזמנת מקום ונגישות. לעוד רעיונות בגליל המערבי ראו את <a href="https://gonorth.co.il/weekend-trip-north-israel/">מדריך סוף השבוע בצפון</a>.</p>
'@},
@{id=237;guide='https://gonorth.co.il/yekavim-batzafon/';content=@'
<p>יקב שאטו גולן נמצא במושב אליעד בדרום רמת הגולן. זהו יקב בוטיק שהחל לפעול ב־1999 ומייצר יינות מכרמים סמוכים הנטועים בקרקע בזלתית בגובה של כ־400 מטר.</p>
<h2>הכרמים והיינות</h2>
<p>לפי עמוד עסק מאומת, ליקב כ־170 דונם כרמים ועשרה זני ענבים שמקורם בעיקר בצרפת ובחלקם בספרד ובפורטוגל. היקב מייצר כ־100 אלף בקבוקים בשנה, ומציע טעימה ורכישה של יינות שאינם כשרים.</p>
<h2>ביקורים וטעימות</h2>
<p>היקב פתוח למבקרים בתיאום מראש. לפני ההגעה יש להזמין מקום ולברר מה כולל הביקור, משך הטעימה, המחיר, אפשרויות מזון ונגישות. טעימות מיועדות לבגירים בלבד ומומלץ לתכנן נהג תורן.</p>
<h2>שעות ותכנון ההגעה</h2>
<p>עמוד עסק מאומת מציג פעילות בכל ימות השבוע 10:30–15:30, אך יש לאשר את השעה ישירות מול היקב, במיוחד בשבתות ובחגים. לניווט חפשו שאטו גולן במושב אליעד. לעוד תחנות טעימה ראו את <a href="https://gonorth.co.il/yekavim-batzafon/">מדריך היקבים בצפון</a>.</p>
'@}
)
$plan=@();foreach($i in $items){$r=CallWp 'wp.getPost' $i.id;$live=($r.methodResponse.params.param.value.struct.member|? name -eq 'post_content').value.InnerText;$plan+=[pscustomobject]@{id=$i.id;before=$live;after=$i.content.Trim();guide=$i.guide}}
$backup='memory/data/2026-09-23-batch-15-publication-backup.json';if(Test-Path $backup){throw 'Backup exists'};$plan|ConvertTo-Json -Depth 5|Set-Content $backup -Encoding utf8
$q=@();foreach($p in $plan){$r=CallWp 'wp.getPost' $p.id;$live=($r.methodResponse.params.param.value.struct.member|? name -eq 'post_content').value.InnerText;if($live -cne $p.before){throw 'Concurrent'};$x='<param><value><struct><member><name>post_content</name><value><string>'+(X $p.after)+'</string></value></member></struct></value></param>';$r=CallWp 'wp.editPost' $p.id $x;if($r.methodResponse.params.param.value.boolean -ne '1'){throw 'Write failed'};$r=CallWp 'wp.getPost' $p.id;$saved=($r.methodResponse.params.param.value.struct.member|? name -eq 'post_content').value.InnerText;if($saved -cne $p.after){throw 'Mismatch'};$pub=Invoke-RestMethod ('https://gonorth.co.il/wp-json/geodir/v2/places/'+$p.id);$page=Invoke-WebRequest $pub.link;$qa=[pscustomobject]@{id=$p.id;url=$pub.link;http=[int]$page.StatusCode;before=$p.before.Length;after=$p.after.Length;exact=$true;guide=$page.Content.Contains($p.guide)};$q+=$qa;$q|ConvertTo-Json|Set-Content memory/data/2026-09-23-batch-15-publication-qa.json -Encoding utf8;$qa|ConvertTo-Json -Compress}