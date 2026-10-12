$ErrorActionPreference='Stop'
$stamp='2026-09-23'; $backupPath="memory/data/$stamp-closed-listings-before-restore.json"; $qaPath="memory/data/$stamp-closed-listings-restore-qa.json"
$m=[regex]::Match((Get-Content AGENTS.md -Raw),'Username:\s*(\S+)\r?\nPassword:\s*([^\r\n]+)'); if(-not $m.Success){throw 'Credential missing'}
function X([string]$s){[System.Security.SecurityElement]::Escape($s)}
$auth='<param><value><int>1</int></value></param><param><value><string>'+(X $m.Groups[1].Value)+'</string></value></param><param><value><string>'+(X $m.Groups[2].Value.Trim())+'</string></value></param>'
function CallWp($method,$id,$extra=''){ $body='<?xml version="1.0" encoding="UTF-8"?><methodCall><methodName>'+$method+'</methodName><params>'+$auth+'<param><value><int>'+$id+'</int></value></param>'+$extra+'</params></methodCall>'; try{[xml]$r=(Invoke-WebRequest 'https://gonorth.co.il/xmlrpc.php' -Method Post -ContentType 'text/xml; charset=utf-8' -Body ([Text.Encoding]::UTF8.GetBytes($body)) -TimeoutSec 30).Content}catch{throw "Request uncertain for $id; inspect before retry"}; if($r.methodResponse.fault){throw "WordPress fault for $id"}; return $r }
function GetWp([int]$id){ $r=CallWp 'wp.getPost' $id; $o=[ordered]@{}; foreach($v in $r.methodResponse.params.param.value.struct.member){if($v.name -in 'post_id','post_title','post_content','post_excerpt','post_status','link','post_modified_gmt'){$o[$v.name]=$v.value.InnerText}}; [pscustomobject]$o }
$updates=@(
 [pscustomobject]@{id=219;excerpt='עגלת קפה מיכאל בשדה נחמיה מסומנת כסגורה. העמוד נשמר לצורכי מידע והפניה לאפשרויות בילוי וקפה חלופיות באזור.';content=@'
<div class="gn-place-status gn-place-status--closed" role="status"><strong>המקום סגור</strong><br>לפי המידע הציבורי שנבדק, עגלת קפה מיכאל אינה פעילה. אין להגיע למקום על סמך שעות הפתיחה או פרטי הקשר הישנים. המידע נבדק לאחרונה ב־23 בספטמבר 2026.</div>
<p>עגלת קפה מיכאל פעלה בקיבוץ שדה נחמיה בגליל העליון, באזור גשר יוסף ונהר הירדן. העגלה הציעה בעבר קפה, מאפים ונשנושים בסגנון טבעוני וצמחוני, ושימשה נקודת עצירה למטיילים באזור עמק החולה והגליל העליון.</p>
<h2>מה חשוב לדעת?</h2>
<p>העסק מסומן כסגור ולכן שעות הפתיחה, הטלפון והניווט שהופיעו בעבר אינם תקפים והוסרו מהעמוד. העמוד נשאר באתר כדי שמי שמחפש את העסק יקבל תשובה ברורה ולא יגיע לשווא.</p>
<h2>אפשרויות נוספות באזור</h2>
<p>לפני היציאה אפשר לבחור מקום פעיל אחר דרך <a href="https://gonorth.co.il/places/">מאגר המקומות בצפון</a>, או להיעזר ב<a href="https://gonorth.co.il/weekend-trip-north-israel/">מדריך סוף השבוע בצפון</a>. מומלץ לבדוק שעות פעילות ישירות מול כל עסק לפני הנסיעה.</p>
'@},
 [pscustomobject]@{id=221;excerpt='אסתר — קפה ובישולים לדרך בשתולה אינה זמינה כרגע. העמוד נשמר לצורכי מידע והפניה למקומות חלופיים בגליל המערבי.';content=@'
<div class="gn-place-status gn-place-status--unavailable" role="status"><strong>המקום אינו פעיל כרגע</strong><br>המקורות שנבדקו מציגים את אסתר — קפה ובישולים לדרך כסגורה זמנית. אין להגיע על סמך שעות הפתיחה או פרטי הקשר הישנים. המידע נבדק לאחרונה ב־23 בספטמבר 2026.</div>
<p>אסתר — קפה ובישולים לדרך פעלה במושב שתולה שבגליל המערבי. המקום הציע בעבר קפה, מאפים ותבשילים ביתיים באווירה כפרית, וכן הזמנות למשפחות שהתארחו באזור.</p>
<h2>מה חשוב לדעת?</h2>
<p>נכון למועד הבדיקה לא נמצא אישור לחזרה לפעילות. שעות הפתיחה, הטלפון והניווט שהופיעו בעבר אינם מוצגים כמידע פעיל, כדי למנוע נסיעה מיותרת. אם העסק יודיע על פתיחה מחדש, נבדוק את הפרטים ונעדכן את העמוד.</p>
<h2>אפשרויות נוספות באזור</h2>
<p>אפשר לחפש בתי קפה ומקומות אוכל פעילים דרך <a href="https://gonorth.co.il/places/">מאגר המקומות בצפון</a>, ולשלב את הביקור עם רעיונות מתוך <a href="https://gonorth.co.il/weekend-trip-north-israel/">מדריך סוף השבוע בצפון</a>. מומלץ לאמת שעות פעילות מול העסק הנבחר לפני היציאה.</p>
'@})
if(Test-Path $backupPath){throw "Backup already exists: $backupPath"}
$before=@($updates|ForEach-Object{GetWp $_.id}); foreach($p in $before){if($p.post_status -ne 'draft'){throw "Expected draft for $($p.post_id), found $($p.post_status)"}}; $before|ConvertTo-Json -Depth 6|Set-Content $backupPath -Encoding utf8
$results=@()
foreach($u in $updates){ $old=$before|Where-Object{[int]$_.post_id -eq $u.id}; $live=GetWp $u.id; if($live.post_modified_gmt -ne $old.post_modified_gmt -or $live.post_content -cne $old.post_content){throw "Concurrent change $($u.id)"}; $content=$u.content.Trim(); $extra='<param><value><struct><member><name>post_status</name><value><string>publish</string></value></member><member><name>post_excerpt</name><value><string>'+(X $u.excerpt)+'</string></value></member><member><name>post_content</name><value><string>'+(X $content)+'</string></value></member></struct></value></param>'; $r=CallWp 'wp.editPost' $u.id $extra; if($r.methodResponse.params.param.value.boolean -ne '1'){throw "Write mismatch $($u.id)"}; $saved=GetWp $u.id; if($saved.post_status -ne 'publish' -or $saved.post_content -cne $content -or $saved.post_excerpt -cne $u.excerpt){throw "Verification mismatch $($u.id)"}; $results+=[pscustomobject]@{id=$u.id;title=$saved.post_title;status=$saved.post_status;link=$saved.link;notice_present=$saved.post_content.Contains('המידע נבדק לאחרונה ב־23 בספטמבר 2026');old_hours_removed=(-not $saved.post_content.Contains('שעות פתיחה:'));guide_link_present=$saved.post_content.Contains('weekend-trip-north-israel');verified_at=(Get-Date).ToUniversalTime().ToString('o')}; $results|ConvertTo-Json -Depth 6|Set-Content $qaPath -Encoding utf8 }
$results|Format-Table -AutoSize
