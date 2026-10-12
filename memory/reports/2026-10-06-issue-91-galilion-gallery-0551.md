# #91 — פיילוט גלריה מורשית: מלון גליליון (#490)

תאריך: 06.10.2026, 05:51 שעון ישראל. כרטיס: https://github.com/rynrn/gonorth/issues/91; משימת־אב: https://github.com/rynrn/gonorth/issues/74. דף: https://gonorth.co.il/places/%d7%9e%d7%9c%d7%95%d7%9f-%d7%92%d7%9c%d7%99%d7%9c%d7%99%d7%95%d7%9f/

## תוצאה ומקורות

הגלריה השלישית בפיילוט מציגה שני צילומי חוץ מזוהים של מלון גליליון: מבנה אירוח עם מרפסות ועצים, וזווית נוספת לצד המדשאה וההרים. צילום המבנה הראשון החליף את תמונת הנושא הישנה #492, שנשמרה לשחזור. כותרת הגלריה והכיתובים מדגישים שאינם מציגים חדרים או מעידים על מצב המלון כיום. לצד כל תמונה מופיעים צלם, ייחוס, מקור, רישיון ועיבוד. לא הועתקה תמונה מאתר המלון ללא הרשאה.

מקורות שנבדקו ב־06.10.2026:
- https://commons.wikimedia.org/wiki/File:PikiWiki_Israel_78859_galilion_hotel.jpg — ישראל פרקר / Israel Preker; ייחוס Heritage Conservation Outside The City / PikiWiki Israel; CC BY 2.5, https://creativecommons.org/licenses/by/2.5/ .
- https://commons.wikimedia.org/wiki/File:PikiWiki_Israel_78866_galilion_hotel.jpg — אותו צלם וייחוס, CC BY 2.5.
- https://galilion.co.il/ — אתר המלון הרשמי לזיהוי המקום, לא מקור הרשאה להעתקת תמונותיו.

שני הקבצים כלולים בקטגוריית Galilion Hotel ב־Commons; imageinfo מזהה את שם המלון, היוצר, רישיון CC BY 2.5 ואת שנת 2021. נבדקו חזותית בגודל גדול. סקירת מנהל הסוכנים ותת־סוכן איכות תוכן אישרה פיילוט צילום חוץ בלבד עם גילוי מפורש שאין תצלום חדר. קובצי מקור מקומיים נשמרו תחת memory/work. הם הוקטנו ללא חיתוך והומרו ל־WebP: מבנה 1200×851, 204,954 בתים; מדשאה 1200×1129, 223,200 בתים. גרסאות ששירתו את Chrome ברוחב 390: 67,248 ו־84,944 בתים.

## שינוי ובדיקות

רמת סיכון L2; ממשק קנוני WordPress/WP-CLI. snapshot לפני שינוי: memory/work/issue91-490-before.json ובשרת /home/claude/issue91-490-before.json; thumbnail קודם #492. כל שדות העובדות הדינמיות נשארו ללא שינוי. tools/validate-place-records.py עבר עם קואורדינטות קיימות ואתר המלון הרשמי. המדיה יובאה לתיקייה ייעודית באמצעות --skip-copy, ללא שינוי הרשאות תיקיית ההעלאות החודשית; מזהים #532 ו־#533.

סקריפט memory/work/issue91-490-gallery.php עבר php -l ובדיקת מצב מקדים. apply מוגן checksum שינה רק post_content ותמונת נושא. המצב אחרי נשמר ב־memory/work/issue91-490-after.json ובשרת /home/claude/issue91-490-after.json. שחזור מוגן: wp eval-file /home/claude/issue91-490-gallery.php rollback --path=/var/www/gonorth --allow-root; יחזיר את התוכן ואת #492 רק אם תוכן הדף ותמונת הנושא עדיין תואמים למצב אחרי. קבצי המדיה החדשים לא נמחקים אוטומטית.

Chrome בשני רוחבים (390/1280): HTTP 200, H1 תקין, תמונת OG חדשה, שתי תמונות נטענות, ALT, srcset, lazy, קישורי מקור/רישיון וקרדיט גלוי, ללא שגיאות JS או גלישה אופקית. צילום מסך מובייל נבדק: memory/work/issue91-490-gallery-mobile.png. ב־1280 צילום ראשון נטען כ־204,954 בתים והשני כ־84,944 בתים; בגבול תקציב של פחות מ־300KB לשתי תמונות הגלריה יחד.

## גבולות והמשך

אין תצלום חדר בעל הרשאת שימוש נפרדת, ולכן לא הוצגה יחידת אירוח לדוגמה. צילום חוץ מ־2021 אינו הבטחה למצב היחידות כיום. זו גלריה שלישית מתוך עשרה מקומות נבחרים; שבעה נותרו למחקר/הרשאות או לחריג מנומק. #495 פארק דפנה נבדק, אך חיפוש Commons החזיר בעיקר תצלומי קיבוץ היסטוריים ולא זיהוי מדויק של הפארק; לא שונה. הרשאות 20 הנכסים הקיימים במדגם עדיין unknown. ללא רכישה.

status: partial
scope: listing 490, two licensed exterior photos, public mobile/desktop
risk_level: L2
canonical_writer: WordPress WP-CLI post/media APIs
changes_made: attachments 532/533, gallery post_content, thumbnail 532
unchanged: old attachment 492, dynamic place facts
validation: manager/content-quality review, PHP lint, validator, canonical guard, Chrome 390/1280 and screenshot
snapshot: memory/work/issue91-490-before.json
rollback: memory/work/issue91-490-gallery.php mode rollback, checksum-guarded
blockers: licensed current room photo and seven other pilot places
remaining_risks: exterior photos do not represent current room inventory; old media rights unresolved
issue_recommendation: keep-open

