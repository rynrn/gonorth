# #75 — בדיקת דפוסי אישורים בהיסטוריית Git

תאריך: 06.10.2026 03:57 (ישראל). כרטיס: https://github.com/rynrn/gonorth/issues/75. רמת סיכון L0, קריאה בלבד; לא שונו אישורים, מאגר מרוחק או היסטוריית Git.

הבדיקה השתמשה בכללים הקיימים ב־`tools/scan-secrets.py`. נסרקו 238 אובייקטים נגישים מה־refs המקומיים, מהם 130 blobs ייחודיים; 129 מהם טקסט UTF‑8 ואחד לא פוענח כ־UTF‑8. הסקריפט לא הדפיס ערך, שורת מקור, נתיב קובץ או מזהה אובייקט. `HEAD` כולל 95 blobs. הסריקה אינה מכסה refs שנמחקו, reflog, forks, עותקי עבודה אחרים או remote שאינו כלול ב־refs המקומיים.

| כלל | blobs בהיסטוריה | blobs ב־HEAD | ערכי התאמה שונים |
|---|---:|---:|---:|
| דפוס Google API key | 10 | 3 | 1 |
| דפוס WordPress application password | 5 | 3 | 2 |
| דפוס השמת credential | 2 | 2 | 2 |

לא נמצאו התאמות לכללי private key, GitHub token או Basic Auth literal בטווח הזה. בדיקת `python tools/scan-secrets.py` על קובצי Git בעץ העבודה הנוכחי עברה ללא דפוסים. לכן ההסרה מהקבצים הנוכחיים אינה מנקה את גרסת `HEAD` או את ההיסטוריה.

התאמת regex אינה הוכחה שהמחרוזת הייתה אישור אמיתי או שהיא עדיין תקפה. אין לבצע ניסיון שימוש באישורים ישנים כדי לברר זאת. הצעד הבא לפי `docs/credential-management.md`: בעל החשבון בודק בחנות האישורים הפרטית את האינטגרציות הרלוונטיות, מכין חלופות מצומצמות, בודק אותן בנתיב העבודה, מבטל את הישנות ומתאם טיפול בהיסטוריה לאחר מכן. אין לשכתב היסטוריה לפני סבב ההחלפה והתיאום. הממצא נשאר בדוח מקומי ולא פורסם ב־GitHub.

קובצי הבדיקה: `memory/work/issue75-history-scan.py`, `memory/work/issue75-history-scan-detail.py`; שניהם מחזירים ספירות בלבד.

```yaml
status: partial
scope: "reachable local Git history and current tracked working tree"
risk_level: L0
canonical_writer: "none"
findings:
  - "דפוסי אישורים קיימים בבלובים של HEAD ובהיסטוריה"
  - "קובצי Git בעץ העבודה הנוכחי עוברים את סורק הסודות"
sources:
  - {url: "tools/scan-secrets.py", checked_at: "2026-10-06"}
  - {url: "docs/credential-management.md", checked_at: "2026-10-06"}
changes_made: []
unchanged:
  - {item: "credentials and Git history", reason: "החלפה ושכתוב דורשים בעל חשבון ותיאום"}
validation:
  - {check: "git rev-list --objects --all, git cat-file, same pattern rules", result: pass, evidence: "aggregate counts above"}
  - {check: "python tools/scan-secrets.py", result: pass, evidence: "No tracked credential patterns detected"}
snapshot: "not-required"
rollback: "no change"
blockers:
  - "בעלי החשבונות וחנות האישורים הפרטית נדרשים להחלפה ולביטול"
remaining_risks:
  - "תוקף התאמות regex לא אומת; refs אחרים ועותקים חיצוניים לא נסרקו"
issue_recommendation: keep-open
```
