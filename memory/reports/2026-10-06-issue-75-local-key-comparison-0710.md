# #75 — השוואת דפוס מפתח Google בין היסטוריה לתצורה המקומית

תאריך: 06.10.2026. המשך ל[דוח הסריקה](2026-10-06-issue-75-redacted-history-scan-0703.md). סיכון L0; לא שונו קבצים, אתר או אישורים. נבדקו בזיכרון בלבד התאמות לכלל Google API key של הסורק בשני קובצי תצורה מקומיים בלתי־מנוהלים מול blobs מהיסטוריית Git המקומית. הפלט הציג ערכי אמת/שקר בלבד — ללא מפתח, גיבוב, שורת מקור או שם קובץ מפורט.

תוצאה: בהיסטוריה המקומית קיימות התאמות לדפוס; בשני קובצי התצורה המקומיים שנבדקו לא נמצאה התאמה לדפוס, ולכן לא נמצאה התאמה מדויקת בין ערך נוכחי לערך היסטורי במסגרת זו. **אין בכך ראיה שהמפתח ההיסטורי בוטל**: מפתח חי עשוי להישמר בשרת, אצל ספק או במאגר אחר, והדפוס אינו מזהה כל פורמט אפשרי. תוקף מפתחות והגבלות Google לא נבדקו. תנאי הקבלה של #75 עדיין חסרים.

```yaml
status: partial
scope: "equality-only local Google key pattern comparison"
risk_level: L0
canonical_writer: none
findings:
  - "Historical Google-key patterns exist"
  - "No matching pattern in the two checked current local configs"
sources:
  - {url: "tools/scan-secrets.py", checked_at: "2026-10-06"}
  - {url: "local Git refs and ignored local configs", checked_at: "2026-10-06"}
changes_made: []
unchanged:
  - {item: "Credentials and site", reason: "Read-only equality check"}
validation:
  - {check: "in-memory regex comparison", result: pass, evidence: "boolean-only output, no values"}
snapshot: not-required
rollback: "No change"
blockers:
  - "Owner-assisted verification of active key and confirmed revocation of any superseded key"
remaining_risks:
  - "Historical key may remain valid outside checked local files"
issue_recommendation: keep-open
```