# #78 — בדיקת שלמות לגיבוי המקומי הקיים, ללא שחזור

תאריך: 06.10.2026 07:07 שעון ישראל. כרטיס: https://github.com/rynrn/gonorth/issues/78; משימת־אב: https://github.com/rynrn/gonorth/issues/74. סיכון L1 לתיעוד בלבד; הגיבוי לא נוצר, לא הוחלף ולא נמחק. לא הייתה התחברות לשרת לצורך איסוף.

לפי [הנוהל](../../docs/monthly-backup.md) וה[סקיל](../../.agents/skills/gn-monthly-backup/SKILL.md), קיים עותק יחיד ב־`C:/dev/gonorth/backups/monthly/current/`, הכולל `site.zip` ו־`manifest.json`. ה־snapshot נאסף ב־02.10.2026 09:58:58 UTC; גודל הארכיון 475,861,569 בתים. `memory/backup-status.json` מזהה אותו כעותק התקין היחיד ומציין במפורש `database_restore_tested: false`.

הפעלתי `python .agents/skills/gn-monthly-backup/scripts/monthly_backup.py verify C:/dev/gonorth/backups/monthly/current`. הכלי החזיר exit 0 עם `status=verified`, ו־32,394 פריטי קבצים/תיקיות שנבדקו לפי המניפסט. לא פתחתי או הדפסתי SQL, wp-config או ערכי אישורים. עדכנתי רק `last_checked_at` ו־`last_verified_at` ל־06.10.2026 04:06:52 UTC; מועד האיסוף, מועדי האריזה ותכולת ה־snapshot לא שונו. השחזור המאומת המוגדר בתנאי הקבלה של #78 עדיין לא בוצע: לא נפרס יישום לסביבה מבודדת, לא יובא SQL ולא נמדד RTO. מבחינת RPO, שחזור מהעותק הזה יחזיר את מצב איסוף 02.10; שינויים חיים שאחריו אינם כלולים בו. זהו גבול תכנית הגיבוי החודשי המאושרת.

המשך: תרגיל שחזור מבודד לפי מדיניות המשתמש, בלי יבוא ל־DB החי ובלי יצירת snapshot חודשי חדש ללא אישור. יש למדוד זמן פריסה, הצלחת ייבוא SQL ופתיחת עמודים בסביבת הבדיקה. #78 נשאר פתוח גם לבדיקת כותרות, WAF והרשאות.

```yaml
status: partial
scope: "existing local monthly backup integrity only"
risk_level: L1
canonical_writer: "memory/backup-status.json verification timestamps"
findings:
  - "The one existing local backup verifies successfully"
  - "No database restore test or RTO measurement exists"
sources:
  - {url: "docs/monthly-backup.md", checked_at: "2026-10-06"}
  - {url: "memory/backup-status.json", checked_at: "2026-10-06"}
  - {url: "backups/monthly/current/manifest.json", checked_at: "2026-10-06"}
changes_made:
  - "Updated only last_checked_at and last_verified_at after a successful verify"
unchanged:
  - {item: "Backup payload and snapshot timestamp", reason: "Read-only integrity check"}
  - {item: "Live site and SQL", reason: "Restore exercise not yet performed"}
validation:
  - {check: "monthly_backup.py verify", result: pass, evidence: "exit 0, verified, 32394 manifest entries"}
snapshot: "not-required for read-only payload check"
rollback: "Restore prior verification timestamps from dated report if necessary; snapshot payload unchanged"
blockers:
  - "Isolated application and SQL restore exercise not yet executed"
remaining_risks:
  - "Monthly snapshot excludes changes since collection; no measured RTO"
issue_recommendation: keep-open
```