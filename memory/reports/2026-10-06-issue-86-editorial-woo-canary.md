# #86 — הפחתת נכסי WooCommerce בכתבת קיאקים, ניסוי מוגבל

תאריך: 06.10.2026 (ישראל). כרטיס: https://github.com/rynrn/gonorth/issues/86. שינוי חי L2 בדף אחד בלבד: `https://gonorth.co.il/tours-kayaks-north-israel/` (עמוד WordPress ‏436). כותב קנוני: קובץ `functions.php` של תבנית הבת. אין שינויי ROOT או Nginx.

## ממצא והחלטה

מלאי Chrome לפני שינוי הראה נכסי WooCommerce, GeoDirectory ו־Bootstrap בשלוש התבניות. בכתבת המדגם אין רכיב WooCommerce או GeoDirectory גלוי; ברשימת DOM נמצאו רק תגיות הנכס ו־body class. לא הסרנו GeoDirectory או Bootstrap: מדידת coverage של המסך הראשון אינה מוכיחה אי־תלות בכל הדף. הסימולציה ללא נכסי WooCommerce בדף 436 עברה ב־390/1280: ללא pageerror, אותן תמונות וקישורי מקום, ללא גלישה; 900 הפיקסלים הראשונים היו זהים לחלוטין בשני הרוחבים. הבדלי גובה קטנים בהמשך הדף נבדקו בחיתוך חזותי ולא נראתה פגיעה בשורות או בטבלה.

נוסף hook ממוקד ב־`/var/www/gonorth/wp-content/themes/gonorth-child/functions.php` שמסיר בדף 436 בלבד ארבע ידיות CSS ושש ידיות JS של WooCommerce, גם ב־`wp_enqueue_scripts` המאוחר וגם לפני הדפסת נכסי footer. הקובץ המקורי נשמר מקומית ב־`memory/work/issue86-functions-before.php` ובשרת מחוץ לתיקיית האתר ב־`/home/claude/issue86-functions-before.php`. לפני ההחלפה בוצעו `cmp` לקובץ החי מול העותק, `php -l` למועמד, החלפה אטומית ו־`wp cache flush`.

## מדידות לפני/אחרי

אותו סקריפט HTTP, שלוש בקשות סדרתיות לכל תבנית, חציון. המספרים הם מעבדה מהמחשב הזה, ללא מדדי שדה. TTFB כולל רשת ו־TLS ולכן שינוי של עשרות מילישניות אינו מיוחס לקוד.

| תבנית | TTFB לפני→אחרי | HTML בהעברה לפני→אחרי |
|---|---:|---:|
| ארכיון | 694→679ms | 135,161→135,555B |
| מקום | 757→724ms | 140,436→140,525B |
| כתבה | 672→657ms | 135,186→129,877B |

Chrome, פרופיל 390px ללא מטמון בין דפים: בכתבה מספר קובצי CSS מקומיים ירד מ־14 ל־10 ומספר קובצי JS מקומיים מ־15 ל־9. CSS בהעברה ירד מ־352,116 ל־329,301B (22,815B); JS מ־463,367 ל־423,976B (39,391B). יחד 62,206B פחות בהעברת נכסי CSS/JS במדגם. בארכיון ובדף המקום הספירות והמשקלים נשארו זהים. מדידת Resource Timing היא מדידת הדפדפן ואינה סכום כל העמוד, והיא תלויה במטמון/תנאי רשת.

בדיקות חיות: 200 בכל ארבע התבניות כולל הבית; אין pageerror; בכתבה רוחב המסמך 390/1280 בדיוק, חמש תמונות נטענות, וניווט בלחיצה לקישור מקום מתוך `main` עבד בשני הרוחבים. צילום המסך החי של 900 הפיקסלים הראשונים היה זהה פיקסל־לפיקסל לסימולציה ללא WooCommerce. checksum חי תואם למועמד: `a3699dcfad6af4e2764a6e192e560618354f6c349f2fc70421a686474ad78d4c`. סקריפט `memory/work/issue86-rollback.ps1` נבדק ב־`-CheckOnly` וה־guard עבר. להפעלת שחזור במקרה תקלה: `pwsh -NoProfile -File memory/work/issue86-rollback.ps1` מתוך שורש המאגר; הסקריפט מסרב לפעול אם הקובץ החי השתנה מאז.

## גבולות והמשך

הכרטיס נשאר פתוח: אין עדיין דחיסה/Cache-Control מפורשים לחלק מהנכסים הסטטיים שמגיש Nginx; המשתמש דחה שינויי ROOT. נדרש גם מחקר תלות לרכיבי GeoDirectory/Bootstrap לפני dequeue רחב, בדיקת אינטראקציות מלאה אם מרחיבים את האופטימיזציה, ולבסוף LCP/INP/CLS מנתוני שדה כשיש מדגם. שינוי זה הוא canary לדף אחד ולא פתרון לכל תבנית הכתבה.

מקורות וראיות מקומיים: `memory/data/2026-10-06-issue-86-baseline.json`, `memory/data/2026-10-06-issue-86-after-editorial.json`, `memory/data/2026-10-06-issue-86-resource-coverage.json`, `memory/data/2026-10-06-issue-86-resource-coverage-after.json`, `memory/data/2026-10-06-issue-86-woo-simulation.json`, צילומים ב־`memory/work/issue86-*-after-live.png`.

```yaml
status: partial
scope: "WooCommerce assets on WordPress page 436; archive/place/home regression"
risk_level: L2
canonical_writer: "child theme functions.php via SSH"
findings:
  - "WooCommerce assets were loaded on an editorial page with no commerce UI"
  - "One-page canary removed four CSS and six JS files, saving 62,206 transfer bytes in Chrome sample"
sources:
  - {url: "https://gonorth.co.il/tours-kayaks-north-israel/", checked_at: "2026-10-06"}
  - {url: "https://gonorth.co.il/places/", checked_at: "2026-10-06"}
  - {url: "https://gonorth.co.il/places/קיאק-כנרת/", checked_at: "2026-10-06"}
changes_made:
  - "Scoped dequeue hook in live child-theme functions.php"
  - "This report and memory status"
unchanged:
  - {item: "Nginx configuration", reason: "User deferred ROOT changes"}
  - {item: "GeoDirectory and Bootstrap assets", reason: "Full interaction dependency unproven"}
validation:
  - {check: "php -l", result: pass, evidence: "No syntax errors detected"}
  - {check: "Chrome 390/1280 with page navigation", result: pass, evidence: "200, no errors, no horizontal overflow"}
  - {check: "three-run HTTP and Chrome resource timing", result: pass, evidence: "before/after data files above"}
  - {check: "rollback guard dry run", result: pass, evidence: "Rollback guard passed; no files changed"}
snapshot: "memory/work/issue86-functions-before.php"
rollback: "pwsh -NoProfile -File memory/work/issue86-rollback.ps1"
blockers:
  - "Nginx static compression/cache requires ROOT change currently deferred by user"
remaining_risks:
  - "Field Core Web Vitals unavailable; canary covers one editorial page only"
issue_recommendation: keep-open
```
