# #93 — בדיקת מקורות צילום וכניסה לשני המועמדים הראשונים

תאריך: 06.10.2026. כרטיס: https://github.com/rynrn/gonorth/issues/93. המשך לדוח `2026-10-06-issue-93-first-two-candidates.md`. רמת סיכון L0; לא שונה האתר או תור המקומות.

## מדעטק, חיפה
[דף ההגעה הרשמי](https://www.madatech.org.il/main-info/arrival-ways/) ו[דף ההכנה לביקור](https://www.madatech.org.il/main-info/kastah/) מאשרים שהכניסה והקופות הן מרחוב שמריהו לוין 25, דרך חזית הבניין ההיסטורי. אי אפשר להיכנס מהחניה בבלפור. דף ההגעה גם מזהיר שיישומי ניווט פועלים חלקית וממליץ לתכנן מסלול מראש. קישור Waze שבדף מכיל 32.80955060,34.99568310, אך אינו מוכיח שזו נקודת דלת הכניסה. לא נשתמש בו כשדה GPS עד אימות נקודתי.

[צילום מועמד ממדעטק](https://commons.wikimedia.org/wiki/File:PikiWiki_Israel_756_The_Haifa_National_Museum_of_Science_%D7%9E%D7%93%D7%A2%D7%98%D7%A7.JPG): זאב זמיר / PikiWiki Israel, [CC BY 2.5](https://creativecommons.org/licenses/by/2.5/), 600×450, משנת 2009. מתאר כניסה לתערוכת דינוזאורים מכיוון החניה. יש לבדוק חזותית אם מתאים לרשומה עכשווית; המיקום והגיל עשויים להטעות ולכן זה מועמד בלבד. [צילום חלופי של מבנה השייך למתחם](https://commons.wikimedia.org/wiki/File:Madatech-Workshop_building_2.jpg): McKaby, CC0, 2025, מתאר לוח זיכרון על חזית מבנה המעבדות; פחות מתאים לייצוג חוויית ביקור.

## אולמות האבירים, עכו
[דף מרכז המבקרים הרשמי](https://www.akko.org.il/attraction/the-visitors-center/) ו[השאלות הנפוצות](https://www.akko.org.il/faq/) מאשרים שהביקור מתחיל במרכז המבקרים בגן הקסום, ויצמן 1, עכו. קישור Google בדף מצביע לאזור הגן, אך אינו מזהה את דלת מרכז המבקרים בדיוק הנדרש לשדה GPS. אין להשתמש בקואורדינטת צילום או מרכז המתחם כתחליף.

[צילום מועמד של האולמות](https://commons.wikimedia.org/wiki/File:Acre_-_Knights_Halls1.jpg): McKaby, [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/), 2018. [צילום מועמד של אזור הכניסה](https://commons.wikimedia.org/wiki/File:Acre_-_Akko_2_-_Knights_Halls_-_entrance_(6658880249).jpg): Israel Tourism, [CC BY 2.0](https://creativecommons.org/licenses/by/2.0/), 2007. מקור, יוצר ורישיון מופיעים בעמודי הקבצים; עדיין נדרשת בדיקה חזותית של ההתאמה והעדכניות. נתוני EXIF של צילום הכניסה אינם תואמים את הכתובת הרשמית ואין להשתמש בהם לניווט.

## החלטה והמשך
שני המקומות נשארים בתור כ״ממתין״. לפני יצירת רשומת gd_place יש לאמת GPS לדלת המבקרים, לבחור צילום לאחר בדיקה חזותית, להכין ייחוס/ALT, לבדוק כפילות מחדש, להעביר עובדות דינמיות דרך place-facts, להריץ `tools/validate-place-records.py` ולבצע תהליך gn-place. רשומה חדשה תישאר pending. תנאי הקבלה של #93 עדיין לא הושלמו.

```yaml
status: partial
scope: "מקורות כניסה ותמונות מועמדות למדעטק ולאולמות האבירים"
risk_level: L0
canonical_writer: "none"
findings:
  - "נמצאו מקורות רשמיים לכתובות כניסה אך לא GPS מאומת לדלת"
  - "נמצאו תמונות ברישיון מפורש כמועמדות, טרם נבחרו חזותית"
sources:
  - {url: "https://www.madatech.org.il/main-info/arrival-ways/", checked_at: "2026-10-06"}
  - {url: "https://www.akko.org.il/attraction/the-visitors-center/", checked_at: "2026-10-06"}
  - {url: "https://commons.wikimedia.org/wiki/File:Acre_-_Knights_Halls1.jpg", checked_at: "2026-10-06"}
changes_made: []
unchanged:
  - {item: "WordPress and places-to-add.md", reason: "GPS entrance and final photo still unverified"}
validation:
  - {check: "official entrance source and Commons license pages", result: pass, evidence: "links above"}
snapshot: "not-required"
rollback: "no live or queue change"
blockers:
  - "GPS exact visitor entrance and final visual media selection before preparing a pending record"
remaining_risks:
  - "Navigation pin may point to parking or center of complex"
issue_recommendation: keep-open
```
