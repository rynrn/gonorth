# #86 — תיקון guard לעמוד הקנרי 436

תאריך: 06.10.2026 (ישראל). תיקון ל[דוח הרחבת הכתבות](2026-10-06-issue-86-all-editorial-woo.md), שלא נדרס. הדוח הקודם ציין שה־guard לתוכן מסחרי חל גם על עמוד 436; בזמן חתימתו הקוד המפורסם החזיר `true` לעמוד הזה לפני בדיקת התוכן. הממצא תוקן כאן ובאתר.

בדיקת `post_content` של עמוד 436 לא מצאה אף אחד מסמני המסחר/טופס/iframe/script שה־guard מחפש. נשמר עותק `memory/work/issue86-functions-before-guard.php` ובשרת `/home/claude/issue86-functions-before-guard.php`. השינוי בקוד ב־`functions.php` העביר את בדיקת עמוד 436 לפני אותו guard שמופעל על 25 הכתבות: אם יתוסף בו תוכן מסחרי או meta opt-out, נכסי WooCommerce יישארו. אין שינוי ברשימת מזהי הכתבות או הנכסים.

`php -l`, בדיקת diff, השוואת snapshot לפני החלפה ו־`wp cache flush` עברו. גרסה חיה: SHA-256 ‏`a3ec4e487089355f50abbb858469312f037b33422a0d736a9d7d8b654b432e23`. רגרסיית Chrome חוזרת של 26/26 כתובות ב־390 וב־1280: כולן 200, ללא pageerror, גלישה, H1/footer חסרים או נכסי WooCommerce. תוצאות נפרדות: `memory/data/2026-10-06-issue-86-guardfix-live-390.json` ו־`memory/data/2026-10-06-issue-86-guardfix-live-1280.json`. סקריפט `memory/work/issue86-batch-rollback.ps1` עודכן ל־checksum החדש ו־`-CheckOnly` עבר; הוא מחזיר את גרסת הקנרי אם צריך.

שאר מגבלות #86 ללא שינוי: דחיסה/Cache-Control ב־Nginx נדחו עם עבודת ROOT, ונתוני שדה LCP/INP/CLS אינם זמינים.

```yaml
status: partial
scope: "content guard for page 436 and 25-post regression"
risk_level: L3
canonical_writer: "child-theme functions.php via SSH"
findings:
  - "Previous guard bypassed content checks on page 436"
  - "Page 436 now passes through the same content/meta guard as allowlisted posts"
sources:
  - {url: "memory/reports/2026-10-06-issue-86-all-editorial-woo.md", checked_at: "2026-10-06"}
  - {url: "https://gonorth.co.il/tours-kayaks-north-israel/", checked_at: "2026-10-06"}
changes_made:
  - "Scoped live guard condition fix"
  - "Updated checksum guard in rollback script"
  - "This dated correction report"
unchanged:
  - {item: "Nginx and assets outside allowlist", reason: "not in this change"}
validation:
  - {check: "php -l and guarded deployment", result: pass, evidence: "no syntax error; snapshot matched"}
  - {check: "Chrome 26/26 at 390 and 1280", result: pass, evidence: "guardfix-live JSON files"}
  - {check: "rollback -CheckOnly", result: pass, evidence: "Rollback guard passed; no files changed"}
snapshot: "memory/work/issue86-functions-before-guard.php"
rollback: "pwsh -NoProfile -File memory/work/issue86-batch-rollback.ps1"
blockers:
  - "Nginx static compression/Cache-Control remain deferred"
remaining_risks:
  - "Field Core Web Vitals unavailable"
issue_recommendation: keep-open
```
