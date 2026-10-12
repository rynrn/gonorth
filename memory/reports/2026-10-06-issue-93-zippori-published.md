# #93 — גן לאומי ציפורי פורסם וקושר ממדריך המשפחות

תאריך: 06.10.2026, שעון ישראל. סיכון L2. [כרטיס](https://github.com/rynrn/gonorth/issues/93). זהו דוח המשך ל[יצירת הרשומה הממתינה](2026-10-06-issue-93-zippori-pending-0656.md).

לאחר ביקורת מנהל וביקורת תוכן עצמאית, רשומה #540 שונתה מ־pending ל־publish באמצעות WordPress WP-CLI, ששמר בדיוק את גוף התוכן והקישורים שנבדקו. [דף גן לאומי ציפורי](https://gonorth.co.il/places/גן-לאומי-ציפורי/) מציג תיאור בעברית, קישור [לרשות הטבע והגנים](https://www.parks.org.il/reserve-park/גן-לאומי-ציפורי/), קרדיט לזאב שטיין ול־Heritage Conservation Outside The City / PikiWiki Israel, [קישור לקובץ ב־Commons](https://commons.wikimedia.org/wiki/File:PikiWiki_Israel_73447_zippori.jpg) ו[רישיון CC BY 2.5](https://creativecommons.org/licenses/by/2.5/). הנחיית הניווט מבהירה שהנקודה היא יעד Waze בשם הגן ולא שער פיזי מאומת. שדות עיר ורחוב נשארו ריקים, ונתוני שעות/מחיר/טלפון לא הומצאו. המפעיל מציין בעת הבדיקה מגבלות זמניות במוקדים אחדים; הרשומה מפנה לבדיקה עדכנית אצלו לפני ביקור ואינה מציגה זמינות כמובטחת.

ב[מדריך המשפחות לאטרקציות בצפון](https://gonorth.co.il/atrakziot-leyeladim-batzafon/) (פוסט #270) נוסף משפט הקשר על אתר מורשת נוסף, וקישור לדף ציפורי. שאר המדריך נשמר. כתובת הרשומה, המדריך והתמונה מחזירות HTTP 200; GeoDirectory API ציבורי מחזיר 200, והמצב הקנוני הוא publish. קישורי המפעיל, מקור התמונה והרישיון, שם היוצר והסתייגות הניווט נמצאו ב־HTML הציבורי. [פלט QA](../data/2026-10-06-issue-93-zippori-publish-public-qa.json).

לפני שינוי נשמרו [snapshot הרשומה](../data/2026-10-06-issue-93-zippori-publish-540-before.json), [GeoDirectory](../data/2026-10-06-issue-93-zippori-publish-540-before-geodir.json) ו[snapshot המדריך](../data/2026-10-06-issue-93-zippori-publish-270-before.json). המצב החדש נשמר ב[רשומה](../data/2026-10-06-issue-93-zippori-publish-540-after.json), [GeoDirectory](../data/2026-10-06-issue-93-zippori-publish-540-after-geodir.json) וב[מדריך](../data/2026-10-06-issue-93-zippori-publish-270-after.json). [סקריפט שחזור מוגן](../scripts/issue93-zippori-publication-rollback.ps1) בודק שמצב הרשומה והמדריך לא השתנה לפני שחזור ל־pending ולתוכן המדריך הקודם. בדיקת dry-run עברה; Apply לא הופעל.

#93 נשאר פתוח לביקורת פרסום וקישורי מדריכים של עוד 10 מועמדים; אדמית דורש גם אישור רשמי לפתיחה מחדש ואימות הגעה.

```yaml
status: complete
scope: "published Zippori #540 and linked guide #270; parent #93 remains open"
risk_level: L2
canonical_writer: "WordPress WP-CLI for listing status; authenticated WordPress REST for guide"
findings:
  - "Reviewed HTML links survived publication through WP-CLI."
sources:
  - {url: "https://www.parks.org.il/reserve-park/גן-לאומי-ציפורי/", checked_at: "2026-10-06"}
  - {url: "https://commons.wikimedia.org/wiki/File:PikiWiki_Israel_73447_zippori.jpg", checked_at: "2026-10-06"}
changes_made:
  - "Published listing #540."
  - "Linked guide #270 to #540 with a contextual heritage sentence."
unchanged:
  - {item: "Unverified city, street, hours, price, phone and rating", reason: "No verified canonical value"}
validation:
  - {check: "Canonical and public listing/guide/image/API", result: pass, evidence: "memory/data/2026-10-06-issue-93-zippori-publish-public-qa.json"}
  - {check: "Guarded rollback dry run", result: pass, evidence: "Rollback guard passed; no changes made"}
snapshot: "memory/data/2026-10-06-issue-93-zippori-publish-540-before.json and memory/data/2026-10-06-issue-93-zippori-publish-270-before.json"
rollback: "memory/scripts/issue93-zippori-publication-rollback.ps1 -Apply after checking guards"
blockers: []
remaining_risks:
  - "Other #93 candidates await publication review and relevant guide links."
issue_recommendation: keep-open
```
