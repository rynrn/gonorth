# GoNorth — מאגר אימות מידע משתנה

תיקייה זו מנהלת ב־SQLite מידע שעשוי להשתנות בלי לשנות את התוכן העריכתי של עמודי GoNorth: סטטוס פעילות, טלפון, כתובת, אתר ושעות. מסד הנתונים הוא יומן האימות ומקור האמת לשינויים שנבדקו; GeoDirectory נשאר ההיטל שמשרת את האתר החי, ורק שינוי שעבר ביקורת יכול להסתנכרן אליו.

המימוש משתמש ב־Python וב־SQLite מהספרייה התקנית בלבד. אין תלות בחבילות חיצוניות.

## עקרונות

- תוכן עריכתי אינו נכנס למסד.
- כל בדיקה שומרת מקור, תאריך, ביטחון ותוצאה גולמית.
- ערך חדש אינו דורס את הערך הנוכחי. הוא נרשם ב־place_changes כ־pending.
- שינוי ל־temporarily_closed או permanently_closed מקבל פעולת SEO מפורשת: להשאיר את כתובת העמוד חיה, להציג הודעת מצב ולהסיר פעולות ישנות.
- המערכת אינה מוחקת, מעבירה לטיוטה או מחזירה 404.
- שעות וסטטוס שבת יכולים להיגזר מנתוני השעות; אין צורך לשמור אמת כפולה.
- מפתח API נקרא רק ממשתנה סביבה ואינו נשמר במסד, בקוד או בשורת הפקודה.

## מבנה הנתונים

- places — זהות יציבה מול WordPress, slug, URL, קטגוריה ו־Google Place ID.
- place_facts — הערכים הנוכחיים שאושרו ותאריך הבדיקה האחרון.
- place_verifications — כל ניסיון בדיקה, כולל כשל.
- place_changes — old/new, מקור, זמן, ביטחון וסטטוס ביקורת.

הסכמה נמצאת ב־schema.sql. מסד העבודה המקומי נוצר ב־data/place-facts.sqlite3.

## התחלה

מ־PowerShell בשורש הפרויקט:

    py -3.14 place-facts/scripts/place-facts.py init
    py -3.14 place-facts/scripts/place-facts.py import-snapshot memory/data/2026-09-23-place-facts-baseline-page-audit.json

הייבוא ממפה את מזהה WordPress למזהה הפנימי. אם Google Place ID קיים ב־snapshot הוא נשמר; אם הוא חסר, הוא נשאר חסר עד לתוצאת אימות מאושרת.

## מקומות שמועד הבדיקה שלהם הגיע

ייצוא JSON לסוכן או לסקריפט:

    py -3.14 place-facts/scripts/place-facts.py due --days 90 --output memory/data/places-due.json

ייצוא טבלת Markdown:

    py -3.14 place-facts/scripts/place-facts.py due --days 90 --format markdown --output memory/reports/places-due.md

רק מקומות עם Google Place ID:

    py -3.14 place-facts/scripts/place-facts.py due --days 90 --google-only

## בדיקה מול Google Places

הכלי מבקש רק businessStatus, כתובת, טלפון, אתר, קישור מפות ושעות רגילות/נוכחיות.

    $env:GOOGLE_PLACES_API_KEY = '...'
    py -3.14 place-facts/scripts/place-facts.py fetch-google --days 90 --limit 20 --output memory/data/google-results.json

הפקודה מפיקה JSON בלבד. היא אינה משנה את מקור האמת. את התוצאות מגישים בנפרד:

    py -3.14 place-facts/scripts/place-facts.py submit memory/data/google-results.json

## ממשק תוצאות מובנה

החוזה הפורמלי נמצא ב־erification-result.schema.json. ה־CLI מבצע גם אימות סמנטי של סטטוסים, כתובות URL ותאריכים עם אזור זמן.

