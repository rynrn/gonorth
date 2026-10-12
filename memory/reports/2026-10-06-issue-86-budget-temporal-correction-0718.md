# #86 — תיקון זמניות מדגם תמונת הארכיון

תאריך: 06.10.2026 07:18 (ישראל). תיקון ל־`2026-10-06-issue-86-asset-budgets-0714.md`. קובץ מדידת Chrome `2026-10-06-issue-86-geo-resource-coverage-after.json` נוצר ב־05:05, ואילו החלפת תמונת הטנק בכרטיס #91 הושלמה ב־05:37. לכן החריגה של 809,338B שדווחה בתקציב היא **היסטורית**; אין לייחס אותה לארכיון החי כעת. היא נשארת במדגם הישן כדי לשמר השוואת לפני.

בדיקת GET ציבורית חדשה ל־https://gonorth.co.il/places/ החזירה 200. כרטיס המקום #503 מציג ב־HTML את התמונה המורשית `tank-503-cc-by-sa-4-768x576.webp` ואת `srcset` עם 300, 400, 600, 768 ו־1024 פיקסלים. המחרוזת של תמונת Google הישנה אינה מופיעה ב־HTML זה. בקשות HEAD פומביות לגרסאות התמונה החדשה החזירו 200 ו־Content-Length של 26,528B ‏(300), 45,996B ‏(400), 102,822B ‏(600) ו־149,422B ‏(768), כולם מתחת לסף 200,000B לתמונה. זו בדיקת HTML/HEAD; לא נערכה מדידת Chrome מלאה חדשה של כל משאבי הארכיון ולא נקבע איזה גודל הורד בפועל בכל רוחב. כלי הדפדפן המקומי נכשל בהפעלה, ולכן אין טענה למדידת רשת חדשה.

התקציבים והכלי נשארים תקפים למדגמים עתידיים. התראת התקציב היחידה במדגם הישן אינה הוכחה לחריגה נוכחית. #86 נשאר פתוח לאיסוף מדגם רשת חדש בשלוש התבניות וליתר תנאי הקבלה. לא בוצע שינוי באתר.

```yaml
status: partial
scope: "temporal correction and public markup/HEAD verification for archive tank image"
risk_level: L0
canonical_writer: none
findings:
  - "809338 B image alert is historical; sample predates issue 91 image replacement"
  - "Current markup references licensed responsive image variants under 200000 B through 768px"
sources:
  - {url: "memory/data/2026-10-06-issue-86-geo-resource-coverage-after.json", checked_at: "2026-10-06"}
  - {url: "memory/reports/2026-10-06-issue-91-tank-gallery-0537.md", checked_at: "2026-10-06"}
  - {url: "https://gonorth.co.il/places/", checked_at: "2026-10-06"}
changes_made:
  - "Added this correction report"
unchanged:
  - {item: "live site", reason: "read-only verification"}
validation:
  - {check: "public archive GET and four variant HEAD requests", result: pass, evidence: "HTTP 200; current srcset and sizes 26528/45996/102822/149422 B"}
snapshot: not-required
rollback: not-required
blockers:
  - "Browser network capture unavailable in this run"
remaining_risks:
  - "No new full-resource capture or field vitals"
issue_recommendation: keep-open
```
