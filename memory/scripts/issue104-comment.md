#104 הושלם. תיקון CSS מצומצם מופעל רק עד רוחב 768px ורק כאשר iframe השירות המוסתר של Google Preferred Sources נמצא בדף. לא נערך קוד Google, ה־SDK ממשיך להיטען לאחר אישור וקישור ה־CTA נשמר.

בדיקת Chrome בשישה מצבים (375/390/768 × אישור/סירוב) עברה: `scrollWidth=clientWidth` בכל מצב, גרסת CSS ציבורית 2.9.8, ללא שגיאות JavaScript. גיבוי, diff ושחזור נשמרו. דוח: `memory/reports/2026-10-06-issue-104-mobile-sdk-overflow.md`.
