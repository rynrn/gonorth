# Issue #103 — תיקון GPS יקב תל שיפון

תאריך: 06.10.2026  
כרטיס: https://github.com/rynrn/gonorth/issues/103  
דף מקום: https://gonorth.co.il/places/%d7%99%d7%a7%d7%91-%d7%aa%d7%9c-%d7%a9%d7%99%d7%a4%d7%95%d7%9f/

## מקור והחלטה

[מרכז המבקרים באתר היקב](https://www.telshifonwinery.co.il/visitor-center) מציג את קיבוץ אורטל ומקשר ישירות ליעד Waze `venues.23462219.234360044.63283`. [דף הניווט של אותו יעד ב־Waze](https://www.waze.com/live-map/directions?to=place.w.23462219.234360044.63283) מחזיר בכרטיס היעד „יקב תל שיפון tel shifon winery”, אורטל, עם `routing.to.latLng = 33.084579429, 35.761299609`. פרופיל עסק אחר ב־Waze, בשם Tel Shifon Winery ובאותה כתובת, מציג 33.0844651,35.7612252 — כ־14.5 מטרים משם. נבחרה נקודת הניווט שהיקב מקשר אליה בעצמו, בלי להמציא נקודת GPS. המקורות נבדקו ב־06.10.2026.

לפני השינוי GeoDirectory שמר 33.2507,35.7764, כ־18.525 ק״מ מהיעד הרשמי, בעוד העיר הייתה אורטל ו־`gn_region=golan`.

## שינוי ושחזור

רמת סיכון L2. לאחר [אימות הרשומה](../data/2026-10-06-issue-103-gps-plan.json) באמצעות `tools/validate-place-records.py`, נשמרו snapshots של [הרשומה הקנונית](../data/2026-10-06-issue-103-before-edit.json), [REST הציבורי](../data/2026-10-06-issue-103-before-public.json) ו[הדף הציבורי](../data/2026-10-06-issue-103-before-page.html). הכותב הקנוני היה GeoDirectory REST מאומת: עודכנו רק `latitude` ו־`longitude` של #235 באמצעות [סקריפט מוגן](../scripts/issue103-update-gps.ps1), לאחר בדיקת ערכי טרום השינוי ומועד העדכון. לא נכתבו טבלאות SQL ולא שונו כתובת, טקסט, קטגוריה או אזור.

שחזור: מתוך שורש הפרויקט להריץ `& memory/scripts/issue103-rollback-gps.ps1`. [סקריפט השחזור](../scripts/issue103-rollback-gps.ps1) מוודא קודם שהנקודה החיה היא הנקודה החדשה, ואז מחזיר דרך אותו GeoDirectory REST את 33.2507,35.7764 ואת בדיקת הקריאה החוזרת. הוא הוכן ונבדק תחבירית; לא הופעל כדי לא להחזיר מיקום שגוי.

## אימות אחרי שינוי

- [GeoDirectory REST קנוני](../data/2026-10-06-issue-103-after-edit.json) ו[ציבורי](../data/2026-10-06-issue-103-after-public.json): 33.084579429,35.761299609; סטטוס `publish`, עיר אורטל ואזור צפון נשמרו.
- [הדף הציבורי](../data/2026-10-06-issue-103-after-page.html) מחזיר HTTP 200. קישור Waze, קישור Google Maps, סמן OpenStreetMap ו־JSON-LD `GeoCoordinates` משתמשים כולם בנקודה החדשה. הקואורדינטה הישנה אינה מופיעה בדף.
- `wp post term list 235 gn_region` החזיר `golan`. לא נמצאו שינויים בשדות תוכן אחרים לפי בקשת REST הקנונית.

```yaml
status: complete
scope: "GPS דף יקב תל שיפון #235"
risk_level: L2
canonical_writer: "GeoDirectory REST מאומת"
findings:
  - "הנקודה הישנה רחוקה 18.525 ק״מ מהיעד שהיקב מקשר אליו"
sources:
  - {url: "https://www.telshifonwinery.co.il/visitor-center", checked_at: "2026-10-06"}
  - {url: "https://www.waze.com/live-map/directions?to=place.w.23462219.234360044.63283", checked_at: "2026-10-06"}
changes_made:
  - "latitude/longitude של #235 ל-33.084579429,35.761299609"
unchanged:
  - {item: "שעות, תוכן, כתובת, קטגוריה ואזור", reason: "אינם חלק מתיקון נקודת ניווט"}
validation:
  - {check: "validate-place-records", result: pass, evidence: "רשומה אחת, אפס שגיאות"}
  - {check: "REST, Waze, Maps, OSM, JSON-LD, gn_region", result: pass, evidence: "memory/data/2026-10-06-issue-103-after-page.html"}
snapshot: "memory/data/2026-10-06-issue-103-before-edit.json"
rollback: "& memory/scripts/issue103-rollback-gps.ps1"
blockers: []
remaining_risks: []
issue_recommendation: close
```
