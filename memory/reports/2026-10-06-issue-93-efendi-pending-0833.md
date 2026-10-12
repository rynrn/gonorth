# #93 — מלון האפנדי בעכו: רשומה ממתינה ותמונה מורשית

תאריך: 06.10.2026, 08:33 שעון ישראל. [כרטיס #93](https://github.com/rynrn/gonorth/issues/93); משימת־אב [#74](https://github.com/rynrn/gonorth/issues/74). סיכון L2. לא בוצע פרסום ציבורי.

מנהל המשימה וביקורת תוכן עצמאית אישרו יצירת רשומת `pending` בלבד. [אתר המלון](https://efendi-hotel.co.il/) מאשר את פעילותו בעכו העתיקה. [הוראות ההגעה של המפעיל](https://efendi-hotel.co.il/%D7%93%D7%A8%D7%9B%D7%99-%D7%94%D7%92%D7%A2%D7%94-%D7%9C%D7%9E%D7%9C%D7%95%D7%9F/) ו[עמוד יצירת קשר](https://efendi-hotel.co.il/contact/) מציינים רחוב לואי התשיעי, היעדר חניון למלון, חניונים ציבוריים באזור והורדת חפצים בכניסה לסמטה. קישור Waze שהמפעיל מציג כולל `ll=32.92259730,35.06819240` ו־place ID של המלון. זו נקודת יעד לניווט, לא דלת כניסה או נקודת הורדה/חניה מאומתת; ההסתייגות מופיעה בתוכן. שעות, מחירים, טלפון, דירוג, זמינות וכתובת דינמית נשארו ריקים/unknown.

לפני הכתיבה נבדקו 134 רשומות `gd_place` בכל הסטטוסים ו־166 פריטי מדיה, ללא כפילות בשם אפנדי/Efendi/Effendi. `tools/validate-place-records.py` עבר רשומה אחת ללא שגיאות או אזהרות. נשמרו מלאי קודם, כרטיס ביקורת וראיות התמונה/הניווט.

נוצרה רשומת GeoDirectory #546 בסטטוס `pending`, קטגוריה `accommodation`, אזור תוכן `galil`, עיר עכו, מדינה ישראל ואזור צפון. המיקום הוזן דרך GeoDirectory REST מאומת. צילום כניסה עם שלט המלון מ־28.09.2012 מאת Yuval Y צורף כמדיה ראשית #547: [מקור Wikimedia Commons](https://commons.wikimedia.org/wiki/File:Efendi_Hotel,_Acre,_Israel_-_01.jpg), [CC BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0/). כיתוב המדיה וגוף הרשומה כוללים מחבר, תאריך, מקור, רישיון והודעת הקטנה/דחיסה; ALT מתאר צילום היסטורי. התמונה אינה ראיה למצב הכניסה כיום.

אימות קנוני: WP-CLI אישר סטטוס, כותרת, תוכן, טקסונומיות, תמונה, ALT וכיתוב; GeoDirectory REST אישר עיר ומיקום. דף המקום ללא הזדהות החזיר HTTP 404, API המקום HTTP 401, והתמונה המעובדת HTTP 200. [סקריפט השחזור הממוקד](../scripts/issue93-efendi-rollback.ps1) עבר dry-run בלבד; `-Apply` לא הופעל. SHA-256 הקובץ לפני ההעלאה הוא `97a8e7a6645f7116d6aaf3d199b600c3785b5d644b1cb2a0433a499b62ed03e4`; לאחר עיבוד WordPress, קובץ המקור בשרת הוא `2cf9a9aa5d903a515460708c82b080dd3472177526324fd99afdcf6af2036c5d`.

ראיות: [כרטיס ביקורת](../drafts/2026-10-06-issue-93-efendi-approval.md), [מלאי קודם](../data/2026-10-06-issue-93-efendi-before-inventory.json), [מדיה קודמת](../data/2026-10-06-issue-93-efendi-before-media.json), [פוסט אחרי](../data/2026-10-06-issue-93-efendi-after-post.json), [מדיה אחרי](../data/2026-10-06-issue-93-efendi-after-media.json), [מיקום לפני](../data/2026-10-06-issue-93-efendi-before-location.json), [מיקום אחרי](../data/2026-10-06-issue-93-efendi-after-location.json), [בדיקות ציבוריות](../data/2026-10-06-issue-93-efendi-public-qa.json).

#93 נשאר פתוח לביקורת פרסום מנהל, קישור ממדריך רק לאחר פרסום, והחלטות על יתר המועמדים.

```yaml
status: partial
scope: "pending Efendi Hotel Acre gd_place #546 with licensed media #547"
risk_level: L2
canonical_writer: "WordPress CLI editorial/terms/media; authenticated GeoDirectory REST location"
sources:
  - {url: "https://efendi-hotel.co.il/", checked_at: "2026-10-06"}
  - {url: "https://efendi-hotel.co.il/דרכי-הגעה-למלון/", checked_at: "2026-10-06"}
  - {url: "https://efendi-hotel.co.il/contact/", checked_at: "2026-10-06"}
  - {url: "https://commons.wikimedia.org/wiki/File:Efendi_Hotel,_Acre,_Israel_-_01.jpg", checked_at: "2026-10-06"}
changes_made:
  - "Created pending gd_place #546 and licensed featured media #547"
  - "Marked candidate #119 pending administrator review"
validation:
  - {check: "place validator", result: pass, evidence: "1 record, 0 errors, 0 warnings"}
  - {check: "canonical post/category/region/location/media", result: pass, evidence: "pending #546, media #547"}
  - {check: "public page/API/image", result: pass, evidence: "404/401/200"}
  - {check: "guarded rollback dry-run", result: pass, evidence: "guard passed, no changes"}
snapshot: "memory/data/2026-10-06-issue-93-efendi-before-inventory.json"
rollback: "pwsh -File memory/scripts/issue93-efendi-rollback.ps1 -Apply, only if guard passes"
blockers:
  - "WordPress administrator publication review and guide link only after publication"
remaining_risks:
  - "Navigation destination is not a verified entrance or parking location"
issue_recommendation: keep-open
```
