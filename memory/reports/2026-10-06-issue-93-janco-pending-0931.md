# #93 — מוזיאון ינקו־דאדא נוצר כממתין בלבד

תאריך: 06.10.2026, 09:31 ישראל. [Issue #93](https://github.com/rynrn/gonorth/issues/93). סיכון L2. מנהל המשימה ובדיקת איכות תוכן עצמאית אישרו יצירת `pending` בלבד, עם דגל שמסתיר ניווט ברכב, ללא פרסום ציבורי.

[איקו״ם ישראל](https://www.icom.org.il/museum/1505) מאשר את מוזיאון ינקו־דאדא במרכז כפר האמנים עין הוד, תערוכת קבע ומתחלפות, וחניה בכניסה לכפר במרחק הליכה של כחמש דקות. [אתר כפר האמנים](https://www.ein-hod.org/מוזיאון-ינקו-דאדא/) מאשר את המקום. [OSM node 3071435099](https://www.openstreetmap.org/node/3071435099) מסמן את *המוזיאון עצמו* ב־32.7017278,34.9799967; הוא אינו חניה או כניסת רכב מאומתת. לכן נקודת המקום הוזנה למפה אך הדגל `_gonorth_hide_driving_navigation=1` הוגדר לפני כל אפשרות פרסום. בגוף התוכן יש הוראות חניה והליכה מפורשות, ואין קישור Waze/Maps ידני.

השעות בדפי איקו״ם וכפר האמנים אינן תואמות, ואתר המוזיאון החזיר 429; לכן שעות, טלפון, מחיר, אתר, דירוג, כתובת רחוב וקואורדינטת חניה נשארו unknown. אין להסיק ש־429 מציין סגירה. אין טענה שהסטודיו המשוחזר פתוח בכל ביקור.

לפני הכתיבה נשמר מלאי 136 מקומות בכל הסטטוסים ולא נמצאה כפילות בשם ינקו/דאדא/Janco/Dada. נשמר מלאי 168 פריטי מדיה. `tools/validate-place-records.py` עבר: רשומה אחת, 0 שגיאות ו־0 אזהרות. נשמרו [כרטיס ביקורת](../drafts/2026-10-06-issue-93-janco-approval.md), [מלאי מקומות](../data/2026-10-06-issue-93-janco-before-inventory-final.json) ו[מלאי מדיה](../data/2026-10-06-issue-93-janco-before-media.json).

נוצרה רשומת `gd_place` #550 בסטטוס `pending`, סוג `atraktziot-main`, אזור תוכן `carmel`, יישוב עין הוד. המיקום הוזן דרך GeoDirectory REST מאומת עם מדינה ישראל ואזור צפון. צילום חזית מ־07.07.2011 מאת ד״ר אבישי טייכר / PikiWiki Israel צורף כמדיה ראשית #551: [מקור Wikimedia Commons](https://commons.wikimedia.org/wiki/File:PikiWiki_Israel_13688_Janco_Dada_Museum_in_Ein_Hod.jpg), [CC BY 2.5](https://creativecommons.org/licenses/by/2.5/). ALT, כיתוב המדיה וגוף הרשומה כוללים תיאור, מחבר, מקור, רישיון, גיל הצילום וציון ש־WordPress הקטין/דחס גרסאות תצוגה. הצילום אינו ראיה למצב המבנה כיום. SHA-256 הקובץ המקומי לפני העלאה `13e0ae74174da1d9793336ad98284e60ca754b5b97a9d25224e65b933fed0c42`; WordPress עיבד את המקור בשרת, וקובץ ה־scaled המאוחסן הוא `5662350374b55b751fca9b1eb73909e206bcc26f3be67fdd3f7bc91c250c9df7`.

אימות קנוני: [פוסט](../data/2026-10-06-issue-93-janco-after-post.json), [מיקום](../data/2026-10-06-issue-93-janco-after-location.json), [תמונה](../data/2026-10-06-issue-93-janco-after-media.json), [מונחים ודגל](../data/2026-10-06-issue-93-janco-after-terms.json) ו[validator](../data/2026-10-06-issue-93-janco-validation.json). בדיקה פומבית: [עמוד pending החזיר 404, API ציבורי 401, תמונה JPEG החזירה 200](../data/2026-10-06-issue-93-janco-public-qa.json). [סקריפט שחזור ממוקד](../scripts/issue93-janco-rollback.ps1) עבר dry-run לאחר שתוקן חיבור `?context=edit` בכתובת ה־REST; `-Apply` לא הופעל.

#93 נשאר פתוח לביקורת מנהל לפני פרסום, QA בנתיב הציבורי לאחר פרסום, קישור ממדריך והחלטות על יתר המועמדים. אין להציג את #550 כקישור ציבורי עובד לפני פרסום.

```yaml
status: partial
scope: "pending Janco-Dada Museum gd_place #550 with licensed media #551"
risk_level: L2
canonical_writer: "WordPress CLI for editorial/terms/media/flag; authenticated GeoDirectory REST for location"
findings:
  - "Museum pin differs from car arrival; navigation flag set"
  - "Historic licensed facade image used with explicit date caveat"
  - "Conflicting opening hours omitted"
sources:
  - {url: "https://www.icom.org.il/museum/1505", checked_at: "2026-10-06"}
  - {url: "https://www.ein-hod.org/מוזיאון-ינקו-דאדא/", checked_at: "2026-10-06"}
  - {url: "https://api.openstreetmap.org/api/0.6/node/3071435099", checked_at: "2026-10-06"}
  - {url: "https://commons.wikimedia.org/wiki/File:PikiWiki_Israel_13688_Janco_Dada_Museum_in_Ein_Hod.jpg", checked_at: "2026-10-06"}
changes_made:
  - "Created pending listing #550 and featured licensed media #551"
  - "Updated candidate 117 in local queue"
unchanged:
  - {item: "public listing and guide", reason: "publication requires administrator review"}
validation:
  - {check: "place validator", result: pass, evidence: "1 record; 0 errors, 0 warnings"}
  - {check: "canonical post/category/region/location/flag/media", result: pass, evidence: "pending #550, media #551, venue pin, flag 1"}
  - {check: "public page/API/image", result: pass, evidence: "404/401/200"}
  - {check: "guarded rollback dry-run", result: pass, evidence: "guard passed; no deletion"}
snapshot: "memory/data/2026-10-06-issue-93-janco-before-inventory-final.json"
rollback: "pwsh -NoProfile -File memory/scripts/issue93-janco-rollback.ps1 -Apply if guard passes"
blockers:
  - "administrator publication review and guide link after publication"
remaining_risks:
  - "2011 photo may differ from current facade"
  - "alternate single-gd_place.php public path remains linted but not live-exercised"
issue_recommendation: keep-open
```
