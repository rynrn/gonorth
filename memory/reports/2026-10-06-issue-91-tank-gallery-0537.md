# #91 — פיילוט גלריה מורשית: הטנק הסורי ההפוך (#503)

תאריך: 06.10.2026, 05:37 שעון ישראל. כרטיס: https://github.com/rynrn/gonorth/issues/91; משימת־אב: https://github.com/rynrn/gonorth/issues/74. דף: https://gonorth.co.il/places/%d7%94%d7%98%d7%a0%d7%a7-%d7%94%d7%a1%d7%95%d7%a8%d7%99-%d7%94%d7%94%d7%a4%d7%95%d7%9a/

## תוצאה

במקום הראשון מבין עשרת מועמדי הפיילוט נוצרה גלריה בת שתי תמונות אמיתיות. תמונת הנושא הוחלפה מצילום שמקורו ב־Google ושזכות האחסון שלו טרם הוכחה לצילום טנק מורשה; הצילום הקודם (#509) נשמר לצורך שחזור. צילום הנחל הסמוך מוצג בגלריה, בלי לטעון שהוא מראה כניסה או חניה. לצד שתי התמונות מוצגים שם הצלם, קישור למקור, קישור לרישיון והעיבוד (הקטנה והמרה ל־WebP). קישור רשות הטבע והגנים מכוון לבדיקת מידע מסלול עדכני.

מקורות שנבדקו ב־06.10.2026:
- צילום הטנק: https://commons.wikimedia.org/wiki/File:SyrianTank_in_Banias_river_3.jpg — Shayshal2, CC BY-SA 4.0: https://creativecommons.org/licenses/by-sa/4.0/ . תמונת מקור ליד הטנק, GPS בדף המקור סמוך לנקודת האתר.
- צילום הנחל: https://commons.wikimedia.org/wiki/File:Syrian_Tank_Stream.jpg — Yotam Nachum, CC0 1.0: https://creativecommons.org/publicdomain/zero/1.0/ . נחל הבניאס סמוך לטנק; אינו מציג את הטנק, כניסה או חניה.
- מידע מסלול: https://www.parks.org.il/new/syrian-tank/ — רשות הטבע והגנים.
- סקירת הזכויות הקודמת: memory/reports/2026-10-06-issue-91-media-rights-audit-2300.md.

סקירה עצמאית של orchestrator ותת־סוכן איכות תוכן אישרה את המסלול הטכני ואת נוסחי ALT/כיתוב לפני הפרסום. תמונות מקור הורדו ל־memory/work, נבדקו חזותית והוקטנו ל־1200×900 בלי חיתוך. קובצי WebP: טנק 274,980 בתים; נחל 346,398 בתים. גרסאות 400×300 שמשרתות את הדף הן 45,996 ו־51,310 בתים. WordPress יצר srcset; התמונות בגוף נטענות ב־lazy.

## שינוי ובדיקות

רמת סיכון L2; ממשק כתיבה קנוני WordPress/WP-CLI. לפני שינוי נשמרו תוכן הפוסט ומצבו ב־memory/work/issue91-503-before.json ובשרת /home/claude/issue91-503-before.json; תמונת נושא ישנה #509. הרשומה עברה tools/validate-place-records.py עם מקור רשמי וקואורדינטות קיימות. הרשאות תיקיית ההעלאה החודשית לא שונו: המדיה יובאה בתיקייה ייעודית עם --skip-copy. קבצי המדיה החדשים הם #527 ו־#528.

הסקריפט memory/work/issue91-503-gallery.php עבר php -l. פעולת check אישרה checksum ותמונה לפני השינוי; פעולת apply דרשה התאמה למצב הקודם והחליפה רק post_content ו־thumbnail. מצב אחרי נשמר ב־memory/work/issue91-503-after.json ובשרת /home/claude/issue91-503-after.json. rollback מוגן checksum קיים בסקריפט: wp eval-file /home/claude/issue91-503-gallery.php rollback --path=/var/www/gonorth --allow-root. הוא מחזיר תוכן ותמונה #509 רק אם מצב הדף עדיין תואם למצב אחרי, ומשאיר קבצים חדשים ללא מחיקה.

בדיקות ציבוריות ב־Chrome ב־390 וב־1280 פיקסלים: HTTP 200, H1 תקין, שתי תמונות נטענות, ALT תואם, srcset ו־lazy, שני זוגות קישורי מקור/רישיון גלויים, קישור מקור רשמי, ללא שגיאות JavaScript וללא גלישה אופקית. OG מצביע לצילום הטנק החדש; צילום Google הישן לא מופיע בין תמונות הדף. צילום מסך לבדיקה: memory/work/issue91-503-gallery-mobile.png.

## גבולות והמשך

זהו מקום אחד מתוך עשרת מועמדי הפיילוט. לא נמצאה תמונה מאומתת ועדכנית של כניסה או חניה, ולכן אין מצג כזה בגלריה. תשעת המקומות הנותרים עדיין דורשים תמונה עם זכות שימוש ברורה או חריג מנומק; הרשאות 20 נכסי המדיה שנבדקו במקור עדיין אינן מוכחות. זמן/עלות השגת הרשאות מהעסקים נותרו לא ידועים; הפיילוט הנוכחי השתמש בצילומי Commons ללא רכישה. #91 נשאר פתוח.

status: partial
scope: listing 503, two licensed media assets, public mobile/desktop
risk_level: L2
canonical_writer: WordPress WP-CLI post/media APIs
changes_made: media 527/528, post_content gallery, thumbnail 527
unchanged: old attachment 509 and all dynamic place facts
validation: PHP lint, place validator, canonical guard, Chrome 390/1280, OG and visual screenshot passed
snapshot: memory/work/issue91-503-before.json
rollback: guarded WP-CLI script memory/work/issue91-503-gallery.php, mode rollback
blockers: licensed entrance/parking photos for this site and nine further pilot places
remaining_risks: old Google media asset remains stored but no longer used on this page; other sampled assets have unknown rights
issue_recommendation: keep-open

