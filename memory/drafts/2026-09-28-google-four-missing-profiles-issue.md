Parent: #70

## מצב נוכחי

לאחר ביקורת ידנית וסנכרון מבוקר, 105 מתוך 109 דפי מקום כוללים חבילת Google מלאה: Place ID, דירוג, מספר ביקורות ותאריך בדיקה. ארבעת הדפים הבאים אומתו כפעילים מול מקורות ציבוריים, אך Places API (New), Text Search, Nearby Search ו-Autocomplete אינם מחזירים עבורם ישות Google נפרדת:

- [ ] #202 חלהוולאוו — מופיע כיום גם בשם "ג'חנון ברמה", אודם, טלפון 058-4345278.
- [ ] #228 קופיש Coffish — נמל עין גב, טלפון 054-493-9011.
- [ ] #357 F Ranch — נטור, טלפון 058-455-9312.
- [ ] #409 מורשת גוש קטיף בגולן — אבני איתן, טלפון 04-6763535.

אין לשייך להם Place ID או דירוג של יישוב, חניון, מסעדה או עסק סמוך.

## פעולה נדרשת

1. להוסיף כל מקום חסר ל-Google Maps או ליצור/לתבוע פרופיל עסק באמצעות חשבון בעל העסק או גורם מורשה.
2. להשלים כל אימות בעלות ש-Google דורשת. אין ל-GoNorth להצהיר בעלות בשם עסק בלי הרשאה.
3. לאחר שהפרופיל מאושר וזמין ב-Places API, לאמת התאמה לפי שם, טלפון, כתובת וקואורדינטות.
4. להזין את התוצאה רק דרך place-facts/scripts/place-facts.py, לבצע review, dry-run, גיבוי ו-apply דרך הממשק הקנוני.
5. להריץ audit ו-reconciliation.

## תנאי קבלה

- [ ] לכל ארבעת הדפים יש Place ID מדויק של אותה ישות.
- [ ] לכל ארבעת הדפים יש דירוג, מספר ביקורות ותאריך בדיקה מאותו Place ID.
- [ ] wp gonorth place-data audit מציג 109/109 חבילות Google מלאות.
- [ ] reconciliation מציג 109/109 רשומות, 872 שדות תואמים ו-0 פערים.
- [ ] אין pending changes או closure reviews.
- [ ] לא נמחק או הועבר לטיוטה אף דף קיים.

## ראיות

- memory/data/2026-09-28-google-final-four-autocomplete.json
- memory/data/2026-09-28-google-final-four-alias-search.json
- memory/data/2026-09-28-google-remaining-nearby-candidates.json
- memory/data/2026-09-28-google-coverage-105-reconciliation.json
- memory/data/2026-09-28-google-coverage-105-final-monitor.json
