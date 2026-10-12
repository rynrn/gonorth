# #86 — מדידת ביצועים חוזרת לתבניות פנים

תאריך: 06.10.2026. כרטיס: https://github.com/rynrn/gonorth/issues/86. משימת אב: https://github.com/rynrn/gonorth/issues/74.

## שיטה ותוצאה

בוצעו שלוש בקשות HTTPS סדרתיות לכל אחת משלוש תבניות, עם Accept-Encoding gzip, br וללא פרמטר עוקף מטמון. TTFB כולל DNS, TLS ורשת של מכונת הבדיקה; זה אינו זמן PHP או Core Web Vitals של משתמשים. המשקל הדחוס הוא גוף תגובת HTML כפי שהתקבל, ולא סכום כל המשאבים. נתונים מלאים: memory/data/2026-10-06-issue-86-baseline.json.

| תבנית | HTTP | חציון TTFB | חציון HTML בהעברה | HTML לאחר פריסה |
|---|---:|---:|---:|---:|
| ארכיון | 200 | 694ms | 135KB | 500KB |
| מקום | 200 | 757ms | 140KB | 502KB |
| כתבה | 200 | 672ms | 135KB | 479KB |

בשלושת סוגי הדף נוספו נכסי WooCommerce, GeoDirectory ו־Bootstrap, אך עצם טעינתם אינה הוכחה שאפשר להסירם בלי פגיעה. לא נערך עדיין ניסוי coverage או אינטראקציה שמוכיח היעדר תלות.

## קבצים סטטיים

בדיקת GET נפרדת ב־06.10, עם בקשת gzip/br, מצאה:

| נכס | גודל בהעברה | Content-Encoding | Cache-Control |
|---|---:|---|---|
| /wp-includes/css/dist/block-library/style.min.css | 141KB | none | חסר |
| /wp-content/plugins/geodirectory/vendor/ayecode/wp-ayecode-ui/assets-v5/js/bootstrap.bundle.min.js | 137KB | none | חסר |
| /wp-content/themes/astra/assets/js/minified/frontend.min.js | 26KB | none | חסר |

כל שלוש הבקשות החזירו 200 עם ETag, כך שאין לומר שאין מטמון כלל; חסרה מדיניות Cache-Control מפורשת והעברת הקבצים אינה דחוסה במדגם. שרת התגובה הוא Nginx 1.24.0. שינוי ההגשה של קבצים סטטיים דורש בדיקת תצורת Nginx, ולא שינוי .htaccess של WordPress. המשתמש בחר לדחות שינויי ROOT כרגע; לא בוצע שינוי שרת.

## המשך ובדיקות

כאשר שינוי תצורת שרת יהיה בתחום העבודה: גיבוי קובץ ההגדרות, בדיקת nginx -t, הפעלת gzip או Brotli לקובצי CSS/JS, Cache-Control מותאם לכתובות גרסה, reload מדורג, GET חוזר בשלוש התבניות ובדיקת נכס עם גרסה חדשה. חשוב לבדוק ש־HTML ישן לא מפנה לנכס Autoptimize שנמחק. בנפרד יש לבצע coverage ל־WooCommerce/GeoDirectory/Bootstrap לפני dequeue לפי תבנית, ולבדוק חיפוש, מפות, טפסים, מובייל RTL ונגישות. מדדי שדה LCP/INP/CLS אינם זמינים כעת; אין להסיק מהמדידה הזו ציון PSI או אחוזון 75.

מקורות מקומיים: memory/data/2026-10-06-issue-86-baseline.json; memory/data/2026-10-06-issue-86-static-headers.json; דוח הביצועים מ־26.09.2026. מקורות ציבוריים שנבדקו ב־06.10: https://gonorth.co.il/places/ ; https://gonorth.co.il/places/%d7%a7%d7%99%d7%90%d7%a7-%d7%9b%d7%a0%d7%a8%d7%aa/ ; https://gonorth.co.il/tours-kayaks-north-israel/.

חוזה שינוי: status=partial; risk_level=L0; canonical_writer=none; changes_made=דוח ונתוני מדידה בלבד; validation=9 בקשות HTML ו־3 בדיקות נכס סטטי; snapshot=not-required; rollback=not-required; issue_recommendation=keep-open.
