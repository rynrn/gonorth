# #87 — תמונת שיתוף לעמודי אודות ופתוח בשבת

תאריך: 06.10.2026. רמת סיכון L2. שינוי חי ממוקד במטא Yoast של עמוד #26 (/about/) ועמוד #439 (/open-saturday/). תוכן העמודים לא שונה.

לפני השינוי, בשני העמודים לא הופיע og:image אף ש־twitter:card היה קיים. נקבעה לכל עמוד תמונת השיתוף הקיימת של אתר GoNorth: https://gonorth.co.il/wp-content/themes/gonorth-child/assets/social/gonorth-og-home.jpg . זהו אותו נכס שכבר משמש את דף הבית, בגודל 1200×630, ונבדק חזותית כתמונת נוף צפוני. לא הוכנס נכס חדש או מקור מדיה חיצוני.

הכתיבה נעשתה דרך _yoast_wpseo_opengraph-image לכל עמוד, ולאחר מכן save_post רגיל לעדכון indexables של Yoast. דגימות HTML ציבורי לשני העמודים: HTTP 200, og:image לכתובת הנכס הקיים, twitter:card=summary_large_image, canonical נכון וללא הפניה שבורה. השוואת post_content לפני/אחרי דרך WP-CLI הייתה זהה לחלוטין בשני העמודים.

יצוא טרום שינוי וראיות לאחריו: memory/work/issue87-social/page-26-before.json, page-439-before.json, page-26-after.json, page-439-after.json. סקריפט שחזור מוגן: memory/work/issue87-social/rollback.php וכן /home/claude/.local/share/gonorth-issue87-social/rollback.php בשרת. php -l ובדיקת guard ללא שינוי עברו. הפעלה עם ISSUE87_SOCIAL_ROLLBACK_APPLY=1 מסירה את שתי דריסות המטא ומרעננת את Yoast; לא הופעל שחזור.

הבדיקה אימתה את המטא המוגש לציבור. היא אינה מוכיחה שפלטפורמות חברתיות רעננו מטמון שיתוף קודם. #87 נשאר פתוח לתמונות כרטיסי מסעדות/סיורים ולבדיקות שיתוף מסכמות.

