# #93 — נחל המערות: הסתייגות ניווט מונעת רשומה חדשה בשלב זה

תאריך: 06.10.2026 06:45 שעון ישראל. סיכון L0. לא נעשה שינוי באתר או בתור המקומות.

[רשות הטבע והגנים](https://www.parks.org.il/reserve-park/שמורת-טבע-נחל-המערות/) מתארת שמורה פעילה בכרמל ואת מסלולי מערות האדם הקדמון. הוראות ההגעה שלה חד־משמעיות: הכניסה מכביש 4 בין גבע כרמל לעין כרמל; לבאים מצפון אסור לפנות שמאלה ויש להסתובב סמוך לגבע כרמל. המפעיל מזהיר מפורשות שטעות Waze עלולה להפנות דרך כרם מהרל, שאינה דרך הכניסה. לכן קואורדינטת Waze לבדה אינה הוכחת שער או הוראת נסיעה בטוחה.

[עמוד Waze בשם השמורה](https://www.waze.com/en-GB/live-map/directions/שמורת-טבע-נחל-המערות?to=place.w.22937927.229117123.549523) חושף בשדה `routing.to.latLng` את 32.67078183398377,34.9655800201109. הראיה הטכנית נשמרה ב[קובץ מחקר](../data/2026-10-06-issue-93-mearot-waze-research.json). ניתן לתארו לכל היותר כיעד ניווט לשמורה עם הסתייגות, אך אין להשתמש בו ככניסת רכב מאומתת או להציג הוראות Waze ללא אזהרת המפעיל. נדרש אימות נקודת תחילת הביקור על כביש 4 לפני יצירת רשומה הכוללת GPS.

בדיקה עצמאית של תמונות Commons מצאה את [Nahal-mearot-hoehlen.JPG](https://commons.wikimedia.org/wiki/File:Nahal-mearot-hoehlen.JPG) מאת Mboesch, 2015, CC BY-SA 4.0, כמועמד רחב־פריים; [El-Wad Cave DSCN6888](https://commons.wikimedia.org/wiki/File:El-Wad_Cave,_Nahal_Me'arot,_Israel_-_2019-12-21_-_DSCN6888.jpg) מאת DmitTrix, 2019, CC0, כמועמד ממוקד במערת אל־וואד. הקובץ [Nahal Mearot caves.jpg](https://commons.wikimedia.org/wiki/File:Nahal_Mearot_caves.jpg) קטן 480×360 ולכן לא מתאים לתמונת נושא. הרכב התמונה הראשונה טרם נבדק חזותית באופן מספק; לא הועלתה מדיה.

המשך: לאמת נקודת כניסה נגישה מכביש 4 במקור מפעיל/מפה מהימנה, לבדוק חזותית את צילום Commons, לחזור על בדיקת כפילות, ולהעביר כרטיס ביקורת מנהל לפני יצירת pending. שעות, מחיר וטלפון נשארים unknown עד מסלול place-facts.

```yaml
status: partial
scope: "read-only route and photo research for Nahal Me'arot candidate #116"
risk_level: L0
canonical_writer: none
findings:
  - "RTG explicitly warns Waze can route via Kerem Maharal in error"
  - "Waze numeric point is a named reserve destination, not a verified vehicle entrance"
sources:
  - {url: "https://www.parks.org.il/reserve-park/שמורת-טבע-נחל-המערות/", checked_at: "2026-10-06"}
  - {url: "https://www.waze.com/en-GB/live-map/directions/שמורת-טבע-נחל-המערות?to=place.w.22937927.229117123.549523", checked_at: "2026-10-06"}
  - {url: "https://commons.wikimedia.org/wiki/File:Nahal-mearot-hoehlen.JPG", checked_at: "2026-10-06"}
  - {url: "https://commons.wikimedia.org/wiki/File:El-Wad_Cave,_Nahal_Me'arot,_Israel_-_2019-12-21_-_DSCN6888.jpg", checked_at: "2026-10-06"}
changes_made: []
unchanged:
  - {item: "WordPress listing and media", reason: "Vehicle entrance and final visual photo unverified"}
  - {item: "Candidate queue", reason: "Already pending research"}
validation:
  - {check: "Official directions cross-check", result: pass, evidence: "RTG warns of Waze misroute"}
  - {check: "Commons metadata review", result: pass, evidence: "Two licensed candidates; visual selection pending"}
snapshot: not-required
rollback: "No live or queue change"
blockers:
  - "Verified visitor entrance point and visual image selection"
remaining_risks:
  - "Routing to a reserve centroid may lead via inaccessible Kerem Maharal"
issue_recommendation: keep-open
```