# Issue #100 — אצווה C פורסמה

תאריך: 2026-10-01

## פורסמו

- שוורצע (משק שוורץ) — ID 480, Media 482
  - מקור אימות חלופי: Easy, בתוספת Google Places פעיל
  - סיבת המקור החלופי: לא נמצא אתר רשמי פעיל לעסק
  - קטגוריה: ochel; אזור: galil
  - תמונת Google Places מזוהה של חזית החנות
  - https://gonorth.co.il/places/%d7%a9%d7%95%d7%95%d7%a8%d7%a6%d7%a2-%d7%9e%d7%a9%d7%a7-%d7%a9%d7%95%d7%95%d7%a8%d7%a5/

- Peti Peti — פטי פטי — ID 481, Media 483
  - מקור: קישור הזמנות פעיל של העסק ומקור תיירות אזורי
  - קטגוריה: restaurants-sub; אזור: galil
  - תמונת Google Places רוחבית שהועלתה על ידי העסק
  - https://gonorth.co.il/places/peti-peti-%d7%a4%d7%98%d7%99-%d7%a4%d7%98%d7%99/

## בקרות

- ולידציה: 2 רשומות, ללא שגיאות או אזהרות.
- בדיקת כפילויות לפי שם ו-Google Place ID: ללא התאמות.
- PHP lint: עבר.
- נוצרו תחילה כ-pending.
- הנתונים נכתבו דרך GoNorth_Place_Data::update.
- שעות לא הוזנו משום שלא אומתו מול מקור רשמי יציב.
- לכל תמונה נשמרו ALT, ייחוס, קישור למעלה ומזהה משאב Google.
- אימות ציבורי: שני הדפים HTTP 200 ומכילים כותרת ותמונה נכונות.

## שחזור

לפני האצווה לא היו התאמות. שחזור ממוקד: להסיר את הרשומות 480 ו-481 ואת המדיה 482 ו-483 לאחר אימות הבעלות על המזהים.

## ראיות

- memory/data/2026-10-01-issue-100-batch-c-candidates.json
- memory/data/2026-10-01-issue-100-batch-c-validation.json
- memory/data/2026-10-01-issue-100-batch-c-receipt.json

