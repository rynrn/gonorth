# #86 — תיקון ראיית OneTap

תאריך: 06.10.2026. תיקון ממוקד ל[דוח נכסי GeoDirectory](2026-10-06-issue-86-editorial-geo-assets.md), בלי לדרוס אותו. בקובץ `memory/data/2026-10-06-issue-86-geo-unaffected-interactions.json` בדיקת סרגל הנגישות השתמשה ב־selector שגוי `.onetap-toolbar`, ולכן רשמה `toolbarVisible:false`; זה לא היה כשל במוצר. בדיקה חוזרת השתמשה ב־`nav.onetap-accessibility.onetap-toggle-open` וכתבה `memory/data/2026-10-06-issue-86-geo-onetap-live.json`: תגובת 200, התפריט עבר `aria-expanded=false→true`, הסרגל נפתח, וללא pageerror. קובץ הראיה החדש הוא הקובע לטענת האינטראקציה. לא נעשה שינוי באתר.

```yaml
status: complete
scope: "Correct OneTap interaction evidence for issue 86"
risk_level: L0
canonical_writer: none
findings:
  - "Old test used a nonexistent toolbar selector"
sources:
  - {url: "https://gonorth.co.il/tours-kayaks-north-israel/", checked_at: "2026-10-06"}
changes_made:
  - "Added corrected interaction evidence"
unchanged:
  - {item: "live site", reason: "read-only verification"}
validation:
  - {check: "Mobile menu and OneTap in Chrome", result: pass, evidence: "memory/data/2026-10-06-issue-86-geo-onetap-live.json"}
snapshot: not-required
rollback: not-required
blockers: []
remaining_risks: []
issue_recommendation: keep-open
```

