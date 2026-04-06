---
name: wp-listing
description: Creates, formats, and publishes directory listings for gonorth.co.il using GeoDirectory. Handles all four listing types — attractions, hotels/accommodation, restaurants/food, and tours/activities. Generates complete listing data including Hebrew description, category, tags, coordinates, and pushes it to WordPress via WP-CLI over SSH. Use when adding a new place, attraction, restaurant, zimmer, or tour to the directory.
license: MIT
metadata:
  author: gonorth
  version: "1.0.0"
  domain: directory
  triggers: listing, place, attraction, hotel, zimmer, restaurant, tour, activity, directory, GeoDirectory, add place, new listing, coordinates, map pin
  role: expert
  scope: content creation + publishing
  output-format: structured data + wp-cli commands
---

# WP Listing — gonorth.co.il

Directory listing creator for a Hebrew tourism site powered by GeoDirectory. Creates complete, SEO-ready listings for places in northern Israel.

## Server Access

```bash
ssh gonorth
WP path: /var/www/gonorth
GeoDirectory CPT: gd_place
```

## Listing Categories

| Hebrew | Slug | GD Category | Icon |
|--------|------|-------------|------|
| אטרקציות וטבע | atraktziot | attractions | 🏛️ |
| לינה | lina | accommodation | 🏨 |
| אוכל ושתייה | ochel | food | 🍽️ |
| סיורים ופעילויות | siurim | tours | 🗺️ |

## Listing Intake Form

When a user asks to create a listing, gather these fields:

```
REQUIRED:
├── שם המקום (name)
├── קטגוריה (category): אטרקציה / לינה / אוכל / סיור
├── עיר / אזור (city/region): e.g., טבריה, צפת, כרמיאל
├── תיאור קצר (tagline, up to 160 chars)
├── תיאור מלא (full description, 2-4 paragraphs)
└── כתובת (address)

OPTIONAL BUT RECOMMENDED:
├── קואורדינטות GPS (lat/lng) — ask user or suggest lookup
├── טלפון (phone)
├── אתר אינטרנט (website)
├── שעות פתיחה (opening hours)
├── מחיר (price range: חינם / ₪ / ₪₪ / ₪₪₪)
├── תמונה ראשית (main image URL or path)
├── תגיות (tags): e.g., משפחות, טבע, ים כינרת
└── עונה מומלצת (season): כל השנה / אביב / קיץ / חורף
```

## Complete Listing Templates

### Attraction (אטרקציה)
```
שם: [name]
תיאור קצר: [tagline — 160 chars, Hebrew, SEO-friendly]

תיאור מלא:
[Paragraph 1: What is it? What makes it special?]
[Paragraph 2: What will visitors experience?]
[Paragraph 3: Practical tips — best time, what to bring, nearby stops]

מה מחכה לכם:
• [Feature 1]
• [Feature 2]
• [Feature 3]

מידע מעשי:
כתובת: [address]
שעות: [hours]
כניסה: [free / price]
חניה: [yes/no/paid]
מתאים ל: [families / couples / solo / groups]
```

### Zimmer / Accommodation (לינה)
```
שם: [name]
תיאור קצר: [tagline]

תיאור מלא:
[Description of the property and atmosphere]
[Amenities and what's included]
[Surroundings and nearby attractions]

מה כלול:
• [Amenity 1]
• [Amenity 2]

מידע מעשי:
כתובת: [address]
טלפון: [phone]
מחיר: [price range] ללילה
צ׳ק-אין: [time] | צ׳ק-אאוט: [time]
```

### Restaurant / Food (אוכל)
```
שם: [name]
סוג מטבח: [e.g., ים-תיכוני, גריל, קפה, יקב]
תיאור קצר: [tagline]

תיאור מלא:
[Atmosphere and concept]
[Signature dishes or drinks]
[Why visit]

מידע מעשי:
כתובת: [address]
טלפון: [phone]
שעות: [hours]
מחיר ממוצע: [₪ / ₪₪ / ₪₪₪]
כשרות: [yes/no/type]
```

### Tour / Activity (סיור)
```
שם: [name]
סוג: [guided tour / self-guided / kayak / jeep / hike]
תיאור קצר: [tagline]

תיאור מלא:
[What the activity involves]
[Who leads it / what's included]
[Duration, meeting point, what to bring]

מידע מעשי:
משך: [duration]
רמת קושי: [קל / בינוני / מאתגר]
מחיר: [per person]
הזמנות: [phone / website]
מינימום משתתפים: [number]
```

## Publishing via WP-CLI

```bash
# Create a new GeoDirectory listing
ssh gonorth "wp post create \
  --path=/var/www/gonorth \
  --allow-root \
  --post_type='gd_place' \
  --post_title='{name}' \
  --post_content='{full_description}' \
  --post_excerpt='{short_description}' \
  --post_status='pending' \
  --porcelain"

# Set listing category
ssh gonorth "wp post term set {post_id} gd_placecategory {category_slug} --path=/var/www/gonorth --allow-root"

# Set GeoDirectory meta fields
ssh gonorth "wp post meta update {post_id} geodir_post_address '{address}' --path=/var/www/gonorth --allow-root"
ssh gonorth "wp post meta update {post_id} geodir_post_city '{city}' --path=/var/www/gonorth --allow-root"
ssh gonorth "wp post meta update {post_id} geodir_post_latitude '{lat}' --path=/var/www/gonorth --allow-root"
ssh gonorth "wp post meta update {post_id} geodir_post_longitude '{lng}' --path=/var/www/gonorth --allow-root"
ssh gonorth "wp post meta update {post_id} geodir_post_phone '{phone}' --path=/var/www/gonorth --allow-root"
ssh gonorth "wp post meta update {post_id} geodir_post_website '{website}' --path=/var/www/gonorth --allow-root"

# List pending listings awaiting approval
ssh gonorth "wp post list --post_type=gd_place --post_status=pending --path=/var/www/gonorth --allow-root"

# Approve (publish) a listing
ssh gonorth "wp post update {post_id} --post_status=publish --path=/var/www/gonorth --allow-root"
```

## GPS Coordinates Helper

Common northern Israel locations:
```
טבריה:        32.7940, 35.5310
צפת:          32.9646, 35.4953
נהריה:        33.0076, 35.0970
עכו:          32.9227, 35.0681
כרמיאל:       32.9193, 35.2985
קיסריה:       32.5018, 34.9046
חיפה (כרמל): 32.7940, 34.9896
ראש פינה:     32.9713, 35.5444
בניאס:        33.2483, 35.6949
כינרת (אגם): 32.8000, 35.5800
```

## Constraints

### MUST DO
- Always save new listings as `pending` (not publish) for admin review
- Confirm GPS coordinates before publishing — wrong pins mislead users
- Use Hebrew for all visible content
- Include a short SEO description (160 chars) for every listing
- Add relevant tags for filtering (משפחות, טבע, ספורט, רומנטי, etc.)

### MUST NOT DO
- Invent phone numbers, prices, or opening hours
- Publish directly without admin approval unless explicitly authorized
- Create duplicate listings for the same place
- Leave latitude/longitude empty — map pins are essential
