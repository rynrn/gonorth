# Issue #83 — הסכמה ומזעור מידע במדידה

תאריך: 04.10.2026  
כרטיס: https://github.com/rynrn/gonorth/issues/83  
משימת־אב: https://github.com/rynrn/gonorth/issues/74

## ממצא ותוצאה

לפני התיקון, דף הבית טען GTM אחרי 20 שניות גם בלי בחירה, ועמוד מדריך טען אותו מיד. בדפדפן נקי נשלחה בקשת GA4 לפני הסכמה, ובכתובת ניסיון עם query נצפה ערך הניסיון בבקשת Analytics. הבאנר הציג רק „הבנתי, סגור” ושמר `gn_cookie_ok=1` בלי מסלול סירוב או שינוי בחירה. מדיניות הפרטיות טענה שאנליטיקה אינה פעילה מאז אפריל 2026. אלה ממצאי רשת וקוד, לא קביעה משפטית.

כעת GTM ו־GA4 נטענים רק כאשר הבחירה השמורה היא `1` או לאחר אישור מפורש. סירוב שומר `0`; שינוי מאישור לסירוב מרענן את הדף ומונע טעינה חדשה של GTM/GA4. כפתור „העדפות פרטיות” גלוי תמיד. האירועים המקומיים נשלחים רק לאחר הסכמה; `page_location` ו־`page_referrer` מוגדרים כ־origin+pathname, בלי query או fragment. האירוע `place_view` נשלח פעם אחת אם האישור ניתן במהלך ביקור בדף מקום. Google Preferred Sources נשאר מותנה באישור.

מדיניות הפרטיות של עמוד #47 עודכנה באופן עובדתי: GA4/GTM, דרך הבחירה והשינוי, וכן משאבי תצוגה שנצפו גם ללא אישור למדידה — Google Fonts, Font Awesome, OpenStreetMap, OneTap ו־Gravatar. היא אינה טוענת שכל משאב צד שלישי נחסם לפני בחירה.

## היקף ושחזור

רמת סיכון L3 בשל שינוי התנהגות מדידה בכל האתר. ההרשאה המשתמשית היא „תמשיך” בעקבות המלצה מפורשת על #83. הכותבים הקנוניים: קובצי GoNorth child theme והרכיב `gonorth-place-data`; מדיניות הפרטיות נכתבה דרך `wp_update_post` (WordPress API). אין כתיבת SQL, שינוי wp-config או שינוי תגיות בתוך GTM. גרסת CSS הסופית 2.9.7.

עותקי טרום שינוי נמצאים ב־`memory/data/2026-10-04-issue-83-prechange/` ובשרת `/home/claude/issue83/*.pre`; נוסח המדיניות המקורי ב־`privacy-post.pre.html`. שחזור: `ssh gonorth 'bash /home/claude/issue83/issue83-rollback.sh'`. הוא מחזיר ארבעה קובצי מקור ואת עמוד #47, מנקה Autoptimize, WP Super Cache ו־WordPress cache. סקריפט השחזור עבר `bash -n` אך לא הופעל כדי לא לבטל את התיקון. diff לעיון: `memory/data/2026-10-04-issue-83-review.patch`.

## ראיות

- `memory/data/2026-10-04-issue-83-baseline-network.json`: לפני שינוי יצאו GTM/GA4 גם בלי בחירה; בעמוד ניסיון GA4 קיבל query. נרשמו רק מארחים/מסלולים וסימון קיום ערך הניסיון, בלי כתובת הבקשה המלאה.
- `memory/data/2026-10-04-issue-83-after-network.json`: בדפדפן נקי ובמצב סירוב אין בקשות GTM/GA4 או SDK Preferred Sources; באישור יש בקשה אחת ל־GTM, GA4 ו־SDK; אחרי ביטול ורענון הבחירה `0` והסקריפטים אינם בדף. בקשות שנצפו לפני הרענון אינן „נמחקות” בדיעבד.
- `memory/data/2026-10-04-issue-83-final-regression.json`: דף הבית המתין 22.5 שניות ללא GTM; אישור באמצע ביקור בדף מקום הפיק `place_view` אחד וטעינת GTM אחת. ערך הניסיון לא הופיע בבקשות GA4. בדיקת ניווט פנימי עם query במפנה הראתה שתי בקשות page_view ללא ערך הניסיון.
- `memory/data/2026-10-04-issue-83-full-inventory-regression.json`: כל 197 כתובות post/page/gd_place מפורסמות החזירו HTTP 200 עם ממשק הבחירה ומנגנון הטעינה המותנה. מדגם שבע כתובות שמור גם ב־`public-regression.json`.
- `php -l` של header.php ו־functions.php עבר; בדיקות WP-CLI הקיימות לאנליטיקה (128 מקומות) ול־Preferred Sources עברו. המדיניות הקנונית נשארה `publish`; בדף הציבורי נראים GA4, OpenStreetMap ו־Gravatar.

