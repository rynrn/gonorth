# #77 — מלאי תוספים וסקירת עדכונים

תאריך: 06.10.2026. כרטיס: https://github.com/rynrn/gonorth/issues/77. משימת אב: https://github.com/rynrn/gonorth/issues/74. תלות #76 נסגרה.

## מצב מאומת

WordPress 7.1.2 (השורה הישנה ב־AGENTS.md אינה מעודכנת). 15 תוספים רגילים, MU אחד ו־drop-in אחד. הצעת עדכון ל־12 תוספים רגילים, מהם Wordfence אינו פעיל. עדכון Astra מוצע מ־4.13.0 ל־4.14.0; gonorth-child פעילה ב־1.0.0. אין עדכונים אוטומטיים פעילים.

| רכיב | מטרה | מצב וגרסה | עדכון מוצע | בעל אחריות מקצועי | רישיון |
|---|---|---|---|---|---|
| accessibility-onetap | נגישות | active 2.10.0 | 2.14.0 | designer-agent + dev-agent | לא מוצהר בכותרת; זכאות מסחרית לא אומתה |
| ameliabooking | הזמנות פעילויות | active 2.3 | 2.4.11 | dev-agent + cfo-agent | לא מוצהר בכותרת; זכאות מסחרית לא אומתה |
| autoptimize | אופטימיזציית נכסים | active 3.1.15.1 | 3.1.16 | performance-agent | לא מוצהר בכותרת; זכאות מסחרית לא אומתה |
| elementor | בונה עמודים | active 4.0.7 | 4.3.4 | dev-agent + designer-agent | לא מוצהר בכותרת; זכאות מסחרית לא אומתה |
| geodirectory | מקומות, מפות, חיפוש ושעות | active 2.8.160 | 2.8.189 | listings-agent + dev-agent | לא מוצהר בכותרת; זכאות מסחרית לא אומתה |
| gonorth-place-data | נתוני מקומות, סכמה ואנליטיקה | active 1.18.9 | אין הצעה | dev-agent + seo-agent | לא מוצהר בכותרת; זכאות מסחרית לא אומתה |
| mcp-adapter | ממשק אוטומציה | active 0.4.1 | 0.7.0 | dev-agent + security-agent | לא מוצהר בכותרת; זכאות מסחרית לא אומתה |
| sassy-social-share | שיתוף חברתי, לא פעיל | inactive 3.3.79 | אין הצעה | social-media-agent | לא מוצהר בכותרת; זכאות מסחרית לא אומתה |
| scaup-seo | שכבת SEO משלימה | active 1.58.2 | אין הצעה | seo-agent | לא מוצהר בכותרת; זכאות מסחרית לא אומתה |
| wp-smushit | דחיסת תמונות | active 4.3.0 | 4.3.4 | performance-agent | לא מוצהר בכותרת; זכאות מסחרית לא אומתה |
| updraftplus | גיבוי WordPress | active 1.26.3 | 1.26.8 | security-agent + performance-agent | לא מוצהר בכותרת; זכאות מסחרית לא אומתה |
| woocommerce | מסחר והזמנות | active 10.7.0 | 11.1.2 | dev-agent + cfo-agent | לא מוצהר בכותרת; זכאות מסחרית לא אומתה |
| wordfence | אבטחה, לא פעיל | inactive 8.2.1 | 9.0.2 | security-agent | לא מוצהר בכותרת; זכאות מסחרית לא אומתה |
| wp-super-cache | מטמון עמודים | active 3.1.0 | 3.1.4 | performance-agent | לא מוצהר בכותרת; זכאות מסחרית לא אומתה |
| wordpress-seo | Yoast: מטא, sitemap וסכמה | active 27.5 | 28.6 | seo-agent | לא מוצהר בכותרת; זכאות מסחרית לא אומתה |
| geodir-fast-ajax | האצת חיפוש GeoDirectory | must-use 1.0.0 | אין הצעה | dev-agent + performance-agent | לא מוצהר בכותרת; זכאות מסחרית לא אומתה |
| advanced-cache.php | drop-in של מטמון | dropin — | אין הצעה | performance-agent | לא מוצהר בכותרת; זכאות מסחרית לא אומתה |

