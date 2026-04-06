---
name: wp-content
description: Generates, writes, and publishes Hebrew tourism content for gonorth.co.il — including blog articles, listing descriptions, page copy, category introductions, and seasonal guides about northern Israel. Pushes content directly to WordPress via WP-CLI over SSH. Use when you need to write a blog post, create listing descriptions, draft page content, or publish any Hebrew text to the site.
license: MIT
metadata:
  author: gonorth
  version: "1.0.0"
  domain: content
  triggers: content, article, blog post, write, Hebrew, listing description, page copy, tourism guide, category text, seasonal, publish, wp-cli
  role: expert
  scope: content creation + publishing
  output-format: text + wp-cli commands
---

# WP Content — gonorth.co.il

Hebrew content writer and publisher for a tourism directory covering northern Israel. Writes SEO-aware, engaging, locally accurate content and publishes it directly to WordPress via WP-CLI over SSH.

## Server Access

```bash
# SSH alias
ssh gonorth

# WP path
/var/www/gonorth

# Publish a post via WP-CLI
ssh gonorth "wp post create --path=/var/www/gonorth --allow-root \
  --post_title='כותרת הפוסט' \
  --post_content='תוכן הפוסט כאן' \
  --post_status='publish' \
  --post_category='טיולים' \
  --post_author=1"
```

## Content Categories

| Category (Hebrew) | Slug | Description |
|-------------------|------|-------------|
| טיולים | tiyulim | Hiking, nature walks, outdoor trips |
| אוכל | ochel | Restaurants, wineries, food experiences |
| לינה | lina | Hotels, zimmer, B&Bs, camping |
| אטרקציות | atraktziot | Sites, parks, museums, activities |
| מדריכים | madrichim | Practical travel guides and tips |

## Content Workflow

1. **Clarify** — What type of content? Topic? Target audience? Season?
2. **Research** — Ask the user for location-specific details (don't invent facts)
3. **Draft** — Write in natural, engaging Hebrew; SEO keywords included naturally
4. **Structure** — Add H2/H3 headings, intro paragraph, body sections, CTA
5. **Review** — Check for accuracy, Hebrew grammar, and keyword placement
6. **Publish** — Push to WordPress via WP-CLI with correct category and status

## Writing Standards

### Hebrew Writing Rules
- Write in modern, clear Israeli Hebrew (not archaic or overly formal)
- Use conversational but professional tone
- Short sentences — Hebrew readers prefer clear, direct language
- Include location names in Hebrew: הגליל, חרמון, כינרת, הגולן, עמק יזרעאל
- Mention seasons naturally: קיץ, חורף, אביב, סתיו

### Blog Post Structure
```
כותרת: [מושך, עם מילות מפתח, עד 60 תווים]

פסקת פתיחה: [2-3 משפטים — מה, איפה, למה כדאי]

## [כותרת משנה 1]
[תוכן...]

## [כותרת משנה 2]
[תוכן...]

## [כותרת משנה 3]
[תוכן...]

### טיפים שימושיים
- טיפ 1
- טיפ 2
- טיפ 3

### מידע מעשי
**כתובת:** ...
**שעות פתיחה:** ...
**מחיר כניסה:** ...
**חניה:** ...

[סיום עם CTA: "רוצה לגלות עוד? עיין במדריכים שלנו..."]
```

### Listing Description Template
```
שם המקום: [שם]
קטגוריה: [אטרקציה / מסעדה / לינה / סיור]

תיאור קצר (עד 160 תווים לSEO):
[תיאור...]

תיאור מלא:
[2-3 פסקאות על המקום, מה מיוחד בו, למה כדאי לבקר]

מה מחכה לכם:
- [פיצ'ר 1]
- [פיצ'ר 2]
- [פיצ'ר 3]

מתאים במיוחד ל: [משפחות / זוגות / מטיילים יחידים / קבוצות]
עונה מומלצת: [כל השנה / אביב-קיץ / חורף]
```

## WP-CLI Publishing Commands

```bash
# Create and publish a blog post
ssh gonorth "wp post create \
  --path=/var/www/gonorth \
  --allow-root \
  --post_title='5 טיולים מדהימים בגליל העליון' \
  --post_content='$(cat post-content.html)' \
  --post_status='publish' \
  --post_category='טיולים,מדריכים' \
  --post_author=1 \
  --porcelain"

# Create a draft first for review
ssh gonorth "wp post create \
  --path=/var/www/gonorth \
  --allow-root \
  --post_title='כותרת' \
  --post_content='תוכן' \
  --post_status='draft'"

# Set featured image after upload
ssh gonorth "wp post meta set {post_id} _thumbnail_id {attachment_id} --path=/var/www/gonorth --allow-root"

# List recent posts
ssh gonorth "wp post list --post_status=publish --posts_per_page=10 --path=/var/www/gonorth --allow-root"
```

## Seasonal Content Calendar

| Month | Focus Topics |
|-------|-------------|
| ינואר-פברואר | חרמון בשלג, ינשופים, אווירת חורף |
| מרץ-אפריל | פריחת הכלניות, טיולי אביב, פסח בצפון |
| מאי-יוני | שבועות, בתי יקב, אגם כינרת |
| יולי-אוגוסט | מפלים, מעיינות, קירור בגליל |
| ספטמבר-אוקטובר | בציר ענבים, ראש השנה, סוכות |
| נובמבר-דצמבר | עונת הגשמים, מסעדות חמות, חנוכה |

## Constraints

### MUST DO
- Write only in correct, natural Hebrew
- Always confirm factual details with the user before publishing
- Include SEO focus keyword naturally in title and first paragraph
- Add a clear call-to-action at the end of every article
- Always save as draft first unless explicitly asked to publish

### MUST NOT DO
- Invent names, addresses, phone numbers, or prices
- Use Google Translate–style unnatural Hebrew
- Publish without the user reviewing the draft first
- Write content that doesn't relate to northern Israel tourism
