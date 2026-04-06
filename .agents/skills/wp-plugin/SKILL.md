---
name: wp-plugin
description: Recommends, installs, configures, and manages WordPress plugins for gonorth.co.il. Evaluates plugins based on the site's stack (GeoDirectory, Elementor, WooCommerce, Hebrew/RTL). Installs and activates via WP-CLI over SSH. Use when you need a plugin for a specific feature, want to audit current plugins, or need to install/remove/configure a plugin.
license: MIT
metadata:
  author: gonorth
  version: "1.0.0"
  domain: plugins
  triggers: plugin, install, activate, deactivate, remove, configure, WooCommerce, Elementor, GeoDirectory, recommend, what plugin, which plugin
  role: expert
  scope: recommendation + installation
  output-format: recommendation + commands
---

# WP Plugin — gonorth.co.il

WordPress plugin advisor and manager for a Hebrew tourism directory site. Recommends plugins that are RTL-compatible, GeoDirectory-friendly, and Elementor-compatible.

## Server Access

```bash
ssh gonorth
WP path: /var/www/gonorth
```

## Core Plugin Stack (Already Installed / Target)

| Plugin | Purpose | Priority |
|--------|---------|----------|
| GeoDirectory | Directory & listings | 🔴 Core |
| Elementor | Page builder | 🔴 Core |
| Yoast SEO | SEO management | 🔴 Core |
| WooCommerce | Payments & bookings base | 🔴 Core |
| Wordfence | Security firewall | 🔴 Core |
| UpdraftPlus | Backups | 🔴 Core |
| Bookly / Amelia | Tour & activity bookings | 🟡 Important |
| MotoPress Hotel Booking | Accommodation bookings | 🟡 Important |
| WP Super Cache | Caching | 🟡 Important |
| Autoptimize | Asset optimization | 🟡 Important |
| WP Smush | Image compression | 🟡 Important |
| Advanced Ads | Future ad management | 🟢 Future |

## Plugin Recommendation Framework

When the user describes a need, evaluate plugins based on:

```
1. RTL / Hebrew compatibility    ← critical for gonorth.co.il
2. GeoDirectory compatibility    ← must not conflict
3. Elementor compatibility       ← must not break builder
4. Active installs + rating      ← > 10k installs, > 4 stars
5. Last updated                  ← < 6 months ago preferred
6. License                       ← free tier sufficient?
7. Performance impact            ← low DB queries, no bloat
```

## Common Feature Requests & Recommendations

### Booking System
```
Need: Let users book tours and activities
Recommended: Amelia (free tier)
Alternative: Bookly (free tier limited)
Avoid: Plugins not updated in 1+ year
Install: wp plugin install ameliabooking --activate
```

### Contact / Inquiry Forms
```
Need: Contact form, listing inquiry form
Recommended: WPForms Lite (free)
Alternative: Fluent Forms (free, very fast)
Avoid: Contact Form 7 (no spam protection in free tier)
Install: wp plugin install wpforms-lite --activate
```

### Social Sharing
```
Need: Share listings/articles on WhatsApp, Facebook (Israeli users)
Recommended: Social Warfare (free) or Sassy Social Share
Note: WhatsApp share is critical for Israeli audience
Install: wp plugin install sassy-social-share --activate
```

### Maps (backup for GeoDirectory)
```
Need: Standalone map blocks in posts
Recommended: WP Google Maps (free)
Alternative: Leaflet Maps Marker
```

### Hebrew / RTL Tools
```
Need: Improve RTL rendering
Recommended: RTL Tester (dev tool)
Note: Most RTL issues are theme-level, not plugin-level
```

### Newsletter / Email Marketing
```
Need: Send newsletters to subscribers
Recommended: Mailchimp for WordPress (free)
Alternative: MailPoet (free up to 1k subscribers — great for Israel)
Install: wp plugin install mailchimp-for-wp --activate
```

### Pop-ups / Lead Capture
```
Need: Capture emails, promote seasonal deals
Recommended: Hustle (free)
Alternative: Sumo (free tier)
```

## WP-CLI Plugin Commands

```bash
# List all installed plugins
ssh gonorth "wp plugin list --path=/var/www/gonorth --allow-root"

# Search for a plugin
ssh gonorth "wp plugin search '{keyword}' --per-page=5 --fields=name,slug,rating,num_ratings --path=/var/www/gonorth --allow-root"

# Install a plugin
ssh gonorth "wp plugin install {slug} --activate --path=/var/www/gonorth --allow-root"

# Deactivate a plugin
ssh gonorth "wp plugin deactivate {slug} --path=/var/www/gonorth --allow-root"

# Delete a plugin
ssh gonorth "wp plugin delete {slug} --path=/var/www/gonorth --allow-root"

# Update a specific plugin
ssh gonorth "wp plugin update {slug} --path=/var/www/gonorth --allow-root"

# Update all plugins
ssh gonorth "wp plugin update --all --path=/var/www/gonorth --allow-root"

# Check plugin info
ssh gonorth "wp plugin get {slug} --fields=name,version,update,status --path=/var/www/gonorth --allow-root"
```

## Plugin Audit Command

Run this monthly to check for issues:
```bash
ssh gonorth "echo '=== Plugin Audit ===' && \
  echo '--- Active Plugins ---' && \
  wp plugin list --status=active --fields=name,version,update --path=/var/www/gonorth --allow-root && \
  echo '--- Inactive Plugins (consider removing) ---' && \
  wp plugin list --status=inactive --path=/var/www/gonorth --allow-root && \
  echo '--- Available Updates ---' && \
  wp plugin update --all --dry-run --path=/var/www/gonorth --allow-root"
```

## Constraints

### MUST DO
- Verify RTL compatibility before recommending any UI plugin
- Test plugin installs on staging if available, or during low-traffic hours
- Always check plugin conflict potential with GeoDirectory + Elementor
- Remove (not just deactivate) unused plugins

### MUST NOT DO
- Install multiple plugins that do the same thing
- Install nulled or unofficial plugin versions
- Install page builder plugins that conflict with Elementor
- Recommend plugins last updated > 12 months ago
