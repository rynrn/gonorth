# דוח ביצוע — Google Preferred Sources

**GitHub:** #47  
**תאריך:** 26.09.2026  
**סטטוס:** הושלם

## תוצאה

רכיב Google Preferred Sources פורסם בעמוד הבית, באינדקס הבלוג, בכתבות ובדפי מקום. הרכיב מציג הסבר עברי שאינו מבטיח דירוג, ומאפשר להוסיף את `gonorth.co.il` כמקור מועדף ב־Google.

היישום מבוסס על ה־SDK הרשמי במצב manual ועל קישור ה־fallback הרשמי:
`https://www.google.com/preferences/source?q=gonorth.co.il`.

מקור רשמי שנבדק: https://developers.google.com/search/docs/appearance/preferred-sources

## התנהגות

- קוד מקומי נטען ללא חסימת העמוד.
- `publisher.js` נטען באופן אסינכרוני ופעם אחת בלבד.
- טעינת Google ושליחת Analytics מתבצעות רק אחרי דגל ההסכמה הקיים `gn_cookie_ok`.
- ה־SDK מאותחל עם `theme: light` ו־`lang: he`.
- אם Google חסום או נכשל, הקישור הרשמי נשאר פעיל והאתר ממשיך לפעול.
- האירוע `preferred_source_click` נשלח פעם אחת לקליק עם `placement`, `page_type`, `page_location` ו־`place_id` בדף מקום.
- לא נשלח `preferred_source_complete`, משום שאין בתיעוד הרשמי callback אמין להשלמה.

## פריסה

תוסף `gonorth-place-data` עודכן מ־1.17.1 ל־1.17.2. התיעוד החי עודכן, בדיקת PHP עברה ומטמוני WordPress, WP Super Cache ו־Autoptimize נוקו.

גיבוי הקבצים לפני הפריסה נמצא ב־`memory/backups/2026-09-26-issue-47-plugin/`. חבילת הפריסה נמצאת ב־`memory/deploy/issue-47/`.

## בדיקות

- בדיקת התוסף: `Preferred Sources: OK`.
- ללא הסכמה: 0 בקשות ל־SDK ו־0 אירועי Analytics.
- עם הסכמה בדף מקום: בקשת SDK אחת, תג script אחד, קריאת `addPreferredSource()` אחת ואירוע אחד.
- payload שנבדק כלל `placement=footer`, `page_type=place`, `place_id=425` ו־`page_location`.
- חסימת SDK במובייל: העמוד נשאר תקין, fallback קיים, אין גלילה אופקית ואין שגיאות JavaScript.
- עמוד הבית ודף מקום מחזירים HTTP 200 ומציגים רכיב אחד וכפתור אחד.
- בדיקות RTL עברו ברוחב 1440px וברוחב 390px.

נתוני הבדיקה: `memory/data/2026-09-26-issue-47-runtime-qa.json`.
צילום מובייל: `memory/screenshots/2026-09-26-issue-47-mobile.png`.
