# Issue #100 — Batch A research and validation

Date: 2026-10-01
Status: ready for validated pending-post creation

## Duplicate decisions

- `שמורת טבע החולה` already exists as WordPress listing ID 54; do not create a duplicate.
- `מפל הבניאס והשביל התלוי` and `הגשר התלוי` are features of existing listing ID 191, `שמורת טבע הבניאס`; enrich that URL instead of creating competing pages.
- No exact-title match was found in the 2026-10-01 listing snapshot for the two Batch A candidates.

## המקום של ארנון

- Category: `ochel`
- Official source: https://arnons-place.co.il/
- Phone: 050-277-0613
- Address: רחוב השזיף פינת המגדל, יסוד המעלה
- Google Place ID: ChIJd1H9FQChHhURr33cM2c8b38
- Coordinates: 33.0596945, 35.6125568
- Google rating snapshot: 4.9 from 41 reviews
- Hours conflict: the official site and Google currently disagree. Do not publish hours until manually reconciled.

## רנטו בר יין ומעדנייה

- Category: `ochel`
- Official source: https://renatodeli.co.il/terms/
- Phone: 054-670-0937
- Address: כיכר המושבה, המייסדים 130, יסוד המעלה
- Google Place ID: ChIJ0853nvohHBURd5AEOnjKy_A
- Coordinates: 33.0573462, 35.6090588
- Google rating snapshot: 4.7 from 72 reviews
- Hours are available from Google but were not found on the official site; omit them pending review.

## Rollback

The new listings must remain `pending`. Rollback is to trash the newly created post IDs and their newly uploaded media IDs, then re-run the canonical export. No existing listing is modified in this batch.


## Execution receipt

- Validator: PASS — 2 records, 0 errors, 0 warnings.
- WordPress pending post 470: המקום של ארנון.
- WordPress pending post 471: רנטו בר יין ומעדנייה.
- Media attachment 472 is the featured illustration for post 470.
- Media attachment 473 is the featured illustration for post 471.
- Canonical verification: category, Galilee region, coordinates, phone, website, Google Place ID, rating and review count are present.
- Business hours are intentionally empty pending reconciliation.
- Public verification: posts 470 and 471 return HTTP 404 while pending; both media files return HTTP 200.
- Rollback: delete posts 470, 471, 472 and 473 only after confirming the IDs still belong to this batch.

