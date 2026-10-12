# #93 — פסטורל פורסם ושלושת המלונות קושרו ממדריך הלינה

תאריך: 08.10.2026 (ישראל). כרטיס: https://github.com/rynrn/gonorth/issues/93. סיכון L2; כותב קנוני: WP-CLI לסטטוס הרשומה, WordPress REST מאומת לתוכן המדריך.

לאחר אישור מנהל ובקרת תוכן עצמאית, רשומת [מלון פסטורל כפר בלום](https://gonorth.co.il/places/מלון-פסטורל-כפר-בלום/) #542 הועברה מ־pending ל־publish ללא שינוי בגוף שנבדק. [אתר המלון](https://www.kfarblum-hotel.co.il/about) ו[הוראות ההגעה](https://www.kfarblum-hotel.co.il/location-and-map) אומתו ב־08.10.2026. ניווט Waze הוא יעד בשם המלון ולא שער או דלת קבלה מאומתים. פרטי מחיר, זמינות ושירותים משתנים אינם נטענים כעובדה.

תמונה #543: צילום Hanay מ־27.11.2025. אימות API של [קובץ Commons המדויק #26](https://commons.wikimedia.org/wiki/File:מלון_פסטורל_בכפר_בלום_26.jpg) הראה 4000×3000 ורישיון [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/). המקור המקומי נבדק חזותית מול המלון והשלט; SHA-256 המקור 3e57c8983aec40e18914fda7def34c762ec7adbc06d7e2482c9c3eaa139ada55. ה־ALT, הקרדיט והסבר על שינוי גודל נשמרו.

נוסף סעיף דוגמאות מאומתות תחת ״מלונות״ ב[מדריך הלינה #97](https://gonorth.co.il/לינה-בצפון-ישראל-המדריך-המלא-לצימרים-ו/) עם קישורים לפסטורל #542, [האפנדי #546](https://gonorth.co.il/places/מלון-האפנדי-עכו/) ו[אלמא #548](https://gonorth.co.il/places/מלון-אלמא-זכרון-יעקב/). הנוסח עבר בקרת תוכן עצמאית. המדריך הקיים מכיל הכללות ישנות נוספות שלא נערכו בסבב זה; יש לבקרו בנפרד לפני רענון רחב.

בדיקות: חיפוש כפילות בשם פסטורל החזיר רק #542; סטטוס WP ו־GeoDirectory publish; גוף הרשומה נותר זהה ל־snapshot; דף המקום 200, התמונה 200, דף המדריך 200 וכל שלושת הקישורים מופיעים בפועל. מקור/רישיון והסתייגות ניווט מופיעים בציבור. שחזור הרשומה ושחזור המדריך עברו dry-run ללא שינוי. ראיות: `memory/data/2026-10-08-issue-93-pastoral-publish-*`; שחזור: `memory/scripts/issue93-pastoral-publication-rollback.ps1` ו־`memory/scripts/issue93-lodging-guide-rollback.ps1` עם `-Apply` רק אם שומרי המצב עוברים. דיווח זה אינו מחליף את הדוח על יצירת הרשומה הממתינה מ־06.10.2026.

#93 נשאר פתוח: פארק אדמית/מערת קשת #556 ממתין לאישור פתיחה רשמי ולאימות נקודת הגעה בטוחה. אין לפרסמו על סמך מועד משוער בלבד.

```yaml
status: partial
scope: "Published Pastoral #542 and linked three hotel listings from guide #97"
risk_level: L2
canonical_writer: "WordPress CLI and authenticated WordPress REST"
sources:
  - {url: "https://www.kfarblum-hotel.co.il/about", checked_at: "2026-10-08"}
  - {url: "https://www.kfarblum-hotel.co.il/location-and-map", checked_at: "2026-10-08"}
  - {url: "https://commons.wikimedia.org/wiki/File:מלון_פסטורל_בכפר_בלום_26.jpg", checked_at: "2026-10-08"}
changes_made:
  - "Published #542 without editing reviewed content"
  - "Added #542, #546 and #548 links to lodging guide #97"
validation:
  - {check: "canonical/public listing and guide QA", result: pass, evidence: "memory/data/2026-10-08-issue-93-pastoral-publish-public-qa.json"}
  - {check: "guarded rollback dry runs", result: pass, evidence: "both guards passed; no changes"}
snapshot: "memory/data/2026-10-08-issue-93-pastoral-publish-542-before.json and -97-guide-before.json"
rollback: "pwsh -File memory/scripts/issue93-pastoral-publication-rollback.ps1 -Apply; pwsh -File memory/scripts/issue93-lodging-guide-rollback.ps1 -Apply"
blockers:
  - "Official reopening and safe arrival for Adamit/Keshet #556"
remaining_risks:
  - "Waze hotel destination is not a verified physical entrance"
issue_recommendation: keep-open
```
