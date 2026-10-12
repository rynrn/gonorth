# Issue #100 — אצווה F פורסמה

תאריך: 2026-10-01

## סיכום

פורסמו שני דפי מקום חדשים לאחר בדיקת כפילויות, ולידציה, יצירה במצב pending, חיבור תמונות Google Places ואימות קנוני וציבורי.

- מלון גליליון — ID 490; תמונה ID 492.
- שמורת עין תאו — ID 491; תמונה ID 493.

## מקורות

- מלון גליליון: https://galilion.co.il/landingpage/ וכן Google Places, נבדקו ב־2026-10-01.
- שמורת עין תאו: https://inature.info/wiki/שמורת_עין_תאו, דף רט״ג על השמורה וכן Google Places, נבדקו ב־2026-10-01.

## החלטות תוכן

- למלון הוזנו טלפון ואתר רשמי, אך לא הוזנו שעות או מחירים משום שהם תלויי חבילה ועונה.
- שמורת עין תאו הוצגה כאתר טבע לא מוסדר. צוין במפורש שאסור להיכנס לבריכה הטבעית הגדולה, שאין שירותי מטיילים ושיש לבדוק גישה עדכנית. לא הוצגו שעות פעילות.
- התמונות נבחרו מתוך שלוש מועמדות לכל מקום ונשמרו עם ALT, שם המעלה, קישור ייחוס ושם משאב Google.

## בקרות

- סיכון: L2.
- ממשק קנוני: WordPress ו־GoNorth_Place_Data::update; ללא SQL.
- בדיקת כפילויות חיה לפי שם ו־Place ID: 0 התאמות.
- validate-place-records: שתי רשומות, 0 שגיאות, 0 אזהרות.
- PHP lint בשרת: עבר לכל הסקריפטים.
- אימות קנוני: publish, קטגוריה ואזור, GPS, Google bundle, מקור ותמונה נמצאו בשתי הרשומות.
- אימות ציבורי: שני הדפים HTTP 200; כותרת, canonical, תמונה ו־RTL נמצאו. בדף עין תאו נמצא גם סימון הבטיחות.
- wp cache flush עבר. ניקוי ידני של חלק מקובצי Autoptimize נתקל בהרשאות, אך אינו חוסם: הדפים הציבוריים החזירו תוכן חדש לאחר הניקוי הקנוני.

## Snapshot ושחזור

- snapshot: memory/data/2026-10-01-issue-100-batch-f-before.json
- rollback: להעביר את 490 ו־491 ל־draft או למחוק אותם ואת המדיה 492 ו־493, להסיר את שני קובצי התמונה מתיקיית gonorth-listing-illustrations ולבצע wp cache flush. מאחר שה־snapshot הראה שאין רשומות קודמות, אין ערך קודם לשחזור.

## ראיות

- memory/data/2026-10-01-issue-100-batch-f-candidates.json
- memory/data/2026-10-01-issue-100-batch-f-validation.json
- memory/data/2026-10-01-issue-100-batch-f-before.json
- memory/data/2026-10-01-issue-100-batch-f-pending-check.json
- memory/data/2026-10-01-issue-100-batch-f-receipt.json
- memory/data/2026-10-01-issue-100-batch-f-public-check.json

Issue #100 נשאר פתוח להמשך המקומות הבאים.
