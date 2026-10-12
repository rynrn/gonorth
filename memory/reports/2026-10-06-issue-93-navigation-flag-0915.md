# #93 — הבחנה בין מקום ליעד נהיגה

תאריך: 06.10.2026, 09:15 ישראל. סיכון L2. שינוי חי ממוקד בתבנית הבת; #93 נשאר פתוח. מנהל המשימה אישר את התכנון בתנאי בדיקה ושחזור.

בקבצי התבנית הפעילים functions.php ו־single-gd_place.php נוסף תנאי לרשומה שמטא `_gonorth_hide_driving_navigation=1`. התנאי מסתיר כפתורי Waze; בקוד הפעולות הראשי מוסתר גם קישור Google Maps כדי לא להציע מסלול ל־GPS המקום במקום יעד הגעה לרכב. הודעה בעברית מסבירה שהמפה מציגה את מיקום המקום בלבד ושיש לבדוק הוראות הגעה. ללא הדגל ההתנהגות הקיימת נשמרת.

לפני הפריסה נשמרו עותקים ב־memory/work/issue93-navigation/ ומלאי ב־[קובץ המצב לפני שינוי](../data/2026-10-06-issue-93-navigation-before.json). ה־SHA-256 של הקבצים החיים אומת מול המקור לפני הכתיבה. PHP lint עבר על שני קובצי staging ועל שני הקבצים החיים. [שחזור מוגן](../scripts/issue93-navigation-rollback.ps1) עבר dry-run לפני ואחרי פריסה; `-Apply` לא הופעל.

בדיקת עמוד #494: לפני סימון זמני HTTP 200 עם Waze אחד ו־Google Maps אחד; עם הדגל HTTP 200, אפס משניהם והודעת הגעה גלויה; אחרי הסרת הדגל חזרו 1/1 וללא הודעה. הדגל הזמני הוסר. גם בדיקת #503 הוחזרה למצב קודם; שם התגלה קישור Waze ידני בגוף התוכן שאינו נשלט על ידי התבנית. לכן עודכן סקיל gn-place לדרוש בדיקת קישורים ידניים לפני שימוש בדגל. בדיקת מטא חוזרת לא החזירה ערך לשתי רשומות הבדיקה.

קובצי התבנית החיים: functions.php SHA-256 `6d603eee842e81a214c991a3de36a701facc4b885dda0a721c2fe9d610fbe7f1`; single-gd_place.php SHA-256 `b541f6cfcb293b09733622f5f0d08860fcbb431f86da62fbc42b4381efb7942e`. שחזור במקרה הצורך: `pwsh -NoProfile -File memory/scripts/issue93-navigation-rollback.ps1 -Apply`.

```yaml
status: partial
scope: "opt-in driving-navigation suppression in active child theme"
risk_level: L2
canonical_writer: "SSH child-theme files; WordPress CLI for temporary test flags"
findings:
  - "Template-generated driving links can misrepresent venue GPS"
  - "Manual editorial Waze links require separate review"
sources:
  - {url: "memory/reports/2026-10-06-issue-93-janco-dada-navigation-0853.md", checked_at: "2026-10-06"}
  - {url: "memory/reports/2026-10-06-issue-93-madatech-photo-arrival-0904.md", checked_at: "2026-10-06"}
changes_made:
  - "Opt-in theme flag and visitor notice"
  - "gn-place skill navigation guidance"
unchanged:
  - {item: "test listing meta", reason: "temporary flags removed"}
validation:
  - {check: "PHP lint", result: pass, evidence: "both live files passed"}
  - {check: "public flagged and restored #494", result: pass, evidence: "HTTP 200; Waze/Maps 1/1 -> 0/0 -> 1/1"}
  - {check: "rollback dry-run", result: pass, evidence: "guard accepted live hashes"}
snapshot: "memory/data/2026-10-06-issue-93-navigation-before.json"
rollback: "pwsh -NoProfile -File memory/scripts/issue93-navigation-rollback.ps1 -Apply"
blockers:
  - "Candidate-level arrival and photo review before pending listing"
remaining_risks:
  - "Editorial links are outside the template flag"
  - "single-gd_place.php is linted and guarded but not exercised on current public route"
issue_recommendation: keep-open
```
