---
name: gn-place
description: יוצר או עורך ליסטינג מקום בגונורת'. כולל מחקר, שליפת דירוג ותמונה מגוגל עסקים, בניית תוכן בעברית, לינק ויז, אישור משתמש לפני פרסום. השתמש בסקיל הזה לכל הוספת מקום חדש או עדכון קיים.
license: MIT
metadata:
  author: gonorth
  version: "1.0.0"
  domain: directory
  triggers: הוסף מקום, עדכן מקום, ליסטינג חדש, listing, gd_place, place, attraction, hotel, restaurant, tour
  role: expert
  scope: research + content + media + publishing
  output-format: approval card → wp-cli
---

# GN Place — יצירת / עריכת מקום ב-gonorth.co.il

## סדר עבודה (חובה לפי הסדר)

```
0. בדיקת כפילות  →  1. מחקר  →  2. Google Places API  →  3. בניית תוכן  →  4. אישור משתמש  →  5. פרסום  →  6. עדכון סטטוס
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
| קיים באתר (סטטוס `publish`) | **עצור.** הודע למשתמש שהמקום כבר פורסם + ציין את ה-ID |
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

**API Key:** `AIzaSyArHd6QyrA753zJcbNjQbKWGPPHUeFdhSM`
**חשוב:** הפעל את ה-curl **מהשרת** בלבד (המפתח מוגבל ל-IP של השרת).

### 2א — שליפת place_id + דירוג

```bash
ssh gonorth "curl -s 'https://maps.googleapis.com/maps/api/place/findplacefromtext/json?\
input=PLACE_NAME_URL_ENCODED\
&inputtype=textquery\
&fields=place_id,name,rating,user_ratings_total\
&key=AIzaSyArHd6QyrA753zJcbNjQbKWGPPHUeFdhSM'"
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

### 2ב — שליפת photo_reference

```bash
ssh gonorth "curl -s 'https://maps.googleapis.com/maps/api/place/details/json?\
place_id=PLACE_ID\
&fields=photos\
&key=AIzaSyArHd6QyrA753zJcbNjQbKWGPPHUeFdhSM' \
| python3 -c \"import sys,json; photos=json.load(sys.stdin)['result']['photos']; print(photos[0]['photo_reference'])\""
```

### 2ג — הורדת התמונה לשרת

```bash
ssh gonorth "curl -s -L 'https://maps.googleapis.com/maps/api/place/photo?\
maxwidth=1200\
&photo_reference=PHOTO_REFERENCE\
&key=AIzaSyArHd6QyrA753zJcbNjQbKWGPPHUeFdhSM' \
-o /tmp/place_photo.jpg"
```

### 2ד — העלאת התמונה כ-featured image

```bash
ssh gonorth "wp media import /tmp/place_photo.jpg \
  --post_id=POST_ID \
  --featured_image \
  --title='PLACE_NAME' \
  --path=/var/www/gonorth --allow-root"
```

---

## שלב 3 — בניית תוכן בעברית

### קטגוריות

| עברית | Slug | מתי |
|-------|------|-----|
| אטרקציות וטבע | `attractions` | גנים, שמורות, יקבים, אתרי טבע, מוזיאונים |
| לינה | `accommodation` | מלונות, צימרים, גלמפינג, קמפינג |
| אוכל ושתייה | `restaurants` | מסעדות, בתי קפה, שווקים |
| סיורים ופעילויות | `tours` | טיולים מודרכים, ספורט, סדנאות |

### תבנית תוכן — Approval Card

בנה כרטיס אישור בפורמט הבא לפני כל פרסום:

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📍 [שם המקום]
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

קטגוריה:   [קטגוריה]
עיר:        [עיר]
כתובת:      [כתובת]
GPS:        [lat], [lng]
טלפון:      [טלפון]
אתר:        [url]
שעות:       [שעות פתיחה]

⭐ דירוג גוגל: [rating] ([total] ביקורות)

