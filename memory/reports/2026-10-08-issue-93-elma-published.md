# #93 — מלון אלמא פורסם; קישור מדריך עדיין חסר

08.10.2026, שעון ישראל. סיכון L2. [כרטיס](https://github.com/rynrn/gonorth/issues/93). המשך ל[דוח הרשומה הממתינה](2026-10-06-issue-93-elma-pending-0847.md).

לאחר אישור מנהל וביקורת תוכן עצמאית, רשומה #548 פורסמה ב־WordPress WP-CLI ללא שינוי בגוף שנבדק. [דף מלון אלמא](https://gonorth.co.il/places/מלון-אלמא-זכרון-יעקב/) מבוסס על [אתר המפעיל](https://gordoniahotels.com/elma/) ועל [עמוד הקשר](https://gordoniahotels.com/צור-קשר/): מלון בוטיק ומרכז אמנויות ביאיר 1, זכרון יעקב. Waze מזוהה כיעד המלון, לא דלת כניסה או מקום חניה מובטח. מחירים, זמינות, אירועים, דירוג ונתונים דינמיים לא אומתו ולא הומצאו. בדיקת כפילות חוזרת מצאה רק רשומה אחת בשם אלמא; קטגוריית הלינה והמיקום נשמרו.

צילום מתחם וחצר #549 מ־2016 מיוחס ל־Elgaard, עם [מקור Commons](https://commons.wikimedia.org/wiki/File:Elma_Arts_Complex_and_Hotel_top.jpg), [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/) וציון התאמות גודל ודחיסה. הוא מוצג כ־`img` ראשי עם ALT המזכיר 2016 ואינו ראיה למצב כיום. דף המקום, התמונה וה־API הציבורי מחזירים 200; קישורי המפעיל, הקשר, Commons והרישיון, הקרדיט והסתייגות הניווט אומתו ב־HTML. Yoast מציג `index, follow` ו־`datePublished` בתאריך 08.10.2026. [QA](../data/2026-10-08-issue-93-elma-publish-public-qa.json).

לפני שינוי נשמרו [רשומה](../data/2026-10-08-issue-93-elma-publish-548-before.json), [GeoDirectory](../data/2026-10-08-issue-93-elma-publish-548-before-geodir.json) ו[מדיה](../data/2026-10-08-issue-93-elma-publish-549-before-media.json). מצב אחרי: [רשומה](../data/2026-10-08-issue-93-elma-publish-548-after.json) ו[GeoDirectory](../data/2026-10-08-issue-93-elma-publish-548-after-geodir.json). [שחזור מוגן](../scripts/issue93-elma-publication-rollback.ps1) עבר dry-run ללא Apply.

קישור מדריך עדיין חסר. [מדריך חוף דור](https://gonorth.co.il/dor-beach-guide/) (עמוד #146) מזכיר לינה בזכרון יעקב, אך כולל גם פרטי מרחק/שירותים שעשויים להשתנות; הוא דורש ביקורת תוכן ורענון לפני הוספת המלצה למלון. מדריך הלינה הכללי אינו ממוקד באזור. לכן מועמד #120 אינו משלים את תנאי קישור המדריך ב־#93. #93 נשאר פתוח גם לשני המועמדים האחרים שטרם פורסמו/הוכרעו.

```yaml
status: partial
scope: "published Elma #548; relevant guide link remains open"
risk_level: L2
canonical_writer: "WordPress WP-CLI for listing status"
findings:
  - "Dor Beach page has Zichron lodging context but needs separate content review."
sources:
  - {url: "https://gordoniahotels.com/elma/", checked_at: "2026-10-08"}
  - {url: "https://gordoniahotels.com/צור-קשר/", checked_at: "2026-10-08"}
  - {url: "https://commons.wikimedia.org/wiki/File:Elma_Arts_Complex_and_Hotel_top.jpg", checked_at: "2026-10-08"}
changes_made:
  - "Published listing #548."
unchanged:
  - {item: "Guide link", reason: "Candidate guide needs independent content review"}
  - {item: "Unverified dynamic fields", reason: "No canonical verified value"}
validation:
  - {check: "Canonical and public listing/image/API/SEO", result: pass, evidence: "memory/data/2026-10-08-issue-93-elma-publish-public-qa.json"}
  - {check: "Guarded rollback dry run", result: pass, evidence: "Rollback guard passed; no changes made"}
snapshot: "memory/data/2026-10-08-issue-93-elma-publish-548-before.json"
rollback: "memory/scripts/issue93-elma-publication-rollback.ps1 -Apply after checking guards"
blockers:
  - "Reviewed relevant guide link before #93 acceptance is complete for this candidate"
remaining_risks:
  - "Hotel availability and access may change."
issue_recommendation: keep-open
```
