$ErrorActionPreference='Stop'
$helper=Get-Content memory/scripts/apply-batch-02-links.ps1 -Raw
$start=$helper.IndexOf('$m=[regex]');$end=$helper.IndexOf('$receipts=@()');Invoke-Expression $helper.Substring($start,$end-$start)
$items=@(
 [pscustomobject]@{id=195;guide='https://gonorth.co.il/yekavim-batzafon/';content=@'
<p>מפגש היקב נמצא בכניסה לקיבוץ עמיעד, סמוך לכביש 90, ומשמש חנות ומרכז טעימות לליקרים, יינות ומוצרים מקומיים מהגליל ומהגולן. זו עצירה נוחה בדרך בין הכנרת, ראש פינה והגליל העליון, במיוחד למי שמחפש להכיר יצרנים צפוניים או לבחור מתנה מהאזור.</p>
<h2>מה מוצאים במקום?</h2>
<p>ההיצע כולל ליקרים פירותיים, חלביים ובטעמי שוקולד, לצד יינות בוטיק מהגליל העליון, מהגליל המערבי ומרמת הגולן. בחנות נמכרים גם גבינות, דבש וסילאן, ממרחים, ריבות, שמנים ומארזי שי. המבחר משתנה, ולכן כדאי לבדוק מראש אם מחפשים מוצר או יקב מסוים.</p>
<h2>טעימות וביקורי קבוצות</h2>
<p>מבקרים יחידים יכולים להגיע בשעות הפעילות ולהתרשם מהמבחר ולטעום בהתאם לפעילות במקום. קבוצות נדרשות לתאם מראש בטלפון. מאחר שמדובר בטעימות אלכוהול, הן מיועדות לבגירים בלבד ומומלץ לתכנן נהג תורן.</p>
<h2>שעות ותכנון העצירה</h2>
<p>לפי האתר הרשמי, המקום פתוח בימים א׳–ה׳ 10:00–18:00 וביום ו׳ 09:30–17:00. שעות שבת וחג אינן מפורטות בדף העדכני, ולכן יש לוודא אותן טלפונית לפני הנסיעה. אפשר לשלב את הביקור עם מקומות נוספים מתוך <a href="https://gonorth.co.il/yekavim-batzafon/">מדריך היקבים בצפון</a>.</p>
'@},
 [pscustomobject]@{id=335;guide='https://gonorth.co.il/%d7%9e%d7%a1%d7%9c%d7%95%d7%9c-%d7%98%d7%99%d7%95%d7%9c-%d7%91%d7%a8%d7%9e%d7%aa-%d7%94%d7%92%d7%95%d7%9c%d7%9f-%d7%9e%d7%94%d7%91%d7%a0%d7%99%d7%90%d7%a1-%d7%95%d7%a2%d7%93-%d7%aa%d7%9c-%d7%a4/';content=@'
<p>גיל גולן הוא מרכז רכיבה ואילוף סוסים במושב אלוני הבשן שבצפון רמת הגולן. המרכז פועל משנת 1995 בניהולו של אמיר דובדבני, ומציע פעילות המבוססת על קשר עם הסוס, הדרכה אישית ועבודה בשטח הפתוח.</p>
<h2>מה מציע המרכז?</h2>
<p>הפעילות משלבת רכיבה, אילוף בשיטת Natural Horsemanship ותוכניות רכיבה טיפולית. באתר המרכז מתוארות גם פעילויות שטח להעצמה ולמנהיגות בשיטות מקוריות, וכן ניסיון בהקמת מרכזי רכיבה טיפוליים לקהלים בעלי צרכים שונים. אופי הפעילות המדויק נקבע לפי המשתתפים והמטרה.</p>
<h2>מי מוביל את הפעילות?</h2>
<p>אמיר דובדבני הוא בוגר קורסי רכיבה במכון וינגייט, בארצות הברית ובאורוגוואי, מדריך רכיבה טיפולית ומאלף סוסים. הרקע המקצועי מאפשר להתאים את המפגש לרוכבים מתחילים, למשפחות, לקבוצות ולתהליכים טיפוליים, לאחר שיחת תיאום.</p>
<h2>לפני שמגיעים</h2>
<p>הפעילות מתקיימת בתיאום מראש בלבד. בשיחה כדאי לברר את גיל המינימום, משך הפעילות, המחיר, לבוש מתאים והתאמה למזג האוויר. אל תגיעו לחווה ללא תיאום, מכיוון שהמדריך עשוי להיות בפעילות שטח. אפשר לשלב את הביקור עם תחנות מתוך <a href="https://gonorth.co.il/%d7%9e%d7%a1%d7%9c%d7%95%d7%9c-%d7%98%d7%99%d7%95%d7%9c-%d7%91%d7%a8%d7%9e%d7%aa-%d7%94%d7%92%d7%95%d7%9c%d7%9f-%d7%9e%d7%94%d7%91%d7%a0%d7%99%d7%90%d7%a1-%d7%95%d7%a2%d7%93-%d7%aa%d7%9c-%d7%a4/">מסלול הטיול ברמת הגולן</a>.</p>
'@}
)
$plan=@();foreach($i in $items){$r=CallWp 'wp.getPost' $i.id;$live=($r.methodResponse.params.param.value.struct.member|? name -eq 'post_content').value.InnerText;$plan+=[pscustomobject]@{id=$i.id;before=$live;after=$i.content.Trim();guide=$i.guide}}
$backup='memory/data/2026-09-23-batch-08-publication-backup.json';if(Test-Path $backup){throw 'Backup exists'};$plan|ConvertTo-Json -Depth 5|Set-Content $backup -Encoding utf8
$receipts=@();foreach($p in $plan){$r=CallWp 'wp.getPost' $p.id;$live=($r.methodResponse.params.param.value.struct.member|? name -eq 'post_content').value.InnerText;if($live -cne $p.before){throw "Concurrent change $($p.id)"};$extra='<param><value><struct><member><name>post_content</name><value><string>'+(X $p.after)+'</string></value></member></struct></value></param>';$r=CallWp 'wp.editPost' $p.id $extra;if($r.methodResponse.params.param.value.boolean -ne '1'){throw "Write failed"};$r=CallWp 'wp.getPost' $p.id;$saved=($r.methodResponse.params.param.value.struct.member|? name -eq 'post_content').value.InnerText;if($saved -cne $p.after){throw "Readback mismatch"};$pub=Invoke-RestMethod ('https://gonorth.co.il/wp-json/geodir/v2/places/'+$p.id);$page=Invoke-WebRequest $pub.link;$qa=[pscustomobject]@{id=$p.id;url=$pub.link;status=$pub.status;http=[int]$page.StatusCode;before_chars=$p.before.Length;after_chars=$p.after.Length;exact_readback=$true;h2=([regex]::Matches($p.after,'<h2>')).Count;guide_link=$page.Content.Contains($p.guide);at=(Get-Date).ToUniversalTime().ToString('o')};$receipts+=$qa;$receipts|ConvertTo-Json -Depth 5|Set-Content memory/data/2026-09-23-batch-08-publication-qa.json -Encoding utf8;$qa|ConvertTo-Json -Compress}