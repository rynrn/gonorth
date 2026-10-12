# Issue #100 — אצווה B פורסמה

תאריך: 2026-10-01

## תוצאה

פורסמו שתי רשומות חדשות לאחר ולידציה, בדיקת כפילויות, יצירה במצב ממתין, חיבור תמונה ואימות ציבורי:

1. פאן ריידר בצפון — ID 476
   - קטגוריה: tours
   - אזור: galil
   - מקור רשמי: https://funraiderbzafon.co.il/אודות/
   - Google Place ID: ChIJs8iEj3KhHhURvORrh6SY09E
   - תמונה: Google Places, הועלתה על ידי העסק
   - קובץ מדיה: issue100-funraider-google.jpg
   - עמוד: https://gonorth.co.il/places/%d7%a4%d7%90%d7%9f-%d7%a8%d7%99%d7%99%d7%93%d7%a8-%d7%91%d7%a6%d7%a4%d7%95%d7%9f/

2. Nice to Meat — ID 477
   - קטגוריה: restaurants-sub
   - אזור: galil
   - מקור רשמי: https://www.nicetomeatrest.co.il/
   - Google Place ID: ChIJT1nuvL8fHBURJh8BTXoSWQQ
   - תמונה: Google Places, הועלתה על ידי העסק
   - קובץ מדיה: issue100-nicetomeat-google.jpg
   - עמוד: https://gonorth.co.il/places/nice-to-meat/

## בקרות

- tools/validate-place-records.py: עבר, 2 רשומות, ללא שגיאות או אזהרות.
- בדיקת כפילויות לפי כותרת ומזהה Google Place: לא נמצאו התאמות.
- PHP lint לסקריפט היצירה: עבר.
- שתי הרשומות נוצרו תחילה כ-pending.
- הנתונים המעשיים נשמרו דרך GoNorth_Place_Data::update.
- נשמרו ALT, שם מעלה, קישור ייחוס, מזהה משאב Google ותאריך שליפה לכל תמונה.
- אימות קנוני לאחר פרסום נשמר בקבלה.
- אימות ציבורי: שני הדפים מחזירים HTTP 200 ומציגים את הכותרת ואת קובץ התמונה הנכון.

## שחזור

נקודת המצב לפני האצווה הייתה ללא התאמות לשמות או למזהי Google. במקרה צורך ניתן להחזיר את המצב באמצעות הסרת הרשומות 476 ו-477 והמדיה 478 ו-479. לא בוצעו שינויים ברשומות קיימות.

## ראיות

- Candidates: memory/data/2026-10-01-issue-100-batch-b-candidates.json
- Validation: memory/data/2026-10-01-issue-100-batch-b-validation.json
- Receipt: memory/data/2026-10-01-issue-100-batch-b-receipt.json

