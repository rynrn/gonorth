# #93 — שמורת טבע נחל המערות פורסמה וקושרה ממדריך המשפחות

08.10.2026, שעון ישראל. סיכון L2. [כרטיס](https://github.com/rynrn/gonorth/issues/93). המשך ל[דוח הרשומה הממתינה](2026-10-06-issue-93-nahal-mearot-pending-1026.md).

לאחר אישור מנהל וביקורת תוכן עצמאית מעודכנת, רשומה #554 פורסמה ב־WordPress WP-CLI תוך שמירה על גוף התוכן. [דף נחל המערות](https://gonorth.co.il/places/שמורת-טבע-נחל-המערות/) מבדיל בין הכניסה מכביש 4, אזהרת הניווט דרך כרם מהר״ל, וסגירת שביל 4×4 מסוים לבין מצב השמורה כולה. כל אלה תואמים את [רשות הטבע והגנים](https://www.parks.org.il/reserve-park/שמורת-טבע-נחל-המערות/) שנבדקה ב־08.10.2026. סמן המפה מבוסס על [מבנה מידע ב־OSM](https://www.openstreetmap.org/way/117880693); הוא אינו שער כניסה או חניה. דגל הסתרת ניווט ברכב נשמר, ואין בדף קישור נהיגה. עיר, רחוב ונתונים דינמיים לא מאומתים נשארו ריקים.

צילום מרכז המבקרים #555 מ־2016 מיוחס לד״ר אבישי טייכר / Avi1111. בדף מופיעים [מקור Commons](https://commons.wikimedia.org/wiki/File:Nahal_Mearot_-_visitor_center.jpg), [רישיון CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/) והבהרה שהתצלום אינו מאמת את הגישה כיום. ה־ALT מציין את המבנה ואת שנת הצילום. תמונת הנושא מופיעה כאלמנט `img` בפועל. ב[מדריך המשפחות](https://gonorth.co.il/atrakziot-leyeladim-batzafon/) (#270) נוסף קישור בסעיף הטבע עם הפניה לבדיקת מצב השבילים והגעה מכביש 4.

בדיקה קנונית וציבורית: הרשומה, המדריך, התמונה וה־API הציבורי מחזירים HTTP 200; קישורי הרשות, OSM, Commons והרישיון וההסתייגויות מופיעים ב־HTML; קישורי נהיגה נעדרים; קישור המדריך תקין. [פלט QA](../data/2026-10-08-issue-93-nahal-publish-public-qa.json). נשמרו snapshots לפני: [רשומה](../data/2026-10-08-issue-93-nahal-publish-554-before.json), [GeoDirectory](../data/2026-10-08-issue-93-nahal-publish-554-before-geodir.json), [מדריך](../data/2026-10-08-issue-93-nahal-publish-270-before.json). מצב אחרי: [רשומה](../data/2026-10-08-issue-93-nahal-publish-554-after.json), [GeoDirectory](../data/2026-10-08-issue-93-nahal-publish-554-after-geodir.json), [מדריך](../data/2026-10-08-issue-93-nahal-publish-270-after.json). [שחזור מוגן](../scripts/issue93-nahal-publication-rollback.ps1) עבר dry-run; Apply לא הופעל.

#93 נשאר פתוח: חמישה מועמדים נוספים דורשים ביקורת פרסום וקישורי מדריכים. אדמית דורש אישור רשמי לפתיחה מחדש ואימות הגעה.

```yaml
status: complete
scope: "published Nahal Me'arot #554 and linked guide #270; parent #93 remains open"
risk_level: L2
canonical_writer: "WordPress WP-CLI for listing status; authenticated WordPress REST for guide"
findings:
  - "The 4×4 trail closure is distinct from a reserve-wide closure."
sources:
  - {url: "https://www.parks.org.il/reserve-park/שמורת-טבע-נחל-המערות/", checked_at: "2026-10-08"}
  - {url: "https://commons.wikimedia.org/wiki/File:Nahal_Mearot_-_visitor_center.jpg", checked_at: "2026-10-08"}
changes_made:
  - "Published listing #554 and linked it from guide #270."
unchanged:
  - {item: "Unverified city, street, hours, price, phone and rating", reason: "No verified canonical value"}
validation:
  - {check: "Canonical and public listing/guide/image/API and navigation", result: pass, evidence: "memory/data/2026-10-08-issue-93-nahal-publish-public-qa.json"}
  - {check: "Guarded rollback dry run", result: pass, evidence: "Rollback guard passed; no changes made"}
snapshot: "memory/data/2026-10-08-issue-93-nahal-publish-554-before.json and memory/data/2026-10-08-issue-93-nahal-publish-270-before.json"
rollback: "memory/scripts/issue93-nahal-publication-rollback.ps1 -Apply after checking guards"
blockers: []
remaining_risks:
  - "Other #93 candidates await review; access notices may change."
issue_recommendation: keep-open
```
