# #93 — שמורת טבע נחל תנינים פורסמה וקושרה ממדריך מסלולי המים

06.10.2026, שעון ישראל. סיכון L2. [כרטיס](https://github.com/rynrn/gonorth/issues/93). המשך ל[דוח הרשומה הממתינה](2026-10-06-issue-93-taninim-pending-0640.md).

לאחר אישור מנהל וביקורת תוכן עצמאית, רשומה #538 פורסמה באמצעות WordPress WP-CLI תוך שמירה מדויקת על גוף התוכן והקישורים שנבדקו. [דף שמורת טבע נחל תנינים](https://gonorth.co.il/places/שמורת-טבע-נחל-תנינים/) מציג מידע על הסכר הרומי, אמות המים וטחנות הקמח לפי [רשות הטבע והגנים](https://www.parks.org.il/reserve-park/שמורת-טבע-נחל-תנינים/). מסלול המים באמה הבלתי גמורה מוצג בכפוף להנחיות משתנות. Waze הוא יעד בשם השמורה ולא שער הולכי רגל מאומת. עיר, רחוב ועובדות דינמיות שלא אומתו נשארו ריקים.

תמונה #539 מציגה גלגל משוחזר. ה־ALT אומר זאת במפורש. בגוף הרשומה מופיעים קרדיט אלישבע ורנר-רייז / PikiWiki Israel, [מקור התמונה](https://commons.wikimedia.org/wiki/File:PikiWiki_Israel_48361_Taninim_River_Reserve.jpg), [רישיון CC BY 2.5](https://creativecommons.org/licenses/by/2.5/) וציון התאמות גודל ודחיסה. התמונה אינה מציגה מצב פעולה נוכחי.

ל[מדריך מסלולי המים](https://gonorth.co.il/maslulei-mayim-batzafon/) (#262) נוסף פריט קצר תחת „עוד אפשרויות לטיול מים קצר”, עם קישור למקום ולמפעיל הרשמי. שבעת המסלולים הממוספרים והכותרת לא שונו. הרשומה, המדריך, התמונה וה־API הציבורי מחזירים HTTP 200. ב־HTML אומתו קישורי המפעיל, Commons והרישיון, קרדיט היוצרת והסתייגות הניווט. [פלט QA](../data/2026-10-06-issue-93-taninim-publish-public-qa.json).

לפני שינוי נשמרו [רשומה](../data/2026-10-06-issue-93-taninim-publish-538-before.json), [GeoDirectory](../data/2026-10-06-issue-93-taninim-publish-538-before-geodir.json) ו[מדריך](../data/2026-10-06-issue-93-taninim-publish-262-before.json). מצב אחרי: [רשומה](../data/2026-10-06-issue-93-taninim-publish-538-after.json), [GeoDirectory](../data/2026-10-06-issue-93-taninim-publish-538-after-geodir.json) ו[מדריך](../data/2026-10-06-issue-93-taninim-publish-262-after.json). [שחזור מוגן](../scripts/issue93-taninim-publication-rollback.ps1) עבר dry-run; Apply לא הופעל.

#93 נשאר פתוח: שמונה מועמדים נוספים ממתינים לביקורת פרסום וקישורי מדריכים. אדמית דורש אישור רשמי לפתיחה מחדש ואימות הגעה.

```yaml
status: complete
scope: "published Nahal Taninim #538 and linked guide #262; parent #93 remains open"
risk_level: L2
canonical_writer: "WordPress WP-CLI for listing status; authenticated WordPress REST for guide"
findings:
  - "Selected Commons photo shows a reconstructed wheel and ALT identifies it correctly."
sources:
  - {url: "https://www.parks.org.il/reserve-park/שמורת-טבע-נחל-תנינים/", checked_at: "2026-10-06"}
  - {url: "https://commons.wikimedia.org/wiki/File:PikiWiki_Israel_48361_Taninim_River_Reserve.jpg", checked_at: "2026-10-06"}
changes_made:
  - "Published listing #538 and linked it from guide #262."
unchanged:
  - {item: "Unverified city, street, hours, price, phone and rating", reason: "No verified canonical value"}
validation:
  - {check: "Canonical and public listing/guide/image/API", result: pass, evidence: "memory/data/2026-10-06-issue-93-taninim-publish-public-qa.json"}
  - {check: "Guarded rollback dry run", result: pass, evidence: "Rollback guard passed; no changes made"}
snapshot: "memory/data/2026-10-06-issue-93-taninim-publish-538-before.json and memory/data/2026-10-06-issue-93-taninim-publish-262-before.json"
rollback: "memory/scripts/issue93-taninim-publication-rollback.ps1 -Apply after checking guards"
blockers: []
remaining_risks:
  - "Other #93 candidates await review; wet-route conditions may change."
issue_recommendation: keep-open
```
