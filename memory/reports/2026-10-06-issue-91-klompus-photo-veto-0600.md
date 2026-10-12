# #91 — קלומפוס: תמונת עבר לא מתאימה לגלריה הנוכחית

תאריך: 06.10.2026. סיכון L0; אין שינוי ב־WordPress, במדיה או ברשומת המקום.

בבדיקה עצמאית של מנהל וביקורת תוכן נפסלה העלאת צילום המנה ההיסטורי של קלומפוס. [קובץ Commons](https://commons.wikimedia.org/wiki/File:Klompus_Burger_With_Smoked_Goose_Breast_and_French_Fries.jpg) צולם ב־05.09.2019 ומטא־דאטה שלו מצביע על 33.223356,35.615687; הוא משוחרר כ־CC0, אך רישיון לבדו אינו מבטיח שהוא מתאר את הסניף והתפריט הנוכחיים. על פי [דף ההגעה הרשמי](https://klompus.co.il/how-to-get/) ו[התפריט הרשמי](https://klompus.co.il/menu/) שהביקורת בדקה, העסק עבר מהמתחם הקודם למיקום דפנה׳לה והתפריט השתנה. לכן הצילום עלול להטעות כצילום עדכני של המקום או של מנה המוגשת כיום.

אין להעלות את התצלום לגלריית #502 כמצב עכשווי. אפשר יהיה לשקול צילום עדכני מהעסק או מבעל זכויות עם הרשאת שימוש נפרדת, ולבדוק התאמה חזותית ומיקום מחדש. דף #502 נשאר ללא שינוי; #91 נשאר פתוח. דפי העסק חסמו פתיחה חוזרת בכלי web ב־403 בעת כתיבת דוח זה, ולכן ממצא המעבר נשען על בדיקת Chrome של הביקורת העצמאית ולא על קריאה חוזרת כאן.

```yaml
status: partial
scope: "issue 91 candidate gallery for Klompus #502"
risk_level: L0
canonical_writer: "none"
findings:
  - "Historical licensed food photo is from 2019 and may imply an obsolete location/menu"
sources:
  - {url: "https://commons.wikimedia.org/wiki/File:Klompus_Burger_With_Smoked_Goose_Breast_and_French_Fries.jpg", checked_at: "2026-10-06"}
  - {url: "https://klompus.co.il/how-to-get/", checked_at: "2026-10-06"}
  - {url: "https://klompus.co.il/menu/", checked_at: "2026-10-06"}
changes_made:
  - "This report only"
unchanged:
  - {item: "WordPress listing #502 and media", reason: "Content-quality veto"}
validation:
  - {check: "Independent manager/content review", result: pass, evidence: "Photo rejected for current gallery"}
snapshot: "not-required"
rollback: "delete this local L0 report if incorrect"
blockers:
  - "Need current venue or dish photo with documented rights and editorial fit"
remaining_risks:
  - "Official business pages returned 403 to repeat web read"
issue_recommendation: keep-open
```
