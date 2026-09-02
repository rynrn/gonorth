# GoNorth place analytics contract

The internal WordPress `gd_place` post ID is the primary reporting key. Event
names never contain a business name, and no event sends user-entered text or
personally identifiable information.

## Events

| Event | Trigger | Extra parameters |
| --- | --- | --- |
| `place_view` | Once after a real place detail page is ready | Common place parameters |
| `place_cta_click` | Phone, official site, Waze, Google Maps or share click | `action`, `cta_location` |
| `place_plan_around_click` | A recommendation is selected in “תכננו סביב המקום” | `action=plan_around`, `target_place_id` |
| `place_faq_open` | A visitor explicitly expands an FAQ item | `faq_id`, `faq_topic` |
| `place_related_click` | A nearby/related place is selected | `target_place_id`, `target_place_name`, `related_section` |
| `place_impression` | At least 35% of a place card is visible; once per card instance | `discovery_surface`, `position` |
| `place_card_click` | A discovery card link is selected | `discovery_surface`, `position`, `click_target` |

Common parameters are `place_id`, `place_slug`, `place_name`,
`place_category`, `place_city`, optional `place_region`, and `page_location`.
Normalized CTA actions are `phone`, `official_website`, `waze`, `google_maps`,
`share`, and `whatsapp` if a direct WhatsApp contact is added later. Normalized
discovery surfaces are `category`, `search`, `homepage`, `nearby`, `related`,
`plan_around`, and `editorial`.

The helper is available as `window.GoNorthAnalytics`. Its public methods are
`track`, `trackOnce`, `trackPlaceView`, and `trackPlaceAction`. The implementation
uses one delegated click listener and one impression observer, so rerenders do
not register duplicate listeners and scrolling a card away and back does not
repeat its impression.

## GA4 configuration

Create event-scoped custom dimensions for:

- `place_id` (primary business filter)
- `place_name`
- `place_category`
- `place_city`
- `action`
- `cta_location`
- `discovery_surface`
- `faq_topic`
- `target_place_id`
- `related_section`

Collect `place_slug`, `place_region`, `position`, `click_target`, `faq_id`, and
`section_name`, but register them only if an active report needs them. This
preserves GA4 custom-definition capacity.

Mark meaningful actions as key events by creating derived GA4 events for
`place_cta_click` where `action` is `phone`, `whatsapp`, `official_website`,
`waze`, or `google_maps`, plus `place_plan_around_click`. Do not mark FAQ,
impression, share, or ordinary related clicks as key events.

## Per-place report and KPIs

Build a GA4 Exploration filtered by `place_id`, with the chosen date range.
Use event name and `action` as rows; add `discovery_surface`, session
source/medium, device category, or default channel group as breakdowns.

Report:

- Reach: users, sessions containing `place_view`, views, impressions, and users exposed.
- Discovery: card clicks, impressions, and profile-entry CTR (`card clicks / impressions`).
- High intent: phone, WhatsApp, official-site, Waze and Google Maps users/sessions, plus plan-around users/sessions.
- Assistance: FAQ users/opens, shares and related-place clicks.
- Meaningful Actions: distinct users or sessions with at least one high-intent CTA or plan-around action. Keep every underlying action visible separately.

Rates use users or sessions, not raw repeated clicks:

- high-intent action rate = users/sessions with a meaningful action / users/sessions with a place view
- navigation rate = users/sessions with Waze or Google Maps / users/sessions with a place view
- contact rate = users/sessions with phone or WhatsApp / users/sessions with a place view
- official-site outbound rate = users/sessions with official-site click / users/sessions with a place view
- plan-around rate = users/sessions with plan-around click / users/sessions with a place view
- FAQ usage rate = users/sessions with FAQ open / users/sessions with a place view

Do not attach money or arbitrary weights in v1. The stable contract can be
exported to BigQuery or another reporting layer later without changing frontend
event names.

## Validation

Append `?gn_analytics_debug=1` to a page URL and open the browser console. Every
event is logged as `[GoNorth analytics]` with its final payload while still being
sent to the configured GA4 tag. Validate a detail page and an archive page in
GA4 DebugView; confirm one `place_view`, one event per explicit click, deferred
below-the-fold impressions, stable IDs, and unaffected navigation.
