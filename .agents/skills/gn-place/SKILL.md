---
name: gn-place
description: יוצר או עורך ליסטינג מקום בגונורת'. כולל מחקר, שליפת דירוג ותמונה מגוגל עסקים, בניית תוכן בעברית, לינק ויז, אישור משתמש לפני פרסום. השתמש בסקיל הזה לכל הוספת מקום חדש או עדכון קיים.
license: MIT
metadata:
  author: gonorth
  domain: directory
  triggers: הוסף מקום, עדכן מקום, ליסטינג חדש, listing, gd_place, place, attraction, hotel, restaurant, tour
  role: expert
  scope: research + content + media + publishing
  output-format: approval card → wp-cli → public place URL
  version: "1.3.0"
---

# GN Place — יצירת / עריכת מקום ב-gonorth.co.il

## כלל קדימות הפרויקט

לפי AGENTS.md, רשומה חדשה מסוג gd_place נוצרת תמיד בסטטוס pending לביקורת מנהל. אין להשתמש ב-post_status=publish ביצירה, גם אם המשתמש אישר פרסום ישיר של כתבות. אין להציג URL של pending כדף ציבורי עובד. פרסום לאחר ביקורת נעשה רק בממשק הקנוני ובהרשאה המתאימה.

## סדר עבודה (חובה לפי הסדר)

```
0. בדיקת כפילות  →  1. מחקר  →  2. Google Places API  →  3. בניית תוכן  →  4. אישור משתמש  →  5. פרסום  →  6. עדכון סטטוס וקישור  →  7. החזרת לינק לדף המקום
```

---

## שלב 0 — בדיקת כפילות (לפני כל הוספה)

לפני שמתחילים, בדוק שהמקום לא קיים כבר באתר.

### בדיקה ב-WP-CLI

```bash
# חיפוש לפי שם (מחזיר ID + כותרת אם קיים):
ssh gonorth "wp post list --post_type=gd_place --post_status=any \
  --fields=ID,post_title,post_status \
  --search='PLACE_NAME' \
  --path=/var/www/gonorth --allow-root"
```

### בדיקה בקובץ places-to-add.md

קרא את `/Users/navisror/dev/gonorth/places-to-add.md` וחפש אם המקום מופיע כבר ברשימה.

### תוצאות אפשריות

| תוצאה | פעולה |
|-------|--------|
| לא קיים — בשום מקום | המשך לשלב 1 |
| קיים באתר (סטטוס `publish`) | **עצור.** הודע למשתמש שהמקום כבר פורסם + ציין את ה-ID. אם המקום מופיע ב-`places-to-add.md` בלי קישור, שלוף את ה-URL ועדכן את עמודת הקישור |
| קיים בקובץ עם סטטוס "הוסף" | **עצור.** הודע שהמקום כבר הוסף לאתר |
| קיים בקובץ עם סטטוס "ממתין" | הודע שהמקום ברשימת ההמתנה — שאל אם להמשיך |

---

## שלב 1 — מחקר המקום

אסוף את כל הנתונים הבאים לפני שתיצור תוכן. **אל תמציא שום פרט.**

| שדה | מקור מומלץ |
|-----|-----------|
| שם רשמי בעברית | אתר רשמי / גוגל |
| כתובת מלאה | גוגל מאפס |
| עיר / ישוב | גוגל מאפס |
| קואורדינטות GPS (lat, lng) | tourgolan.org.il / Google Maps URL |
| טלפון | אתר רשמי / דפי זהב |
| אתר אינטרנט | חיפוש |
| שעות פתיחה | אתר רשמי / גוגל |
| קטגוריה | ראה טבלה למטה |

---

## שלב 2 — Google Places API

**API Key:** `${GOOGLE_PLACES_API_KEY}`
**חשוב:** בדוק תחילה היכן המפתח המאושר זמין ומה מגבלותיו; אין להניח שהשרת או הדפדפן מורשים. אל תדפיס את ערכו ואל תנסה לעקוף הגבלות.

### 2א — שליפת place_id + דירוג

```bash
ssh gonorth "curl -s 'https://maps.googleapis.com/maps/api/place/findplacefromtext/json?\
input=PLACE_NAME_URL_ENCODED\
&inputtype=textquery\
&fields=place_id,name,rating,user_ratings_total\
&key=${GOOGLE_PLACES_API_KEY}'"
```