שדות License ו־Requires Plugins בכותרות של כל 15 התוספים הרגילים ריקים. לכן הטבלה אינה טוענת שהזכאות המסחרית תקפה או שלא נדרש רישיון. לא נקראו מפתחות או פרטי חשבון. מקור: memory/data/2026-10-06-issue-77-live-inventory.json ו־memory/data/2026-10-06-issue-77-plugin-headers.json.

## GeoDirectory ותלויות

בשרת 2.8.160; WP-CLI מציע 2.8.189 וגם dry-run מאשר זאת. היומן הרשמי של GeoDirectory מפרט בין 2.8.160 ל־2.8.189 תיקוני סינון/escaping לחיפוש, שעות, קואורדינטות ושדה דוא״ל, תיקון התנגשות מפה עם Astra ב־2.8.178 ותיקון תאימות לווידג׳טים של Elementor 4.3 ב־2.8.185. אין בכך הוכחה שהאתר פגיע בפועל או שעדכון 2.8.189 תואם לקוד המותאם. מקור רשמי שנבדק ב־06.10: https://wordpress.org/plugins/geodirectory/#developers.

מסלול עדכון בטוח: להקים סביבת staging פרטית עם עותק נתונים; לשמור גיבוי ושחזור נפרדים של קבצי התוספים ומסד הנתונים לפני ניסוי; לעדכן קודם GeoDirectory לבדו ולבדוק חיפוש עברי/קטגוריות/AJAX, שעות, מפות ו־REST, טופס הוספת מקום, מובייל RTL ותבניות Astra/Elementor. אחר כך לבחון Elementor ו־Astra יחד באותה סביבה. רק אחרי הצלחה לבצע פריסה מדורגת עם שחזור מוכן, בדיקת 197 כתובות ותרחישי משתמש. אין לעדכן הכל בבת אחת.

## אחריות חופפת

- Schema: seo-agent אחראי ל־Yoast ולבדיקת הפלט; dev-agent אחראי לשכבת הסכמה ב־gonorth-place-data ולמניעת ישויות סותרות. GeoDirectory מספק נתוני מקור של מקום, לא עורך ידנית את הסכמה.
- Lazy loading ודחיסת תמונות: performance-agent אחראי להכריע בין WordPress/Smush/Autoptimize על בסיס רשת ו־LCP, בלי להפעיל מנגנונים כפולים על סמך השערה.
- מטמון: performance-agent אחראי ל־WP Super Cache/Autoptimize/drop-in. שינוי קוד מחייב רענון HTML ונכסים יחד. #84 הראה שדף הבית שמר הפניה לגרסה ישנה גם אחרי wp cache flush; הבעיה נפתרה רק אחרי wp_cache_clear_cache().
- תוסף לא פעיל אינו בהכרח מיותר: לפני הסרת Sassy Social Share או Wordfence יש להוכיח היעדר שימוש/תלות, בדיקת אבטחה וחזרה לאחור. לא הוסר רכיב.

## מגבלה והמשך

לא נמצאה סביבת staging קיימת או runtime מקומי ל־WordPress/MySQL. קיימת MariaDB בשרת אך לא הוכחה האפשרות להקים עותק DB פרטי ובטוח עם הרשאות משתמש claude; לכן לא הורץ עדכון ניסיוני או חי, ולא נבדקו תרחישי חיפוש/שעות/מפות/טפסים אחרי שינוי גרסה. השלב הבא הוא סביבת staging מבודדת והוכחת שחזור לפני פריסת GeoDirectory. זהו חסם טכני, לא בקשה לרכישת תוסף.

חוזה שינוי: status=partial; risk_level=L0 (מלאי וקריאת מקורות בלבד); canonical_writer=none; changes_made=דוח וקובצי נתונים בלבד; validation=WP-CLI inventory+dry-run עברו; snapshot=not-required; rollback=not-required; issue_recommendation=keep-open.
