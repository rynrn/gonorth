# #93 — שמורת טבע נחל המערות נוצרה כממתינה בלבד

תאריך: 06.10.2026, 10:26 ישראל. [Issue #93](https://github.com/rynrn/gonorth/issues/93). סיכון L2. דוח זה מעדכן את [דוח חסם העיר](2026-10-06-issue-93-nahal-mearot-city-blocker.md): בדיקת כתיבה וקריאה חוזרת הוכיחה שערך עיר ריק נשמר ב־GeoDirectory REST אף על פי שהשדה נדרש בבקשת POST. לא הוזן יישוב משוער.

## תוכן ומיקום

ביקורת מנהל אישרה רשומה ממתינה בתנאי שעיר לא מאומתת לא תוצג. לאחר [מלאי טרום שינוי](../data/2026-10-06-issue-93-nahal-preprobe-inventory.json) של 138 רשומות בכל הסטטוסים ללא כפילות, נוצרה רשומת gd_place #554 בסטטוס pending. שדה העיר נשלח ונקרא חזרה כריק; GeoDirectory REST שמר מדינה ישראל, אזור צפון וסמן 32.6712039,34.9652355. הסמן הוא מרכז מבנה המידע [OSM way 117880693](https://www.openstreetmap.org/way/117880693), ואינו שער הכניסה, חניה או יעד נסיעה. דגל הסתרת ניווט ברכב הוגדר לפני המיקום; אין בגוף קישור Waze ידני. קטגוריות: atraktziot-main ו־teva; אזור: carmel.

[רט״ג](https://www.parks.org.il/reserve-park/שמורת-טבע-נחל-המערות/) מציינת כניסה מכביש 4 בין גבע כרמל לעין כרמל, אזהרה על ניווט Waze דרך כרם מהר״ל, וסגירת שביל 4×4 מסוים לשיקום, ללא איסור הליכה רגלית לפי ההודעה. התוכן מתארך את הבדיקה ומפנה למפעיל לעדכונים; שעות, מחיר, טלפון, דירוג וכתובת רחוב נשארו ריקים.

## מדיה

[צילום מרכז המבקרים](https://commons.wikimedia.org/wiki/File:Nahal_Mearot_-_visitor_center.jpg) מ־18.10.2016 מאת ד״ר אבישי טייכר / Avi1111, [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/), נבדק חזותית. מקור JPEG ‏4608×3456 ו־4,686,719 בתים, SHA-256 8D49E7E5C932E70760EDE451C996C0CDC70BC786E6B01F7C9CDC1B97695EF35B. ניסיון ייבוא לתיקיית החודש נכשל בהרשאות; לא שונו הרשאות או ROOT. הקובץ הועבר לתיקיית issue93-licensed הקיימת ויובא עם --skip-copy כמדיה #555. WordPress יצר גרסת 1920×1440, 12 גדלים וקובץ scaled עם SHA-256 E4C5A2B026C76C8AFCA3E12CF66CBFD732A2EC1E9620C74CD9D14AD18C816D94. כיתוב, תיאור, ALT וגוף הרשומה מציינים צלם, מקור, רישיון, הקטנה ואת העובדה שהתצלום אינו ראיה למצב כיום.

## אימות ושחזור

[מיקום קודם](../data/2026-10-06-issue-93-nahal-before-location.json), [רשומה אחרי](../data/2026-10-06-issue-93-nahal-after-post.json), [מיקום אחרי](../data/2026-10-06-issue-93-nahal-after-location.json), [מדיה אחרי](../data/2026-10-06-issue-93-nahal-after-media.json) ו[בדיקת שדות](../data/2026-10-06-issue-93-nahal-after-terms.json) נשמרו. Validator עבר: רשומה אחת, אפס שגיאות ואזהרות. שני קובצי PHP נבדקו ב־php -l לפני הפעלה. [QA ציבורי](../data/2026-10-06-issue-93-nahal-public-qa.json): דף pending מחזיר 404, API אנונימי 401, תמונה 200 מסוג JPEG. [שחזור ממוקד](../scripts/issue93-nahal-rollback.ps1) עבר dry-run: guard passed, ללא מחיקה. הפעלת -Apply בעת צורך מוחקת רק את הרשומה החדשה #554 והמדיה החדשה #555 אם כל ה־guards עדיין תואמים.

#93 נשאר פתוח: רשומה #554 דורשת ביקורת ופרסום מנהל לפי AGENTS, בדיקת דף ציבורי לאחר פרסום וקישור מדריך. מועמד 118 עדיין טעון בדיקת זמינות אחרי סגירה זמנית. לא הוגדר ניווט נסיעה לסמן מרכז המידע.

status: partial; scope: pending #554 and licensed media #555; risk_level: L2; canonical_writer: WordPress WP-CLI for editorial/terms/media and authenticated GeoDirectory REST for location; validation: 138-record duplicate inventory, validator, PHP lint, canonical snapshots, public 404/401/200, guarded rollback dry-run; snapshot: memory/data/2026-10-06-issue-93-nahal-preprobe-inventory.json; rollback: memory/scripts/issue93-nahal-rollback.ps1 -Apply if guard passes; blocker: administrator publication review and guide link; issue_recommendation: keep-open.
