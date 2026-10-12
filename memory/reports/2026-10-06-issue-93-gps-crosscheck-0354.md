# #93 — אימות GPS כניסה: ממצאי OSM מול המפעילים

תאריך: 06.10.2026 03:54 (ישראל). המשך למחקר שני המועמדים הראשונים. רמת סיכון L0; לא שונו WordPress, תור המקומות או מדיה.

## מדעטק
[המפעיל מציין](https://www.madatech.org.il/main-info/arrival-ways/) שהכניסה לקופות היא שמריהו לוין 25, ומזהיר שאפליקציות ניווט עלולות לפעול חלקית. בדף שלו יש קישור Waze לנקודה 32.80955060,34.99568310. ב[נתוני OpenStreetMap הגולמיים](https://api.openstreetmap.org/api/0.6/map?bbox=34.9945,32.8085,34.9980,32.8110) דרך `1119264264` (מדעטק) כוללת שלוש נקודות `entrance=yes`: 32.8100450,34.9971614; 32.8106398,34.9962896; 32.8106945,34.9968837. הנקודה האמצעית מקושרת לשביל רגלי ומתקרבת לרחוב שמריהו לוין, אך OSM אינו מתייג אותה כקופה או ככניסה הראשית. נקודת Waze רחוקה ממנה מעל מאה מטר. אין ראיה מספקת לבחור אחת מהן כ־GPS המבקרים. אין להשתמש ב־Waze של החניה או במרכז מצולע המוזיאון כתחליף.

## אולמות האבירים
[המפעיל מציין](https://www.akko.org.il/attraction/the-visitors-center/) שהביקור מתחיל במרכז המבקרים בגן הקסום, ויצמן 1. ב[נתוני OSM הגולמיים](https://api.openstreetmap.org/api/0.6/map?bbox=35.0695,32.9225,35.0725,32.9250) יש נקודת tourist information ב־32.9237454,35.0700504 ונקודת `entrance=yes` לשביל ב־32.9238457,35.0701681. קישור Google הרשמי בדף מצביע לאזור 32.9238,35.0707325. המקורות מצביעים על אותו מתחם אך אינם מסכימים על דלת מדויקת. נקודת OSM השנייה היא כניסה לשביל, לא בהכרח דלת מרכז המבקרים. אין לקבע GPS מדויק ממנה כרגע.

## החלטה
שני המיקומים נשארים `unknown` מבחינת נקודת כניסת המבקרים; הכתובות המילוליות אומתו. בקשת השלמה למפעיל או בדיקה חזותית של תצלום רחוב עדכני היא הצעד המדויק הבא לפני רשומה עם ניווט. עד אז לא נוצרות רשומות ממתינות. ממצאי OSM הם נתוני מיפוי מתנדבים, ולא אימות תפעולי של כניסה עכשווית. סקריפטי השאילתה ועותקי הבדיקה המקומיים ב־`memory/work/issue93-images/`.

```yaml
status: partial
scope: "GPS כניסה למדעטק ולאולמות האבירים"
risk_level: L0
canonical_writer: "none"
findings:
  - "מדעטק: שלוש נקודות OSM מסומנות כניסה וקישור Waze רחוק; אין זיהוי קופה"
  - "אולמות האבירים: נקודות מרכז מידע, כניסה לשביל וקישור מפעיל אינן זהות"
sources:
  - {url: "https://www.madatech.org.il/main-info/arrival-ways/", checked_at: "2026-10-06"}
  - {url: "https://www.akko.org.il/attraction/the-visitors-center/", checked_at: "2026-10-06"}
  - {url: "https://api.openstreetmap.org/api/0.6/map?bbox=34.9945,32.8085,34.9980,32.8110", checked_at: "2026-10-06"}
  - {url: "https://api.openstreetmap.org/api/0.6/map?bbox=35.0695,32.9225,35.0725,32.9250", checked_at: "2026-10-06"}
changes_made: []
unchanged:
  - {item: "WordPress, places-to-add.md and media", reason: "אין GPS כניסה מאומת"}
validation:
  - {check: "השוואת נקודות OSM, דרך מדעטק ושבילי מרכז המבקרים", result: pass, evidence: "memory/work/issue93-images/check-osm*.ps1"}
snapshot: "not-required"
rollback: "no live change"
blockers:
  - "יש לאמת נקודת כניסה תפעולית מול המפעיל או ראיית רחוב עדכנית"
remaining_risks:
  - "מיקום OSM עשוי לתאר שער מתחם ולא דלת קופה"
issue_recommendation: keep-open
```
