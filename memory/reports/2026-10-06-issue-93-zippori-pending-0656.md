# #93 — גן לאומי ציפורי: רשומה ממתינה ותמונה מורשית

תאריך: 06.10.2026 06:56 שעון ישראל. כרטיס: https://github.com/rynrn/gonorth/issues/93; משימת־אב: https://github.com/rynrn/gonorth/issues/74. סיכון L2. הרשומה אינה מפורסמת.

מנהל המשימה וביקורת תוכן עצמאית דחו תחילה את הניסוח בשל קרדיט Commons חסר והצגת יעד Waze לא ברורה. שני הסעיפים תוקנו והתקבל אישור מנהל ליצירת `pending` בלבד. [רשות הטבע והגנים](https://www.parks.org.il/reserve-park/גן-לאומי-ציפורי/) מתארת את הרחובות, הפסיפסים, בית הכנסת והתיאטרון ומפנה להגעה מכביש 79 בין ק״מ 22–23 ולניווט בשם הגן. היא מדווחת כעת על סגירת תצפית פסיפס אורפיאוס ועל פתיחה חלקית של מאגר המים. התוכן מפנה לבדיקה מעודכנת אצל המפעיל סמוך לביקור ואינו מקבע מידע זמני בשדות דינמיים.

[יעד Waze בשם הגן על כביש 79](https://www.waze.com/he/live-map/directions/il/מחוז-הצפון/גן-לאומי-ציפורי?to=place.ChIJV8mh6SDXAhURf19Ces5W-G0) חושף `routing.to.latLng` 32.7508232,35.2845292. [תוצאת Waze נוספת](https://www.waze.com/live-map/directions/גן-לאומי-ציפורי?to=place.w.23134536.231214283.3155) מציבה יעד קרוב אך שונה, 32.750840688837926,35.28493400175353. נבחרה התוצאה הראשונה כי היא מציינת כביש 79 וקשורה לאתר המפעיל; היא מוצגת כיעד ניווט למבקרים, לא כשער פיזי מאומת. ההבחנה מופיעה בגוף הרשומה.

בדיקת כפילות חוזרת בכל 131 רשומות `gd_place` שלפני השינוי לא מצאה שם/slug תואם. נוצרה רשומה #540 במצב `pending`, קטגוריה `atraktziot-main`, אזור תוכן `galil`, ותוכן עברי מקורי. קואורדינטות, מדינה ישראל ואזור צפון נשמרו דרך GeoDirectory REST המאומת; עיר ורחוב ריקים. טלפון, אתר דינמי, שעות ומחיר נשארו unknown. GeoDirectory מציג דירוג ברירת מחדל 0 ללא ביקורות; לא הועתק דירוג לא מבוסס.

צילום [מבט על גן לאומי ציפורי](https://commons.wikimedia.org/wiki/File:PikiWiki_Israel_73447_zippori.jpg) מאת זאב שטיין, ייחוס Commons המדויק Heritage Conservation Outside The City / PikiWiki Israel, ברישיון [CC BY 2.5](https://creativecommons.org/licenses/by/2.5/) נבדק חזותית מול שתי חלופות. הוא מציג את המצודה, עתיקות ותיאטרון בפריים אחד; חתימת הצלם נשמרה. המדיה #541 נוספה עם ALT, כיתוב וקישורי מקור ורישיון, וקרדיט גלוי בגוף. קובץ המקור נדחס אוטומטית מ־1,639,320 בתים; WordPress יצר 11 גדלי תצוגה. SHA-256 מקורי מקומי: `44a0b0f10c8b58bc10951ec1775a7618b82ac4ab4dc307b568864264ef10094d`; מקור מאוחסן לאחר דחיסה: `0131c73d212a4ffb2315ceba2da4276eca1398b3da2614a40a085446f7818a14`.

אימות: `tools/validate-place-records.py` עבר 0 שגיאות/אזהרות על [קלט האימות](../data/2026-10-06-issue-93-zippori-validation.json). WP-CLI אימת `pending`, קטגוריה, אזור, תמונה ראשית #541, ALT וקרדיט; GeoDirectory REST אימת מיקום ושדות ריקים. דף ציבורי החזיר 404 ו־API ללא הזדהות 401; קובץ התמונה החזיר 200. [שחזור ממוקד](../scripts/issue93-zippori-rollback.ps1) עבר dry-run ללא שינוי. `-Apply` לא הופעל.

ראיות: [כרטיס ביקורת](../drafts/2026-10-06-issue-93-zippori-approval.md), [מלאי קודם](../data/2026-10-06-issue-93-zippori-before-inventory.json), [post אחרי](../data/2026-10-06-issue-93-zippori-after-post.json), [media אחרי](../data/2026-10-06-issue-93-zippori-after-media.json), [GeoDirectory לפני](../data/2026-10-06-issue-93-zippori-before-location.json), [GeoDirectory אחרי](../data/2026-10-06-issue-93-zippori-after-location.json). מועמד #112 עודכן כממתין לביקורת מנהל WordPress. רק לאחר פרסום יש לבדוק דף ציבורי ולקשר ממדריך רלוונטי. #93 נשאר פתוח.

```yaml
status: partial
scope: "pending Tzippori gd_place #540 with licensed media #541"
risk_level: L2
canonical_writer: "WordPress CLI editorial/terms/media; authenticated GeoDirectory REST location"
findings:
  - "No duplicate among 131 listings across statuses"
  - "Navigation destination is not a verified physical entrance"
sources:
  - {url: "https://www.parks.org.il/reserve-park/גן-לאומי-ציפורי/", checked_at: "2026-10-06"}
  - {url: "https://www.waze.com/he/live-map/directions/il/מחוז-הצפון/גן-לאומי-ציפורי?to=place.ChIJV8mh6SDXAhURf19Ces5W-G0", checked_at: "2026-10-06"}
  - {url: "https://commons.wikimedia.org/wiki/File:PikiWiki_Israel_73447_zippori.jpg", checked_at: "2026-10-06"}
changes_made:
  - "Created pending gd_place #540 and licensed featured media #541"
  - "Updated candidate #112 as pending in places-to-add.md"
unchanged:
  - {item: "Public listing and guide links", reason: "Pending record is not public"}
  - {item: "Dynamic place facts", reason: "Require place-facts review or remain unknown"}
validation:
  - {check: "tools/validate-place-records.py", result: pass, evidence: "0 errors, 0 warnings"}
  - {check: "Canonical post, taxonomy, location, media", result: pass, evidence: "pending #540, media #541"}
  - {check: "Unauthenticated page/API/media", result: pass, evidence: "404/401/200 as expected"}
  - {check: "Guarded rollback dry-run", result: pass, evidence: "Guard passed, no changes made"}
snapshot: "memory/data/2026-10-06-issue-93-zippori-before-inventory.json"
rollback: "pwsh -File memory/scripts/issue93-zippori-rollback.ps1 -Apply, only if guard passes"
blockers:
  - "WordPress administrator publication review and public guide link"
remaining_risks:
  - "Navigation point is not a verified gate; operator access restrictions may change"
issue_recommendation: keep-open
```