כל תוצאה חייבת לכלול מזהה, מקור ותאריך עם אזור זמן. שדות שלא נבדקו מושמטים. null הוא בקשה מפורשת להסיר ערך.

    [
      {
        "id": 219,
        "source": "manual_review",
        "checked_at": "2026-09-23T12:00:00Z",
        "confidence": 0.95,
        "facts": {
          "business_status": "permanently_closed",
          "phone": null
        }
      }
    ]

סטטוסים מותרים:

- operational
- temporarily_closed
- permanently_closed
- unknown

תוצאות זהות מעדכנות last_checked_at. הבדלים יוצרים שינויים ממתינים ואינם נכתבים אוטומטית לערכים המאושרים.

## ביקורת שינויים

    py -3.14 place-facts/scripts/place-facts.py pending --output memory/data/pending-place-changes.json
    py -3.14 place-facts/scripts/place-facts.py review approve 12 13 --note "אומת מול מקור רשמי"
    py -3.14 place-facts/scripts/place-facts.py review reject 14 --note "מקורות סותרים"

אישור משנה את SQLite בלבד. סנכרון עתידי ל־WordPress חייב להשתמש בממשק הנתונים הקנוני של gonorth-place-data, עם גיבוי ובדיקת HTTP לאחר הכתיבה.

## יצוא לסוכנים ולסקילים

    py -3.14 place-facts/scripts/place-facts.py export --format json --output memory/data/place-facts-export.json
    py -3.14 place-facts/scripts/place-facts.py export --format markdown --output memory/reports/place-facts-review.md

JSON ו־Markdown הם ממשקים ותצוגות בלבד. SQLite נשאר מקור האמת.

## התאמה וסנכרון ל־WordPress

    wp gonorth place-data export --path=/var/www/gonorth --allow-root > wordpress-export.json
    py -3.14 place-facts/scripts/place-facts.py reconcile wordpress-export.json --output reconciliation.json
    py -3.14 place-facts/scripts/place-facts.py export-wordpress-sync --output sync-package.json
    wp gonorth place-data sync sync-package.json --path=/var/www/gonorth --allow-root
    wp gonorth place-data sync sync-package.json --apply --backup-dir=/private/backups --path=/var/www/gonorth --allow-root
    py -3.14 place-facts/scripts/place-facts.py record-wordpress-sync apply-receipt.json

הפקודה הראשונה ב־WordPress וה־reconcile הן לקריאה בלבד. `sync` ללא `--apply`
הוא dry-run. כתיבה מותרת רק לאחר אישור השינויים ובדיקת החבילה; לפני הכתיבה
נוצר גיבוי. עסק סגור נשאר מחוץ לסנכרון האוטומטי ומטופל ידנית עם URL חי.

שחזור:

    wp gonorth place-data rollback /private/backups/place-sync-YYYYMMDD-HHMMSS.json --path=/var/www/gonorth --allow-root

## ניטור

    py -3.14 place-facts/scripts/place-facts.py monitor --output memory/data/place-facts-monitoring.json

הדוח מציג תור בדיקה, שינויים ממתינים, ביקורות סגירה, כיסוי Place ID וכיסוי
דירוג מלא. `--fail-on-attention` מתאים להרצה מתוזמנת שמתריעה רק כשנדרש טיפול.

## בדיקות

    py -3.14 -m unittest discover -s place-facts/tests -v

הבדיקות מכסות ייבוא, בחירת מקומות לבדיקה, נרמול Google, איתור שינויים, מניעת דריסה, אישור/דחייה ומדיניות SEO לעסק סגור.

## מקורות אימות שאינם Google

הניטור מדווח בנפרד על כיסוי מקורות מאומתים ועל כיסוי Google. מקום יכול להיחשב מאומת גם כאשר אין לו פרופיל Google מזוהה, בתנאי שנבדק מול אתר רשמי או מדריך מקצועי אמין, נשמר שם המקור ותאריך הבדיקה, ובדף הציבורי מצוין המקור. אין ליצור Place ID, דירוג או מספר ביקורות מלאכותיים.
