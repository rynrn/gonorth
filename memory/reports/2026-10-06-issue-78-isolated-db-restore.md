# #78 — תרגיל שחזור SQL מבודד מהגיבוי המקומי

תאריך: 06.10.2026, כ־08:02 שעון ישראל. [כרטיס #78](https://github.com/rynrn/gonorth/issues/78); משימת־אב [#74](https://github.com/rynrn/gonorth/issues/74). זהו תרגיל **שחזור מסד נתונים בלבד**, לא שחזור אתר מלא ולא מדידת RTO מקצה לקצה.

השתמשתי בעותק המקומי היחיד שנאסף ב־02.10.2026 ב־`C:/dev/gonorth/backups/monthly/current/`. לא נוצר, הוחלף או נמחק snapshot. הארכיון `site.zip` אומת מחדש ב־SHA-256 מול `memory/backup-status.json` לפני הייבוא; הוא כולל `database.sql` יחיד של 5,023,794 בתים. בדיקה לפי ספירת פקודות בלבד מצאה 124 `CREATE TABLE` וללא `CREATE DATABASE` או `USE`, כך שלא היה יעד DB חיצוני מפורש בדאמפ. SQL, wp-config, סודות ונתוני שורות לא הודפסו.

גרסת השרת החי היא MariaDB 10.11.14. הורדתי [חבילת ZIP רשמית תואמת](https://archive.mariadb.org/mariadb-10.11.14/winx64-packages/mariadb-10.11.14-winx64.zip) ואימתתי את SHA-256 שלה `838b7d91b871a30c2e42e5ceff9c6859ddd061b57328fb7cb51154af9bd8ddf4` מול [רשימת היצרן](https://archive.mariadb.org/mariadb-10.11.14/winx64-packages/sha256sums.txt). לפי [תיעוד MariaDB](https://mariadb.com/docs/server/server-management/install-and-upgrade-mariadb/installing-mariadb/binary-packages/installing-mariadb-windows-zip-packages), נוצר מופע זמני בתיקיית עבודה מקומית, ללא Windows service. הופעל `skip-networking=ON` וחיבור named pipe בלבד; שאילתת `@@skip_networking` החזירה 1.

ה־SQL הוזרם ישירות מתוך ה־ZIP למסד `gonorth_recovery` החדש, בלי לפרוס קובץ SQL לדיסק. הייבוא הסתיים בהצלחה: 124 טבלאות; טבלאות WordPress צפויות נמצאו; ספירות נתונים ללא תוכן: 381 פוסטים, 2,417 ערכי options ו־5 משתמשים. בדיקת checksum, ייבוא וספירות נמשכה 5.113 שניות במחשב זה; **זו אינה מדידת RTO של אתר**. ראיה: [תוצאת התרגיל](../data/2026-10-06-issue-78-isolated-db-restore.json). [סקריפט התרגיל](../scripts/issue78-restore-db.py) אינו חושף פלט SQL; לצורך הרצה חוזרת צריך להכין מופע MariaDB מבודד כפי שמתואר כאן.

לאחר הבדיקות, MariaDB הזמני נעצר, נבדק שתהליך השרת הסתיים, ותיקיית `memory/work/issue78-restore` הוסרה בנתיב מוחלט ומאומת. הגיבוי המקורי עדיין קיים, ורק הוא נשמר. ב־[זיכרון הגיבוי](../backup-status.json) סומן `database_restore_tested=true` עם קישור לראיה; מועד איסוף ה־snapshot נשמר בדיוק כפי שהיה. `application_restore_tested=false`.

#78 נשאר פתוח: לא הותקנה ליבת WordPress ולא הורץ אתר בסביבה נפרדת; לכן אין עדיין RTO מקצה לקצה או בדיקת עמודים על שחזור. יתר בדיקות הכותרות, WAF והרשאות בכרטיס לא נכללות בתרגיל זה.

```yaml
status: partial
risk_level: L1
scope: "isolated import of existing October 2 SQL snapshot"
canonical_writer: "temporary network-disabled local MariaDB 10.11.14"
snapshot: "existing backups/monthly/current/site.zip, unchanged"
changes_made:
  - "Validated archive hash and imported 5,023,794 SQL bytes into a temporary local DB"
  - "Recorded database_restore_tested=true and evidence path; preserved snapshot collection time"
validation:
  - {check: "MariaDB import", result: pass, evidence: "124 tables; 381 posts, 2417 options, 5 users"}
  - {check: "network isolation", result: pass, evidence: "@@skip_networking=1"}
  - {check: "cleanup", result: pass, evidence: "server process stopped; temporary workspace removed; current backup present"}
rollback: "not applicable; no live site or retained backup payload changed"
blockers:
  - "Full WordPress application restore and end-to-end RTO not tested"
  - "Other security acceptance checks remain"
issue_recommendation: keep-open
```
