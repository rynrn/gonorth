# #93 — מלון פסטורל כפר בלום: רשומה ממתינה ותמונה מורשית

תאריך: 06.10.2026, 07:46 שעון ישראל. [כרטיס #93](https://github.com/rynrn/gonorth/issues/93); משימת־אב [#74](https://github.com/rynrn/gonorth/issues/74). סיכון L2. לא בוצע פרסום ציבורי.

מנהל המשימה וביקורת תוכן עצמאית אישרו יצירת רשומת `pending` בלבד. [אתר המלון](https://www.kfarblum-hotel.co.il/) ו[עמוד ההגעה](https://www.kfarblum-hotel.co.il/location-and-map) מאשרים שם, קיבוץ כפר בלום, גליל עליון והכוונה לשער הקיבוץ. [Waze](https://www.waze.com/fil/live-map/directions/%D7%A4%D7%A1%D7%98%D7%95%D7%A8%D7%9C-%D7%9E%D7%9C%D7%95%D7%9F-%D7%9B%D7%A4%D7%A8-%D7%91%D7%9C%D7%95%D7%9D?to=place.w.23331148.233377013.517820) מזהה יעד ניווט בשם המלון, 33.17495239309772,35.610987919498726, עם טלפון התואם לאתר המפעיל. **אין ראיה שזו דלת הקבלה או שער הכניסה הפיזי**; ההבהרה מופיעה בתוכן. כתובת רחוב, שעות, מחירים, זמינות, דירוג ושדות קשר דינמיים נשארו ריקים/unknown. ב־132 רשומות קודמות בכל הסטטוסים לא נמצאה כפילות; בלום וילג׳ וקייקי כפר בלום הם מקומות אחרים.

נוצרה רשומת GeoDirectory #542 בסטטוס `pending`, קטגוריה `accommodation`, אזור `galil`, יישוב כפר בלום, מדינה ישראל ואזור צפון. תוכן עברי מקשר למפעיל ולהוראות ההגעה. המיקום הוזן דרך GeoDirectory REST מאומת. צילום חזית חלקית ושלט המלון מ־27.11.2025 מאת Hanay, [מקור Wikimedia Commons](https://commons.wikimedia.org/wiki/File:%D7%9E%D7%9C%D7%95%D7%9F_%D7%A4%D7%A1%D7%98%D7%95%D7%A8%D7%9C_%D7%91%D7%9B%D7%A4%D7%A8_%D7%91%D7%9C%D7%95%D7%9D_26.jpg), [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/), צורף כמדיה ראשית #543 עם ALT, כיתוב, קרדיט וקישורי מקור/רישיון גלויים. WordPress יצר גרסאות מוקטנות ודחוסות; צילום זה אינו ראיה למצב הכניסה כיום.

ניסיון ייבוא ראשון לתיקיית `uploads/2026/10` נכשל בהרשאות. ללא שינוי הרשאות או ROOT, הקובץ הועבר לתיקיית `issue93-licensed` הקיימת, אומת מול SHA-256 המקורי `3e57c8983aec40e18914fda7def34c762ec7adbc06d7e2482c9c3eaa139ada55`, ונרשם ב־WordPress עם `--skip-copy`. הקובץ המקורי נדחס בידי WordPress; SHA-256 אחרי העיבוד `11f92c74e9caeb7787518e0c7f7b186a0218f8a8d7cb263360f05c0f8f8b8aaa`. התמונה הראשית הציבורית היא [קובץ scaled](https://gonorth.co.il/wp-content/uploads/issue93-licensed/pastoral-hanay-2025-scaled.jpg).

אימות: `tools/validate-place-records.py` עבר על רשומה אחת, 0 שגיאות ואזהרות; WP-CLI ו־GeoDirectory REST אימתו סטטוס, תוכן, טקסונומיות, מיקום, תמונה וקרדיט. דף המקום ללא הזדהות החזיר HTTP 404, API המקום HTTP 401, והתמונה HTTP 200. [שחזור ממוקד](../scripts/issue93-pastoral-rollback.ps1) עבר dry-run בלבד; `-Apply` לא הופעל. [תור המקומות](../../places-to-add.md) עודכן לממתין לביקורת מנהל ללא קישור ציבורי.

ראיות: [כרטיס ביקורת](../drafts/2026-10-06-issue-93-pastoral-approval.md), [מלאי מקומות קודם](../data/2026-10-06-issue-93-pastoral-before-inventory.json), [מלאי מדיה קודם](../data/2026-10-06-issue-93-pastoral-before-media.json), [post אחרי](../data/2026-10-06-issue-93-pastoral-after-post.json), [media אחרי](../data/2026-10-06-issue-93-pastoral-after-media.json), [מיקום לפני](../data/2026-10-06-issue-93-pastoral-before-location.json), [מיקום אחרי](../data/2026-10-06-issue-93-pastoral-after-location.json), [בדיקות ציבוריות](../data/2026-10-06-issue-93-pastoral-public-qa.json). #93 נשאר פתוח לביקורת פרסום מנהל, קישור מדריך לאחר פרסום, והחלטות על יתר המועמדים.

```yaml
status: partial
scope: "pending Pastoral Kfar Blum gd_place #542 with licensed media #543"
risk_level: L2
canonical_writer: "WordPress CLI editorial/terms/media; authenticated GeoDirectory REST location"
sources:
  - {url: "https://www.kfarblum-hotel.co.il/location-and-map", checked_at: "2026-10-06"}
  - {url: "https://www.waze.com/fil/live-map/directions/%D7%A4%D7%A1%D7%98%D7%95%D7%A8%D7%9C-%D7%9E%D7%9C%D7%95%D7%9F-%D7%9B%D7%A4%D7%A8-%D7%91%D7%9C%D7%95%D7%9D?to=place.w.23331148.233377013.517820", checked_at: "2026-10-06"}
  - {url: "https://commons.wikimedia.org/wiki/File:%D7%9E%D7%9C%D7%95%D7%9F_%D7%A4%D7%A1%D7%98%D7%95%D7%A8%D7%9C_%D7%91%D7%9B%D7%A4%D7%A8_%D7%91%D7%9C%D7%95%D7%9D_26.jpg", checked_at: "2026-10-06"}
changes_made:
  - "Created pending gd_place #542 and licensed featured media #543"
  - "Marked candidate #121 pending administrator review"
validation:
  - {check: "place validator", result: pass, evidence: "1 record, 0 errors, 0 warnings"}
  - {check: "canonical post/category/region/location/media", result: pass, evidence: "pending #542, media #543"}
  - {check: "public page/API/image", result: pass, evidence: "404/401/200"}
  - {check: "guarded rollback dry-run", result: pass, evidence: "guard passed, no changes"}
snapshot: "memory/data/2026-10-06-issue-93-pastoral-before-inventory.json"
rollback: "pwsh -File memory/scripts/issue93-pastoral-rollback.ps1 -Apply, only if guard passes"
blockers:
  - "WordPress administrator publication review and guide link only after publication"
remaining_risks:
  - "Navigation destination is not a verified physical entrance"
issue_recommendation: keep-open
```
