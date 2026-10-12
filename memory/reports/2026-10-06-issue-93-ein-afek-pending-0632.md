# #93 — שמורת טבע עין אפק: רשומה ממתינה עם יעד ניווט וצילום מורשה

תאריך: 06.10.2026 06:32 שעון ישראל. כרטיס: https://github.com/rynrn/gonorth/issues/93; משימת־אב: https://github.com/rynrn/gonorth/issues/74. סיכון L2. רשומה 536 אינה מפורסמת.

מנהל המשימה ובדיקה גאוגרפית עצמאית הכריעו שהשמורה נכללת באזור התוכן `galil` של האתר, בקצה הדרומי של הגליל המערבי. [רשות הטבע והגנים](https://www.parks.org.il/reserve-park/%D7%A9%D7%9E%D7%95%D7%A8%D7%AA-%D7%98%D7%91%D7%A2-%D7%A2%D7%99%D7%9F-%D7%90%D7%A4%D7%A7/) ממקמת אותה בעמק עכו ליד קריית ביאליק ומנחה לנווט ב־Waze בשם השמורה; [זמן גליל מערבי](https://www.westgalil.org.il/ent/en-afek-nature-reserve/) כוללת אותה באזור זה. יעד השמורה הנקוב ב־[Waze](https://www.waze.com/he/live-map/directions/il/%D7%9E%D7%97%D7%95%D7%96-%D7%94%D7%A6%D7%A4%D7%95%D7%9F/%D7%A7%D7%A8%D7%99%D7%99%D7%AA-%D7%91%D7%99%D7%90%D7%9C%D7%99%D7%A7/%D7%A9%D7%9E%D7%95%D7%A8%D7%AA-%D7%94%D7%98%D7%91%D7%A2-%D7%A2%D7%99%D7%9F-%D7%90%D7%A4%D7%A7?to=place.ChIJ0Rm4sua2HRURfJ5EpikCjOQ) מכיל `32.847661,35.111361`; זהו יעד ניווט למבקרים, לא שער פיזי מאומת. חניית Waze ומרכז השמורה הם נקודות אחרות ולא שימשו כתחליף. ההבחנה כתובה גם בגוף הרשומה.

בדיקת כפילות חוזרת בכל 129 רשומות `gd_place` לא מצאה התאמת שם או slug. אחריה נוצרה רשומה `pending` #536, סוג `teva`, אזור `galil`, ותוכן עברי מקורי. יעד Waze נשמר דרך GeoDirectory REST המאומת; country=ישראל, region=צפון, city/street ריקים. טלפון, אתר בשדה הדינמי, שעות, מחיר ודירוג נשארו unknown עד place-facts. אין טענה על זמינות נוכחית מתוך צילום ישן.

צילום [הגשר הצף מעל הביצה](https://commons.wikimedia.org/wiki/File:Tel_afek_-_The_wooden_flow_bridge_over_the_swamp.jpg) מאת Roni250b ברישיון [CC BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0/) נבדק חזותית ונוסף כמדיה #537 עם ALT, כיתוב, קישור מקור ורישיון וקרדיט גלוי. WordPress יצר גרסת תצוגה 1920×878 ו־12 גדלים; אופטימיזציה אוטומטית גם דחסה את קובץ המקור בשרת מ־2,053,174 ל־1,900,345 בתים. ציון ההתאמה בגודל ובדחיסה נוסף לכיתוב ולגוף. SHA-256 של המקור המקומי: `5002eb3d4744549c08a1fdec5585c896d8346ac7c7a488defd568392355cf2a8`; של הקובץ המאוחסן לאחר אופטימיזציה: `a64b00629c8eba6b0623b4498dc77bbe556eff9714f856812725717e68426bcf`. הקובץ בשרת `wp-content/uploads/issue93-licensed/ein-afek-floating-bridge-roni250b.jpg`.

אימות: `tools/validate-place-records.py` עבר 0 שגיאות/אזהרות על [קלט האימות](../data/2026-10-06-issue-93-ein-afek-validation.json). WP-CLI אישר `pending`, `teva`, `galil`, תמונה ראשית 537, ALT, קרדיט והסתייגות Waze. GeoDirectory REST אישר קואורדינטות ושדות דינמיים ריקים. בדיקה ללא הזדהות החזירה 404 לעמוד, 401 ל־API הפרטי ו־200 לקובץ התמונה — צפוי לרשומה ממתינה. [שחזור ממוקד](../scripts/issue93-ein-afek-rollback.ps1) עבר dry-run ללא שינוי; הוא עוצר אם הרשומה, המיקום, המדיה או checksum השתנו.

ראיות: [כרטיס ביקורת](../drafts/2026-10-06-issue-93-ein-afek-approval.md), [מלאי לפני שינוי](../data/2026-10-06-issue-93-ein-afek-before-inventory.json), [post התחלתי](../data/2026-10-06-issue-93-ein-afek-created-post.json), [GeoDirectory לפני](../data/2026-10-06-issue-93-ein-afek-before-location.json), [post סופי](../data/2026-10-06-issue-93-ein-afek-after-post.json), [media סופי](../data/2026-10-06-issue-93-ein-afek-after-media.json), [GeoDirectory אחרי](../data/2026-10-06-issue-93-ein-afek-after-location.json). דוח [מחקר קודם](2026-10-06-issue-93-ein-afek-research-0618.md) מתעד מצב לפני הכרעת המנהל; חסמי GPS/אזור שבו נפתרו רק עבור יעד ניווט מוצהר.

המשך: ביקורת מנהל WordPress לפני פרסום. רק לאחר פרסום יש לבדוק דף ציבורי ולהוסיף קישור ממדריך רלוונטי. #93 נשאר פתוח.

```yaml
status: partial
scope: "pending Ein Afek gd_place #536 with licensed media #537"
risk_level: L2
canonical_writer: "WordPress CLI editorial/terms/media; authenticated GeoDirectory REST location"
findings:
  - "No duplicate among 129 listings across statuses"
  - "Waze coordinate is named reserve navigation target, not physical gate"
sources:
  - {url: "https://www.parks.org.il/reserve-park/שמורת-טבע-עין-אפק/", checked_at: "2026-10-06"}
  - {url: "https://www.waze.com/he/live-map/directions/il/מחוז-הצפון/קריית-ביאליק/שמורת-הטבע-עין-אפק?to=place.ChIJ0Rm4sua2HRURfJ5EpikCjOQ", checked_at: "2026-10-06"}
  - {url: "https://commons.wikimedia.org/wiki/File:Tel_afek_-_The_wooden_flow_bridge_over_the_swamp.jpg", checked_at: "2026-10-06"}
changes_made:
  - "Created pending gd_place #536 and licensed featured media #537"
  - "Updated candidate #114 as pending in places-to-add.md"
unchanged:
  - {item: "Public listing and guide links", reason: "Pending record is not public"}
  - {item: "Dynamic fields", reason: "Require place-facts review or remain unknown"}
validation:
  - {check: "tools/validate-place-records.py", result: pass, evidence: "0 errors, 0 warnings"}
  - {check: "Canonical post, taxonomy, location, media", result: pass, evidence: "pending #536, media #537"}
  - {check: "Unauthenticated page/API", result: pass, evidence: "404/401 as expected"}
  - {check: "Guarded rollback dry-run", result: pass, evidence: "Guard passed, no changes made"}
snapshot: "memory/data/2026-10-06-issue-93-ein-afek-before-inventory.json"
rollback: "pwsh -File memory/scripts/issue93-ein-afek-rollback.ps1 -Apply, only if guard passes"
blockers:
  - "Administrator publication review and public guide link"
remaining_risks:
  - "Waze destination is not a physical entrance guarantee; historical photo does not establish present access"
issue_recommendation: keep-open
```
