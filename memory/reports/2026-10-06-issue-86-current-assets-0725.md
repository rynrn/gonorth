# #86 — מדידת רשת עדכנית לשלוש תבניות ותקציבי נכסים

תאריך: 06.10.2026 07:25 (ישראל). כרטיס: https://github.com/rynrn/gonorth/issues/86. משימת אב: https://github.com/rynrn/gonorth/issues/74.

## שיטה

Chromium/Chrome מקומי ב־headless דרך Playwright, שלוש ריצות סדרתיות לכל תבנית. כל ריצה פתחה browser context נקי עם viewport ‏390×844 ו־deviceScaleFactor=1, ביטלה מטמון דרך Chrome DevTools, ניווטה ל־DOMContentLoaded, המתינה 3 שניות, גללה לתחתית והמתינה עוד 3 שניות. נאספו PerformanceResourceTiming רק עבור אותו origin. `transferSize` כולל תקורת רשת; זהו נפח בקשות CSS/JS/תמונות שנטענו במדגם ולא מדד שדה או זמן ציור. לא נאספו כאן ביצועי משתמשים אמיתיים, LCP/INP/CLS או תגובות צד שלישי. הקוד החוזר: `memory/scripts/collect-issue86-assets.cjs`.

הכתובות שנבדקו ב־06.10.2026: https://gonorth.co.il/places/ ; https://gonorth.co.il/places/%d7%a7%d7%99%d7%90%d7%a7-%d7%9b%d7%a0%d7%a8%d7%aa/ ; https://gonorth.co.il/tours-kayaks-north-israel/ . כל 9 הניווטים החזירו HTTP 200; בכל ריצה מספר המשאבים וסכומי הקטגוריות היו זהים, ולכל המשאבים שנכללו `transferSize>0`.

| תבנית | CSS בהעברה | JS בהעברה | תמונות בהעברה | תמונה מרבית | תקציב |
|---|---:|---:|---:|---:|---|
| ארכיון | 347,885B | 463,367B | 660,596B | 123,766B | כל 4 הספים עברו |
| מקום | 352,137B | 468,467B | 87,136B | 75,170B | כל 4 הספים עברו |
| כתבה | 261,739B | 221,757B | 82,060B | 70,094B | כל 4 הספים עברו |

המדידה מאשרת שהחריגה ההיסטורית של תמונת הטנק ב־809,338B אינה קיימת במדגם הארכיון הנוכחי. תמונת הכרטיס החדשה נטענה בגרסת 400×300 מורשית; תמונת הארכיון הכבדה ביותר כעת היא תמונת שירי פסטה ב־123,766B. המדגם הישן כלל 8 תמונות בארכיון והחדש 14, ולכן אין להציג את ההפרש בסך התמונות כשיפור שנגרם משינוי אחד בלי בקרה נוספת. שלוש מדידות זהות אינן מבטיחות שתבניות או תכנים אחרים עומדים בתקציב.

ראיות גולמיות: `memory/data/2026-10-06-issue-86-current-assets-0730.json`, `...-0732-b.json`, `...-0733-c.json`. `node --check` עבר לסקריפט; כלי `check-asset-budgets.py` החזיר exit 0 לשלושת הקבצים. לא שונה האתר.

#86 נשאר פתוח: תנאי הקבלה לדחיסה ומטמון של נכסי Nginx טרם הושלמו בהתאם לדחיית עבודת ROOT; אין נתוני שדה LCP/INP/CLS ואין עדיין ניסוי אינטראקציה חדש לאחר השינויים האחרונים. מדידת HTML לפני/אחרי קודמת ודוח הסרת נכסי הכתבות נפרדים מן המדידה הזאת.

```yaml
status: partial
scope: "three-run mobile cold asset capture for archive/place/article"
risk_level: L1
canonical_writer: "local repository files only"
findings:
  - "All 12 budget checks passed in each of three current captures"
  - "Historical 809338 B tank image absent from current archive capture"
sources:
  - {url: "https://gonorth.co.il/places/", checked_at: "2026-10-06"}
  - {url: "https://gonorth.co.il/places/%d7%a7%d7%99%d7%90%d7%a7-%d7%9b%d7%a0%d7%a8%d7%aa/", checked_at: "2026-10-06"}
  - {url: "https://gonorth.co.il/tours-kayaks-north-israel/", checked_at: "2026-10-06"}
changes_made:
  - "Added repeatable headless Chrome capture script"
  - "Saved three current network snapshots and this report"
unchanged:
  - {item: "live site and Nginx", reason: "read-only measurement; ROOT work deferred"}
validation:
  - {check: "node --check memory/scripts/collect-issue86-assets.cjs", result: pass, evidence: "exit 0"}
  - {check: "three runs of check-asset-budgets.py", result: pass, evidence: "exit 0; zero exceedances each"}
  - {check: "nine public navigations", result: pass, evidence: "all HTTP 200, no zero transfer entries"}
snapshot: not-required
rollback: "Remove the new local script and data if measurement method changes; no live state changed"
blockers:
  - "Static compression/Cache-Control requires deferred ROOT work"
remaining_risks:
  - "No field Core Web Vitals; one URL per template; mobile viewport only"
issue_recommendation: keep-open
```