תוצאה לדוגמה:
```json
{
  "candidates": [{
    "place_id": "ChIJ...",
    "name": "יקב הר אודם",
    "rating": 4.6,
    "user_ratings_total": 481
  }]
}
```

שמור: `PLACE_ID`, `rating`, `user_ratings_total`

### מדיניות תמונת מקום — חובה

כאשר קיים פרופיל Google Places מזוהה, תמונה אמיתית מאותו פרופיל היא ברירת המחדל לתמונת הנושא.

1. שלוף לפחות 3 תמונות מועמדות, כאשר קיימות.
2. בדוק אותן חזותית לפני בחירה. אין לבחור אוטומטית את התמונה הראשונה.
3. העדף תמונה שממחישה את המקום עצמו: חזית, חלל, אזור ישיבה או חוויה מרכזית. העדף תמונה שהועלתה מחשבון העסק.
4. ודא שה־Place ID ושם העסק תואמים לליסטינג. אין להשתמש בתמונה מתוצאת חיפוש דומה.
5. תעד את ייחוס היוצר וקישור תמונת המקור לפי מדיניות Places העדכנית. קרדיט או העלאה של העסק ל־Google אינם רישיון להעתקת הקובץ לשרת.
6. כתוב ALT עובדתי המתאר את מה שנראה בתמונה, בלי מילות מפתח מיותרות.
7. אסור ליצור או להשתמש באיור AI עבור מקום אמיתי, אלא אם המשתמש ביקש זאת במפורש לאחר שנאמר לו שאין תמונה אמיתית מתאימה.
8. אם אין תמונת Google מתאימה, השתמש בתמונה מהאתר הרשמי רק לאחר תיעוד המקור וזכות השימוש. אם גם זו אינה זמינה, עצור את פרסום הליסטינג ודווח שחסרה תמונה מאומתת.

### 2ב — שליפת תמונות וייחוס מ־Places API (New)

שלוף את שדה photos דרך Place Details API. כל פריט כולל name, מידות ו־authorAttributions.

המפתח חייב להיקרא מהאפשרות המאובטחת בשרת. אין להדפיס אותו ואין לשמור אותו בסקריפט או בדוח.

### 2ג — בדיקת זכות שימוש ואופן התצוגה

תמונת Google Places היא מועמדת לבדיקה, בהתאם להעדפת בעל האתר לתצלום אמיתי של המקום. אין להוריד, לשמור ב־uploads, להעלות ל־WordPress או לרשתות צילום שהתקבל רק מ־Places API. לפי תנאי Google Maps Platform ומדיניות Places, אחסון/אירוח מחדש מוגבלים ויש דרישות לייחוס היוצר וקישור ישיר לתמונת המקור. בדוק את התנאים המעודכנים לפני כל יישום: https://cloud.google.com/maps-platform/terms וכן https://developers.google.com/maps/documentation/places/web-service/policies .

להצגה יש שתי דרכים: תצוגת API שמתעדכנת דרך השירות וממלאת את דרישות הייחוס וקישור המקור, רק אם קיים רכיב מאומת לכך; או קובץ מקורי שהצלם/המפעיל סיפק בנפרד, עם הרשאה מפורשת לשימוש באתר, לחיתוך ולהפצה הנדרשת. תמונה באתר הרשמי או בחשבון העסק ב־Google אינה מעניקה כשלעצמה הרשאה להעתיק אותה. שמור מקור, בעל זכויות, היקף הרשאה, תאריך ואופן ייחוס במרשם המדיה. אם אף דרך אינה מוכחת, רשום unknown והשאר את המקום ממתין בלי תמונה חדשה.

---

## שלב 3 — בניית תוכן בעברית

### קטגוריות — טבלת עזר מלאה

> **חובה:** כל מקום חייב לקבל לפחות קטגוריה אחת ואזור לפני פרסום.
> **מרובה קטגוריות:** מקום יכול לשייך ל-2 קטגוריות ויותר — למשל חווה שיש בה גם מסעדה, או יקב שמשמש גם כאטרקציה. זהה זאת בשלב המחקר והוסף את כל הקטגוריות הרלוונטיות.

#### קטגוריות ראשיות

