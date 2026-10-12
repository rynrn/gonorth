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

Google ratings are third-party data. When the source terms allow display, the
rating, review count and checked date are shown together with explicit Google
attribution. They are never emitted as GoNorth `AggregateRating` structured
data; a future first-party review system requires a separate policy review.

## External verification ledger

The local [`place-facts`](../../../place-facts/README.md) SQLite database is the
source of truth for verification history, detected differences and review
status. It does not render the live site directly. GeoDirectory receives only
reviewed values through the canonical sync command. Closure findings are kept
out of automatic field sync and require the SEO flow that keeps the URL live.

### Locality provenance

Verified locality work stores its audit trail in WordPress post metadata while
the locality itself remains canonical in the GeoDirectory `city` column:

| Meta key | Meaning |
|---|---|
| `_gonorth_locality_source_url` | Source used to verify the locality |
| `_gonorth_locality_verification_method` | Verification method or `justified_missing` |
| `_gonorth_locality_evidence` | Concise evidence supporting the decision |
| `_gonorth_locality_verified_at` | ISO-8601 UTC verification timestamp |

When a place is not inside a settlement, `city` and `addressLocality` are
omitted and the reason is recorded with `justified_missing`. A broad geographic
region must never be stored as a city; it belongs in the `gn_region` taxonomy.

## Legacy migration

The CLI migration copies the existing `geodir_post_*` metadata to canonical
GeoDirectory columns without deleting or overwriting the legacy values. They
remain a rollback-only fallback while the theme and import workflows move to
`gonorth_get_place_data()` and `GoNorth_Place_Data::update()`.

An intentionally omitted canonical locality must not retain a legacy
`geodir_post_city` value, because the compatibility layer would otherwise treat
that legacy value as a rollback fallback.

```bash
wp gonorth place-data install_schema --path=/var/www/gonorth --allow-root
wp gonorth place-data migrate --post_id=86 --dry-run --path=/var/www/gonorth --allow-root
wp gonorth place-data migrate --post_id=86 --path=/var/www/gonorth --allow-root
wp gonorth place-data audit --path=/var/www/gonorth --allow-root
```

## Sync contract

Google results enter SQLite through submit/review. Only approved changes are
exported to a versioned WordPress package. The package carries expected old
values, so a stale live value stops the write and forces a new reconciliation.
Apply always writes a private JSON backup first; rollback restores the exact
canonical record, including nullable Google fields and freshness timestamps.

```bash
wp gonorth place-data export --path=/var/www/gonorth --allow-root > wordpress-export.json
py -3.14 place-facts/scripts/place-facts.py reconcile wordpress-export.json --output reconciliation.json
py -3.14 place-facts/scripts/place-facts.py export-wordpress-sync --output sync-package.json
wp gonorth place-data sync sync-package.json --path=/var/www/gonorth --allow-root
wp gonorth place-data sync sync-package.json --apply --backup-dir=/private/backups --path=/var/www/gonorth --allow-root
py -3.14 place-facts/scripts/place-facts.py record-wordpress-sync apply-receipt.json
wp gonorth place-data rollback /private/backups/place-sync-YYYYMMDD-HHMMSS.json --path=/var/www/gonorth --allow-root
```

`wp gonorth place-data google_sync` remains dry-run by default and requires a
server-authorized key for Places API (New). `--format=json` emits reviewable
candidate bundles including the Place ID without writing data. It uses Text Search (New) and Place
Details (New) with explicit field masks. It discovers a missing Place ID only
when the name and coordinates pass conservative confidence thresholds, rejects
incomplete rating responses, and writes the ID, rating, review count and
freshness timestamp as one unit with `--apply`. Complete external ratings may
be shown visibly with Google attribution and a checked date, but are not emitted
as `AggregateRating`.

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

## Attribute landing pages

`GoNorth_Attribute_Landing_Pages` owns SEO landing pages derived from positive
tri-state attributes. A profile is allowed only when at least 12 published
places have an explicit `yes` value and a non-empty `practical_checked_at`.
Unknown and explicit-negative states are never queried or displayed.

The first eligible profile is `open_saturday`, rendered through
`[gonorth_attribute_landing attribute="open_saturday"]`. Its visible list and
Yoast `ItemList` graph come from the same request-local cached result set. If
verified inventory later falls below the floor, the page becomes `noindex` and
the list is withheld instead of degrading into a thin page.

The `free`, `children` and `accessible` profiles remain intentionally absent
until their verified inventories pass the same floor. A page is associated
with a profile through the protected `_gonorth_attribute_landing` post meta.

## Evergreen seasonal hubs

`GoNorth_Seasonal_Hubs` associates a year-free WordPress page with a code-owned
SEO profile through `_gonorth_seasonal_hub`. The event year and updated date
live in page content and metadata, never in the URL. Promotion windows use
`_gonorth_seasonal_promo_start` and `_gonorth_seasonal_promo_end` in `Y-m-d`
format.

Only a published seasonal page inside its current promotion window can create
an inbound banner. Eligible surfaces are the homepage, category and region
archives, the blog index, and explicitly opted-in posts marked with
`_gonorth_seasonal_promote=1`. When the window ends, the banner disappears
without deleting or redirecting the evergreen page.

