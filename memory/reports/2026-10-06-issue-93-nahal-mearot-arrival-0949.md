# #93 — נחל המערות: בדיקת הגעה והגבלת שביל

תאריך: 06.10.2026, 09:49 ישראל. סיכון L1 — תיעוד מקומי בלבד; לא שונו WordPress או מדיה.

[דף שמורת נחל המערות של רט״ג](https://www.parks.org.il/reserve-park/שמורת-טבע-נחל-המערות/) נשלף כעת בהצלחה מהאתר הרשמי. הוא מתאר ביקור במערות האדם הקדמון ומציין כי שביל 4X4 מהשמורה מזרחה לכרם מהר״ל סגור בשני צדדיו לצורך שיקום; הליכה רגלית אינה מנועה לפי ההודעה. זו הגבלה על שביל מסוים, לא הודעת סגירה של כל השמורה. בדף ההגעה רט״ג מכוונת מכביש 4 לכניסה שבין גבע כרמל לעין כרמל; מכיוון צפון אין פנייה מזרחה לשמורה בגלל קו הפרדה לבן, ולכן יש להגיע מכיוון גבע כרמל. רט״ג מזהירה ש־Waze עלול להפנות מכרם מהר״ל בטעות. אין להעתיק נקודת GPS או להפעיל ניווט אוטומטי לפני זיהוי מיקום הכניסה הנכון; דגל הסתרת ניווט הוא אפשרות אם משתמשים בסמן כללי למתחם.

נמצא [צילום מרכז המבקרים מ־18.10.2016](https://commons.wikimedia.org/wiki/File:Nahal_Mearot_-_visitor_center.jpg), ד״ר אבישי טייכר (Avi1111), ברישיון [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/). זהו מועמד לתמונה בלבד: יש לבדוק אותו חזותית מול חלופות, ולציין שהתמונה היסטורית ואינה מאמתת מצב שבילים כיום. טרם נבחרה תמונה ולא הועלה קובץ.

המשך נדרש: לזהות סמן כניסה/מרכז מבקרים מהימן ולוודא שאינו מפנה לכרם מהר״ל, לבדוק צילום מתאים חזותית ורישיונו, לבדוק כפילות מעודכנת בכל סטטוסי WordPress, להכין כרטיס ביקורת, ואז להחליט אם אפשר ליצור רשומה `pending`. אין כרגע רשומה חדשה למועמד 116.

```yaml
status: partial
scope: "Nahal Me'arot candidate #116 arrival and trail notice research"
risk_level: L1
canonical_writer: "local Markdown only"
findings:
  - "Specific 4X4 trail to Kerem Maharal closed; official notice says walking remains possible"
  - "Official route is via Highway 4 between Geva Carmel and Ein Carmel; Waze may misroute via Kerem Maharal"
  - "2016 CC BY-SA 4.0 visitor-center photo identified as candidate, not selected"
sources:
  - {url: "https://www.parks.org.il/reserve-park/שמורת-טבע-נחל-המערות/", checked_at: "2026-10-06"}
  - {url: "https://commons.wikimedia.org/wiki/File:Nahal_Mearot_-_visitor_center.jpg", checked_at: "2026-10-06"}
changes_made:
  - "Dated research report and queue status clarification"
unchanged:
  - {item: "WordPress and media", reason: "entrance marker and photo selection require verification"}
validation:
  - {check: "Official page fetched", result: pass, evidence: "HTTP 200, saved HTML in memory/work/issue93-nahal-mearot-official.html"}
snapshot: not-required
rollback: "Revert local Markdown only"
blockers:
  - "Verified entrance or venue marker and visual photo selection"
issue_recommendation: keep-open
```
