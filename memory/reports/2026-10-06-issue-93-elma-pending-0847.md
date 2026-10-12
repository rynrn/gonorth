# #93 — מלון אלמא בזכרון יעקב: רשומה ממתינה ותמונה מורשית

תאריך: 06.10.2026, 08:47 שעון ישראל. [כרטיס #93](https://github.com/rynrn/gonorth/issues/93); משימת־אב [#74](https://github.com/rynrn/gonorth/issues/74). סיכון L2. לא בוצע פרסום ציבורי.

מנהל המשימה וביקורת תוכן עצמאית אישרו יצירת רשומת `pending` בלבד. [אתר המפעיל](https://gordoniahotels.com/elma/) מזהה את אלמא כמלון בוטיק ומרכז אמנויות בזכרון יעקב; [עמוד יצירת הקשר](https://gordoniahotels.com/%D7%A6%D7%95%D7%A8-%D7%A7%D7%A9%D7%A8/) נותן כתובת יאיר 1. [Waze](https://www.waze.com/he/live-map/directions/il/%D7%9E%D7%97%D7%95%D7%96-%D7%97%D7%99%D7%A4%D7%94/%D7%96%D7%9B%D7%A8%D7%95%D7%9F-%D7%99%D7%A2%D7%A7%D7%91/%D7%9E%D7%9C%D7%95%D7%9F-%D7%90%D7%9C%D7%9E%D7%90?to=place.ChIJ4yveaisKHRURCtflCqcmv4I) מזהה יעד בשם „מלון אלמא” בקואורדינטות 32.5777742,34.9449457; זה יעד ניווט ולא דלת כניסה או חניה מאומתת. ההסתייגות מופיעה בגוף הרשומה. שעות, מחירים, טלפון, דירוג, זמינות, אירועים וכתובת דינמית נשארו ריקים/unknown. לפני ההעלאה תוקן הנוסח „נוף הכרמל והים” ל„נוף ההר והים” לפי ביקורת התוכן.

לפני הכתיבה נבדקו 135 רשומות `gd_place` בכל הסטטוסים ו־167 פריטי מדיה, ללא כפילות בשם אלמא/Elma. `tools/validate-place-records.py` עבר רשומה אחת ללא שגיאות או אזהרות. נשמרו מלאי קודם וכרטיס ביקורת.

נוצרה רשומת GeoDirectory #548 בסטטוס `pending`, קטגוריה `accommodation`, אזור תוכן `carmel`, עיר זכרון יעקב, מדינה ישראל ואזור צפון. המיקום הוזן דרך GeoDirectory REST מאומת. צילום מבנה וחצר המתחם מ־21.06.2016 מאת Elgaard צורף כמדיה ראשית #549: [מקור Wikimedia Commons](https://commons.wikimedia.org/wiki/File:Elma_Arts_Complex_and_Hotel_top.jpg), [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/). כיתוב המדיה וגוף הרשומה כוללים מחבר, תאריך, מקור, רישיון והודעת הקטנה/דחיסה; ALT מתאר צילום היסטורי. התמונה אינה ראיה למצב המתחם כיום.

אימות קנוני: WP-CLI אישר סטטוס, כותרת, תוכן, טקסונומיות, תמונה, ALT וכיתוב; GeoDirectory REST אישר עיר ומיקום. דף המקום ללא הזדהות החזיר HTTP 404, API המקום HTTP 401, והתמונה המעובדת HTTP 200. [סקריפט השחזור הממוקד](../scripts/issue93-elma-rollback.ps1) עבר dry-run בלבד; `-Apply` לא הופעל. בהרצה הראשונה של dry-run ה־guard עצר בגלל החלפת מזהה ששינתה ספרות בתוך checksum בסקריפט. תוקן checksum הסקריפט מול הקובץ הקנוני וההרצה השנייה עברה, ללא שינוי באתר. SHA-256 התמונה המקורית לפני העלאה `60e0d792c13db487a782b794b1b2669ed4d1c75b4d40d855ff0c47788a399069`; SHA-256 קובץ המקור בשרת לאחר עיבוד WordPress `991c84150e142969425f201d311b022f7238cd3954d0af56aab8498733a5b642`.

ראיות: [כרטיס ביקורת](../drafts/2026-10-06-issue-93-elma-approval.md), [מלאי קודם](../data/2026-10-06-issue-93-elma-before-inventory.json), [מדיה קודמת](../data/2026-10-06-issue-93-elma-before-media.json), [פוסט אחרי](../data/2026-10-06-issue-93-elma-after-post.json), [מדיה אחרי](../data/2026-10-06-issue-93-elma-after-media.json), [מיקום לפני](../data/2026-10-06-issue-93-elma-before-location.json), [מיקום אחרי](../data/2026-10-06-issue-93-elma-after-location.json), [בדיקות ציבוריות](../data/2026-10-06-issue-93-elma-public-qa.json).

#93 נשאר פתוח לביקורת פרסום מנהל, קישור ממדריך רק לאחר פרסום, והחלטות על יתר המועמדים.

```yaml
status: partial
scope: "pending Elma Hotel Zichron Yaakov gd_place #548 with licensed media #549"
risk_level: L2
canonical_writer: "WordPress CLI editorial/terms/media; authenticated GeoDirectory REST location"
sources:
  - {url: "https://gordoniahotels.com/elma/", checked_at: "2026-10-06"}
  - {url: "https://gordoniahotels.com/צור-קשר/", checked_at: "2026-10-06"}
  - {url: "https://www.waze.com/he/live-map/directions/il/מחוז-חיפה/זכרון-יעקב/מלון-אלמא?to=place.ChIJ4yveaisKHRURCtflCqcmv4I", checked_at: "2026-10-06"}
  - {url: "https://commons.wikimedia.org/wiki/File:Elma_Arts_Complex_and_Hotel_top.jpg", checked_at: "2026-10-06"}
changes_made:
  - "Created pending gd_place #548 and licensed featured media #549"
  - "Marked candidate #120 pending administrator review"
validation:
  - {check: "place validator", result: pass, evidence: "1 record, 0 errors, 0 warnings"}
  - {check: "canonical post/category/region/location/media", result: pass, evidence: "pending #548, media #549"}
  - {check: "public page/API/image", result: pass, evidence: "404/401/200"}
  - {check: "guarded rollback dry-run", result: pass, evidence: "guard passed on corrected script, no changes"}
snapshot: "memory/data/2026-10-06-issue-93-elma-before-inventory.json"
rollback: "pwsh -File memory/scripts/issue93-elma-rollback.ps1 -Apply, only if guard passes"
blockers:
  - "WordPress administrator publication review and guide link only after publication"
remaining_risks:
  - "Navigation destination is not a verified entrance or parking location"
issue_recommendation: keep-open
```
