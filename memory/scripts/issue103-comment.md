#103 הושלם. אתר היקב מקשר ליעד Waze שלו באורטל; כרטיס היעד מפרסם 33.084579429,35.761299609. הנקודה הקודמת בדף #235 הייתה רחוקה 18.525 ק״מ. שמרתי snapshot של REST קנוני, REST ציבורי ו־HTML, תיקנתי רק latitude/longitude דרך GeoDirectory REST מאומת, והשארתי rollback מוגן.

אימות אחרי שינוי: REST קנוני וציבורי תואמים; הדף מחזיר 200; קישורי Waze ו־Google Maps, סמן OpenStreetMap ו־JSON-LD מציגים את הנקודה החדשה; `gn_region=golan` נשמר. `validate-place-records.py` עבר.

דוח: `memory/reports/2026-10-06-issue-103-tel-shifon-gps.md`. מקור רשמי: https://www.telshifonwinery.co.il/visitor-center וקישור ה-Waze ממנו, נבדקו ב־06.10.2026.
