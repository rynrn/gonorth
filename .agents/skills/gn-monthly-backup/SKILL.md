---
name: gn-monthly-backup
description: "Create and verify GoNorth's single local WordPress snapshot, safely replace it after approval, or check its age and ask monthly whether to refresh."
---

# גיבוי מקומי חודשי — GoNorth

קראו את [הנוהל הקובע](../../../docs/monthly-backup.md) ואת memory/backup-status.json.
היעד הקבוע: C:/dev/gonorth/backups/monthly/current/. עותק אחד, מחיקה רק לאחר אימות החדש.

## בדיקה חודשית בלבד
1. קראו את מועד האיסוף מהזיכרון והשוו למניפסט. אל תשנו אותו לתאריך הבדיקה.
2. הפעילו monthly_backup.py verify על current; אל תדפיסו SQL, הגדרות או רשימת סודות.
3. אם עברו לפחות 28 ימים, שאלו אם לחדש; אם הגיבוי חסר/פגום, דווחו ובקשו אישור. תעדו last_checked_at ו-last_reminder_at.
4. גיבוי צעיר ותקין או שאלה שכבר נשלחה ב-25 הימים האחרונים אינם מצריכים הודעה נוספת, אלא אם יש כשל חדש.
5. אין ליצור או למחוק דבר כחלק מהבדיקה. חידוש עתידי מחייב אישור חדש; אישור לגיבוי ראשון אינו אישור מתמשך.

## יצירה או חידוש מאושרים — L2
1. בצעו בדיקת הרשאות, מקום פנוי וריצות קיימות לפי הנוהל.
2. אספו בשרת דרך scripts/collect_server.py: יצוא DB, קבצים, תצורה ומועד snapshot אמיתי. scripts/monthly_backup.py אורז ומאמת.
3. הורידו את site.zip וה-manifest.json לתיקיית new המקומית. השאירו current ללא שינוי בזמן ההורדה.
4. הפעילו scripts/install_local.py --approved-refresh רק לאחר אישור המשתמש; הוא מאמת, מקדם ומסיר את הישן לאחר בדיקה נוספת.
5. עדכנו memory/backup-status.json ודוח מתוארך. הבדילו בין זמן איסוף, אריזה ואימות.
6. נקו את תיקיית העבודה המדויקת בשרת רק לאחר שאומת העותק המקומי. אין שינויי root ואין ניקוי גיבויים אחרים.
7. הציגו נתיב, תאריך, גודל, תוצאות הבדיקה והאם שחזור DB נבדק בפועל.

## ניסוי — L1
הריצו מהפרויקט:
```powershell
python .agents/skills/gn-monthly-backup/scripts/rehearse.py
```
תקנו כשל והריצו שוב. הנתונים מלאכותיים; מעבר אינו מוכיח גיבוי חי או שחזור SQL.
הבדיקה החודשית מוגדרת באוטומציית השיחה snapshot-gonorth. אין להתקין cron לחידוש אוטומטי.

