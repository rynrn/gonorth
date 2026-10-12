## #86 — בסיס מדידה חוזר, 06.10.2026

מדדתי שלוש בקשות לכל אחת מתבניות הארכיון, המקום והכתבה. חציון TTFB שנצפה מהמחשב הזה: 694/757/672ms; חציון HTML בהעברה: 135/140/135KB. אלו מדידות רשת מעבדה, לא Core Web Vitals של משתמשים.

ב־GET תומך gzip/br, שלושה נכסים סטטיים מייצגים (Gutenberg CSS, Bootstrap JS, Astra JS) חזרו ללא Content-Encoding וללא Cache-Control מפורש; יש ETag. Nginx מגיש אותם. אין כאן הוכחה שכל נכס WooCommerce/GeoDirectory מיותר, ולכן לא הוסר enqueue.

דוח ושיטת שחזור המדידה: memory/reports/2026-10-06-issue-86-interior-baseline.md; נתונים: memory/data/2026-10-06-issue-86-baseline.json ו־memory/data/2026-10-06-issue-86-static-headers.json. לא שונה האתר. תיקון דחיסת/מטמון קבצים סטטיים מצריך שינוי תצורת Nginx, שהמשתמש בחר לדחות כרגע. הכרטיס נשאר פתוח לבדיקת שינוי תצורה ורגסיית חיפוש, מפות, טפסים, RTL וביצועים לפני/אחרי.
