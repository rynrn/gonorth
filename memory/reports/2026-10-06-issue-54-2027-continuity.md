# #54 — חיבור דו־כיווני לתוכנית חנוכה 2027

תאריך: 06.10.2026. נבדקו [#54](https://github.com/rynrn/gonorth/issues/54), [#67](https://github.com/rynrn/gonorth/issues/67) ו[דוח בסיס 2026](2026-09-26-issue-54-hanukkah-baseline.md). כרטיס 2027 כבר קיים, ולכן לא נוצרה כפילות. נוסף קישור מפורש מ־#54 ל־#67 ומ־#67 חזרה ל־#54, עם URL קבוע, לקחי 2026 והוראת רענון עובדות 2027. לא שונה תוכן האתר.

#54 נשאר פתוח: אירועים, שעות ומחירים צריכים בדיקה מול מקורות רשמיים בשבוע של 29.11.2026, ואחרי החג יש להסיר קידום זמני אם קיים. הקישור לשנה הבאה אינו הוכחה שתנאי העונה הנוכחית הושלמו.

```yaml
status: partial
scope: "Issue 54 and 67 continuity links"
risk_level: L1
canonical_writer: "GitHub issue comments"
findings:
  - "2027 Hanukkah issue 67 already exists"
  - "2026 baseline report said bidirectional links were missing"
sources:
  - {url: "https://github.com/rynrn/gonorth/issues/54", checked_at: "2026-10-06"}
  - {url: "https://github.com/rynrn/gonorth/issues/67", checked_at: "2026-10-06"}
  - {url: "memory/reports/2026-09-26-issue-54-hanukkah-baseline.md", checked_at: "2026-10-06"}
changes_made:
  - "Linked 2026 and 2027 issues in both directions"
unchanged:
  - {item: "published Hanukkah guide", reason: "event refresh window has not arrived"}
validation:
  - {check: "Issue comments published", result: pass, evidence: "https://github.com/rynrn/gonorth/issues/54#issuecomment-6007917017 and https://github.com/rynrn/gonorth/issues/67#issuecomment-6007917282"}
snapshot: not-required
rollback: "Delete the two scoped issue comments if their links prove incorrect"
blockers: []
remaining_risks:
  - "2026 event, hours and price updates remain time-sensitive"
issue_recommendation: keep-open
```

