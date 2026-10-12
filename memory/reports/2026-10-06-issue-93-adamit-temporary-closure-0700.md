# #93 — פארק אדמית ומערת קשת: סגירה זמנית לפי קק״ל

תאריך בדיקה: 06.10.2026. רמת סיכון L1. הכרטיס: https://github.com/rynrn/gonorth/issues/93. לא נוצרה רשומת WordPress, לא הועלתה תמונה ולא שונה האתר.

[דף פארק אדמית הרשמי של קק״ל](https://www.kkl.org.il/travel/trips/adamit_park/) מפרסם בהודעות למטיילים כי הפארק ייסגר לציבור החל מ־4.10.2026 למשך שלושה שבועות עקב עבודות תשתית. נכון ל־6.10.2026 חלון הסגירה טרם חלף. קק״ל מתארת את מערת קשת כחלק מהפארק ומסלול ההליכה היוצא מחניונוף; לכן אין להציג כרגע את הצמד כיעד פתוח בלי אימות חדש.

נבדק גם [דף החניונים של קק״ל](https://www.kkl.org.il/travel/parking_lot_admit/): הוא מתאר הגעה מכביש 8993 ומקשר לניווט, אך אינו מבטל את הודעת הסגירה החדשה. המידע השימושי על שירותים בדפים אינו עקבי, ולכן לא מועתק לשדה דינמי. לא הועתקו תמונות של קק״ל משום שלא נמצא רישיון להעתקה. GPS מדויק לכניסת המבקרים ותמונה מורשית עדיין חסרים.

שורת מועמד #118 ב־places-to-add.md עודכנה ל״מושהה״ עם הסיבה ותאריך הבדיקה. לאחר תום חלון הסגירה המשוער יש לבדוק הודעת פתיחה עדכנית אצל קק״ל, לחזור על בדיקת כפילות, לאמת נקודת התחלה ותמונה מורשית, ורק אז להכין רשומת pending.

```yaml
status: partial
scope: "Adamit Park/Keshet Cave candidate #118 status review"
risk_level: L1
canonical_writer: "places-to-add.md"
findings:
  - "KKL announces closure from 2026-10-04 for three weeks due to infrastructure work"
sources:
  - {url: "https://www.kkl.org.il/travel/trips/adamit_park/", checked_at: "2026-10-06"}
  - {url: "https://www.kkl.org.il/travel/parking_lot_admit/", checked_at: "2026-10-06"}
changes_made:
  - "Candidate #118 marked temporarily paused in places-to-add.md"
unchanged:
  - {item: "WordPress and media", reason: "Official closure announcement and missing GPS/photo license"}
validation:
  - {check: "Queue row exact replacement", result: pass, evidence: "candidate #118 retains one row"}
snapshot: "Previous row recorded in this report: ממתין — מחקר #93; GPS כניסה ותמונה מורשית"
rollback: "Restore previous #118 status only after confirming latest KKL opening notice"
blockers:
  - "Fresh operator reopening confirmation after closure window"
remaining_risks:
  - "Three weeks is planned duration, not proof of exact reopening date"
issue_recommendation: keep-open
```