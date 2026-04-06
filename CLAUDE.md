# GoNorth — Project Reference for AI Agents

> **Read this first.** This file is the single source of truth for any AI model working on gonorth.co.il. Whether you are Claude, GPT-4, Gemini, or another model — start here before doing anything.

---

## 0. QUICK START

```
Project:    Hebrew RTL tourism directory — northern Israel (gonorth.co.il)
Server:     ssh gonorth   ← key-based auth, no password prompt
WP root:    /var/www/gonorth
WP-CLI:     wp --path=/var/www/gonorth --allow-root
Credentials: read server.config.json (in this repo root)
Skills:     read .agents/skills/{skill-name}/SKILL.md before doing specialized work
Agents:     read .claude/agents/{agent-name}.md for autonomous agent definitions
```

---

## 1. PROJECT IDENTITY

| Field | Value |
|---|---|
| **Site** | gonorth.co.il |
| **Purpose** | Free tourism directory + blog for northern Israel |
| **Audience** | Israeli families, locals, businesses in Galilee / Golan / Carmel |
| **Language** | Hebrew only — RTL (`dir="rtl"`) everywhere |
| **Stack** | WordPress 6.x + Astra + GoNorth child theme + GeoDirectory + WooCommerce + Amelia |
| **Status** | Active development — Phase 2 (design implemented, content seeding in progress) |

**Regions covered:** גליל עליון, גליל תחתון, רמת הגולן, עמק יזרעאל, חוף הכרמל

**Directory categories (4):**
- `atraktziot` — אטרקציות וטבע
- `lina` — לינה וצימרים
- `ochel` — אוכל ושתייה
- `siurim` — סיורים ופעילויות

---

## 2. SERVER ACCESS

### SSH

```bash
# Recommended (uses ~/.ssh/config alias):
ssh gonorth

# Full command (if alias not configured):
ssh -i ~/.ssh/gonorth_rsa -o StrictHostKeyChecking=no claude@204.168.145.116

# Always use the alias — it uses key auth, no password.
```

**SSH config block** (`~/.ssh/config`):
```
Host gonorth
  HostName 204.168.145.116
  User claude
  IdentityFile ~/.ssh/gonorth_rsa
  StrictHostKeyChecking no
```

### File Transfers (SCP)

```bash
# Local → Server
scp /local/path/file.php gonorth:/var/www/gonorth/wp-content/themes/gonorth-child/

# Multiple files
scp /tmp/template1.php /tmp/template2.php gonorth:/var/www/gonorth/wp-content/themes/gonorth-child/

# Server → Local (read/inspect)
scp gonorth:/var/www/gonorth/wp-content/themes/gonorth-child/functions.php /tmp/functions.php
```

### WP-CLI (the right way)

```bash
# Base command — always include both flags:
wp COMMAND --path=/var/www/gonorth --allow-root

# Examples:
ssh gonorth "wp cache flush --path=/var/www/gonorth --allow-root"
ssh gonorth "wp plugin list --path=/var/www/gonorth --allow-root"
ssh gonorth "wp post list --post_type=gd_place --path=/var/www/gonorth --allow-root"

# Get a single value:
ssh gonorth "wp option get siteurl --path=/var/www/gonorth --allow-root"

# Create a post and get its ID:
ssh gonorth "wp post create --post_type=post --post_title='Title' --post_status=publish \
  --path=/var/www/gonorth --allow-root --porcelain"
```

### MCP WordPress (alternative to SSH)

For WordPress-specific operations, an MCP server is configured:

```
Endpoint: https://gonorth.co.il/wp-json/mcp/mcp-adapter-default-server
Username: rynrn
Password: fALc 6wqz QTvw xVKA D4ng 3mGU
Config:   .claude/.mcp.json
```

> **Note:** SSH + WP-CLI is more reliable for bulk operations. MCP is useful for reading/creating posts and pages via REST API.

### Credentials File

All credentials are in `server.config.json` (repo root). Always read it before hardcoding any values.

---

## 3. WORDPRESS ARCHITECTURE

### Paths on Server

