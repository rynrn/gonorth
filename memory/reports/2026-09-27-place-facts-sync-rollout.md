# rollout תשתית place-facts — 27.09.2026

## תחום ומיפוי

הכותרות שסופקו כ־#74–#83 הן חבילות עבודה בתוכנית השיחה. המספרים #74–#83 ב־GitHub שייכים למשימות אחרות שנוצרו בביקורת הארגונית, ולכן לא נסגרו ולא שונו. העבודה כאן מתועדת מול [GitHub #70](https://github.com/rynrn/gonorth/issues/70) ובנושא דירוגים מול [GitHub #31](https://github.com/rynrn/gonorth/issues/31).

## מצב לפי חבילת עבודה

| חבילה | מצב | ראיה |
|---|---|---|
| #74 — פריסה, גיבוי ו־PHP lint | הושלם | תוסף חי 1.18.3; גיבויי 1.18.1 וגיבויי apply פרטיים בשרת; lint מלא ובדיקות הליבה עברו. |
| #75 — Export ו־reconciliation מול WordPress | הושלם | export טרי מ־1.18.1 הועבר באישור; checksum זהה בשרת ובמחשב; 109 רשומות, 872 שדות תואמים ו־0 פערים. |
| #76 — Dry-run לחבילת sync | הושלם | חבילה סופית עם 0 שינויים ו־0 דחיות; dry-run חי החזיר receipt ללא כתיבה. |
| #77 — Apply, backup ו־rollback | הושלם | חזרת apply מבוקרת על post 48, גיבוי פרטי, rollback ושוויון SHA-256 לפני/אחרי. באותה בדיקה תוקן שחזור שדות Google nullable. |
| #78 — ביקורת שינויים ממתינים | הושלם | submit, pending, review, export ו־record-wordpress-sync פעילים; 504 שינויי Google אושרו, ו־8 בדיקות סגירה נשארו ממתינות במכוון. |
| #79 — מיפוי Google Place ID | מתקדם | מפתח שרת פעיל. 84/109 Place IDs אושרו וסונכרנו; 14 מועמדות לא בטוחות, 2 חסרות קואורדינטות, תוצאה אחת חסרה ו־8 סגירות נשארו לבדיקה. |
| #80 — אימות Google Places באצוות | הושלם לסבב הראשון | dry-run מלא עבר על 109 מקומות: 72 התאמות אוטומטיות, 28 חלשות, 6 סגירות, 2 ללא קואורדינטות ותוצאה חסרה אחת. לאחר סקירה ידנית כוללת אושרו 84 התאמות. |
| #81 — אישור שינויים וסגירות | הושלם תשתיתית; 8 בבדיקה | שתי סגירות ידועות נשארו עם URL חי והודעה. שמונה תוצאות סגירה חדשות הוכנסו לתור ולא שונו באתר; אין מחיקה או apply אוטומטי. |
| #82 — דירוגים, ביקורות ו־AggregateRating | הושלם ל־84 מקומות | 84 חבילות מלאות כוללות Place ID, דירוג, מספר ביקורות ותאריך בדיקה. הדירוג מוצג עם ייחוס Google; אין AggregateRating חיצוני. |
| #83 — QA וניטור | הושלם | 109/109 מחזירים 200, canonical יחיד ו־H1 יחיד; 0 שגיאות קריטיות; heartbeat שבועי שקט נוצר. |

## פריסה 1.18.1–1.18.3

ב־27.09.2026 פקודת WordPress הועברה מה־Places API הישן ל־Places API (New):

- Text Search (New): `https://places.googleapis.com/v1/places:searchText`
- Place Details (New): `https://places.googleapis.com/v1/places/{place_id}`
- בקשות שרת משתמשות ב־`X-Goog-Api-Key` וב־field mask מצומצם.
- התאמה חדשה נשמרת רק אחרי סף שם ומרחק שמרני.
- `CLOSED_TEMPORARILY` ו־`CLOSED_PERMANENTLY` מפסיקים apply אוטומטי ומועברים לביקורת.
- המפתח אינו מתקבל בשורת הפקודה ואינו מודפס בלוגים.

