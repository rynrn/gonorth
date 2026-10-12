# #93 — נחל המערות: שדה העיר חוסם יצירה בטוחה

תאריך: 06.10.2026. סיכון L1; לא בוצע שינוי באתר. ביקורת מנהל אישרה יצירת רשומת pending בלבד בתנאי שלא יוזן יישוב לא מאומת.

[רט״ג](https://www.parks.org.il/reserve-park/שמורת-טבע-נחל-המערות/) ממקמת את הכניסה מכביש 4 **בין גבע כרמל לעין כרמל**. לכן `עין כרמל` אינו ערך עיר מאומת. בדיקת OPTIONS מאומתת של `/wp-json/geodir/v2/places` ב־06.10 הראתה שב־POST השדה `city` הוא `required: true`; אי אפשר להשאירו ריק לפי סכמת ה־API. לא נוצרה רשומה, ולא הועלתה תמונה.

הוכנו [כרטיס ביקורת](../drafts/2026-10-06-issue-93-nahal-mearot-review.md), [מלאי כפילות](../data/2026-10-06-issue-93-nahal-before-inventory.json) של 138 רשומות ללא התאמת שם, ו[מועמד מיקום](../work/issue93-nahal-candidate.json) עם עיר ריקה. [Validator](../data/2026-10-06-issue-93-nahal-validation-revised.json) עבר, אך אינו מחליף את דרישת ה־API. סמן [מבנה המידע ב־OSM](https://www.openstreetmap.org/way/117880693) אינו שער כניסה או יעד נסיעה; צילום [מרכז המבקרים](https://commons.wikimedia.org/wiki/File:Nahal_Mearot_-_visitor_center.jpg) מ־2016 נבדק חזותית ורישיונו CC BY-SA 4.0. שניהם ימתינו עד להכרעת העיר. דף רט״ג מציין גם אזהרת Waze וסגירת שביל 4×4 מסוים, לא סגירת השמורה כולה.

השלב הבא: לקבל ממקור מפעיל/רשות מוסמכת שיוך יישוב מדויק, או להוסיף למוצר תמיכה מאומתת באתרי טבע שאין להם עיר. לאחר מכן להריץ שוב כפילויות, validator, snapshot, יצירת pending דרך הממשקים הקנוניים, בדיקות אתר ושחזור מוגן. #93 נשאר פתוח.

status: partial; scope: candidate 116; risk_level: L1; canonical_writer: local Markdown/JSON only; validation: 138 listings checked, place validator passed, authenticated POST schema requires city; snapshot: not-required; rollback: revert local candidate/review/queue; blocker: authoritative city/locality or verified product support for natural sites without city; issue_recommendation: keep-open.
