# #82 — חיפוש אחיד ושמירת מצב בין קטגוריות

תאריך: 04.10.2026. רמת סיכון L2 — שינוי חי בשלושה קובצי תבנית בת. ממשק כתיבה קנוני: קובצי התבנית `functions.php`, `front-page.php`, `assets/css/gonorth.css`; WordPress core ו־GeoDirectory לא נערכו. משימת־אב: [#74](https://github.com/rynrn/gonorth/issues/74); תלות UX [#81](https://github.com/rynrn/gonorth/issues/81) הושלמה קודם.

## הבעיה שנמדדה

החלפת קטגוריה בארכיון מחקה את מילת החיפוש. השדות לא היו בתוך טופס GET ולא נשאו `label`. בחיפוש ללא התאמות GeoDirectory הציג הודעת אפס תקינה, אבל תבנית Astra חזרה אחריה על פריט ריק 43,790 פעמים עד HTTP 500 (תגובה של כ־12.5MB). התוצאה נבדקה עם `/places/?s=zzzxxyyq` לפני שינוי; לא הודפסו פרטי משתמשים.

## שינוי

- דף הבית והארכיון שולחים `GET` עם `category` ו־`s`. השרת מפנה את `category` לנתיב הטקסונומיה הקבוע `/places/category/{slug}/` ומשאיר רק `?s=...` לחיפוש. זה פועל גם בלי JavaScript; החלפה בממשק שולחת את אותו טופס.
- בטופס הארכיון נוספו תוויות אמיתיות, ערכים שנקראים מהכתובת, ספירת תוצאות, תקציר מצב וקישור איפוס. `pageshow` ו־`popstate` מסנכרנים את הבחירה אחרי Back כדי שזיכרון הטופס של הדפדפן לא יציג קטגוריה לא נכונה.
- כאשר השאילתה הראשית מכילה אפס תוצאות, התבנית מציגה מסך אפס יחיד עם הצעות וקישור לכל המקומות, ומפסיקה לפני לולאת Astra הפגומה. השאילתה נשארת בכתובת וה־SEO נשאר `noindex, follow`.
- נוספו עיצוב נגיש ל־label מוסתר חזותית, פוקוס ומצב סינון. גרסת `gonorth.css` עלתה מ־2.9.2 ל־2.9.3.

## גיבוי וחזרה

עותקים מדויקים לפני שינוי, גרסת ביניים, מועמדים, בדיקות וצילומים שמורים ב־`C:/dev/gonorth/backups/rollback/2026-10-04-issue-82/`. עותקי `*.before.*` נשמרו גם בתיקייה פרטית בשרת `/home/claude/.local/share/gonorth-issue82/`. SHA-256 של שלושת קובצי המקור המקומיים והחיים הושווה לפני פריסה; PHP lint עבר לפני ואחרי.

לשחזור מלא של #82 בלבד:
```bash
ssh gonorth "cp /home/claude/.local/share/gonorth-issue82/functions.before.php /var/www/gonorth/wp-content/themes/gonorth-child/functions.php && cp /home/claude/.local/share/gonorth-issue82/front-page.before.php /var/www/gonorth/wp-content/themes/gonorth-child/front-page.php && cp /home/claude/.local/share/gonorth-issue82/gonorth.before.css /var/www/gonorth/wp-content/themes/gonorth-child/assets/css/gonorth.css && php -l /var/www/gonorth/wp-content/themes/gonorth-child/functions.php && php -l /var/www/gonorth/wp-content/themes/gonorth-child/front-page.php"
ssh gonorth "wp cache flush --path=/var/www/gonorth --allow-root && wp eval 'wp_cache_clear_cache();' --path=/var/www/gonorth --allow-root"
```
הקבצים שוחזרו מנקודת הזמן שאחרי #81, כך שקיצור תווית הבית נשמר.

## אימות ציבורי ודפדפן

- חיפוש “יקב” בכל המקומות: HTTP 200 ו־21 תוצאות. אותו חיפוש בקטגוריית יקבים: HTTP 200 ו־12 תוצאות — 10 בעמוד הראשון ו־2 בשני; `s` והבחירה נשמרים בקישור הדפדוף.
- שליחת Enter מטופס הבית עם קטגוריה יקבים הגיעה לכתובת הקבועה `/places/category/yakavim/?s=...`; בחירה בטבע שמרה את השאילתה והציגה מקום אחד. Back החזיר כתובת, בחירה, טקסט ותוצאות של יקבים; פתיחת אותו URL מחדש החזירה אותו מצב.
- בקשת GET ישירה ל־`/places/?category=yakavim&s=...` הופנתה לנתיב הקטגוריה, ולכן השליחה פועלת ללא JavaScript. קישור “נקה סינון” מוביל ל־`/places/`.
- חיפוש `zzzxxyyq` מחזיר כעת HTTP 200, אפס כרטיסים, הודעה והצעות, תגובה של כ־451KB במקום HTTP 500. טקסט HTML בזדון הוצג בבריחה (`&lt;script&gt;`) ולא כתג פעיל.
- כתובות ארכיון וקטגוריה ללא חיפוש נשארו `index, follow` עם canonical עצמי. כתובות `?s=` והתוצאה הריקה נשארו `noindex, follow`.
- רגרסיית בית בשישה רוחבים 375/390/768/1024/1280/1440: שבעה כרטיסים בכל רוחב, `scrollWidth<=innerWidth`; צילום מובייל של טופס החיפוש נבדק חזותית. PHP lint עבר; `phpcs` אינו מותקן בשרת.

מקורות שנבדקו ב־04.10.2026: [#82](https://github.com/rynrn/gonorth/issues/82), [דף הבית](https://gonorth.co.il/), [ארכיון המקומות](https://gonorth.co.il/places/), קוד התבנית החי וקוד GeoDirectory המקומי בשרת.

```yaml
status: complete
scope: "טפסי חיפוש בית/ארכיון, מעבר קטגוריה, חיפוש אפס תוצאות"
risk_level: L2
canonical_writer: "קובצי תבנית הבת החיים; ללא כתיבה ישירה למסד"
findings:
  - "מעבר קטגוריה איבד s וטופס הארכיון לא היה סמנטי"
  - "חיפוש ריק גרם ללולאת רינדור ו-HTTP 500"
sources:
  - url: "https://github.com/rynrn/gonorth/issues/82"
    checked_at: "2026-10-04"
  - url: "https://gonorth.co.il/places/"
    checked_at: "2026-10-04"
changes_made:
  - "GET קנוני עם category ו-s, label וערכי טופס"
  - "סנכרון Back ומסך אפס תוצאות"
  - "CSS 2.9.3"
unchanged:
  - item: "ליבת WordPress, תוסף GeoDirectory ומפת האתר"
    reason: "התיקון תחום לתבנית הבת"
validation:
  - check: "HTTP, דפדפן, דפדוף, SEO, PHP lint ושישה רוחבי מסך"
    result: pass
    evidence: "מדידות וצילומים בתיקיית rollback"
snapshot: "C:/dev/gonorth/backups/rollback/2026-10-04-issue-82/"
rollback: "העתקת שלושת קובצי *.before.* מהתיקייה הפרטית בשרת וניקוי שני המטמונים"
blockers: []
remaining_risks: []
issue_recommendation: close
```
