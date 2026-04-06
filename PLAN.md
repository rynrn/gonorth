# gonorth.co.il — Website Plan

## Overview
A Hebrew-only tourism directory for northern Israel, targeting Israeli families, locals, and businesses.
Visitors can discover attractions, accommodation, food, and tours.
Businesses can self-register their listings.
Free to start, with monetization options built-in for later.

---

## Architecture

```
WordPress (CMS)
├── Directory & Listings       → GeoDirectory plugin
├── Booking / Reservations     → WooCommerce + Bookly
├── Map Integration            → GeoDirectory + Google Maps API
├── Blog / Articles            → WordPress native posts
├── Business self-registration → GeoDirectory front-end submission
└── Design / Layout            → Elementor + RTL Hebrew theme
```

---

## Phase 1 — Foundation

| Task | Tool / Plugin |
|------|---------------|
| WordPress install + RTL setup | WordPress core |
| Theme selection (nature/outdoors feel) | Neve or Astra (lightweight, RTL-ready, Elementor-compatible) |
| Page builder | Elementor Free |
| SEO foundation | Yoast SEO (supports Hebrew) |
| Security & backups | Wordfence + UpdraftPlus |

---

## Phase 2 — Directory & Map

| Task | Tool / Plugin |
|------|---------------|
| Listing categories (attractions, hotels, restaurants, tours) | GeoDirectory (free) |
| Interactive map with pins | GeoDirectory + Google Maps API |
| Business self-submission form | GeoDirectory front-end forms |
| Admin moderation / approval | GeoDirectory built-in |
| Ratings & reviews | GeoDirectory add-on |

---

## Phase 3 — Bookings

| Task | Tool / Plugin |
|------|---------------|
| Booking system (tours, activities) | Bookly or Amelia |
| Payment gateway (Israeli market) | WooCommerce + Tranzila or PayPlus |
| Hotel/accommodation bookings | MotoPress Hotel Booking (free tier) |

---

## Phase 4 — Content & Blog

- WordPress native Posts for articles, travel guides, seasonal content
- Categories: טיולים, אוכל, לינה, אטרקציות, מדריכים
- Homepage featuring curated picks + featured listings
- Elementor templates for consistent article layout

---

## Phase 5 — Future Monetization

- Featured listings — premium placement in search results (GeoDirectory supports this)
- Paid listing packages — businesses pay to list (GeoDirectory + WooCommerce)
- Banner ads — Advanced Ads plugin
- Commission on bookings — WooCommerce commissions plugin

---

## Open Questions

- [ ] Hosting — Cloudways or SiteGround recommended for WordPress in Israel
- [ ] Google Maps API key needed for map functionality
- [ ] Is gonorth.co.il already pointed to the WordPress host?
- [ ] Seed content — any listings ready, or all from business self-registration?