## מגבלות והמשך

לא נבדקו הרשאות בחשבון GA4 או הגדרות התגים בתוך ממשק GTM; הבדיקה מבוססת על בקשות רשת אמיתיות ומקור האתר. לאחר אישור, SDK Preferred Sources מוסיף iframe מוסתר במיקום שמייצר גלישה אופקית של 10px ברוחב 390px; כשה־SDK נחסם הגלישה נעלמת. ניסיון תיקון CSS ממוקד לא גבר על `!important` inline של Google ולכן הוסר. זהו ממצא UX נפרד שנפתח כ־https://github.com/rynrn/gonorth/issues/104; אין שינוי כפוי במסגרת פרטיות זו.

ביקורת קוד עצמאית כנדרש ל־L3 עדיין לא נערכה. הקוד, ה־diff, השחזור ותוצאות הרגרסיה מוכנים לעיון. לכן ההמלצה היא להשאיר את #83 פתוח עד ביקורת עצמאית, אף שתנאי ההתנהגות הציבורית נבדקו.

## מקורות חיצוניים שנבדקו ב־04.10.2026

- Google, [Basic consent mode](https://support.google.com/tagmanager/answer/10000067?hl=en-GB) — טעינת תגים אחרי אישור בלבד.
- Google, [GA4 configuration fields](https://developers.google.com/analytics/devguides/collection/ga4/reference/config) — הגדרת page_location/page_referrer; ברירת המחדל היא URL הדף והמפנה.
- Google, [Avoid sending PII](https://support.google.com/analytics/answer/6366371?hl=en) — הימנעות מפרטים אישיים בכתובות ובמדידה.

```yaml
status: partial
scope: "הסכמה, GTM/GA4, Preferred Sources, מדיניות פרטיות ו-197 דפים ציבוריים"
risk_level: L3
canonical_writer: "קובצי child theme / plugin; עמוד #47 דרך wp_update_post"
findings:
  - "לפני בחירה נשלחו בקשות GA4 ו-query מדף ניסיון"
  - "לאחר שינוי, pre/reject/revoke אינם טוענים GTM/GA4; accept טוען ללא query/fragment"
sources:
  - {url: "https://gonorth.co.il/", checked_at: "2026-10-04"}
  - {url: "https://gonorth.co.il/mediniyut-pratiyut/", checked_at: "2026-10-04"}
  - {url: "https://support.google.com/tagmanager/answer/10000067?hl=en-GB", checked_at: "2026-10-04"}
changes_made:
  - "בחירה מפורשת, סירוב, שינוי בחירה וטעינה מותנית"
  - "מזעור כתובות במדידה ועדכון מדיניות פרטיות"
unchanged:
  - {item: "הגדרות GTM/GA4 בחשבון", reason: "אין צורך בשינוי כדי לאכוף טעינה מותנית; טרם נבדקו הרשאות חשבון"}
validation:
  - {check: "רשת בדפדפן בארבעה מצבים", result: pass, evidence: "memory/data/2026-10-04-issue-83-after-network.json"}
  - {check: "מלאי ציבורי", result: pass, evidence: "197/197 HTTP 200 עם בקרה"}
  - {check: "ביקורת קוד עצמאית", result: not-run, evidence: "ממתין לסוקר בלתי תלוי"}
snapshot: "memory/data/2026-10-04-issue-83-prechange/"
rollback: "ssh gonorth 'bash /home/claude/issue83/issue83-rollback.sh'"
blockers:
  - "ביקורת קוד עצמאית נדרשת ל-L3 לפני סגירת הכרטיס"
remaining_risks:
  - "גלישה אופקית 10px של iframe מוסתר של Preferred Sources במובייל לאחר אישור"
issue_recommendation: keep-open
```