# #71 — סגירת קידום סוכות 2026 ושימור עמוד קבוע

תאריך ביצוע: 04.10.2026. רמת סיכון: L2 — שינוי מצומצם בתוכן חי ובמטא של עמוד WordPress יחיד. ממשק הכתיבה הקנוני: WordPress באמצעות WP-CLI / `wp_update_post` ו-`update_post_meta`; לא בוצעה כתיבה ישירה למסד הנתונים.

## מקורות שנבדקו

- [לוח Hebcal לישראל — שמיני עצרת תשפ״ז](https://www.hebcal.com/holidays/shmini-atzeret?i=on), נבדק 04.10.2026: החג בישראל הסתיים בצאת 03.10.2026.
- [פרסום הפנינג אינדי פארק לשנת 2026](https://www.indipark.co.il/הפנינגסוכות), נבדק 04.10.2026: תאריכי 27.09–01.10.2026 ושעות 10:00–17:00.
- [כרטיס #71](https://github.com/rynrn/gonorth/issues/71), [כרטיס האב #44](https://github.com/rynrn/gonorth/issues/44), נבדקו 04.10.2026.
- תוכן ומטא עמוד WordPress מספר 441, יוצאו לפני השינוי ב־04.10.2026. מנגנון הקידום המקומי והחי: `class-gonorth-seasonal-hubs.php`.

## ממצא ושינוי

המנגנון לא הסיר את הקידום אוטומטית ב־04.10, כי ערך `_gonorth_seasonal_promo_end=2026-10-04` נבדק באופן כולל. תאריך הסיום עודכן ל־`2026-10-03`. לכן אותו מנגנון מפסיק להציג את מקטע `gn-seasonal-promo` בבית, באזור ובמדריכים, ללא שינוי בקישורים קבועים או בקוד התוסף.

תוכן עמוד 441 נערך בלשון עבר; בראשו הודעה שסוכות 2026 הסתיים, ופרטי ההפנינג ושעות החג מסומנים כארכיון שאינו מתאים לתכנון ביקור כיום. כתובת העמוד, סטטוס פרסום, H1, Title, Meta, canonical וקישורים קבועים לא שונו. המדריך מיועד לרענון ב־2027 במסגרת #65; לא נוצר עמוד נוסף.

## גיבוי וחזרה לאחור

הייצוא לפני השינוי, התוכן המוצע וסקריפטים שמורים ב־`C:/dev/gonorth/backups/rollback/2026-10-04-issue-71/` (תיקייה מקומית שאינה מנוהלת ב־Git). עותק פרטי לצורך הרצת WP-CLI נמצא בשרת תחת `/home/claude/.local/share/gonorth-issue71/`. קובצי התוכן הועברו ונבדקו עם SHA-256; שני סקריפטי PHP עברו `php -l`. סקריפט העדכון דרש התאמה מדויקת לתוכן ולמטא לפני שינוי, ואימת קריאה חוזרת אחרי שינוי.

אם נדרש שחזור, להריץ:
```bash
ssh gonorth "wp eval-file /home/claude/.local/share/gonorth-issue71/issue71-rollback.php --path=/var/www/gonorth --allow-root"
ssh gonorth "wp cache flush --path=/var/www/gonorth --allow-root && wp eval 'wp_cache_clear_cache();' --path=/var/www/gonorth --allow-root"
```
סקריפט השחזור מסרב לדרוס תוכן שנערך שוב בינתיים.

## אימות לאחר השינוי

- WordPress: עמוד 441 עדיין `publish`, אותו slug, תאריך סיום קידום `2026-10-03`; תוכן ומטא נקראו חזרה.
- האתר הציבורי: הבית, `/region/galil/`, `/atrakziot-leyeladim-batzafon/` ועמוד סוכות החזירו HTTP 200. בארבעת העמודים לא נמצא מקטע `<aside class="gn-seasonal-promo...">` פעיל. סמני CSS למחלקה נשארו בבית אך אינם באנר.
- עמוד סוכות: HTTP 200; `robots=index,follow`; canonical עצמי `https://gonorth.co.il/sukkot-batzafon/`; H1 יחיד `סוכות בצפון`; Title ותיאור מטא קיימים ותקינים; הודעת הארכיון ושעות 2026 המסומנות כארכיון נצפו ב־HTML הציבורי.
- `/page-sitemap.xml` החזיר 200 וכלל את כתובת סוכות. כל 19 הקישורים הפנימיים בתוכן נשמרו בדיוק.

אין מדדי דירוג חדשים מ־Search Console; מדידה נותרה ב־#46.

```yaml
status: complete
scope: "עמוד סוכות 441 וקידום gn-seasonal-promo"
risk_level: L2
canonical_writer: "WordPress WP-CLI: wp_update_post + update_post_meta"
findings:
  - "הקידום נותר פעיל ב-04.10 בגלל תאריך סיום כולל"
sources:
  - url: "https://www.hebcal.com/holidays/shmini-atzeret?i=on"
    checked_at: "2026-10-04"
  - url: "https://www.indipark.co.il/הפנינגסוכות"
    checked_at: "2026-10-04"
  - url: "https://github.com/rynrn/gonorth/issues/71"
    checked_at: "2026-10-04"
changes_made:
  - "ארכוב תוכן 2026 בעמוד 441"
  - "סיום קידום עונתי ב-2026-10-03"
unchanged:
  - item: "כתובת העמוד, סטטוס פרסום וקישורים פנימיים"
    reason: "שמירת הערך הקבוע וה-SEO"
validation:
  - check: "קריאה חוזרת מ-WordPress והצלבה מול HTML ציבורי, מפת אתר וארבע כתובות"
    result: pass
    evidence: "בדיקות 04.10.2026 כמפורט לעיל"
snapshot: "C:/dev/gonorth/backups/rollback/2026-10-04-issue-71/"
rollback: "wp eval-file /home/claude/.local/share/gonorth-issue71/issue71-rollback.php --path=/var/www/gonorth --allow-root; then flush both caches"
blockers: []
remaining_risks:
  - "מידע אירועי 2027 דורש אימות חדש לפני פרסום"
issue_recommendation: close
```
