# השלמת ביקורת Google עד 105/109 — 28.09.2026

## תוצאה

כיסוי חבילות Google המלאות עלה מ־88 ל־105 מתוך 109 דפי מקום. כל 105 החבילות כוללות Place ID, דירוג, מספר ביקורות ותאריך בדיקה. ארבעה מקומות נוספים אומתו ידנית כפעילים, אך אין עבורם ישות Google נפרדת שניתן לקבל דרך Places API (New).

## אצוות שהושלמו

- שש התאמות מדויקות של מקומות עונתיים או בעלי סתירת סטטוס.
- 11 התאמות נוספות לפי שם, מיקום, טלפון או מפעיל רשמי.
- מיקה אומתה כסגורה לצמיתות ונשארה כעמוד 200 עם הודעת מצב, מידע היסטורי וחלופות. טלפון, ניווט, מפה וסכמה פעילה הוסרו.
- ארבעת החריגים נבדקו ב־Text Search, Nearby Search, Autocomplete, שמות חלופיים, טלפונים וקואורדינטות.

## ארבעת החריגים

1. חלהוולאוו, רשומה 202: העסק מופיע כיום גם בשם ג'חנון ברמה עם אותו טלפון באודם. אין ישות Google תואמת.
2. קופיש Coffish, רשומה 228: מקור עדכני מאשר פעילות בנמל עין גב וטלפון 054-493-9011. אין ישות Google תואמת.
3. F Ranch, רשומה 357: תיירות גולן מאשרת את החווה והטלפון בנטור. אין ישות Google תואמת.
4. מורשת גוש קטיף בגולן, רשומה 409: תיירות גולן ומרכז מורשת גוש קטיף מאשרים את המרכז באבני איתן. אין ישות Google תואמת.

המועמדים הקרובים ב־Google היו יישובים, חניונים, מסעדות או עסקים אחרים ונדחו. לא הועתקו דירוגים של ישות אחרת.

## שינויים באתר ובפנקס

- הסטטוס של ארבעת המקומות עודכן בפנקס ל־operational לאחר ביקורת.
- נוספו ל־WordPress הטלפונים של קופיש ושל מורשת גוש קטיף לאחר dry-run וגיבוי.
- גיבוי שרת: /home/claude/backups/2026-09-28-place-facts-sync/place-sync-20260928-131642.json
- QA ציבורי: שני הדפים מחזירים 200 ומציגים את המספרים החדשים.
- reconciliation סופי: 109/109 רשומות, 872 שדות תואמים, 0 פערים.
- due_for_verification: 0.
- pending_changes: 0.
- closure_reviews: 0.
- audit חי: 105 חבילות Google מלאות, 0 מיושנות.

## המשך

נפתחה משימה #99 להוספה או תביעה מורשית של ארבע הישויות ב־Google Maps. השלמת 109/109 תלויה ביצירת ישויות אלה ובאימות ש־Google עשויה לדרוש מבעלי העסקים. לאחר שהן זמינות ב־Places API, יש להשלים את תהליך submit, review, dry-run, backup, apply ו־reconciliation.

GitHub:
- #70: https://github.com/rynrn/gonorth/issues/70
- #99: https://github.com/rynrn/gonorth/issues/99
- עדכון #70: https://github.com/rynrn/gonorth/issues/70#issuecomment-5870670418

## ראיות

- memory/data/2026-09-28-google-final-four-alias-search.json
- memory/data/2026-09-28-google-final-four-autocomplete.json
- memory/data/2026-09-28-google-final-four-manual-verification-submit.json
- memory/data/2026-09-28-google-final-four-wordpress-sync.json
- memory/data/2026-09-28-google-final-four-wordpress-sync-receipt.json
- memory/data/2026-09-28-google-final-four-public-qa.json
- memory/data/2026-09-28-google-coverage-105-final-reconciliation.json
- memory/data/2026-09-28-google-coverage-105-postreview-monitor.json

## המשך באותו יום — הכנת ארבע הגשות Google

רשומה 202 עודכנה לשם הנוכחי "ג'חנון ברמה — חלהוולאוו לשעבר" תוך שמירת ה-URL הישן. תוכן ושעות שלא היו עקביים בין המקורות הוסרו והוחלפו בהנחיה לוודא פעילות בטלפון. QA ציבורי עבר. הוכנו חבילות הגשה לכל ארבעת המקומות ב-memory/plans/2026-09-28-google-missing-place-submission-packets.md. כלי Chrome נכשל לפני פתיחת דפדפן עקב שגיאת sandbox של Windows; לא נשלחה הצעה ל-Google ולא נטענה בעלות על עסק.
