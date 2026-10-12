# #93 — מוזיאון ינקו דאדא: מקור פעילות נמצא, מיקום מדויק ותמונה עדכנית חסרים

תאריך בדיקה: 06.10.2026. סיכון L0. לא נוצרה רשומת WordPress ולא הועלתה מדיה.

[איגוד המוזאונים ואיקו״ם ישראל](https://www.icom.org.il/museum/1505) מתאר את המוזיאון כפעיל במרכז כפר האמנים עין הוד, עם תערוכת קבע, תערוכות מתחלפות ופעילות לקהל. לפי דף המבקר, חניית המבקרים בכניסה לכפר ונדרשת הליכה של כחמש דקות למוזיאון. לכן קואורדינטת חניה אינה קואורדינטת דלת המוזיאון. [אתר כפר האמנים](https://www.ein-hod.org/en/yanko-dada-museum-2/) מתאר את המוזיאון ואת הקשר למרסל ינקו, אך לא סיפק בשלב זה נקודת כניסה מספרית. אתר המוזיאון שאליו מקשר האיגוד החזיר בתהליך הבדיקה 429, ולכן לא נבדק ישירות; אין להסיק מכך שהוא סגור.

[Commons](https://commons.wikimedia.org/wiki/Category:Janco_Dada_museum) מכיל כמה צילומי מבנה ברישיונות חופשיים. למשל [Janco Dada Museum.JPG](https://commons.wikimedia.org/wiki/File:Janco_Dada_Museum.JPG) מאת Yagasi, ‏CC0, צולם ב־2011, ו[En hod dada museum.jpg](https://commons.wikimedia.org/wiki/File:En_hod_dada_museum.jpg) הוא צילום כניסה משנת 2007 בנחלת הכלל. גיל הצילומים מחייב התאמה חזותית למבנה הנוכחי לפני בחירת תמונת נושא; מקור Commons לבדו אינו מוכיח שהחזית לא השתנתה.

החלטה: מועמד #117 נשאר במחקר. יש לאמת GPS לכניסת הולכי הרגל למוזיאון, להבחין בינה לבין חניית הכפר, לבחור תמונה אחרי השוואה לחזית עדכנית, ואז להגיש כרטיס ביקורת, בדיקת כפילות ושדות place-facts. אין להזין שעות או טלפון דינמיים ישירות מהדף.

```yaml
status: partial
scope: "read-only research for Janco Dada Museum candidate #117"
risk_level: L0
canonical_writer: none
findings:
  - "Museum association identifies active museum and five-minute walk from village parking"
  - "Museum doorway GPS and current-façade photo match not yet verified"
sources:
  - {url: "https://www.icom.org.il/museum/1505", checked_at: "2026-10-06"}
  - {url: "https://www.ein-hod.org/en/yanko-dada-museum-2/", checked_at: "2026-10-06"}
  - {url: "https://commons.wikimedia.org/wiki/Category:Janco_Dada_museum", checked_at: "2026-10-06"}
changes_made: []
unchanged:
  - {item: "WordPress, media and candidate queue", reason: "Entry coordinate and photo suitability unresolved"}
validation:
  - {check: "Primary institution and association sources", result: pass, evidence: "ICOM and Ein Hod descriptions"}
snapshot: not-required
rollback: "No live or queue change"
blockers:
  - "Verified pedestrian museum entrance coordinate and current visual image match"
remaining_risks:
  - "2011/2007 Commons photos may not depict current façade"
issue_recommendation: keep-open
```