| עברית | Slug | term_id | מתי להשתמש |
|-------|------|---------|------------|
| אטרקציות | `atraktziot-main` | 17 | פארקים, מוזיאונים, חוות, גני חיות, אתרי בידור |
| טבע ומסלולים | `teva` | 35 | שמורות טבע, נחלים, הרים, מסלולי הליכה |
| לינה | `accommodation` | 18 | מלונות, צימרים, גלמפינג, קמפינג, אכסניות |
| אוכל ושתייה | `restaurants` | 19 | (קטגוריה ראשית — עדיף תת-קטגוריה) |
| סיורים ופעילויות | `tours` | 20 | טיולים מודרכים, ספורט אתגרי, סדנאות |

#### סוגי אוכל ושתייה (כולם parent=0 במצב האתר הנוכחי)

| עברית | Slug | term_id | מתי להשתמש |
|-------|------|---------|------------|
| מסעדות | `restaurants-sub` | 33 | מסעדות מכל הסוגים |
| יקבים | `yakavim` | 36 | יקבי יין, מסלולי יין |
| בתי קפה | `batei-kafe` | 37 | בתי קפה, קפה ועוגה |
| עגלות קפה | `agalot-kafe` | 38 | עגלות קפה, דוכני קפה ניידים |

#### אזורים — gn_region (חובה לכל מקום)

| עברית | Slug | term_id | כולל |
|-------|------|---------|------|
| גליל | `galil` | 28 | גליל עליון, גליל תחתון ומערבי, אצבע הגליל ועמק החולה |
| גולן | `golan` | 29 | רמת הגולן, הרמון |
| כרמל ועמק יזרעאל | `carmel` | 30 | הכרמל, עמק יזרעאל, חוף הכרמל |

גבולות האזורים וחריגי עמק המעיינות מתועדים ב־docs/region-and-category-taxonomy.md. אל תשייך מקום לאזור לא נכון רק כדי להשלים שדה; מקום חדש מחוץ לכיסוי הקיים מחייב החלטת אזור לפני פרסום.

### תבנית תוכן — Approval Card

בנה כרטיס אישור בפורמט הבא לפני כל פרסום:

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📍 [שם המקום]
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

קטגוריה:   [קטגוריה ראשית] + [קטגוריה נוספת אם רלוונטי]
אזור:       [גליל / גולן / כרמל ועמק יזרעאל]
עיר:        [עיר]
כתובת:      [כתובת]
GPS:        [lat], [lng]
טלפון:      [טלפון]
אתר:        [url]
שעות:       [שעות פתיחה]

⭐ דירוג גוגל: [rating] ([total] ביקורות)

🗺️ ניווט ברכב: [יעד מאומת ב־Waze / אין יעד רכב מאומת — להסתיר קישורי ניווט]

📸 תמונה: [מקור, בעל זכויות, הרשאה, ייחוס ואופן הצגה מאומתים / unknown]

─────────────────────────────
תיאור קצר (excerpt):
[עד 160 תווים, עברית, SEO]

תיאור מלא:
[פסקה 1 — מה המקום ומה מיוחד בו]
[פסקה 2 — מה חווים שם]
[פסקה 3 — טיפים מעשיים]
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
האם לאשר ולפרסם? (כן / שינויים)
```

---

## שלב 4 — אישור משתמש

**עצור כאן.** הצג את ה-Approval Card למשתמש וחכה לתשובה.
- `כן` / `אשר` / `looks good` → המשך לשלב 5
- כל תגובה אחרת → עדכן לפי הפידבק וחזור לשלב 4

---

## שלב 5 — יצירת רשומה ממתינה

### יצירת listing חדש

```bash
# 1. יצירת הפוסט
ID=$(ssh gonorth "wp post create \
  --post_type=gd_place \
  --post_title='PLACE_NAME' \
  --post_excerpt='SHORT_DESC' \
  --post_content='FULL_CONTENT' \
  --post_status=pending \
  --path=/var/www/gonorth --allow-root --porcelain 2>/dev/null")

# 2. קטגוריה ראשית (חובה — ראה טבלת קטגוריות בשלב 3)
# השתמש ב-set לקטגוריה הראשית (מוחק קטגוריות קודמות ומגדיר חדשה):
ssh gonorth "wp post term set $ID gd_placecategory CATEGORY_SLUG \
  --by=slug --path=/var/www/gonorth --allow-root 2>/dev/null"

