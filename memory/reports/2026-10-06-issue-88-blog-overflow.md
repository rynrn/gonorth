# #88 — תיקון גלילה אופקית בארכיון הבלוג

תאריך: 06.10.2026. משימת־אב #74. רמת סיכון L2; כותב קנוני: קובצי child theme `gonorth.css` ו־`functions.php` דרך SSH.

בבדיקת Chrome נמצא כי ב־`/blog/` רוחב המסמך היה 1296px בחלון 1280px ו־1456px בחלון 1440px. `.ast-row` של Astra קיבל `margin-inline:-16px` בלי ריווח מתאים ברכיב העוטף של תבנית הבלוג; הכרטיסים בשולי השורה גלשו מחוץ למסך. ב־320, 390 ו־768 לא הייתה גלילה. בדיקת הדמיה עם כלל CSS ממוקד ביטלה את הגלישה בדסקטופ ולא שינתה את רוחב הפריסה במובייל.

נוסף ל־`gonorth.css` כלל יחיד המוגבל ל־`body.blog .site-main > .ast-row`: `margin-inline:0 !important`, עם כיוון RTL ויישור לימין. גרסת stylesheet ב־`functions.php` עלתה מ־2.9.12 ל־2.9.13. ה־`!important` נדרש כי כלל Astra המקורי גבר על כלל רגיל בהדמיה. לא שונו תבניות אחרות.

לפני הפריסה נשמרו שני קובצי המקור מקומית ב־`memory/work/blog-overflow-before.css` וב־`memory/work/blog-overflow-before-functions.php`, ובשרת תחת `/home/claude/issue88-blog-overflow-before*`. סקריפט הפריסה בדק את ארבעת ה־SHA-256, הריץ `php -l`, העתיק את שני הקבצים וניקה WordPress object cache. סקריפט שחזור `memory/work/issue88-blog-overflow-rollback.sh` נמצא גם בשרת; `--check-only` עבר בלי שינוי.

בדיקה ציבורית אחרי הפריסה: שלושת עמודי הבלוג `/blog/`, `/blog/page/2/`, `/blog/page/3/` נבדקו בחמישה רוחבים — 15/15 החזירו 200, H1/footer קיימים, ללא pageerror, ורוחב המסמך זהה לרוחב החלון. מספר הכרטיסים: 10, 10 ו־5. דפי הבית, ארכיון המקומות וכתבת חנוכה נבדקו ב־390/1280 — 6/6 החזירו 200 ללא גלילה או שגיאות. בדיקה חזותית של שורת הכרטיסים ב־1280 הראתה שהכרטיסים נשארו בשלוש עמודות ורק נכנסו לגבולות החלון. נתונים: `memory/data/2026-10-06-blog-overflow-baseline.json`, `memory/data/2026-10-06-blog-overflow-simulation-important.json`, `memory/data/2026-10-06-issue-88-blog-overflow-live.json`.

SHA-256 חי לאחר השינוי: CSS `a138c9a2d0ef41077a797df8513684f4148ad65163755f912829204cd7a4490c`; PHP `aeb1a129ab3b004275aaa257e8f141dfdeb594e92bfdb7965329797e9bb6fd3f`. #88 נשאר פתוח למשימת איתור פעילות וניווט עם משתמשים אמיתיים ולבדיקת זום ידנית.

```yaml
status: partial
scope: "Blog archive grid on three paginated URLs"
risk_level: L2
canonical_writer: "GoNorth child-theme CSS and functions.php via SSH"
findings:
  - "Astra row negative margins caused 16px desktop horizontal overflow"
sources:
  - {url: "https://gonorth.co.il/blog/", checked_at: "2026-10-06"}
  - {url: "https://gonorth.co.il/blog/page/2/", checked_at: "2026-10-06"}
  - {url: "https://gonorth.co.il/blog/page/3/", checked_at: "2026-10-06"}
changes_made:
  - "Scoped RTL-aware row margin override"
  - "Bumped stylesheet version to 2.9.13"
unchanged:
  - {item: "Other templates", reason: "CSS selector is scoped to the blog archive"}
validation:
  - {check: "PHP lint and live checksums", result: pass, evidence: "server output and hashes above"}
  - {check: "15 blog viewport cases and 6 control cases", result: pass, evidence: "memory/data/2026-10-06-issue-88-blog-overflow-live.json"}
  - {check: "Rollback --check-only", result: pass, evidence: "guard passed; no files changed"}
snapshot: "memory/work/blog-overflow-before.css and memory/work/blog-overflow-before-functions.php"
rollback: "ssh gonorth 'bash /home/claude/issue88-blog-overflow-rollback.sh'"
blockers: []
remaining_risks:
  - "Real-user navigation task and manual browser zoom remain unverified"
issue_recommendation: keep-open
```

