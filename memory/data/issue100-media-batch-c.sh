#!/usr/bin/env bash
set -euo pipefail
WP=(wp --path=/var/www/gonorth --allow-root)
dir=/var/www/gonorth/wp-content/uploads/gonorth-listing-illustrations
install -d -m 0755 "$dir"
cp /tmp/issue100-c-google-photos/shwartz-1.jpg "$dir/issue100-shwartz-google.jpg"
cp /tmp/issue100-c-google-photos/petipeti-1.jpg "$dir/issue100-petipeti-google.jpg"
a=$("${WP[@]}" media import "$dir/issue100-shwartz-google.jpg" --skip-copy --post_id=480 --title='שוורצע — משק שוורץ בשדה אליעזר' --porcelain)
b=$("${WP[@]}" media import "$dir/issue100-petipeti-google.jpg" --skip-copy --post_id=481 --title='Peti Peti — מסעדה אסייתית בשדה אליעזר' --porcelain)
"${WP[@]}" post meta update "$a" _wp_attachment_image_alt 'הכניסה לחנות משק שוורץ בשדה אליעזר'
"${WP[@]}" post meta update "$b" _wp_attachment_image_alt 'חלל מסעדת Peti Peti בשדה אליעזר'
for id in "$a" "$b"; do "${WP[@]}" post meta update "$id" _gonorth_image_role content; "${WP[@]}" post meta update "$id" _gonorth_google_photo_fetched_at '2026-10-01T20:25:00+03:00'; done
"${WP[@]}" post meta update "$a" _gonorth_google_photo_attribution_name 'Haya Live Pilates'
"${WP[@]}" post meta update "$a" _gonorth_google_photo_attribution_uri 'https://maps.google.com/maps/contrib/115637712204428161278'
"${WP[@]}" post meta update "$a" _gonorth_google_photo_name 'places/ChIJM5EjMbQhHBURl3TFbMs_-PE/photos/Aa-ngMYvHg1m4J3KeHki90ciG5fkkLQcD0IKtgj8h6C8CqczuYyFINwkvoH7cS7UIVzaxbg5mwOYU-3AnxT-x8x-auP282_BVY8KZF_Ye6wXwCDY3445GJlZaufcmzFYpDvKDFe6t3CYhGmz_kO0Z-UPXfHk2sTRNwBd2A6ImC52mVQUd69AmmRB8axbaZAmUUcZZ79IYStnYpEg6tl4k8gZpvEpkkH4ZhOHLQdQ2WzFWFEGXHYZBqfXAoVO7-AYY2fFNzDQU4lQw7cDSFV-_IqaQnRl3hdHO7wkFY0G_pXRqoiyK0XmUXYbpyg1DdZlNOlSBaN6RwQmno_e7UN-ho7RsZ0Jw-4ErTlM83m6NeBPgT1hahjoaMxudx4b4ZWP1li5Y5aVBRQMF161DcUqwnJcUVFnJWQ'
"${WP[@]}" post meta update "$b" _gonorth_google_photo_attribution_name 'Peti Peti'
"${WP[@]}" post meta update "$b" _gonorth_google_photo_attribution_uri 'https://maps.google.com/maps/contrib/107031342619915571008'
"${WP[@]}" post meta update "$b" _gonorth_google_photo_name 'places/ChIJmeF0nmUfHBURwC8gAyBgeJg/photos/Aa-ngMagqKAkfu01QkbqsRA9aq3iaKx0-28ZkZcERWDgmcVt0meGTJSBOt6SSIpetM0SXy-gFIalF-pOWgov0d1WM-Rvn2ofKolzK0laqiCmeEosJAI62IOLFOYgdPoxIfh4WB4djNWvJu9m-QClQnVHEYE5Vxfvd8Gn2DQbfwRQe2ZQ6lzekX75GRQ5Ad4g7rOP-AOnPDhQqVYggf4aNVdKlI4wfK-bK27TMRxoYECSHeHe_QOFPSDloqaDzOWkm9By1dtzm1q8Cx_FKeK4aChjWjHglqUCnUY-1PMu9hYLs1DQ_PQBC9VEZ38k7dzE3e_t0d5vPanJgIr_4gWlo6dUgaJtfT72iPPeGFFdh8PvnT0EkAE6ixyhRunpyeO_HNaAzD6bfLQksBaTrlbM2n1RVOjDeyIPi5Q0PnZfqBmC_8qiE0C0eYbsxbEA9t7sYERq'
"${WP[@]}" post meta update 480 _thumbnail_id "$a"
"${WP[@]}" post meta update 481 _thumbnail_id "$b"
printf '{"shwartz_attachment":%s,"petipeti_attachment":%s}\n' "$a" "$b"

