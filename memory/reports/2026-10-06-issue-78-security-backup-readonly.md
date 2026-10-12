# #78 — אבטחה וגיבוי: בדיקת מצב ללא שינוי באתר

תאריך: 06.10.2026. רמת סיכון: L0. לא שונו האתר, השרת או הגיבוי.

הגיבוי המקומי היחיד ב־C:/dev/gonorth/backups/monthly/current/ נאסף ב־02.10.2026. הפקודה monthly_backup.py verify עברה על 32,394 פריטים. המניפסט מציין 28,112 קבצים, 4,282 תיקיות וארכיון של 475,861,569 בתים. יצוא SQL נכלל, אך לא בוצעו ייבוא SQL או תרגיל שחזור. לא נוצר, הוחלף או נמחק גיבוי.

wp core verify-checksums עבר. הבדיקה מאמתת את ליבת WordPress בלבד, לא תוספים, תבנית או היעדר נוזקה. נספרו שני חשבונות administrator בלי לקרוא זהויות. wp-config.php בהרשאות 640; wp-content ו־uploads בהרשאות 2775. תחת uploads נמצא קובץ PHP יחיד smush/index.php, שלא נבדק כקוד. DISALLOW_FILE_EDIT=1, FS_METHOD=direct, FORCE_SSL_ADMIN=1; WP_DEBUG לא החזיר ערך בפקודת WP-CLI.

Wordfence 8.2.1 מותקן אך אינו פעיל ומוצע לו עדכון. לכן אין הגנת WAF של Wordfence פעילה; מצב WAF ברמת ספק/שרת אינו ידוע. 12 מ־15 תוספים רגילים מוצעים לעדכון לפי מלאי #77. לא בוצע עדכון.

בקשות HEAD ב־HTTPS לבית, /blog/, /places/, /wp-login.php ו־/wp-json/ החזירו 200. בשלושת דפי התוכן לא נצפו HSTS, CSP, CSP Report-Only, X-Content-Type-Options, Referrer-Policy, X-Frame-Options או Permissions-Policy. לדף הכניסה קיימים CSP frame-ancestors self, SAMEORIGIN ו־Referrer-Policy; ל־REST יש nosniff. זה מדגם בלבד.

להשלמת #78 נדרש תרגיל שחזור בסביבה מבודדת עם מנוע DB, ייבוא SQL, בדיקת כניסה/תוכן ומדידת RPO/RTO; במחשב המקומי לא נמצאו Docker, MySQL/MariaDB או PHP זמינים. אין לבצע ניסוי במסד הייצור. יש למפות WAF ספק, התראות והרשאות מנהלים; לבצע עדכונים מדורגים ב־staging לפי #77; ולהגדיר כותרות רק לאחר בדיקת הטמעות, CSP Report-Only ומיפוי דומיינים ל־HSTS. יש לשמר את כותרות הכניסה וה־REST, את FS_METHOD=direct ואת פעולת REST. שינויי root אינם נכללים עקב העדפת המשתמש.

מקורות שנבדקו 06.10.2026: docs/monthly-backup.md, memory/backup-status.json, דוח #77, GitHub #78; https://developer.wordpress.org/cli/commands/core/verify-checksums/ ; https://cheatsheetseries.owasp.org/cheatsheets/HTTP_Headers_Cheat_Sheet.html ; https://cheatsheetseries.owasp.org/cheatsheets/Content_Security_Policy_Cheat_Sheet.html ; https://cheatsheetseries.owasp.org/cheatsheets/HTTP_Strict_Transport_Security_Cheat_Sheet.html . הבדיקות היו פסיביות ומדגמיות; לא בוצעו סריקת חדירות או שחזור.

המלצה: להשאיר את #78 פתוח עד עמידה בתנאי הקבלה. כל רענון של הגיבוי החודשי דורש אישור חדש.

הערה תפעולית: פרסום ממצאי #78 ב־GitHub נדחה בבדיקת אישור אוטומטית בשל פרטי אבטחה ותשתית רגישים; הדוח נשמר מקומית בלבד, וכרטיס GitHub לא עודכן בסבב זה.
