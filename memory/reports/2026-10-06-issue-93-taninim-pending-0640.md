# #93 — שמורת טבע נחל תנינים: רשומה ממתינה ותמונה מורשית

תאריך: 06.10.2026 06:40 שעון ישראל. כרטיס: https://github.com/rynrn/gonorth/issues/93; משימת־אב: https://github.com/rynrn/gonorth/issues/74. סיכון L2. הרשומה אינה מפורסמת.

לאחר ביקורת מנהל וביקורת תוכן עצמאית אושרה יצירת רשומה ממתינה בלבד. [רשות הטבע והגנים](https://www.parks.org.il/reserve-park/שמורת-טבע-נחל-תנינים/) מתארת הגעה מכיוון בית חנניה וחניה מחוץ לשמורה. [Waze](https://www.waze.com/live-map/directions/שמורת-טבע-נחל-תנינים?to=place.w.22872389.228854967.517961) מספק את יעד הניווט בשם השמורה: 32.547591853,34.915458312 בשדה `routing.to.latLng`. זהו יעד ניווט, לא שער כניסה פיזי מאומת; ההסתייגות מופיעה בגוף הרשומה. [ראיית הניווט](../data/2026-10-06-issue-93-taninim-waze-navigation-evidence.json) כוללת מקור ומועד.

בדיקת כפילות בכל 130 רשומות `gd_place` שלפני השינוי לא מצאה התאמת שם או slug. נוצרה רשומה #538 במצב `pending`, סוג `teva`, אזור `carmel`, ותוכן עברי מקורי. קואורדינטות, מדינה ישראל ואזור צפון נשמרו דרך GeoDirectory REST המאומת; עיר ורחוב נותרו ריקים. טלפון, אתר בשדה הדינמי, שעות, מחיר ודירוג נשארו unknown עד לבדיקת place-facts.

צילום [גלגל טחנת קמח בנחל תנינים](https://commons.wikimedia.org/wiki/File:PikiWiki_Israel_48361_Taninim_River_Reserve.jpg) מאת אלישבע ורנר-רייז / PikiWiki Israel ברישיון [CC BY 2.5](https://creativecommons.org/licenses/by/2.5/) נבדק חזותית ונוסף כמדיה #539 עם ALT, כיתוב, קישור מקור ורישיון וקרדיט גלוי. הכיתוב והגוף מציינים שגרסאות התצוגה הוקטנו/נדחסו ושצילום עבר אינו מוכיח מצב פעילות נוכחי. SHA-256 מקומי: `f465c24fd27b8dc6d90446780de640fb28a626f1f5f837710acfe1d62adfabf6`; SHA-256 של הקובץ בשרת לאחר אופטימיזציה: `d21002d19c6d61dc5f9ef446c92d2b0cf0a4d42d4cab2af594eb8a0e6d4566d5`.

אימות: `tools/validate-place-records.py` עבר עם 0 שגיאות ו־0 אזהרות על [קלט האימות](../data/2026-10-06-issue-93-taninim-validation.json). WP-CLI אישר מצב, קטגוריה, אזור, תמונה ראשית, ALT וקרדיט; GeoDirectory REST אישר מיקום ושדות ריקים. בדיקה ללא הזדהות החזירה 404 לדף, 401 ל־API ו־200 לקובץ התמונה. [שחזור ממוקד](../scripts/issue93-taninim-rollback.ps1) עבר dry-run ללא שינוי; `-Apply` לא הופעל.

ראיות: [כרטיס ביקורת](../drafts/2026-10-06-issue-93-taninim-approval.md), [מלאי קודם](../data/2026-10-06-issue-93-taninim-before-inventory.json), [רשומה אחרי](../data/2026-10-06-issue-93-taninim-after-post.json), [מדיה אחרי](../data/2026-10-06-issue-93-taninim-after-media.json), [מיקום לפני](../data/2026-10-06-issue-93-taninim-before-location.json), [מיקום אחרי](../data/2026-10-06-issue-93-taninim-after-location.json). [דוח המחקר הקודם](2026-10-06-issue-93-taninim-research-0622.md) תיעד GPS חסר לפני הכרעת המנהל; דוח זה מעדכן אותו לגבי יעד ניווט מסומן.

המשך: ביקורת מנהל WordPress לפני פרסום, ואז בדיקת דף ציבורי וקישור ממדריך רלוונטי. #93 נשאר פתוח.

```yaml
status: partial
scope: "pending Nahal Taninim gd_place #538 with licensed media #539"
risk_level: L2
canonical_writer: "WordPress CLI editorial/terms/media; authenticated GeoDirectory REST location"
findings:
  - "No duplicate among 130 listings across statuses"
  - "Waze coordinate is named reserve navigation target, not verified physical gate"
sources:
  - {url: "https://www.parks.org.il/reserve-park/שמורת-טבע-נחל-תנינים/", checked_at: "2026-10-06"}
  - {url: "https://www.waze.com/live-map/directions/שמורת-טבע-נחל-תנינים?to=place.w.22872389.228854967.517961", checked_at: "2026-10-06"}
  - {url: "https://commons.wikimedia.org/wiki/File:PikiWiki_Israel_48361_Taninim_River_Reserve.jpg", checked_at: "2026-10-06"}
changes_made:
  - "Created pending gd_place #538 and licensed featured media #539"
  - "Updated candidate #115 as pending in places-to-add.md"
unchanged:
  - {item: "Public listing and guide links", reason: "Pending record is not public"}
  - {item: "Dynamic fields", reason: "Require place-facts review or remain unknown"}
validation:
  - {check: "tools/validate-place-records.py", result: pass, evidence: "0 errors, 0 warnings"}
  - {check: "Canonical post, taxonomy, location, media", result: pass, evidence: "pending #538, media #539"}
  - {check: "Unauthenticated page/API/media", result: pass, evidence: "404/401/200 as expected"}
  - {check: "Guarded rollback dry-run", result: pass, evidence: "Guard passed, no changes made"}
snapshot: "memory/data/2026-10-06-issue-93-taninim-before-inventory.json"
rollback: "pwsh -File memory/scripts/issue93-taninim-rollback.ps1 -Apply, only if guard passes"
blockers:
  - "Administrator publication review and public guide link"
remaining_risks:
  - "Waze destination is not a physical entrance guarantee; historical photo does not establish present access"
issue_recommendation: keep-open
```