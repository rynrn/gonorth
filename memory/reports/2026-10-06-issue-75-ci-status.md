# #75 — מצב בדיקת הסודות ב־GitHub

תאריך: 06.10.2026 (ישראל). בדיקה מקומית בלבד; לא נשלחו אישורים, קוד או ממצאי נתיבים למאגר המרוחק.

`python tools/scan-secrets.py` עבר על קובצי העבודה הנוכחיים. הקבצים `.github/workflows/secret-scan.yml` ו־`tools/scan-secrets.py` קיימים מקומית, אך אינם ב־Git index; `gh api repos/rynrn/gonorth/actions/workflows` החזיר `total_count: 0`. לכן אין כרגע בדיקת סודות פעילה ב־GitHub. דוח [סריקת ההיסטוריה](2026-10-06-issue-75-git-history-scan-0357.md) מתעד דפוסים ב־HEAD ובהיסטוריה, בלי ערכים.

אין להעלות את הניקוי כ־PR לפני סבב החלפה וביטול של האישורים החשודים: גרסת המקור וה־diff עלולים לחשוף מחרוזות ישנות. בעל החשבון צריך לבדוק את האינטגרציות בחנות האישורים הפרטית, ליצור חלופות מצומצמות, לבדוק אותן ואז לבטל את הישנות. לאחר מכן ניתן להעלות את הקבצים הנקיים עם ה־workflow, לוודא ש־CI רץ ב־PR ולתאם בנפרד טיפול בהיסטוריה. אין לבדוק תוקף אישור באמצעות ניסיון שימוש בו.

```yaml
status: partial
scope: "local tracked working tree, Git index, and GitHub Actions workflow inventory"
risk_level: L0
canonical_writer: "none"
findings:
  - "Local scanner passes against the current working tree"
  - "Scanner and workflow exist locally but are untracked"
  - "GitHub reports zero Actions workflows"
sources:
  - {url: "tools/scan-secrets.py", checked_at: "2026-10-06"}
  - {url: ".github/workflows/secret-scan.yml", checked_at: "2026-10-06"}
  - {url: "docs/credential-management.md", checked_at: "2026-10-06"}
  - {url: "https://api.github.com/repos/rynrn/gonorth/actions/workflows", checked_at: "2026-10-06"}
changes_made:
  - "This local report and memory status only"
unchanged:
  - {item: "GitHub repository and credentials", reason: "Credential replacement precedes publication"}
validation:
  - {check: "python tools/scan-secrets.py", result: pass, evidence: "No tracked credential patterns detected"}
  - {check: "git ls-files -- .github/workflows/secret-scan.yml tools/scan-secrets.py", result: pass, evidence: "zero tracked paths"}
  - {check: "gh api repos/rynrn/gonorth/actions/workflows --jq .total_count", result: pass, evidence: "0"}
snapshot: "not-required"
rollback: "delete this local report and its memory entry"
blockers:
  - "Credential owner must replace, test, and revoke suspected exposed credentials before PR or CI activation"
remaining_risks:
  - "Regex matches are not proof that a credential is valid"
  - "Current GitHub branch has no active secret-scan workflow"
issue_recommendation: keep-open
```
