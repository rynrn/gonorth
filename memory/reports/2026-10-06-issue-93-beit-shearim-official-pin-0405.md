# #93 — נקודת ניווט רשמית לגן לאומי בית שערים

תאריך: 06.10.2026 04:05 (ישראל). המשך למחקר 12 המועמדים; בדיקה בלבד, ללא שינוי באתר או ברשומת מקום.

[רשות הטבע והגנים](https://www.parks.org.il/reserve-park/%D7%92%D7%9F-%D7%9C%D7%90%D7%95%D7%9E%D7%99-%D7%91%D7%99%D7%AA-%D7%A9%D7%A2%D7%A8%D7%99%D7%9D/) מפרסמת במפורש בהוראות ההגעה ״וויז: 32.7027036,35.1275862״. זו נקודת ניווט שהמפעיל עצמו מספק למבקרים, ולא הוכחה למיקום דלת מסוימת או לכל אחת ממערות האתר. אותו מקור מתאר הגעה ברכב לחניון ליד מרכז השירות למטייל, ומציין שבמבואת הכניסה מתבצע שיפוץ. אין להעתיק שעות, מחירים או מגבלות מן הדוח לשדות חיים בלי בדיקה חדשה דרך place-facts סמוך ליצירה.

צילום מועמד: [חצר הכניסה למערת רבי יהודה הנשיא](https://commons.wikimedia.org/wiki/File:Catacomb_no._14,_The_Cave_of_Rabbi_Yehuda_HaNasi.jpg), צילום Davidbena משנת 2019, יצירה עצמית ב־Commons, CC BY-SA 4.0. ייחוס נדרש: Davidbena, קישור לקובץ, קישור לרישיון וציון שינוי אם יבוצע. תיאור הקובץ מתאים לאתר, אך הצילום טרם נבדק חזותית כאן ולא נבחר כתמונה ראשית. צילום מערה אינו תמונת שער הכניסה הראשי; אין לסמן אותו כך.

לפני יצירת כרטיס אישור: בדיקת התמונה בעין, חיפוש כפילות חוזר בכל סטטוסי gd_place, אימות עובדות דינמיות דרך place-facts, בניית טיוטת תוכן בעברית ובדיקת `tools/validate-place-records.py`. כל רשומה חדשה תישאר pending בהתאם ל־AGENTS.md ול־gn-place. ציפורי נשארת עם הנחיית הגעה בשם האתר ובכביש 79, בלי קואורדינטות רשמיות מדויקות; לא להסיק GPS ממרכז הגן.

```yaml
status: partial
scope: "official navigation point and one licensed photo candidate for Beit Shearim"
risk_level: L0
canonical_writer: "none"
findings:
  - "Official visitor navigation coordinates: 32.7027036,35.1275862"
  - "Commons photo candidate has explicit CC BY-SA 4.0 license"
sources:
  - {url: "https://www.parks.org.il/reserve-park/גן-לאומי-בית-שערים/", checked_at: "2026-10-06"}
  - {url: "https://commons.wikimedia.org/wiki/File:Catacomb_no._14,_The_Cave_of_Rabbi_Yehuda_HaNasi.jpg", checked_at: "2026-10-06"}
changes_made:
  - "This local report and memory status only"
unchanged:
  - {item: "WordPress and place-facts", reason: "Photo visual review and listing approval are pending"}
validation:
  - {check: "Read official directions and Commons license", result: pass, evidence: "linked primary pages above"}
snapshot: "not-required"
rollback: "delete this local report and its memory entry"
blockers:
  - "Photo needs visual review before selection"
  - "Fresh duplicate check and approval card before creating pending listing"
remaining_risks:
  - "Official coordinate is a visitor navigation point, not a specific doorway"
issue_recommendation: keep-open
```
