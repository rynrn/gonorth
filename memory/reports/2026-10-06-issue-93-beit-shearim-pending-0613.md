# #93 — גן לאומי בית שערים: רשומה ממתינה עם צילום מורשה

תאריך: 06.10.2026 06:13 שעון ישראל. כרטיס: https://github.com/rynrn/gonorth/issues/93; משימת־אב: https://github.com/rynrn/gonorth/issues/74. רמת סיכון L2. הרשומה אינה מפורסמת.

אחרי בדיקת 128 רשומות בכל הסטטוסים ללא כפילות, ביקורת תוכן עצמאית ואישור מנהל, נוצרה רשומת `gd_place` מספר 534 בסטטוס `pending`. הוגדרו סוג `atraktziot-main` ואזור תוכן `carmel`. נקודת הניווט למבקרים `32.7027036,35.1275862` נשמרה דרך GeoDirectory REST המאומת, עם `country=ישראל`, `region=צפון`; עיר ורחוב נותרו ריקים כי לא אומתו. טלפון, אתר בשדה הדינמי, שעות, מחיר ודירוג נותרו ריקים עד מסלול place-facts. התוכן העריכתי מקשר לאתר המפעיל ומפנה אליו לבדיקת מידע משתנה.

צילום חזית מערת רבי יהודה הנשיא מ־2019 מאת Davidbena הובא מ־[Wikimedia Commons](https://commons.wikimedia.org/wiki/File:Catacomb_no._14,_The_Cave_of_Rabbi_Yehuda_HaNasi.jpg) ברישיון [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/). מדיה 535 משויכת לרשומה כתמונה ראשית, עם ALT ממוקד, כיתוב, קרדיט וקישורים גלויים בגוף הרשומה. WordPress יצר גרסת 1920×1280 וגדלים רספונסיביים; הציון שהצילום הוקטן נוסף לגוף ולכיתוב. המקור המקורי נשמר בשרת ב־`wp-content/uploads/issue93-licensed/beit-shearim-cave14-davidbena-2019.jpg`, SHA-256 `a3a8abe729f9a9fee47bdd53d71d2855893a8e497dfb9667390eb80aa2602cf6`. אין להציג את הצילום כשער הגן או ראיה למצב הגישה הנוכחי.

מקור העובדות: [רשות הטבע והגנים](https://www.parks.org.il/reserve-park/%D7%92%D7%9F-%D7%9C%D7%90%D7%95%D7%9E%D7%99-%D7%91%D7%99%D7%AA-%D7%A9%D7%A2%D7%A8%D7%99%D7%9D/), נבדק 06.10.2026. [כרטיס הביקורת](../drafts/2026-10-06-issue-93-beit-shearim-approval.md) מתעד את ההחלטות. `tools/validate-place-records.py` עבר ללא שגיאות או אזהרות על [רשומת האימות](../data/2026-10-06-issue-93-beit-shearim-validation.json).

אימות אחרי שינוי: WP-CLI אישר `pending`, שתי הטקסונומיות, תמונה ראשית 535, שיוך attachment, קישורי מקור ורישיון בתוכן. GeoDirectory REST מאומת אישר GPS מדויק, שדות עיר/רחוב ריקים, טלפון ואתר דינמיים `null`. בדיקה ללא הזדהות החזירה 401 ל־API הפרטי ו־404 לכתובת הדף; קובץ התמונה מחזיר 200. זוהי התנהגות צפויה לרשומה ממתינה, ולא קישור ציבורי תקין.

Snapshot לפני שינוי: [מלאי 128 הרשומות](../data/2026-10-06-issue-93-beit-shearim-before-inventory.json). מצב התחלתי של הרשומה לפני מיקום: [post](../data/2026-10-06-issue-93-beit-shearim-created-post.json), [GeoDirectory](../data/2026-10-06-issue-93-beit-shearim-before-location.json). מצב סופי: [post](../data/2026-10-06-issue-93-beit-shearim-after-post.json), [media](../data/2026-10-06-issue-93-beit-shearim-after-media.json), [GeoDirectory](../data/2026-10-06-issue-93-beit-shearim-after-location.json). [סקריפט השחזור](../scripts/issue93-beit-shearim-rollback.ps1) בודק מזהה, סטטוס, תוכן, זמן שינוי, מדיה, מיקום ו־checksum לפני הסרת הרשומה הממתינה והקובץ; מצב dry-run עבר, `-Apply` לא הופעל.

המשך: ביקורת מנהל המערכת ב־WordPress; רק לאחר אישור פרסום אפשר לבצע בדיקות דף ציבורי ולקשר ממדריך רלוונטי. #93 נשאר פתוח; יתר 11 המועמדים טרם הוכרעו.

```yaml
status: partial
scope: "pending Beit Shearim gd_place #534 with licensed media #535"
risk_level: L2
canonical_writer: "WordPress CLI editorial/terms/media; authenticated GeoDirectory REST location"
findings:
  - "No duplicate in 128 listings across statuses"
  - "Official visitor point verified; city/street remain unknown"
sources:
  - {url: "https://www.parks.org.il/reserve-park/גן-לאומי-בית-שערים/", checked_at: "2026-10-06"}
  - {url: "https://commons.wikimedia.org/wiki/File:Catacomb_no._14,_The_Cave_of_Rabbi_Yehuda_HaNasi.jpg", checked_at: "2026-10-06"}
changes_made:
  - "Created pending gd_place #534 and featured licensed media #535"
  - "Updated candidate #113 as pending in places-to-add.md"
unchanged:
  - {item: "Public listing and guide links", reason: "Pending record cannot be linked publicly"}
  - {item: "Dynamic phone, website, hours, price, rating", reason: "Require place-facts review or remain unknown"}
validation:
  - {check: "tools/validate-place-records.py", result: pass, evidence: "0 errors, 0 warnings"}
  - {check: "Canonical post, taxonomy, GeoDirectory, media", result: pass, evidence: "ID 534 pending; media 535"}
  - {check: "Unauthenticated page/API", result: pass, evidence: "404/401 while pending"}
  - {check: "Guarded rollback dry-run", result: pass, evidence: "Guard passed; no changes made"}
snapshot: "memory/data/2026-10-06-issue-93-beit-shearim-before-inventory.json"
rollback: "pwsh -File memory/scripts/issue93-beit-shearim-rollback.ps1 -Apply, only if guard passes"
blockers:
  - "Administrator publication review is needed for a public listing and guide link"
remaining_risks:
  - "Photo is archival; availability and access can change"
issue_recommendation: keep-open
```
