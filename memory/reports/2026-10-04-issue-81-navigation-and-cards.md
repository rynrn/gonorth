# #81 — ניווט דף הבית וכרטיסי הקטגוריות

תאריך: 04.10.2026. רמת סיכון L2 — שינוי חי ממוקד בפריט תפריט WordPress. כותב קנוני: WP-CLI `wp menu item update`. קובצי התבנית נקראו ונשמרו אך לא נערכו; גרסת CSS נשארה 2.9.2. משימת־אב: [#74](https://github.com/rynrn/gonorth/issues/74).

## ממצא ושינוי

בדיקת Chrome לפני השינוי מצאה ב־1280px `scrollWidth=1318`, וקישור “אודות” התחיל ב־x=-53. הסיבה: פריט 29 בתפריט 26 ירש את כותרת עמוד הבית הארוכה ותפס כ־375px. שיניתי את תווית הפריט ל־“בית” בלי לשנות את כתובתו `https://gonorth.co.il/`, את כותרת העמוד או את שאר קישורי התפריט. לאחר ניקוי מטמון, אותו רוחב נתן `scrollWidth=1265`, וקישור “אודות” התחיל ב־x=24. פריסת שבעת כרטיסי הקטגוריות נשארה 3+4 ב־1280px.

## גיבוי וחזרה

לפני השינוי יוצאו פריט 29 והמטא שלו ל־`C:/dev/gonorth/backups/rollback/2026-10-04-issue-81/`, יחד עם עותקים מדויקים של `header.php`, `gonorth.css` ו־`functions.php`. `post_title` המקורי של פריט 29 היה ריק, ולכן WordPress ירש את כותרת העמוד. לשחזור התנהגות זו:
```bash
ssh gonorth "wp post update 29 --post_title='' --path=/var/www/gonorth --allow-root"
ssh gonorth "wp cache flush --path=/var/www/gonorth --allow-root && wp eval 'wp_cache_clear_cache();' --path=/var/www/gonorth --allow-root"
```
לא נדרש שחזור.

## בדיקות קבלה

- בדפדפן Chrome נבדקו רוחבים 375, 390, 768, 1024, 1280, 1440. בכל רוחב `scrollWidth<=innerWidth`; קישורי הניווט הגלויים בתחום המסך. ב־375/390 שבעת הכרטיסים ברוחב 320px ונראים; ב־1280 פריסת 3+4 נשמרה. לכל שבעת הכרטיסים קישור פעיל.
- בגישה ראשונה עם פרופיל Chrome נקי הופיעה הודעת העוגיות. הקידום העונתי האמיתי כבר פג במסגרת #71; הזרקת DOM זמנית בדפדפן בלבד עם אותו מבנה `gn-seasonal-promo` לא יצרה גלישה באף אחד משבעת רוחבי הבדיקה, כולל 640px כמבחן reflow המקביל בקירוב לזום 200% מ־1280px.
- ב־390 וב־640 פתיחת ההמבורגר עם מקש Enter העבירה את `aria-expanded` ל־true ואת `aria-hidden` ל־false. כל שבעת קישורי התפריט הנפתח נשארו בתחום המסך, ו־Tab העביר את המיקוד לקישור הראשון.
- בדיקה חזותית של צילומי 1280 ומובייל 390 אישרה תפריט וכותרת קריאים ללא חיתוך. הבית נשאר HTTP 200. לא נמדדה רגרסיה ב־#72.

מקור: [#81](https://github.com/rynrn/gonorth/issues/81), קובצי התבנית החיים ועמוד הבית הציבורי, נבדקו 04.10.2026. צילומים ופקודות בדיקה שמורים בתיקיית הגיבוי לעיל. אין תלות בנתוני חשבון חיצוני.

```yaml
status: complete
scope: "ניווט הבית, פריט תפריט 29, שבעת כרטיסי הקטגוריות"
risk_level: L2
canonical_writer: "WordPress WP-CLI menu item update"
findings:
  - "כותרת עמוד הבית הארוכה גרמה לגלישה אופקית ב-1280px"
sources:
  - url: "https://github.com/rynrn/gonorth/issues/81"
    checked_at: "2026-10-04"
  - url: "https://gonorth.co.il/"
    checked_at: "2026-10-04"
changes_made:
  - "תווית פריט 29 עודכנה ל-בית"
unchanged:
  - item: "כתובת הפריט, כותרת העמוד, קובצי התבנית ופריסת 3+4"
    reason: "לא נדרש שינוי נוסף לפתרון הגלישה"
validation:
  - check: "Chrome בשישה רוחבים, מקלדת, עוגיות וסימולציית באנר"
    result: pass
    evidence: "מדידות וצילומים בתיקיית הגיבוי"
snapshot: "C:/dev/gonorth/backups/rollback/2026-10-04-issue-81/"
rollback: "wp post update 29 --post_title='' ואז ניקוי שני המטמונים"
blockers: []
remaining_risks: []
issue_recommendation: close
```
