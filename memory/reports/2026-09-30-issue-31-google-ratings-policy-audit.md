# Issue #31 — דירוגי Google, רעננות ו־AggregateRating

תאריך: 30.09.2026

## תוצאה

- 105/109 רשומות כוללות חבילת Google מלאה: Place ID, דירוג, מספר דירוגים ותאריך בדיקה.
- 109/109 כוללות מקור אימות עדכני; ארבע הרשומות ללא ישות Google נשענות על מקורות חלופיים ומציגות שאין דירוג Google.
- אין רשומה שמועד האימות שלה הגיע, אין pending changes ואין closure reviews.
- סריקת 109 דפים מצאה 0 מופעים של AggregateRating, 109 תשובות HTTP 200, canonical יחיד ו־H1 יחיד בכל דף.
- בדיקת העסקה החיה אישרה שחבילת Google חלקית אינה נחשפת ושדירוג חיצוני אינו נפלט כ־AggregateRating; העסקה בוטלה בסוף הבדיקה.

## מדיניות

Google Places מתירה שמירה ארוכת טווח של Place ID, אך מגבילה אחסון מוקדם או מתמשך של תוכן Places ודורשת ייחוס מתאים בתצוגה. דירוג, מספר דירוגים ומועד בדיקה נשמרים כחבילה מיוחסת לצורכי תצוגה ורעננות, ולא מוצגים כביקורות מקוריות של GoNorth.

Google Search אוסרת לאגד דירוגים או ביקורות מאתרים אחרים. לכן נתוני Google הגלויים אינם מוזנים ל־AggregateRating. אם יוקם בעתיד מערך ביקורות מקוריות של GoNorth, הוא ייבדק בנפרד.

מקורות רשמיים שנבדקו ב־30.09.2026:

- https://developers.google.com/maps/documentation/places/web-service/policies
- https://developers.google.com/maps/documentation/places/web-service/place-details
- https://developers.google.com/search/docs/appearance/structured-data/review-snippet
- https://developers.google.com/search/docs/appearance/structured-data/sd-policies

## כיסוי וחריגים

דוח monitor הנוכחי מסווג 104 רשומות תחת google_places וחמש תחת מקור חלופי: ארבע הרשומות הידועות ועוד אגמון החולה שאומת מחדש מול אתר רשמי במסגרת #80. במקביל קיימים 105 Place IDs וחבילות דירוג מלאות. זהו הבדל בין מקור האימות העדכני לבין עצם קיום חבילת Google, לא פער נתונים.

ארבעת החריגים ללא ישות Google נשארים תחת #99. אין לשייך להם מזהה או דירוג של עסק סמוך.

## בדיקות וראיות

- memory/data/2026-09-30-issue-31-public-qa.json
- memory/data/2026-09-30-issue-31-monitor.json
- memory/data/2026-09-30-issue-31-place-facts-export.json
- 11 בדיקות place-facts עברו.
- בדיקת google-sync.php החיה עברה בתוך transaction עם rollback.

## שינוי ו־rollback

רמת סיכון L0. לא בוצע שינוי באתר או בפנקס, ולכן snapshot ו־rollback אינם נדרשים.
