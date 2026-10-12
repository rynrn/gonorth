# #93 — אולמות האבירים בעכו פורסמו וקושרו ממדריך המשפחות

06.10.2026, שעון ישראל. סיכון L2. [כרטיס](https://github.com/rynrn/gonorth/issues/93). המשך ל[דוח הרשומה הממתינה](2026-10-06-issue-93-knights-pending-0823.md).

לאחר אישור מנהל וביקורת תוכן עצמאית, רשומה #544 פורסמה באמצעות WordPress WP-CLI תוך שמירה על התוכן המקורי והקישורים. [דף אולמות האבירים](https://gonorth.co.il/places/אולמות-האבירים-המצודה-ההוספיטלרית-ב/) מתאר את המצודה ההוספיטלרית ומפנה ל[אתר המפעיל](https://www.akko.org.il/attraction/the-knights-halls/) ול[הנחיות ההגעה](https://www.akko.org.il/faq/). כתובת מרכז המבקרים ויצמן 1 נתמכת במקור; יעד Waze מוצג כיעד כתובת בלבד, ללא הבטחה לדלת כניסה או חניה. עיר עכו נשמרה; רחוב ונתונים דינמיים שלא אומתו נשארו ריקים.

צילום פנים מ־2013 #545 מיוחס ל־Zapi81 עם [מקור Commons](https://commons.wikimedia.org/wiki/File:%D7%90%D7%95%D7%9C%D7%9E%D7%95%D7%AA_%D7%94%D7%90%D7%91%D7%99%D7%A8%D7%99%D7%9D_%D7%A2%D7%9B%D7%95.jpg), [CC BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0/) וציון התאמות גודל ודחיסה. ALT התמונה מציין שזה צילום משנת 2013, ואינו טוען למצב הנוכחי. ב[מדריך המשפחות](https://gonorth.co.il/atrakziot-leyeladim-batzafon/) (#270) האזכור הקיים בסעיף עכו נקשר לכתובת הרשומה שנוצרה בפועל. קישורי ציפורי ועין אפק נשמרו.

הרשומה, המדריך, התמונה וה־API הציבורי מחזירים HTTP 200. ב־HTML נמצאו קישורי המפעיל וה־FAQ, מקור התמונה, הרישיון, הקרדיט והסתייגות הניווט. קישור המדריך מאומת. [QA](../data/2026-10-06-issue-93-knights-publish-public-qa.json). לפני השינוי נשמרו [רשומה](../data/2026-10-06-issue-93-knights-publish-544-before.json), [GeoDirectory](../data/2026-10-06-issue-93-knights-publish-544-before-geodir.json) ו[מדריך](../data/2026-10-06-issue-93-knights-publish-270-before.json); מצב אחרי נשמר ב[רשומה](../data/2026-10-06-issue-93-knights-publish-544-after.json), [GeoDirectory](../data/2026-10-06-issue-93-knights-publish-544-after-geodir.json) ו[מדריך](../data/2026-10-06-issue-93-knights-publish-270-after.json). [שחזור מוגן](../scripts/issue93-knights-publication-rollback.ps1) עבר dry-run, ללא Apply.

#93 נשאר פתוח: שבעה מועמדים ממתינים לביקורת פרסום וקישורי מדריכים. אדמית דורש אישור רשמי לפתיחה מחדש ואימות הגעה.

```yaml
status: complete
scope: "published Knights Halls #544 and linked guide #270; parent #93 remains open"
risk_level: L2
canonical_writer: "WordPress WP-CLI for listing status; authenticated WordPress REST for guide"
findings:
  - "Actual permalink differs from a guessed slug; guide uses the canonical URL."
sources:
  - {url: "https://www.akko.org.il/attraction/the-knights-halls/", checked_at: "2026-10-06"}
  - {url: "https://www.akko.org.il/faq/", checked_at: "2026-10-06"}
  - {url: "https://commons.wikimedia.org/wiki/File:אולמות_האבירים_עכו.jpg", checked_at: "2026-10-06"}
changes_made:
  - "Published listing #544 and linked existing mention in guide #270."
unchanged:
  - {item: "Unverified street, hours, price, phone and rating", reason: "No verified canonical value"}
validation:
  - {check: "Canonical and public listing/guide/image/API", result: pass, evidence: "memory/data/2026-10-06-issue-93-knights-publish-public-qa.json"}
  - {check: "Guarded rollback dry run", result: pass, evidence: "Rollback guard passed; no changes made"}
snapshot: "memory/data/2026-10-06-issue-93-knights-publish-544-before.json and memory/data/2026-10-06-issue-93-knights-publish-270-before.json"
rollback: "memory/scripts/issue93-knights-publication-rollback.ps1 -Apply after checking guards"
blockers: []
remaining_risks:
  - "Other #93 candidates await review."
issue_recommendation: keep-open
```