# קטגוריות נוספות (אם רלוונטי — למשל חווה שהיא גם מסעדה):
# השתמש ב-add (לא set) כדי להוסיף קטגוריה נוספת בלי למחוק את הקודמת:
ssh gonorth "wp post term add $ID gd_placecategory SECOND_CATEGORY_SLUG \
  --by=slug --path=/var/www/gonorth --allow-root 2>/dev/null"
# חזור על שורה זו לכל קטגוריה נוספת.

# 3. אזור (חובה — ראה טבלת אזורים בשלב 3)
ssh gonorth "wp post term add $ID gn_region REGION_SLUG \
  --by=slug --path=/var/www/gonorth --allow-root 2>/dev/null"

# 4. מטא-דאטה
ssh gonorth "wp post meta add $ID geodir_post_city 'CITY' --path=/var/www/gonorth --allow-root 2>/dev/null"

ssh gonorth "wp post meta add $ID geodir_post_address 'ADDRESS' --path=/var/www/gonorth --allow-root 2>/dev/null"
ssh gonorth "wp post meta add $ID geodir_post_latitude 'LAT' --path=/var/www/gonorth --allow-root 2>/dev/null"
ssh gonorth "wp post meta add $ID geodir_post_longitude 'LNG' --path=/var/www/gonorth --allow-root 2>/dev/null"
ssh gonorth "wp post meta add $ID geodir_post_phone 'PHONE' --path=/var/www/gonorth --allow-root 2>/dev/null"
ssh gonorth "wp post meta add $ID geodir_post_website 'WEBSITE' --path=/var/www/gonorth --allow-root 2>/dev/null"
ssh gonorth "wp post meta add $ID geodir_post_rating 'RATING' --path=/var/www/gonorth --allow-root 2>/dev/null"
ssh gonorth "wp post meta add $ID geodir_featured '1' --path=/var/www/gonorth --allow-root 2>/dev/null"

# 4. תמונה: ייבוא מותר רק מקובץ מקורי שהתקבל בנפרד עם הרשאת שימוש מתועדת.
# אין לייבא קובץ שהורד מ־Google Places API. אם אין קובץ מורשה, השאר pending.
```

### עריכת listing קיים

```bash
# עדכון שדה טקסט
ssh gonorth "wp post update POST_ID --post_title='NEW_TITLE' --path=/var/www/gonorth --allow-root"

# עדכון מטא
ssh gonorth "wp post meta update POST_ID FIELD_NAME 'NEW_VALUE' --path=/var/www/gonorth --allow-root"

# החלפת תמונה ראשית
ssh gonorth "wp post meta update POST_ID _thumbnail_id ATTACHMENT_ID --path=/var/www/gonorth --allow-root"
```

### חובה לאחר העלאה / פרסום

לאחר כל יצירה או פרסום של `gd_place`, שלוף את הקישור הציבורי לדף המקום והחזר אותו למשתמש בתשובה הסופית. זה נדרש לבדיקות ידניות.

```bash
# שליפת URL ציבורי אחרי יצירה / פרסום:
URL=$(ssh gonorth "wp post get $ID --field=url --path=/var/www/gonorth --allow-root")
echo "$URL"

# אימות שהקישור הציבורי עובד:
ssh gonorth "curl -s -o /dev/null -w '%{http_code}' '$URL'"
```

כללי אימות:
- אם הפוסט פורסם (`publish`), הקישור חייב להחזיר `200`, `301` או `302`.
- אם מתקבל `404`, בדוק שהפוסט לא נשאר `pending` ושקישורי המבנה (`permalinks`) תקינים.
- אל תדווח שההעלאה הושלמה בלי להחזיר לפחות קישור ציבורי אחד לדף המקום שנוצר.
- בהעלאה מרובה, החזר רשימת קישורים או לפחות דוגמאות מייצגות + נתיב לקובץ שבו נשמרו כל הקישורים.

### עדכון מיקום במפה עבור listing קיים

כאשר מוסיפים או מתקנים קואורדינטות GPS של מקום קיים — יש לעדכן **שלושה שדות**:

```bash
# עדכון קואורדינטות GPS (המפה מסתמכת על אלה):
ssh gonorth "wp post meta update POST_ID geodir_post_latitude 'LAT' --path=/var/www/gonorth --allow-root"
ssh gonorth "wp post meta update POST_ID geodir_post_longitude 'LNG' --path=/var/www/gonorth --allow-root"

