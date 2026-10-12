# Issue #83 — תיקון הסכמה ישנה וביקורת עצמאית

תאריך: 06.10.2026  
כרטיס: https://github.com/rynrn/gonorth/issues/83  
דוח קודם: [04.10](2026-10-04-issue-83-consent-and-analytics.md)

## ממצא ותיקון

ביקורת עצמאית מצאה כי לפני 04.10 לחיצה על „הבנתי, סגור” שמרה `gn_cookie_ok=1`, ואילו קוד ההסכמה החדש פירש את אותו ערך כאישור מפורש למדידה. מבקר חוזר היה עלול להפעיל GTM/GA4 בלי בחירה חדשה. הביקורת הראשונה דחתה סגירת #83.

כל קוראי ההסכמה החיים משתמשים כעת ב־`gn_analytics_consent_v2`. אין הסבה אוטומטית מהמפתח הישן: מבקר עם `gn_cookie_ok=1` בלבד רואה את בחירת המדידה מחדש. בחירה חדשה שומרת `1` או `0` במפתח החדש ומסירה את הישן. גרסת התוסף עלתה ל־1.18.8 כדי לרענן את שני קובצי JavaScript. הבדיקה הקיימת של Preferred Sources עודכנה לאותו מפתח.

## פריסה, גיבוי ושחזור

רמת סיכון L3: זהו שער המדידה של האתר כולו. ההרשאה הקיימת היא להמשיך בהשלמת #83. מקור האמת הוא קובצי GoNorth child theme ורכיב `gonorth-place-data`; לא בוצעה כתיבה למסד נתונים. ששת הקבצים שונו רק אחרי עותק טרום שינוי ובדיקות תחביר. העותק המקומי ב־`memory/data/2026-10-06-issue-83-legacy-consent-prechange/`; עותק השרת ב־`/home/claude/issue83-legacy-v2/pre/`. קובצי הפריסה ב־`/home/claude/issue83-legacy-v2/stage/`; diff ב־`memory/data/2026-10-06-issue-83-legacy-consent.patch`. הפקודה לשחזור: `ssh gonorth "bash /home/claude/issue83-legacy-v2/issue83-legacy-v2-rollback.sh"`. הסקריפט מחזיר את ששת הקבצים ומרענן מטמונים; `bash -n` עבר. שחזור בפועל לא הופעל, כדי לא להחזיר את פער הפרטיות.

## אימות

- Chrome אמיתי, [שישה מצבים](../data/2026-10-06-issue-83-legacy-consent-browser.json): מפתח ישן בלבד, מבקר חדש, סירוב, אישור שמור, אישור במהלך ביקור, ביטול אישור. כל תשעת התנאים עברו. במצב המפתח הישן בלבד הבאנר הופיע, ולא יצאה בקשת Google גם אחרי 22 שניות. אישור טען GTM; לאחר ביטול ורענון אין סקריפט GTM או Preferred Sources חדש. בקשה אחת שנצפתה אחרי לחיצה על ביטול עשויה להיות השלמה של בקשה שהתחילה לפני הרענון; אין טענה שביטול מוחק בקשות קודמות.
- [רגרסיית מלאי](../data/2026-10-06-issue-83-full-inventory-regression.json): 197/197 כתובות מפורסמות החזירו 200 עם כפתור הסירוב, אירוע ההסכמה ומפתח ההסכמה החדש. זו בדיקת טעינה וממשק, לא ניטור רשת פרטני בכל 197 הדפים.
- `php -l` עבר על שלושת קובצי PHP החדשים; `node --check` עבר על שני קובצי JavaScript. בשרת `wp eval-file .../tests/preferred-source.php --path=/var/www/gonorth --allow-root` עבר עם `consent_gate=1`. גרסת התוסף החיה היא 1.18.8.
- הסוקר העצמאי דחה בתחילה את #83 עקב פער ה־legacy, בדק מחדש את ה־diff, הקוד החי, השחזור, ראיית הדפדפן והמלאי, ואישר את התיקון לאחר ריצת מבחן Preferred Sources המעודכן. לא נבדקו הגדרות חשבון GTM/GA4; #104 ממשיך לעקוב אחר גלישת 10px של Google SDK אחרי אישור.

## מקורות

- עותקי הקוד החיים ועותקי טרום השינוי, נבדקו ב־06.10.2026.
- https://gonorth.co.il/yizreel-valley-day-trip/ — בדיקת רשת בדפדפן ב־06.10.2026.
- https://gonorth.co.il/ — בדיקת ממשק ב־06.10.2026.

```yaml
status: complete
scope: "מיגרציית הסכמה ישנה ב-GTM, אנליטיקה, Preferred Sources ו-197 דפים"
risk_level: L3
canonical_writer: "קובצי child theme ורכיב gonorth-place-data"
findings:
  - "gn_cookie_ok=1 היה אישור הודעת עוגיות ישנה, לא הסכמה מפורשת למדידה"
sources:
  - {url: "memory/data/2026-10-06-issue-83-legacy-consent-prechange/", checked_at: "2026-10-06"}
  - {url: "https://gonorth.co.il/yizreel-valley-day-trip/", checked_at: "2026-10-06"}
changes_made:
  - "מפתח הסכמה חדש ועקבי בכל קוראי המדידה"
  - "רענון גרסת התוסף ועדכון בדיקת Preferred Sources"
unchanged:
  - {item: "הגדרות תגיות בחשבון GTM/GA4", reason: "אינן נדרשות לשער המדידה בצד האתר ולא נבדקו"}
validation:
  - {check: "שישה מצבי Chrome", result: pass, evidence: "memory/data/2026-10-06-issue-83-legacy-consent-browser.json"}
  - {check: "מלאי ציבורי", result: pass, evidence: "197/197 HTTP 200 עם ממשק חדש"}
  - {check: "ביקורת קוד עצמאית", result: pass, evidence: "בדיקת security-agent לאחר תיקון legacy ומבחן Preferred Sources"}
snapshot: "memory/data/2026-10-06-issue-83-legacy-consent-prechange/"
rollback: "ssh gonorth \"bash /home/claude/issue83-legacy-v2/issue83-legacy-v2-rollback.sh\""
blockers: []
remaining_risks:
  - "Google SDK יוצר גלישה אופקית 10px במובייל לאחר אישור; מטופל ב-#104"
issue_recommendation: close
```
