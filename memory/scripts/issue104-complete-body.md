ממצא משלים ל־#83 בבדיקת מובייל ב־04.10.2026. ברוחב viewport של 390px, לאחר הסכמה למדידה נטען Google Preferred Sources SDK ומוסיף iframe מוסתר של `news.google.com/swg/ui/v1/serviceiframe` במיקום inline `left:-10px !important`. רוחב המסמך עולה ל־400px. כשחוסמים את ה־SDK בניסוי, רוחב המסמך נשאר 390px. ללא הסכמה אין גלישה זו.

- [x] לבחור תיקון צר שלא יפגע ב־SDK, בנגישות או ב־RTL; לא לערוך קוד של Google.
- [x] לבדוק לפני ואחרי ברוחבי 375/390/768 ובמצבי אישור/סירוב.
- [x] לשמור rollback ולתעד את התוצאה ב־memory/reports/.

מקור וראיות: `memory/reports/2026-10-04-issue-83-consent-and-analytics.md`, `memory/scripts/issue83-sdk-overflow-qa.js`. ניסיון CSS רגיל לא גבר על סגנון inline של ה־SDK ולכן הוסר. אין צורך בשינוי התנהגות הסכמה כדי לטפל בזה.
