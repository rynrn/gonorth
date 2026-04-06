---
name: wp-theme
description: Develops, edits, and customizes the WordPress block theme for gonorth.co.il. Handles theme.json, templates, template parts, patterns, RTL Hebrew layout, CSS, Elementor customizations, and style variations. Connects directly to the server via SSH to read and apply changes. Use when modifying layout, colors, typography, spacing, RTL fixes, adding block patterns, or editing any theme file.
license: MIT
metadata:
  author: gonorth
  version: "1.0.0"
  domain: platform
  triggers: theme, design, layout, RTL, CSS, style, template, block, pattern, typography, colors, Hebrew layout, theme.json, Elementor, header, footer, homepage
  role: expert
  scope: implementation
  output-format: code
---

# WP Theme — gonorth.co.il

Expert WordPress block theme developer for a Hebrew-language RTL tourism site targeting northern Israel.

## Server Access

Always read `server.config.json` for connection details before making any changes.

```bash
# Config file location
/Users/navisror/dev/gonorth/server.config.json

# SSH alias (always use this)
ssh gonorth

# Key WordPress paths on server
WP root:   /var/www/gonorth
Themes:    /var/www/gonorth/wp-content/themes
Active:    ssh gonorth "wp theme list --status=active --path=/var/www/gonorth --allow-root"
```

## Core Workflow

1. **Detect** — SSH in and identify the active theme, WordPress version, and existing structure
2. **Read** — Pull the relevant file(s) before editing (never overwrite blindly)
3. **Plan** — Describe what will change and why before touching any file
4. **Implement** — Apply changes; respect RTL, Hebrew fonts, and mobile-first layout
5. **Verify** — Re-read the file after writing; confirm structure is valid JSON/PHP/CSS
6. **Report** — Summarize what was changed and what the user should see in the browser

## RTL & Hebrew Rules (ALWAYS apply)

```css
/* Base RTL direction — must be on html/body */
html, body { direction: rtl; text-align: right; }

/* Font stack for Hebrew readability */
font-family: 'Heebo', 'Assistant', 'Rubik', Arial, sans-serif;

/* Flip horizontal spacing/padding for RTL */
margin-right instead of margin-left (logical properties preferred)
padding-inline-start / padding-inline-end

/* Icons and arrows must be mirrored */
transform: scaleX(-1); /* for LTR arrows/chevrons */
```

## theme.json Patterns

### Typography (Hebrew-friendly)
```json
{
  "settings": {
    "typography": {
      "fontFamilies": [
        {
          "fontFamily": "'Heebo', 'Assistant', sans-serif",
          "name": "Heebo",
          "slug": "heebo"
        }
      ],
      "fontSizes": [
        { "name": "Small",   "slug": "small",   "size": "0.875rem" },
        { "name": "Medium",  "slug": "medium",  "size": "1rem" },
        { "name": "Large",   "slug": "large",   "size": "1.5rem" },
        { "name": "X-Large", "slug": "x-large", "size": "2rem" }
      ]
    },
    "color": {
      "palette": [
        { "name": "Nature Green",  "slug": "nature-green",  "color": "#2D6A4F" },
        { "name": "Sky Blue",      "slug": "sky-blue",      "color": "#48CAE4" },
        { "name": "Warm Sand",     "slug": "warm-sand",     "color": "#F4A261" },
        { "name": "Earth Brown",   "slug": "earth-brown",   "color": "#6B4226" },
        { "name": "Light Bg",      "slug": "light-bg",      "color": "#F8F5F0" },
        { "name": "Dark Text",     "slug": "dark-text",     "color": "#1A1A1A" }
      ]
    }
  }
}
```

## Style Hierarchy
```
WordPress core defaults
  → theme.json (base settings)
    → child theme overrides
      → user customizations (Site Editor)
        → Elementor widget styles (if used)
```
> If a style seems ignored, check whether user customizations are overriding it in the Site Editor.

## File Organization

```
/wp-content/themes/{active-theme}/
├── theme.json              ← global settings & styles
├── templates/              ← full page templates (.html)
│   ├── index.html
│   ├── single.html
│   ├── archive.html
│   └── page-home.html
├── parts/                  ← reusable sections (non-nested)
│   ├── header.html
│   ├── footer.html
│   └── listing-card.html
├── patterns/               ← reusable block patterns
│   ├── hero-banner.php
│   ├── listing-grid.php
│   └── cta-section.php
├── assets/
│   ├── css/
│   │   ├── rtl.css         ← RTL overrides
│   │   └── custom.css      ← site-specific styles
│   └── js/
│       └── main.js
└── functions.php
```

## SSH Commands Reference

```bash
# Read active theme name
ssh gonorth "wp theme list --status=active --path=/var/www/gonorth --allow-root --format=value --field=name"

# Read a theme file
ssh gonorth "cat /var/www/gonorth/wp-content/themes/{theme}/theme.json"

# Write a file (pipe local content to server)
cat local-file.css | ssh gonorth "cat > /var/www/gonorth/wp-content/themes/{theme}/assets/css/custom.css"

# Copy a file to server
scp ./file.html gonorth:/var/www/gonorth/wp-content/themes/{theme}/templates/

# Clear theme cache after changes
ssh gonorth "wp cache flush --path=/var/www/gonorth --allow-root"
```

## Constraints

### MUST DO
- Always SSH in to read files before editing
- Always respect RTL layout for all new CSS
- Use Hebrew-friendly fonts (Heebo, Assistant, Rubik)
- Use the gonorth color palette from theme.json
- Run `wp cache flush` after template/theme.json changes
- Mobile-first responsive design

### MUST NOT DO
- Overwrite files without reading them first
- Use LTR-only layout assumptions (e.g., float:left without RTL mirror)
- Modify WordPress core files
- Remove existing RTL or Hebrew font configurations
- Hard-code absolute paths or colors outside theme.json
