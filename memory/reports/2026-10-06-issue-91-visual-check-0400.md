# #91 — בדיקה חזותית ל־20 תמונות מדגם

תאריך: 06.10.2026 04:00 (ישראל). כרטיס: https://github.com/rynrn/gonorth/issues/91. דוח זה משלים את `2026-10-06-issue-91-media-rights-audit-2300.md` ואינו דורס את נתוני המקור.

באמצעות Chrome הוצגו 20 קובצי התמונה שבמדגם הקודם, בחמש תמונות בכל גיליון בדיקה. כל 20 הקבצים נטענו והוצגו; צילומי המסך נמצאים ב־`memory/work/issue91-contact-1.png` עד `issue91-contact-4.png`. עבור כל תמונה נרשם מה נראה בפועל ב־`memory/data/2026-10-06-issue-91-visual-check-0400.json`.

ב־20/20 התמונות הנושא המצולם מתאים באופן סביר ל־ALT הקיים ברמת סוג התמונה: למשל עגלת קפה, אוהלי קמפינג, בריכה, חלל מסעדה, שביל מים או כלי רכיבה. זו בדיקת התאמה תיאורית בלבד. התמונה אינה מוכיחה שנלקחה אצל העסק המסוים, שהמצב המצולם עדיין עדכני, שהצלם זוהה, או שניתנה הרשאת פרסום. לכן `place_identity_verified_from_photo=false` בכל 20; הרשאת שימוש נשארת unknown כפי שתועדה בביקורת הראשונה. בפרט, צילום שרידי הטנק (#503) תואם לתיאור כללי של כלי משוריין בנחל, אך אינו מוכיח לבד את שם האתר; תמונות לינה אינן מבטיחות סוג חדר זמין כיום.

לא הוחלפו תמונות, לא שונו ALT או דפי מקום, ולא נוספו גלריות בלי מקור זכויות. הצעד הבא הוא זיהוי מקור והרשאת שימוש לכל נכס בפיילוט או צילום חלופי מורשה, ולאחר מכן יצירת גלריה אחת מדודה עם שחזור ואימות ביצועים לפני הרחבה.

```yaml
status: partial
scope: "20 featured images and ALT subject consistency"
risk_level: L0
canonical_writer: "none"
findings:
  - "20/20 images load and show a subject plausibly consistent with their ALT"
  - "Photograph alone does not verify place identity or reuse permission"
sources:
  - {url: "memory/data/2026-10-06-issue-91-media-rights-audit.json", checked_at: "2026-10-06"}
  - {url: "memory/work/issue91-contact-1.png", checked_at: "2026-10-06"}
changes_made: []
unchanged:
  - {item: "WordPress images, ALT and galleries", reason: "rights evidence is still missing"}
validation:
  - {check: "Chrome image load and visual inspection of 20 files", result: pass, evidence: "memory/data/2026-10-06-issue-91-visual-check-0400.json and four contact sheets"}
snapshot: "not-required"
rollback: "no live change"
blockers:
  - "specific reuse permission or licensed replacement for each pilot image"
remaining_risks:
  - "business identity and current state not confirmed by photographs"
issue_recommendation: keep-open
```
