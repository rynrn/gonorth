# #91 — תיקון הוראות שימוש בתמונות Google Places

תאריך: 06.10.2026. דוח קודם: 2026-10-06-issue-91-media-rights-audit-2300.md.

בעקבות בדיקת מדיניות Google Maps Platform ו־Places, סקיל gn-place עודכן מגרסה 1.2.0 ל־1.3.0. הוסרו הוראות הורדה של צילום API ל־tmp וייבואו אוטומטית ל־WordPress כ־featured image. ההעדפה של בעל האתר לצילום אמיתי מתוך Google Places נשמרה לצורך זיהוי ובחירה; שימוש באתר מחייב תצוגת API תואמת לתנאים, כולל ייחוס וקישור מקור, או קובץ שהתקבל ישירות מבעל הזכויות עם הרשאה מתועדת. אין להסיק הרשאה מכך שהעסק העלה תמונה לפרופיל Google. כאשר אין דרך מוכחת, המקום נשאר pending. הוסרה גם סתירה בין הוראות מפתח מוגבל ל־IP שרת לבין הערה שמפתח מוגבל ל־HTTP Referer; מעתה בודקים את המגבלות בפועל בלי להדפיס מפתח.

מקורות שנבדקו 06.10.2026: https://cloud.google.com/maps-platform/terms ; https://developers.google.com/maps/documentation/places/web-service/policies ; https://developers.google.com/maps/documentation/places/web-service/place-photos .

אימות: סעיף הורדת תמונה וסעיף wp media import של צילום Google אינם קיימים עוד בסקיל; בדיקת סודות tracked עברה; אין שינוי באתר או בקובצי תמונה קיימים.

status: partial; scope: סקיל gn-place מקומי; risk_level: L1; canonical_writer: .agents/skills/gn-place/SKILL.md; changes_made: הנחיית תמונות ומפתח; unchanged: WordPress, מדיה ותוכן; validation: חיפוש הוראות ישנות וסורק סודות; snapshot: גרסת הקובץ הקודמת ב־Git ובדוח הביקורת; rollback: לשחזר את הסעיפים הקודמים מתוך היסטוריית עבודה רק לאחר בדיקת התאמה לתנאים, ללא שחזור אישור מוטמע ישן; remaining_risks: תמונות Google שכבר מאוחסנות באתר דורשות בדיקת מקור/הרשאה ותוכנית תיקון מדורגת; issue_recommendation: keep-open.