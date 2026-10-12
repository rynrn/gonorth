# #93 — ינקו־דאדא: נקודת המוזיאון אינה יעד נסיעה מאומת

תאריך בדיקה: 06.10.2026. סיכון L1 לתיעוד מקומי בלבד; לא נעשה שינוי באתר.

[איקו״ם ישראל](https://www.icom.org.il/museum/1505) מתאר את המוזיאון במרכז עין הוד, עם חניה בכניסה לכפר והליכה של כחמש דקות. [אתר כפר האמנים](https://www.ein-hod.org/מוזיאון-ינקו-דאדא/) מאשר את פעילות המוזיאון. [OSM node 3071435099](https://www.openstreetmap.org/node/3071435099) נמצא ב־32.7017278, 34.9799967 ומסומן tourism=museum, עם survey:date=2024-05-18. זהו סימון המוזיאון, לא חניה או כניסת רכב מאומתת. בבדיקת נתוני המפה סביבו לא נמצאה תגית entrance מתאימה.

בשני קובצי התבנית הפעילים, functions.php:1226 ו־single-gd_place.php:251, כפתורי Waze נוצרים ישירות מה־GPS של הליסטינג עם navigate=yes. לפיכך שימוש בנקודת המוזיאון עלול להנחות נהגים למרכז הכפר בניגוד להנחיית איקו״ם. דרוש יעד חניה/הגעה מאומת או הפרדה מבוקרת בין מיקום המקום ליעד ניווט לפני יצירת רשומה עם נקודה זו.

[צילום חזית מועמד](https://commons.wikimedia.org/wiki/File:PikiWiki_Israel_13688_Janco_Dada_Museum_in_Ein_Hod.jpg) מאת ד״ר אבישי טייכר / PikiWiki Israel, 07.07.2011, [CC BY 2.5](https://creativecommons.org/licenses/by/2.5/), נבדק חזותית: שם המוזיאון נראה על המבנה. הצילום אינו מוכיח את מצב החזית היום ולא הועלה ל־WordPress. אתר המוזיאון החזיר 429; אין להסיק מכך שהוא סגור.

החלטה: מועמד 117 ו־#93 נשארים פתוחים. אין שינוי באתר. הצעד הבא הוא אימות יעד הגעת רכב ממקור מפעיל, או פיתוח ובדיקת הפרדה בין נקודת מקום ליעד ניווט, עם מסלול שחזור.

```yaml
status: partial
scope: "Janco Dada candidate #117, navigation and image research"
risk_level: L1
canonical_writer: "local Markdown only"
findings:
  - "OSM museum point is not a verified driving destination"
  - "Both live Waze links use canonical listing GPS"
  - "A licensed 2011 facade image exists but currency is unverified"
sources:
  - {url: "https://www.icom.org.il/museum/1505", checked_at: "2026-10-06"}
  - {url: "https://www.ein-hod.org/מוזיאון-ינקו-דאדא/", checked_at: "2026-10-06"}
  - {url: "https://api.openstreetmap.org/api/0.6/node/3071435099", checked_at: "2026-10-06"}
  - {url: "https://commons.wikimedia.org/wiki/File:PikiWiki_Israel_13688_Janco_Dada_Museum_in_Ein_Hod.jpg", checked_at: "2026-10-06"}
  - {url: "server child theme functions.php:1226 and single-gd_place.php:251", checked_at: "2026-10-06"}
changes_made:
  - "Dated report and local status references"
unchanged:
  - {item: "WordPress and media", reason: "No verified driving destination"}
validation:
  - {check: "Read both live Waze link renderers", result: pass, evidence: "Both use listing latitude and longitude"}
  - {check: "Visual inspection and license metadata", result: pass, evidence: "2011 PikiWiki facade, CC BY 2.5"}
snapshot: not-required
rollback: "Revert this report and the two local status references"
blockers:
  - "Verified vehicle destination or tested venue/arrival split"
remaining_risks:
  - "Historic photo may differ from current facade"
issue_recommendation: keep-open
```
