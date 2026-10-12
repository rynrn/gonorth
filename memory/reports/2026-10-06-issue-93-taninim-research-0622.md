# #93 — נחל תנינים: מקור כניסה וצילום מורשה; GPS עדיין unknown

תאריך: 06.10.2026. סיכון L0; לא נוצרה רשומה ולא שונה האתר.

[רשות הטבע והגנים](https://www.parks.org.il/reserve-park/%D7%A9%D7%9E%D7%95%D7%A8%D7%AA-%D7%98%D7%91%D7%A2-%D7%A0%D7%97%D7%9C-%D7%AA%D7%A0%D7%99%D7%A0%D7%99%D7%9D/) מתארת כניסה ממערב לבית חנניה, דרך דרך עפר המגיעה לשמורה, ומבקשת לחפש בוויז "שמורת טבע נחל תנינים". הדף אינו מפרסם קואורדינטה של כניסת המבקרים, ומציין שהחניה מחוץ לשמורה והכניסה ברגל. לכן אין להעביר נקודת מרכז הנחל או צילום ל־GPS של רשומה. אזור התוכן `carmel` מתאים לחוף הכרמל; סוג המקום `teva`.

נבדקו חזותית שלושה צילומי Commons. מועמד מתאים הוא [צילום טחנת הקמח והגלגל בשמורת נחל תנינים](https://commons.wikimedia.org/wiki/File:PikiWiki_Israel_48361_Taninim_River_Reserve.jpg), אלישבע ורנר-רייז / PikiWiki Israel, [CC BY 2.5](https://creativecommons.org/licenses/by/2.5/). התמונה מציגה גלגל טחנת קמח ושרידי מתקן המים, התואמים מוקד שהמפעיל מתאר. ALT מוצע: "גלגל טחנת קמח ושרידי מתקן מים בשמורת נחל תנינים". היא אינה צילום שער הכניסה ואינה מוכיחה שהגלגל מופעל כיום. הצילומים האחרים מציגים תעלת מים ונוף נחל, אך פחות מזהים את המוקד הייחודי. תמונות בדיקה קטנות נשמרו ב־`memory/work/issue93-taninim-photo-{1,2,3}.jpg`; לא הועלו לשרת.

לפני הכנת כרטיס אישור נדרשים נקודת ניווט מאומתת לכניסת המבקרים, בדיקת כפילות חוזרת, אימות שדות משתנים ב־place-facts ו־validator. אי אפשר לסמן את המועמד כהוסף. #93 נשאר פתוח.

```yaml
status: partial
scope: "Nahal Taninim reserve research for issue 93"
risk_level: L0
canonical_writer: "none"
findings:
  - "Official approach via Beit Hanania is known; visitor entrance coordinate is not"
  - "Selected visually appropriate licensed water-mill photo candidate"
sources:
  - {url: "https://www.parks.org.il/reserve-park/שמורת-טבע-נחל-תנינים/", checked_at: "2026-10-06"}
  - {url: "https://commons.wikimedia.org/wiki/File:PikiWiki_Israel_48361_Taninim_River_Reserve.jpg", checked_at: "2026-10-06"}
changes_made:
  - "This local report only"
unchanged:
  - {item: "WordPress", reason: "Precise visitor entrance GPS unknown"}
validation:
  - {check: "Three candidate photos visual review and Commons API license check", result: pass, evidence: "memory/work/issue93-taninim-photo-{1,2,3}.jpg"}
snapshot: "not-required"
rollback: "delete this L0 report if incorrect"
blockers:
  - "Need a verified visitor entrance coordinate"
remaining_risks:
  - "Historical photo does not establish current condition"
issue_recommendation: keep-open
```
