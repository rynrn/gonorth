# פתרון התאמת Google לחמת גדר — 28.09.2026

## תוצאה

רשומת חמת גדר (WordPress 405) קיבלה חבילת Google מלאה ומאומתת. הכיסוי עלה מ־87/109 ל־88/109, ללא פער בין פנקס האימות לבין WordPress.

## אבחון

החיפוש האוטומטי “חמת גדר חמת גדר” בחר רשומת locality/political של היישוב, ללא דירוג וללא ביקורות, ולכן מנגנון הבטיחות החזיר incomplete_google_response ולא כתב דבר.

חיפוש ממוקד החזיר מספר ישויות. נבחרה ישות המתחם הראשי:

- Google Place ID: ChIJpW_MwKJsHBUR13O0U86aTl4
- דירוג: 4.2
- מספר ביקורות: 7,995
- סטטוס: פעיל
- כתובת: כביש 98
- טלפון: 04-665-9999
- אתר: hamat-gader.com
- הקואורדינטות זהות לקואורדינטות ברשומת GoNorth.

הישות מייצגת את אתר הנופש המלא שמתואר בדף GoNorth: מרחצאות, בריכות, מתחם מים, חוות תנינים ולינה. רשומת “חמת גדר המרחצאות המודרניים”, מלון ספא וילג' ועין ג'ונס נדחו כישויות משנה או ישויות נפרדות. רשומת היישוב נדחתה משום שאינה מקום העסק.

## סנכרון

שישה שינויי ledger אושרו. שלושה שדות קנוניים נכתבו ל־WordPress לאחר dry-run:

- Google Place ID
- דירוג Google
- מספר ביקורות Google

סטטוס העסק, קישור המפה ותאריך הבדיקה נשמרו בפנקס האימות כשדות ללא הקרנה עצמאית ל־WordPress. גיבוי פרטי נוצר לפני הכתיבה.

שמירת ה־Place ID המדויק מאפשרת לרענונים הבאים לפנות ישירות לישות המאומתת, ולכן אין צורך לשנות את אלגוריתם החיפוש הכללי בעקבות מקרה יחיד זה.

## אימות

- audit חי: 88 Place IDs, ‏88 מספרי ביקורות, ‏88 חותמות רענון ו־88 חבילות Google מלאות; 0 מיושנות.
- reconciliation: ‏109/109 רשומות, ‏872 שדות תואמים, 0 פערים.
- monitor: ‏0 שינויים ממתינים ו־0 בדיקות סגירה.
- הדף הציבורי מציג 4.2 ו־7,995 ביקורות עם ייחוס Google.
- SEO בדף: H1 יחיד, canonical יחיד ו־0 AggregateRating חיצוני.
- הדף נשאר חי ב־HTTP 200.

## ראיות

- memory/data/2026-09-28-hamat-gader-google-decision.json
- memory/data/2026-09-28-hamat-gader-google-submit.json
- memory/data/2026-09-28-hamat-gader-wordpress-sync.json
- memory/data/2026-09-28-hamat-gader-wordpress-sync-receipt.json
- memory/data/2026-09-28-hamat-gader-deferred-receipt.json
- memory/data/2026-09-28-hamat-gader-post-sync-audit.json
- memory/data/2026-09-28-hamat-gader-reconciliation.json
- memory/data/2026-09-28-hamat-gader-monitor.json