מקורות רשמיים:

- https://developers.google.com/maps/documentation/places/web-service/overview
- https://developers.google.com/maps/documentation/places/web-service/text-search
- https://developers.google.com/maps/documentation/places/web-service/place-details

## בדיקות

- `py -3.14 -m unittest discover -s place-facts/tests -v`: 11/11 עברו.
- כל קבצי PHP בתוסף עברו `php -l`.
- `tests/google-sync.php`, `integration-transaction.php`, `seo-integration.php`: עברו.
- בדיקת Google מדמה Text Search (New) ומאמתת POST, field mask, היעדר key ב-URL ונרמול ID/דירוג/סטטוס.
- dry-run ב־post 48: `google_http_403`, 0 stored, 0 data changes.
- HTTP: דף הבית 200; post 48 200.
- סריקה ציבורית: 109/109 HTTP 200, canonical יחיד, H1 יחיד, 0 AggregateRating, 0 critical errors.
- סגירות: post 219 ו־221 נשארו חיים והודעת הסגירה קיימת.

## נכסים וראיות

- SQLite פעיל: `place-facts/data/place-facts.sqlite3`
- גיבוי SQLite: `memory/backups/2026-09-26-place-facts-sync/place-facts-before-sync.sqlite3`
- export חי 1.18.1: `memory/data/2026-09-27-wordpress-export-1.18.1.json`
- reconciliation: `memory/data/2026-09-27-wordpress-reconciliation-1.18.1.json`
- חבילת sync סופית: `memory/data/2026-09-27-wordpress-sync-final.json`
- receipt סגירות: `memory/data/2026-09-26-closure-sync-receipt.json`
- QA ציבורי: `memory/data/2026-09-27-place-facts-live-qa.json`
- סקריפט QA חוזר: `place-facts/scripts/public-qa.py`
- ניטור: `memory/data/2026-09-27-place-facts-monitoring.json`
- גיבוי קוד חי: `memory/backups/2026-09-26-place-facts-sync/gonorth-place-data-1.17.3.tar.gz`
- גיבויי apply/rollback, export ו־lint נשמרים בהרשאות פרטיות בשרת.

## חסמים שנותרו

1. להגדיר מפתח נפרד ל־Places API (New), מוגבל ל־IP השרת `204.168.145.116`, ולשמור אותו כ־`gonorth_google_server_api_key`. אין לשלוח את המפתח בצ׳אט.

לא בוצע apply של Google, לא ניחשו Place IDs ולא נמחקו דפי עסקים סגורים.


## עדכון 28.09.2026 — Google Places פעיל

- מפתח Places API (New) הוגדר בשרת עם הגבלת IP, בלי חשיפת המפתח בלוגים או בקבצי הפרויקט.
- התוסף עודכן ל־1.18.3. נוספה אפשרות --format=json לפלט reviewable עם Place ID; ברירת המחדל נשארה dry-run.
- סריקה של 109 מקומות החזירה 72 מועמדות שעברו את שער הקוד, 28 weak match, שש סגירות זמניות, שני מקומות ללא קואורדינטות ותוצאה אחת ללא חבילת דירוג מלאה.
- 55 התאמות מדויקות או וריאציות שם ברורות אושרו בסבב הראשון. 12 התאמות נוספות אושרו לאחר סקירה ידנית. לאחר סקירת המועמדים החלשים אושרו עוד 17 התאמות של שם זהה, תעתיק או שגיאת כתיב. בסך הכול 84 חבילות מלאות סונכרנו.
- שלושה dry-runs עברו 55/55, 12/12 ו־17/17. שלושה apply-ים יצרו גיבויים פרטיים והחזירו synced בכל הרשומות.
- reconciliation סופי: 109 רשומות מול 109, 872 שדות תואמים ו־0 פערים.
- audit חי: 84 Place IDs עם דירוג, מספר ביקורות ותאריך בדיקה מלא; 0 חבילות מיושנות.
- שמונה תוצאות סגירה נשארו כתור review בלבד. ה־URLs חיים ולא שונו.
- QA ציבורי לדוגמאות מסונכרנות וסגירה: HTTP 200, דירוג וייחוס Google מוצגים במקום המתאים, ו־0 AggregateRating.

