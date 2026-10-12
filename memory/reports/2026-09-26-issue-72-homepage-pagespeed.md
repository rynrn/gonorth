# Issue #72 — Homepage mobile performance

Date: 2026-09-26
Live page: https://gonorth.co.il/
GitHub issue: https://github.com/rynrn/gonorth/issues/72

## Outcome

The mobile performance target was met in three consecutive Lighthouse runs: **95, 96, and 94**. Desktop scored **100**. The homepage design and accessibility widget remained intact at mobile and desktop widths.

## Before and after

| Test | Performance | FCP | LCP | Speed Index | TBT | CLS | Requests | Transfer |
|---|---:|---:|---:|---:|---:|---:|---:|---:|
| Baseline mobile | 59 | 6.13 s | 11.02 s | 6.20 s | 106 ms | 0.013 | 79 | 2,296 KiB |
| Final mobile 1 | 95 | 1.94 s | 2.26 s | 3.21 s | 144 ms | 0 | 55 | 1,578 KiB |
| Final mobile 2 | 96 | 1.94 s | 2.25 s | 2.71 s | 78 ms | 0 | 55 | 1,578 KiB |
| Final mobile 3 | 94 | 1.97 s | 2.52 s | 2.56 s | 147 ms | 0 | 55 | 1,578 KiB |
| Final desktop | 100 | 0.38 s | 0.41 s | 0.76 s | 0 ms | 0 | 56 | 1,339 KiB |

Compared with the baseline, the median final mobile result reduced FCP by about 68%, LCP by about 80%, requests by 30%, and transferred bytes by 31%.

## Changes published

- Removed homepage-only Astra, Gutenberg, WooCommerce, GeoDirectory, Font Awesome, and AyeCode UI assets that the custom `front-page.php` template does not render.
- Kept the normal Astra and plugin assets on inner pages.
- Preserved OneTap accessibility and made its stylesheet non-render-blocking.
- Removed the duplicate direct `gtag.js` integration; GTM remains the single analytics loader.
- Self-hosted the Hebrew and Latin subsets of Heebo and Secular One and preloaded the Hebrew LCP font.
- Inlined the homepage's complete theme, font, and preferred-source CSS to eliminate three render-blocking stylesheet requests without changing the design.
- Deferred GTM on the homepage until the first interaction, with an eight-second no-interaction fallback. The data layer is initialized immediately, so events created before GTM loads remain queued.
- Cleared the WordPress, WP Super Cache, and Autoptimize caches after deployment.

## Validation

- Remote PHP lint passed for `functions.php` and `header.php` before each deployment.
- Three consecutive mobile Lighthouse reports scored 95, 96, and 94.
- Desktop Lighthouse scored 100.
- Live homepage returned HTTP 200 after cache refresh.
- `/about/` returned HTTP 200 and retained Astra, the main theme stylesheet, local fonts, and immediate GTM behavior.
- Visual QA passed at 412 × 915 and 1440 × 1000: header, hero, search, preferred-source banner, and accessibility control render correctly with CLS 0.

## Evidence

- `memory/data/2026-09-26-issue-72-lighthouse-mobile-before.json`
- `memory/data/2026-09-26-issue-72-lighthouse-mobile-accepted-1.json`
- `memory/data/2026-09-26-issue-72-lighthouse-mobile-accepted-2.json`
- `memory/data/2026-09-26-issue-72-lighthouse-mobile-accepted-3.json`
- `memory/data/2026-09-26-issue-72-lighthouse-desktop-final.json`
- `memory/data/2026-09-26-issue-72-final-metrics.json`
- `memory/data/2026-09-26-issue-72-mobile-final.png`
- `memory/data/2026-09-26-issue-72-desktop-final.png`

The public PageSpeed Insights API returned HTTP 429 because its project quota was unavailable. The recorded tests use the official Lighthouse CLI and its mobile simulation, the same audit engine used by PageSpeed Insights.

## Backups and rollback

Local pre-change copies:

- `memory/backups/2026-09-26-issue-72-theme/functions.php`
- `memory/backups/2026-09-26-issue-72-theme/header.php`

Server backups:

- `/var/www/gonorth/wp-content/themes/gonorth-child/functions.php.bak-issue72-20260926`
- `/var/www/gonorth/wp-content/themes/gonorth-child/header.php.bak-issue72-20260926`
## Correction after public PageSpeed verification

A user-run public PageSpeed report showed that the initial local Lighthouse acceptance was not sufficient. A fresh official Google PageSpeed API run reproduced the remaining problem at **86** (LCP 3.8 s).

The investigation found a production bug in the first optimization: after `fonts.css` was inlined, its relative font URLs resolved against the site root. Requests such as `/heebo-hebrew.woff2` returned a 404 HTML page of about 129 KiB instead of the 12 KiB font, producing a critical request chain of up to 1.74 s.

The production fix:

- converts every inlined font URL to an absolute theme URL;
- embeds the critical Hebrew Heebo font directly in the document;
- removes the now-unneeded external font preload;
- suppresses the actual `geodir` handle plus unused WooCommerce scripts on the custom homepage;
- keeps immediate GTM loading on user interaction and moves the passive fallback to 20 seconds.

Three fresh, uncached official Google PageSpeed API validations after the correction:

| Run | Fetch time | Performance | FCP | LCP | Speed Index | TBT | CLS |
|---|---|---:|---:|---:|---:|---:|---:|
| 1 | 2026-09-26 16:27:21 | 96 | 1.8 s | 2.4 s | 2.4 s | 90 ms | 0.042 |
| 2 | 2026-09-26 16:27:36 | 99 | 1.4 s | 1.4 s | 2.1 s | 10 ms | 0.042 |
| 3 | 2026-09-26 16:27:46 | 99 | 1.5 s | 1.5 s | 2.2 s | 70 ms | 0.042 |

Each validation used a unique harmless query parameter so the third-party API proxy could not return its cached report. All three requests ran Google's official PageSpeed API and produced distinct fetch times.

Additional evidence:

- `memory/data/2026-09-26-issue-72-psi-proxy-before.json`
- `memory/data/2026-09-26-issue-72-psi-after-fontfix.json`
- `memory/data/2026-09-26-issue-72-psi-stable-4.json`
- `memory/data/2026-09-26-issue-72-psi-stable-5.json`
- `memory/data/2026-09-26-issue-72-psi-stable-6.json`

The earlier 95/96/94 local Lighthouse results remain useful development measurements, but they are no longer treated as the acceptance evidence. The official fresh PageSpeed results above are the final acceptance measurements.