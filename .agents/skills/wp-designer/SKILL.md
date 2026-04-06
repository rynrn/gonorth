---
name: wp-designer
description: Designs visual assets for gonorth.co.il — including UI layouts, block designs, banners, hero sections, SVG illustrations, icon sets, color schemes, and Gutenberg block patterns. Extracts design systems from reference images, generates implementation-ready CSS and HTML, and produces tourism-themed visuals for northern Israel. Use when designing new pages, creating banners, generating SVGs, building a visual component, designing listing cards, or defining the site's visual identity.
license: MIT
metadata:
  author: gonorth
  version: "1.0.0"
  domain: design
  triggers: design, banner, hero, illustration, SVG, icon, image, visual, color, layout, block pattern, card, logo, branding, UI, mockup, tourism design, nature illustration
  role: expert
  scope: design + implementation
  output-format: code + assets
---

# WP Designer — gonorth.co.il

Visual designer for a nature-tourism website focusing on the north of Israel. Creates SVGs, CSS components, block patterns, and design systems that are RTL-ready and Hebrew-compatible.

## Brand Identity — gonorth.co.il

```
Theme:      Nature / Outdoors / Adventure
Audience:   Israeli families, locals, travel lovers
Language:   Hebrew (RTL)
Feel:       Fresh, natural, warm, trustworthy, local

Color Palette:
  --nature-green:  #2D6A4F   (forests, nature)
  --sky-blue:      #48CAE4   (Kinneret, sky, water)
  --warm-sand:     #F4A261   (desert, warm tones)
  --earth-brown:   #6B4226   (earth, trails)
  --light-bg:      #F8F5F0   (clean, airy backgrounds)
  --dark-text:     #1A1A1A   (readable Hebrew text)
  --accent-yellow: #E9C46A   (highlights, CTA)

Typography:
  Primary:   Heebo (Hebrew + Latin, clean, modern)
  Secondary: Assistant (body text)
  Display:   Rubik (headings, bold statements)

Design Principles:
  - Photography-driven (landscape, food, people)
  - Generous whitespace
  - Rounded corners (8-16px)
  - Subtle shadows (not flat, not skeuomorphic)
  - Mobile-first, RTL-first
```

## Core Workflow

1. **Understand** — What is the asset for? Where does it appear? What emotion should it convey?
2. **Extract** — If a reference image is provided, extract colors, typography, spacing, and component patterns
3. **Design system check** — Ensure the design aligns with the gonorth brand palette and typography
4. **Generate** — Produce the asset (SVG / CSS / HTML block pattern / Elementor JSON)
5. **RTL audit** — Verify the design works in right-to-left layout
6. **Deliver** — Provide the asset + usage instructions + where to place it on the server

## Design Templates

### Hero Banner (CSS + HTML)
```html
<section class="gn-hero" dir="rtl">
  <div class="gn-hero__overlay"></div>
  <div class="gn-hero__content">
    <h1 class="gn-hero__title">גלה את הצפון</h1>
    <p class="gn-hero__subtitle">אטרקציות, לינה, אוכל וסיורים בצפון ישראל</p>
    <a href="/listings" class="gn-hero__cta">גלה עכשיו</a>
  </div>
</section>
```
```css
.gn-hero {
  position: relative;
  min-height: 80vh;
  display: flex;
  align-items: center;
  background-size: cover;
  background-position: center;
  font-family: 'Heebo', sans-serif;
  direction: rtl;
}
.gn-hero__overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to left, rgba(45,106,79,0.7), rgba(0,0,0,0.3));
}
.gn-hero__content {
  position: relative;
  padding: 2rem;
  color: white;
  max-width: 600px;
  text-align: right;
}
.gn-hero__cta {
  display: inline-block;
  background: var(--warm-sand, #F4A261);
  color: #1A1A1A;
  padding: 0.75rem 2rem;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 700;
  margin-top: 1.5rem;
}
```

### Listing Card (SVG + CSS)
```html
<article class="gn-card" dir="rtl">
  <div class="gn-card__image">
    <img src="{image}" alt="{title}" loading="lazy" />
    <span class="gn-card__category">{category}</span>
  </div>
  <div class="gn-card__body">
    <h3 class="gn-card__title">{title}</h3>
    <p class="gn-card__desc">{description}</p>
    <div class="gn-card__footer">
      <span class="gn-card__location">📍 {location}</span>
      <a href="{url}" class="gn-card__link">פרטים נוספים ←</a>
    </div>
  </div>
</article>
```
```css
.gn-card {
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 16px rgba(0,0,0,0.08);
  background: #fff;
  transition: transform 0.2s, box-shadow 0.2s;
  direction: rtl;
  text-align: right;
}
.gn-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
.gn-card__image { position: relative; aspect-ratio: 16/9; overflow: hidden; }
.gn-card__image img { width: 100%; height: 100%; object-fit: cover; }
.gn-card__category {
  position: absolute; top: 12px; right: 12px;
  background: var(--nature-green, #2D6A4F);
  color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem;
}
.gn-card__body { padding: 1.25rem; }
.gn-card__title { font-size: 1.1rem; font-weight: 700; color: #1A1A1A; margin-bottom: 0.5rem; }
.gn-card__footer { display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; }
```

## SVG Assets

### Location Pin Icon (gonorth branded)
```svg
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
  <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"
        fill="#2D6A4F"/>
  <circle cx="12" cy="9" r="2.5" fill="#F8F5F0"/>
</svg>
```

### Nature Divider SVG (between sections)
```svg
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 60" preserveAspectRatio="none">
  <path d="M0,0 C360,60 1080,0 1440,60 L1440,60 L0,60 Z" fill="#F8F5F0"/>
</svg>
```

## SSH Deployment

```bash
# Upload CSS asset to theme
cat ./custom-design.css | ssh gonorth "cat >> /var/www/gonorth/wp-content/themes/{theme}/assets/css/custom.css"

# Upload SVG to uploads
scp ./icon.svg gonorth:/var/www/gonorth/wp-content/uploads/icons/

# Clear cache after design changes
ssh gonorth "wp cache flush --path=/var/www/gonorth --allow-root"
```

## Constraints

### MUST DO
- Always use the gonorth color palette
- All designs must work RTL (Hebrew right-to-left)
- Use Heebo/Assistant/Rubik font stack
- Provide both the asset code AND placement instructions
- Designs must be mobile-first
- SVGs must be accessible (title + role attributes)

### MUST NOT DO
- Use LTR-only layout patterns without RTL overrides
- Use low-contrast color combinations (accessibility)
- Hard-code pixel fonts that break on Hebrew characters
- Create designs that don't reflect the nature/tourism theme
