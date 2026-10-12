# #93 — אולמות האבירים בעכו: רשומה ממתינה ותמונה מורשית

תאריך: 06.10.2026, 08:23 שעון ישראל. [כרטיס #93](https://github.com/rynrn/gonorth/issues/93); משימת־אב [#74](https://github.com/rynrn/gonorth/issues/74). סיכון L2. לא בוצע פרסום ציבורי.

מנהל המשימה וביקורת תוכן עצמאית אישרו יצירת רשומת `pending` בלבד. [אתר המפעיל](https://www.akko.org.il/attraction/the-knights-halls/) מאשר את זהות אולמות האבירים והמצודה ההוספיטלרית. [שאלות נפוצות של המפעיל](https://www.akko.org.il/faq/) מאשרות שמתחילים במרכז המבקרים ברחוב ויצמן 1 בעכו העתיקה וממליצות על יעד Waze בשם האולמות או הכתובת. [Waze לויצמן 1](https://www.waze.com/live-map/directions?to=place.ws.il.84305.1) מציג 32.923404693603516,35.07143020629883; זו נקודת ניווט לכתובת, לא דלת כניסה או חניה מאומתת. ההסתייגות מופיעה בגוף הרשומה. שעות, מחירים, טלפון, דירוג וכתובת דינמית נשארו ריקים/unknown.

לפני הכתיבה נבדקו כל 133 רשומות `gd_place` בכל הסטטוסים ולא נמצאה כפילות בשם האולמות/המצודה; נבדקו גם 165 פריטי מדיה. `tools/validate-place-records.py` עבר רשומה אחת ללא שגיאות או אזהרות. נשמרו מלאי קודם וכרטיס ביקורת. במהלך הביצוע WP-CLI `--search` החזיר את כל המלאי ללא סינון, ולכן נבדקו הכותרות לאחר פענוח JSON לפני יצירה.

נוצרה רשומת GeoDirectory #544 בסטטוס `pending`, קטגוריה `atraktziot-main`, אזור `galil`, עיר עכו, מדינה ישראל ואזור צפון. יעד הכתובת הוזן דרך GeoDirectory REST מאומת. צילום פנים האולמות מ־18.05.2013 מאת Zapi81 צורף כמדיה ראשית #545: [מקור Wikimedia Commons](https://commons.wikimedia.org/wiki/File:%D7%90%D7%95%D7%9C%D7%9E%D7%95%D7%AA_%D7%94%D7%90%D7%91%D7%99%D7%A8%D7%99%D7%9D_%D7%A2%D7%9B%D7%95.jpg), [CC BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0/). כיתוב המדיה וגוף הרשומה כוללים קרדיט, מקור, רישיון והודעת שינוי גודל/דחיסה; ALT מתאר צילום משנת 2013. התמונה אינה ראיה למצב התצוגות כיום.

אימות קנוני: WP-CLI אישר סטטוס, כותרת, תוכן, קטגוריה, אזור, תמונה, ALT וכיתוב; GeoDirectory REST אישר עיר ומיקום. דף המקום ללא הזדהות החזיר HTTP 404, API המקום HTTP 401, והתמונה HTTP 200. [סקריפט השחזור הממוקד](../scripts/issue93-knights-rollback.ps1) עבר dry-run בלבד; `-Apply` לא הופעל. התמונה המקורית התאימה ל־SHA-256 שנבדק לפני העלאה; WordPress דחס אותה וה־SHA-256 לאחר הייבוא הוא `19ee32a2cd2152201a1c2d91cc0ad26b2b79298ee78cc65f2c622a1bba18fb77`.

ראיות: [כרטיס ביקורת](../drafts/2026-10-06-issue-93-knights-approval.md), [מלאי קודם](../data/2026-10-06-issue-93-knights-before-inventory.json), [מדיה קודמת](../data/2026-10-06-issue-93-knights-before-media.json), [פוסט אחרי](../data/2026-10-06-issue-93-knights-after-post.json), [מדיה אחרי](../data/2026-10-06-issue-93-knights-after-media.json), [מיקום לפני](../data/2026-10-06-issue-93-knights-before-location.json), [מיקום אחרי](../data/2026-10-06-issue-93-knights-after-location.json), [בדיקות ציבוריות](../data/2026-10-06-issue-93-knights-public-qa.json).

#93 נשאר פתוח לביקורת פרסום מנהל, קישור ממדריך רק לאחר פרסום, והחלטות על יתר המועמדים.

```yaml
status: partial
scope: "pending Knights Halls Acre gd_place #544 with licensed media #545"
risk_level: L2
canonical_writer: "WordPress CLI editorial/terms/media; authenticated GeoDirectory REST location"
sources:
  - {url: "https://www.akko.org.il/attraction/the-knights-halls/", checked_at: "2026-10-06"}
  - {url: "https://www.akko.org.il/faq/", checked_at: "2026-10-06"}
  - {url: "https://www.waze.com/live-map/directions?to=place.ws.il.84305.1", checked_at: "2026-10-06"}
  - {url: "https://commons.wikimedia.org/wiki/File:%D7%90%D7%95%D7%9C%D7%9E%D7%95%D7%AA_%D7%94%D7%90%D7%91%D7%99%D7%A8%D7%99%D7%9D_%D7%A2%D7%9B%D7%95.jpg", checked_at: "2026-10-06"}
changes_made:
  - "Created pending gd_place #544 and licensed featured media #545"
  - "Marked candidate #111 pending administrator review"
validation:
  - {check: "place validator", result: pass, evidence: "1 record, 0 errors, 0 warnings"}
  - {check: "canonical post/category/region/location/media", result: pass, evidence: "pending #544, media #545"}
  - {check: "public page/API/image", result: pass, evidence: "404/401/200"}
  - {check: "guarded rollback dry-run", result: pass, evidence: "guard passed, no changes"}
snapshot: "memory/data/2026-10-06-issue-93-knights-before-inventory.json"
rollback: "pwsh -File memory/scripts/issue93-knights-rollback.ps1 -Apply, only if guard passes"
blockers:
  - "WordPress administrator publication review and guide link only after publication"
remaining_risks:
  - "Navigation destination is not a verified physical entrance"
issue_recommendation: keep-open
```
