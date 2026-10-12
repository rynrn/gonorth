# #93 — שמורת טבע עין אפק פורסמה וקושרה ממדריך המשפחות

06.10.2026, שעון ישראל. סיכון L2. [כרטיס](https://github.com/rynrn/gonorth/issues/93). המשך ל[דוח הרשומה הממתינה](2026-10-06-issue-93-ein-afek-pending-0632.md).

לאחר אישור מנהל וביקורת תוכן עצמאית, רשומה #536 פורסמה באמצעות WordPress WP-CLI תוך שמירה מדויקת על גוף התוכן והקישורים שנבדקו. [דף שמורת טבע עין אפק](https://gonorth.co.il/places/שמורת-טבע-עין-אפק/) מציג מידע מבוסס על [רשות הטבע והגנים](https://www.parks.org.il/reserve-park/שמורת-טבע-עין-אפק/), הסתייגות ש־Waze מוביל ליעד השמורה אך אינו מזהה שער פיזי, והנחיה לבדוק תנאים עדכניים. עיר, רחוב, שעות, מחיר, טלפון ודירוג שלא אומתו נשארו ריקים. קטגוריה, אזור ומיקום לא שונו.

תמונת הגשר הצף #537 מיוחסת ל־Roni250b עם [קישור לקובץ ב־Commons](https://commons.wikimedia.org/wiki/File:Tel_afek_-_The_wooden_flow_bridge_over_the_swamp.jpg), [רישיון CC BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0/) וציון הקטנה ודחיסה. התמונה אינה מוצגת כראיה למצב הנגישות כיום. ב[מדריך אטרקציות לילדים בצפון](https://gonorth.co.il/atrakziot-leyeladim-batzafon/) (#270) נוסף משפט קשר בסעיף שמורות הטבע; קישור ציפורי ושאר התוכן נשמרו.

בדיקות קנוניות וציבוריות: הרשומה, המדריך, התמונה וה־API הציבורי מחזירים HTTP 200; מקור המפעיל, קרדיט הצלם, מקור התמונה, רישיון והסתייגות הניווט קיימים ב־HTML. קישור המדריך אומת בכתובת עברית קריאה; API GeoDirectory מחזיר אותה כתובת בקידוד אחוזים. [QA](../data/2026-10-06-issue-93-ein-afek-publish-public-qa.json). צילום מקדים: [רשומה](../data/2026-10-06-issue-93-ein-afek-publish-536-before.json), [GeoDirectory](../data/2026-10-06-issue-93-ein-afek-publish-536-before-geodir.json), [מדריך](../data/2026-10-06-issue-93-ein-afek-publish-270-before.json). מצב אחרי: [רשומה](../data/2026-10-06-issue-93-ein-afek-publish-536-after.json), [GeoDirectory](../data/2026-10-06-issue-93-ein-afek-publish-536-after-geodir.json), [מדריך](../data/2026-10-06-issue-93-ein-afek-publish-270-after.json). [סקריפט השחזור המוגן](../scripts/issue93-ein-afek-publication-rollback.ps1) עבר dry-run; Apply לא הופעל.

#93 נשאר פתוח: תשעה מועמדים אחרים ממתינים לביקורת פרסום וקישורי מדריכים. אדמית דורש אישור רשמי לפתיחה מחדש ואימות הגעה.

```yaml
status: complete
scope: "published Ein Afek #536 and linked guide #270; parent #93 remains open"
risk_level: L2
canonical_writer: "WordPress WP-CLI for listing status; authenticated WordPress REST for guide"
findings:
  - "Public guide uses a readable Hebrew URL equivalent to the encoded GeoDirectory link."
sources:
  - {url: "https://www.parks.org.il/reserve-park/שמורת-טבע-עין-אפק/", checked_at: "2026-10-06"}
  - {url: "https://commons.wikimedia.org/wiki/File:Tel_afek_-_The_wooden_flow_bridge_over_the_swamp.jpg", checked_at: "2026-10-06"}
changes_made:
  - "Published listing #536 and linked it from guide #270."
unchanged:
  - {item: "Unverified city, street, hours, price, phone and rating", reason: "No verified canonical value"}
validation:
  - {check: "Canonical and public listing/guide/image/API", result: pass, evidence: "memory/data/2026-10-06-issue-93-ein-afek-publish-public-qa.json"}
  - {check: "Guarded rollback dry run", result: pass, evidence: "Rollback guard passed; no changes made"}
snapshot: "memory/data/2026-10-06-issue-93-ein-afek-publish-536-before.json and memory/data/2026-10-06-issue-93-ein-afek-publish-270-before.json"
rollback: "memory/scripts/issue93-ein-afek-publication-rollback.ps1 -Apply after checking guards"
blockers: []
remaining_risks:
  - "Other #93 candidates await review; reserve access may change."
issue_recommendation: keep-open
```
