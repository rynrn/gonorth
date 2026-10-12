# #93 — מדעטק פורסם וקושר ממדריך המשפחות

08.10.2026, שעון ישראל. סיכון L2. [כרטיס](https://github.com/rynrn/gonorth/issues/93). המשך ל[דוח הרשומה הממתינה](2026-10-06-issue-93-madatech-pending-0946.md).

לאחר אישור מנהל וביקורת תוכן עצמאית מעודכנת ל־08.10, רשומה #552 פורסמה באמצעות WordPress WP-CLI ללא שינוי בגוף התוכן שנבדק. [דף מדעטק](https://gonorth.co.il/places/מדעטק-המוזיאון-הלאומי-למדע-טכנולוג/) מבדיל בין הכניסה והקופות בשמריהו לוין 25 לבין החניה בבלפור 12, לפי [הנחיות ההגעה הרשמיות](https://www.madatech.org.il/main-info/arrival-ways/) ו[מידע המפעיל על החניה](https://www.madatech.org.il/main-info/kastah/). סמן OpenStreetMap הוא מיקום מייצג למתחם בלבד; `_gonorth_hide_driving_navigation=1` נשמר, ואין קישור נהיגה בדף הציבורי. שדות משתנים שלא אומתו נשארו ריקים.

צילום חזית #553, אושרי רייכר / אבני מורשת, 14.07.2026, מופיע כאלמנט `<img>` ראשי עם ALT מתאים. גוף הרשומה כולל [מקור Commons](https://commons.wikimedia.org/wiki/File:Madatech_The_Science_Museum_Haifa_Avney_Moreshet_9cf10973.jpg), [רישיון CC BY 2.5](https://creativecommons.org/licenses/by/2.5/) וקרדיט. התמונה אינה ראיה לתערוכות או למצב גישה נוכחי.

ב[מדריך האטרקציות לילדים בצפון](https://gonorth.co.il/atrakziot-leyeladim-batzafon/) (#270) טבלת היום הגשום כוללת כעת ארבע חלופות במקום שלוש; נוספה שורת מדעטק עם קישור לרשומה ולמפעיל, וצוין שהמידע עליה נבדק ב־08.10.2026. שלוש השורות הקודמות וקישוריהן נשמרו. הניסוח אינו מבטיח שכל ההגעה או הביקור מקורים.

בדיקות קנוניות וציבוריות: רשומה, מדריך, תמונה ו־API ציבורי מחזירים HTTP 200; תמונת הנושא מופיעה ב־HTML כ־`img`; קישורי המפעיל, OSM, Commons והרישיון וקרדיט היוצר קיימים; הסתייגות הסמן מופיעה וקישורי נהיגה נעדרים; שורת המדריך והקישור אומתו. [QA](../data/2026-10-08-issue-93-madatech-publish-public-qa.json). לפני שינוי: [רשומה](../data/2026-10-08-issue-93-madatech-publish-552-before.json), [GeoDirectory](../data/2026-10-08-issue-93-madatech-publish-552-before-geodir.json), [מדריך](../data/2026-10-08-issue-93-madatech-publish-270-before.json). מצב אחרי: [רשומה](../data/2026-10-08-issue-93-madatech-publish-552-after.json), [GeoDirectory](../data/2026-10-08-issue-93-madatech-publish-552-after-geodir.json), [מדריך](../data/2026-10-08-issue-93-madatech-publish-270-after.json). [שחזור מוגן](../scripts/issue93-madatech-publication-rollback.ps1) עבר dry-run; Apply לא הופעל.

#93 נשאר פתוח: שישה מועמדים נוספים ממתינים לביקורת פרסום וקישורי מדריכים. אדמית דורש אישור רשמי לפתיחה מחדש ואימות הגעה.

```yaml
status: complete
scope: "published Madatech #552 and linked guide #270; parent #93 remains open"
risk_level: L2
canonical_writer: "WordPress WP-CLI for listing status; authenticated WordPress REST for guide"
findings:
  - "Representative OSM pin must not be a driving destination; public page has no driving anchors."
sources:
  - {url: "https://www.madatech.org.il/main-info/arrival-ways/", checked_at: "2026-10-08"}
  - {url: "https://www.madatech.org.il/main-info/kastah/", checked_at: "2026-10-08"}
  - {url: "https://commons.wikimedia.org/wiki/File:Madatech_The_Science_Museum_Haifa_Avney_Moreshet_9cf10973.jpg", checked_at: "2026-10-08"}
changes_made:
  - "Published listing #552 and added a fourth, dated row in guide #270."
unchanged:
  - {item: "Unverified hours, price and rating", reason: "No verified canonical value"}
validation:
  - {check: "Canonical and public listing/guide/image/API and navigation", result: pass, evidence: "memory/data/2026-10-08-issue-93-madatech-publish-public-qa.json"}
  - {check: "Guarded rollback dry run", result: pass, evidence: "Rollback guard passed; no changes made"}
snapshot: "memory/data/2026-10-08-issue-93-madatech-publish-552-before.json and memory/data/2026-10-08-issue-93-madatech-publish-270-before.json"
rollback: "memory/scripts/issue93-madatech-publication-rollback.ps1 -Apply after checking guards"
blockers: []
remaining_risks:
  - "Other #93 candidates await review; venue details may change."
issue_recommendation: keep-open
```
