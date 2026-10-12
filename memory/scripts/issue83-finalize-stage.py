from pathlib import Path
p=Path('memory/scripts/issue83-staged/place-analytics.js')
s=p.read_text(encoding='utf-8')
old="\tfunction sendOnce( key, eventName, parameters ) {\n\t\tif ( sentOnce.has( key ) ) return;"
new="\tfunction sendOnce( key, eventName, parameters ) {\n\t\ttry { if ( '1' !== window.localStorage.getItem( 'gn_cookie_ok' ) ) return; } catch ( error ) { return; }\n\t\tif ( sentOnce.has( key ) ) return;"
assert old in s
s=s.replace(old,new,1)
old2="\t\tif ( config.currentPlace ) window.GoNorthAnalytics.trackPlaceView();"
new2="\t\tif ( config.currentPlace ) {\n\t\t\twindow.GoNorthAnalytics.trackPlaceView();\n\t\t\twindow.addEventListener( 'gn-consent-granted', function () { window.GoNorthAnalytics.trackPlaceView(); }, { once: true } );\n\t\t}"
assert old2 in s
s=s.replace(old2,new2,1)
p.write_text(s,encoding='utf-8')
policy=Path('memory/data/2026-10-04-issue-83-prechange/privacy-post.html').read_text(encoding='utf-8').strip()
old3='<h2>שירותי צד שלישי</h2><ul><li>מפות ורכיבי מיקום עשויים להשתמש בשירותי Google כאשר הם פעילים בעמוד.</li><li>כלי הנגישות באתר מופעל על ידי OneTap.</li></ul><h2>מדידה ואנליטיקה</h2><p>נכון ליום 7 באפריל 2026 לא הופעלה באתר מערכת אנליטיקה לשיווק או מדידה. אם יתווספו שירותים כאלה, המדיניות תעודכן בהתאם.</p>'
new3='<h2>שירותי צד שלישי</h2><ul><li>עמודי מקום עשויים לטעון מפה של OpenStreetMap ורכיבי מיקום נוספים כאשר הם מוצגים.</li><li>גופנים ומשאבי תצוגה עשויים להיטען מ־Google Fonts ומ־Font Awesome גם ללא אישור למדידה.</li><li>כלי הנגישות באתר מופעל על ידי OneTap.</li><li>רכיב Google Preferred Sources נטען רק לאחר אישור מדידה.</li></ul><h2>מדידה ואנליטיקה</h2><p>נכון ל־4 באוקטובר 2026 האתר משתמש ב־Google Tag Manager וב־Google Analytics 4 רק לאחר בחירה מפורשת ב״מאשר/ת מדידה״. בבחירה ״ללא מדידה״ או לפני בחירה, רכיבי מדידה אלה אינם נטענים. לאחר אישור נמדדים צפיות בדפים ואירועי שימוש כגון מעבר ממדריך למקום ולחיצה על פעולה בדף מקום. כתובת העמוד והמפנה הנשלחים דרך הגדרת המדידה מוסרים מפרמטרי השאילתה וממקטע ה־fragment. אפשר לשנות את הבחירה באמצעות הכפתור ״העדפות פרטיות״ המופיע באתר. הבחירה נשמרת בדפדפן; ביטול האישור עוצר טעינת מדידה בביקורים הבאים ומרענן את הדף הנוכחי.</p>'
assert old3 in policy
Path('memory/scripts/issue83-staged/privacy-post.html').write_text(policy.replace(old3,new3,1),encoding='utf-8')
print('analytics and policy staged')