# #51 — המשכיות יום כיפור ועשרה בטבת

תאריך: 06.10.2026 07:28 (ישראל). כרטיס: https://github.com/rynrn/gonorth/issues/51. משימות המשך: https://github.com/rynrn/gonorth/issues/64 ו־https://github.com/rynrn/gonorth/issues/68.

לוח ישראל של Hebcal שנבדק ב־06.10 מציין יום כיפור 2026 משקיעת 20.09 עד צאת 21.09, ויום כיפור 2027 משקיעת 10.10 עד צאת 11.10: https://www.hebcal.com/holidays/yom-kippur-2027 . עשרה בטבת 2026 חל ב־20.12; אין occurrence בשנת 2027 האזרחית, והבא חל ב־09.01.2028: https://www.hebcal.com/holidays/asara-btevet-2028 . אין להוסיף 365 ימים לתאריך או ליצור תוכן 2027 לצום שאינו חל בה.

חיפוש ציבורי ב־WordPress REST עבור 'יום כיפור' החזיר רשומה אחת שאינה מדריך ייעודי; ב־SEO-KEYWORDS.md אין מיפוי מילת מפתח למדריך יום כיפור או עשרה בטבת. יום כיפור 2026 כבר חל; אין לפרסם בדיעבד מדריך שמציג שעות או פעילות כמוכחות לתקופה שחלפה. משימת #64 שומרת את הבדיקה לפני יום כיפור 2027. עשרה בטבת 2026 עדיין ממתין לחלון בדיקה בנובמבר ולרענון בשבוע שלפני 20.12, ולכן #51 נשאר פתוח. #68 מתעד את היעדר הצום ב־2027, ולקראת תכנון 2028 יש לבדוק את שני מועדי הצום האפשריים בשנת 2028 האזרחית לפי הלוח, בלי לפתוח כרטיס 2027 מלאכותי.

נוספו קישורים בשני הכיוונים בתגובות GitHub של #51, #64 ו־#68, עם התאריכים והלקחים. לא שונה האתר ולא נוצרו כרטיסים כפולים. הכרטיסים של 2027 נשארים ממתינים לסקירת נובמבר–דצמבר 2026 ולביצוע סמוך למועדים. הוראת ההמשכיות שלהם ל־2028 כבר קיימת בגוף הכרטיסים.

```yaml
status: partial
scope: "2026 Yom Kippur and 10 Tevet continuity to 2027 planning"
risk_level: L1
canonical_writer: "GitHub issue comments and local memory"
findings:
  - "Yom Kippur 2027 tracked by issue 64"
  - "No 10 Tevet in civil 2027; next occurrence January 2028"
sources:
  - {url: "https://www.hebcal.com/holidays/yom-kippur-2027", checked_at: "2026-10-06"}
  - {url: "https://www.hebcal.com/holidays/asara-btevet-2028", checked_at: "2026-10-06"}
  - {url: "https://gonorth.co.il/wp-json/wp/v2/search?search=%D7%99%D7%95%D7%9D%20%D7%9B%D7%99%D7%A4%D7%95%D7%A8&per_page=20", checked_at: "2026-10-06"}
changes_made:
  - "Bidirectional issue comments and this dated report"
unchanged:
  - {item: "live site", reason: "No retroactive holiday facts or unverified operating hours"}
validation:
  - {check: "issue 64/68 duplicate and continuity review", result: pass, evidence: "existing issues and forward clauses inspected"}
  - {check: "public REST search and local keyword search", result: pass, evidence: "no dedicated mapped guide found"}
snapshot: not-required
rollback: "Remove the issue comments only if the continuity mapping is later corrected; site unchanged"
blockers: []
remaining_risks:
  - "10 Tevet 2026 service facts require fresh November/December source checks"
issue_recommendation: keep-open
```
