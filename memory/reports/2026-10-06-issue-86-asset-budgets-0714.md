# #86 — תקציבי נכסים לתבניות פנים

תאריך: 06.10.2026 07:14 (ישראל). כרטיס: https://github.com/rynrn/gonorth/issues/86. משימת אב: https://github.com/rynrn/gonorth/issues/74.

הוגדרו תקציבי העברה ראשוניים ב־`memory/data/issue-86-asset-budgets.json` וכלי בדיקה חוזרת ב־`memory/scripts/check-asset-budgets.py`. הספים הם שמרניים יחסית למדגם Chrome הקיים מ־06.10, עם מרווח קטן לשינוי תוכן; הם אינם יעד Core Web Vitals ואינם מוכיחים זמן טעינה למשתמשים. `link` סווג כ־stylesheet, `script` כ־JS ו־`img` כתמונה. גופנים ו־`other` לא נכללו בספים אלה ומדווחים בנפרד בדוח המקור. `transferSize` כולל תקורת HTTP ועשוי להיות אפס במטמון; ההשוואה דורשת מדגם רשת קר ותבניות זהות.

| תבנית | CSS נמדד / תקציב | JS נמדד / תקציב | תמונות נמדד / תקציב | תמונה מרבית / סף |
|---|---:|---:|---:|---:|
| ארכיון | 347,864 / 400,000 B | 463,367 / 500,000 B | 1,051,108 / 1,200,000 B | 809,338 / 200,000 B — חריגה |
| מקום | 352,116 / 400,000 B | 468,467 / 500,000 B | 87,136 / 250,000 B | 75,170 / 200,000 B |
| כתבה | 261,718 / 300,000 B | 221,757 / 250,000 B | 82,060 / 250,000 B | 70,094 / 200,000 B |

החריגה היא `/wp-content/uploads/gonorth-listing-illustrations/issue100-overturned-syrian-tank-google.jpg.webp` בארכיון. לפני שינוי מדיה יש לבדוק התאמה למקום, זכויות, גודל תצוגה ו־srcset ולבצע מדידת לפני/אחרי. המדגם כולל עמוד אחד לכל תבנית ואינו מייצג את כל הרשומות או ציון PageSpeed. לא בוצע שינוי באתר. הרצת הכלי על המדגם החזירה exit 1 כמצופה בשל חריגה אחת; `py_compile` עבר.

עדיין חסרים ל־#86 מדידות לפני/אחרי מלאות לשלוש תבניות, מדדי שדה LCP/INP/CLS אם קיימים, ודחיסה/Cache-Control לקבצים סטטיים ב־Nginx. עבודת ROOT נדחתה בהחלטת המשתמש. הכרטיס נשאר פתוח.

מקור המדגם: `memory/data/2026-10-06-issue-86-geo-resource-coverage-after.json`, נאסף ב־06.10.2026 אחרי הסרת נכסי GeoDirectory בכתבות. עמודי הבקרה הציבוריים בדוח המדידה: https://gonorth.co.il/places/ ; https://gonorth.co.il/places/%d7%a7%d7%99%d7%90%d7%a7-%d7%9b%d7%a0%d7%a8%d7%aa/ ; https://gonorth.co.il/tours-kayaks-north-israel/.

```yaml
status: partial
scope: "asset budgets and repeatable checker for archive/place/article"
risk_level: L1
canonical_writer: "local repository files"
findings:
  - "One 809338 B archive image exceeds the 200000 B single-image budget"
sources:
  - {url: "memory/data/2026-10-06-issue-86-geo-resource-coverage-after.json", checked_at: "2026-10-06"}
  - {url: "https://gonorth.co.il/places/", checked_at: "2026-10-06"}
changes_made:
  - "Added memory/data/issue-86-asset-budgets.json"
  - "Added memory/scripts/check-asset-budgets.py"
unchanged:
  - {item: "live site and Nginx", reason: "measurement and local policy only; ROOT work deferred"}
validation:
  - {check: "python -m py_compile memory/scripts/check-asset-budgets.py", result: pass, evidence: "exit 0"}
  - {check: "python memory/scripts/check-asset-budgets.py memory/data/2026-10-06-issue-86-geo-resource-coverage-after.json", result: pass, evidence: "exit 1 by design: exactly one budget exceedance"}
snapshot: not-required
rollback: "Remove the two new local files if budgets are revised; no live state changed"
blockers:
  - "Nginx static compression and Cache-Control require deferred ROOT work"
remaining_risks:
  - "Single-page samples and no field Core Web Vitals"
issue_recommendation: keep-open
```
