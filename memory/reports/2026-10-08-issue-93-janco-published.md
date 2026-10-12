# #93 — מוזיאון ינקו־דאדא פורסם וקושר ממדריך המשפחות

08.10.2026, שעון ישראל. סיכון L2. [כרטיס](https://github.com/rynrn/gonorth/issues/93). המשך ל[דוח הרשומה הממתינה](2026-10-06-issue-93-janco-pending-0931.md).

לאחר אישור מנהל וביקורת תוכן עצמאית, רשומה #550 פורסמה ב־WordPress WP-CLI תוך שמירה על התוכן שנבדק. [דף מוזיאון ינקו־דאדא](https://gonorth.co.il/places/מוזיאון-ינקו־דאדא-עין-הוד/) מבוסס על [איקו״ם ישראל](https://www.icom.org.il/museum/1505): המוזיאון במרכז עין הוד, חניה בכניסה לכפר והליכה של כחמש דקות. שעות, מחירים וזמינות הסטודיו לא הוצגו כעובדות קבועות. אתר המוזיאון החזיר 429 בעת בדיקת המקור, ולא הוסק מכך שהוא סגור. סמן OSM מציין את המוזיאון, לא חניה או יעד נסיעה; דגל הסתרת ניווט ברכב נשמר ואין קישורי נהיגה בדף.

תמונת החזית #551 מ־2011 מיוחסת לד״ר אבישי טייכר / PikiWiki Israel. בגוף הדף מופיעים [קובץ Commons](https://commons.wikimedia.org/wiki/File:PikiWiki_Israel_13688_Janco_Dada_Museum_in_Ein_Hod.jpg), [CC BY 2.5](https://creativecommons.org/licenses/by/2.5/) וציון התאמות גודל ודחיסה. התמונה מוצגת כ־`img` עם ALT המזכיר במפורש 2011, ולא כראיה לחזית הנוכחית. ב[מדריך המשפחות](https://gonorth.co.il/atrakziot-leyeladim-batzafon/) (#270) נוסף קישור בסעיף היצירה עם הסתייגות על זמינות פעילות והתאמה לגיל ועל ההליכה החשופה מהחניה.

הרשומה, המדריך, התמונה וה־API הציבורי מחזירים HTTP 200. ב־HTML אומתו קישורי איקו״ם, Commons והרישיון, הקרדיט, הערת הצילום הישן, הנחיית החניה והסתייגות הסמן; קישורי נהיגה נעדרים. [QA](../data/2026-10-08-issue-93-janco-publish-public-qa.json). לפני שינוי נשמרו [רשומה](../data/2026-10-08-issue-93-janco-publish-550-before.json), [GeoDirectory](../data/2026-10-08-issue-93-janco-publish-550-before-geodir.json) ו[מדריך](../data/2026-10-08-issue-93-janco-publish-270-before.json); מצב אחרי ב[רשומה](../data/2026-10-08-issue-93-janco-publish-550-after.json), [GeoDirectory](../data/2026-10-08-issue-93-janco-publish-550-after-geodir.json) ו[מדריך](../data/2026-10-08-issue-93-janco-publish-270-after.json). [שחזור מוגן](../scripts/issue93-janco-publication-rollback.ps1) עבר dry-run; Apply לא הופעל.

#93 נשאר פתוח: ארבעה מועמדים אחרים ממתינים לביקורת פרסום וקישורי מדריכים. אדמית דורש אישור רשמי לפתיחה מחדש ואימות הגעה.

```yaml
status: complete
scope: "published Janco-Dada #550 and linked guide #270; parent #93 remains open"
risk_level: L2
canonical_writer: "WordPress WP-CLI for listing status; authenticated WordPress REST for guide"
findings:
  - "Historic 2011 facade image is labeled and does not establish current appearance."
sources:
  - {url: "https://www.icom.org.il/museum/1505", checked_at: "2026-10-08"}
  - {url: "https://commons.wikimedia.org/wiki/File:PikiWiki_Israel_13688_Janco_Dada_Museum_in_Ein_Hod.jpg", checked_at: "2026-10-08"}
changes_made:
  - "Published listing #550 and linked it from guide #270."
unchanged:
  - {item: "Unverified hours, prices, studio availability and parking GPS", reason: "Sources conflict or do not verify current values"}
validation:
  - {check: "Canonical and public listing/guide/image/API and navigation", result: pass, evidence: "memory/data/2026-10-08-issue-93-janco-publish-public-qa.json"}
  - {check: "Guarded rollback dry run", result: pass, evidence: "Rollback guard passed; no changes made"}
snapshot: "memory/data/2026-10-08-issue-93-janco-publish-550-before.json and memory/data/2026-10-08-issue-93-janco-publish-270-before.json"
rollback: "memory/scripts/issue93-janco-publication-rollback.ps1 -Apply after checking guards"
blockers: []
remaining_risks:
  - "Other #93 candidates await review; museum hours may change."
issue_recommendation: keep-open
```