```
/var/www/gonorth/                          ← WordPress root
├── wp-config.php                          ← Has FS_METHOD=direct (important!)
├── wp-content/
│   ├── themes/
│   │   ├── astra/                         ← Parent theme (DO NOT MODIFY)
│   │   └── gonorth-child/                 ← Our theme (all edits go here)
│   │       ├── functions.php              ← Enqueue CSS, hooks, helpers
│   │       ├── header.php                 ← Custom GoNorth header
│   │       ├── footer.php                 ← Custom GoNorth footer
│   │       ├── front-page.php             ← Homepage template
│   │       ├── archive-gd_place.php       ← GeoDirectory archive (unused — GD overrides)
│   │       ├── single-gd_place.php        ← Single listing template
│   │       ├── archive.php                ← Blog archive
│   │       ├── single.php                 ← Single blog post
│   │       └── assets/
│   │           └── css/
│   │               └── gonorth.css        ← Main stylesheet (v1.4.0, ~1250 lines)
│   ├── plugins/                           ← All plugins here
│   └── uploads/                           ← Media files
```

### Template Hierarchy (actual usage)

| URL | Template Used |
|---|---|
| `/` (homepage) | `front-page.php` |
| `/places/` (listings archive) | Astra `archive.php` + GeoDirectory content + `astra_content_before` hook injects hero |
| `/places/{slug}` (single listing) | `single-gd_place.php` |
| `/blog/` | Astra `home` template + `astra_content_before` hook injects hero |
| `/blog/{slug}` | `single.php` |
| All others | Astra defaults (with our header.php + footer.php) |

### Key WordPress Page IDs

| Page | ID | Slug |
|---|---|---|
| דף הבית (Home) | 23 | `home` |
| מקומות (Places) | 24 | `places` |
| בלוג (Blog) | 25 | `blog` |
| הוסף מקום (Add Listing) | 28 | `add-listing-2` |
| אודות (About) | 26 | `about` |
| צור קשר (Contact) | 27 | `contact` |
| GD Archive (internal) | 19 | `gd-archive` |
| GD Details (internal) | 21 | `gd-details` |

### GeoDirectory (Directory Plugin)

```
Post type:  gd_place
Taxonomy:   gd_placecategory
Meta fields:
  geodir_post_city       — city name (Hebrew)
  geodir_post_address    — full address
  geodir_post_latitude   — GPS lat (e.g., "32.9")
  geodir_post_longitude  — GPS lng (e.g., "35.5")
  geodir_post_rating     — numeric (e.g., "4.8")
  geodir_featured        — "1" = featured
  geodir_post_phone      — phone number
  geodir_post_website    — URL
  geodir_post_email      — email

Category slugs: atraktziot | lina | ochel | siurim
Default map center: lat=32.9, lng=35.5, zoom=9 (northern Israel)
```

### Active Plugin Stack

| Plugin | Purpose |
|---|---|
| GeoDirectory | Directory listings + maps |
| Elementor (v4.0.1) | Page builder (some pages) |
| Yoast SEO | SEO meta + sitemap |
| WooCommerce | E-commerce / booking support |
| Amelia | Tour booking system |
| Wordfence | Security + firewall |
| UpdraftPlus | Backups |
| WP Super Cache | Page caching |
| Autoptimize | CSS/JS minification |
| Smush | Image compression |

---

## 4. THEME & BRAND

### CSS Variables (defined in gonorth.css `:root`)

```css
:root {
  --gn-green:        #2D6A4F;   /* Primary green — logo, CTAs, headings */
  --gn-green-light:  #40916c;   /* Hover states */
  --gn-green-dark:   #1a3a2a;   /* Hero backgrounds, footer */
  --gn-blue:         #48CAE4;   /* Accent — water, sky */
  --gn-sand:         #F4A261;   /* Warm accent — food, desert */
  --gn-yellow:       #E9C46A;   /* Hero title highlight, badges */
  --gn-brown:        #6B4226;   /* Tours, earthy */
  --gn-bg:           #F8F5F0;   /* Page background (warm off-white) */
  --gn-text:         #1a2e1e;   /* Body text (dark green-black) */
  --gn-muted:        #6e8070;   /* Secondary text, placeholders */
  --gn-border:       #e2e8e4;   /* Dividers, input borders */
  --gn-shadow:       0 2px 16px rgba(0,0,0,.08);
  --gn-shadow-hover: 0 8px 32px rgba(0,0,0,.14);
}
```

