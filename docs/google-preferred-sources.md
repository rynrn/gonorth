# Google Preferred Sources

GoNorth renders one reusable Preferred Sources CTA in the footer area of the homepage, blog index, WordPress posts, and GeoDirectory place pages. Editors can use `[gonorth_preferred_source placement="content"]` on another singular page; the renderer prevents duplicate components.

The implementation follows Google's advanced JavaScript flow documented at:
https://developers.google.com/search/docs/appearance/preferred-sources

- Local code loads first and never blocks rendering.
- Google's `publisher.js` loads asynchronously, once, in manual-control mode, and only after the existing `gn_cookie_ok` consent flag is present.
- The SDK is initialized with `theme: 'light'` and `lang: 'he'`.
- The visible button always points to the official deeplink `https://www.google.com/preferences/source?q=gonorth.co.il`, so blocking or failing the SDK leaves a working fallback.
- The shared analytics helper sends `preferred_source_click` once per explicit click after consent. Parameters are `placement`, `page_type`, `page_location`, and `place_id` on place pages.
- No completion event is emitted because Google's documented SDK does not expose a reliable completion callback.

The feature lives in `gonorth-place-data`: `includes/class-gonorth-preferred-source.php`, `assets/preferred-source.js`, the shared `assets/place-analytics.js`, and the CTA styles in `assets/place-data.css`.