# עדכון כתובת (טקסטואלי — מופיע בכרטיס):
ssh gonorth "wp post meta update POST_ID geodir_post_address 'ADDRESS' --path=/var/www/gonorth --allow-root"

# עדכון עיר:
ssh gonorth "wp post meta update POST_ID geodir_post_city 'CITY' --path=/var/www/gonorth --allow-root"

# לאחר עדכון — חובה לנקות cache כדי שהמפה תעדכן:
ssh gonorth "wp cache flush --path=/var/www/gonorth --allow-root"
```

**אימות:** בדוק שהמיקום הוגדר נכון:

```bash
ssh gonorth "wp post meta get POST_ID geodir_post_latitude --path=/var/www/gonorth --allow-root"
ssh gonorth "wp post meta get POST_ID geodir_post_longitude --path=/var/www/gonorth --allow-root"
```

**כלי עזר למציאת קואורדינטות מדויקות:**
- Google Maps URL: `https://www.google.com/maps/place/PLACE_NAME` — לחץ על נקודה → הקואורדינטות מופיעות בכתובת
- GPS reference points בתחתית הסקיל

---

## שלב 6 — עדכון סטטוס וקישור בקובץ places-to-add.md

לאחר פרסום מוצלח, עדכן את הסטטוס בקובץ מ-"ממתין" ל-"הוסף" והוסף קישור ישיר לליסטינג.

```bash
# קרא את הקובץ כדי למצוא את השורה הנכונה:
# places-to-add.md בשורש הריפו

# שלוף את URL הפוסט לאחר יצירתו:
URL=$(ssh gonorth "wp post get $ID --field=url --path=/var/www/gonorth --allow-root")

# עדכן את הסטטוס והקישור בשורה של המקום שנוסף:
# לדוגמה: | 9 | פארק המעיינות | אטרקציות | ממתין |  |
# →        | 9 | פארק המעיינות | אטרקציות | הוסף | https://gonorth.co.il/places/.../ |
```

אם אין בקובץ עמודת `קישור`, הוסף אותה לכותרת הטבלה ולכל השורות לפני עדכון המקום. השתמש בכלי Edit (לא sed) כדי לעדכן את הטבלה — חפש את השורה המדויקת של המקום, שנה "ממתין" ל-"הוסף", והכנס את URL הליסטינג בעמודת `קישור`.

**חשוב:** עשה זאת רק לאחר שהפרסום אושר ו-`wp post create` הצליח.

---

## שלב 7 — החזרת לינק לדף המקום

בסיום כל הרצת `gn-place`, חובה להחזיר למשתמש לינק ציבורי תקין לדף המקום שנוצר או עודכן.

פורמט תשובה מינימלי:

```
פורסם בהצלחה:
- [שם המקום](https://gonorth.co.il/places/...)

אימות: הקישור מחזיר HTTP 200.
```

בהרצה מרובה:

```
פורסמו X מקומות.
דוגמאות לבדיקה:
- [שם 1](https://gonorth.co.il/places/...)
- [שם 2](https://gonorth.co.il/places/...)

כל הקישורים עודכנו בקובץ places-to-add.md.
אימות: נבדקו קישורים מדגמיים והחזירו HTTP 200.
```

אם המקום נשאר `pending`, אל תיתן קישור ציבורי כאילו הוא עובד. החזר קישור עריכה לאדמין וציין במפורש שהצד הציבורי יחזיר 404 עד פרסום.

---

## ניווט ומיקום מקום

GPS של המקום אינו בהכרח יעד להגעה ברכב. לפני הצגת Waze יש לאמת יעד הגעה מתאים ממקור מפעיל או ממקור ניווט מזוהה, ולכתוב אם זה יעד מתחם, חניה או כניסה פיזית. אל תציג נקודת מקום או שביל הולכי רגל ככניסה לרכב.

כשאין יעד נהיגה מאומת, השאר את נקודת המקום המדויקת במפה רק אם היא אכן מתארת את המקום, והגדר לרשומה דרך WordPress את מטא `_gonorth_hide_driving_navigation=1`. הדגל מסתיר את קישורי Waze ו־Google Maps שבתבנית ומציג הודעת הגעה. לפני הגדרת הדגל סרוק גם את תוכן הרשומה לקישורי Waze/Maps ידניים: הדגל אינו מסיר קישורים שנכתבו בתוך התוכן. כתוב בתוכן את הוראות ההגעה המאומתות, כולל ההבדל בין חניה לכניסת המבקרים. אמת בדף הציבורי שאין בו קישורי ניווט מטעים; אם הרשומה pending, בדוק בתצוגה מאומתת לפני פרסום. אין להפעיל דגל רק כדי לעקוף בדיקת מיקום.