### Typography

```css
font-family: 'Heebo', 'Assistant', sans-serif;  /* Always RTL-native fonts */
/* Heebo loaded from Google Fonts — weights 300-900 */
```

### RTL Rules (mandatory on all CSS)

```css
/* Every component must have: */
direction: rtl;
text-align: right;

/* Logical properties (RTL-aware): */
padding-inline-start: 16px;   /* = padding-right in RTL */
margin-inline-end: 8px;       /* = margin-left in RTL */
```

### CSS File Management

```bash
# After ANY CSS change, bump the version in functions.php:
ssh gonorth "sed -i \"s/'1\.4\.0'/'1.5.0'/\" /var/www/gonorth/wp-content/themes/gonorth-child/functions.php"

# Then flush caches:
ssh gonorth "wp cache flush --path=/var/www/gonorth --allow-root"
ssh gonorth "rm -rf /var/www/gonorth/wp-content/cache/autoptimize/*"
```

### Astra Integration Notes

Our `header.php` replaces Astra's header entirely. At the bottom of our `header.php` we call:
```php
if ( function_exists( 'astra_content_before' ) ) astra_content_before();
```
This allows Astra hooks to fire (used to inject custom hero sections on archive pages).

**Injecting content before archive main area:**
```php
// In functions.php:
add_action( 'astra_content_before', function() {
    if ( ! is_post_type_archive('gd_place') ) return;
    echo '<section class="gn-listings-hero">...</section>';
});
```

---

## 5. AGENT SYSTEM

Agents are autonomous AI assistants defined in `.claude/agents/`. Each has a specific role, model, and set of skills.

| Agent | Model | Role | Skills Used |
|---|---|---|---|
| `orchestrator` | opus | Decomposes big tasks, dispatches agents in parallel | All |
| `dev-agent` | sonnet | PHP, CSS, template development | wp-theme, wordpress-pro, ssh-server |
| `designer-agent` | sonnet | SVG, CSS components, visual assets | wp-designer, ssh-server |
| `content-agent` | sonnet | Hebrew blog posts, page copy | wp-content, ssh-server |
| `listings-agent` | sonnet | GeoDirectory listings management | wp-listing, ssh-server |
| `seo-agent` | sonnet | SEO audits, meta, schema markup | wp-seo, ssh-server |
| `security-agent` | sonnet | Security audits, hardening | wp-security, ssh-server |
| `performance-agent` | sonnet | Speed optimization, caching | wp-performance, ssh-server |
| `plugin-agent` | sonnet | Plugin installs, updates, conflicts | wp-plugin, ssh-server |
| `cfo-agent` | opus | Monetization, pricing, financial modeling | wp-monetize |
| `social-media-agent` | sonnet | Hebrew social media content | wp-content |
| `content-quality-agent` | sonnet | Content review, fact-checking | wp-seo, wp-content |

**How to use agents in Claude Code:**
```
# In a conversation, you can say:
"Use the dev-agent to fix the CSS spacing on mobile"
"Ask the orchestrator to run a full site audit"

# Or reference agents directly when spawning sub-agents with the Agent tool.
```

---

## 6. SKILL SYSTEM

Skills are reusable knowledge modules in `.agents/skills/{skill}/SKILL.md`. Always read the relevant skill before doing specialized work.

| Skill | When to Use | Path |
|---|---|---|
| `ssh-server` | Any SSH op, WP-CLI, file transfer | `.agents/skills/ssh-server/SKILL.md` |
| `wp-theme` | CSS changes, PHP templates, RTL fixes | `.agents/skills/wp-theme/SKILL.md` |
| `wordpress-pro` | Hooks, filters, custom PHP, security patterns | `.agents/skills/wordpress-pro/SKILL.md` |
| `wp-listing` | Create/edit GeoDirectory listings | `.agents/skills/wp-listing/SKILL.md` |
| `wp-content` | Hebrew articles, listing descriptions | `.agents/skills/wp-content/SKILL.md` |
| `wp-designer` | SVG icons, CSS components, visual design | `.agents/skills/wp-designer/SKILL.md` |
| `wp-seo` | SEO audit, meta, schema, robots.txt | `.agents/skills/wp-seo/SKILL.md` |
| `wp-security` | Security audit, .htaccess, permissions | `.agents/skills/wp-security/SKILL.md` |
| `wp-performance` | Caching, images, DB optimization | `.agents/skills/wp-performance/SKILL.md` |
| `wp-plugin` | Plugin installs, updates, recommendations | `.agents/skills/wp-plugin/SKILL.md` |
| `wp-monetize` | Revenue streams, pricing, timing | `.agents/skills/wp-monetize/SKILL.md` |
| `elementor` | Elementor page builder operations | `.agents/skills/elementor/SKILL.md` |
| `woocommerce` | WooCommerce, orders, payment gateways | `.agents/skills/woocommerce/SKILL.md` |

