$ErrorActionPreference='Stop'
$helper=Get-Content memory/scripts/apply-batch-02-links.ps1 -Raw;$start=$helper.IndexOf('$m=[regex]');$end=$helper.IndexOf('$receipts=@()');Invoke-Expression $helper.Substring($start,$end-$start)
$items=@(
@{id=231;guide='https://gonorth.co.il/weekend-trip-north-israel/';content=@'
<p>עספורה היא עגלת קפה בקיבוץ הרדוף, שפועלת בתוך מבנה ששימש בעבר כרפת. המקום נוצר כמעין סלון קהילתי שמחבר בין תושבי הקיבוץ למטיילים, עם חלל רחב, ישיבה מוגנת ותפריט שמדגיש הכנה מקומית.</p>
<h2>מה בתפריט?</h2>
<p>התפריט צמחוני וכולל כריכים, סלטים, מוזלי, מאפים וקינוחים, עם מבחר טבעוני ואפשרויות לנמנעים מגלוטן. בימי שלישי מתקיים לעיתים ערב פיצה, ובסופי שבוע מוגשות מנות מיוחדות. ההיצע משתנה, ולכן יש לבדוק במקום התאמה לאלרגיות ולצרכים תזונתיים.</p>
<h2>מה יש למשפחות?</h2>
<p>העגלה נמצאת במרחב גדול שבו ילדים יכולים לנוע, ובקיבוץ יש גן שעשועים ופינת חי. במקום חניה, שירותים, Wi-Fi, מחסה מהגשם ומרחב מוגן לפי עמוד העגלה המאומת. יש להשגיח על ילדים ולכבד את פעילות הקיבוץ.</p>
<h2>שעות ותכנון ההגעה</h2>
<p>עמוד מאומת מציג פעילות בשלישי 07:30–15:00 וגם 17:00–21:00, רביעי וחמישי 07:30–15:00, שישי 07:30–14:30 ושבת 09:00–16:00; ראשון ושני סגור. מקור נוסף מציג הבדלים קלים, ולכן מומלץ לבדוק לפני הגעה. ראו גם <a href="https://gonorth.co.il/weekend-trip-north-israel/">מדריך סוף השבוע בצפון</a>.</p>
'@},
@{id=357;guide='https://gonorth.co.il/weekend-trip-north-israel/';content=@'
<p>F Ranch היא חוות רכיבה משפחתית במושב נטור שבדרום רמת הגולן. החווה מתמקדת בטיולי רכיבה קטנים ואישיים בנופי האזור, ומתאימה במיוחד לזוגות או למי שמחפש חוויית שטח שקטה הרחק מקבוצה גדולה.</p>
<h2>איך נראה הטיול?</h2>
<p>מסלולי הרכיבה יוצאים לשטחי דרום הגולן ומשלבים תצפיות לעבר הכנרת, החרמון ומצוקי נחל סמך בהתאם למסלול ולתנאים. משך הרכיבה, הקצב והנתיב נקבעים מראש לפי הניסיון של הרוכבים, מזג האוויר ומצב השטח.</p>
<h2>למי הפעילות מתאימה?</h2>
<p>אין צורך להניח שהרכיבה מתאימה לכל גיל או לכל מצב רפואי. בשיחת ההזמנה יש למסור גיל, גובה, משקל וניסיון קודם, ולברר את מגבלות החווה. מומלץ להגיע במכנסיים ארוכים, בנעליים סגורות ועם מים, ולפעול לפי הוראות המדריך.</p>
<h2>תיאום ובטיחות</h2>
<p>הפעילות מתקיימת בתיאום מראש בלבד. המידע הקיים מציג שעות א׳–ה׳ 09:00–19:00 ושישי עד 15:00, אך לא נמצא אתר רשמי פעיל לאימות ולכן יש לאשר כל פרט בטלפון, כולל מחיר, משך ונקודת מפגש. אפשר לשלב את הרכיבה בעזרת <a href="https://gonorth.co.il/weekend-trip-north-israel/">מדריך סוף השבוע בצפון</a>.</p>
'@}
)
$plan=@();foreach($i in $items){$r=CallWp 'wp.getPost' $i.id;$live=($r.methodResponse.params.param.value.struct.member|? name -eq 'post_content').value.InnerText;$plan+=[pscustomobject]@{id=$i.id;before=$live;after=$i.content.Trim();guide=$i.guide}}
$backup='memory/data/2026-09-23-batch-14-publication-backup.json';if(Test-Path $backup){throw 'Backup exists'};$plan|ConvertTo-Json -Depth 5|Set-Content $backup -Encoding utf8
$q=@();foreach($p in $plan){$r=CallWp 'wp.getPost' $p.id;$live=($r.methodResponse.params.param.value.struct.member|? name -eq 'post_content').value.InnerText;if($live -cne $p.before){throw 'Concurrent'};$x='<param><value><struct><member><name>post_content</name><value><string>'+(X $p.after)+'</string></value></member></struct></value></param>';$r=CallWp 'wp.editPost' $p.id $x;if($r.methodResponse.params.param.value.boolean -ne '1'){throw 'Write failed'};$r=CallWp 'wp.getPost' $p.id;$saved=($r.methodResponse.params.param.value.struct.member|? name -eq 'post_content').value.InnerText;if($saved -cne $p.after){throw 'Mismatch'};$pub=Invoke-RestMethod ('https://gonorth.co.il/wp-json/geodir/v2/places/'+$p.id);$page=Invoke-WebRequest $pub.link;$qa=[pscustomobject]@{id=$p.id;url=$pub.link;http=[int]$page.StatusCode;before=$p.before.Length;after=$p.after.Length;exact=$true;guide=$page.Content.Contains($p.guide)};$q+=$qa;$q|ConvertTo-Json|Set-Content memory/data/2026-09-23-batch-14-publication-qa.json -Encoding utf8;$qa|ConvertTo-Json -Compress}