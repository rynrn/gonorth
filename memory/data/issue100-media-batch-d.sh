#!/usr/bin/env bash
set -euo pipefail
WP=(wp --path=/var/www/gonorth --allow-root)
d=/var/www/gonorth/wp-content/uploads/gonorth-listing-illustrations; install -d -m 0755 "$d"
cp /tmp/issue100-d-google-photos/yarden-1.jpg "$d/issue100-yarden-estate-google.jpg"
cp /tmp/issue100-d-google-photos/norah-3.jpg "$d/issue100-norah-cafe-google.jpg"
a=$("${WP[@]}" media import "$d/issue100-yarden-estate-google.jpg" --skip-copy --post_id=484 --title='מלון אחוזת הירדן — בריכת המלון' --porcelain)
b=$("${WP[@]}" media import "$d/issue100-norah-cafe-google.jpg" --skip-copy --post_id=485 --title='Norah Café — קפה נורה ביסוד המעלה' --porcelain)
"${WP[@]}" post meta update "$a" _wp_attachment_image_alt 'בריכת מלון אחוזת הירדן ביסוד המעלה'
"${WP[@]}" post meta update "$b" _wp_attachment_image_alt 'חזית Norah Café קפה נורה ביסוד המעלה'
for id in "$a" "$b";do "${WP[@]}" post meta update "$id" _gonorth_image_role content;"${WP[@]}" post meta update "$id" _gonorth_google_photo_fetched_at '2026-10-01T21:10:00+03:00';done
"${WP[@]}" post meta update "$a" _gonorth_google_photo_attribution_name "Achuzat Ha'yarden Hotel"
"${WP[@]}" post meta update "$a" _gonorth_google_photo_attribution_uri 'https://maps.google.com/maps/contrib/109251267170194516683'
"${WP[@]}" post meta update "$a" _gonorth_google_photo_name 'places/ChIJ6TyNuOMfHBURJIWElcvImbQ/photos/Aa-ngMbxyK93yycr9MRmRmzwat0WKldFbdE5ApFnaK3vh9kgz7frIcRF8rdmwOif08dwZme6WmhGMz_Z_zdWJi-q2iRUTnbgLIjMaoYEkiyQai0fSz_VZcI8oMq-x_CafAGbL38EWvDmZSssXyddCzNR9Bc62SjSaN-jZUedImJj20m9I-xZQ2E8-Ykz2mwQatGgkpN7-P6QQaATBuZYM7BqZSr9pHtK_7bhnM6oXySMcXicBA3Oz4S98N65sTs1JQ6PXdQ2yM7KU96BgiaWw7ixo8VSXF6Vl0QVB7LBI5OIgjAOsnfsf8bN7EEGRPonGn04fBrvQ1M3HxK-tMDjvW2PrjQ0xSnu5csdNZN9sgZ9ezXp45fIqZm8ZQcWOZmzudXb_1y6Nr8FcBhvR9My0tzKzH-kTcINE-jRkIWk69a1abKVwFtKpzOs_thFT8YPWBWZ'
"${WP[@]}" post meta update "$b" _gonorth_google_photo_attribution_name 'Norah Café - קפה נורה'
"${WP[@]}" post meta update "$b" _gonorth_google_photo_attribution_uri 'https://maps.google.com/maps/contrib/110792974914190300970'
"${WP[@]}" post meta update "$b" _gonorth_google_photo_name 'places/ChIJA8-i1YGhHhURZDxFMbqONQs/photos/Aa-ngMZ9xmxDiHC3OpRri0yXKXT53EMoRAB7goPRzvRjBhwy2Qun_Yubu-791o-BrTdCHUtuNZDuEc8tjXPn1GCRaQIdk05fqOug72f7tpBZwIoPN870fwWvi3tWLkRWHyCShq8SrtfAjodkIf5Yoc5c-FDYV7xgbcaq1TEjoIMQ9wlZJdXu2-kQSt4YBrBPiWEqZ9vI6dI9-IaRAKjfYUVHbWhj8hDnmXYKXQTXWeqcdvQq3oO5lHj_ZYhev22LjwEM-6gkhjok7UOpskVCyI1f50d3YKz760vtYJsmjo_dcIhss182wf76wgjnGbx2j5KrOQGtkvU0Gjc3gqja7a7qIYGmE_wtegLVfR37PA6AOHw3XagUJ12il57JGp-DMRlzqP6Lt7_PtH2lrVzotcXvkfp3iKSCvXc'
"${WP[@]}" post meta update 484 _thumbnail_id "$a";"${WP[@]}" post meta update 485 _thumbnail_id "$b"
printf '{"hotel_attachment":%s,"norah_attachment":%s}\n' "$a" "$b"

