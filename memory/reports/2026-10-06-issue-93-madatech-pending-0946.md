# #93 — מדעטק נוצר כממתין בלבד

תאריך: 06.10.2026, 09:46 ישראל. [Issue #93](https://github.com/rynrn/gonorth/issues/93). סיכון L2. ביקורת מנהל עצמאית אישרה יצירת `pending` בלבד, בתנאי שסמן המתחם לא ישמש לניווט ברכב. דוח המחקר הקודם מ־09:04 מוחלף לגבי חסם הניווט, אך מקורותיו נשמרים.

[אתר מדעטק](https://www.madatech.org.il/main-info/arrival-ways/) מציין כי הכניסה והקופות ברחוב שמריהו לוין 25, והחניה בבלפור 12; [דף הביקור](https://www.madatech.org.il/main-info/kastah/) שולל כניסה רגילה ישירות מהחניון. בבדיקה מ־06.10 המפעיל מזהיר שיישומי ניווט עלולים לפעול חלקית. קישור Google Maps בדף הרשמי מוביל לחניה, ולכן אין להציגו ככניסת המבקרים. סמן המתחם `32.8099166,34.9961485` חושב כממוצע 25 צמתים ייחודיים של [פוליגון המוזיאון ב־OpenStreetMap](https://www.openstreetmap.org/way/1119264264) ונבדק שהוא בתוכו. זהו סמן כללי, לא קואורדינטת כניסה/חניה מדודה או יעד רכב. גוף הרשומה מעניק קרדיט ל־© תורמי OpenStreetMap, מפנה להוראות הרשמיות ומתארך את אזהרת הניווט. דגל `_gonorth_hide_driving_navigation=1` הוגדר מיד אחרי יצירת הפוסט; אין קישור ניווט ידני בתוכן.

נשמרו [כרטיס הביקורת](../drafts/2026-10-06-issue-93-madatech-review.md), [מלאי 137 מקומות](../data/2026-10-06-issue-93-madatech-before-inventory.json) בכל הסטטוסים ו[מלאי 169 פריטי מדיה](../data/2026-10-06-issue-93-madatech-before-media.json) לפני הכתיבה. לא נמצאה כפילות בשם מדעטק/מוזיאון המדע/Madatech. [Validator](../data/2026-10-06-issue-93-madatech-validation.json) עבר: רשומה אחת, אפס שגיאות ואזהרות. הרשומה #552 נוצרה `pending`, קטגוריית אטרקציות `atraktziot-main` ואזור `carmel`; המיקום נכתב דרך GeoDirectory REST מאומת, עם עיר חיפה וללא שדה רחוב. שעות, מחיר ודירוג לא מולאו בהשערה.

[צילום החזית](https://commons.wikimedia.org/wiki/File:Madatech_The_Science_Museum_Haifa_Avney_Moreshet_9cf10973.jpg) מאת אושרי רייכר / אבני מורשת מ־14.07.2026 נבדק חזותית, ו־Commons API אישר רישיון [CC BY 2.5](https://creativecommons.org/licenses/by/2.5/). קובץ המקור 4032×3024, SHA-256 `49c1600255f858bf33363507a577188a1b4fce5e7e6005a72a9d95efc6357b32`, הועלה לאחר אימות גודל ו־hash. WordPress עיבד אותו וצירף מדיה #553 כתמונה ראשית; ALT, כיתוב וגוף הפוסט כוללים ייחוס, מקור, רישיון וציון הקטנה/דחיסה. הצילום אינו מקור לשעות או לתערוכות עדכניות.

אימות קנוני: [פוסט](../data/2026-10-06-issue-93-madatech-after-post.json), [מיקום](../data/2026-10-06-issue-93-madatech-after-location.json), [מדיה](../data/2026-10-06-issue-93-madatech-after-media.json) ו[מונחים/דגל/תמונה](../data/2026-10-06-issue-93-madatech-after-terms.json) תואמים. [QA ציבורי](../data/2026-10-06-issue-93-madatech-public-qa.json): דף pending 404, API אנונימי 401, תמונה 200. [סקריפט שחזור ממוקד](../scripts/issue93-madatech-rollback.ps1) עבר dry-run: guard passed, ללא מחיקה; `-Apply` לא הופעל. #93 נשאר פתוח לביקורת פרסום מנהל, בדיקת דף ציבורי לאחר פרסום וקישור מדריך.

```yaml
status: partial
scope: "pending Madatech gd_place #552 with licensed media #553"
risk_level: L2
canonical_writer: "WordPress CLI for editorial/terms/media/flag; authenticated GeoDirectory REST for location"
findings:
  - "Official visitor entrance and parking are separate; venue-only pin is not a driving destination"
  - "2026 licensed facade photo attached with attribution"
sources:
  - {url: "https://www.madatech.org.il/main-info/arrival-ways/", checked_at: "2026-10-06"}
  - {url: "https://www.madatech.org.il/main-info/kastah/", checked_at: "2026-10-06"}
  - {url: "https://api.openstreetmap.org/api/0.6/way/1119264264/full", checked_at: "2026-10-06"}
  - {url: "https://commons.wikimedia.org/wiki/File:Madatech_The_Science_Museum_Haifa_Avney_Moreshet_9cf10973.jpg", checked_at: "2026-10-06"}
changes_made:
  - "Created pending listing #552 and featured media #553"
  - "Updated candidate 110 in local queue"
unchanged:
  - {item: "public listing and guide", reason: "publication requires administrator review"}
validation:
  - {check: "place validator", result: pass, evidence: "1 record, 0 errors/warnings"}
  - {check: "canonical record, terms, location, flag and media", result: pass, evidence: "pending #552, media #553, venue pin, flag 1"}
  - {check: "public page/API/image", result: pass, evidence: "404/401/200"}
  - {check: "guarded rollback dry-run", result: pass, evidence: "no deletion"}
snapshot: "memory/data/2026-10-06-issue-93-madatech-before-inventory.json"
rollback: "pwsh -NoProfile -File memory/scripts/issue93-madatech-rollback.ps1 -Apply if guard passes"
blockers:
  - "administrator publication review and guide link after publication"
remaining_risks:
  - "representative OSM vertex mean is not a measured entrance"
  - "official navigation warning may change"
issue_recommendation: keep-open
```