---

## 7. COMMON WORKFLOWS

### Add CSS to the theme

```bash
# 1. Append new CSS:
ssh gonorth 'cat >> /var/www/gonorth/wp-content/themes/gonorth-child/assets/css/gonorth.css << '"'"'EOF'"'"'
.my-new-class {
  color: var(--gn-green);
  direction: rtl;
}
EOF'

# 2. Bump CSS version (e.g., 1.4.0 → 1.5.0):
ssh gonorth "sed -i \"s/'1\.4\.0'/'1.5.0'/\" /var/www/gonorth/wp-content/themes/gonorth-child/functions.php"

# 3. Flush cache:
ssh gonorth "wp cache flush --path=/var/www/gonorth --allow-root"
```

### Edit a PHP template

```bash
# 1. Read it first (REQUIRED before Edit tool):
ssh gonorth "cat /var/www/gonorth/wp-content/themes/gonorth-child/front-page.php"

# 2. Write locally, then push:
scp /tmp/front-page.php gonorth:/var/www/gonorth/wp-content/themes/gonorth-child/

# 3. Check PHP syntax before deploying:
ssh gonorth "php -l /var/www/gonorth/wp-content/themes/gonorth-child/front-page.php"

# 4. Test HTTP response:
curl -s -o /dev/null -w "%{http_code}" https://gonorth.co.il/
```

### Create a new GeoDirectory listing

```bash
# 1. Create the post (always 'pending', not 'publish'):
ID=$(ssh gonorth "wp post create \
  --post_type=gd_place \
  --post_title='שם המקום' \
  --post_excerpt='תיאור קצר עד 160 תווים' \
  --post_status=pending \
  --path=/var/www/gonorth --allow-root --porcelain 2>/dev/null")

# 2. Set category (one of: atraktziot, lina, ochel, siurim):
ssh gonorth "wp post term set $ID gd_placecategory atraktziot --by=slug --path=/var/www/gonorth --allow-root"

# 3. Set location metadata:
ssh gonorth "wp post meta add $ID geodir_post_city 'שם העיר' --path=/var/www/gonorth --allow-root"
ssh gonorth "wp post meta add $ID geodir_post_latitude '32.9' --path=/var/www/gonorth --allow-root"
ssh gonorth "wp post meta add $ID geodir_post_longitude '35.5' --path=/var/www/gonorth --allow-root"

# 4. Set optional fields:
ssh gonorth "wp post meta add $ID geodir_post_rating '4.8' --path=/var/www/gonorth --allow-root"
ssh gonorth "wp post meta add $ID geodir_featured '1' --path=/var/www/gonorth --allow-root"
ssh gonorth "wp post meta add $ID geodir_post_phone '04-XXX-XXXX' --path=/var/www/gonorth --allow-root"
```

### Publish a blog post

```bash
ID=$(ssh gonorth "wp post create \
  --post_type=post \
  --post_title='כותרת הכתבה' \
  --post_excerpt='תקציר קצר לצורכי SEO ו-meta' \
  --post_status=publish \
  --path=/var/www/gonorth --allow-root --porcelain 2>/dev/null")
echo "Blog post created: ID $ID"
```

### Debug a 500 error