כאשר יעד נהיגה אומת, אפשר ליצור קישור בפורמט:

```
https://waze.com/ul?ll={lat},{lng}&navigate=yes
```

הדגל הוא אפשרות תצוגה בלבד ואינו מחליף את ממשק GeoDirectory REST לעדכון קואורדינטות. כל שינוי ברשומה מחייב בדיקת כפילות, מקור, validator, שחזור ואימות לפי `docs/agent-change-contract.md`.

---
## GPS — נקודות ייחוס בצפון

| מקום | lat | lng |
|------|-----|-----|
| טבריה | 32.7940 | 35.5300 |
| צפת | 32.9646 | 35.4961 |
| נהריה | 33.0076 | 35.0967 |
| עכו | 32.9261 | 35.0756 |
| כרמיאל | 32.9094 | 35.2974 |
| ראש פינה | 32.9659 | 35.5400 |
| בניאס | 33.2481 | 35.6943 |
| מג'דל שמס | 33.2667 | 35.7667 |
| אודם | 33.1920 | 35.7518 |

---

## כללים קשיחים

| חובה | אסור |
|------|------|
| לחכות לאישור משתמש לפני פרסום | להמציא כתובת, טלפון, שעות |
| **לשייך קטגוריה (gd_placecategory) — חובה** | לפרסם בלי קטגוריה |
| **לשייך אזור (gn_region) — חובה** | לפרסם בלי אזור |
| לבדוק תמונה אמיתית ב-Google Places ולאמת דרך תצוגה או הרשאה מותרת | להוריד ולהעלות מחדש צילום API בלי זכות שימוש |
| לאמת כתובת ניווט וכניסה ממקור מתאים | להניח שהמפתח זמין מהשרת בלי בדיקה |
| לכתוב תוכן בעברית RTL בלבד | לפרסם בלי GPS |
| לסמן `geodir_featured=1` | ליצור כפילות לאותו מקום |
| **אסור להשתמש באימוג'י** — לא בתוכן, לא בתיאורים, לא בכרטיס האישור | שימוש באימוג'י בכל שדה |
| להעדיף תמונה שהועלתה מחשבון העסק | ליצור איור AI למקום אמיתי ללא בקשה מפורשת |

### הערה על מפתח ועל תמונות

הגבלת המפתח עשויה להיות IP, HTTP Referer או תצורה אחרת; אימת אותה בחנות האישורים המאושרת לפני קריאה. מפתח שאינו זמין אינו סיבה לנחש או להדפיס אישור. Google Places הוא מקור לזיהוי מועמד לתצלום אמיתי, אך אין להוריד ולהעלות צילום API ל־WordPress ללא בסיס שימוש נפרד ותואם לתנאים. חלופה להעלאה היא קובץ שנמסר ישירות מבעל הזכויות עם הרשאה מתועדת. אין להכניס שם משתמש, סיסמה, Application Password או כותרת Authorization לקובץ סקיל, לפקודת shell או לדוח. אם אין דרך תצוגה תואמת או קובץ מורשה, תעד את החסם והשאר את הליסטינג ממתין.

## מקום ללא Google Places

אם לא נמצא פרופיל Google Places מזוהה, אין לעצור את עדכון המקום ואין להמציא נתוני Google. אפשר להשתמש באתר הרשמי או במדריך מקצועי אמין כמקור חלופי. שמור את המקור ותאריך הבדיקה ב־place-facts, הוסף בדף גילוי מקור קצר, וציין שאין דירוג או מספר ביקורות של Google. בדוחות הצג בנפרד כיסוי מקורות כולל וכיסוי Google.

## Shared change contract

Follow docs/agent-change-contract.md for every change. Classify risk and canonical writer first; record sources and dates; create a snapshot and rollback for live changes; run tools/validate-place-records.py for place data; verify the canonical record and public output; then write the dated report and issue evidence. Official non-Google sources are valid when documented. Existing dynamic business facts must use the place-facts review and sync workflow.

