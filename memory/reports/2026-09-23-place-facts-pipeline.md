# תשתית אימות מידע משתנה — #70

תאריך: 23.09.2026

## מה הוקם

- סכמת SQLite עם places, place_facts, place_verifications ו־place_changes.
- CLI ללא תלויות חיצוניות לאתחול, ייבוא snapshot, בחירת מקומות לבדיקה, הגשת תוצאות, ביקורת שינויים וייצוא JSON/Markdown.
- לקוח Google Places שמבקש רק סטטוס עסק, כתובת, טלפון, אתר, קישור מפות ושעות; המפתח נקרא ממשתנה סביבה בלבד.
- הגנה על SEO: שינוי לסטטוס סגור נכנס לביקורת ומחזיר הוראה לשמור את כתובת העמוד חיה. אין פעולת מחיקה, טיוטה או 404 במערכת.
- תיעוד חוזה הסנכרון מול gonorth-place-data: SQLite הוא יומן האימות ומקור האמת לשינויים, ו־GeoDirectory הוא ההיטל באתר החי.

## מצב הנתונים

- נאסף snapshot ציבורי חדש של 109 עמודים.
- 109/109 מקומות ו־109/109 רשומות facts יובאו למסד.
- עמוד 219 נשמר כ־permanently_closed ועמוד 221 כ־temporarily_closed לאחר יצירת שינוי ממתין ואישורו.
- 0 שינויים ממתינים בסיום.
- 55 מקומות נמצאים בתור בדיקה של 90 יום.
- כיסוי Google Place ID הוא 0/109 ולכן fetch-google אינו יכול עדיין לבדוק מקומות חיים.

## אימות

- SQLite integrity_check: ok.
- שש בדיקות אינטגרציה עברו, כולל מיפוי Google Place ID דרך ביקורת.
- compileall עבר.
- ייבוא חוזר של 109 מקומות עבר ללא יצירת כפילויות.
- יצוא JSON ו־Markdown נוצר בהצלחה.

## קבצים

- קוד ותיעוד: `place-facts/`
- snapshot: `memory/data/2026-09-23-place-facts-baseline-page-audit.json`
- גיבוי SQLite: `memory/data/2026-09-23-place-facts.sqlite3`
- יצוא JSON: `memory/data/2026-09-23-place-facts-export.json`
- שינויים ממתינים: `memory/data/2026-09-23-place-facts-pending-final.json`
- תור 90 יום: `memory/data/2026-09-23-places-due.json`
- תור Google: `memory/data/2026-09-23-google-places-due.json`
- טבלת סקירה: `memory/reports/2026-09-23-place-facts-review.md`

## הרצת Google Places המאושרת

- המשתמש אישר הרצה חד־פעמית של עד 164 בקשות.
- נשלחו 109 בקשות Text Search דרך שרת GoNorth.
- כל 109 הבקשות נדחו ב־HTTP 403 עם `API_KEY_HTTP_REFERRER_BLOCKED`: המפתח מוגבל ל־HTTP referrer ואינו מורשה לקריאות שרת.
- לא נמצאו מזהים, ולכן לא נשלחו בקשות Place Details. סך השימוש בפועל: 109 בקשות.
- לא נוצרו שינויים ולא עודכנו עמודים באתר. SQLite נשאר תקין (`integrity_check: ok`).
- נוצר גיבוי לפני ההרצה: `memory/data/2026-09-23-place-facts-before-google.sqlite3`.
- תוצאת ההרצה המלאה: `memory/data/2026-09-23-google-places-run.json`.
- כדי להמשיך נדרש מפתח נפרד ל־Places API (New) שמוגבל לכתובת ה־IP של השרת, או שינוי הגבלות המפתח הקיים ב־Google Cloud. אין לעקוף את ההגבלה באמצעות זיוף Referer.