## Contextual internal links

`GoNorth_Internal_Links` connects four public content layers: category hubs,
region hubs, editorial guides and place pages. Category/region links are emitted
only when at least one published place exists at that intersection. Guide
classification uses the published title and stable slug; it never infers place
attributes or promotes dated and explicitly excluded guides.

Each eligible guide links to its matching canonical hubs and up to four
published places. Place pages link back to their assigned category and region
hubs plus up to three context-matched guides. Category and region archives link
to one another and to a short ranked guide list. Existing links in editorial
content are not duplicated, and private drafts are never eligible targets.

## Google Preferred Sources

`GoNorth_Preferred_Source` renders one reusable Hebrew CTA in the footer area of
the homepage, blog index, articles and place pages. The shortcode
`[gonorth_preferred_source placement="content"]` supports an editor-selected
placement without creating a duplicate automatic component.

The button always has Google's official domain deeplink as a no-SDK fallback.
Only after explicit opt-in is stored as `gn_analytics_consent_v2=1`, the local integration
loads Google's `publisher.js` asynchronously, once, in manual-control mode and
initializes the official flow in Hebrew and invokes the documented `addPreferredSource()` flow. A blocked SDK never blocks
navigation or page rendering. The shared analytics helper emits
`preferred_source_click` with `placement`, `page_type`, `page_location` and an
optional `place_id`, and suppresses the event before consent. No completion
event is emitted because the documented API exposes no reliable success
callback.

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

Published place titles follow the local-search formula
`place name | verified locality or region | GoNorth`. If the location already
appears in the place name it is not repeated. A missing city falls back to the
first `gn_region` term, then to the honest site-wide label `צפון ישראל` when no
region exists. The formatter preserves location and brand while shortening the
place-name portion so the complete title stays within 60 characters.

The featured attachment is also the single canonical image for the HTML page,
Open Graph, Twitter and structured data. The GeoDirectory place node references
Yoast's `#primaryimage` node instead of embedding its own ImageObject. That node
owns the full URL, content URL, dimensions and any stored caption; attachment
ALT text is represented by the schema.org `name` property because ImageObject
has no dedicated `alt` property. Website screenshots and WordPress mShots must
never be used as the representative place image.

Every image attachment has an explicit `_gonorth_image_role`: `content` or
`decorative`. Content images require a non-empty ALT value. Decorative assets
keep an empty ALT intentionally, which lets audits distinguish them from
missing editorial work. When an image is assigned as a featured image,
`GoNorth_Media_Alt` marks it as content and fills a missing ALT from the verified
post title plus place locality. Editors can override the role in Media Library;
choosing decorative deliberately clears ALT.

## Image delivery variants

JPEG and PNG attachments remain the canonical media files. GoNorth generates a
smaller sibling named `{original filename}.webp` for each useful attachment
file and registered derivative. A `{filename}.webp.skip` marker means the
modern encoding was tested but was not smaller; `{filename}.webp.error` is an
auditable generation failure and must never be treated as a successful result.

`GoNorth_Nextgen_Images` owns this lifecycle. It generates sidecars during
attachment metadata creation, deletes them with the attachment, and exposes
them through a semantic `<picture>` wrapper around `wp_get_attachment_image()`.
The original IMG remains the fallback and preserves ALT, dimensions, loading
and fetch-priority attributes. WebP URLs carry the sidecar modification time as
a version, so regenerating a file invalidates the browser cache.

The longest WebP display edge and future WordPress big-image threshold are
1920 px. Generated WebP display assets must remain below 1 MiB; the audit in
`tests/nextgen-images-audit.php` enforces the limit across the uploads library.

## Primary-page social images

The homepage, places archive, seven canonical `gd_placecategory` archives and
three `gn_region` archives use fixed 1200×630 JPEG assets from the child
theme's `assets/social` directory. `gonorth_social_image_profile()` is the
single context-to-asset map. It feeds Yoast's Open Graph image container and
Twitter presenter, with the same HTTPS URL, Hebrew page title and Hebrew page
description on both platforms. Social bitmaps contain no baked-in text, so RTL
copy remains accessible and adapts to each platform's card layout.

## Site organization graph

The stable site entity is `https://gonorth.co.il/#organization`, with the
primary Hebrew name `כיוון צפון` and alternate name `GoNorth`.
`GoNorth_Organization_Schema` is the only integration that adds or enriches
this node in Yoast's graph. The WebSite points to it through `publisher`; the
About page is typed as `AboutPage` and points to it through `about` and
`mainEntity`. Contact data uses the public contact-page URL and never exposes a
private WordPress administrator address. Only verified, site-owned identity
URLs may be added to `sameAs`.

`כיוון צפון` is also the only primary public brand in document titles, Open
Graph/Twitter titles, `og:site_name`, visible templates, footer and editorial
display names. `GoNorth` is reserved for the Organization `alternateName`, an
explicit parenthetical introduction where useful, and technical identifiers
such as the domain, plugin name, PHP/JavaScript namespaces and file paths.

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
