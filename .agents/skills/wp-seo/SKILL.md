---
name: wp-seo
description: Audits and improves SEO for gonorth.co.il — including meta titles, descriptions, schema markup, Hebrew keyword research, Yoast SEO configuration, sitemap, robots.txt, and page-level SEO scoring. Connects to the server via SSH to read files and run WP-CLI commands. Use when checking SEO health, optimizing a page or post, configuring Yoast, researching Hebrew keywords, or preparing for Google indexing.
license: MIT
metadata:
  author: gonorth
  version: "1.0.0"
  domain: seo
  triggers: SEO, meta, keywords, Google, Yoast, sitemap, robots, schema, ranking, search, Hebrew SEO, indexing, page title, description, structured data
  role: expert
  scope: audit + implementation
  output-format: report + wp-cli commands
---

# WP SEO — gonorth.co.il

SEO specialist for a Hebrew WordPress tourism site. Focuses on Israeli search behavior, Google.co.il rankings, and local tourism keywords for the north of Israel.

## Server Access

```bash
ssh gonorth
WP path: /var/www/gonorth
Yoast plugin: /var/www/gonorth/wp-content/plugins/wordpress-seo
```

## Core Workflow

1. **Audit** — SSH in and check current SEO state (Yoast config, meta tags, sitemap)
2. **Identify** — Find pages with missing/weak titles, descriptions, or schema
3. **Research** — Suggest Hebrew keywords based on page topic
4. **Fix** — Apply improvements via WP-CLI or file edits
5. **Verify** — Re-check and confirm changes were applied
6. **Report** — Deliver a prioritized list of improvements

## Hebrew Keyword Research

### High-value keyword patterns for gonorth.co.il:
```
[activity] + [location] — e.g., "טיולים בגליל", "צימרים בגולן"
[place type] + בצפון — e.g., "מסעדות בצפון", "מפלים בצפון"
[season] + [location] — e.g., "חרמון בחורף", "כינרת בקיץ"
מה לעשות ב + [location] — "מה לעשות בגליל העליון"
אטרקציות + [location] — "אטרקציות בחיפה", "אטרקציות בצפת"

Primary targets:
- טיולים בצפון ישראל
- אטרקציות בגליל
- צימרים בצפון
- מסעדות בגליל
- דברים לעשות בצפון
- סיורים בצפון ישראל
- לינה בגולן
```

## SEO Audit Checklist

```bash
# Check Yoast is active
ssh gonorth "wp plugin status wordpress-seo --path=/var/www/gonorth --allow-root"

# List pages without Yoast meta title set
ssh gonorth "wp post list --meta_key=_yoast_wpseo_title --meta_value='' --path=/var/www/gonorth --allow-root"

# Check sitemap exists
ssh gonorth "curl -s -o /dev/null -w '%{http_code}' https://gonorth.co.il/sitemap_index.xml"

# Check robots.txt
ssh gonorth "curl -s https://gonorth.co.il/robots.txt"

# List all published posts with their slugs
ssh gonorth "wp post list --post_status=publish --fields=ID,post_title,post_name --path=/var/www/gonorth --allow-root"
```

## Yoast SEO Configuration via WP-CLI

```bash
# Set site-wide meta title template
ssh gonorth "wp option update wpseo_titles '{\
  \"title-home-wpseo\":\"גלה את הצפון | gonorth.co.il\",\
  \"title-post\":\"%%title%% | gonorth.co.il\",\
  \"title-page\":\"%%title%% | גו נורת׳\"\
}' --format=json --path=/var/www/gonorth --allow-root"

# Set Yoast meta for a specific post
ssh gonorth "wp post meta update {post_id} _yoast_wpseo_title 'כותרת SEO כאן' --path=/var/www/gonorth --allow-root"
ssh gonorth "wp post meta update {post_id} _yoast_wpseo_metadesc 'תיאור SEO עד 160 תווים' --path=/var/www/gonorth --allow-root"
ssh gonorth "wp post meta update {post_id} _yoast_wpseo_focuskw 'מילת מפתח ראשית' --path=/var/www/gonorth --allow-root"
```

## Schema Markup for Tourism (JSON-LD)

```json
{
  "@context": "https://schema.org",
  "@type": "TouristAttraction",
  "name": "{name}",
  "description": "{description}",
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "{city}",
    "addressCountry": "IL"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": "{lat}",
    "longitude": "{lng}"
  },
  "url": "{url}",
  "image": "{image_url}",
  "inLanguage": "he"
}
```

## Title & Description Formulas

| Page Type | Title Formula | Description Formula |
|-----------|--------------|---------------------|
| Homepage | גלה את הצפון – אטרקציות, לינה ואוכל \| gonorth.co.il | כל מה שצריך לטיול מושלם בצפון ישראל – אטרקציות, צימרים, מסעדות וסיורים מודרכים |
| Category | {Category} בצפון ישראל \| gonorth | מדריך {Category} בצפון – מהמומלצים והמובחרים |
| Single Post | {Title} – מדריך מלא \| gonorth | {Excerpt 155 chars} |
| Listing | {Name} – {City} \| gonorth | {Short description 155 chars} |

## robots.txt Template

```
User-agent: *
Allow: /

Disallow: /wp-admin/
Disallow: /wp-login.php
Disallow: /?s=
Disallow: /search/

Sitemap: https://gonorth.co.il/sitemap_index.xml
```

## Constraints

### MUST DO
- Always check Hebrew keyword intent — Israeli users search differently than diaspora
- Ensure all meta titles stay under 60 characters
- Ensure all meta descriptions stay under 160 characters
- Include location name (גליל, גולן, כינרת, etc.) in meta when relevant
- Add schema markup for all listings (TouristAttraction, Restaurant, LodgingBusiness)

### MUST NOT DO
- Use English keywords for Hebrew pages
- Set duplicate meta titles across multiple pages
- Forget to verify sitemap after major content changes
- Block important pages in robots.txt
