# Issue #104 — גלישה אופקית אחרי Google Preferred Sources

תאריך: 06.10.2026  
כרטיס: https://github.com/rynrn/gonorth/issues/104

לאחר הסכמה, Google Preferred Sources מוסיף iframe שירות מוסתר עם `left:-10px !important`, ולכן ברוחבי 375/390/768 רוחב המסמך גדל ב־10 פיקסלים. מקור הממצא ותיעוד הניסוי הראשוני ב[דוח #83](2026-10-04-issue-83-consent-and-analytics.md). לפני שינוי נמדדו בדפדפן 385/400/778 פיקסלים בהתאמה. CSS רגיל על ה־iframe לא גבר על הסגנון inline של Google.

נוסף כלל CSS מוגבל ל־`max-width:768px` שפועל רק כש־iframe השירות המדויק קיים: `html:has(iframe[src^="https://news.google.com/swg/ui/v1/serviceiframe"]) { overflow-x: clip; }`. אין עריכה של קוד Google או שינוי בהתנהגות ההסכמה. סגנון ה־iframe נשאר בידי Google. גרסת CSS עודכנה מ־2.9.7 ל־2.9.8 כדי למנוע מטמון ישן.

רמת סיכון L2; הכותב הקנוני הוא GoNorth child theme. לפני השינוי נשמרו `gonorth.css` ו־`functions.php` ב־`memory/data/2026-10-06-issue-104-prechange/` וגם בשרת `/home/claude/issue104/pre/`. [diff](../data/2026-10-06-issue-104.patch). דרך שחזור מוכנה: `ssh gonorth "bash /home/claude/issue104/issue104-rollback.sh"`. הסקריפט עבר `bash -n`; לא הופעל כדי לא לבטל את התיקון.

[בדיקת Chrome](../data/2026-10-06-issue-104-browser.json) אחרי השינוי עברה בשישה מצבים: 375/390/768 עם הסכמה או סירוב. בכל מצב `scrollWidth=clientWidth`. באישור ה־SDK וה־iframe נטענים והכלל המצומצם פעיל; בסירוב אינם נטענים והכלל אינו פעיל. קישור ה־CTA הרשמי נשאר בדף, גרסת CSS הציבורית היא 2.9.8, ולא היו שגיאות JavaScript. `php -l` עבר על `functions.php` לפני פריסה.

```yaml
status: complete
scope: "גלישת SDK Preferred Sources במובייל, שישה מצבי בדיקה"
risk_level: L2
canonical_writer: "GoNorth child theme CSS וגרסת enqueue"
findings:
  - "iframe מוסתר של Google הוסיף 10px לרוחב המסמך"
sources:
  - {url: "memory/reports/2026-10-04-issue-83-consent-and-analytics.md", checked_at: "2026-10-06"}
  - {url: "https://gonorth.co.il/yizreel-valley-day-trip/", checked_at: "2026-10-06"}
changes_made:
  - "CSS מותנה בנוכחות iframe שירות של Google במסכים עד 768px"
  - "גרסת CSS 2.9.8"
unchanged:
  - {item: "SDK וקוד Google", reason: "הפתרון מקומי ב-CSS של האתר"}
validation:
  - {check: "Chrome 375/390/768, אישור/סירוב", result: pass, evidence: "memory/data/2026-10-06-issue-104-browser.json"}
  - {check: "PHP lint וקישור CTA", result: pass, evidence: "ללא שגיאות, קישור רשמי נשמר"}
snapshot: "memory/data/2026-10-06-issue-104-prechange/"
rollback: "ssh gonorth \"bash /home/claude/issue104/issue104-rollback.sh\""
blockers: []
remaining_risks: []
issue_recommendation: close
```