🗺️ ויז: https://waze.com/ul?ll=[lat],[lng]&navigate=yes

📸 תמונה: מגוגל עסקים ✓

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

## שלב 5 — פרסום

### יצירת listing חדש

```bash
# 1. יצירת הפוסט
ID=$(ssh gonorth "wp post create \
  --post_type=gd_place \
  --post_title='PLACE_NAME' \
  --post_excerpt='SHORT_DESC' \
  --post_content='FULL_CONTENT' \
  --post_status=publish \
  --path=/var/www/gonorth --allow-root --porcelain 2>/dev/null")

# 2. קטגוריה
ssh gonorth "wp post term set $ID gd_placecategory CATEGORY_SLUG \
  --by=slug --path=/var/www/gonorth --allow-root 2>/dev/null"

# 3. מטא-דאטה
ssh gonorth "wp post meta add $ID geodir_post_city 'CITY' --path=/var/www/gonorth --allow-root 2>/dev/null"
ssh gonorth "wp post meta add $ID geodir_post_address 'ADDRESS' --path=/var/www/gonorth --allow-root 2>/dev/null"
ssh gonorth "wp post meta add $ID geodir_post_latitude 'LAT' --path=/var/www/gonorth --allow-root 2>/dev/null"
ssh gonorth "wp post meta add $ID geodir_post_longitude 'LNG' --path=/var/www/gonorth --allow-root 2>/dev/null"
ssh gonorth "wp post meta add $ID geodir_post_phone 'PHONE' --path=/var/www/gonorth --allow-root 2>/dev/null"
ssh gonorth "wp post meta add $ID geodir_post_website 'WEBSITE' --path=/var/www/gonorth --allow-root 2>/dev/null"
ssh gonorth "wp post meta add $ID geodir_post_rating 'RATING' --path=/var/www/gonorth --allow-root 2>/dev/null"
ssh gonorth "wp post meta add $ID geodir_featured '1' --path=/var/www/gonorth --allow-root 2>/dev/null"

# 4. תמונה (לאחר הורדה מגוגל — ראה שלב 2ג-2ד)
ssh gonorth "wp media import /tmp/place_photo.jpg \
  --post_id=$ID --featured_image --title='PLACE_NAME' \
  --path=/var/www/gonorth --allow-root 2>/dev/null"
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

## שלב 6 — עדכון סטטוס בקובץ places-to-add.md

לאחר פרסום מוצלח, עדכן את הסטטוס בקובץ מ-"ממתין" ל-"הוסף".

```bash
# קרא את הקובץ כדי למצוא את השורה הנכונה:
# /Users/navisror/dev/gonorth/places-to-add.md

# עדכן את הסטטוס בשורה של המקום שנוסף:
# לדוגמה: | 9 | פארק המעיינות | אטרקציות | ממתין |
# →        | 9 | פארק המעיינות | אטרקציות | הוסף  |
```

השתמש בכלי Edit (לא sed) כדי לעדכן את הסטטוס — חפש את השורה המדויקת של המקום ושנה "ממתין" ל-"הוסף".

**חשוב:** עשה זאת רק לאחר שהפרסום אושר ו-`wp post create` הצליח.

---

## לינק ויז

פורמט קבוע לכל מקום:
```
https://waze.com/ul?ll={lat},{lng}&navigate=yes
```

דוגמה:
```
https://waze.com/ul?ll=33.191991,35.751795&navigate=yes
```

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
| לשלוף תמונה מגוגל עסקים | להשתמש ב-TripAdvisor כמקור דירוג |
| לכלול לינק ויז בכל מקום | לשכוח לרוץ מהשרת על ה-Places API |
| לכתוב תוכן בעברית RTL בלבד | לפרסם בלי GPS |
| לסמן `geodir_featured=1` | ליצור כפילות לאותו מקום |
| **אסור להשתמש באימוג'י** — לא בתוכן, לא בתיאורים, לא בכרטיס האישור | שימוש באימוג'י בכל שדה |
