---
name: wp-monetize
description: Plans and implements monetization strategies for gonorth.co.il — including paid listings, featured placement, banner ads, booking commissions, and subscription packages. Advises on timing, pricing, and implementation using GeoDirectory + WooCommerce. Use when ready to add revenue streams, set up paid listing packages, configure ad placements, or plan a monetization roadmap.
license: MIT
metadata:
  author: gonorth
  version: "1.0.0"
  domain: monetization
  triggers: monetize, revenue, paid listing, featured, premium, ads, commission, subscription, pricing, package, income, business model
  role: expert
  scope: strategy + implementation
  output-format: plan + commands
---

# WP Monetize — gonorth.co.il

Monetization strategist for a Hebrew tourism directory. Advises on when and how to introduce revenue streams without harming user trust or listing quality.

## Server Access

```bash
ssh gonorth
WP path: /var/www/gonorth
```

## Monetization Roadmap

### Phase 1 — Launch (Free, Build Trust)
> **Now** — focus on getting listings and traffic first
- All listings free
- No ads
- Goal: reach 50+ quality listings and 1,000+ monthly visitors

### Phase 2 — Soft Monetization (3-6 months post-launch)
> Introduce low-friction revenue — businesses barely notice
- Featured/highlighted listings (small badge, top placement)
- "Claim your listing" upsell for businesses
- Email newsletter sponsorship (single sponsor per issue)

### Phase 3 — Full Monetization (6-12 months)
> Structured packages, ads, commissions
- Tiered listing packages (Free / Basic / Pro / Premium)
- Banner ads in category pages and sidebar
- Booking commissions via WooCommerce

### Phase 4 — Scale
- Annual business subscription plans
- API access for aggregators
- Sponsored content / editorial partnerships

---

## Revenue Streams Detail

### 1. Featured Listings (GeoDirectory)
GeoDirectory has built-in support for featured listings — they appear first in search results with a highlight badge.

```bash
# Enable featured listing in GeoDirectory settings
ssh gonorth "wp option update geodir_settings__featured_listing 'yes' --path=/var/www/gonorth --allow-root"

# Manually feature a listing (free for now, paid later)
ssh gonorth "wp post meta update {post_id} geodir_featured 1 --path=/var/www/gonorth --allow-root"
```

**Suggested pricing (Israeli market):**
| Package | Price | Duration |
|---------|-------|----------|
| Featured listing | ₪49 | 1 month |
| Featured listing | ₪149 | 3 months |
| Featured + top of category | ₪299 | 3 months |

---

### 2. Paid Listing Packages (GeoDirectory + WooCommerce)

```bash
# Install GeoDirectory Payment Manager add-on (if available)
# Or create WooCommerce products for each package

# Create WooCommerce product for Basic listing package
ssh gonorth "wp wc product create \
  --name='רישום בסיסי - gonorth' \
  --regular_price='29' \
  --type='simple' \
  --status='publish' \
  --path=/var/www/gonorth \
  --allow-root \
  --user=1"
```

**Package Tiers:**
```
חינם (Free):
  ✅ רישום בסיסי
  ✅ תמונה אחת
  ✅ פרטי קשר
  ❌ מפה
  ❌ גלריית תמונות
  ❌ קידום בחיפוש

בייסיק (Basic) — ₪29/חודש:
  ✅ כל האפשרויות החינמיות
  ✅ מפה אינטראקטיבית
  ✅ גלריה עד 5 תמונות
  ✅ כפתור הזמנה
  ❌ פיצ׳ר בחיפוש

פרו (Pro) — ₪79/חודש:
  ✅ כל אפשרויות הבייסיק
  ✅ גלריה עד 20 תמונות
  ✅ סטטיסטיקות צפיות
  ✅ עדיפות בתוצאות חיפוש
  ✅ תגית "מומלץ"

פרמיום (Premium) — ₪199/חודש:
  ✅ כל אפשרויות הפרו
  ✅ פיצ׳ר בדף הבית
  ✅ כתבה שיווקית
  ✅ ניוזלטר mention
  ✅ מנהל חשבון אישי
```

---

### 3. Banner Advertising (Advanced Ads)

```bash
# Install Advanced Ads
ssh gonorth "wp plugin install advanced-ads --activate --path=/var/www/gonorth --allow-root"
```

**Placement strategy for tourism site:**
```
מיקום            | גודל        | מחיר מוצע
-----------------|------------|------------
Header banner    | 728x90     | ₪499/חודש
Category sidebar | 300x250    | ₪299/חודש
Between listings | 600x150    | ₪199/חודש
Article footer   | 728x90     | ₪149/חודש
Mobile sticky    | 320x50     | ₪199/חודש
```

---

### 4. Booking Commissions (WooCommerce)

When bookings are made through the site:
```bash
# Set up WooCommerce for commission tracking
ssh gonorth "wp plugin install woocommerce --activate --path=/var/www/gonorth --allow-root"

# Commission rate: 10-15% per booking (Israeli market standard)
# Configure in: WooCommerce > Settings > Payments
```

**Commission structure:**
```
סיורים ופעילויות: 12% עמלה
לינה (צימרים):   10% עמלה
מסעדות:          לא רלוונטי (הפניה בלבד)
אטרקציות:        8% עמלה (כרטיסים)
```

---

### 5. Newsletter Sponsorship

Once email list reaches 500+ subscribers:
```
פורמט: ספונסר בודד לניוזלטר שבועי
מחיר: ₪299 לשליחה
תוכן: 3 שורות טקסט + תמונה + קישור
אוכלוסייה: מטיילים ישראלים בצפון
```

---

## When to Activate Each Stream

```
חודש 1-3:  הכל חינם — בנה תוכן ותנועה
חודש 4:    הצע "claim your listing" לבעלי עסקים
חודש 5-6:  הפעל רישומים מפוצ׳רים (Featured) בחינם → אחר כך בתשלום
חודש 7:    הוסף חבילות מדורגות
חודש 8:    הפעל פרסומות באנר
חודש 10+:  עמלות הזמנות מלאות
```

## Constraints

### MUST DO
- Always reach a minimum threshold of listings/traffic before monetizing
- Be transparent with businesses about what they're paying for
- Keep the free tier genuinely useful (don't cripple it)
- Price in ₪ for Israeli market

### MUST NOT DO
- Activate paid features before the site has enough content
- Run too many ads — hurts trust and SEO
- Charge businesses before the site delivers real traffic value
- Hide pricing — Israeli businesses expect clear, upfront pricing
