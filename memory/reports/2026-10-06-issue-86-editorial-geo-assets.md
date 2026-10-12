# #86 — הסרת נכסי GeoDirectory ו־Bootstrap מכתבות מאומתות

תאריך: 06.10.2026 (ישראל). משימת־אב: #74. רמת סיכון L3; כותב קנוני: קובץ child theme `/var/www/gonorth/wp-content/themes/gonorth-child/functions.php` דרך SSH. שינוי זה ממשיך את [דוח WooCommerce](2026-10-06-issue-86-all-editorial-woo.md) ואת [תיקון ה־guard](2026-10-06-issue-86-guardfix.md), בלי לדרוס אותם.

ב־25 הכתבות הקיימות ובעמוד 436 נטענו `ayecode-ui`, `bootstrap-js-bundle` ו־`geodir-js` אף שאין בהם רכיב GeoDirectory. לפני השינוי מדידת Chrome בדף 436 הצביעה על שלושה קבצים בהעברה של 269,802 בתים (717,355 בתים לאחר פריסה). אלה נתוני רשת לקבצים, לא ציון PageSpeed או מדדי שדה.

נוסף תנאי נפרד לנכסי GeoDirectory, עם רשימת 26 מזהים סגורה. אם העורך יוסיף סימון GeoDirectory/AyeCode לתוכן או יגדיר `gn_keep_geodir_assets`, הקבצים יישארו. כתבות עתידיות אינן כלולות אוטומטית. דף הבית כבר הסיר נכסים אלה קודם; ארכיון המקומות, דף מקום, הבלוג ודפים כלליים נשארו מחוץ לשינוי. לא הוסרה פונקציונליות או מדיה מהאתר.

ביקורת עצמאית של orchestrator נתנה אישור מותנה לפריסה מדורגת לאחר ניסוי רשת של 26/26 כתובות ב־390 וב־1280. בעמוד 436 ובכתבה 94 לא היה שינוי פיקסלים; בכתבה 517 הבדלי הצילום תאמו שונות חוזרת של כרטיסי כתבות מתחלפים גם בין שתי טעינות בסיס. הפריסה נעשתה בעמוד 436, אחר כך בשלוש כתבות, ולבסוף ב־25 הכתבות. בכל שלב נעשו snapshot, `php -l`, checksum guard, ניקוי מטמון ו־rollback check-only. במהלך השלב הראשון סקריפט הפריסה החזיר שגיאת CRLF **אחרי** שהקוד הותקן ואומת; הסקריפטים הומרו ל־LF, מצב האתר נבדק לפי SHA-256 והשלבים הבאים הושלמו ללא שגיאה.

בדיקה ציבורית סופית: 26/26 כתובות החזירו 200 בשני רוחבים, ללא JavaScript pageerror, גלילה אופקית, H1 או footer חסרים, וללא שלושת הנכסים. גם נכסי WooCommerce שהוסרו קודם לא חזרו. דף הבית, בלוג, ארכיון, דף מקום, צור קשר ועמוד 436 החזירו 200; הנכסים עדיין נטענים בארכיון, בדף המקום ובדפים שלא נכללו. תפריט המובייל נפתח (`aria-expanded` false→true) וסרגל OneTap נפתח (`onetap-toggle-open`). בדף הבלוג נמדד `scrollWidth=1296` ברוחב 1280; זה דף בקרה שלא נכלל בשינוי, ולכן לא נטען כאן שתוקנה הגלילה שלו.

מדידת Chrome חוזרת בעמוד 436: העברת CSS/JS ירדה מ־753,277 ל־483,475 בתים, בדיוק 269,802 בתים פחות ו־3 בקשות פחות. המדידה היא מעבדה נקודתית; היא אינה מוכיחה שיפור LCP/INP/CLS בשדה או ב־PageSpeed.

קבצים: `memory/data/2026-10-06-issue-86-geo-all-posts-simulation-{390,1280}.json`, `memory/data/2026-10-06-issue-86-geo-visual-samples.json`, `memory/data/2026-10-06-issue-86-geo-baseline-repeat.json`, `memory/data/2026-10-06-issue-86-geo-canary-live.json`, `memory/data/2026-10-06-issue-86-geo-stage3-live.json`, `memory/data/2026-10-06-issue-86-geo-full-live-{390,1280}.json`, `memory/data/2026-10-06-issue-86-geo-unaffected-interactions.json`, `memory/data/2026-10-06-issue-86-geo-resource-coverage-after.json`. Snapshot מקומי לפני השלב האחרון: `memory/work/issue86-functions-before-geo-full.php`; עותק שרת: `/home/claude/issue86-functions-before-geo-full.php`. גרסה חיה SHA-256: `1c9cf74319c9bac42622db408f1e1a19d41a884f45a3e1f8ae3944c14b612152`.

שחזור מדורג ומוגן checksum: `ssh gonorth 'bash /home/claude/issue86-rollback-geo-full.sh'` מחזיר לשלב שלוש הכתבות; אם צריך לבטל את כל השינוי, ממשיכים ב־`issue86-rollback-geo-stage3.sh` ואז `issue86-rollback-geo-canary.sh`. כל סקריפט בודק את hash הקוד החי ואת ה־snapshot לפני החלפה. `--check-only` עבר לשלב הסופי. #86 נשאר פתוח: דחיסת/Cache-Control ב־Nginx נדחו עם עבודת ROOT לפי החלטת המשתמש; מדדי שדה עדיין אינם זמינים, ויש להשלים תקציבי נכסים ואימות נוסף לפי תנאי הכרטיס.

```yaml
status: partial
scope: "26 existing editorial URLs: page 436 and 25 posts"
risk_level: L3
canonical_writer: "child-theme functions.php via SSH"
findings:
  - "Three GeoDirectory/AyeCode/Bootstrap assets loaded on verified editorial URLs without GeoDirectory UI"
  - "269802 transferred bytes and three requests removed on the measured page"
sources:
  - {url: "https://gonorth.co.il/tours-kayaks-north-israel/", checked_at: "2026-10-06"}
  - {url: "https://gonorth.co.il/places/", checked_at: "2026-10-06"}
  - {url: "memory/reports/2026-10-06-issue-86-guardfix.md", checked_at: "2026-10-06"}
changes_made:
  - "Added exact-ID GeoDirectory guard and opt-out meta"
  - "Deployed canary, three-post stage, then full allowlist"
unchanged:
  - {item: "Nginx configuration", reason: "ROOT work deferred by user"}
  - {item: "GeoDirectory templates", reason: "plugin assets needed there"}
validation:
  - {check: "PHP lint, live SHA-256, rollback check-only", result: pass, evidence: "live hash 1c9cf74319c9bac42622db408f1e1a19d41a884f45a3e1f8ae3944c14b612152"}
  - {check: "Public regression 26 URLs x 2 viewports", result: pass, evidence: "memory/data/2026-10-06-issue-86-geo-full-live-{390,1280}.json"}
  - {check: "Unaffected templates and mobile menu/OneTap", result: pass, evidence: "memory/data/2026-10-06-issue-86-geo-unaffected-interactions.json"}
snapshot: "memory/work/issue86-functions-before-geo-full.php"
rollback: "ssh gonorth 'bash /home/claude/issue86-rollback-geo-full.sh'"
blockers:
  - "Nginx compression/cache policy requires deferred ROOT work"
  - "Field LCP/INP/CLS sample unavailable"
remaining_risks:
  - "Future editorial widgets outside raw post content may require gn_keep_geodir_assets opt-out"
issue_recommendation: keep-open
```

