# #86 — הרחבת הפחתת WooCommerce ל־25 כתבות מאומתות

תאריך: 06.10.2026 04:35 (ישראל). כרטיס: https://github.com/rynrn/gonorth/issues/86. הרחבה חיה L3 אחרי [ניסוי בדף אחד](2026-10-06-issue-86-editorial-woo-canary.md). כותב קנוני: `/var/www/gonorth/wp-content/themes/gonorth-child/functions.php`. אין שינוי ROOT או Nginx.

## החלטה ותחום

נאספו דרך WordPress REST כל 25 הכתבות הציבוריות ועמוד הקנרי 436. סריקת Chrome לכל 25 הכתבות מצאה נכסי WooCommerce בכולן, אך לא מצאה רכיב מסחר או GeoDirectory ב־DOM התוכן. בדיקת `post_content` דרך WP-CLI מצאה 25/25 ללא shortcode או בלוק WooCommerce. ביקורת קוד עצמאית של orchestrator אישרה הרחבה מדורגת לכתבות הנוכחיות בלבד; היא בדקה גם את `single.php`, ה־footer וה־sidebar, והזהירה מפני כלל גורף לפוסטים עתידיים.

הקוד משתמש ב־allowlist של 25 מזהי הכתבות שנבדקו, נוסף על עמוד 436. כתבה עתידית שטרם נבדקה שומרת את נכסי WooCommerce. גם בכתבה מאושרת הנכסים נשמרים אם נוספו בתוכן סמני מסחר/טופס/iframe/script או אם הוגדר meta `gn_keep_woocommerce_assets`. אותה רשימת ארבע ידיות CSS ושש ידיות JS מן הניסוי מוסרת רק לאחר מעבר תנאי ה־guard. GeoDirectory, Bootstrap, OneTap, jQuery ונכסי האתר נשארו.

## פריסה מדורגת ועצירה

לפני השלב הראשון נשמר עותק הקובץ הקנרי: `memory/work/issue86-functions-before-batch.php` וגם `/home/claude/issue86-functions-before-batch.php` בשרת מחוץ לאתר. `php -l` עבר לשתי גרסאות המועמד. בשלב א' הורחבה הרשימה ל־431, 432, 517; `cmp` מול snapshot מנע דריסת שינוי מקביל, והקובץ הוחלף אטומית. שלוש הכתבות והבקרות 516 ו־436 החזירו 200; שלוש הכתבות והקנרי ללא Woo, בקרה 516 עדיין עם 4 CSS ו־9 JS של Woo. בדיקת desktop לאחר גלילה עברה, ללא pageerror או גלישה. תנאי העצירה היו 4xx/5xx, שגיאת JS, גלישה, היעלמות תוכן/תחתית או שינוי נכסים בבקרה; אף אחד לא הופיע.

בשלב ב' הורחבה רק רשימת המזהים ל־25. שוב בוצעו `cmp`, ‏`php -l`, החלפה אטומית ו־`wp cache flush`. checksum הקובץ החי אחרי הפריסה: `eaec55cb342a8fe4025925897271a4eeb20fea3567be1aed008964a39c808384`.

## אימות אחרי פריסה

רגרסיית Chrome חיה ב־390 וב־1280 פיקסלים: 26/26 כתובות (25 כתבות והקנרי) בכל רוחב החזירו 200, לאחר גלילה לתחתית. אין pageerror, אין גלישת מסמך, H1 ו־footer קיימים, ולא נטענו נכסי WooCommerce מתוך רשימת היעד. בדף מסלול #517 לחיצה על קישור ״מתחם חמי טבריה״ הגיעה לדף המקום ב־200 בשני הרוחבים. סרגל הנגישות נפתח בכתבה. הבית, הארכיון, דף מקום, /contact/ ו־/blog/ נבדקו כקבוצת בקרה; הם מחזירים 200 וללא גלישת מובייל, והארכיון ודף המקום עדיין טוענים נכסי WooCommerce כבעבר.

החיסכון שנמדד במדויק בדף הקנרי הוא 62,206B בהעברת CSS/JS ו־5,309B בחציון HTML בהעברה, לפי [דוח הניסוי](2026-10-06-issue-86-editorial-woo-canary.md). אין לכפול מספר זה ב־25 ולהציגו כחיסכון תנועה בפועל: מספר צפיות, מטמון ותמהיל נכסים אינם ידועים. בדיקת 25 הכתבות מוכיחה היעדר נכסי היעד ולא מדד שדה.

סקריפט שחזור `memory/work/issue86-batch-rollback.ps1` מחזיר את הקובץ הקנרי אם checksum החי עדיין תואם; `-CheckOnly` עבר בלי שינוי. אם צריך לשחזר גם את ניסוי העמוד הבודד, קיים `memory/work/issue86-rollback.ps1`, אך קודם יש להחזיר את גרסת הקנרי. ראיות: `memory/data/2026-10-06-issue-86-editorial-inventory.json`, `memory/data/2026-10-06-issue-86-editorial-dom-audit.json`, `memory/data/2026-10-06-issue-86-all-posts-simulation.json`, `memory/data/2026-10-06-issue-86-stage1-dom.json`, `memory/data/2026-10-06-issue-86-all25-live-390.json`, `memory/data/2026-10-06-issue-86-all25-live-1280.json`.

הכרטיס נשאר פתוח: לא שונו Nginx, דחיסת קבצים סטטיים או Cache-Control, בהתאם להעדפת המשתמש לדחות ROOT. אין עדיין מדדי שדה LCP/INP/CLS עם מדגם מספיק. GeoDirectory/Bootstrap דורשים מיפוי תלות נפרד לפני כל dequeue.

```yaml
status: partial
scope: "25 existing published posts and page 436; unaffected template regression"
risk_level: L3
canonical_writer: "child-theme functions.php via SSH"
findings:
  - "25 existing posts load WooCommerce assets without commerce content"
  - "All 25 posts now omit target assets; future posts remain unoptimized until reviewed"
sources:
  - {url: "https://gonorth.co.il/wp-json/wp/v2/posts", checked_at: "2026-10-06"}
  - {url: "https://gonorth.co.il/south-kinneret-day-trip/", checked_at: "2026-10-06"}
  - {url: "memory/data/2026-10-06-issue-86-editorial-dom-audit.json", checked_at: "2026-10-06"}
changes_made:
  - "Allowlisted 25 verified post IDs in live child-theme functions.php"
  - "Added content and meta guards for later changes"
  - "This report and memory status"
unchanged:
  - {item: "Nginx compression and cache configuration", reason: "ROOT work deferred by user"}
  - {item: "GeoDirectory, Bootstrap and future posts", reason: "Dependency not proven"}
validation:
  - {check: "independent orchestrator review", result: pass, evidence: "approved allowlist and staged rollout"}
  - {check: "php -l, snapshot cmp, staged rollout", result: pass, evidence: "both stages passed"}
  - {check: "Chrome 26/26 URLs at 390 and 1280 after scroll", result: pass, evidence: "memory/data/2026-10-06-issue-86-all25-live-*.json"}
  - {check: "content link and OneTap toolbar", result: pass, evidence: "live Chrome interactions"}
  - {check: "rollback guard dry run", result: pass, evidence: "Rollback guard passed; no files changed"}
snapshot: "memory/work/issue86-functions-before-batch.php"
rollback: "pwsh -NoProfile -File memory/work/issue86-batch-rollback.ps1"
blockers:
  - "Nginx static compression and Cache-Control require deferred ROOT work"
remaining_risks:
  - "No field Core Web Vitals"
  - "25-page allowlist requires review when new posts are published"
issue_recommendation: keep-open
```
