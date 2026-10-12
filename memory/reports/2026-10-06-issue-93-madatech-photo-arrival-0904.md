# #93 — מדעטק: צילום חזית עדכני נמצא; יעד הגעה עדיין טעון הבחנה

תאריך: 06.10.2026, 09:04 ישראל. סיכון L1 לתיעוד מקומי. אין שינוי ב־WordPress או במדיה.

[הנחיות ההגעה של מדעטק](https://www.madatech.org.il/main-info/arrival-ways/) קובעות שכניסת המבקרים והקופות ברחוב שמריהו לוין 25, בעוד חניון מדעטק בבלפור 12. [הנחיות הביקור](https://www.madatech.org.il/main-info/kastah/) מדגישות שאין כניסה רגילה ישירות מהחניון. המפעיל גם מזהיר שיישומי ניווט פועלים חלקית בתקופה הנוכחית. קישור ה־Waze המופיע באתרו מצביע על 32.80955060,34.99568310; דוח [בדיקת ה־GPS הקודם](2026-10-06-issue-93-gps-crosscheck-0354.md) הראה שהנקודה רחוקה משלוש נקודות שער ב־OSM, ולכן אינה מזהה את קופות המבקרים. האתר הפעיל יוצר Waze אוטומטי מקואורדינטת הליסטינג; לא נכניס את נקודת הניווט הזאת ככניסה מאומתת.

נבדקו חזותית שבעה צילומים ברישיון חופשי. נבחר [צילום חזית הבניין ההיסטורי והקופות](https://commons.wikimedia.org/wiki/File:Madatech_The_Science_Museum_Haifa_Avney_Moreshet_9cf10973.jpg), מאת אושרי רייכר / אבני מורשת, צולם ביולי 2026, [CC BY 2.5](https://creativecommons.org/licenses/by/2.5/). בצילום נראים הבניין, המדרגות והקופות, והוא מזהה את המקום טוב יותר מצילום שביל הגן. צילום 1920–1939 שנמצא באותה קטגוריית Commons אינו מתאים להציג את המצב הנוכחי. קובץ התמונה שנבדק נשמר מקומית לצורכי מחקר בלבד ב־memory/work/issue93-madatech/9cf10973.jpg; לא הועלה לאתר.

החלטה: מועמד 110 נשאר ממתין למחקר הגעה/ניווט; חסם התמונה נפתר. נדרש מקור מפעיל שמבהיר יעד רכב אמין או פיתוח יכולת להציג סמן כניסה בלי קישור Waze אוטומטי. ניתן להכין תוכן מראש, אך לא נוצרה רשומה עם מיקום מטעה.

```yaml
status: partial
scope: "Madatech candidate #110 photo and arrival-source review"
risk_level: L1
canonical_writer: "local Markdown only"
findings:
  - "Official entrance Shmaryahu Levin 25 differs from parking at Balfour 12"
  - "Official Waze destination does not identify visitor ticket entrance and navigation warning remains"
  - "July 2026 licensed facade photograph selected after visual review"
sources:
  - {url: "https://www.madatech.org.il/main-info/arrival-ways/", checked_at: "2026-10-06"}
  - {url: "https://www.madatech.org.il/main-info/kastah/", checked_at: "2026-10-06"}
  - {url: "https://commons.wikimedia.org/wiki/File:Madatech_The_Science_Museum_Haifa_Avney_Moreshet_9cf10973.jpg", checked_at: "2026-10-06"}
  - {url: "memory/reports/2026-10-06-issue-93-gps-crosscheck-0354.md", checked_at: "2026-10-06"}
changes_made:
  - "Dated report and candidate status update"
unchanged:
  - {item: "WordPress and media", reason: "Safe visitor-arrival GPS remains unresolved"}
validation:
  - {check: "Official arrival/visitor pages compared", result: pass, evidence: "Entrance and parking are separate"}
  - {check: "Visual image review and Commons license", result: pass, evidence: "2026 facade with ticket area, CC BY 2.5"}
snapshot: not-required
rollback: "Revert only local Markdown changes"
blockers:
  - "Verified driving arrival destination or a tested no-Waze venue-pin mode"
remaining_risks:
  - "Official navigation service may route imperfectly"
issue_recommendation: keep-open
```