### ראיות חדשות

- memory/data/2026-09-28-google-sync-dry-run.json
- memory/data/2026-09-28-google-safe-submit.json
- memory/data/2026-09-28-google-manual-safe-submit.json
- memory/data/2026-09-28-google-wordpress-sync-apply-receipt.json
- memory/data/2026-09-28-google-wordpress-sync-manual-apply-receipt.json
- memory/data/2026-09-28-wordpress-reconciliation-after-google-final.json
- memory/data/2026-09-28-pending-closure-reviews.json

### נותר להמשך

- 14 מועמדות לא בטוחות דורשות חיפוש ידני; אין לשייך דירוג של חניון, פארק או מסעדה לישות אחרת.
- post 219 ו־221 חסרים קואורדינטות.
- post 405 החזיר חבילת Google לא מלאה.
- שמונה סגירות דורשות אימות נוסף ויישום הודעת סגירה/חלופות אם יאושרו.



## עדכון 28.09.2026 — ביקורת הסגירות הושלמה

- כל שמונת סימוני הסגירה נבדקו: שבעה הוחזרו ל־operational על בסיס מקורות עדכניים; קפה התן נשאר temporarily_closed.
- קפה התן נשאר מפורסם עם HTTP 200, הודעה בולטת, מידע היסטורי וחלופות. טלפון, שעות, קואורדינטות וכפתורי ניווט ישנים הוסרו.
- מדריך הקפה אינו מציג או מקשר לקפה התן.
- התוסף עודכן ל־1.18.4 ומסנן עמודים עם _gonorth_business_status סגור מהמלצות אוטומטיות. עמודי 217, 219 ו־221 סומנו.
- בדיקת internal links עברה, ו־QA ציבורי עבר לשני העמודים.
- monitor סופי: 0 שינויים ממתינים, 0 בדיקות סגירה, 84/109 חבילות Google מלאות.
- reconciliation סופי: 109/109, ‏872 שדות תואמים, 0 פערים.

פירוט: [דוח ביקורת הסגירות](2026-09-28-google-closure-review.md).


## עדכון 28.09.2026 — ביקורת מועמדים חלשים סופית

- 14 מועמדים נבדקו ידנית: 3 אושרו ו־11 נדחו.
- עזיזו, גיל גולן ומבשלת בזלת קיבלו חבילת Google מלאה לאחר התאמת טלפון, יישוב וזהות עסקית.
- הכיסוי עלה מ־84 ל־87 מתוך 109.
- dry-run, apply עם גיבוי, receipt, reconciliation ו־QA ציבורי עברו.
- reconciliation: ‏109/109, ‏872 שדות תואמים, 0 פערים.
- monitor: ‏0 pending changes, ‏0 closure reviews.
- 11 הדחיות תועדו כדי למנוע שיוך דירוג של חניון, פארק, אתר כללי או עסק אחר.

פירוט: [ביקורת המועמדים הסופית](2026-09-28-google-final-candidate-review.md).


## עדכון 28.09.2026 — חמת גדר הותאמה לישות Google הראשית

- החיפוש האוטומטי בחר קודם רשומת locality ללא דירוג ולכן נעצר בבטחה.
- ישות אתר הנופש הראשית אומתה לפי קואורדינטות זהות, אתר, טלפון והיקף הפעילות.
- Place ID, דירוג 4.2 ו־7,995 ביקורות סונכרנו לאחר dry-run וגיבוי.
- הכיסוי עלה מ־87 ל־88 מתוך 109.
- reconciliation נשאר 109/109, ‏872 שדות תואמים ו־0 פערים.
- QA ציבורי עבר: דירוג וייחוס Google, H1 יחיד, canonical יחיד ו־0 AggregateRating.
- תורי pending changes ו־closure reviews נשארו ריקים.

פירוט: [דוח התאמת חמת גדר](2026-09-28-hamat-gader-google-match.md).
