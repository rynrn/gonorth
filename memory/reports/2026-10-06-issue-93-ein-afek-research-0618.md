# #93 — עין אפק: מסלול רשמי וצילום מועמד, GPS ואזור טרם הוכרעו

תאריך: 06.10.2026. סיכון L0; לא שונה האתר ולא נוצרה רשומה.

[רשות הטבע והגנים](https://www.parks.org.il/reserve-park/%D7%A9%D7%9E%D7%95%D7%A8%D7%AA-%D7%98%D7%91%D7%A2-%D7%A2%D7%99%D7%9F-%D7%90%D7%A4%D7%A7/) מתארת הגעה מכביש 4 מזרחה לכביש 79, פנייה לרחוב חוגלה ובהמשך לכביש 7911. בהנחיות הניווט נכתב לחפש בוויז "שמורת טבע עין אפק", ללא קואורדינטה מפורשת של כניסת המבקרים. נתוני Wikidata/OSM שנמצאו מתארים את שטח השמורה ולא מוכיחים נקודת כניסה. לכן GPS לשדה GeoDirectory נשאר unknown. דף הרשות מציין טחנת קמח, תל אפק וגשר צף, וכן שהגשר עצמו אינו נגיש במלואו; שעות ומחיר לא יועתקו לשדה חי ללא אימות דינמי.

נבדקו חזותית שלושה צילומי Commons. הצילום המייצג ביותר הוא [הגשר הצף מעל הביצה](https://commons.wikimedia.org/wiki/File:Tel_afek_-_The_wooden_flow_bridge_over_the_swamp.jpg), Roni250b, משנת 2014 לפי נתוני Commons, ברישיון [CC BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0/). בתצלום נראה שביל עץ מעוקל מעל מים; הוא ממחיש מוקד ביקור שהרשות מתארת, ולא שער כניסה או מצב נגישות עכשווי. ALT מוצע: "שביל עץ מעל מים בשמורת טבע עין אפק". שני צילומי [צמחיית הגדה](https://commons.wikimedia.org/wiki/File:124387_ein_afek_nature_reserve-_PikiWiki_Israel.jpg) ו[אדם על הגשר](https://commons.wikimedia.org/wiki/File:124394_ein_afek_nature_reserve_-_the_swamp_road_PikiWiki_Israel.jpg) מאת שלמה רודד, CC BY 2.5, גם הם מזוהים עם השמורה, אך פחות מתאימים כתמונת נושא. צילומי בדיקה קטנים נשמרו ב־`memory/work/issue93-ein-afek-photo-{a,b,c}.jpg`; דבר לא הועלה ל־WordPress.

נדרש גם להכריע שיוך אזור תוכן: עמק זבולון/אזור קריית ביאליק אינו מוזכר במפורש בגבולות `galil` או `carmel` ב־`docs/region-and-category-taxonomy.md`. אין לשייך אוטומטית רק כדי למלא שדה. סוג המקום מתאים ל־`teva` (טבע ומסלולים), בכפוף לביקורת התוכן. לפני יצירה: GPS כניסה מאומת ממפה/מפעיל, החלטת אזור, כפילות עדכנית, validator וכרטיס אישור. #93 נשאר פתוח.

```yaml
status: partial
scope: "Ein Afek reserve research for issue 93"
risk_level: L0
canonical_writer: "none"
findings:
  - "Official directions and Waze name are available; precise visitor entrance GPS is not"
  - "Identified visually suitable 2014 Commons image of floating bridge, CC BY-SA 3.0"
sources:
  - {url: "https://www.parks.org.il/reserve-park/שמורת-טבע-עין-אפק/", checked_at: "2026-10-06"}
  - {url: "https://commons.wikimedia.org/wiki/File:Tel_afek_-_The_wooden_flow_bridge_over_the_swamp.jpg", checked_at: "2026-10-06"}
changes_made:
  - "This local report only"
unchanged:
  - {item: "WordPress", reason: "Visitor GPS and content region unresolved"}
validation:
  - {check: "Three candidate thumbnails visual review", result: pass, evidence: "memory/work/issue93-ein-afek-photo-{a,b,c}.jpg"}
snapshot: "not-required"
rollback: "delete this L0 report if incorrect"
blockers:
  - "Need verified visitor entrance GPS"
  - "Need explicit content-region assignment for the Acre/Zvulun valley edge"
remaining_risks:
  - "2014 photo is historical and does not establish current accessibility"
issue_recommendation: keep-open
```
