# GoNorth place data contract

`gonorth-place-data` makes the GeoDirectory `gd_place` detail table the source
of truth for practical place information. Editorial content remains in the
WordPress post and Google-derived facts remain in explicitly named Google
fields.

## Canonical fields

| Meaning | GeoDirectory field | Format | Unknown behavior |
|---|---|---|---|
| Address | `street` | text | omit |
| City | `city` | text | omit |
| Coordinates | `latitude`, `longitude` | validated decimal | omit location features |
| Phone | `phone` | text | omit action |
| Official website | `website` | URL | omit action |
| Opening hours | `business_hours` | GeoDirectory per-day schema | omit hours/status |
| Price type | `price_type` | `free`, `paid`, `unknown` | do not display `unknown` |
| Open Saturday | `open_saturday` | `yes`, `no`, `unknown` | do not display `unknown` |
| Google Place ID | `google_place_id` | text | no Google sync |
| Google rating | `google_rating` | decimal 0–5 | omit |
| Google review count | `google_review_count` | integer | omit count |
| Google freshness | `google_checked_at` | ISO-8601 UTC | omit freshness |
| Practical freshness | `practical_checked_at` | ISO-8601 UTC | omit freshness |

Freshness timestamps must only be written after the corresponding source block
was actually checked. A migration or a WordPress post edit is not a factual
verification.

## Legacy migration

The CLI migration copies the existing `geodir_post_*` metadata to canonical
GeoDirectory columns without deleting or overwriting the legacy values. They
remain a rollback-only fallback while the theme and import workflows move to
`gonorth_get_place_data()` and `GoNorth_Place_Data::update()`.

```bash
wp gonorth place-data install_schema --path=/var/www/gonorth --allow-root
wp gonorth place-data migrate --post_id=86 --dry-run --path=/var/www/gonorth --allow-root
wp gonorth place-data migrate --post_id=86 --path=/var/www/gonorth --allow-root
wp gonorth place-data audit --path=/var/www/gonorth --allow-root
```

## Sync contract

Future Google sync code should call `gonorth_store_google_place_data()` after a
successful response. Internal imports should call `GoNorth_Place_Data::update()`
and set the verification flags only when the source was checked during that run.

## Place attributes

The `gn_place_attribute` taxonomy is the indexed positive-state representation
for the definitions owned by `GoNorth_Place_Attributes::definitions()`. Explicit
negative states are stored in `_gn_place_attribute_false`; absence from both is
unknown. The setter always removes the opposite state, so a place cannot be both
positive and negative for the same attribute.

`free` and `open_saturday` are query-index mirrors of the canonical `price_type`
and `open_saturday` fields. Updates through either public data API are synchronized.

```bash
wp gonorth attributes install --path=/var/www/gonorth --allow-root
wp gonorth attributes definitions --path=/var/www/gonorth --allow-root
wp gonorth attributes set 86 accessible yes --path=/var/www/gonorth --allow-root
wp gonorth attributes query accessible,children --path=/var/www/gonorth --allow-root
wp gonorth attributes audit --path=/var/www/gonorth --allow-root
```

Theme and search code can use:

```php
$query = gonorth_query_places_by_attributes( array( 'accessible', 'children' ) );
```

## Nearby places

`GoNorth_Nearby_Places::get_nearby_places()` derives recommendations directly
from the canonical `latitude` and `longitude` columns. It uses a prepared
Haversine query, excludes the current place, and limits results to 50 km by
default. Selection is distance-first with a small scoring penalty for repeated
categories, so a genuinely nearby alternative type is preferred without
pulling in a distant result merely to create variety.

Results are cached for 12 hours. The cache key includes a shared version that
is incremented whenever a `gd_place` post or its canonical place data changes,
so editors do not maintain nearby relationships manually.

## SEO, FAQ and freshness

`GoNorth_Place_SEO` derives visible FAQ answers only from explicit canonical
values or tri-state attributes. Unknown fields produce no question. The same
question set is emitted as a separate `FAQPage` graph, while GeoDirectory's
existing place graph is enhanced through `geodir_details_schema` rather than
duplicated.

Every FAQ item also owns a stable normalized `id` and `topic`. New FAQ filters
must supply both values and derive the answer from a verified canonical field.
This lets analytics measure an explicit open without sending the full Hebrew
question text or creating a second FAQ storage/rendering system.

## Place analytics

`GoNorth_Place_Analytics` is the single frontend source of truth for place
events. It exposes `window.GoNorthAnalytics`, sends stable event names with the
internal WordPress place ID, and fails silently when GA4 is unavailable.
Place-card impressions use `IntersectionObserver`, fire once per card instance,
and are never inferred from server rendering alone. The complete event and
reporting contract lives in `docs/place-analytics.md`.

Existing Yoast descriptions and canonical URLs are preserved. A concise Hebrew
description is generated only when Yoast would otherwise output none.

Freshness uses the newest verified `practical_checked_at` or
`google_checked_at` value. Until one exists, the UI says only that the page was
updated, based on `post_modified`; it never claims that the factual information
was checked.

## Contextual trip planning

`GoNorth_Trip_Planner::rules()` defines independently adjustable contexts for
coffee, food, activities, nature and lodging. Every rule owns its category
slugs, local radius and concise result limit. Rendering is independent of those
rules, so contexts can change without rewriting the page component.

The planner reuses the nearby service's prepared Haversine query and shared
versioned transient cache. Candidates come only from published GoNorth places,
the current place and previously used IDs are excluded, and empty groups are
omitted. On place pages the contextual planner replaces the older generic
nearby grid to avoid repetitive recommendations.
