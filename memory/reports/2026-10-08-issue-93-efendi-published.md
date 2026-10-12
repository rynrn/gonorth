# #93 — מלון האפנדי פורסם; קישור מדריך עדיין חסר

08.10.2026, שעון ישראל. סיכון L2. [כרטיס](https://github.com/rynrn/gonorth/issues/93). המשך ל[דוח הרשומה הממתינה](2026-10-06-issue-93-efendi-pending-0833.md).

לאחר אישור מנהל וביקורת תוכן עצמאית, רשומה #546 פורסמה באמצעות WordPress WP-CLI תוך שמירה על הגוף שנבדק. [דף מלון האפנדי](https://gonorth.co.il/places/מלון-האפנדי-עכו/) מבוסס על [אתר המלון](https://efendi-hotel.co.il/) ו[הוראות ההגעה](https://efendi-hotel.co.il/דרכי-הגעה-למלון/): רחוב לואי התשיעי, חניה ציבורית בסביבה והורדת חפצים בכניסה לסמטה. Waze מוצג כיעד המלון, לא כדלת נגישה ברכב או כחניה מובטחת. מחירים, זמינות, שעות, דירוג ופרטי קשר שלא עברו אימות דינמי לא הומצאו. בדיקת כפילות חוזרת מצאה רק את הרשומה הזו בשם האפנדי. קטגוריית הלינה `accommodation` אומתה קנונית.

צילום כניסה ושלט #547 מ־2012 מיוחס ל־Yuval Y עם [קישור Commons](https://commons.wikimedia.org/wiki/File:Efendi_Hotel,_Acre,_Israel_-_01.jpg), [CC BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0/) וציון התאמות. הוא מוצג כ־`img` ראשי עם ALT המזכיר את שנת הצילום; הוא אינו מוצג כראיה למצב הכניסה הנוכחי.

דף המקום, התמונה וה־API הציבורי מחזירים 200. קישורי המפעיל, ההגעה, Commons והרישיון, הקרדיט והסתייגות Waze אומתו ב־HTML. Yoast מציג `index, follow` ו־`datePublished` בתאריך 08.10.2026; [פלט QA](../data/2026-10-08-issue-93-efendi-publish-public-qa.json). מצב קודם נשמר ב[רשומה](../data/2026-10-08-issue-93-efendi-publish-546-before.json), [GeoDirectory](../data/2026-10-08-issue-93-efendi-publish-546-before-geodir.json) ו[מדיה](../data/2026-10-08-issue-93-efendi-publish-547-before-media.json); מצב אחרי ב[רשומה](../data/2026-10-08-issue-93-efendi-publish-546-after.json) ו[GeoDirectory](../data/2026-10-08-issue-93-efendi-publish-546-after-geodir.json). [שחזור מוגן](../scripts/issue93-efendi-publication-rollback.ps1) עבר dry-run; Apply לא הופעל.

קישור מדריך עדיין חסר: מדריך הילדים #270 אינו רלוונטי למלון, ומדריך הלינה הכללי #97 מכיל מידע ישן שדורש ביקורת תוכן ורענון נפרדים לפני הוספת המלצה. לכן אין לסמן את מועמד #119 כהשלמה מלאה של תנאי הקבלה ב־#93. אפשר לשקול מדריך לינה בעכו לאחר מחקר ותוכן מאושרים. #93 נשאר פתוח גם לשלושת המועמדים האחרים הממתינים לפרסום או לסגירת חסם.

```yaml
status: partial
scope: "published Efendi #546; relevant guide link remains open"
risk_level: L2
canonical_writer: "WordPress WP-CLI for listing status"
findings:
  - "Existing family guide is irrelevant; general lodging guide requires a separate content review."
sources:
  - {url: "https://efendi-hotel.co.il/", checked_at: "2026-10-08"}
  - {url: "https://efendi-hotel.co.il/דרכי-הגעה-למלון/", checked_at: "2026-10-08"}
  - {url: "https://commons.wikimedia.org/wiki/File:Efendi_Hotel,_Acre,_Israel_-_01.jpg", checked_at: "2026-10-08"}
changes_made:
  - "Published listing #546."
unchanged:
  - {item: "Guide link", reason: "No reviewed relevant guide is ready"}
  - {item: "Unverified dynamic fields", reason: "No canonical verified value"}
validation:
  - {check: "Canonical and public listing/image/API/SEO", result: pass, evidence: "memory/data/2026-10-08-issue-93-efendi-publish-public-qa.json"}
  - {check: "Guarded rollback dry run", result: pass, evidence: "Rollback guard passed; no changes made"}
snapshot: "memory/data/2026-10-08-issue-93-efendi-publish-546-before.json"
rollback: "memory/scripts/issue93-efendi-publication-rollback.ps1 -Apply after checking guards"
blockers:
  - "Reviewed relevant lodging guide before #93 acceptance is complete for this candidate"
remaining_risks:
  - "Hotel availability and access may change."
issue_recommendation: keep-open
```
