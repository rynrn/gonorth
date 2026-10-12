# #85 — תיקון SEO חי ואימות חוזר, 02.10.2026

מצב: תיקון האתר הושלם; סעיף גישת זחלנים אמיתיים ממתין לקריאת לוג בהרשאה קיימת. רמת סיכון L2. כותבים קנוניים: שני קובצי תבנית הבת ו-WordPress post/meta לדף #24. לא נגעתי בליבת WordPress, בתוספים, ב-CSS, בהרשאות שרת או במסד נתונים באמצעות SQL ישיר.

## שינוי ממוקד

- `functions.php`: תיאור הארכיון, OG ו-Twitter אינם אומרים עוד “מאות מקומות”; גם טקסט ההבלטה בעמוד עודכן. Yoast `WebPage` schema משתמש באותו נוסח.
- נוספו פרופילי SEO עבריים לקטגוריות הקיימות `ochel` ו-`batei-kafe`, ללא יצירת כתובות חדשות. תוויות פירורי הלחם ב-JSON-LD תורגמו בארכיון המקומות ובשתי הקטגוריות בלבד. האפשרויות החדשות לא נוספו למסנן הבית של הארכיון, כדי לא לשנות את מבנה המסנן.
- `archive-gd_place.php`: טקסט ההבלטה הישן הוחלף גם בתבנית הגיבוי שאינה התבנית הפעילה כיום.
- רשומת WordPress #24 קיבלה כותרת ותיאור Yoast עקביים עם הארכיון. ה-slug והסטטוס לא שונו.

מקורות שנבדקו 02.10.2026: האתר הציבורי `/places/`, `/places/category/ochel/`, `/places/category/batei-kafe/`; קוד תבנית הבת החי; WP-CLI לדף #24 ולטקסונומיה. מסנן Yoast לפירורי לחם תועד ב-[Yoast developer](https://developer.yoast.com/features/schema/pieces/breadcrumb/).

## גיבוי וחזרה לאחור

לפני שינוי נשמרו עותקים מדויקים תחת `backups/rollback/2026-10-02-issue-85/ (מוחרג מ-Git)` ובתיקייה פרטית בשרת `/home/claude/.local/share/gonorth-issue85/`.
- SHA256 קודם `functions.php`: `997dab4ec1a42a317adc122ec734edf184c0030d24556b3cd4b2715fa8c43ea7`.
- SHA256 קודם `archive-gd_place.php`: `8449f5542fe810fd85b0439c0947dca910082cf4140e0c582c065e2cc6f37416`.
- SHA256 חדש `functions.php`: `f4d894790911490b14b4cdbc5edb73310bbfdec5adf05f4970b36ef9c08c95ca`.
- SHA256 חדש `archive-gd_place.php`: `1f4ffc5dff888288abfa6490d7a947d9b604fd7d65a98a3bec845cb55c74cbaf`.
- ערכי הכותרת ותיאור Yoast הקודמים של דף #24 שמורים ב-`page24.before.json` בתיקייה הפרטית ובתיקיית העבודה; קיים `rollback-page24.php` שעבר PHP lint. לחזרה לאחור: להחזיר את שני קובצי `*.before.php` מהתיקייה הפרטית למיקומיהם בתבנית, להפעיל `wp eval-file /home/claude/.local/share/gonorth-issue85/rollback-page24.php --path=/var/www/gonorth --allow-root`, לבצע `wp cache flush`, ולאמת URL ציבורי. לא בוצעה חזרה לאחור משום שהבדיקות עברו.

## אימות

שני קובצי PHP עברו `php -l` לפני ואחרי העדכון. WP-CLI אישר את ערכי דף #24. בקשות ציבוריות: בית, `/places/`, `ochel`, `batei-kafe` החזירו 200. בדיקת רגרסיה חוזרת: 12 עמודים, 12 כתובות הפניה ו-4 מפות אתר; `failures=[]` ב-`memory/data/2026-10-02-issue-85-seo-regression.json`. בארכיון ובקטגוריות שנערכו Title, description, H1, canonical, robots ו-JSON-LD תקינים. בפירורי הלחם מופיעים `דף הבית`, `מקומות בצפון`, `אוכל ושתייה` ו-`בתי קפה` בהקשרים המתאימים. תיאור המטא שווה ל-OG ולתיאור WebPage schema, והמילים “מאות מקומות” אינן עוד ב-HTML של `/places/`.