```bash
# 1. Enable debug log:
ssh gonorth "wp config set WP_DEBUG true --path=/var/www/gonorth --allow-root"
ssh gonorth "wp config set WP_DEBUG_LOG true --path=/var/www/gonorth --allow-root"

# 2. Trigger the page and read the log:
curl -s -o /dev/null https://gonorth.co.il/
ssh gonorth "tail -50 /var/www/gonorth/wp-content/debug.log"

# 3. After fixing, disable debug and clear log:
ssh gonorth "wp config set WP_DEBUG false --path=/var/www/gonorth --allow-root"
ssh gonorth "> /var/www/gonorth/wp-content/debug.log"
```

### Full cache flush

```bash
ssh gonorth "wp cache flush --path=/var/www/gonorth --allow-root"
ssh gonorth "rm -rf /var/www/gonorth/wp-content/cache/autoptimize/* 2>/dev/null"
ssh gonorth "rm -rf /var/www/gonorth/wp-content/cache/supercache/* 2>/dev/null"
```

---

## 8. HARD RULES

These rules must never be broken. Any AI agent working on this project must follow them.

| # | Rule | Why |
|---|---|---|
| 1 | **Never modify WordPress core** (`/wp-includes/`, `/wp-admin/`) | Updates will overwrite changes |
| 2 | **Never set listing `post_status=publish` directly** | All listings go through admin approval first (`pending`) |
| 3 | **Never invent facts** in listings or articles | Addresses, coordinates, prices must be verified |
| 4 | **Always read a file before editing** | The Edit tool requires a prior Read call |
| 5 | **Always use `--allow-root` with WP-CLI** | Server runs as root user |
| 6 | **Always bump CSS version** after editing `gonorth.css` | Prevents browser caching stale CSS |
| 7 | **All content must be Hebrew + RTL** | `direction: rtl; text-align: right` on all CSS |
| 8 | **Never expose server password in tool output** | Use SSH key auth only (`gonorth` alias) |
| 9 | **Never disable `FS_METHOD=direct`** in wp-config.php | WooCommerce will crash with FTP error |
| 10 | **Always PHP lint before deploying** | `php -l /path/to/file.php` |

---

## 9. TECHNICAL REFERENCE

### URLs

| Resource | URL |
|---|---|
| Homepage | https://gonorth.co.il/ |
| Listings archive | https://gonorth.co.il/places/ |
| Blog | https://gonorth.co.il/blog/ |
| Add listing | https://gonorth.co.il/add-listing-2/ |
| WP Admin | https://gonorth.co.il/wp-admin/ |
| WP REST API | https://gonorth.co.il/wp-json/ |
| Main CSS | https://gonorth.co.il/wp-content/themes/gonorth-child/assets/css/gonorth.css |

### Server Environment

| Item | Value |
|---|---|
| OS | Ubuntu (Linux) |
| Web server | Nginx / Apache |
| PHP version | 8.3 |
| WordPress | 6.9.4 |
| WordPress language | he_IL (Hebrew) |
| DB user | in wp-config.php |

### WP Admin Credentials

```
URL:      https://gonorth.co.il/wp-admin/
Username: rynrn
Password: (in server.config.json or MCP config)
```

### CSS File Version History

| Version | Lines | Notes |
|---|---|---|
| 1.0.0 | ~300 | Initial |
| 1.1.0 | ~500 | Full site coverage |
| 1.2.0 | 683 | CSS variables added |
| 1.3.0 | 710 | Astra overrides added |
| 1.4.0 | 1244 | All page templates + GeoDirectory styles |

**Current version: `1.4.0`** — next version should be `1.5.0`

### GeoDirectory GPS Reference Points (Northern Israel)

| Location | Latitude | Longitude |
|---|---|---|
| טבריה (Tiberias) | 32.7940 | 35.5300 |
| צפת (Tzfat) | 32.9646 | 35.4961 |
| נהריה (Nahariya) | 33.0076 | 35.0967 |
| עכו (Akko) | 32.9261 | 35.0756 |
| כרמיאל (Karmiel) | 32.9094 | 35.2974 |
| ראש פינה (Rosh Pina) | 32.9659 | 35.5400 |
| בניאס (Banias) | 33.2481 | 35.6943 |
| כינרת מרכז (Sea of Galilee center) | 32.8300 | 35.6000 |
| מג'דל שמס (Golan) | 33.2667 | 35.7667 |

---

*Last updated: 2026-04-06 | GoNorth project*
