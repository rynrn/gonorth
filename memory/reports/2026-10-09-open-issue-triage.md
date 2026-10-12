# מיון 34 כרטיסי GitHub פתוחים — 09.10.2026

מאגר: https://github.com/rynrn/gonorth/issues . ביקורת קריאה של תוכן הכרטיסים והעדכונים האחרונים בוצעה על ידי האורקסטרטור; תוויות הוחלו ב־GitHub ואומתו: 34 מתוך 34 עם תווית מצב אחת בלבד. אין סגירת כרטיסים בסבב זה. נתונים מלאים: `memory/data/2026-10-09-open-issue-triage.json`.

## חלוקה

- **בתהליך (6):** #75, #77, #78, #86, #88, #91. יש עבודה מקצועית שאפשר להמשיך עכשיו. ב־#86 שינויי Nginx/ROOT מסומנים בנפרד כ״נדחה לבקשת בעל האתר״; מדידות ומחקר עדיין אפשריים. ב־#75 אפשר להכין מיפוי וסדר פעולות, אך החלפה, ביטול ואימות דו־שלבי של חשבונות יצריכו בהמשך את בעל החשבון.
- **ממתין לבעל האתר (4):** #46 (גישת Search Console או יצוא 28 ימים), #84 (גישת קריאה ל־GA4), #96 (חשבוניות ועלויות ושעות בפועל), #97 (הרשאות חשבונות סושיאל; שימוש במדיה רק עם רישיון מתועד). אלה בקשות גישה/נתונים, לא בקשה לאישור מקצועי גורף.
- **ממתין לגורם חיצוני (2):** #93 — פתיחה רשמית של פארק אדמית ואימות נקודת גישה בטוחה, בדיקה חוזרת מ־25.10.2026; #99 — ישויות Google מדויקות ואימות בעלות של ארבעה עסקים, בדיקה חוזרת מ־09.11.2026. אין להמציא מזהים/דירוגים.
- **מתוזמן להמשך (19):** #50, #51, #54, #55–#69, #98. תכנון 2027 וסקירת #51 מתחילים בנובמבר 2026; רענון חנוכה #54 בשבוע של 29.11; ניסוי #98 בסוף נובמבר ובהתאם לנתוני #84/#96. משימות החגים של 2027 ייסקרו במסגרת #50 לפני חלונות הביצוע שלהן. לא לפתוח פרסום מוקדם בלי בדיקה עונתית.
- **מעקב משימת אב (3):** #48, #49, #74. נשארים פתוחים עד להשלמת המשימות התלויות.

## מדיניות החלטות ותזכורת

החלטות מקצועיות, ביקורת תוכן ובחירת יישום בתחום ההרשאות הקיימות נמסרות לאורקסטרטור ולסוכנים הרלוונטיים לפי AGENTS.md; אין להפוך אותן לבקשת אישור מיותרת מהמשתמש. גישה לחשבונות, בעלות עסק, הוצאות ופעולות חשבון נשארות לבעל האתר. החלטת המשתמש לדחות ROOT נשמרת. אוטומציית Codex `gonorth` מבצעת בדיקה שבועית בתיקייה ובכרטיסים, מתריעה רק על פעולה חדשה או שינוי משמעותי, ואינה חוזרת על אותה בקשה בתוך 25 ימים ללא שינוי. תווית לבדה אינה תזכורת.

```yaml
status: complete
scope: "Classification and labeling of all 34 open GitHub issues"
risk_level: L1
canonical_writer: "GitHub Issues labels via gh; local triage ledger"
sources:
  - {url: "https://github.com/rynrn/gonorth/issues", checked_at: "2026-10-09"}
changes_made:
  - "Created workflow labels and applied exactly one primary status to each open issue"
  - "Marked #86 ROOT part explicitly deferred"
  - "Created dated triage ledger and weekly quiet follow-up automation"
validation:
  - {check: "all open issue primary labels", result: pass, evidence: "34/34 have exactly one; counts 6+4+2+19+3"}
snapshot: "memory/data/2026-10-09-open-issue-triage.json"
rollback: "Remove the newly added workflow labels from affected GitHub issues; keep existing enhancement labels"
blockers: []
remaining_risks:
  - "Future status can change and requires weekly review"
issue_recommendation: keep-open
```