סקריפט WP-CLI חד-פעמי עדכן את הדף בהצלחה אך הסתיים בשגיאה לאחר העדכון בגלל קריאה לפונקציית ניקוי מטמון בשם שגוי. הערכים אומתו דרך WP-CLI והציבור, `wp cache flush` עבר, והסקריפט התקול הוסר כדי שלא ידרוס את הגיבוי. לא הייתה שגיאת HTTP באתר.

## גישת זחלנים — טרם הוכחה

`robots.txt` מאפשר גישה ומפנה למפת האתר. בקשות ניסוי עם User-Agent של Googlebot, OAI-SearchBot ו-GPTBot קיבלו 200, אך הן אינן בוטים אמיתיים. Wordfence אינו פעיל. בתצורת Nginx הקריאה לא נמצא כלל מפורש לשלושת השמות, אך לוג הגישה אינו קריא למשתמש SSH `claude` (`sudo -n` דורש סיסמה). לכן אין להצהיר שגישת בוט אמיתי או מדיניות WAF הוכחה.

הוכן כלי קריאה בלבד ב-`/home/claude/.local/share/gonorth-issue85/audit-crawlers.py` עם טווחי IP רשמיים של Google, OAI-SearchBot ו-GPTBot שנמשכו ב-02.10.2026. הוא קורא לוג מ-stdin **ללא הרשאות root** ומדפיס ספירות מצטברות בלבד לפי בוט מאומת, רובוטים/תוכן וקבוצת קוד HTTP; אינו מדפיס IP, נתיב או שורת לוג. בדיקות מלאכותיות עברו. להרצה חד-פעמית בשרת על ידי בעל גישת sudo קיימת: `sudo zcat -f /var/log/nginx/access.log* | python3 /home/claude/.local/share/gonorth-issue85/audit-crawlers.py --stdin`. רק `zcat` קורא את הלוג עם sudo; הסקריפט נשאר ללא הרשאה מוגברת. אין שינוי root או הרשאות קבצים. נסיון להשתמש באישור הקיים בקובץ התצורה המקומי לא התאפשר משום שאין בו ערך סיסמה זמין ל-sudo; לא בוצעה קריאת לוג ולא הודפס אישור כלשהו. אם התוצאה אפס, זה אינו מוכיח חסימה; יש לתעד כיסוי זמנים ולבדוק WAF בנפרד.

[טווחי Google](https://developers.google.com/static/crawling/ipranges/common-crawlers.json), [טווחי OAI-SearchBot](https://openai.com/searchbot.json), [טווחי GPTBot](https://openai.com/gptbot.json). [OpenAI מתעדת](https://developers.openai.com/api/docs/bots) כי OAI-SearchBot משמש לחיפוש ו-GPTBot לאימון; אלה הגדרות נפרדות.

#85 נשארת פתוחה רק עד לקבלת פלט הבדיקה או החלטה מתועדת אחרת לגבי סעיף גישת הבוטים. מדדי Search Console נשארים ב-#46.

## עדכון 04.10.2026 — ניסיון קריאת לוג ללא הרשאה

בעל האתר הפעיל את פקודת הבדיקה והתקבלה ההודעה `claude is not in the sudoers file`. סקריפט הספירה קיבל אפס שורות (`total_log_rows_scanned: 0`) משום שפקודת `sudo zcat` נכשלה; זהו **כשל איסוף נתונים**, לא ממצא לגבי גישה או חסימה של זחלנים. בדיקה נוספת בחיבור SSH רגיל אישרה שהמשתמש `claude` אינו יכול לקרוא את `/var/log/nginx/access.log`; אין `/var/log/apache2/access.log` במיקומו הרגיל. אין טעם לבקש סיסמת sudo נוספת עבור חשבון שאינו ב-sudoers, ואין לשנות הרשאות שרת לצורך הבדיקה. סעיף גישת זחלנים אמיתיים ב-#85 עדיין ממתין לבעל גישת ניהול קיימת שיפיק פלט מצטבר, או להחלטה מפורשת לסגור את הכרטיס עם מגבלה מתועדת. אין להציג אפס שורות כהוכחת חסימה.
